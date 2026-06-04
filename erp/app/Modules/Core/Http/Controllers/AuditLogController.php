<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        // Only super-admin can view the cross-tenant audit log
        if (! $request->user()->hasRole('super-admin')) {
            abort(403);
        }

        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($auditableType = $request->query('auditable_type')) {
            $query->where('auditable_type', 'like', "%{$auditableType}%");
        }

        if ($tenantId = $request->query('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(30)->withQueryString();

        $filters = $request->only([
            'event',
            'user_id',
            'auditable_type',
            'tenant_id',
            'date_from',
            'date_to',
        ]);

        return Inertia::render('Core/AuditLogs/Index', [
            'logs'    => $logs,
            'filters' => $filters,
        ]);
    }
}
