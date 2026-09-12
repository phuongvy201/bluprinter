@extends('layouts.admin')

@section('title', 'Studio AI settings')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('admin.studio-ai.index') }}" class="text-sm text-blue-600 hover:underline">&larr; All generations</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Studio AI settings</h1>
        <p class="mt-1 text-sm text-gray-600">Prompts, limits, models, and try-on for Create Your Own, AI Design Gen, and Virtual Try-On. API keys stay in <code class="text-xs bg-gray-100 px-1 rounded">.env</code>.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600">
        <p><span class="font-semibold text-gray-900">Gateway:</span> {{ $baseUrl ?: 'not set' }}</p>
        <p class="mt-1"><span class="font-semibold text-gray-900">API key:</span> {{ $hasApiKey ? 'configured' : 'missing — set STUDIO_AI_API_KEY' }}</p>
    </div>

    <form method="POST" action="{{ route('admin.studio-ai.settings.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Features</h2>
            <label class="flex items-center gap-3">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" class="rounded border-gray-300 text-blue-600" @checked(old('enabled', $settings['enabled']))>
                <span class="text-sm font-medium text-gray-900">Enable AI Design Gen (prompt + image)</span>
            </label>
            <label class="flex items-center gap-3">
                <input type="hidden" name="try_on_enabled" value="0">
                <input type="checkbox" name="try_on_enabled" value="1" class="rounded border-gray-300 text-blue-600" @checked(old('try_on_enabled', $settings['try_on_enabled']))>
                <span class="text-sm font-medium text-gray-900">Enable AI Virtual Try-On</span>
            </label>
            <p class="text-xs text-gray-500">When Design Gen is off, customers can still upload files and use the design library.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Virtual Try-On photos</h2>
            <p class="text-sm text-gray-600">These photos appear in the Try-On modal (Upload now is always first). JPG, PNG, or WEBP, up to 10 MB each. Leave a row’s file empty to keep the current image.</p>
            <div id="try-on-model-rows" class="space-y-4">
                @foreach (old('try_on_models', $settings['try_on_models']) as $i => $model)
                    @php
                        $model = is_array($model) ? $model : [];
                        $preview = $model['src'] ?? '';
                        $stored = $model['path'] ?? $model['src'] ?? '';
                    @endphp
                    <div class="try-on-model-row flex flex-col sm:flex-row gap-4 rounded-lg border border-gray-200 p-4">
                        <div class="w-24 h-24 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                            @if ($preview)
                                <img src="{{ $preview }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="hidden" name="try_on_models[{{ $i }}][id]" value="{{ $model['id'] ?? '' }}">
                            <input type="hidden" name="try_on_models[{{ $i }}][src]" value="{{ $stored }}">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                                <input type="text" name="try_on_models[{{ $i }}][label]" value="{{ $model['label'] ?? '' }}"
                                       class="w-full rounded-lg border-gray-300 text-sm" placeholder="Woman">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Replace photo</label>
                                <input type="file" name="try_on_models[{{ $i }}][image]" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                            </div>
                            <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="try_on_models[{{ $i }}][_delete]" value="1" class="rounded border-gray-300">
                                Remove this photo
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="try-on-model-add" class="text-sm font-semibold text-blue-600 hover:underline">+ Add photo</button>
            <template id="try-on-model-template">
                <div class="try-on-model-row flex flex-col sm:flex-row gap-4 rounded-lg border border-gray-200 p-4">
                    <div class="w-24 h-24 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"></div>
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="hidden" name="try_on_models[__I__][id]" value="">
                        <input type="hidden" name="try_on_models[__I__][src]" value="">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" name="try_on_models[__I__][label]" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Model">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Photo</label>
                            <input type="file" name="try_on_models[__I__][image]" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Models</h2>
            <div>
                <label for="image_model" class="block text-sm font-medium text-gray-700 mb-1">Image model</label>
                <input type="text" name="image_model" id="image_model" value="{{ old('image_model', $settings['image_model']) }}"
                       class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ $defaults['image_model'] ?: 'gpt-image-2' }}">
                <p class="mt-1 text-xs text-gray-500">Must exist on GET /v1/models of your gateway. Leave blank to use .env.</p>
            </div>
            <div>
                <label for="chat_model" class="block text-sm font-medium text-gray-700 mb-1">Prompt (chat) model</label>
                <input type="text" name="chat_model" id="chat_model" value="{{ old('chat_model', $settings['chat_model']) }}"
                       class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ $defaults['chat_model'] ?: 'gpt-5-5-mini' }}">
                <p class="mt-1 text-xs text-gray-500">Text model for Improve prompt. Do not use an image model here.</p>
            </div>
            <div>
                <label for="image_size" class="block text-sm font-medium text-gray-700 mb-1">Image size</label>
                <input type="text" name="image_size" id="image_size" value="{{ old('image_size', $settings['image_size']) }}"
                       class="w-full rounded-lg border-gray-300 text-sm" placeholder="1024x1024">
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Limits</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="image_count" class="block text-sm font-medium text-gray-700 mb-1">Images per generate</label>
                    <input type="number" name="image_count" id="image_count" min="1" max="4"
                           value="{{ old('image_count', $settings['image_count']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="timeout" class="block text-sm font-medium text-gray-700 mb-1">Timeout (seconds)</label>
                    <input type="number" name="timeout" id="timeout" min="30" max="300"
                           value="{{ old('timeout', $settings['timeout']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="prompt_max" class="block text-sm font-medium text-gray-700 mb-1">Max prompt characters</label>
                    <input type="number" name="prompt_max" id="prompt_max" min="200" max="2000"
                           value="{{ old('prompt_max', $settings['prompt_max']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="max_references" class="block text-sm font-medium text-gray-700 mb-1">Max reference images</label>
                    <input type="number" name="max_references" id="max_references" min="1" max="8"
                           value="{{ old('max_references', $settings['max_references']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="generate_per_minute" class="block text-sm font-medium text-gray-700 mb-1">Generate / minute / IP</label>
                    <input type="number" name="generate_per_minute" id="generate_per_minute" min="1" max="60"
                           value="{{ old('generate_per_minute', $settings['generate_per_minute']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="improve_per_minute" class="block text-sm font-medium text-gray-700 mb-1">Improve prompt / minute / IP</label>
                    <input type="number" name="improve_per_minute" id="improve_per_minute" min="1" max="60"
                           value="{{ old('improve_per_minute', $settings['improve_per_minute']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="try_on_per_minute" class="block text-sm font-medium text-gray-700 mb-1">Try-on / minute / IP</label>
                    <input type="number" name="try_on_per_minute" id="try_on_per_minute" min="1" max="60"
                           value="{{ old('try_on_per_minute', $settings['try_on_per_minute']) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">System prompts</h2>
            <div>
                <label for="improve_system_prompt" class="block text-sm font-medium text-gray-700 mb-1">Improve prompt (system)</label>
                <textarea name="improve_system_prompt" id="improve_system_prompt" rows="5" class="w-full rounded-lg border-gray-300 text-sm">{{ old('improve_system_prompt', $settings['improve_system_prompt']) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Instructions sent when the customer taps AI improve prompt.</p>
            </div>
            <div>
                <label for="improve_references_note" class="block text-sm font-medium text-gray-700 mb-1">Improve prompt — when references are attached</label>
                <textarea name="improve_references_note" id="improve_references_note" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old('improve_references_note', $settings['improve_references_note']) }}</textarea>
            </div>
            <div>
                <label for="design_suffix" class="block text-sm font-medium text-gray-700 mb-1">Design generate suffix</label>
                <textarea name="design_suffix" id="design_suffix" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old('design_suffix', $settings['design_suffix']) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Appended to every design generation (print graphic, no mockup, etc.).</p>
            </div>
            <div>
                <label for="describe_references_prompt" class="block text-sm font-medium text-gray-700 mb-1">Describe reference photos</label>
                <textarea name="describe_references_prompt" id="describe_references_prompt" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old('describe_references_prompt', $settings['describe_references_prompt']) }}</textarea>
            </div>
            <div>
                <label for="try_on_prompt" class="block text-sm font-medium text-gray-700 mb-1">Virtual try-on prompt</label>
                <textarea name="try_on_prompt" id="try_on_prompt" rows="6" class="w-full rounded-lg border-gray-300 text-sm">{{ old('try_on_prompt', $settings['try_on_prompt']) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Use <code class="bg-gray-100 px-1 rounded">{product_name}</code> and <code class="bg-gray-100 px-1 rounded">{product_type}</code> (T-Shirt, Phone Case, Hat, Mug…). The model should wear apparel, put hats on the head, put case artwork on a phone, and never print accessory designs onto clothing.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Inspiration prompts</h2>
            <p class="text-xs text-gray-500">One prompt per line. Shown as examples on Studio and AI Design Gen.</p>
            <textarea name="inspiration_prompts" rows="8" class="w-full rounded-lg border-gray-300 text-sm">{{ old('inspiration_prompts', implode("\n", $settings['inspiration_prompts'])) }}</textarea>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Customer artwork prices (USD)</h2>
            <p class="text-sm text-gray-600">AI generation, try-on, and customer uploads are free. Only the garment size price is charged at checkout. Library designs can still have their own price.</p>
            <input type="hidden" name="ai_design_price" value="0">
            <input type="hidden" name="upload_design_price" value="0">
            <div>
                <label for="library_default_price" class="block text-sm font-medium text-gray-700 mb-1">Default library design</label>
                <input type="number" name="library_default_price" id="library_default_price" step="0.01" min="0"
                       value="{{ old('library_default_price', $pricing['library_default_price']) }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
                <p class="mt-1 text-xs text-gray-500">Used when adding a new library design. Each design can still have its own price.</p>
            </div>
        </div>

        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Save settings</button>
    </form>
</div>
<script>
document.getElementById('try-on-model-add')?.addEventListener('click', function () {
    const wrap = document.getElementById('try-on-model-rows');
    const tpl = document.getElementById('try-on-model-template');
    if (!wrap || !tpl) return;
    const index = wrap.querySelectorAll('.try-on-model-row').length;
    wrap.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__I__', String(index)));
});
</script>
@endsection
