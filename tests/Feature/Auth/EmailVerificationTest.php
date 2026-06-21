<?php

use App\Models\User;
use App\Notifications\VerifyEmailWithCode;
use App\Services\EmailVerificationCodeService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('email verification screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertOk();
    Notification::assertSentTo($user, VerifyEmailWithCode::class);
});

test('email can be verified with a valid code', function () {
    $user = User::factory()->unverified()->create();
    $code = app(EmailVerificationCodeService::class)->issue($user);

    Event::fake();

    $response = $this->actingAs($user)->post(route('verification.verify-code'), [
        'code' => $code,
    ]);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('planner', absolute: false).'?verified=1');
});

test('email is not verified with an invalid code', function () {
    $user = User::factory()->unverified()->create();
    app(EmailVerificationCodeService::class)->issue($user);

    Event::fake();

    $this->actingAs($user)
        ->post(route('verification.verify-code'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email is not verified with an expired code', function () {
    $user = User::factory()->unverified()->create();
    $code = app(EmailVerificationCodeService::class)->issue($user);

    app(EmailVerificationCodeService::class)->verify($user, $code);

    Event::fake();

    $this->actingAs($user)
        ->post(route('verification.verify-code'), ['code' => $code])
        ->assertSessionHasErrors('code');

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('legacy signed verification links redirect to the code screen', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('verification.notice'));
});

test('verified user is redirected to dashboard from verification prompt', function () {
    $user = User::factory()->create();

    Event::fake();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    Event::assertNotDispatched(Verified::class);
    $response->assertRedirect(route('planner', absolute: false));
});

test('already verified user submitting a code is redirected to planner', function () {
    $user = User::factory()->create();
    $code = app(EmailVerificationCodeService::class)->issue($user);

    Event::fake();

    $this->actingAs($user)
        ->post(route('verification.verify-code'), ['code' => $code])
        ->assertRedirect(route('planner', absolute: false));

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
