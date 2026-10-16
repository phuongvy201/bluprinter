<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrokBotJob extends Model
{
    protected $fillable = [
        'user_id',
        'request_type',
        'width',
        'height',
        'dpi',
        'instruction',
        'outcome',
        'coordinator_message',
        'coordinator_model',
        'print_model',
        'source_path',
        'print_path',
        'layers',
        'checklist',
        'redo_note',
    ];

    protected function casts(): array
    {
        return [
            'layers' => 'array',
            'checklist' => 'array',
            'width' => 'integer',
            'height' => 'integer',
            'dpi' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checklistReady(): bool
    {
        $items = $this->checklist ?? [];
        if ($items === []) {
            return false;
        }

        foreach ($items as $item) {
            if (($item['status'] ?? '') !== 'dat') {
                return false;
            }
        }

        return true;
    }
}
