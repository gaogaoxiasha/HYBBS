<?php
namespace Model;
use HY\Model;

class ForumModel extends Model {
    public function update_int($id,$key='count',$type="+",$size=1){
        $key .= ($type=='+') ? '[+]' : '[-]';
        $this->update(array(
            $key=>$size
        ),array(
            'id'=>$id
        ));
    }
    //判断用户组板块权限
    //$id = 分类ID
    //$group = 用户组ID
    //判断权限类型 vforum vthread trehad post
    public function is_comp($id,$group,$type){
        $json = json_decode(
            $this->find("json",array(
                "id"=>$id
            ))
        ,true);
        //echo $json[$type];
        $str = isset($json[$type]) ? $json[$type] : false ;
        $arr = explode(",",$str);
        foreach ($arr as $v) {
            if($v == $group)
                return false;
        }
        return true;
    }

    public function read(){

    }
    public function read_all(){
        $forum = $this->select("*");
        $tmp = array();
        foreach ($forum as $k => $v) {
            $tmp[intval($v['id'])] = $v;
        }
        return $tmp;
    }
}
