<?php
namespace Action;
use HY\Action;

class MyAction extends HYBBS {
    public $menu_action;
    public function __construct(){
		parent::__construct();
        {hook a_my_init}
        $this->view = $this->conf['userview'];

        $this->menu_action = array(
            'index'=>'',
            'thread'=>'',
            'post'=>'',
            'mess'=>'',
            'op'=>''
        );
        //$left_menu = array('index'=>'active','forum'=>'');
		//$this->v("left_menu",$left_menu);

    }

    public function _empty(){
        {hook a_my_empty_1}
        $username   = isset($_GET['HY_URL'][1])?$_GET['HY_URL'][1]:'';
        $method     = isset($_GET['HY_URL'][2])?$_GET['HY_URL'][2]:'index';

        if(empty($username))
            return $this->message("请输入一个用户名称");
        {hook a_my_empty_2}
        $User = M("User"); //实例用户模型
        $id = $User->user_to_id($username); //用户名转ID
        if(!$id)
            return $this->message("不存在该用户");

        {hook a_my_empty_3}
        $this->menu_action[$method] = 'active';
        $this->v('menu_action',$this->menu_action);

        if($method == 'index'){ //用户首页
            {hook a_my_empty_4}
            $thread_data = S("Thread")->select("*",array(
                'uid'=>$id,
                "ORDER"=>'atime DESC',
                'LIMIT'=>5
            ));
            $post_data = S("Post")->select("*",array(
                'AND'=>array(
                    'uid'=>$id,
                    'isthread'=>0
                ),
                "ORDER"=>'atime DESC',
                'LIMIT'=>5
            ));
            {hook a_my_empty_5}
            $this->v("thread_data",$thread_data);
            $this->v("post_data",$post_data);
            $data = $User->read($id);
            $data['avatar'] = $this->avatar($data['user']);
            $this->v("title",$data['user']." 用户中心");
            $this->v('data',$data);
            $this->display('user_index');
        }elseif($method == 'thread'){
            {hook a_my_empty_6}
            $data = $User->read($id);
            $data['avatar'] = $this->avatar($data['user']);

            $Thread = S("Thread");
            $pageid=intval(isset($_GET['HY_URL'][3]) ? $_GET['HY_URL'][3] : 1) or $pageid=1;
            $thread_data = $Thread->select('*',array(
                'uid'=>$id,
                "LIMIT" => array(($pageid-1) * 10, 10),
                "ORDER" => "atime DESC"
            ));

            foreach ($thread_data as &$v) {
                $v['atime']=humandate($v['atime']);
            }
            {hook a_my_empty_7}
            //print_r($thread_data);

            $count = $data['threads'];
    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);
            {hook a_my_empty_8}
            $this->v("title",$data['user']."的主题");
            $this->v("pageid",$pageid);
    		$this->v("page_count",$page_count);
            $this->v('thread_data',$thread_data);
            $this->v('data',$data);
            $this->display('user_thread');
        }elseif($method == 'post'){
            {hook a_my_empty_9}
            $data = $User->read($id);
            $data['avatar'] = $this->avatar($data['user']);

            $Post = S("Post");

            $pageid=intval(isset($_GET['HY_URL'][3]) ? $_GET['HY_URL'][3] : 1) or $pageid=1;
            $post_data = $Post->select('*',array(
                'AND'=>array(
                    'uid'=>$id,
                    'isthread'=>0,
                ),
                "LIMIT" => array(($pageid-1) * 10, 10),
                "ORDER" => "atime DESC"
            ));

            foreach ($post_data as &$v) {
                $v['atime']=humandate($v['atime']);
            }
            {hook a_my_empty_10}

            $count = $data['posts'];
    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

            $this->v("title",$data['user'].'的帖子');
            $this->v("pageid",$pageid);
    		$this->v("page_count",$page_count);
            $this->v('post_data',$post_data);
            $this->v('data',$data);
            $this->display('user_post');
        }elseif($method == 'mess'){
            {hook a_my_empty_11}
            $data = $User->read($id);
            $data['avatar'] = $this->avatar($data['user']);

            $Mess = S("Mess");
            $pageid=intval(isset($_GET['HY_URL'][3]) ? $_GET['HY_URL'][3] : 1) or $pageid=1;
            $mess_data = $Mess->select('*',array(
                'uid'=>$id,
                "LIMIT" => array(($pageid-1) * 10, 10),
                "ORDER"=>"atime DESC",
            ));

            $User=M('User');
            foreach ($mess_data as &$v) {
                $v['avatar']=$this->avatar($User->id_to_user($v['suid']));

            }
            {hook a_my_empty_12}
            $count = $Mess->count(array('uid'=>$id));

    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

            $this->v("title","消息中心");
            $this->v("pageid",$pageid);
    		$this->v("page_count",$page_count);
            $this->v('mess_data',$mess_data);
            $this->v('data',$data);
            $this->display('user_mess');
        }elseif($method == 'op'){
            {hook a_my_empty_13}
            $data = $User->read($id);
            $data['avatar'] = $this->avatar($data['user']);
    
            $this->v('data',$data);
            $this->v("title","消息中心");
            $this->display('user_op');

        }

    }
}
