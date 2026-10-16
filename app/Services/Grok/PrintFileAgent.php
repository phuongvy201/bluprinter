<?php

namespace App\Services\Grok;

use LogicException;

class PrintFileAgent
{
    public function __construct(
        private XaiClient $xai,
        private ClonePrintPipeline $pipeline,
    ) {}

    public function run(PrintSpec $spec): ClonePrintResult
    {
        if ($spec->type !== 'clone' || ! $spec->isComplete()) {
            throw new LogicException('Agent file in chỉ chạy khi clone đã đủ pixel và DPI.');
        }

        $reply = $this->xai->complete(
            'Bạn là agent file in POD của BLUPRINTER. Chỉ xác nhận đã nhận spec clone (pixel và DPI). Không yêu cầu vẽ lại bằng ảnh generative. Không đánh giá font, vị trí, art hay phần trăm khớp. Trả một câu tiếng Việt ngắn.',
            json_encode([
                'loai' => 'clone',
                'rong' => $spec->width,
                'cao' => $spec->height,
                'dpi' => $spec->dpi,
                'yeu_cau' => $spec->instruction,
                'muc_gui_lai' => $spec->redoNote,
                'cach_xuat' => 'Pipeline cục bộ lấy mẫu lại ảnh nguồn ra một PNG đúng pixel và gắn chunk pHYs. Không sinh ảnh mới.',
            ], JSON_UNESCAPED_UNICODE)
        );

        return $this->pipeline->build(
            $spec->sourceBytes,
            (int) $spec->width,
            (int) $spec->height,
            (int) $spec->dpi,
            $reply->model,
        );
    }
}
