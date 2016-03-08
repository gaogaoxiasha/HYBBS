<?php
namespace Action;
use HY\Action;

class IndexAction extends HYBBS {
	public function __construct(){
		parent::__construct();
		{hook a_index_init}
		$left_menu = array('index'=>'active','forum'=>'');
		$this->v("left_menu",$left_menu);
	}
	public function Index(){
		{hook a_index_index_1}

		$this->v('title',$this->conf['title']);

		$pageid=intval(X('get.pageid')) or $pageid=1;
		$type = X('get.type') or $type='New';
		if($type != 'New' && $type != 'Btime')
			$type='';
		$this->v("type",strtolower($type));
		$Thread = M("Thread");
		$desc = 'id DESC';
		if($type == 'Btime')
			$desc = 'btime DESC'; //最新回复
		//获取主题列表
		$data=array();
		$data = $Thread->read_list($pageid,$this->conf['homelist'],$desc);
		$Thread->format($data);
		{hook a_index_index_2}

		//获取置顶缓存
		$top_data=array();
		$top_data = $Thread->get_top_cache();
		if(!$top_data || DEBUG){
			$top_where = array('top'=>2); //全局置顶
	        $top_data = $Thread->select("*",$top_where);

	        

	        //格式数据显示
	        
	        $Thread->format($top_data);
	        //写入缓存
			$Thread->put_top_cache($top_data);
		}
		//End
		{hook a_index_index_3}

		$count = M("Count")->xget('thread');
		$count = (!$count)?1:$count;
		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);


		{hook a_index_index_v}
		$this->v("pageid",$pageid);
		$this->v("page_count",$page_count);
		$this->v("data",$data);
		$this->v("top_list",$top_data);

		$this->display('index_index');
	}
	public function test(){


		$arr = array(
			array(
				'name'=>'hy_qq_login',
				'title'=>'QQ登录',
				'image'=>array('1.png','1png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_qq_test',
				'title'=>'QQ登录test',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
		);
		$this->jsonp($arr);



	}
	public function downview(){
		$arr = array(
			array(
				'name'=>'hy_qq_login',
				'title'=>'QQ登录',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_qq_test',
				'title'=>'QQ登录test',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
			array(
				'name'=>'hy_message',
				'title'=>'消息模板',
				'image'=>array('/upload/userfile/1/6cd52dfe6403f7bcac1f2b6ece38e1c1.png','/upload/userfile/1/92dfdefaebeb62365ff10bf4343d65d3.png','/upload/userfile/1/3587a4d9e251915f3016866b70976347.png'),
				'icon'=>'qq',
				'mess'=>'QQ登录描述',
				'user'=>'krabs',
			),
		);
		$this->jsonp($arr);
	}
	public function test1(){
		$zip = L("Zip");

		var_dump($zip->unzip(INDEX_PATH . 'test.zip',INDEX_PATH));
	}
	{hook a_index_fun}
}
