<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;

class TasksController extends Controller
{
    // getでtasks/にアクセスされた場合の「一覧表示処理」
    public function index()
    {
        if (\Auth::check()) {
          $user = \Auth::user();
            // ユーザーの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザーの投稿も取得するように変更しますが、現時点ではこのユーザーの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
        
        return view('tasks.index', [
            'tasks' => $tasks,
            ]);
        }
        else {
            return view('dashboard');
        }
        
    }

    // getでtasks/createにアクセスされた場合の「新規登録画面表示処理」
    public function create()
    {
        $task = new Task;

        // タスク作成ビューを表示
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    // postでtasks/にアクセスされた場合の「新規登録処理」
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:10',
        ]);
        
        // 認証済みユーザー（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'status' => $request->status,
            'content' => $request->content,
        ]);
        
        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // getでtasks/（任意のid）にアクセスされた場合の「取得表示処理」
    public function show(string $id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

    
        // // タスク詳細ビューでそれを表示
        // return view('tasks.show', [
        //     'task' => $task,
        // ]);
        
        // 認証済みユーザー（閲覧者）がその投稿の所有者である場合は投稿を表示
        if (\Auth::id() === $task->user_id) {
            return view('tasks.show', [
                'task' => $task,
            ]);
        }
        
        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // getでtasks/（任意のid）/editにアクセスされた場合の「更新画面表示処理」
    public function edit(string $id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        
        // 認証済みユーザー（閲覧者）がその投稿の所有者である場合は投稿を編集
        if (\Auth::id() === $task->user_id) {
            return view('tasks.edit', [
            'task' => $task,
            ]);
        }
        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // putまたはpatchでtasks/（任意のid）にアクセスされた場合の「更新処理」
    public function update(Request $request, string $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:10',
        ]);
        
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザー（閲覧者）の投稿として更新（リクエストされた値をもとに作成）
        if (\Auth::id() === $task->user_id) {
        $request->user()->tasks()->update([
            'status' => $request->status,
            'content' => $request->content,
        ]);
    }
        
        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // deleteでtasks/（任意のid）にアクセスされた場合の「削除処理」
    public function destroy(string $id)
    {
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザー（閲覧者）がその投稿の所有者である場合は投稿を削除
    if (\Auth::id() === $task->user_id) {
        $task->delete();
    }

        // トップページへリダイレクトさせる
        return redirect('/');
    }
}