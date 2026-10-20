<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class CatatLoginTerakhir
{
    public function handle(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
    }
}