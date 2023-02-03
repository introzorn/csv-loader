<?php

// Не изменяйте этот файл

namespace App\Models;
use App;
use App\Model as M;
class table1 extends App\Model{
    // public $CHARSET='utf8mb4'; 
    // public $COLLATE='unicode_ci';

    public function MIGRATE() //миграции 
    {
    
        $this->TABLE = [
        'id' => 'int(11) NOT NULL AUTO_INCREMENT',
        'csv_file_id' => 'int(11) NOT NULL',
        //#colums
        // 'text' => 'text(1080) NOT NULL',
        'PRIMARY KEY'=>'id',
        'CHARSET'=>'utf8'];
    }


}





