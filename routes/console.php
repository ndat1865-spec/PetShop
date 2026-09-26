<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pawora:seed-if-empty', function () {
    if (User::query()->exists()) {
        $this->info('Database đã có dữ liệu, bỏ qua seed.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
})->purpose('Seed dữ liệu demo lần đầu khi database còn trống (dùng khi deploy)');
