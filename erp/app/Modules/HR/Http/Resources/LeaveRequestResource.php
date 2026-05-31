<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'employee_id' => $this->employee_id,
            'employee'    => $this->whenLoaded('employee', fn () => $this->employee
                ? ['id' => $this->employee->id, 'full_name' => $this->employee->full_name]
                : null),
            'leave_type_id' => $this->leave_type_id,
            'leave_type'  => $this->whenLoaded('leaveType', fn () => $this->leaveType?->name),
            'type'        => $this->leaveType?->name ?? 'other',
            'start_date'  => $this->start_date?->toDateString(),
            'end_date'    => $this->end_date?->toDateString(),
            'days'        => $this->days,
            'reason'      => $this->notes,
            'notes'       => $this->notes,
            'status'      => $this->status,
            'approved_by' => $this->reviewed_by,
            'approved_at' => $this->reviewed_at?->toDateTimeString(),
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toDateTimeString(),
            'approver'    => $this->whenLoaded('reviewer', fn () => $this->reviewer?->name),
            'created_at'  => $this->created_at?->toDateString(),
        ];
    }
}
