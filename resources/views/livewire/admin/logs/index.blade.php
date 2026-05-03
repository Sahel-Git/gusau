<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\ActivityLog;

new #[Layout('admin.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        $query = ActivityLog::with('user')->latest();

        if ($this->search) {
            $query->where('action', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        }

        return [
            'logs' => $query->paginate(20)
        ];
    }

}; ?>

<div class="space-y-6 max-w-6xl mx-auto">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Activity Logs</h1>
        <div class="w-64">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search logs..." class="w-full border rounded-lg p-2 dark:bg-zinc-800 dark:border-zinc-700">
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm overflow-x-auto">

        <table class="w-full text-sm">
            <thead class="text-left text-zinc-500">
                <tr>
                    <th class="pb-2">User</th>
                    <th class="pb-2">Action</th>
                    <th class="pb-2">Description</th>
                    <th class="pb-2">Date</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $log)
                    <tr class="border-t">
                        <td class="py-2">{{ $log->user->name ?? 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-zinc-500">
                            No activity logs found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>