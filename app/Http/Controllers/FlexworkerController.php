<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Flexworker;
use Carbon\Carbon;

class FlexworkerController extends Controller
{
    public function index() {

        return View('flexworker.overview', [
            'flexworkers' => Flexworker::all()
        ]);

    }

    public function create() {
        return View('flexworker.create');
    }

    public function store() {

        $attributes = request()->validate([
            'invoice' => 'required',
            'initials' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'gender' => 'required',
            'nationality' => 'required',
            'description' => 'nullable',          
        ]);

        $attributes['status'] = 'pending';

        Flexworker::create($attributes);

        toastr()->success('Flexworker added successfully');
        return redirect(route('flexworker.index'));
    }

    public function show(Flexworker $flexworker) {

        return View('flexworker.show', [
            'flexworker' => $flexworker
        ]);
    }

    public function update(Flexworker $flexworker) {

        if($flexworker->relation_id){
            $attributes = request()->validate([
                'invoice' => 'required',
                'description' => 'nullable',          
            ]);
        } else {
            $attributes = request()->validate([
                'invoice' => 'required',
                'initials' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'gender' => 'required',
                'nationality' => 'required',
                'description' => 'nullable',
                'snelstart_id' => 'nullable',           
            ]);
        } 

        $flexworker->update($attributes);

        toastr()->success('Flexworker modified successfully');
        return redirect(route('flexworker.show', $flexworker->id));
    }

    public static function importCSV($csvFileName)
    {
        $csvFile = storage_path('app/private/flexworker_imports/' . $csvFileName);
        $row = 0;
        $debug = array();
        $now = Carbon::now()->format('Y-m-d');
        $flexworker_ids = array();

        $file_handle = fopen($csvFile, 'r');
        while ($csvRow = fgetcsv($file_handle, null, ';')) {

            if($row > 0){

                // Convert from ISO-8859-1 to UTF-8 to handle special characters
                $csvRow = array_map(function($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }, $csvRow);

                $relationID = $csvRow[0];
                $first_name = $csvRow[5];
                $last_name = $csvRow[6];

                $flexworker_imported = Flexworker::where('relation_id', $relationID)->first();
                $flexworker_manual = Flexworker::where('first_name', $first_name)->where('last_name', $last_name)->whereNull('relation_id')->first();

                if($flexworker_imported){
                    $flexworker = $flexworker_imported;
                }
                elseif($flexworker_manual) {
                    $flexworker = $flexworker_manual;
                    $flexworker->description = ("Merged with local account on " . $now);
                }
                else {
                    $flexworker = new Flexworker();
                    $flexworker->description = ("Imported " . $now);
                }                

                $flexworker->relation_id = $csvRow[0];
                $flexworker->initials = $csvRow[8];
                $flexworker->first_name = $first_name;
                $flexworker->last_name = $last_name;
                $flexworker->email = $csvRow[10] ?? null;

                if($csvRow[3] == 'M') {
                    $flexworker->gender = 'male';
                } else {
                    $flexworker->gender = 'female';
                }

                switch($csvRow[9]) {
                    case 'Letland':
                        $flexworker->nationality = 'latvian';
                        break;
                    case 'Polen':
                        $flexworker->nationality = 'polish';
                        break;
                    case str_contains($csvRow[9], 'Oekra'):
                        $flexworker->nationality = 'ukrainian';
                        break;
                    case 'Nederland':
                        $flexworker->nationality = 'dutch';
                        break;
                    case 'Griekenland':
                        $flexworker->nationality = 'greek';
                        break;
                    case 'Litouwen':
                        $flexworker->nationality = 'lithuanian';
                        break;
                    case 'Bulgarije':
                        $flexworker->nationality = 'bulgarian';
                        break;
                    default:
                        $flexworker->nationality = '??';
                        break;
                }

                $flexworker->status = "working";
                $flexworker->invoice = "yes";

                $flexworker->save();

                $flexworker_ids[] = $flexworker->id;
            }

            $row++;
        }
        fclose($file_handle);

        $nonActiveFlexworkers = Flexworker::whereNotIn('id', $flexworker_ids)->whereNot('status', 'pending')->get();
        foreach($nonActiveFlexworkers as $nonActiveFlexworker){
            $nonActiveFlexworker->status = 'nonactive';
            $nonActiveFlexworker->save();
        }
    }
}