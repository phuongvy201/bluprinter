/* Simple direct S3 uploader using presigned PUT URLs.
 * - Upload nhiều file song song với giới hạn concurrency
 * - Retry theo exponential backoff khi lỗi tạm thời (5xx, network)
 */

async function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function fetchWithRetry(url, options, { retries = 3, baseDelay = 400 } = {}) {
    let attempt = 0;
    while (true) {
        try {
            const res = await fetch(url, options);
            if (!res.ok) {
                // 5xx retryable
                if (res.status >= 500 && attempt < retries) {
                    attempt++;
                    await sleep(baseDelay * Math.pow(2, attempt - 1));
                    continue;
                }
                throw new Error(`HTTP ${res.status}`);
            }
            return res;
        } catch (err) {
            if (attempt < retries) {
                attempt++;
                await sleep(baseDelay * Math.pow(2, attempt - 1));
                continue;
            }
            throw err;
        }
    }
}

export async function uploadFilesDirect({
    files,
    presignEndpoint = '/api/upload/presigned-urls',
    extraPayload = {},
    concurrency = 4,
    onProgress = () => {},
}) {
    // 1) Gọi server để lấy presigned URLs
    const filePayload = files.map((f) => ({
        name: f.name,
        type: f.type || 'application/octet-stream',
        size: f.size,
    }));

    const presignResp = await fetch(presignEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ files: filePayload, ...extraPayload }),
        cache: 'no-store',
    });
    if (!presignResp.ok) throw new Error('Không lấy được presigned URLs');
    const presignData = await presignResp.json();
    const presigned = presignData?.data?.presigned_urls || [];

    // 2) Upload song song với giới hạn concurrency
    let completed = 0;
    const total = files.length;

    const queue = files.map((file, idx) => async () => {
        const info = presigned.find((x) => x.index === idx);
        if (!info) throw new Error(`Thiếu presigned URL cho file index ${idx}`);

        const putHeaders = { 'Content-Type': info.type || file.type || 'application/octet-stream' };

        await fetchWithRetry(info.upload_url, {
            method: 'PUT',
            body: file,
            headers: putHeaders,
            cache: 'no-store',
        });

        completed += 1;
        onProgress({ completed, total, file, index: idx, finalUrl: info.final_url, key: info.key });
        return { index: idx, key: info.key, public_url: info.final_url, type: info.type };
    });

    const results = [];
    const workers = Array.from({ length: Math.min(concurrency, queue.length) }, async () => {
        while (queue.length) {
            const task = queue.shift();
            if (!task) break;
            try {
                const r = await task();
                results.push(r);
            } catch (e) {
                results.push({ error: e?.message || String(e) });
            }
        }
    });
    await Promise.all(workers);

    return results;
}

// Optional global attach for quick testing in console
if (typeof window !== 'undefined') {
    window.DirectS3Upload = { uploadFilesDirect };
}

/**
 * Direct upload helper for Bluprinter
 * Usage:
 *  const uploader = new DirectUploader({ apiBase: '' });
 *  const { presigned } = await uploader.getPresignedUrls(files);
 *  await uploader.uploadAll(presigned, files);
 *  await uploader.createProduct({ name, template_id, media_urls: uploader.getFinalUrls(presigned), ... });
 */
(function(global){
  class DirectUploader {
    constructor(opts = {}) {
      this.apiBase = opts.apiBase || '';
    }

    async getPresignedUrls(files) {
      const payload = {
        files: files.map(f => ({ filename: f.name, content_type: f.type }))
      };
      const res = await fetch(this.apiBase + '/api/upload/presigned-urls', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      if (!res.ok) throw new Error('Failed to get presigned URLs');
      const data = await res.json();
      const list = (data.data && (data.data.presigned_urls || data.data.upload_urls)) || [];
      return { presigned: list };
    }

    async uploadFile(uploadUrl, file, contentType) {
      const res = await fetch(uploadUrl, {
        method: 'PUT',
        headers: { 'Content-Type': contentType },
        body: file
      });
      if (!res.ok) throw new Error('Upload failed: ' + file.name);
      return true;
    }

    async uploadAll(presignedList, files) {
      // Map by filename to original File
      const nameToFile = new Map(files.map(f => [f.name, f]));
      // Upload sequentially to avoid CORS/rate issues; can parallelize if needed
      for (const item of presignedList) {
        const file = nameToFile.get(item.original_name || item.filename);
        if (!file) continue;
        await this.uploadFile(item.upload_url, file, file.type);
      }
    }

    getFinalUrls(presignedList) {
      return presignedList.map(i => i.final_url || i.public_url).filter(Boolean);
    }

    async createProduct(payload) {
      const res = await fetch(this.apiBase + '/api/products/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        const txt = await res.text();
        throw new Error('Create product failed: ' + txt);
      }
      return res.json();
    }
  }

  global.DirectUploader = DirectUploader;
})(window);


