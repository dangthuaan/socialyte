<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Models\Comment;
use App\Services\ActivityService;
use App\Services\NotificationService;

class CommentService
{
    protected $activityService;

    public function __construct(ActivityService $activityService, NotificationService $notificationService)
    {
        $this->activityService = $activityService;
        $this->notificationService = $notificationService;
    }

    /**
     * Store Comment in database.
     *
     * @param Array $data['user_id', 'content', 'post_id']
     * @return Boolean | App\Models\Comment
     */
    public function storeComment($data)
    {
        $post = Post::findOrFail($data['post_id']);

        $activityData = [
            'user_id' => $data['user_id'],
            'post_id' => $data['post_id'],
        ];

        $activityData['type'] = config('activity.type.comment');

        $notificationData = [
            'sender_id' => $data['user_id'],
            'receiver_id' => $post->user->id,
            'post_id' => $data['post_id'],
        ];

        try {
            $comment = Comment::create($data);

            if (is_null($comment['parent_id'])) {
                $notificationData['type'] = config('notification.type.comment');
            } else {
                $parentComment = Comment::findOrFail($comment['parent_id']);

                if (is_null($parentComment->parent_id)) {
                    $notificationData['type'] = config('notification.type.reply');
                } else {
                    $notificationData['type'] = config('notification.type.replies_of_reply');
                }

                $notificationData['receiver_id'] = $parentComment->user_id;
            }

            $this->notificationService->storeNotification($notificationData);
            $this->activityService->storeActivity($activityData);
        } catch (\Throwable $th) {
            Log::error($th);

            return false;
        }

        return $comment;
    }

    /**
     * Update comment in database.
     *
     * @param Int $id
     * @param Array $data['user_id', 'content']
     * @return Boolean
     */
    public function updateComment($id, $data)
    {
        $comment = Comment::findOrFail($id);

        try {
            $comment->update($data);
        } catch (\Throwable $th) {
            Log::error($th);

            return false;
        }

        return $comment;
    }

    /**
     * Delete comment.
     *
     * @param  Int $id
     * @return Boolean
     */
    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);

        try {
            $comment->delete();
        } catch (\Throwable $th) {
            Log::error($th);

            return false;
        }

        return true;
    }
}
