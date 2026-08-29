<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\WorkforceDriveSignupsExport;
use App\Http\Controllers\Controller;
use App\Models\WorkforceDriveSignup;
use App\Models\WorkforceDriveTeam;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Where the Workforce team reviews and exports the drive sign-ups.
 * Guarded by role middleware on the route group.
 */
final class WorkforceDriveAdminController extends Controller
{
    /**
     * Paginated table of registrations, plus per-team totals.
     */
    public function index(Request $request): View
    {
        $teamId = $request->integer('team_id') ?: null;

        $signups = WorkforceDriveSignup::with('team')
            ->when($teamId, fn ($q) => $q->where('workforce_drive_team_id', $teamId))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $teams = WorkforceDriveTeam::withCount('signups')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.workforce-drive.index', [
            'signups' => $signups,
            'teams' => $teams,
            'teamId' => $teamId,
            'total' => WorkforceDriveSignup::count(),
        ]);
    }

    /**
     * Download the registrations as CSV or Excel.
     */
    public function export(Request $request): StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
        ]);

        $filters = array_filter([
            'team_id' => $request->integer('team_id') ?: null,
        ]);

        $format = $request->get('format');
        $filename = 'workforce_drive_signups_'.now()->format('Y-m-d_His').'.'.$format;
        $export = new WorkforceDriveSignupsExport($filters);

        if ($format === 'csv') {
            $rows = $export->collection();

            return response()->streamDownload(function () use ($rows, $export): void {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $export->headings());
                foreach ($rows as $signup) {
                    fputcsv($handle, $export->map($signup));
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return Excel::download($export, $filename);
    }
}
