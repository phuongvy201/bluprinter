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


