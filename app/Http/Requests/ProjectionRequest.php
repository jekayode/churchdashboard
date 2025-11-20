<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProjectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller via Gates
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $projectionId = $this->route('projection')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $isGlobal = filter_var($this->input('is_global', false), FILTER_VALIDATE_BOOL);
        $branchId = $this->input('branch_id');
        $year = (int) $this->input('year');
        $isStoreGlobal = ($this->route() && $this->route()->getName() === 'api.projections.global') || $this->is('api/projections/global') || $this->is('*/projections/global') || $isGlobal;

        return [
            'is_global' => [
                'sometimes',
                'boolean',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'year' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:'.(now()->year - 5), // Allow projections for past 5 years
                'max:'.(now()->year + 10), // Allow projections for next 10 years
                // Skip uniqueness validation for global projections via storeGlobal method
                $isStoreGlobal ? null : Rule::unique('projections')->ignore($projectionId)->where(function ($query) use ($isGlobal, $branchId, $year) {
                    $query->where('year', $year)
                        ->where('is_global', (bool) $isGlobal);
                    if ($isGlobal) {
                        $query->whereNull('branch_id');
                    } else {
                        $query->where('branch_id', $branchId);
                    }

                    return $query;
                }),
            ],
            'attendance_target' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'converts_target' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:0',
                'max:10000',
            ],
            'leaders_target' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:0',
                'max:5000',
            ],
            'volunteers_target' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:0',
                'max:10000',
            ],
            'weekly_avg_attendance_target' => [
                'sometimes',
                'integer',
                'min:0',
                'max:50000',
            ],
            'guests_target' => [
                'sometimes',
                'integer',
                'min:0',
                'max:50000',
            ],
            'lifegroups_target' => [
                'sometimes',
                'integer',
                'min:0',
                'max:2000',
            ],
            'lifegroups_memberships_target' => [
                'sometimes',
                'integer',
                'min:0',
                'max:10000',
            ],
            'lifegroups_weekly_avg_attendance_target' => [
                'sometimes',
                'integer',
                'min:0',
                'max:10000',
            ],
            'quarters' => [
                'sometimes',
                'array',
            ],
            'quarters.attendance' => [
                'sometimes',
                'array',
                'size:4',
            ],
            'quarters.attendance.*' => [
                'integer',
                'min:0',
                'max:10000',
            ],
            'quarters.converts' => [
                'sometimes',
                'array',
                'size:4',
            ],
            'quarters.converts.*' => [
                'integer',
                'min:0',
                'max:1000',
            ],
            'quarters.leaders' => [
                'sometimes',
                'array',
                'size:4',
            ],
            'quarters.leaders.*' => [
                'integer',
                'min:0',
                'max:500',
            ],
            'quarters.volunteers' => [
                'sometimes',
                'array',
                'size:4',
            ],
            'quarters.volunteers.*' => [
                'integer',
                'min:0',
                'max:1000',
            ],
            'status' => [
                'sometimes',
                'string',
                'in:draft,in_review,approved,rejected',
            ],
            'quarterly_breakdown' => [
                'nullable',
                'array',
                'size:4', // Must have exactly 4 quarters
            ],
            'quarterly_breakdown.*.quarter' => [
                'required_with:quarterly_breakdown',
                'integer',
                'min:1',
                'max:4',
            ],
            'quarterly_breakdown.*.attendance_target' => [
                'required_with:quarterly_breakdown',
                'integer',
                'min:0',
            ],
            'quarterly_breakdown.*.converts_target' => [
                'required_with:quarterly_breakdown',
                'integer',
                'min:0',
            ],
            'quarterly_breakdown.*.leaders_target' => [
                'required_with:quarterly_breakdown',
                'integer',
                'min:0',
            ],
            'quarterly_breakdown.*.volunteers_target' => [
                'required_with:quarterly_breakdown',
                'integer',
                'min:0',
            ],
            'monthly_breakdown' => [
                'nullable',
                'array',
                'size:12', // Must have exactly 12 months
            ],
            'monthly_breakdown.*.month' => [
                'required_with:monthly_breakdown',
                'integer',
                'min:1',
                'max:12',
            ],
            'monthly_breakdown.*.attendance_target' => [
                'required_with:monthly_breakdown',
                'integer',
                'min:0',
            ],
            'monthly_breakdown.*.converts_target' => [
                'required_with:monthly_breakdown',
                'integer',
                'min:0',
            ],
            'monthly_breakdown.*.leaders_target' => [
                'required_with:monthly_breakdown',
                'integer',
                'min:0',
            ],
            'monthly_breakdown.*.volunteers_target' => [
                'required_with:monthly_breakdown',
                'integer',
                'min:0',
            ],
            // New quarterly fields validation
            'quarterly_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_converts' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_leaders' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_volunteers' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_weekly_avg_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_guests' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_weekly_avg_guests' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_weekly_avg_converts' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_lifegroups' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_lifegroups_memberships' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_lifegroups_weekly_avg_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            // Actual quarterly tracking
            'quarterly_actual_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_converts' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_leaders' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_volunteers' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_weekly_avg_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_guests' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_weekly_avg_guests' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_weekly_avg_converts' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_lifegroups' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_lifegroups_memberships' => [
                'nullable',
                'array',
                'size:4',
            ],
            'quarterly_actual_lifegroups_weekly_avg_attendance' => [
                'nullable',
                'array',
                'size:4',
            ],
            // Workflow fields
            'approval_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'rejection_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'branch_id' => 'branch',
            'year' => 'year',
            'attendance_target' => 'attendance target',
            'converts_target' => 'converts target',
            'leaders_target' => 'leaders target',
            'volunteers_target' => 'volunteers target',
            'weekly_avg_attendance_target' => 'weekly average attendance target',
            'guests_target' => 'guests target',
            'weekly_avg_guests_target' => 'weekly average guests target',
            'weekly_avg_converts_target' => 'weekly average converts target',
            'lifegroups_target' => 'lifegroups target',
            'lifegroups_memberships_target' => 'lifegroups memberships target',
            'lifegroups_weekly_avg_attendance_target' => 'lifegroups weekly average attendance target',
            'status' => 'status',
            'quarterly_breakdown' => 'quarterly breakdown',
            'monthly_breakdown' => 'monthly breakdown',
            'quarterly_attendance' => 'quarterly attendance',
            'quarterly_converts' => 'quarterly converts',
            'quarterly_leaders' => 'quarterly leaders',
            'quarterly_volunteers' => 'quarterly volunteers',
            'quarterly_weekly_avg_attendance' => 'quarterly weekly average attendance',
            'quarterly_guests' => 'quarterly guests',
            'quarterly_weekly_avg_guests' => 'quarterly weekly average guests',
            'quarterly_weekly_avg_converts' => 'quarterly weekly average converts',
            'quarterly_lifegroups' => 'quarterly lifegroups',
            'quarterly_lifegroups_memberships' => 'quarterly lifegroups memberships',
            'quarterly_lifegroups_weekly_avg_attendance' => 'quarterly lifegroups weekly average attendance',
            'quarterly_actual_attendance' => 'quarterly actual attendance',
            'quarterly_actual_converts' => 'quarterly actual converts',
            'quarterly_actual_leaders' => 'quarterly actual leaders',
            'quarterly_actual_volunteers' => 'quarterly actual volunteers',
            'quarterly_actual_weekly_avg_attendance' => 'quarterly actual weekly average attendance',
            'quarterly_actual_guests' => 'quarterly actual guests',
            'quarterly_actual_weekly_avg_guests' => 'quarterly actual weekly average guests',
            'quarterly_actual_weekly_avg_converts' => 'quarterly actual weekly average converts',
            'quarterly_actual_lifegroups' => 'quarterly actual lifegroups',
            'quarterly_actual_lifegroups_memberships' => 'quarterly actual lifegroups memberships',
            'quarterly_actual_lifegroups_weekly_avg_attendance' => 'quarterly actual lifegroups weekly average attendance',
            'approval_notes' => 'approval notes',
            'rejection_reason' => 'rejection reason',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.unique' => 'A projection for this branch and year already exists.',
            'year.unique' => 'A projection for this branch and year already exists.',
            'year.min' => 'Year must be within the last 5 years.',
            'year.max' => 'Year cannot be more than 10 years in the future.',
            'attendance_target.min' => 'Attendance target must be at least 1.',
            'attendance_target.max' => 'Attendance target cannot exceed 1,000,000.',
            'converts_target.max' => 'Converts target cannot exceed 1,000.',
            'leaders_target.max' => 'Leaders target cannot exceed 500.',
            'volunteers_target.max' => 'Volunteers target cannot exceed 1,000.',
            'status.in' => 'Status must be one of: draft, in_review, approved, rejected.',
            'quarterly_breakdown.size' => 'Quarterly breakdown must contain exactly 4 quarters.',
            'monthly_breakdown.size' => 'Monthly breakdown must contain exactly 12 months.',
            'quarterly_attendance.size' => 'Quarterly attendance must contain exactly 4 quarters.',
            'quarterly_converts.size' => 'Quarterly converts must contain exactly 4 quarters.',
            'quarterly_leaders.size' => 'Quarterly leaders must contain exactly 4 quarters.',
            'quarterly_volunteers.size' => 'Quarterly volunteers must contain exactly 4 quarters.',
            'quarterly_actual_attendance.size' => 'Quarterly actual attendance must contain exactly 4 quarters.',
            'quarterly_actual_converts.size' => 'Quarterly actual converts must contain exactly 4 quarters.',
            'quarterly_actual_leaders.size' => 'Quarterly actual leaders must contain exactly 4 quarters.',
            'quarterly_actual_volunteers.size' => 'Quarterly actual volunteers must contain exactly 4 quarters.',
            'quarterly_actual_weekly_avg_attendance.size' => 'Quarterly actual weekly average attendance must contain exactly 4 quarters.',
            'quarterly_actual_guests.size' => 'Quarterly actual guests must contain exactly 4 quarters.',
            'quarterly_actual_weekly_avg_guests.size' => 'Quarterly actual weekly average guests must contain exactly 4 quarters.',
            'quarterly_actual_weekly_avg_converts.size' => 'Quarterly actual weekly average converts must contain exactly 4 quarters.',
            'quarterly_actual_lifegroups.size' => 'Quarterly actual lifegroups must contain exactly 4 quarters.',
            'quarterly_actual_lifegroups_memberships.size' => 'Quarterly actual lifegroups memberships must contain exactly 4 quarters.',
            'quarterly_actual_lifegroups_weekly_avg_attendance.size' => 'Quarterly actual lifegroups weekly average attendance must contain exactly 4 quarters.',
            'quarterly_weekly_avg_attendance.size' => 'Quarterly weekly average attendance must contain exactly 4 quarters.',
            'quarterly_guests.size' => 'Quarterly guests must contain exactly 4 quarters.',
            'quarterly_weekly_avg_guests.size' => 'Quarterly weekly average guests must contain exactly 4 quarters.',
            'quarterly_weekly_avg_converts.size' => 'Quarterly weekly average converts must contain exactly 4 quarters.',
            'quarterly_lifegroups.size' => 'Quarterly lifegroups must contain exactly 4 quarters.',
            'quarterly_lifegroups_memberships.size' => 'Quarterly lifegroups memberships must contain exactly 4 quarters.',
            'quarterly_lifegroups_weekly_avg_attendance.size' => 'Quarterly lifegroups weekly average attendance must contain exactly 4 quarters.',
            'approval_notes.max' => 'Approval notes cannot exceed 1,000 characters.',
            'rejection_reason.max' => 'Rejection reason cannot exceed 1,000 characters.',
            'rejection_reason.required' => 'A rejection reason is required when rejecting a projection.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize is_global to boolean
        if ($this->has('is_global')) {
            $this->merge([
                'is_global' => filter_var($this->input('is_global'), FILTER_VALIDATE_BOOL),
            ]);
        }

        // Ensure quarterly breakdown has proper structure if provided
        if ($this->has('quarterly_breakdown') && is_array($this->quarterly_breakdown)) {
            $quarterlyData = [];
            foreach ($this->quarterly_breakdown as $quarter) {
                if (is_array($quarter)) {
                    $quarterlyData[] = $quarter;
                }
            }
            $this->merge(['quarterly_breakdown' => $quarterlyData]);
        }

        // Ensure monthly breakdown has proper structure if provided
        if ($this->has('monthly_breakdown') && is_array($this->monthly_breakdown)) {
            $monthlyData = [];
            foreach ($this->monthly_breakdown as $month) {
                if (is_array($month)) {
                    $monthlyData[] = $month;
                }
            }
            $this->merge(['monthly_breakdown' => $monthlyData]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation for quarterly breakdown totals
            if ($this->has('quarterly_breakdown') && is_array($this->quarterly_breakdown)) {
                $this->validateQuarterlyTotals($validator);
            }

            // Custom validation for monthly breakdown totals
            if ($this->has('monthly_breakdown') && is_array($this->monthly_breakdown)) {
                $this->validateMonthlyTotals($validator);
            }
        });
    }

    /**
     * Validate that quarterly breakdown totals match yearly targets.
     */
    private function validateQuarterlyTotals($validator): void
    {
        $quarterlyBreakdown = $this->quarterly_breakdown;

        if (count($quarterlyBreakdown) !== 4) {
            return; // Let the size validation handle this
        }

        $quarterlyTotals = [
            'attendance' => 0,
            'converts' => 0,
            'leaders' => 0,
            'volunteers' => 0,
        ];

        foreach ($quarterlyBreakdown as $quarter) {
            if (is_array($quarter)) {
                $quarterlyTotals['attendance'] += $quarter['attendance_target'] ?? 0;
                $quarterlyTotals['converts'] += $quarter['converts_target'] ?? 0;
                $quarterlyTotals['leaders'] += $quarter['leaders_target'] ?? 0;
                $quarterlyTotals['volunteers'] += $quarter['volunteers_target'] ?? 0;
            }
        }

        // Check if quarterly totals reasonably align with yearly targets (allow 10% variance)
        $targets = [
            'attendance' => $this->attendance_target,
            'converts' => $this->converts_target,
            'leaders' => $this->leaders_target,
            'volunteers' => $this->volunteers_target,
        ];

        foreach ($targets as $key => $yearlyTarget) {
            if ($yearlyTarget > 0) {
                $variance = abs($quarterlyTotals[$key] - $yearlyTarget) / $yearlyTarget;
                if ($variance > 0.1) { // 10% variance allowed
                    $validator->errors()->add(
                        'quarterly_breakdown',
                        "Quarterly {$key} targets should reasonably align with yearly target of {$yearlyTarget}."
                    );
                }
            }
        }
    }

    /**
     * Validate that monthly breakdown totals match yearly targets.
     */
    private function validateMonthlyTotals($validator): void
    {
        $monthlyBreakdown = $this->monthly_breakdown;

        if (count($monthlyBreakdown) !== 12) {
            return; // Let the size validation handle this
        }

        $monthlyTotals = [
            'attendance' => 0,
            'converts' => 0,
            'leaders' => 0,
            'volunteers' => 0,
        ];

        foreach ($monthlyBreakdown as $month) {
            if (is_array($month)) {
                $monthlyTotals['attendance'] += $month['attendance_target'] ?? 0;
                $monthlyTotals['converts'] += $month['converts_target'] ?? 0;
                $monthlyTotals['leaders'] += $month['leaders_target'] ?? 0;
                $monthlyTotals['volunteers'] += $month['volunteers_target'] ?? 0;
            }
        }

        // Check if monthly totals reasonably align with yearly targets (allow 10% variance)
        $targets = [
            'attendance' => $this->attendance_target,
            'converts' => $this->converts_target,
            'leaders' => $this->leaders_target,
            'volunteers' => $this->volunteers_target,
        ];

        foreach ($targets as $key => $yearlyTarget) {
            if ($yearlyTarget > 0) {
                $variance = abs($monthlyTotals[$key] - $yearlyTarget) / $yearlyTarget;
                if ($variance > 0.1) { // 10% variance allowed
                    $validator->errors()->add(
                        'monthly_breakdown',
                        "Monthly {$key} targets should reasonably align with yearly target of {$yearlyTarget}."
                    );
                }
            }
        }
    }
}
