<?php

namespace App\Controllers;

use App\Lang;
//use App\Models\DB;
use App\View;
use App\Query;
use App\Router;
use App\Formater;
use App\Opengraph;
use App\Controllers\Auth;
use App\Models\csv_files;

class Main
{

    function Index(Query $Request, $dat = [])
    {
        $data = Lang::LOAD("index");
        $galls = (new csv_files)->get();
        $data=array_merge($data, $dat);

        View::Show("main", $data);
    }


    function Put(Query $Request)
    {
        if (sizeof($_FILES) != 1 || $_FILES["file"]["type"] != "application/vnd.ms-excel" || $_FILES["file"]["size"] > 1048576) {
            $data['dw_error'] = "Ошибка загрузки файлов";
            return $this->Index($Request, $data);
        }
  
        $allcsv=new csv_files();
        $rt=$allcsv->addCSV($_FILES["file"]);
        
        var_dump($rt);
    }
}
