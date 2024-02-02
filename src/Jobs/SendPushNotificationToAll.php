<?php
namespace Vtlabs\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Vtlabs\Core\Models\PushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vtlabs\Core\Helpers\PushNotificationHelper;

class SendPushNotificationToAll implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pushNotification;

    /**
     * Create a new job instance.
     *
     * @param  PushNotification  $pushNotification
     * @return void
     */
    public function __construct(PushNotification $pushNotification)
    {
        $this->pushNotification = $pushNotification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $oneSignal = PushNotificationHelper::getOneSignalInstance($this->pushNotification->role);
            $oneSignal->sendNotificationToAll($this->pushNotification->title);
        } catch (\Exception $ex) {
            Log::error('Push notification to all: Error occurred: ' . $ex->getMessage());
        }
    }
}
