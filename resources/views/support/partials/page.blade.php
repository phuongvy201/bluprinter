@php
    $eyebrow = $eyebrow ?? 'Support';
    $title = $title ?? 'Contact us';
    $subtitle = $subtitle ?? 'Tell us what you need and our team will get back to you by email.';
    $formAction = $formAction ?? '#';
    $submitLabel = $submitLabel ?? 'Submit';
    $formId = $formId ?? 'support-form';
@endphp

<section class="support-page" aria-labelledby="support-form-heading">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="section-heading section-heading--catalog scroll-reveal">
            <p class="section-heading__eyebrow">{{ $eyebrow }}</p>
            <h1 id="support-form-heading" class="section-heading__title">{{ $title }}</h1>
            <p class="section-heading__sub">{{ $subtitle }}</p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        @if (session('success'))
            <div class="support-alert support-alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="support-alert support-alert--error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="support-alert support-alert--error" role="alert">
                <p class="font-semibold mb-2">Please fix the following:</p>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="support-form-card">
            <form id="{{ $formId }}"
                  action="{{ $formAction }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="support-form"
                  novalidate>
                @csrf

                {{-- Anti-bot traps (must stay empty) --}}
                <div class="support-form__traps" aria-hidden="true">
                    <label for="{{ $formId }}-website">Website</label>
                    <input type="text"
                           name="website_url"
                           id="{{ $formId }}-website"
                           tabindex="-1"
                           autocomplete="off"
                           value="">
                    <label for="{{ $formId }}-fax">Fax</label>
                    <input type="text"
                           name="fax_number"
                           id="{{ $formId }}-fax"
                           tabindex="-1"
                           autocomplete="off"
                           value="">
                </div>
                <input type="hidden" name="form_started_at" id="{{ $formId }}-started-at" value="">

                <div class="support-form__grid support-form__grid--2">
                    <div class="support-form__field">
                        <label class="support-form__label" for="{{ $formId }}-name">Your name</label>
                        <input type="text"
                               name="name"
                               id="{{ $formId }}-name"
                               value="{{ old('name', auth()->user()->name ?? '') }}"
                               class="support-form__input @error('name') is-invalid @enderror"
                               required
                               autocomplete="name">
                        @error('name')
                            <p class="support-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="support-form__field">
                        <label class="support-form__label" for="{{ $formId }}-email">Email</label>
                        <input type="email"
                               name="email"
                               id="{{ $formId }}-email"
                               value="{{ old('email', auth()->user()->email ?? '') }}"
                               class="support-form__input @error('email') is-invalid @enderror"
                               required
                               autocomplete="email">
                        @error('email')
                            <p class="support-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="support-form__grid support-form__grid--2">
                    <div class="support-form__field">
                        <label class="support-form__label" for="{{ $formId }}-subject">Subject</label>
                        <input type="text"
                               name="subject"
                               id="{{ $formId }}-subject"
                               value="{{ old('subject') }}"
                               class="support-form__input @error('subject') is-invalid @enderror"
                               required>
                        @error('subject')
                            <p class="support-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="support-form__field">
                        <label class="support-form__label" for="{{ $formId }}-order">Order number <span class="support-form__optional">(optional)</span></label>
                        <input type="text"
                               name="order_number"
                               id="{{ $formId }}-order"
                               value="{{ old('order_number') }}"
                               class="support-form__input @error('order_number') is-invalid @enderror"
                               placeholder="e.g. BP-123456"
                               autocomplete="off">
                        @error('order_number')
                            <p class="support-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="support-form__field">
                    <label class="support-form__label" for="{{ $formId }}-message">Message</label>
                    <textarea name="message"
                              id="{{ $formId }}-message"
                              rows="6"
                              class="support-form__input support-form__textarea @error('message') is-invalid @enderror"
                              required
                              maxlength="5000">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="support-form__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="support-form__field">
                    <label class="support-form__label" for="{{ $formId }}-attachment">Attachment <span class="support-form__optional">(optional)</span></label>
                    <input type="file"
                           name="attachment"
                           id="{{ $formId }}-attachment"
                           class="support-form__file @error('attachment') is-invalid @enderror"
                           accept="image/*,application/pdf,.doc,.docx,.txt">
                    <p class="support-form__hint">Max 5MB. Allowed: images, PDF, DOC/DOCX, TXT.</p>
                    @error('attachment')
                        <p class="support-form__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="support-form__actions">
                    <button type="submit" class="btn-cta" id="{{ $formId }}-submit">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var startedAt = document.getElementById(@json($formId . '-started-at'));
    if (startedAt) {
        startedAt.value = Math.floor(Date.now() / 1000);
    }

    var form = document.getElementById(@json($formId));
    var submitBtn = document.getElementById(@json($formId . '-submit'));
    if (!form || !submitBtn) return;

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.setAttribute('aria-busy', 'true');
        submitBtn.textContent = 'Sending…';
    });
});
</script>
