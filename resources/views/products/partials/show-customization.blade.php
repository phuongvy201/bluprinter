{{-- Personalization / customization — expects $product --}}
@if($product->hasCustomization())
    @php
        $customizationFields = $product->getNormalizedCustomizations();
        $hasRequiredCustomization = $product->hasRequiredCustomizations();
    @endphp
    <section
        id="product-show-customization"
        class="product-show-customization"
        data-required="{{ $hasRequiredCustomization ? 'true' : 'false' }}"
        aria-labelledby="product-show-customization-heading"
    >
        <div class="product-show-customization__head">
            <div class="product-show-customization__head-text">
                <h3 id="product-show-customization-heading" class="product-show-customization__title">
                    Personalization
                    @if($hasRequiredCustomization)
                        <span class="product-show-customization__required-pill">Required</span>
                    @endif
                </h3>
                <p class="product-show-customization__note">
                    @if($hasRequiredCustomization)
                        Please complete all required fields before adding this item to your cart.
                    @else
                        Add optional personalization to make this item uniquely yours.
                    @endif
                </p>
            </div>
            @unless($hasRequiredCustomization)
                <button
                    type="button"
                    id="customization-toggle"
                    class="product-show-customization__toggle"
                    aria-expanded="true"
                    aria-controls="customization-container"
                    onclick="toggleCustomization()"
                >
                    <span data-toggle-label>Hide</span>
                    <svg class="product-show-customization__toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            @endunless
        </div>

        <div id="customization-container" class="product-show-customization__body space-y-3">
            @foreach($customizationFields as $index => $customization)
                @php
                    $customPrice = (float) ($customization['price'] ?? 0);
                    $customLabel = $customization['label'] ?? $customization['name'] ?? 'Personalize';
                    $fieldType = $customization['type'] ?? 'text';
                    $maxLength = (int) ($customization['max_length'] ?? 15);
                    $isRequired = (bool) ($customization['required'] ?? false);
                    $fieldName = 'customization_' . $index . '_' . $fieldType;
                    $options = $customization['options'] ?? [];
                @endphp
                <div
                    class="product-show-customization__box"
                    data-required="{{ $isRequired ? 'true' : 'false' }}"
                    data-field-label="{{ $customLabel }}"
                >
                    <div class="product-show-customization__field-head">
                        <h4 class="product-show-customization__field-title">
                            {{ $customLabel }}
                            @if($isRequired)
                                <span class="product-show-customization__required">*</span>
                            @endif
                            @if($customPrice > 0)
                                <span class="product-show-customization__price">(+{{ format_price_usd($customPrice) }})</span>
                            @endif
                        </h4>
                        @if(!empty($customization['description']))
                            <p class="product-show-customization__field-desc">{{ strip_tags(html_entity_decode($customization['description'], ENT_QUOTES, 'UTF-8')) }}</p>
                        @elseif(in_array($fieldType, ['text', 'textarea', 'number'], true))
                            <p class="product-show-customization__field-desc">Max {{ $maxLength }} characters.</p>
                        @endif
                    </div>

                    @if($fieldType === 'select' && count($options) > 0)
                        <div class="product-show-customization__options">
                            @foreach($options as $option)
                                <label class="product-show-customization__option">
                                    <input type="radio"
                                           name="{{ $fieldName }}"
                                           value="{{ $option['value'] ?? $option['label'] ?? '' }}"
                                           data-price="{{ convert_currency((float)($option['price'] ?? $customPrice)) }}"
                                           data-label="{{ $customLabel }}"
                                           @if($isRequired) required @endif
                                           onchange="updateCustomizationPrice()"
                                           class="customization-input">
                                    <span>{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($fieldType === 'checkbox')
                        <label class="product-show-customization__option product-show-customization__option--checkbox">
                            <input type="checkbox"
                                   id="customization-field-{{ $index }}"
                                   name="{{ $fieldName }}"
                                   value="yes"
                                   data-price="{{ convert_currency($customPrice) }}"
                                   data-label="{{ $customLabel }}"
                                   @if($isRequired) required @endif
                                   onchange="updateCustomizationPrice()"
                                   class="customization-input">
                            <span>{{ $customization['placeholder'] ?? 'Yes, add this option' }}</span>
                        </label>
                    @elseif($fieldType === 'textarea')
                        <textarea id="customization-field-{{ $index }}"
                                  name="{{ $fieldName }}"
                                  placeholder="{{ $customization['placeholder'] ?? 'e.g. YOUR NAME' }}"
                                  rows="{{ (int) ($customization['rows'] ?? 3) }}"
                                  maxlength="{{ $maxLength }}"
                                  data-price="{{ convert_currency($customPrice) }}"
                                  data-label="{{ $customLabel }}"
                                  @if($isRequired) required @endif
                                  oninput="updateCustomizationPrice()"
                                  class="customization-input product-show-customization__input">{{ old('customization_' . $index) }}</textarea>
                    @elseif($fieldType === 'file')
                        <input type="file"
                               id="customization-field-{{ $index }}"
                               name="customization_file_{{ $index }}"
                               accept="{{ $customization['accepted_formats'] ?? 'image/*' }}"
                               data-price="{{ convert_currency($customPrice) }}"
                               data-label="{{ $customLabel }}"
                               @if($isRequired) required @endif
                               onchange="updateCustomizationPrice()"
                               class="customization-input customization-file product-show-customization__input product-show-customization__input--file">
                    @else
                        <input type="{{ in_array($fieldType, ['number', 'text'], true) ? $fieldType : 'text' }}"
                               id="customization-field-{{ $index }}"
                               name="{{ $fieldName }}"
                               placeholder="{{ $customization['placeholder'] ?? 'e.g. YOUR NAME' }}"
                               value="{{ old('customization_' . $index) }}"
                               maxlength="{{ $fieldType === 'number' ? null : $maxLength }}"
                               data-price="{{ convert_currency($customPrice) }}"
                               data-label="{{ $customLabel }}"
                               @if($isRequired) required @endif
                               oninput="updateCustomizationPrice()"
                               class="customization-input product-show-customization__input">
                    @endif

                    <p class="product-show-customization__error hidden" data-field-error aria-live="polite"></p>
                </div>
            @endforeach
        </div>
    </section>
@endif
