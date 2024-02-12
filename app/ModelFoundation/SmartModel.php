<?php

namespace App\ModelFoundation;

use Illuminate\Database\Eloquent\Model;

class SmartModel extends Model
{

    //Containing the name of table
    protected $table;

    //Containing last insert query's insert_id
    public $insert_id;

    //Containing sent data for queries
    public $data;

    //Containing current record data. use for edit usually
    public $current;

    //contain current pagination page
    public $page = 1;

    //default pagination limit
    public $per_page = 10;

    //contain num rows on select for paging and view search result ect
    public $num_rows = 0;

    //defines group by
    public $group_by = false;

    //defines sort fields
    public $sorts = 0;

    //defines filters fields for use in where cause
    public $filters = array();

    public $fields = array();


    /*
     *Class Constructor
    */
    public function __construct(){
        parent::__construct();


    }


    public function insert($name){

        return $name;


    }

}
