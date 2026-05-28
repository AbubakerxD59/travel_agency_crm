<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateWebPushVapidKeys extends Command
{
    protected $signature = 'webpush:generate-vapid-keys';

    protected $description = 'Generate VAPID public/private keys for Web Push notifications';

    public function handle(): int
    {
        try {
            $keys = VAPID::createVapidKeys();
        } catch (\Throwable $exception) {
            $this->error('Could not generate VAPID keys with OpenSSL: '.$exception->getMessage());
            $this->newLine();
            $this->line('Try instead: npx web-push generate-vapid-keys');
            $this->line('Then copy the Public Key and Private Key into your .env file.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Add these lines to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:your-email@example.com');
        $this->newLine();

        return self::SUCCESS;
    }
}
