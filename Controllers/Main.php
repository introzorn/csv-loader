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
use Exception;

class Main
{

    /**
     * Главная страница
     * 
     * 
     * @param Query $Request
     * @param array $dat
     * 
     * @return [type]
     */
    function Index(Query $Request, $dat = [])
    {
        $data = Lang::LOAD("index");
        $galls = (new csv_files)->orderBy("id","DESC")->get(10);


    
        $data = array_merge($data, $dat);
        $data['lust_10']="";
        foreach ($galls  as $key => $value) {
            $data['lust_10'].='<a class="csv-files-vkladka" href="'.$Request->BASE_URL.'csv_'.$value['slug'].'">'.$value['name'].' - [ '.$value['add_timestamp'].' ]</a>'."\r\n";
        }


        View::Show("main", $data);
    }


    /**
     * Процедура отвечающая за загрузку файлов
     * 
     * @param Query $Request
     * 
     * @return [type]
     */
    function Put(Query $Request)
    {
        if (sizeof($_FILES) != 1 || $_FILES["file"]["type"] != "application/vnd.ms-excel" || $_FILES["file"]["size"] > 1048576) {
            $data['dw_error'] = "<b style=\"color:red\">Ошибка загрузки файлов</b>";
            return $this->Index($Request, $data);
        }

        $allcsv = new csv_files();
        $rt = $allcsv->addCSV($_FILES["file"]);
        
        if ($rt !== false) {
            Router::Redirect("csv_" . $rt['slug']);
        }
        $data['dw_error'] = "<b style=\"color:red\">Неизвестный формат файла</b>";
        return $this->Index($Request, $data);
    }



    /**
     * Процедура отвечающая за выгрузку файла csv
     * 
     * 
     * @param Query $Request
     * 
     * @return [type]
     */
    function Download(Query $Request)
    {
        try {

            $slug = $Request->GetParam["slug"];
            $allcsv = new csv_files();

            $cvs_file = $allcsv->find(["slug" => $slug]);

            header('Content-Description:' . $cvs_file["name"]);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $cvs_file["name"]);
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Content-Encoding: UTF-8');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            ob_start();
     
            echo "\xEF\xFF\xBF";
            $rt = $allcsv->ExtractRows($slug, 0, 0, "blockread", function ($row) {
                unset($row['id']);
                unset($row['csv_file_id']);
                unset($row['add_timestamp']);
                echo (join(";", array_values($row)) . "\r\n");
                ob_flush();
                flush();
            });


        } catch (\Exception $th) {
            Router::Error(404);
        }
        $slug = $Request->GetParam["slug"];
        ob_end_flush();
    }





    /**
     * Процедура отвечающая за просмотр таблиц
     * 
     * @param Query $Request
     * 
     * @return [type]
     */
    function Show(Query $Request)
    {
        $data = Lang::LOAD("index");



        try {

            $slug = $Request->GetParam["slug"];
            $allcsv = new csv_files();
            $cvs_file = $allcsv->find(["slug" => $slug]);
            if ($cvs_file === false) {
                throw new Exception("404", 404);
            }

            if ($allcsv->queryExec("SHOW TABLES LIKE 'csv_table_$slug'") === false) {
                throw new Exception("404", 404);
            };


            $csv_model = $allcsv->createCSVModel("csv_table_{$cvs_file['id']}", []);

            $grid = $csv_model->orderBy("id")->get();

            $data["csv_slug"] = $cvs_file["slug"];
            $data["csv_name"] = $cvs_file["name"];
            $data["csv_date"] = $cvs_file["add_timestamp"];

            $data["csv_grid"] = '<table class="csv_grid"><thead><tr>' . "\r\n";
            $keys = array_diff(array_keys($grid[0]), ['csv_file_id', 'add_timestamp']);

            foreach ($keys as $key) {
                $data["csv_grid"] .= "<th>{$key}</th>\r\n";
            }

            $data["csv_grid"] .= "</tr></thead><tbody>\r\n";

            foreach ($grid as $row) {
                $data["csv_grid"] .= $row['id'] == 1 ? '<tr  class="first-row styck">' : '<tr>';
                foreach ($keys as $key) {
                    $check = $key == 'id' && $row['id'] == 1 ? '<br><br><input type="checkbox"  class="first-row-check" checked>' : '';

                    $data["csv_grid"] .= "<td>{$row[$key]}$check</td>\r\n";
                }
                $data["csv_grid"] .= "</tr>";
            }

            $data["csv_grid"] .= "</tbody></table>";

            View::Show("show", $data);
        } catch (\Exception $th) {

            Router::Redirect(Router::GetBaseUrl());
        }
    }
}
