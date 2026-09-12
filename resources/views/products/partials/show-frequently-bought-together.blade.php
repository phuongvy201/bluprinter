@include('products.partials.show-fbt-bundle', [
    'product' => $product ?? null,
    'fbtProducts' => $fbtProducts ?? collect(),
])
