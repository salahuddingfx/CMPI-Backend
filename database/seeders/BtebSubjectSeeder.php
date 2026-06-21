<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Facades\File;

class BtebSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('../subjects.json');

        if (!File::exists($jsonPath)) {
            $this->command->error('subjects.json not found at: ' . $jsonPath);
            return;
        }

        $json = File::get($jsonPath);
        $subjects = json_decode($json, true);

        if (!is_array($subjects)) {
            $this->command->error('Invalid JSON in subjects.json');
            return;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($subjects as $subject) {
            $semesterMap = [
                1 => '1st',
                2 => '2nd',
                3 => '3rd',
                4 => '4th',
                5 => '5th',
                6 => '6th',
                7 => '7th',
                8 => '8th',
            ];

            $semester = $semesterMap[$subject['semester']] ?? $subject['semester'] . 'th';

            Subject::updateOrCreate(
                [
                    'department'   => $subject['technology_name'],
                    'subject_code' => $subject['subject_code'],
                ],
                [
                    'semester'       => $semester,
                    'technology_code'=> $subject['technology_code'],
                    'technology_name'=> $subject['technology_name'],
                    'subject_name'   => $subject['subject_name'],
                    'credit'         => $subject['credit'] ?? null,
                    'theory_marks'   => $subject['theory_marks'] ?? 0,
                    'practical_marks'=> $subject['practical_marks'] ?? 0,
                    'total_marks'    => $subject['total_marks'] ?? 0,
                ]
            );

            $imported++;
        }

        $totalSubjects = Subject::count();
        $this->command->info("Imported {$imported} subjects from BTEB 2022 Regulation.");
        $this->command->info("Total subjects in database: {$totalSubjects}");
    }
}
