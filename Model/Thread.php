<?php
namespace Model;
use HY\Model;

class ThreadModel extends Model {
    //通过文章ID 获取文章
    //文章ID
    public function read($id){
        return $this->find("*",array(
            'id'=>$id
        ));
    }
    //删除文章
    //主题ID
    public function del($id){
        $this->delete(array(
            'id'=>$id
        ));
    }
    // 赞数操作 踩操作 评论数量操作
    // 主题ID
    // 数据结构KEY
    // 类型 + - 
    // 数值
    public function update_int($id,$key,$type = "+",$size = 1){
        $key .= ($type=='+') ? '[+]' : '[-]';
        $this->update(array(
            $key=>$size
        ),array(
            'id'=>$id
        ));
    }
    //获取主题列表
    //分页ID
    //获取个数
    //排序
    //分类ID - 指定ID
    //用户ID - 指定用户
    public function read_list($pageid , $size = 10,$order = "id DESC",$fid = -1,$uid = 0 ){
        $data = array(

            "ORDER"=>$order,
            "LIMIT" => array(
                ($pageid-1) * $size,
                $size
            )
        );
        if($fid != -1 && $uid != 0){
            $data['AND']=array(
                'fid'=>$fid,
                'uid'=>$uid,
            );
        }elseif($fid != -1 && $uid == 0){
            $data['fid']=$fid;
        }elseif($fid == -1 && $uid != 0){
            $data['uid']=$uid;
        }




        return $this->select('*',$data);
    }
    //----------搜索主题
    //
    //分页ID
    //获取个数
    //搜索关键字
    //排序方式
    //分类ID
    //用户ID
    // 
    public function search_list($pageid,$size,$key,$order = "id DESC",$fid = -1,$uid = 0){
            $data = $this->select(
                "*",
                array(
                    "title[~]" => $key,
                    "ORDER"=>$order,
                    "LIMIT" => array(
                        ($pageid-1) * $size, 
                        $size
                        )
                    )
                );
            return $data;
    }

    //创建 置顶缓存
    //没有分类ID 则创建全站置顶
    //否则 创建分类ID的置顶缓存
    public function put_top_cache($data,$fid = -1){
        if(empty($data))
            return false;
        $this->fc = L("Filecache");
        $md5 = md5("thread_top".$fid.C("MD5_KEY"));
        $this->fc->set('thread_top_'.$md5,$data);
    }

    //获取 置顶缓存
    //没有分类ID 则获取全站置顶缓存
    //否则 获取分类ID 置顶缓存
    public function get_top_cache($fid = -1){
        $this->fc = L("Filecache");
        $md5 = md5("thread_top".$fid.C("MD5_KEY"));
        return $this->fc->get('thread_top_'.$md5);
    }
    //删除置顶缓存
    public function del_top_cache($fid = -1){
        $this->fc = L("Filecache");
        $md5 = md5("thread_top".$fid.C("MD5_KEY"));
        $this->fc->del('thread_top_'.$md5);
    }

    public function format(&$thread_list){
        $user_tmp = $user_tmp1= array();
        $User = M("User");
        foreach ($thread_list as  &$v){
            if(empty($user_tmp[$v['uid']])){
                $user_tmp[$v['uid']] = $User->id_to_user($v['uid']);
            }
            if($v['buid']){
                if(empty($user_tmp1[$v['buid']])){
                    $user_tmp1[$v['buid']] = $User->id_to_user($v['buid']);

                }
                $v['buser'] = $user_tmp1[$v['buid']];
                $v['buser_avatar'] =$User->avatar($v['buser']);
            }
            //UID获取用户名
            $v['user'] = $user_tmp[$v['uid']];
            //摘要去掉标签
            $v['summary'] = strip_tags($v['summary']);
            //$v['atime'] = humandate($v['atime']);
            $v['avatar']=$User->avatar($v['user']);
            if(!empty($v['img'])){
                $v['image']=explode(",", $v['img']);
                $v['image_count']=count($v['image'])-1;
            }

        }

    }




}
