<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $logs = AuditLog::with('user')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($request->event, fn ($q) => $q->where('event', $request->event))
            ->when($request->model, fn ($q) => $q->where('auditable_type', 'like', "%{$request->model}%"))
            ->latest()
            ->paginate(50)
            ->withQueryString()
            ->through(fn ($log) => [
                'id'             => $log->id,
                'event'          => $log->event,
                'model'          => class_basename($log->auditable_type),
                'model_id'       => $log->auditable_id,
                'user'           => $log->user?->name ?? 'System',
                'old_values'     => $log->old_values,
                'new_values'     => $log->new_values,
                'ip_address'     => $log->ip_address,
                'created_at'     => $log->created_at->diffForHumans(),
                'created_at_raw' => $log->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/AuditLog/Index', [
            'logs'        => $logs,
            'filters'     => $request->only(['event', 'model']),
            'breadcrumbs' => [
                ['label' => 'Administration'],
                ['label' => 'Audit Log', 'href' => route('admin.audit-log.index')],
            ],
        ]);
    }
}
