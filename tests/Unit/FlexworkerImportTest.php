<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\FlexworkerController;
use App\Models\Flexworker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlexworkerImportTest extends TestCase
{
    use RefreshDatabase;

    private string $csvFilePath;

    protected function tearDown(): void
    {
        // Clean up test file if it exists
        if (isset($this->csvFilePath) && file_exists($this->csvFilePath)) {
            unlink($this->csvFilePath);
        }
        parent::tearDown();
    }

    /**
     * Test that special characters are properly handled during CSV import.
     */
    public function test_import_csv_handles_special_characters(): void
    {
        // Create the directory structure if it doesn't exist
        $directory = storage_path('app/private/flexworker_imports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Create CSV content with special characters
        // Using é (which is \xE9 in ISO-8859-1)
        $csvContent = "Relation ID;Field1;Field2;Gender;Field4;First Name;Last Name;Field7;Initials;Nationality;Email\n";
        $csvContent .= "7442921;test1;test2;F;test4;Danaé;Tinholt;test7;D.;Nederland;danaetinholt@hotmail.com\n";
        
        // Convert to ISO-8859-1 encoding to simulate the real scenario
        $csvContent = mb_convert_encoding($csvContent, 'ISO-8859-1', 'UTF-8');
        
        $csvFileName = 'test_import.csv';
        $this->csvFilePath = storage_path('app/private/flexworker_imports/' . $csvFileName);
        file_put_contents($this->csvFilePath, $csvContent);
        
        // Run the import
        FlexworkerController::importCSV($csvFileName);
        
        // Verify the flexworker was created with correct special character
        $flexworker = Flexworker::where('relation_id', 7442921)->first();
        
        $this->assertNotNull($flexworker);
        $this->assertEquals('Danaé', $flexworker->first_name);
        $this->assertEquals('Tinholt', $flexworker->last_name);
        $this->assertEquals('D.', $flexworker->initials);
        $this->assertEquals('danaetinholt@hotmail.com', $flexworker->email);
        $this->assertEquals('female', $flexworker->gender);
        $this->assertEquals('dutch', $flexworker->nationality);
        $this->assertEquals('working', $flexworker->status);
    }
}
