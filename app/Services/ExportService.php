<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExportService
{
    /**
     * Export data to CSV format
     */
    public function toCsv(array $data, array $headers, string $filename = 'export.csv'): string
    {
        $output = fopen('php://temp', 'r+');

        // Write BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export events to CSV
     */
    public function exportEventsToCsv(Collection $events): string
    {
        $headers = [
            'ID',
            'Title',
            'Event Space',
            'Client Name',
            'Client Email',
            'Client Phone',
            'Start Date',
            'End Date',
            'Duration (Days)',
            'Start Time',
            'End Time',
            'Status',
            'Staff Count',
            'Created By',
            'Created At',
        ];

        $data = $events->map(function ($event) {
            return [
                $event->id,
                $event->title,
                $event->eventSpace->name,
                $event->client_name,
                $event->client_email,
                $event->client_phone ?? '',
                $event->start_date->format('Y-m-d'),
                $event->end_date->format('Y-m-d'),
                $event->start_date->diffInDays($event->end_date) + 1,
                $event->start_time ?? '',
                $event->end_time ?? '',
                ucfirst($event->status),
                $event->staff->count(),
                $event->creator->name,
                $event->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        return $this->toCsv($data, $headers);
    }

    /**
     * Export spaces to CSV
     */
    public function exportSpacesToCsv(Collection $spaces): string
    {
        $headers = [
            'ID',
            'Name',
            'Location',
            'Description',
            'Capacity',
            'Active',
            'Total Bookings',
            'Created At',
        ];

        $data = $spaces->map(function ($space) {
            return [
                $space->id,
                $space->name,
                $space->location,
                $space->description ?? '',
                $space->capacity ?? '',
                $space->is_active ? 'Yes' : 'No',
                $space->events_count ?? 0,
                $space->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        return $this->toCsv($data, $headers);
    }

    /**
     * Export staff to CSV
     */
    public function exportStaffToCsv(Collection $staff): string
    {
        $headers = [
            'ID',
            'Name',
            'Email',
            'Position',
            'Specializations',
            'Available',
            'Total Assignments',
            'Created At',
        ];

        $data = $staff->map(function ($staffMember) {
            return [
                $staffMember->id,
                $staffMember->user->name,
                $staffMember->user->email,
                $staffMember->position ?? '',
                is_array($staffMember->specializations)
                    ? implode(', ', $staffMember->specializations)
                    : '',
                $staffMember->is_available ? 'Yes' : 'No',
                $staffMember->events_count ?? 0,
                $staffMember->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        return $this->toCsv($data, $headers);
    }

    /**
     * Export data to JSON format
     */
    public function toJson(array $data, string $filename = 'export.json'): string
    {
        return json_encode([
            'exported_at' => now()->toIso8601String(),
            'filename' => $filename,
            'count' => count($data),
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export data to Excel-compatible format (CSV with specific encoding)
     */
    public function toExcel(array $data, array $headers): string
    {
        // Excel prefers CSV with specific formatting
        return $this->toCsv($data, $headers);
    }

    /**
     * Generate filename with timestamp
     */
    public function generateFilename(string $prefix, string $extension = 'csv'): string
    {
        return sprintf(
            '%s_%s.%s',
            $prefix,
            now()->format('Y-m-d_His'),
            $extension
        );
    }

    /**
     * Create response headers for download
     */
    public function getDownloadHeaders(string $filename, string $mimeType = 'text/csv'): array
    {
        return [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }
}
