<?php

namespace BotMan\Drivers\WhatsApp\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppRegisterCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'botman:telegram:register {--remove} {--output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register or unregister your bot with WhatsApp\'s webhook';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $url = 'https://api.telegram.org/bot'
                .config('botman.telegram.token')
                .'/setWebhook';

        $remove = $this->option('remove', null);

        if (! $remove) {
            $url .= '?url='.$this->ask('What is the target url for the telegram bot?');
        }

        $this->info('Using '.$url);

        $this->info('Pinging WhatsApp...');

        $output = json_decode(file_get_contents($url));

        if ($output->ok == true && $output->result == true) {
            $this->info(
                $remove
                ? 'Your bot WhatsApp\'s webhook has been removed!'
                : 'Your bot is now set up with WhatsApp\'s webhook!'
            );
        }

        if ($this->option('output')) {
            dump($output);
        }
    }
}
