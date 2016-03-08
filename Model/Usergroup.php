<?php
namespace Model;
use HY\Model;

class UsergroupModel extends Model {
    // $id 用户组ID 返回权限数组
    public function read_json($id){
        $json = $this->select("json",array(
            "id"=>$id
        ));
        return json_decode($json,true);
    }

    // 获取 某用户组的 某权限真假 return bool
    public function read($id,$type){
        $json = json_decode(
            $this->find("json",array(
                "id"=>$id
            ))
        ,true);
        //echo $json[$type];
        return $json[$type];
    }
    public function id_to_name($id){
        return $this->find("name",array(
            'id'=>$id
        ));
    }
}
