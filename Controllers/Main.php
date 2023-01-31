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

    function Index(Query $Request){
        $data=Lang::LOAD("index");
        $galls=(new csv_files)->get();

    
        View::Show("main",$data);
    }

}