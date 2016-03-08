<?php
namespace HY;

class FileCache {
    public $file;
    public function __construct($file){
        $this->file = TMP_PATH.$file.'.json';
    }
    public function read($key){

        if(!file_exists($this->file))
            file_put_contents($this->file,'{}');
        $json = file_get_contents($this->file);
        $arr = json_decode($json,true);

        return isset($arr[$key])? $arr[$key]['atime'] : 0;

    }
    public function put($key){
        if(!file_exists($this->file))
            $json = '{}';
        else
            $json = file_get_contents($this->file);

        $arr = json_decode($json,true);

        $arr[$key]['atime']=$_SERVER['time'];

        file_put_contents($this->file,json_encode($arr));


    }
}
