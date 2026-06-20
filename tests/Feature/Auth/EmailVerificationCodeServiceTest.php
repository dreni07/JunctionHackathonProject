<?php

use App\Models\User;
use App\Services\EmailVerificationCodeService;
use Illuminate\Support\Facades\Cache;

test('issue stores a six digit code that can be verified once', function () {
    $user = User::factory()->unverified()->create();
    $service = app(EmailVerificationCodeService::class);

    $code = $service->issue($user);

    expect($code)->toMatch('/^\d{6}$/');
    expect($service->hasActiveCode($user))->toBeTrue();
    expect($service->verify($user, $code))->toBeTrue();
    expect($service->hasActiveCode($user))->toBeFalse();
    expect($service->verify($user, $code))->toBeFalse();
});

test('issue replaces an existing active code', function () {
    $user = User::factory()->unverified()->create();
    $service = app(EmailVerificationCodeService::class);

    $firstCode = $service->issue($user);
    $secondCode = $service->issue($user);

    expect($service->verify($user, $firstCode))->toBeFalse();
    expect($service->verify($user, $secondCode))->toBeTrue();
});

test('codes expire after the configured window', function () {
    $user = User::factory()->unverified()->create();
    $service = app(EmailVerificationCodeService::class);
    $code = $service->issue($user);

    Cache::forget('email-verification-code:'.$user->getKey());

    expect($service->verify($user, $code))->toBeFalse();
});
