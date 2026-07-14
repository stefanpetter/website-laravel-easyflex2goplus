<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\FlexworkerController;
use App\Models\Flexworker;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function index()
    {
        /** @var \Webklex\PHPIMAP\Client $client */
        $client = \Webklex\IMAP\Facades\Client::account('smtp_import');

        //Connect to the IMAP Server
        $client->connect();

        //dd($client->getFolders());

        $today = Carbon::now()->startOfDay();

        $inbox = $client->getFolderByName('INBOX');
        $messages = $inbox->messages()->since($today)->get();
        $processed_mails = 0;

        //dd($messages);

        foreach($messages as $message){

            $subject = $message->get('subject')->toString();

            if(str_contains($subject, 'Ingeplande matches') && $processed_mails == 0){

                $flags = $message->getFlags()->toArray();

                if(!array_key_exists('flagged', $flags)) {
                    if($message->hasAttachments()){

                        $attachments = $message->getAttachments();

                        foreach($attachments as $attachment){

                            if(str_contains($attachment->name, 'EXPORT-MATCH') && $attachment->content_type == 'text/csv') {

                                $csvFilename = ($today->format('Y-m-d').'_Export_Flexworkers.csv');
                                $saved_attachment = $attachment->save($path = storage_path("app/private/flexworker_imports/"), $filename = $csvFilename);
                                FlexworkerController::importCSV($csvFilename);
                            }                                
                        }
                    } 

                    $message->setFlag(['Flagged']);
                    $processed_mails++;
                }
            }
        }

        return response()->json('Ok. Processed '.$processed_mails.' mails');
    }

    public function importSnelstart()
    {        
        $file_handle = fopen(storage_path('app/private/snelstart_imports/snelstart_flexworkers.csv'), 'r');
        $count = 0;
        while ($csvRow = fgetcsv($file_handle, null, ';')) {
            if($count > 0){
                // Convert from ISO-8859-1 to UTF-8 to handle special characters
                $csvRow = array_map(function($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }, $csvRow);

                $flexworker_id = $csvRow[0];
                $relation_id = $csvRow[1];
                $snelstart_id = $csvRow[2];

                $flexworker = Flexworker::find($flexworker_id);

                if($flexworker){
                    if($flexworker->relation_id == $relation_id){
                        if(!is_null($snelstart_id) && $snelstart_id != 'NULL'){
                            $flexworker->snelstart_id = $snelstart_id;
                            $flexworker->save();
                        }
                    }
                }
            }
           
            $count++;
        }
        fclose($file_handle);

        return response()->json('Ok. imported ids');
    }
}