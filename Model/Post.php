<?php
namespace Model;
use HY\Model;

class PostModel extends Model {

    // 通过 评论ID 获取评论数据
    public function read($id){
        return $this->find("*",array(
            'id'=>$id
        ));
    }
    //删除 某文章ID 的所有评论以及文章内容
    public function del_thread_all_post($id){
        return $this->delete(array(
            'tid'=>$id
        ));
    }

    //通过 评论过ID 删除评论数据
    public function del($id){
        return $this->delete(array(
            'id'=>$id
        ));
    }
}
