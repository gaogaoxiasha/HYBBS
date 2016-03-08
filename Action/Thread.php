<?php
namespace Action;
use HY\Action;

class ThreadAction extends HYBBS {
    public function __construct(){
		parent::__construct();


		$left_menu = array('index'=>'active','forum'=>'');
		$this->v("left_menu",$left_menu);
        {hook a_thread_init}
	}
    public function index(){
        $this->message("没有该文章");
        {hook a_thread_index_1}
    }
    //帖子页面
    public function _empty(){
        {hook a_thread_empty_1}
        if(IS_GET){
            $pageid=intval(isset($_GET['HY_URL'][2]) ? $_GET['HY_URL'][2] : 1) or $pageid=1;

            $id = intval(METHOD_NAME);
            $this->v('id',$id);

            {hook a_thread_empty_2}
            $Thread = M("Thread");



            //获取文章标题等数据
            $thread_data = $Thread->read($id);
            if(empty($thread_data))
                return $this->message("不存在该主题");

            if(!L("Forum")->is_comp($thread_data['fid'],NOW_GROUP,'vthread',$this->_forum[$thread_data['fid']]['json']))
                return $this->message("你没有权限访问这个帖子");


            {hook a_thread_empty_3}
            $User = M("User");
            $thread_data['user']=$User->id_to_user($thread_data['uid']);
            $thread_data['avatar']=$this->avatar($thread_data['user']);

            {hook a_thread_empty_4}
            $Post = S("Post");
            //获取文章内容
            $PostData = $Post->find("*",array('AND'=>array('tid'=>$id,'isthread'=>1)));

            if(empty($PostData))
                return $this->message("文章内容没有找到");

            {hook a_thread_empty_5}
            //获取文章评论列表
            $PostList=array();
            $PostList = $Post->select('*',array(
                'AND'=>array(
                    'tid'=>$id,
                    'isthread'=>0
                ),
                "LIMIT" => array(($pageid-1) * $this->conf['postlist'], $this->conf['postlist']),


            ));

            $i = 0;
            foreach ($PostList as &$v) {
                $v['user']=$User->id_to_user($v['uid']);
                $v['atime_str']=humandate($v['atime']);
                $v['key'] = (($pageid-1)*10) + (++$i);
                $v['avatar']=$this->avatar($v['user']);
            }
            {hook a_thread_empty_6}

            $Thread->update_int($id,'views');

            $count = $thread_data['posts'];
    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);
            {hook a_thread_empty_v}
            $this->v("title",$thread_data['title']);
            $this->v("post_data",$PostData);
            $this->v("pageid",$pageid);
            $this->v("page_count",$page_count);
            $this->v("thread_data",$thread_data);
            $this->v("PostList",$PostList);
            $this->display('thread_index');
        }elseif(IS_POST){
            {hook a_thread_empty_7}
        }

    }
    public function del(){

        {hook a_thread_del_1}

        if(!IS_LOGIN)
            $this->json(array('error'=>false,'info'=>'请登录'));

        //用户组权限判断
		if(!M("Usergroup")->read($this->_user['group'],'del'))
			return $this->json(array('error'=>false,'info'=>'你当前所在用户组无法删除主题'));

        {hook a_thread_del_3}
        $id = intval(X("post.id"));
        $Thread = M("Thread");

        $t_data = $Thread->read($id);
        if(empty($t_data))
            return $this->json(array('error'=>false,'info'=>'该文章无数据'));

        $arr = explode(",",$this->_forum[$t_data['fid']]['forumg']);

        {hook a_thread_del_4}
        //用户组不是 管理员 &&  用户不是文章作者
        if(
            ($this->_user['group'] != C("ADMIN_GROUP")) &&
            ($this->_user['id'] != $t_data['uid']) &&
            !array_search($this->_user['id'],$arr)
        )
            return $this->json(array('error'=>false,'info'=>'你没有权限操作这个主题'));


        $Thread->del($id);

        if($t_data['posts']){ //存在评论
            $Post = M('Post');
            $Post->del_thread_all_post($id);
        }
        {hook a_thread_del_5}
        return $this->json(array('error'=>true,'info'=>'删除成功'));



    }
    public function top(){
        {hook a_thread_top_1}
        if(!IS_LOGIN)
            return $this->json(array('error'=>false,'info'=>'请登录'));
        {hook a_thread_top_2}


        $id = intval(X("post.id"));
        $Thread = M("Thread");
        $data = $Thread->read($id);
        if(empty($data))
            return $this->json(array('error'=>false,'info'=>'没有该文章'));

        //版主权限
        $arr = explode(",",$this->_forum[$data['fid']]['forumg']);
        if(
            $this->_user['group'] != C("ADMIN_GROUP") &&
            !array_search($this->_user['id'],$arr)
        )
            return $this->json(array('error'=>false,'info'=>'没有权限'));
        {hook a_thread_top_3}

        $type = X("post.type");
        $top = X("post.top"); //1 = 板块置顶 2 = 全站置顶
        if($top < 0 || $top > 2){
            return $this->json(array('error'=>false,'info'=>'参数出错'));
        }
        if($top == 2){
            if($this->_user['group'] != C("ADMIN_GROUP"))
                return $this->json(array('error'=>false,'info'=>'你没有权限全站置顶'));
        }
        {hook a_thread_top_4}
        $Thread->update(array(
            'top'=>($type=='on') ? $top : 0
        ),array(
            'id'=>$id
        ));
        $Thread->del_top_cache($data['fid']);
        {hook a_thread_top_5}
        return $this->json(array('error'=>true,'info'=>'置顶成功'));


    }
    {hook a_thread_fun}

}
