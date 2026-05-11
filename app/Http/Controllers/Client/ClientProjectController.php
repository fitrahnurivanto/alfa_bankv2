<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectChat;
use Illuminate\Http\Request;

class ClientProjectController extends Controller
{
    /**
     * Display a listing of client's projects.
     */
    public function index(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Make sure user has client profile
        if (!$user->client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Akun Client Belum Terhubung');
        }
        
        // Get all projects from client's orders
        $projects = Project::whereHas('order', function($q) use ($user) {
                $q->where('client_id', $user->client->id);
            })
            ->with(['order', 'teams.members.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)->appends($request->query());

        return view('client.projects.index', compact('projects'));
    }

    /**
     * Display the specified project with chat.
     */
    public function show(Project $project)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Verify this project belongs to the client
        if ($project->order->client_id !== $user->client->id) {
            abort(403, 'Anda tidak memiliki akses ke project ini');
        }

        $project->load([
            'order.items.service',
            'teams.members.user',
            'tasks',
            'milestones'
        ]);

        // Get project chats
        $chats = $project->chats()
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        // Calculate project statistics
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'team_members' => $project->teams->sum(fn($team) => $team->members->count()),
            'progress' => $project->tasks->count() > 0 
                ? round(($project->tasks->where('status', 'completed')->count() / $project->tasks->count()) * 100) 
                : 0,
        ];

        return view('client.projects.show', compact('project', 'chats', 'stats'));
    }

    /**
     * Store a new chat message.
     */
    public function storeChat(Request $request, Project $project)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Verify this project belongs to the client
        if ($project->order->client_id !== $user->client->id) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = ProjectChat::create([
            'project_id' => $project->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'message' => $request->message,
        ]);

        $chat->load('user');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'chat' => $chat,
            ]);
        }

        return redirect()->route('client.projects.show', $project)
            ->with('success', 'Pesan terkirim');
    }

    /**
     * Get chat messages via AJAX.
     */
    public function getChats(Project $project)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Verify this project belongs to the client
        if ($project->order->client_id !== $user->client->id) {
            abort(403);
        }

        $chats = $project->chats()
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($chats);
    }

    /**
     * Delete a chat message (admin or message owner only).
     */
    public function deleteChat(Project $project, \App\Models\ProjectChat $chat)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Verify this project belongs to the client
        if ($project->order->client_id !== $user->client->id) {
            abort(403);
        }
        
        // Check if chat belongs to this project
        if ($chat->project_id !== $project->id) {
            abort(404);
        }
        
        // Only message owner can delete (clients can only delete their own messages)
        if ($chat->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat menghapus pesan Anda sendiri');
        }

        $chat->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dihapus']);
        }

        return back()->with('success', 'Pesan berhasil dihapus');
    }
}
