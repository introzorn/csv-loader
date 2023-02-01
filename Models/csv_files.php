<?php

namespace App\Models;

use App;
use App\Model as M;
use App\Contracts\CSV_Parser;

class csv_files extends App\Model
{
    // public $CHARSET='utf8mb4'; 
    // public $COLLATE='unicode_ci';

    public function MIGRATE() //миграции 
    {

        $this->TABLE = [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'slug' => 'char(255) NOT NULL',
            'name' => 'char(255) NOT NULL',
            'description' => 'text(1080)',
            'PRIMARY KEY' => 'id',
            'CHARSET' => 'utf8'
        ];
    }


    public function addCSV($file)
    {
   
        $scv = new CSV_Parser();
       
        if ($scv->load($file["tmp_name"]) === false) {
         
            return false;
        }

        echo ('<table>');
$i=0;
        $scv->parse(function ($csv_asoc, $csv_data)use(&$i) {
             echo ("<tr><td>");
            echo (join("</td><td>", $csv_data));
            echo ("</td></tr>");
           $i++;
        });

        echo ('</table>');

        echo($i);
        die;

        $id = $this->add(["name" => $file["name"], "slug" => ""]);
        $this->edit($id, ["slug" => dechex($id)]);




        return true;
    }
}
