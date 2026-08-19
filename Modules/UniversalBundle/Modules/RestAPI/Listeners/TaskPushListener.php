<?php

namespace Modules\RestAPI\Listeners;

use App\Events\TaskEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use App\Models\User;

class TaskPushListener extends BasePushNotification
{
    public function handle(TaskEvent $event)
    {
        $task = $event->task;
        $role = '';
        // NewClientTask, NewTask, TaskUpdated, TaskCompleted, TaskUpdatedClient
        if ($event->notificationName !== 'NewClientTask'
            && $event->notificationName !== 'TaskUpdatedClient'
            && $event->notificationName != 'TaskCompletedClient') {
            $title = ucwords(Str::snake($event->notificationName, ' '));

            if ($event->notifyUser instanceof Collection) {
                $userIds = $event->notifyUser->pluck('id')->toArray();
                $validUsers = User::whereIn('id', $userIds)->get();

                foreach ($validUsers as $user) {
                    $this->taskNotificationSend($user, $task, $title);
                }
            } else {
                $validUser = User::find($event->notifyUser->id);
                if ($validUser) {
                    $this->taskNotificationSend($validUser, $task, $title);
                }
            }
        }
    }

    private function taskNotificationSend($user, $task, $title)
    {
        $role = $this->getUserRole($user);
        $message = $this->message($task, $title, $role);
        $this->sendNotification($user, $message);
    }

    private function message($task, $title, $role)
    {
        $type = Str::slug($title, '-');

        return [
            'apn' => [
                'notification' => [
                    'title' => $title.' #'.$task->id,
                    'body' => $task->heading.($task->project ? ' - Project:'.$task->project->project_name : ''),
                    'sound' => 'default',
                    'badge' => 1,
                    'id' => $task->id,
                    'type' => 'task',
                    'role' => $role,
                ],
            ],
            'fcm' => [
                'data' => [
                    'title' => $title.' #'.$task->id,
                    'body' => $task->heading.($task->project ? ' - Project:'.$task->project->project_name : ''),
                    'sound' => 'default',
                    'badge' => 1,
                    'id' => $task->id,
                    'type' => 'task',
                    'role' => $role,
                ],
            ],
        ];
    }
}
