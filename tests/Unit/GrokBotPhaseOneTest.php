<?php

// Không dùng RefreshDatabase: một số migration sẵn có chỉ chạy trên MySQL.

use App\Models\GrokBotJob;
use App\Models\User;
use App\Services\Grok\PngDpi;
use App\Services\Grok\PrintFileAgent;
use App\Services\Grok\PrintSpec;
use App\Services\Grok\XaiClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);

beforeEach(function () {
    config([
        'services.xai.api_key' => 'test-not-a-real-key',
        'services.xai.base_url' => 'https://api.x.ai/v1',
        'services.xai.model' => 'grok-4.7',
    ]);

    Schema::dropAllTables();
    $users = require database_path('migrations/0001_01_01_000000_create_users_table.php');
    $users->up();
    $permissions = require database_path('migrations/2025_10_06_080741_create_permission_tables.php');
    $permissions->up();
    $jobs = require database_path('migrations/2026_10_16_090000_create_grok_bot_jobs_table.php');
    $jobs->up();

    Schema::create('domain_currency_configs', function (Blueprint $table) {
        $table->id();
        $table->string('domain')->nullable();
        $table->string('currency', 3)->nullable();
        $table->decimal('currency_rate', 10, 6)->default(1);
        $table->boolean('is_active')->default(true);
    });
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('status')->nullable();
    });
    Schema::create('return_requests', function (Blueprint $table) {
        $table->id();
        $table->string('status')->nullable();
        $table->timestamp('updated_at')->nullable();
    });

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    Storage::fake('local');
    Http::preventStrayRequests();
    $this->withoutVite();
});

function grokAdmin(): User
{
    $user = User::factory()->create();
    Role::findOrCreate('admin');
    $user->assignRole('admin');

    return $user;
}

function grokSourceUpload(): UploadedFile
{
    $image = imagecreatetruecolor(6, 4);
    $color = imagecolorallocate($image, 20, 90, 180);
    imagefilledrectangle($image, 0, 0, 5, 3, $color);
    ob_start();
    imagepng($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return UploadedFile::fake()->createWithContent('nguon.png', $bytes);
}

function fakeGrokReply(): void
{
    Http::fake([
        'https://api.x.ai/v1/chat/completions' => Http::response([
            'model' => 'grok-4.7',
            'choices' => [[
                'message' => ['content' => '{"message":"Font khớp 99%."}'],
            ]],
        ], 200),
    ]);
}

test('trang nội bộ hiện mặc định 4500 x 5400 và 300 dpi', function () {
    expect(parse_url(route('grok-bot.create'), PHP_URL_PATH))->toBe('/bot');

    $this->actingAs(grokAdmin())
        ->get('/bot')
        ->assertOk()
        ->assertSee('Bot file in POD')
        ->assertSee('value="4500"', false)
        ->assertSee('value="5400"', false)
        ->assertSee('value="300"', false)
        ->assertDontSee('Admin Panel')
        ->assertDontSee('Creator Studio');
});

test('thiếu pixel hoặc dpi thì không gọi grok và không gọi agent file in', function (array $spec) {
    $print = Mockery::mock(PrintFileAgent::class);
    $print->shouldNotReceive('run');
    $this->app->instance(PrintFileAgent::class, $print);
    Http::fake();

    $this->actingAs(grokAdmin())
        ->post(route('grok-bot.store'), array_merge([
            'source' => grokSourceUpload(),
            'request_type' => 'clone',
            'instruction' => 'clone ngực áo',
        ], $spec))
        ->assertRedirect()
        ->assertSessionHasErrors('spec');

    Http::assertNothingSent();
    expect(GrokBotJob::count())->toBe(0);
})->with([
    'thiếu rộng' => [['height' => 5400, 'dpi' => 300]],
    'thiếu dpi' => [['width' => 4500, 'height' => 5400]],
    'design thiếu dpi' => [['request_type' => 'design', 'width' => 4500, 'height' => 5400]],
]);

test('design và redesign không xuất file in', function (string $type) {
    fakeGrokReply();
    $print = Mockery::mock(PrintFileAgent::class);
    $print->shouldNotReceive('run');
    $this->app->instance(PrintFileAgent::class, $print);

    $response = $this->actingAs(grokAdmin())
        ->post(route('grok-bot.store'), [
            'source' => grokSourceUpload(),
            'request_type' => $type,
            'width' => 4500,
            'height' => 5400,
            'dpi' => 300,
        ]);

    $job = GrokBotJob::first();
    expect($job)->not->toBeNull()
        ->and($job->outcome)->toBe('phase_2')
        ->and($job->print_path)->toBeNull()
        ->and($job->coordinator_model)->toBe('grok-4.7');

    $response->assertRedirect(route('grok-bot.show', $job));
    $this->get(route('grok-bot.show', $job))
        ->assertOk()
        ->assertSee('giai đoạn 2')
        ->assertDontSee('Tải PNG');

    Http::assertSentCount(1);
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.ai/v1/chat/completions'
            && $request['model'] === 'grok-4.7'
            && ! str_contains($request->url(), 'images');
    });
})->with(['design', 'redesign']);

test('clone xuất png đúng size và dpi, checklist không chấm font đạt', function () {
    fakeGrokReply();

    $response = $this->actingAs(grokAdmin())
        ->post(route('grok-bot.store'), [
            'source' => grokSourceUpload(),
            'request_type' => 'clone',
            'width' => 24,
            'height' => 36,
            'dpi' => 300,
        ]);

    $job = GrokBotJob::first();
    $response->assertRedirect(route('grok-bot.show', $job));

    $checklist = collect($job->checklist)->keyBy('key');
    expect($job->outcome)->toBe('clone')
        ->and($job->coordinator_model)->toBe('grok-4.7')
        ->and($job->print_model)->toBe('grok-4.7')
        ->and($checklist['size']['passed'])->toBeTrue()
        ->and($checklist['dpi']['passed'])->toBeTrue()
        ->and($checklist['image']['passed'])->toBeTrue()
        ->and($checklist['font']['passed'])->toBeFalse()
        ->and($checklist['position']['passed'])->toBeFalse()
        ->and($checklist['art']['passed'])->toBeFalse();

    $download = $this->get(route('grok-bot.file', $job));
    $download->assertOk();
    $download->assertHeader('content-type', 'image/png');
    $info = PngDpi::inspect($download->getContent());
    expect($info['width'])->toBe(24)
        ->and($info['height'])->toBe(36)
        ->and($info['dpi'])->toBe(300);

    $this->get(route('grok-bot.show', $job))
        ->assertOk()
        ->assertSee('Chưa kiểm được')
        ->assertDontSee('99%');

    Http::assertSentCount(2);
});

test('agent file in từ chối spec thiếu và không gọi grok', function () {
    Http::fake();
    $agent = app(PrintFileAgent::class);

    expect(fn () => $agent->run(new PrintSpec('clone', 100, null, 300, 'bytes')))
        ->toThrow(LogicException::class);

    Http::assertNothingSent();
});

test('model bị từ chối thì dùng model grok mà api đang trả, không gắn tên cũ', function () {
    Http::fake([
        'https://api.x.ai/v1/chat/completions' => Http::sequence()
            ->push(['error' => 'model grok-4.7 not found'], 400)
            ->push([
                'model' => 'grok-9.1',
                'choices' => [[
                    'message' => ['content' => 'Đã nhận.'],
                ]],
            ], 200),
        'https://api.x.ai/v1/models' => Http::response([
            'data' => [
                ['id' => 'grok-2', 'created' => 10],
                ['id' => 'grok-imagine-image', 'created' => 9999],
                ['id' => 'grok-9.1', 'created' => 500],
            ],
        ], 200),
    ]);

    $reply = app(XaiClient::class)->complete('hệ thống', 'người dùng');

    expect($reply->model)->toBe('grok-9.1');
    Http::assertSent(function ($request) {
        return str_ends_with($request->url(), '/chat/completions')
            && $request['model'] === 'grok-9.1';
    });
});

test('lỗi xai hiện status và message thật, không xuất file và không lộ khóa', function () {
    Http::fake([
        'https://api.x.ai/v1/chat/completions' => Http::response([
            'code' => 'permission-denied',
            'error' => 'Your newly created team does not have credits. See https://console.x.ai/team/secret and key xai-THISISFAKE',
        ], 403),
    ]);

    $this->actingAs(grokAdmin())
        ->post(route('grok-bot.store'), [
            'source' => grokSourceUpload(),
            'request_type' => 'clone',
            'width' => 20,
            'height' => 20,
            'dpi' => 300,
        ])
        ->assertRedirect(route('grok-bot.create'));

    $this->get(route('grok-bot.create'))
        ->assertSee('HTTP 403')
        ->assertSee('does not have credits')
        ->assertDontSee('xai-THISISFAKE')
        ->assertDontSee('console.x.ai');

    expect(GrokBotJob::count())->toBe(0);
});

test('khách và seller không vào trang bot', function () {
    $this->get(route('grok-bot.create'))
        ->assertRedirect(route('login'));

    $seller = User::factory()->create();
    Role::findOrCreate('seller');
    $seller->assignRole('seller');

    $this->actingAs($seller)
        ->get(route('grok-bot.create'))
        ->assertForbidden();
});

test('gửi làm lại vẫn không đánh font là đạt', function () {
    fakeGrokReply();
    $admin = grokAdmin();

    $this->actingAs($admin)->post(route('grok-bot.store'), [
        'source' => grokSourceUpload(),
        'request_type' => 'clone',
        'width' => 16,
        'height' => 16,
        'dpi' => 150,
    ]);

    $job = GrokBotJob::first();

    $this->actingAs($admin)->post(route('grok-bot.redo', $job), [
        'items' => ['font', 'art'],
        'note' => 'chữ bị lệch',
    ])->assertRedirect(route('grok-bot.show', $job));

    $job->refresh();
    $checklist = collect($job->checklist)->keyBy('key');
    $info = PngDpi::inspect(Storage::disk('local')->get($job->print_path));

    expect($info['width'])->toBe(16)
        ->and($info['height'])->toBe(16)
        ->and($info['dpi'])->toBe(150)
        ->and($checklist['font']['passed'])->toBeFalse()
        ->and($checklist['art']['passed'])->toBeFalse()
        ->and($job->redo_note)->toContain('font');

    Http::assertSentCount(4);
});
