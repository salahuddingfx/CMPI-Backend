<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Facades\File;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(base_path('../subjects.json'));
        $subjects = json_decode($json, true);

        if (is_array($subjects)) {
            foreach ($subjects as $subject) {
                Subject::create($subject);
            }
        }
    }
}
