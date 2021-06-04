<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Laravue\Models\Servers;
use GuzzleHttp\Client;
class canMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'can:messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This update servers table with data which has been taken from other web servers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client();
        $res = $client->request("GET", 'https://api.telegram.org/bot1885406065:AAFMzdtSiMwOCk53D0HZN0BtRr5UHl3PBas/setChatPermissions?chat_id=-1001318719480&permissions={"can_send_messages":true,"can_send_media_messages":true}', [
            "proxy" => "http://username:password@10.12.16.202:8081",
        ]);
        $message = new Client();
        $result = $message->request("GET", 'https://api.telegram.org/bot1885406065:AAFMzdtSiMwOCk53D0HZN0BtRr5UHl3PBas/sendMessage?chat_id=-1001318719480&text=!!! Разрешение на отправку сообщений в эту группу действует до 9:00. Всем удачи. !!!', [
            "proxy" => "http://username:password@10.12.16.202:8081",
        ]);
        \Log::info("Send message has been True");
        $this->info('Send message has been True');
        return 0;
    }
}
