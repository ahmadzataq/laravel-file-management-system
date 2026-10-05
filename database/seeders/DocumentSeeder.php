<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk(Document::DISK);
        $disk->deleteDirectory('documents');   // start clean when re-seeding

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        // [folder, department, title, file name, mime type]
        $samples = [
            ['HR Policies', 'Human Resources', 'Employee Handbook', 'employee-handbook.pdf', 'application/pdf'],
            ['HR Policies', 'Human Resources', 'Leave Policy 2026', 'leave-policy-2026.pdf', 'application/pdf'],
            ['IT Policies', 'IT', 'Information Security Policy', 'information-security-policy.pdf', 'application/pdf'],
            ['IT Policies', 'IT', 'Password Guidelines', 'password-guidelines.txt', 'text/plain'],
            ['Invoices', 'Finance', 'Invoice INV-2026-001', 'inv-2026-001.pdf', 'application/pdf'],
            ['Invoices', 'Finance', 'Invoice INV-2026-002', 'inv-2026-002.pdf', 'application/pdf'],
            ['Reports', 'Finance', 'Q1 Financial Report', 'q1-financial-report.csv', 'text/csv'],
            ['Reports', 'Finance', 'Q2 Financial Report', 'q2-financial-report.csv', 'text/csv'],
            ['Reports', 'Finance', 'Q3 Financial Report', 'q3-financial-report.csv', 'text/csv'],
            ['Project Alpha', 'Operations', 'Project Alpha Brief', 'project-alpha-brief.pdf', 'application/pdf'],
            ['Project Alpha', 'Marketing', 'Launch Plan', 'launch-plan.txt', 'text/plain'],
            ['Project Alpha', 'Legal', 'Vendor Contract Draft', 'vendor-contract-draft.pdf', 'application/pdf'],
        ];

        foreach ($samples as $index => [$folderName, $departmentName, $title, $fileName, $mimeType]) {
            $contents = $mimeType === 'application/pdf'
                ? $this->samplePdf($title)
                : "Sample content for \"{$title}\".\n";

            $path = 'documents/'.Str::uuid().'.'.pathinfo($fileName, PATHINFO_EXTENSION);
            $disk->put($path, $contents);

            $document = new Document([
                'folder_id' => Folder::where('name', $folderName)->value('id'),
                'department_id' => Department::where('name', $departmentName)->value('id'),
                'uploaded_by' => $admin->id,
                'title' => $title,
                'original_name' => $fileName,
                'path' => $path,
                'mime_type' => $mimeType,
                'size' => strlen($contents),
            ]);

            // Spread the upload dates so "latest files" has a natural order.
            $document->created_at = now()->subHours((count($samples) - $index) * 6);
            $document->save();
        }
    }

    /**
     * A tiny but valid one-page PDF, so the PDF preview works right after seeding.
     */
    private function samplePdf(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 18 Tf 24 80 Td ({$text}) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 360 160] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($number + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";
    }
}
