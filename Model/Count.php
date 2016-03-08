<?php
namespace Model;
use HY\Model;

class CountModel extends Model {

    public function _get($name){
//echo $name;
         $this->update(array('v[+]'=>1),array("name"=>$name));
        return $this->find('v',array('name'=>$name));

    }
    public function xget($name){
        return $this->find('v',array('name'=>$name));
    }
}
