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


        $id = $this->add(["name" => $file["name"], "slug" => ""]);
        $this->edit($id, ["slug" => dechex($id)]);

        $model = $this->createCSVModel("csv_table_" . $id, $scv->GridKeys);


        $restructedCSV = $scv->restructCSV();

        $rt = $this->UploadCSVtoDB($restructedCSV, $id, $scv->GridKeys);
        unlink($restructedCSV);

        //альтернативный способ импорта 
        if ($rt === false) {
            $scv->parse(function ($csv_asoc, $csv_data) use ($model, $id) {
                $csv_asoc['csv_file_id'] = $id;
                $idd = $model->add($csv_asoc);
            });
        }

        $scv->close();

        return ["slug" => dechex($id)];
    }


    public function createCSVModel($modelName, $colums)
    {

        $model = file_get_contents(__DIR__ . "/table1.php");
        $maket = [];
        foreach ($colums as $i => $col) {
            $maket[] = "'$col' => 'text(512)'";
        }
        $mak = "";
        if (sizeof($maket) > 0) {
            $mak =  join(",\r\n", $maket) . ',';
        }
        $model = str_replace(["class table1", "//#colums", "<?php"], ["class " . $modelName, $mak, ""], $model);

        //die("<pre> $model</pre>");
        eval($model);

        $class = "App\\Models\\" . $modelName;

        return new $class();
    }





    function GetCSVModelBySlug($slug)
    {
        $cvs_file = $this->find(["slug" => $slug]);
        if ($cvs_file === false) {
            return false;
        }

        if ($this->queryExec("SHOW TABLES LIKE 'csv_table_$slug'") === false) {
            return false;
        };


        return $this->createCSVModel("csv_table_{$cvs_file['id']}", []);
    }

    // mode="callback"перебор массива;
    // mode="blockread"перебор массива;

    function ExtractRows($model, $from = 0, $quantity = 0, $mode = "", $callback = null)
    {

        if (gettype($model) === "string") {
            $model = $this->GetCSVModelBySlug($model);
        }

        if ($model === false) {
            return false;
        }




        $limit = '';
        if ($from === 0 && $quantity > 0) {
            $limit = "0, $quantity";
        } else if ($from > 0 && $quantity == 0) {
            $limit = "$from";
        } else if ($from > 0 && $quantity > 0) {
            $limit = "$from, $quantity";
        }




        if ($mode == "") {
            return $model->orderBy("id")->get($limit);
        }
        if ($mode == "callback") {
            $rows = $model->orderBy("id")->get($limit);
            array_map($callback, $rows);
            return true;
        }
        if ($mode == "blockread") {

            while (true) {


                $quantity = $quantity > 0 ? $quantity : 100;
                $rows = $model->orderBy("id")->get("$from, $quantity");
                if ($rows === false) {
                    return true;
                }
                array_map($callback, $rows);
                $from += $quantity + 1;

            }
        }

        return false;
    }



    public function UploadCSVtoDB($filename, $csv_file_id, $csv_colums, $linebreak = "", $delimetr = ";")
    {

        if ($linebreak == "") {
            $linebreak =  ['', '\\n', '\\r\\n'][strlen(PHP_EOL)];
        }

        $t_colum = [];
        $n_colum = [];
        $i = 0;


        foreach ($csv_colums as $key => $value) {
            $i++;
            $t_colum[] = "@col$i";
            $n_colum[] = $value . "=@col$i";
        }

        $t__colum = join(",", $t_colum);
        $n__colum = join(",", $n_colum);
        $filename = addslashes($filename);

        $sql = "LOAD DATA LOCAL INFILE '$filename' INTO TABLE csv_table_{$csv_file_id} 
        FIELDS TERMINATED BY '$delimetr' LINES TERMINATED BY '$linebreak'  
        ($t__colum) set csv_file_id=$csv_file_id, $n__colum ;";


        $this->query("SET GLOBAL local_infile=1;");
        if ($this->query($sql) === false) {
            return false;
        };
        return true;
    }
}
