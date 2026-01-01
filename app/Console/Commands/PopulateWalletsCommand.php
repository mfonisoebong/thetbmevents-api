<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PopulateWalletsCommand extends Command
{
    protected $signature = 'populate:wallets';


    public function handle(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->wallet()->create();
        }
        $this->info('Wallets populated successfully');
    }
}
