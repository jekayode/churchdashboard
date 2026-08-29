<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\WorkforceDriveSignup;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

final class WorkforceDriveSignupsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(private readonly array $filters = []) {}

    public function collection(): Collection
    {
        $query = WorkforceDriveSignup::with('team');

        if (! empty($this->filters['team_id'])) {
            $query->where('workforce_drive_team_id', $this->filters['team_id']);
        }

        return $query->latest('id')->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Email', 'Phone', 'Team', 'Core Value', 'Sponsor', 'Joined At'];
    }

    /**
     * @param WorkforceDriveSignup $signup
     * @return array<int, string|null>
     */
    public function map($signup): array
    {
        return [
            $signup->name,
            $signup->email,
            $signup->phone,
            $signup->team?->name,
            $signup->team?->core_value,
            $signup->team?->sponsor_name,
            $signup->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function title(): string
    {
        return 'Workforce Drive Signups';
    }
}
