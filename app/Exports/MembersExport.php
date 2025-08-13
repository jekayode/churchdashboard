<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Member;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class MembersExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    private ?int $branchId;
    private array $filters;

    public function __construct(?int $branchId = null, array $filters = [])
    {
        $this->branchId = $branchId;
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Member::with(['branch', 'user', 'departments', 'smallGroups']);

        // Apply branch filter
        if (!is_null($this->branchId)) {
            $query->where('branch_id', $this->branchId);
        }

        // Apply additional filters
        if (!empty($this->filters['member_status'])) {
            $query->where('member_status', $this->filters['member_status']);
        }

        if (!empty($this->filters['growth_level'])) {
            $query->where('growth_level', $this->filters['growth_level']);
        }

        if (!empty($this->filters['teci_status'])) {
            $query->where('teci_status', $this->filters['teci_status']);
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        if (!empty($this->filters['marital_status'])) {
            $query->where('marital_status', $this->filters['marital_status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('date_joined', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('date_joined', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('name')
                    ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Gender',
            'Date of Birth',
            'Age',
            'Marital Status',
            'Occupation',
            'Nearest Bus Stop',
            'Branch',
            'Member Status',
            'Growth Level',
            'TECI Status',
            'Date Joined',
            'Anniversary',
            'Leadership Trainings',
            'Departments',
            'Small Groups',
            'Is Leader',
            'Account Status',
            'Last Updated'
        ];
    }

    public function map($member): array
    {
        return [
            $member->id,
            $member->name,
            $member->email,
            $member->phone,
            $member->gender ? ucfirst($member->gender) : '',
            $member->date_of_birth?->format('Y-m-d'),
            $member->age,
            $member->marital_status ? ucfirst(str_replace('_', ' ', $member->marital_status)) : '',
            $member->occupation,
            $member->nearest_bus_stop,
            $member->branch?->name,
            $member->member_status ? ucfirst(str_replace('_', ' ', $member->member_status)) : '',
            $member->growth_level ? ucfirst(str_replace('_', ' ', $member->growth_level)) : '',
            $member->teci_status ? ucfirst(str_replace('_', ' ', $member->teci_status)) : '',
            $member->date_joined?->format('Y-m-d'),
            $member->anniversary?->format('Y-m-d'),
            implode(', ', $member->leadership_trainings ?? []),
            $member->departments->pluck('name')->join(', '),
            $member->smallGroups->pluck('name')->join(', '),
            $member->isLeader() ? 'Yes' : 'No',
            $member->user ? 'Has Account' : 'No Account',
            $member->updated_at->format('Y-m-d H:i:s')
        ];
    }

    public function title(): string
    {
        $title = 'Members Export';
        
        if ($this->branchId) {
            $branch = \App\Models\Branch::find($this->branchId);
            $title .= ' - ' . ($branch?->name ?? 'Unknown Branch');
        }

        return $title;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = 'Y'; // Column Y is the last column (Last Updated)
        $lastRow = $sheet->getHighestRow();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Data rows styling
            "A2:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ],
            // Alternate row colors
            "A2:{$lastColumn}{$lastRow}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ]
        ];
    }

    public static function getExportFilename(?int $branchId = null, array $filters = []): string
    {
        $filename = 'members_export';
        
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            $filename .= '_' . str_replace(' ', '_', strtolower($branch?->name ?? 'unknown'));
        }

        if (!empty($filters['member_status'])) {
            $filename .= '_' . $filters['member_status'];
        }

        if (!empty($filters['growth_level'])) {
            $filename .= '_' . $filters['growth_level'];
        }

        $filename .= '_' . now()->format('Y_m_d_His');
        
        return $filename . '.xlsx';
    }

    public function getExportSummary(): array
    {
        $collection = $this->collection();
        
        return [
            'total_members' => $collection->count(),
            'branch_name' => $this->branchId ? 
                \App\Models\Branch::find($this->branchId)?->name : 'All Branches',
            'filters_applied' => $this->filters,
            'export_date' => now()->format('Y-m-d H:i:s'),
            'status_breakdown' => $collection->groupBy('member_status')
                ->map(fn($group) => $group->count())->toArray(),
            'growth_level_breakdown' => $collection->groupBy('growth_level')
                ->map(fn($group) => $group->count())->toArray(),
            'gender_breakdown' => $collection->groupBy('gender')
                ->map(fn($group) => $group->count())->toArray(),
        ];
    }
} 