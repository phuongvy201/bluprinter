<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrokBotJob;
use App\Services\Grok\CoordinatorAgent;
use App\Services\Grok\PrintSpec;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GrokBotController extends Controller
{
    public function create(): View
    {
        return view('admin.grok-bot.create');
    }

    public function store(Request $request, CoordinatorAgent $coordinator): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $bytes = (string) file_get_contents($request->file('source')->getRealPath());

        return $this->run(
            $request,
            $coordinator,
            new PrintSpec(
                type: $validated['request_type'],
                width: $validated['width'] ?? null,
                height: $validated['height'] ?? null,
                dpi: $validated['dpi'] ?? null,
                sourceBytes: $bytes,
                instruction: trim((string) ($validated['instruction'] ?? '')),
            ),
        );
    }

    public function show(GrokBotJob $grokBot): View
    {
        return view('admin.grok-bot.show', ['job' => $grokBot]);
    }

    public function file(Request $request, GrokBotJob $grokBot): Response
    {
        if ($grokBot->print_path === null || ! Storage::disk('local')->exists($grokBot->print_path)) {
            abort(404);
        }

        $bytes = Storage::disk('local')->get($grokBot->print_path);
        $name = 'file-in-'.$grokBot->width.'x'.$grokBot->height.'-'.$grokBot->dpi.'dpi.png';
        $disposition = $request->query('inline') === '1' ? 'inline' : 'attachment';

        return response($bytes, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => $disposition.'; filename="'.$name.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function redo(Request $request, GrokBotJob $grokBot, CoordinatorAgent $coordinator): RedirectResponse
    {
        if ($grokBot->outcome !== 'clone' || $grokBot->source_path === null) {
            return back()->with('error', 'Chỉ gửi làm lại được yêu cầu clone đã có ảnh nguồn.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['in:size,dpi,font,position,image,art'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Chọn mục checklist chưa đạt trước khi gửi làm lại.',
            'items.min' => 'Chọn ít nhất một mục checklist.',
        ]);

        if (! Storage::disk('local')->exists($grokBot->source_path)) {
            return back()->with('error', 'Không còn ảnh nguồn để dựng lại.');
        }

        $labels = [
            'size' => 'kích thước',
            'dpi' => 'DPI',
            'font' => 'font',
            'position' => 'vị trí',
            'image' => 'ảnh',
            'art' => 'art',
        ];
        $picked = array_map(fn (string $key) => $labels[$key] ?? $key, $validated['items']);
        $redoNote = 'Mục lệch: '.implode(', ', $picked).'.';
        $extra = trim((string) ($validated['note'] ?? ''));
        if ($extra !== '') {
            $redoNote .= ' '.$extra;
        }

        return $this->run(
            $request,
            $coordinator,
            new PrintSpec(
                type: 'clone',
                width: $grokBot->width,
                height: $grokBot->height,
                dpi: $grokBot->dpi,
                sourceBytes: (string) Storage::disk('local')->get($grokBot->source_path),
                instruction: (string) $grokBot->instruction,
                redoNote: $redoNote,
            ),
            $grokBot,
        );
    }

    private function run(Request $request, CoordinatorAgent $coordinator, PrintSpec $spec, ?GrokBotJob $existing = null): RedirectResponse
    {
        try {
            $result = $coordinator->handle($spec);
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $this->safeMessage($exception->getMessage()));
        }

        if ($result->outcome === 'need_spec') {
            return back()->withInput()->withErrors(['spec' => $result->message]);
        }

        $job = $existing ?? new GrokBotJob(['user_id' => $request->user()->id]);
        $job->fill([
            'user_id' => $request->user()->id,
            'request_type' => $spec->type,
            'width' => $spec->width,
            'height' => $spec->height,
            'dpi' => $spec->dpi,
            'instruction' => $spec->instruction !== '' ? $spec->instruction : null,
            'outcome' => $result->outcome,
            'coordinator_message' => $result->message,
            'coordinator_model' => $result->model,
            'print_model' => null,
            'print_path' => null,
            'layers' => null,
            'checklist' => null,
            'redo_note' => $spec->redoNote !== '' ? $spec->redoNote : null,
        ]);
        $job->save();

        $sourcePath = 'grok-bot/'.$job->id.'/source.bin';
        Storage::disk('local')->put($sourcePath, $spec->sourceBytes);
        $job->source_path = $sourcePath;

        if ($result->print !== null) {
            $printPath = 'grok-bot/'.$job->id.'/print.png';
            Storage::disk('local')->put($printPath, $result->print->png);
            $job->print_path = $printPath;
            $job->print_model = $result->print->model;
            $job->layers = $result->print->layers;
            $job->checklist = $result->print->checklist;
        }

        $job->save();

        return redirect()->route('admin.grok-bot.show', $job);
    }

    /**
     * @return array{request_type: string, width: int|null, height: int|null, dpi: int|null, instruction: string|null}
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'source' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:20480'],
            'request_type' => ['required', 'in:design,clone,redesign'],
            'width' => ['nullable', 'integer', 'min:1', 'max:6000'],
            'height' => ['nullable', 'integer', 'min:1', 'max:6000'],
            'dpi' => ['nullable', 'integer', 'min:1', 'max:1200'],
            'instruction' => ['nullable', 'string', 'max:2000'],
        ], [
            'source.required' => 'Cần đưa ảnh nguồn.',
            'source.image' => 'File nguồn phải là ảnh.',
            'source.mimes' => 'Ảnh nguồn dùng JPG, PNG hoặc WEBP.',
            'source.max' => 'Ảnh nguồn tối đa 20 MB.',
            'request_type.required' => 'Chọn design, clone hoặc redesign.',
            'request_type.in' => 'Loại yêu cầu không hợp lệ.',
            'width.integer' => 'Chiều rộng phải là số pixel.',
            'width.min' => 'Chiều rộng phải lớn hơn 0.',
            'width.max' => 'Chiều rộng tối đa 6000 pixel.',
            'height.integer' => 'Chiều cao phải là số pixel.',
            'height.min' => 'Chiều cao phải lớn hơn 0.',
            'height.max' => 'Chiều cao tối đa 6000 pixel.',
            'dpi.integer' => 'DPI phải là số.',
            'dpi.min' => 'DPI phải lớn hơn 0.',
            'dpi.max' => 'DPI tối đa 1200.',
            'instruction.max' => 'Yêu cầu thêm tối đa 2000 ký tự.',
        ]);
    }

    private function safeMessage(string $message): string
    {
        $message = preg_replace('/xai-[A-Za-z0-9_\-]+/', '[ẩn]', $message) ?? '';
        $message = preg_replace('#https?://\S+#', '', $message) ?? '';

        return trim($message) !== '' ? trim($message) : 'Không xử lý được yêu cầu.';
    }
}
