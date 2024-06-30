<?php

namespace App\Console\Commands;

use App\Models\PasswordResetToken;
use Illuminate\Console\Command;

class ClearPasswordResets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-password-resets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired password reset tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTokens= PasswordResetToken::where('created_at', '>=', now()->subMinutes(30))
        ->select('id')
        ->get();

        $expiredTokens
        ->each
        ->delete();

    }
}
