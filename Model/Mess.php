<?php
namespace Model;
use HY\Model;

class MessModel extends Model {

    public function system_send($uid,$mess){
        $this->insert(array(
            'uid'=>$uid,
            'atime'=>NOW_TIME,

        ));
    }

    public function send($uid,$suid,$title,$mess){
        $this->insert(array(
            'uid'=>$uid,
            'suid'=>$suid,
            'title'=>$title,
            'mess'=>$mess,
            'atime'=>NOW_TIME,

        ));
    }

}
