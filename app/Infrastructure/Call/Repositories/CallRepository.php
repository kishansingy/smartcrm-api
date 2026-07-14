<?php

namespace App\Infrastructure\Call\Repositories;

use App\Domain\Call\Contracts\CallRepositoryInterface;
use App\Domain\Call\Models\CallLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CallRepository implements CallRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = CallLog::with(['user:id,name,email', 'contact:id,first_name,last_name,phone', 'lead:id,first_name,last_name']);

        if ($filters['search'] ?? null) {
            $query->where(function ($q) use ($filters) {
                $q->where('phone_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('contact', fn ($c) => $c->where('first_name', 'like', "%{$filters['search']}%")
                      ->orWhere('last_name', 'like', "%{$filters['search']}%"));
            });
        }

        if ($filters['status']     ?? null) $query->where('status',    $filters['status']);
        if ($filters['direction']  ?? null) $query->where('direction', $filters['direction']);
        if ($filters['user_id']    ?? null) $query->where('user_id',   $filters['user_id']);
        if ($filters['contact_id'] ?? null) $query->where('contact_id', $filters['contact_id']);
        if ($filters['lead_id']    ?? null) $query->where('lead_id',    $filters['lead_id']);
        if ($filters['date_from']  ?? null) $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']    ?? null) $query->whereDate('created_at', '<=', $filters['date_to']);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?CallLog
    {
        return CallLog::with(['user:id,name,email', 'contact', 'lead'])->find($id);
    }

    public function findByProviderCallId(string $providerCallId): ?CallLog
    {
        return CallLog::where('provider_call_id', $providerCallId)->first();
    }

    public function create(array $data): CallLog
    {
        return CallLog::create($data);
    }

    public function update(CallLog $call, array $data): CallLog
    {
        $call->update($data);
        return $call->refresh();
    }

    public function getStatsByTenant(int $tenantId): array
    {
        $q = CallLog::where('tenant_id', $tenantId);

        return [
            'total'        => (clone $q)->count(),
            'completed'    => (clone $q)->where('status', 'completed')->count(),
            'failed'       => (clone $q)->whereIn('status', ['failed', 'no_answer', 'busy'])->count(),
            'in_progress'  => (clone $q)->where('status', 'in_progress')->count(),
            'today'        => (clone $q)->whereDate('created_at', today())->count(),
            'this_week'    => (clone $q)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'avg_duration' => (int) (clone $q)->where('status', 'completed')->avg('duration'),
            'with_summary' => (clone $q)->whereNotNull('ai_summary')->count(),
        ];
    }

    public function getReportData(int $tenantId, array $filters): array
    {
        $q = CallLog::where('tenant_id', $tenantId);

        if ($filters['date_from'] ?? null) $q->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']   ?? null) $q->whereDate('created_at', '<=', $filters['date_to']);
        if ($filters['user_id']   ?? null) $q->where('user_id', $filters['user_id']);

        $calls = (clone $q)->with(['user:id,name', 'contact:id,first_name,last_name'])->get();

        $byStatus = (clone $q)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');

        $byAgent = (clone $q)->select('user_id', DB::raw('count(*) as count, avg(duration) as avg_duration'))
            ->with('user:id,name')
            ->groupBy('user_id')
            ->get()
            ->map(fn ($r) => [
                'agent'        => $r->user?->name ?? 'Unknown',
                'total_calls'  => $r->count,
                'avg_duration' => (int) $r->avg_duration,
            ]);

        $daily = (clone $q)->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return [
            'summary'    => $this->getStatsByTenant($tenantId),
            'by_status'  => $byStatus,
            'by_agent'   => $byAgent,
            'daily'      => $daily,
            'calls'      => $calls,
        ];
    }
}
