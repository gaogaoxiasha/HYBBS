<?php
namespace Lib;

class Filecache{
    public function get($name){
        $path = TMP_PATH . 'Filecache';
        if(!is_dir($path))
            mkdir($path);

        if(is_file($path . '/' . $name)){
            return json_decode(file_get_contents($path . '/' . $name),true);
        }
        return false;
    }
    public function read($name){
        $this->get($name);
    }
    public function set($name,$value){
        $path = TMP_PATH . 'Filecache';
        if(!is_dir($path))
            mkdir($path);

        file_put_contents($path . '/' . $name,json_encode($value));
    }
    public function put($name,$value){
        $this->set($name);
    }
    public function del($name){
        $path = TMP_PATH . 'Filecache';
        if(is_file($path . '/' . $name))
            unlink($path . '/' . $name);
    }
}
