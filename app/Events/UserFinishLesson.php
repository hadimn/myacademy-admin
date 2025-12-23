<?php
namespace App\Events;

use App\Models\CoursesModel;
use App\Models\LessonsModel;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFinishLesson implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public array $user;
    public array $lesson;
    public array $course;
    public string $message;

    /**
     * Create a new event instance.
     * This event should return notification when user completes a lesson
     */
    public function __construct(User $user, LessonsModel $lesson, CoursesModel $course)
    {
        $this->user = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        $this->lesson = [
            'id' => $lesson->lesson_id,
            'title' => $lesson->title,
        ];

        $this->course = [
            'id' => $course->course_id,
            'title' => $course->title,
        ];

        // ✅ Create a single message string
        $this->message = "🎉 {$user->name} has completed the lesson '{$lesson->title}' in course '{$course->title}'!";
    }

    public function broadcastAs(): string
    {
        return 'user.done.lesson';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-notifications');
    }
}
