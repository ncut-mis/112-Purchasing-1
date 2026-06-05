<?php

use App\Models\AgentApplication;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('agent_posts');
    Schema::dropIfExists('agent_applications');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
        $table->string('role')->default('buyer');
        $table->text('avatar')->nullable();
        $table->text('bio')->nullable();
        $table->text('purchasable_countries')->nullable();
    });

    Schema::create('agent_applications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('country');
        $table->string('phone');
        $table->string('id_number');
        $table->string('id_image_front');
        $table->string('id_image_back');
        $table->string('status')->default('pending');
        $table->text('admin_remark')->nullable();
        $table->timestamps();
        $table->string('main_region')->nullable();
        $table->text('experience')->nullable();
    });

    Schema::create('agent_posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('status')->default('open');
        $table->timestamps();
    });
});

function createApprovedShopStoreAgent(array $userAttributes = []): User
{
    $user = User::create(array_merge([
        'name' => '丁丁代購',
        'email' => 'agent-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'bio' => '日本藥妝與零食代購',
        'purchasable_countries' => ['日本'],
    ], $userAttributes));

    AgentApplication::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'country' => '日本',
        'phone' => '0912345678',
        'id_number' => 'A123456789',
        'id_image_front' => 'front.jpg',
        'id_image_back' => 'back.jpg',
        'status' => 'approved',
    ]);

    return $user;
}

test('store search matches approved agents by user name without querying missing nickname column', function () {
    createApprovedShopStoreAgent();

    $response = $this->get(route('store', ['search' => '丁']));

    $response->assertOk();
    $response->assertSee('丁丁代購');
});

test('store search matches approved agents by bio without querying missing nickname column', function () {
    createApprovedShopStoreAgent([
        'name' => '小林代購',
        'email' => 'kobayashi@example.com',
        'bio' => '專門代買東京限定甜點',
    ]);

    $response = $this->get(route('store', ['search' => '甜點']));

    $response->assertOk();
    $response->assertSee('小林代購');
});

test('store country filter matches agents from array cast countries', function () {
    createApprovedShopStoreAgent([
        'name' => '日本代購',
        'email' => 'japan-agent@example.com',
        'purchasable_countries' => ['日本'],
    ]);
    createApprovedShopStoreAgent([
        'name' => '美國代購',
        'email' => 'us-agent@example.com',
        'purchasable_countries' => ['美國'],
    ]);

    $response = $this->get(route('store', ['country' => '日本']));

    $response->assertOk();
    $response->assertSee('日本代購');
    $response->assertDontSee('美國代購');
});

test('store country filter matches agents from double encoded legacy countries', function () {
    $agent = createApprovedShopStoreAgent([
        'name' => '英國代購',
        'email' => 'uk-agent@example.com',
        'purchasable_countries' => [],
    ]);
    $agent->forceFill(['purchasable_countries' => json_encode(['英國'])])->save();

    $response = $this->get(route('store', ['country' => '英國']));

    $response->assertOk();
    $response->assertSee('英國代購');
});

test('store country filter keeps legacy europe option compatible with uk agents', function () {
    createApprovedShopStoreAgent([
        'name' => '歐洲選單相容代購',
        'email' => 'legacy-europe-agent@example.com',
        'purchasable_countries' => ['英國'],
    ]);

    $response = $this->get(route('store', ['country' => '歐洲']));

    $response->assertOk();
    $response->assertSee('歐洲選單相容代購');
});
