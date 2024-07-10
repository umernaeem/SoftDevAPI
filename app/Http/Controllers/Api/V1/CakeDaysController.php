<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CakeDaysResource;
use App\Models\CakeDays;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CakeDaysController extends Controller
{
    /**
     * Return today's Cake day only.
     */
    public function today()
    {
        $current = Carbon::now();
        $today_cake_day = CakeDays::where("cake_date",$current->format("Y-m-d"))->get();
        return CakeDaysResource::collection($today_cake_day);
    }

    /**
     * Return next Cake day only.
     */
    public function next()
    {
        $current = Carbon::now();
        $today_cake_day = CakeDays::where("cake_date",">",$current->format("Y-m-d"))->limit(1)->get();
        return CakeDaysResource::collection($today_cake_day);
    }
    
    /**
     * Return upcoming's Cake day only after skipping the next.
     */
    public function upcoming()
    {
        $current = Carbon::now();
        $today_cake_day = CakeDays::where("cake_date",">",$current->format("Y-m-d"))->get()->skip(1);
        return CakeDaysResource::collection($today_cake_day);
    }
    /**
     * Process input file data and return as an array.
     */
    public function processInputFile($file_contents)
    {
        $file_data = [];
        $file_contents = str_replace("\r","",$file_contents);
        $file_data = explode("\n",$file_contents);
        if(substr_count($file_contents,",")!=count($file_data) || count($file_data)==0)
        {
            return [];
        }
        foreach($file_data as $data)
        {
            
            $temp = explode(",",$data);
            if(
                !isset($temp) ||
                !isset($temp[0]) ||
                !isset($temp[1]) ||
                !is_string($temp[0]) ||
                !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$temp[1])
            )
            {
                return [];
            }
        }
        return $file_data;
    }
    /**
     * process the input array data and filter the data so only current year Birthdays are available in the list.
     */
    public function filterInputData($processed_input_data)
    {
        $filtered_data = [];
        $current_date_obj = Carbon::now();
        foreach($processed_input_data as $data)
        {
            $temp_data = explode(",",$data);
            $current_year_birthday = Carbon::create($temp_data[1]);
            $current_year_birthday->year($current_date_obj->year);
            if($current_date_obj->diffInDays($current_year_birthday,false)+1>=0)
            {
                $filtered_data[] = [
                    'name' => $temp_data[0],
                    'dob' => $current_year_birthday->format("Y-m-d")
                ];
            }
        }
        return $filtered_data;
    }
    /**
     * check if the given date is weekend or not.
     */
    public function checkWeekend($date)
    {
        $date_obj = Carbon::create($date);
        return $date_obj->format('l')=="Saturday" || $date_obj->format('l')=="Sunday" ? true : false;
    }
    /**
     * check if the given date is Christmas Day or not.
     */
    public function checkChristmas($date)
    {
        $date_obj = Carbon::create($date);
        return $date_obj->month==12 && $date_obj->day==25 ? true : false;
    }
    /**
     * check if the given date is Boxing Day or not.
     */
    public function checkboxingDay($date)
    {
        $date_obj = Carbon::create($date);
        return $date_obj->month==12 && $date_obj->day==26 ? true : false;
    }
    /**
     * check if the given date is New Year or not.
     */
    public function checkNewYear($date)
    {
        $date_obj = Carbon::create($date);
        return $date_obj->month==1 && $date_obj->day==1 ? true : false;
    }
    /**
     * find next working day of the office.
     */
    public function findNextWorkingDate($date)
    {
        $new_date = $date;
        if(
            $this->checkWeekend($date) || 
            $this->checkChristmas($date) || 
            $this->checkboxingDay($date) || 
            $this->checkNewYear($date)
        )
        {
            $temp_date = Carbon::create($date);
            $temp_date->addDay();
            $new_date = $this->findNextWorkingDate($temp_date->format("Y-m-d"));
        }

        return $new_date;
    }
    /**
     * Check if the office is closed or not on employee birthday.
     */
    public function checkIfOfficeIsClosedOrNot($date)
    {
        if(
            $this->checkWeekend($date) || 
            $this->checkChristmas($date) || 
            $this->checkboxingDay($date) || 
            $this->checkNewYear($date)
        )
        {
            return true;
        }

        return false;
    }
    /**
     * Process the filtered input data, make possible cake days Array depending upon Office Closure.
     */
    public function possibleCakeDaysFromData($input_data)
    {
        $possible_cake_day_array = [];
        foreach($input_data as $data)
        {
            $employee_off_day = $data["dob"];
            if($this->checkIfOfficeIsClosedOrNot($data["dob"]))
            {
                $employee_off_day = $this->findNextWorkingDate($data["dob"]);// Finding Off Day for the employee.
            }

            $temp_date = Carbon::create($employee_off_day);
            $temp_date->addDay();
            $possible_cake_day = $this->findNextWorkingDate($temp_date->format("Y-m-d")); // Finding Possible Cake Day
            
            $possible_cake_day_array[] = [
                'name' => $data['name'],
                'possible_cake_day' => $possible_cake_day,
            ];
        }
        return $possible_cake_day_array;
    }
    public function findIfNextDateIsSameOrNot($date_current,$date_next)
    {
        return $date_current==$date_next;
    }
    public function findIfNextCakeDayIsNextDateOrNot($date_current,$date_next)
    {
        $current = Carbon::create($date_current);
        
        $next = Carbon::create($date_next);
        
        return $current->diffInDays($next,false)==1;
    }
    /**
     * Handle the process the data and confirm it as cake days.
     */
    public function confirmCakeDays(&$possible_cake_day_array)
    {
        $confirm_cake_day_array = [];
        $cake_free_day = '';
        $counter = 0;        
        for($counter = 0;$counter<count($possible_cake_day_array);$counter++)
        {
            $current_cake_day = $possible_cake_day_array[$counter];
            $next_cake_day = '';
            
            if(isset($possible_cake_day_array[$counter+1]))
            {
                $next_cake_day = $possible_cake_day_array[$counter+1];
                if($cake_free_day==$current_cake_day["possible_cake_day"])
                {
                    $current_cake_date_obj = Carbon::create($current_cake_day["possible_cake_day"]);
                    $current_cake_date_obj->addDay();
                    $possible_cake_day = $this->findNextWorkingDate($current_cake_date_obj->format("Y-m-d"));
                    $possible_cake_day_array[$counter]["possible_cake_day"] = $possible_cake_day;
                    $counter--;
                }
                elseif($this->findIfNextDateIsSameOrNot($current_cake_day["possible_cake_day"],$next_cake_day["possible_cake_day"]))
                {
                    $current_cake_date_obj = Carbon::create($current_cake_day["possible_cake_day"]);
                    $current_cake_date_obj->addDay();
                    $cake_free_day = $current_cake_date_obj->format("Y-m-d");
                    $confirm_cake_day_array[] = [
                        'developer_names' => $current_cake_day["name"].", ".$next_cake_day["name"],
                        'cake_date' => $current_cake_day["possible_cake_day"],
                        'cake_type' => 'large',
                        'no_of_cakes' => 1
                    ];
                    unset($possible_cake_day_array[$counter]);
                    unset($possible_cake_day_array[$counter+1]);
                    $possible_cake_day_array = array_values($possible_cake_day_array);
                    $counter--;
                }
                elseif($this->findIfNextCakeDayIsNextDateOrNot($current_cake_day["possible_cake_day"],$next_cake_day["possible_cake_day"]))
                {
                    $next_cake_date_obj = Carbon::create($next_cake_day["possible_cake_day"]);
                    $next_cake_date_obj->addDay();
                    $cake_free_day = $next_cake_date_obj->format("Y-m-d");
                    $confirm_cake_day_array[] = [
                        'developer_names' => $current_cake_day["name"].", ".$next_cake_day["name"],
                        'cake_date' => $next_cake_day["possible_cake_day"],
                        'cake_type' => 'large',
                        'no_of_cakes' => 1
                    ];
                    unset($possible_cake_day_array[$counter]);
                    unset($possible_cake_day_array[$counter+1]);
                    $possible_cake_day_array = array_values($possible_cake_day_array);
                    $counter--;
                }
                else
                {
                    $current_cake_date_obj = Carbon::create($current_cake_day["possible_cake_day"]);
                    $current_cake_date_obj->addDay();
                    $cake_free_day = $current_cake_date_obj->format("Y-m-d");
                    $confirm_cake_day_array[] = [
                        'developer_names' => $current_cake_day["name"],
                        'cake_date' => $current_cake_day["possible_cake_day"],
                        'cake_type' => 'small',
                        'no_of_cakes' => 1
                    ];
                    
                }
            }
            elseif($cake_free_day==$current_cake_day["possible_cake_day"])
            {
                $current_cake_date_obj = Carbon::create($current_cake_day["possible_cake_day"]);
                $current_cake_date_obj->addDay();
                $possible_cake_day = $this->findNextWorkingDate($current_cake_date_obj->format("Y-m-d"));
                $possible_cake_day_array[$counter]["possible_cake_day"] = $possible_cake_day;
                $counter--;
            }
            else
            {
                $current_cake_date_obj = Carbon::create($current_cake_day["possible_cake_day"]);
                $current_cake_date_obj->addDay();
                $cake_free_day = $current_cake_date_obj->format("Y-m-d");
                $confirm_cake_day_array[] = [
                    'developer_names' => $current_cake_day["name"],
                    'cake_date' => $current_cake_day["possible_cake_day"],
                    'cake_type' => 'small',
                    'no_of_cakes' => 1
                ];
                
            }
        }
        return $confirm_cake_day_array;
    }
    /**
     * Handle the uploaded file, process it and insert into the database.
     */
    public function makeConfirmedCakeDays($cake_days_array)
    {
        CakeDays::truncate();
        foreach($cake_days_array as $cake_day)
        {
            CakeDays::create($cake_day);
        }
    }
    /**
     * Handle the uploaded file, process it and insert into the database.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|extensions:txt'
        ]);
        
        if($request->hasFile('file'))
        {
            $file = $request->file('file');
            $processed_input_data = $this->processInputFile(file_get_contents($file));
            if(count($processed_input_data)<=0)
            {
                return response()->json([
                    'error' => 'parcing',
                    'description' => 'error while parcing input file'
                ]);    
            }
            $filtered_input_data=$this->filterInputData($processed_input_data);
            if(count($filtered_input_data)<=0)
            {
                return response()->json([
                    'error' => 'noFutureBirthdays',
                    'description' => 'There are no future birthdays in the input file'
                ]);    
            }
            $possible_cake_day_array = $this->possibleCakeDaysFromData($filtered_input_data);
            $possible_cake_days_column = array_column($possible_cake_day_array, 'possible_cake_day');
            array_multisort($possible_cake_days_column, $possible_cake_day_array);
            $cake_days_array = $this->confirmCakeDays($possible_cake_day_array);
            $this->makeConfirmedCakeDays($cake_days_array);
            
        }

        return response()->json([
            'success' => 'file uploaded'
        ]);
    }



}
