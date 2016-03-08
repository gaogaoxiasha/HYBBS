<?php
namespace Action;
use HY\Action;

class ForumAction extends HYBBS {
    public function __construct(){
		parent::__construct();
        {hook a_forum_init}
		$left_menu = array('index'=>'','forum'=>'active');
		$this->v("left_menu",$left_menu);
	}

    public function index(){
        {hook a_forum_index_v}
        $this->v("title","板块分类首页");
        $data = S("Forum")->select("*");
        $this->v("data",$data);
        $this->display('forum_index');
    }
    public function _empty(){

        {hook a_forum_empty_1}
        $id = intval(METHOD_NAME); //分类ID


        //echo $id;
        //print_r($this->_forum);
        //var_dump(isset($this->_forum[$id]));
        if(!isset($this->_forum[$id]))
            return $this->message("没有此分类!");

        if(!L("Forum")->is_comp($id,NOW_GROUP,'vforum',$this->_forum[$id]['json']))
            return $this->message("你没有权限浏览此分类");

        {hook a_forum_empty_2}
        //分页ID
        $pageid=intval(isset($_GET['HY_URL'][3]) ? $_GET['HY_URL'][3] : 1) or $pageid=1;
        //类型ID
        $type = (isset($_GET['HY_URL'][2]) ? $_GET['HY_URL'][2] : 'new') or $type='new';
        //echo $type;
        $type = strtolower($type);
        if($type != 'new' && $type != 'btime')
			$type='';

        {hook a_forum_empty_3}
        $this->v("type",$type);

        $desc = 'id DESC';
		if($type == 'btime')
			$desc = 'btime DESC'; //最新回复


        $Thread = M("Thread");
        //获取 主题列表
        $data=array();
		$data = $Thread->read_list($pageid,$this->conf['forumlist'],$desc,$id); //$id = 分类ID
        {hook a_forum_empty_4}
		$user_tmp = $user_tmp1= array();
		$User = M("User");

		$Thread->format($data);
        {hook a_forum_empty_5}
        //获取全站置顶缓存
        $top_data=array();
        $top_data = $Thread->get_top_cache();
        if(!$top_data || DEBUG){
            $top_where = array('top'=>2); //全局置顶
            $top_data = $Thread->select("*",$top_where);

            //格式数据显示
            //
            $Thread->format($top_data);
            //写入缓存
            $Thread->put_top_cache($top_data);
        }
        $this->v("top_list",$top_data);

        {hook a_forum_empty_6}
        //获取板块置顶缓存
        $top_f_data = $Thread->get_top_cache($id);
        if(!$top_f_data || DEBUG){
            $top_where = array('AND'=>array('top'=>1,'fid'=>$id)); //全局置顶
            $top_f_data = $Thread->select("*",$top_where);
            //格式数据显示
            //
            $Thread->format($top_f_data);
            //写入缓存
            $Thread->put_top_cache($top_f_data,$id);
        }
        $this->v("top_f_data",$top_f_data);
        {hook a_forum_empty_7}

		$count = S("Forum")->find('count',array('id'=>$id));
		$count = (!$count)?1:$count;
		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

        {hook a_forum_empty_v}
        $this->v("title",$this->_forum[$id]['name']);
		$this->v("pageid",$pageid);
		$this->v("page_count",$page_count);
		$this->v("data",$data);
        $this->v("fid",$id);

		$this->display('forum_thread');
    }
    {hook a_forum_fun}
}
