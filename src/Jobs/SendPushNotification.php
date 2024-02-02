<?php
namespace Vtlabs\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Vtlabs\Core\Models\PushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vtlabs\Core\Helpers\PushNotificationHelper;

class SendPushNotification implements ShouldQueue 
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
        // get the current locale in temp
        $locale = App::getLocale();

        try {
            // set the user preffered locale for translation purpose
            App::setLocale($this->pushNotification->locale);

            $oneSignal = PushNotificationHelper::getOneSignalInstance($this->pushNotification->role);
            $oneSignal->sendNotificationToUser(
                $this->pushNotification->title,
                $this->pushNotification->notificationId,
                null,
                array_merge(["title" => $this->pushNotification->title, "body" => $this->pushNotification->body], $this->pushNotification->data)
            );
        } catch (\Exception $ex) {
            Log::error('Push notification: Error occurred', [
                'notificationId' => $this->pushNotification->notificationId,
                'role' => $this->pushNotification->role,
                'message' => $ex->getMessage()
            ]);
        }

        // reset the locale
        App::setLocale($locale);
    }
}
