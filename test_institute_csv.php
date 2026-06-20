<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Api\InstituteResultController;

// Create test CSV
$csvContent = "roll,failed_subjects\n593818,\"66454(T),67045(T)\"\n593819,\n593820,\"66641(T)\"\n593821,\"66741(T),66752(T),66763(T)\"\n593822,\n";
$tmpFile = tempnam(sys_get_temp_dir(), 'csv');
file_put_contents($tmpFile, $csvContent);

$user = \App\Models\User::where('role', 'admin')->first();

$request = new \Illuminate\Http\Request();
$request->merge(['semester' => '1st', 'academic_year' => '2025']);
$request->files->set('file', new UploadedFile($tmpFile, 'test.csv', 'text/csv', null, true));
$request->setUserResolver(fn() => $user);

$controller = new InstituteResultController();
$response = $controller->uploadCsv($request);

echo "CSV Upload Response:\n";
echo $response->getContent() . "\n\n";

// Verify DB
$count = \App\Models\InstituteResult::count();
echo "Total records in DB: {$count}\n";

$results = \App\Models\InstituteResult::all();
foreach ($results as $r) {
    echo "  {$r->roll} | {$r->semester} | {$r->academic_year} | {$r->status} | " . json_encode($r->referred_subjects) . "\n";
}

// Test search
$searchRequest = new \Illuminate\Http\Request();
$searchRequest->merge(['roll' => '593818']);
$searchResponse = $controller->search($searchRequest);
echo "\nSearch 593818:\n";
echo $searchResponse->getContent() . "\n";

// Test stats
$statsRequest = new \Illuminate\Http\Request();
$statsResponse = $controller->stats($statsRequest);
echo "\nStats:\n";
echo $statsResponse->getContent() . "\n";

// Cleanup
unlink($tmpFile);
\App\Models\InstituteResult::truncate();
echo "\nCleaned up.\n";
