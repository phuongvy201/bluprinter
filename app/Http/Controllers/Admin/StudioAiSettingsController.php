<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\S3Media;
use App\Support\StudioAiSettings;
use App\Support\StudioPricingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioAiSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.studio-ai.settings', [
            'settings' => StudioAiSettings::resolved(),
            'pricing' => StudioPricingSettings::resolved(),
            'defaults' => StudioAiSettings::defaults(),
            'hasApiKey' => filled(config('studio.ai.api_key')) || filled(config('studio.ai.completion_api_key')),
            'baseUrl' => (string) config('studio.ai.base_url'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'try_on_enabled' => ['nullable', 'boolean'],
            'image_model' => ['nullable', 'string', 'max:80'],
            'chat_model' => ['nullable', 'string', 'max:80'],
            'image_size' => ['nullable', 'string', 'max:32'],
            'image_count' => ['required', 'integer', 'min:1', 'max:4'],
            'timeout' => ['required', 'integer', 'min:30', 'max:300'],
            'prompt_max' => ['required', 'integer', 'min:200', 'max:2000'],
            'max_references' => ['required', 'integer', 'min:1', 'max:8'],
            'generate_per_minute' => ['required', 'integer', 'min:1', 'max:60'],
            'improve_per_minute' => ['required', 'integer', 'min:1', 'max:60'],
            'try_on_per_minute' => ['required', 'integer', 'min:1', 'max:60'],
            'inspiration_prompts' => ['nullable', 'string'],
            'improve_system_prompt' => ['nullable', 'string', 'max:4000'],
            'improve_references_note' => ['nullable', 'string', 'max:2000'],
            'design_suffix' => ['nullable', 'string', 'max:2000'],
            'try_on_prompt' => ['nullable', 'string', 'max:4000'],
            'describe_references_prompt' => ['nullable', 'string', 'max:2000'],
            'try_on_models' => ['nullable', 'array', 'max:12'],
            'try_on_models.*.id' => ['nullable', 'string', 'max:40'],
            'try_on_models.*.label' => ['nullable', 'string', 'max:80'],
            'try_on_models.*.src' => ['nullable', 'string', 'max:500'],
            'try_on_models.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'ai_design_price' => ['required', 'numeric', 'min:0', 'max:9999'],
            'upload_design_price' => ['required', 'numeric', 'min:0', 'max:9999'],
            'library_default_price' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $defaults = StudioAiSettings::defaults();

        StudioAiSettings::save([
            'enabled' => $request->boolean('enabled'),
            'try_on_enabled' => $request->boolean('try_on_enabled'),
            'image_model' => trim((string) ($validated['image_model'] ?? '')),
            'chat_model' => trim((string) ($validated['chat_model'] ?? '')),
            'image_size' => trim((string) ($validated['image_size'] ?? '1024x1024')),
            'image_count' => (int) $validated['image_count'],
            'timeout' => (int) $validated['timeout'],
            'prompt_max' => (int) $validated['prompt_max'],
            'max_references' => (int) $validated['max_references'],
            'generate_per_minute' => (int) $validated['generate_per_minute'],
            'improve_per_minute' => (int) $validated['improve_per_minute'],
            'try_on_per_minute' => (int) $validated['try_on_per_minute'],
            'try_on_models' => $this->saveTryOnModels($request),
            'inspiration_prompts' => StudioAiSettings::normalizePrompts($validated['inspiration_prompts'] ?? ''),
            'improve_system_prompt' => trim((string) ($validated['improve_system_prompt'] ?? '')) ?: $defaults['improve_system_prompt'],
            'improve_references_note' => trim((string) ($validated['improve_references_note'] ?? '')) ?: $defaults['improve_references_note'],
            'design_suffix' => trim((string) ($validated['design_suffix'] ?? '')) ?: $defaults['design_suffix'],
            'try_on_prompt' => trim((string) ($validated['try_on_prompt'] ?? '')) ?: $defaults['try_on_prompt'],
            'describe_references_prompt' => trim((string) ($validated['describe_references_prompt'] ?? '')) ?: $defaults['describe_references_prompt'],
        ]);

        StudioPricingSettings::save([
            'ai_design_price' => $validated['ai_design_price'],
            'upload_design_price' => $validated['upload_design_price'],
            'library_default_price' => $validated['library_default_price'],
        ]);

        return redirect()
            ->route('admin.studio-ai.settings')
            ->with('success', 'Studio AI settings saved.');
    }

    /**
     * @return array<int, array{id: string, label: string, src: string}>
     */
    protected function saveTryOnModels(Request $request): array
    {
        $models = [];
        foreach ($request->input('try_on_models', []) as $index => $row) {
            if (! is_array($row) || ! empty($row['_delete'])) {
                continue;
            }
            $src = trim((string) ($row['src'] ?? ''));
            $file = $request->file('try_on_models.'.$index.'.image');
            if ($file) {
                $uploaded = S3Media::store($file, 'studio/models');
                if ($uploaded) {
                    $src = $uploaded;
                }
            }
            if ($src === '') {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $id = trim((string) ($row['id'] ?? ''));
            $models[] = [
                'id' => $id !== '' ? $id : 'model-'.($index + 1),
                'label' => $label !== '' ? $label : 'Model',
                'src' => $src,
            ];
        }

        return $models !== [] ? $models : StudioAiSettings::defaultTryOnModels();
    }
}
