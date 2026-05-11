<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $user = $this->adminUser();

        if (!$user->isAdmin()) {
            abort(403);
        }
        
        $query = Client::with(['orders.items.service.category', 'projects']);

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('projects', function($q) {
                    $q->whereIn('status', ['pending', 'in_progress']);
                });
            } elseif ($request->status === 'past') {
                $query->whereDoesntHave('projects', function($q) {
                    $q->whereIn('status', ['pending', 'in_progress']);
                })->whereHas('orders');
            }
        }

        $clients = $query->latest()->paginate(15)->appends($request->query());

        // Calculate stats for each client
        foreach ($clients as $client) {
            $client->total_orders = $client->orders->count();
            $client->total_projects = $client->projects->count();
            $client->total_revenue = $client->orders->sum('total_amount');
            $client->has_active_project = $client->projects()->whereIn('status', ['pending', 'in_progress'])->exists();
        }

        return view('admin.clients.index', compact('clients', 'user'));
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        if (!$this->adminUser()->isAdmin()) {
            abort(403);
        }

        $client->load(['user', 'orders.items.service', 'projects']);

        // Calculate stats
        $stats = [
            'total_orders' => $client->orders->count(),
            'total_projects' => $client->projects->count(),
            'total_revenue' => $client->orders->sum('total_amount'),
            'active_projects' => $client->projects()->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_projects' => $client->projects()->where('status', 'completed')->count(),
        ];

        return view('admin.clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        if (!$this->adminUser()->isAdmin()) {
            abort(403);
        }

        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client)
    {
        if (!$this->adminUser()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Data client berhasil diupdate.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        if (!$this->adminUser()->isAdmin()) {
            abort(403);
        }

        // Check if client has any active/ongoing projects
        $activeProjects = $client->projects()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        if ($activeProjects > 0) {
            return redirect()->route('admin.clients.index')
                ->with('error', 'Tidak dapat menghapus client yang memiliki project aktif. Selesaikan atau hapus project yang masih berjalan terlebih dahulu.');
        }

        // Client dapat dihapus jika semua project sudah completed atau tidak ada project
        // Delete related user if exists
        if ($client->user) {
            $client->user->delete();
        }

        $clientName = $client->name ?: $client->user->name ?? 'Client';
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', "Client '{$clientName}' berhasil dihapus.");
    }

    private function adminUser(): \App\Models\User
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user instanceof \App\Models\User) {
            abort(403);
        }

        return $user;
    }
}
