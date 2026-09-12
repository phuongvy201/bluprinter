(function () {
    const CART_PROMO_KEY = 'cart_promo_claimed';
    const FREE_SHIPPING_USD = 100;

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function currencyCode() {
        return window.CURRENT_CURRENCY || window.SITE_CURRENCY || 'USD';
    }

    function currencySymbol() {
        return window.CURRENCY_SYMBOL || window.SITE_CURRENCY_SYMBOL || '$';
    }

    function currencyRate() {
        return parseFloat(window.CURRENT_CURRENCY_RATE || 1) || 1;
    }

    function authUser() {
        return window.AUTH_USER || null;
    }

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf(),
        };
    }

    function showAlert(options) {
        options = options || {};
        if (typeof Swal !== 'undefined') {
            return Swal.fire(options);
        }
        if (options.showCancelButton) {
            const ok = window.confirm(options.text || options.title || '');
            return Promise.resolve({ isConfirmed: ok });
        }
        window.alert((options.title ? options.title + '\n\n' : '') + (options.text || ''));
        return Promise.resolve({ isConfirmed: true });
    }

    function showCartSuccess(message) {
        message = message || 'Added to cart successfully!';
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-[96] flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg border border-green-100 bg-green-50 text-green-700';
        notification.innerHTML =
            '<svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' +
            '<span class="text-sm font-semibold">' + message + '</span>';
        document.body.appendChild(notification);
        setTimeout(function () { notification.remove(); }, 3000);
    }

    function hasCartPromoBeenClaimedLocally() {
        try {
            return sessionStorage.getItem(CART_PROMO_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function markCartPromoClaimedLocally() {
        try {
            sessionStorage.setItem(CART_PROMO_KEY, '1');
        } catch (e) {}
    }

    function updateCartCount() {
        window.dispatchEvent(new CustomEvent('cartUpdated'));
    }

    function getCartItemUnitPrice(item) {
        if (item.effective_unit_price !== undefined && item.effective_unit_price !== null) {
            return parseFloat(item.effective_unit_price) || 0;
        }
        return parseFloat(item.price) || 0;
    }

    function calculateItemTotal(item) {
        return getCartItemUnitPrice(item) * (parseInt(item.quantity, 10) || 1);
    }

    function calculateCartSubtotalFromItems(cartItems) {
        if (!cartItems || !cartItems.length) return 0;
        return cartItems.reduce(function (sum, item) {
            return sum + calculateItemTotal(item);
        }, 0);
    }

    function calculateBaseSubtotalUsd(cartItems, currency, rate) {
        if (!cartItems || !cartItems.length) return 0;
        return cartItems.reduce(function (sum, item) {
            const unit = getCartItemUnitPrice(item);
            const usd = currency !== 'USD' && rate > 0 ? unit / rate : unit;
            return sum + usd * (parseInt(item.quantity, 10) || 1);
        }, 0);
    }

    function qualifiesForFreeShipping(baseSubtotalUsd) {
        return baseSubtotalUsd >= FREE_SHIPPING_USD;
    }

    function renderFreeShippingBar(baseSubtotalUsd, currency, rate) {
        const remainingUsd = Math.max(0, FREE_SHIPPING_USD - baseSubtotalUsd);
        const remainingDisplay = currency !== 'USD' && rate > 0 ? remainingUsd * rate : remainingUsd;
        const progress = Math.min(100, (baseSubtotalUsd / FREE_SHIPPING_USD) * 100);
        if (qualifiesForFreeShipping(baseSubtotalUsd)) {
            return '<div class="cart-drawer-freeship cart-drawer-freeship--done" id="cart-popup-freeship">' +
                '<span class="cart-drawer-freeship__icon" aria-hidden="true">✓</span>' +
                '<span>You&apos;ve unlocked free shipping!</span></div>';
        }
        return '<div class="cart-drawer-freeship" id="cart-popup-freeship">' +
            '<p class="cart-drawer-freeship__text">Add <strong>' + currencySymbol() + remainingDisplay.toFixed(2) + '</strong> more for <strong>FREE SHIPPING</strong></p>' +
            '<div class="cart-drawer-freeship__track" aria-hidden="true"><span class="cart-drawer-freeship__fill" id="cart-popup-freeship-fill" style="width: ' + progress.toFixed(1) + '%"></span></div>' +
            '</div>';
    }

    function renderCartPromoOfferBlock() {
        if (authUser()?.email || hasCartPromoBeenClaimedLocally()) {
            return '';
        }
        return '<div class="cart-drawer-promo" id="cart-promo-offer">' +
            '<div class="cart-drawer-promo__head"><span class="cart-drawer-promo__code" aria-hidden="true">CART5</span><div>' +
            '<p class="cart-drawer-promo__title">Get 5% off your order</p>' +
            '<p class="cart-drawer-promo__sub">Enter your email — we&apos;ll send the code instantly.</p>' +
            '</div></div>' +
            '<form class="cart-drawer-promo__form" onsubmit="submitCartPromoEmail(event)">' +
            '<input type="email" name="email" required autocomplete="email" placeholder="Your email" class="cart-drawer-promo__input" aria-label="Email for promo code">' +
            '<button type="submit" class="cart-drawer-promo__submit">Get code</button>' +
            '</form></div>';
    }

    function handlePostAddToCartPromo() {
        if (!authUser()?.email || hasCartPromoBeenClaimedLocally()) return;
        fetch('/api/promo/claim-cart', {
            method: 'POST',
            headers: jsonHeaders(),
            credentials: 'same-origin',
            body: JSON.stringify({}),
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) return;
                markCartPromoClaimedLocally();
                if (!data.already_sent) {
                    showCartSuccess('Code ' + data.code + ' sent to ' + authUser().email + '!');
                }
            })
            .catch(function () {});
    }

    async function submitCartPromoEmail(event) {
        event.preventDefault();
        const form = event.target;
        const email = form.querySelector('[name="email"]')?.value?.trim();
        if (!email) return;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        try {
            const response = await fetch('/api/promo/claim-cart', {
                method: 'POST',
                headers: jsonHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ email: email }),
            });
            const data = await response.json();
            if (data.success) {
                markCartPromoClaimedLocally();
                const wrap = document.getElementById('cart-promo-offer');
                if (wrap) {
                    wrap.className = 'cart-drawer-promo cart-drawer-promo--success';
                    wrap.innerHTML = 'Code <strong>' + (data.code || '') + '</strong> sent to ' + email + '.';
                }
            } else {
                showAlert({ icon: 'error', title: 'Unable to send code', text: data.message || 'Please try again.', confirmButtonColor: '#005366' });
            }
        } catch (e) {
            showAlert({ icon: 'error', title: 'Unable to send code', text: 'Please try again.', confirmButtonColor: '#005366' });
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    function generateCartPopupItems(cartItems) {
        if (!cartItems || !cartItems.length) {
            return '<p class="cart-popup-empty">Your cart is empty</p>';
        }

        function cartLineName(item) {
            return item.display_name
                || (item.customizations && item.customizations._studio && item.customizations._studio.title)
                || (item.product && item.product.name)
                || 'Custom product';
        }

        function cartLineImage(item, product) {
            if (item.display_image) return item.display_image;
            if (item.customizations && item.customizations._studio && item.customizations._studio.image) {
                return item.customizations._studio.image;
            }
            if (item.is_studio_custom) return null;
            if (product.media) {
                if (Array.isArray(product.media) && product.media.length > 0) {
                    const firstMedia = product.media[0];
                    return typeof firstMedia === 'object' ? (firstMedia.url || firstMedia) : firstMedia;
                }
                if (typeof product.media === 'string') return product.media;
            }
            return null;
        }

        function visibleCustomizations(item) {
            const rows = item.customizations || {};
            return Object.entries(rows).filter(function (entry) {
                return String(entry[0]).charAt(0) !== '_';
            });
        }

        const symbol = currencySymbol();
        return cartItems.map(function (item) {
            const product = item.product || {};
            const shop = product.shop || {};
            const isStudio = !!(item.is_studio_custom || (item.customizations && item.customizations._studio && item.customizations._studio.standalone));
            const name = cartLineName(item);
            const productImage = cartLineImage(item, product);
            const customs = visibleCustomizations(item);
            const imageHtml = productImage
                ? '<img src="' + productImage + '" alt="">'
                : '<div class="cart-popup-item__media-fallback"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>';
            const shopHtml = !isStudio && shop.name
                ? '<p class="cart-popup-item__shop">Sold by <a href="/shops/' + (shop.shop_slug || '') + '">' + shop.name + '</a></p>'
                : '';
            const variantHtml = !isStudio && item.selected_variant && item.selected_variant.attributes
                ? '<div class="mb-2">' + Object.entries(item.selected_variant.attributes).map(function (entry) {
                    return '<span class="cart-popup-item__variant">' + entry[0] + ': ' + entry[1] + '</span>';
                }).join('') + '</div>'
                : '';
            const customHtml = customs.length
                ? '<div class="cart-popup-item__custom">' + customs.map(function (entry) {
                    const custom = entry[1];
                    const extra = custom && custom.price > 0 ? ' (+' + symbol + parseFloat(custom.price).toFixed(2) + ')' : '';
                    return '<div>' + entry[0] + ': ' + (custom && custom.value ? custom.value : '') + extra + '</div>';
                }).join('') + '</div>'
                : '';
            const unitHtml = item.quantity > 1
                ? '<p class="cart-popup-item__unit">' + symbol + getCartItemUnitPrice(item).toFixed(2) + ' each</p>'
                : '';

            return '<article class="cart-popup-item"><div class="cart-popup-item__inner">' +
                '<div class="cart-popup-item__media">' + imageHtml + '</div>' +
                '<div class="flex-1 min-w-0"><div class="flex justify-between items-start gap-2 mb-1">' +
                '<h4 class="cart-popup-item__name">' + name + '</h4>' +
                '<button type="button" class="cart-popup-item__remove remove-cart-item" data-cart-item-id="' + item.id + '" aria-label="Remove item">' +
                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                '</button></div>' + shopHtml + variantHtml + customHtml +
                '<div class="cart-popup-item__footer"><div class="cart-popup-qty">' +
                '<button type="button" class="cart-popup-qty__btn decrease-quantity' + (item.quantity <= 1 ? ' opacity-50 cursor-not-allowed' : '') + '" data-cart-item-id="' + item.id + '" data-new-quantity="' + (item.quantity - 1) + '"' + (item.quantity <= 1 ? ' disabled' : '') + ' aria-label="Decrease quantity">' +
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg></button>' +
                '<span class="cart-popup-qty__value" id="quantity-' + item.id + '">' + item.quantity + '</span>' +
                '<button type="button" class="cart-popup-qty__btn increase-quantity" data-cart-item-id="' + item.id + '" data-new-quantity="' + (item.quantity + 1) + '" aria-label="Increase quantity">' +
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>' +
                '</div><div><p class="cart-popup-item__price">' + symbol + calculateItemTotal(item).toFixed(2) + '</p>' + unitHtml + '</div></div>' +
                '</div></div></article>';
        }).join('');
    }

    function generateCrossSellProducts() {
        const related = window.CART_CROSS_SELL_PRODUCTS || [];
        if (!related.length) {
            return '<p class="cart-popup-recs__empty">No recommendations available</p>';
        }
        const symbol = currencySymbol();
        return related.slice(0, 4).map(function (product) {
            return '<div class="cross-sell-product" data-product-id="' + product.id + '" data-product-slug="' + (product.slug || '') + '">' +
                (product.image ? '<div class="cross-sell-product__media"><img src="' + product.image + '" alt=""></div>' : '') +
                '<h4 class="cross-sell-product__name">' + (product.name || '') + '</h4>' +
                '<div class="cross-sell-product__footer"><span class="cross-sell-product__price">' + symbol + product.price + '</span></div></div>';
        }).join('');
    }

    function renderCartDrawerBill(opts) {
        const itemLabel = opts.totalItems === 1 ? 'item' : 'items';
        const shippingValueText = opts.shippingIsFree ? 'FREE' : currencySymbol() + opts.shippingCost.toFixed(2);
        const valueClass = opts.shippingIsFree ? 'cart-drawer-bill__row-value cart-drawer-bill__row-value--free' : 'cart-drawer-bill__row-value';
        const exchangeHtml = opts.currency !== 'USD' && opts.currencyRate !== 1
            ? '<div class="cart-drawer-bill__row"><span>Rate: 1 USD = ' + opts.currencyRate.toFixed(4) + ' ' + opts.currency + '</span></div>'
            : '';
        return renderFreeShippingBar(opts.baseSubtotalUsd, opts.currency, opts.currencyRate) +
            '<div class="cart-drawer-bill" id="cart-popup-bill">' + exchangeHtml +
            '<div class="cart-drawer-bill__row"><span>Subtotal (' + opts.totalItems + ' ' + itemLabel + ')</span>' +
            '<span class="cart-drawer-bill__row-value">' + currencySymbol() + opts.subtotal.toFixed(2) + '</span></div>' +
            '<div class="cart-drawer-bill__row"><span class="cart-drawer-bill__shipping-label">Shipping</span>' +
            '<span class="' + valueClass + '">' + shippingValueText + '</span></div>' +
            '<div class="cart-drawer-bill__total"><span>Total</span>' +
            '<span class="cart-drawer-bill__total-value">' + currencySymbol() + parseFloat(opts.totalPrice).toFixed(2) + '</span></div></div>';
    }

    function renderCartPopup(popup, cartItems, summary, shippingDetails) {
        const totalItems = cartItems.reduce(function (sum, item) { return sum + item.quantity; }, 0);
        const currency = summary.currency || currencyCode();
        const rate = parseFloat(summary.currency_rate || currencyRate() || 1);
        let baseSubtotal = 0;
        if (summary.base_subtotal != null) {
            baseSubtotal = parseFloat(summary.base_subtotal);
        } else if (cartItems.length) {
            baseSubtotal = calculateBaseSubtotalUsd(cartItems, currency, rate);
        }

        const subtotal = cartItems.length
            ? calculateCartSubtotalFromItems(cartItems)
            : parseFloat(summary.converted_subtotal || summary.subtotal || 0);

        let shippingCost = parseFloat(summary.converted_shipping != null ? summary.converted_shipping : (summary.shipping || 0)) || 0;
        if (qualifiesForFreeShipping(baseSubtotal)) {
            shippingCost = 0;
        }
        const totalPrice = subtotal + shippingCost;

        popup.innerHTML =
            '<div class="cart-popup-head"><div class="cart-popup-head__title-wrap">' +
            '<span class="cart-popup-head__icon" aria-hidden="true"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>' +
            '<div><h2 class="cart-popup-head__title">Added to cart</h2><p class="cart-popup-head__sub">' + totalItems + (totalItems === 1 ? ' item' : ' items') + ' in your cart</p></div></div>' +
            '<button type="button" onclick="closeCartPopup()" class="cart-popup-head__close" aria-label="Close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>' +
            '<div class="cart-drawer__scroll"><div class="cart-popup-body"><div class="cart-popup-items" id="cart-popup-items">' + generateCartPopupItems(cartItems) + '</div></div>' +
            '<div class="cart-popup-recs"><h3 class="cart-popup-recs__title">You may also like</h3><div class="cart-popup-recs__grid">' + generateCrossSellProducts() + '</div></div></div>' +
            '<div class="cart-drawer__footer"><div class="cart-popup-summary">' +
            renderCartDrawerBill({
                totalItems: totalItems,
                subtotal: subtotal,
                baseSubtotalUsd: baseSubtotal,
                shippingCost: shippingCost,
                shippingIsFree: qualifiesForFreeShipping(baseSubtotal),
                totalPrice: totalPrice,
                currency: currency,
                currencyRate: rate,
            }) +
            '</div>' + renderCartPromoOfferBlock() +
            '<div class="cart-popup-actions"><div class="cart-popup-actions__buttons">' +
            '<button type="button" onclick="goToCheckoutFromPopup()" class="btn-cta">Checkout</button>' +
            '<button type="button" onclick="closeCartPopup(); window.location.href=\'' + (window.CART_INDEX_URL || '/cart') + '\'" class="btn-outline-petrol">View cart</button>' +
            '</div><button type="button" onclick="closeCartPopup(); window.location.href=\'' + (window.PRODUCTS_INDEX_URL || '/products') + '\'" class="cart-popup-actions__continue">Continue shopping</button></div></div>';

        setTimeout(setupCartPopupEventDelegation, 100);
    }

    function setupCartPopupEventDelegation() {
        const drawer = document.getElementById('cart-drawer');
        if (!drawer) return;
        drawer.removeEventListener('click', handleCartPopupClick);
        drawer.addEventListener('click', handleCartPopupClick);
    }

    function handleCartPopupClick(e) {
        const target = e.target.closest('button');
        if (!target) {
            const rec = e.target.closest('.cross-sell-product');
            if (rec && rec.dataset.productSlug) {
                window.location.href = '/products/' + rec.dataset.productSlug;
            }
            return;
        }
        if (target.classList.contains('remove-cart-item')) {
            e.preventDefault();
            removeCartItemById(parseInt(target.dataset.cartItemId, 10));
            return;
        }
        if (target.classList.contains('decrease-quantity') || target.classList.contains('increase-quantity')) {
            e.preventDefault();
            updateCartItemQuantity(e, parseInt(target.dataset.cartItemId, 10), parseInt(target.dataset.newQuantity, 10));
        }
    }

    function fetchCart() {
        return fetch('/api/cart/get', {
            method: 'GET',
            headers: jsonHeaders(),
            credentials: 'same-origin',
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        });
    }

    function syncLocalStorageWithBackend() {
        fetchCart()
            .then(function (data) {
                if (!data.success || !data.cart_items) return;
                const backendCart = data.cart_items.map(function (item) {
                    const product = item.product || {};
                    const isStudio = !!(item.is_studio_custom || (item.customizations && item.customizations._studio && item.customizations._studio.standalone));
                    return {
                        cart_item_id: item.id,
                        id: item.product_id || item.id,
                        name: item.display_name || (item.customizations && item.customizations._studio && item.customizations._studio.title) || product.name || 'Custom product',
                        price: parseFloat(item.price),
                        image: item.display_image,
                        quantity: item.quantity,
                        selectedVariant: item.selected_variant,
                        customizations: item.customizations,
                        addedAt: Date.now(),
                        isStudio: isStudio,
                    };
                });
                localStorage.setItem('cart', JSON.stringify(backendCart));
                updateCartCount();
            })
            .catch(function () {
                updateCartCount();
            });
    }

    function showCartPopup() {
        closeCartPopup();
        const backdrop = document.createElement('div');
        backdrop.id = 'cart-drawer-backdrop';
        backdrop.className = 'cart-drawer-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');

        const drawer = document.createElement('aside');
        drawer.id = 'cart-drawer';
        drawer.className = 'cart-drawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');
        drawer.setAttribute('aria-label', 'Shopping cart');
        drawer.innerHTML = '<div class="cart-popup-loading"><div class="animate-spin rounded-full h-10 w-10 border-2 border-[#005366] border-t-transparent"></div></div>';

        document.body.appendChild(backdrop);
        document.body.appendChild(drawer);
        document.body.classList.add('cart-drawer-open');
        requestAnimationFrame(function () {
            backdrop.classList.add('is-open');
            drawer.classList.add('is-open');
        });

        fetchCart()
            .then(function (data) {
                const items = data.success ? (data.cart_items || []) : [];
                const summary = Object.assign({}, data.summary || {}, {
                    currency: data.currency,
                    currency_rate: data.currency_rate,
                });
                renderCartPopup(drawer, items, summary, data.shipping_details || null);
            })
            .catch(function (error) {
                drawer.innerHTML = '<div class="cart-popup-head"><div class="cart-popup-head__title-wrap"><div><h2 class="cart-popup-head__title">Unable to load cart</h2></div></div>' +
                    '<button type="button" onclick="closeCartPopup()" class="cart-popup-head__close" aria-label="Close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>' +
                    '<div class="cart-drawer__scroll"><div class="cart-popup-body"><p class="text-[#e2150c] mb-2 font-semibold">Something went wrong. Please try again.</p><p class="text-sm text-gray-500 mb-4">' + error.message + '</p>' +
                    '<button type="button" onclick="closeCartPopup()" class="btn-outline-petrol">Close</button></div></div>';
            });

        backdrop.addEventListener('click', closeCartPopup);
        if (!window._cartDrawerEscapeHandler) {
            window._cartDrawerEscapeHandler = function (e) {
                if (e.key === 'Escape' && document.getElementById('cart-drawer')) closeCartPopup();
            };
            document.addEventListener('keydown', window._cartDrawerEscapeHandler);
        }
    }

    function refreshCartPopupContent() {
        fetchCart()
            .then(function (data) {
                if (!data.success) return;
                const drawer = document.getElementById('cart-drawer');
                if (!drawer) return;
                if (!data.cart_items || !data.cart_items.length) {
                    closeCartPopup();
                    showCartSuccess('Cart is empty');
                    return;
                }
                const summary = Object.assign({}, data.summary || {}, {
                    currency: data.currency,
                    currency_rate: data.currency_rate,
                });
                renderCartPopup(drawer, data.cart_items, summary, data.shipping_details || null);
                updateCartCount();
            })
            .catch(function (error) {
                const el = document.getElementById('cart-popup-items');
                if (el) {
                    el.innerHTML = '<div class="text-center py-4"><p class="text-red-600">Unable to update cart</p><p class="text-sm text-gray-500">' + error.message + '</p></div>';
                }
            });
    }

    function closeCartPopup() {
        const backdrop = document.getElementById('cart-drawer-backdrop');
        const drawer = document.getElementById('cart-drawer');
        if (!drawer) {
            backdrop?.remove();
            document.body.classList.remove('cart-drawer-open');
            return;
        }
        drawer.classList.remove('is-open');
        backdrop?.classList.remove('is-open');
        setTimeout(function () {
            drawer.remove();
            backdrop?.remove();
            document.body.classList.remove('cart-drawer-open');
        }, 280);
    }

    function updateCartItemQuantity(e, cartItemId, newQuantity) {
        if (!cartItemId) return;
        if (newQuantity < 1) {
            removeCartItemById(cartItemId);
            return;
        }
        const quantitySpan = document.getElementById('quantity-' + cartItemId);
        const originalText = quantitySpan ? quantitySpan.textContent : '';
        if (quantitySpan) {
            quantitySpan.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#005366] mx-auto"></div>';
        }
        fetch('/api/cart/update/' + cartItemId, {
            method: 'PUT',
            headers: jsonHeaders(),
            body: JSON.stringify({ quantity: newQuantity }),
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    syncLocalStorageWithBackend();
                    setTimeout(refreshCartPopupContent, 100);
                } else if (quantitySpan) {
                    quantitySpan.textContent = originalText;
                    showAlert({ icon: 'error', title: 'Unable to Update', text: data.message || 'An error occurred while updating quantity', confirmButtonColor: '#005366' });
                }
            })
            .catch(function (error) {
                if (quantitySpan) quantitySpan.textContent = originalText;
                showAlert({ icon: 'error', title: 'Error', text: error.message || 'An error occurred', confirmButtonColor: '#005366' });
            });
    }

    function removeCartItemById(cartItemId) {
        if (!cartItemId) return;
        showAlert({
            icon: 'question',
            title: 'Confirm Removal',
            text: 'Are you sure you want to remove this product from your cart?',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#E2150C',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            fetch('/api/cart/remove/' + cartItemId, {
                method: 'DELETE',
                headers: jsonHeaders(),
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        syncLocalStorageWithBackend();
                        setTimeout(refreshCartPopupContent, 100);
                        showCartSuccess('Product removed from cart');
                    } else {
                        showAlert({ icon: 'error', title: 'Unable to Remove', text: data.message || 'An error occurred while removing the product', confirmButtonColor: '#005366' });
                    }
                })
                .catch(function (error) {
                    showAlert({ icon: 'error', title: 'Error', text: error.message || 'An error occurred', confirmButtonColor: '#005366' });
                });
        });
    }

    function goToCheckoutFromPopup() {
        closeCartPopup();
        window.location.href = window.CART_CHECKOUT_URL || '/checkout';
    }

    window.showCartPopup = showCartPopup;
    window.closeCartPopup = closeCartPopup;
    window.goToCheckoutFromPopup = goToCheckoutFromPopup;
    window.submitCartPromoEmail = submitCartPromoEmail;
    window.handlePostAddToCartPromo = handlePostAddToCartPromo;
    window.showCartSuccess = showCartSuccess;
})();
