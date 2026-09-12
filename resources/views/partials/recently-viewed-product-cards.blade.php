@foreach ($products as $product)
    <x-product-card-pdp
        :product="$product"
        size="{{ $variant ?? 'default' }}"
        :show-quick-add="($variant ?? '') !== 'mini'"
    />
@endforeach
