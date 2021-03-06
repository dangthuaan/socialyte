<?php

namespace App\Http\Controllers;

use App\Services\FriendService;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\ActivityService;
use App\Models\User;


class HomeController extends Controller
{
    protected $userService;
    protected $postService;
    protected $activityService;
    protected $friendService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserService $userService,
        PostService $postService,
        FriendService $friendService,
        ActivityService $activityService
    ) {
        $this->userService = $userService;
        $this->postService = $postService;
        $this->friendService = $friendService;
        $this->activityService = $activityService;
    }

    /**
     * Show today birthdays.
     *
     * @return \Illuminate\Http\Response
     */
    public function showTodayBirthdays()
    {
        $todayBirthdayUsers = $this->friendService->getListFriendBirthdays(auth()->user(), now());

        return view('pages.birthdays.index', compact('todayBirthdayUsers'));
    }

    /**
     * Show the application dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $posts = $this->postService->getListPosts(auth()->user());

        $suggestUsers = $this->userService->getListNotFriend(
            auth()->user(),
            config('user.suggestion_friend')
        );

        $todayBirthdayUsers = $this->friendService->getListFriendBirthdays(auth()->user(), now());

        $activities = $this->activityService->getListActivities(auth()->user());

        if ($todayBirthdayUsers->count() > 0) {
            $randomTodayBirthdayUser = $todayBirthdayUsers->random(1)->first();
        }

        if ($request->ajax()) {
            $nextPosts = view('pages.blocks.post', compact('posts'))->render();

            return response()->json([
                'html' => $nextPosts
            ]);
        }

        return view('pages.newsfeed.index', compact(
            'posts',
            'suggestUsers',
            'todayBirthdayUsers',
            'randomTodayBirthdayUser',
            'activities'
        ));
    }

    /**
     * Clear register cache and remove created sessions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function clearViewCache(Request $request)
    {
        $registerId = $request->session()->get('userId');

        $request->session()->flush();

        $forceDeleteUser = $this->userService->forceDeleteUser($registerId);

        if ($forceDeleteUser) {
            return redirect('/register');
        }

        return back()->with('error', __('user.error'));
    }
}
