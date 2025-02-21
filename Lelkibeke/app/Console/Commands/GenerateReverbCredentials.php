<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateReverbCredentials extends Command
{
    protected $signature = 'reverb:generate';
    protected $description = 'Generate fresh Reverb credentials';

    public function handle()
    {
        $this->info('Generating new Reverb credentials...');
        
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        $credentials = [
            'REVERB_APP_ID' => rand(100000, 999999),
            'REVERB_APP_KEY' => 'ws_'.Str::random(32),
            'REVERB_APP_SECRET' => Str::random(64),
        ];
        
        foreach ($credentials as $key => $value) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        }
        
        file_put_contents($envPath, $envContent);
        
        $this->info('✅ New credentials generated:');
        $this->table(array_keys($credentials), [array_values($credentials)]);
    }
}