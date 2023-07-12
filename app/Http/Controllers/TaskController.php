<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        // Logic to store the task in the database
        $task = Task::create($request->all());

        // If the task is marked as urgent, store it in Redis
        if ($task->urgent) {
            Redis::rpush('urgent_tasks', $task);
        }

        // Additional logic as per your requirements

        return response()->json($task, 201);
    }

    public function task($userId)
    {
        $tasks = Redis::lrange('urgent_tasks', 0, 9);

        // Assign Redis tasks to the user identified by $userId
        $user = User::find($userId);
        $user->tasks()->attach($tasks);

        // Once the tasks are assigned, remove them from Redis
        Redis::ltrim('urgent_tasks', 10, -1);

        return response()->json($tasks);
    }
}
