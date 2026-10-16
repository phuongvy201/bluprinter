<?php

namespace App\Services\Grok;

class CoordinatorAgent
{
    public function __construct(
        private XaiClient $xai,
        private PrintFileAgent $printFile,
    ) {}

    public function handle(PrintSpec $spec): CoordinatorResult
    {
        if (! $spec->isComplete()) {
            return new CoordinatorResult(
                'need_spec',
                'Thiếu chiều rộng, chiều cao hoặc DPI. Yêu cầu được trả lại, agent file in chưa được gọi.',
            );
        }

        if ($spec->type !== 'clone') {
            $reply = $this->xai->complete(
                'Bạn là agent điều phối của BLUPRINTER. Design và redesign chưa làm file in ở giai đoạn 1. Không giao cho agent file in. Không đánh giá chất lượng ảnh. Trả một câu tiếng Việt xác nhận đã ghi nhận yêu cầu.',
                json_encode([
                    'loai' => $spec->type,
                    'rong' => $spec->width,
                    'cao' => $spec->height,
                    'dpi' => $spec->dpi,
                    'yeu_cau' => $spec->instruction,
                ], JSON_UNESCAPED_UNICODE)
            );

            $label = $spec->type === 'redesign' ? 'redesign' : 'design';

            return new CoordinatorResult(
                'phase_2',
                'Agent điều phối đã ghi nhận yêu cầu '.$label.'. Design và redesign mở ở giai đoạn 2, chưa xuất file in.',
                $reply->model,
            );
        }

        $reply = $this->xai->complete(
            'Bạn là agent điều phối của BLUPRINTER. Loại clone và spec đủ pixel, DPI thì giao cho agent file in. Không dùng ảnh generative. Không chấm điểm font hay 99%. Trả một câu tiếng Việt xác nhận đã giao việc.',
            json_encode([
                'loai' => 'clone',
                'rong' => $spec->width,
                'cao' => $spec->height,
                'dpi' => $spec->dpi,
                'yeu_cau' => $spec->instruction,
                'muc_gui_lai' => $spec->redoNote,
            ], JSON_UNESCAPED_UNICODE)
        );

        $print = $this->printFile->run($spec);

        return new CoordinatorResult(
            'clone',
            'Agent điều phối đã nhận clone và giao cho agent file in. PNG xuất từ ảnh nguồn, không vẽ bằng ảnh generative.',
            $reply->model,
            $print,
        );
    }
}
