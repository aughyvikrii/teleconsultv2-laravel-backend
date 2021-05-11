<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Outbox;
use App\Libraries\Whatsapp;

class SendWhatsapp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:send_wa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim Outbox Whatsapp Pending';

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
        $outboxes = Outbox::where('status', 'pending')->get();

        if($outboxes->count() <= 0) {
            $this->info("Tidak ada outbox pending");
            return 0;
        }
        
        $whatsapp = new Whatsapp;
        $sent = 0;
        foreach($outboxes as $outbox) {
            $send = $whatsapp->sendMessage($outbox->destination, $outbox->message);
            $outbox->update([
                'status' => 'sent',
                'last_update' => date('Y-m-d H:i:s')
            ]);
            $sent++;
        }

        $this->info("{$sent} telah terkirim");

        return 0;
    }
}
