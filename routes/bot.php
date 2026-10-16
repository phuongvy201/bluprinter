<?php

use App\Http\Controllers\GrokBotController;
use Illuminate\Support\Facades\Route;

$register = function () {
    Route::get('/', [GrokBotController::class, 'create'])->name('create');
    Route::post('/', [GrokBotController::class, 'store'])->name('store');
    Route::get('/{grokBot}/file', [GrokBotController::class, 'file'])->name('file');
    Route::post('/{grokBot}/redo', [GrokBotController::class, 'redo'])->name('redo');
    Route::get('/{grokBot}', [GrokBotController::class, 'show'])->name('show');
};

$middleware = ['auth', 'role:admin'];
$domain = config('grok.domain');

if (is_string($domain) && $domain !== '') {
    Route::domain($domain)->middleware($middleware)->name('grok-bot.')->group($register);
    Route::middleware($middleware)->prefix('bot')->name('grok-bot.path.')->group($register);
} else {
    Route::middleware($middleware)->prefix('bot')->name('grok-bot.')->group($register);
}
