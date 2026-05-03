<?php
use App\Models\User;
use Illuminate\Auth\Events\Registered;

$user = User::factory()->create();
echo 'User created: ' . $user->email . "\n";

try {
    event(new Registered($user));
    echo 'Event fired.' . "\n";
} catch (\Throwable $e) {
    echo 'CRASH DETECTED: ' . $e->getMessage() . "\n";
    exit(1);
}

echo 'No crash occurred!' . "\n";
