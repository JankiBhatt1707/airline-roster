<?php namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Roster;
use App\Models\RosterEvent;
use App\Models\FlightDetail;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Storage;

class RosterParserService
{
    public function parseAndStoreFile($file)
    {

        $fileExtension = $file->extension();
        $roster = $this->storeAndSaveFile($file, $fileExtension);
        // Implement parsing logic based on file type (pdf, excel, txt, html, etc.)
        

        switch ($fileExtension) {
            case 'pdf':
                // Implement PDF parsing logic
                break;
            case 'xls':
            case 'xlsx':
                // Implement Excel parsing logic
                break;
            case 'txt':
            case 'html':
                // Implement plain text or HTML parsing logic
                $rosterData = $this->parseTextorHtmlFile($file, $roster);
                break;
            case 'ics':
                // Implement webcal parsing logic
                break;
            default:
                return ['error' => 'Unsupported file type.'];
        }

        // Store the parsed data in the database
        $this->storeParsedData($rosterData);

        return ['message' => 'Roster data parsed and stored successfully.'];
    }

    private function storeAndSaveFile($file, $fileExtension)
    {
        $saveFile = $file->storeAs('file', 'roster.'.$fileExtension, 'local');
        
        $roster = Roster::where('upload_type', $fileExtension)->first();

        if (empty($roster)) {
            $roster = new Roster();
            $roster->upload_type = $fileExtension;
            $roster->uploaded_at = Carbon::now();
            $roster->save();
        }

        return $roster->id;

    }

    private function parseTextorHtmlFile($file, $rosterId)
    {
         try {
            $parsedData = [];
            $html = Storage::get('/file/roster.html');
            // $html = file_get_contents($file);
            $crawler  = new Crawler($html);
            

            $nodeValues = $crawler ->filter('b')->each(function (Crawler $node, $i) {

                if (str_contains($node->text(), 'Period')) {
                    return $node->text();
                }
            });

            $otherData['rosterId'] = $rosterId;
            $period =  array_filter($nodeValues);
            $period = substr(ltrim(str_replace(' ', '', $period[0]), 'Period:'), 0, 16);

            if (str_contains($period, 'to')) {

                $otherData['fromDate'] =  Carbon::parse(stristr($period, "to", true))->format('d-m-Y');
                $otherData['toDate'] = Carbon::parse(ltrim(stristr($period, "to"), 'to'))->format('d-m-Y');
            }

            $tableData = $crawler ->filter('table')/*->first()*/->each(function ($table) {
                return $table->filter('tr')->each(function ($tr) {
                    return $tr->filter('td')->each(function ($td) {
                        return $td->text();
                    });
                });
            });

            $rosterRowAndColumnData['columns'] = $tableData[0][0];
            $rosterRowAndColumnData['rows'] =  array_slice($tableData[0], 1);

            foreach ($rosterRowAndColumnData['columns'] as $cKey => $column) {

                $rosterRowAndColumnData['columns'][$cKey] = trim((str_replace(' ', '_', strtolower($column))), '.');
            }

            $i = 0;
            foreach ($rosterRowAndColumnData['rows'] as $k => $row) {

                foreach ($row as $key => $item) {

                    if (empty($item)) {

                        $insertItem = null;
                    } else {

                        $insertItem = $item;
                    }

                    $parsedData[$k][$rosterRowAndColumnData['columns'][$key]] = $insertItem;
                }
            }

            $parsedRosterData['roster'] = $parsedData;
            $parsedRosterData['otherData'] = $otherData;

            return $parsedRosterData;
        } catch (Exception $exception) {

            return response()->json('Something went wrong!', 500);
        }

       
    }

    private function storeParsedData($rosterData)
    {
        
        foreach ($rosterData['roster'] as $roster) {
            if (isset($roster['date']) && (!empty($roster['date']))) {
                $flyingDate = Carbon::parse($roster['date'] . (substr($rosterData['otherData']['fromDate'], 2)))->format('Y-m-d');
            }

            $event_type = $roster['activity'];
            if (preg_match('~[0-9]+~', $roster['activity'])) { 
                $flightData = [
                    'flight_number' => $roster['activity'],
                    'scheduled_time_departure' => $roster['std(z)'],
                    'scheduled_time_arrival' => $roster['sta(z)']
                ];

                $flightDetails = FlightDetail::where($flightData)->first(); 

                if ($flightDetails) {
                    $flightDetails->update(array_filter($flightData));
                } else {
                    FlightDetail::create(array_filter($flightData));
                }

                $event_type = 'FLT';
            }

            $rosterEventData = [
                'roster_id' => $rosterData['otherData']['rosterId'],
                'flight_number' => $roster['activity'],
                'type' => $event_type ? $event_type : 'UNK',
                'start_location' => $roster['from'],
                'end_location' => $roster['to'],
                'check_in_time' => $roster['c/i(z)'],
                'check_out_time' => $roster['c/o(z)'],
                'date' => $flyingDate,
                
            ];

            $rosterEventDetails = RosterEvent::where($rosterEventData)->first();

            if ($rosterEventDetails) {
                $rosterEventDetails->update(array_filter($rosterEventData));
            } else {
                RosterEvent::create(array_filter($rosterEventData));
            }
        }
    }
}

?>