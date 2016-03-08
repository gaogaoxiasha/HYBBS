<?php
namespace Action;
use HY\Action;

class PostAction extends HYBBS {



	public $tid=0;
	public $title;
	public $content;



	public function __construct() {
		parent::__construct();
		{hook a_post_init}
		if(!IS_LOGIN){
			if(IS_AJAX && IS_POST)
				die($this->json(array('error'=>false,'info'=>'请登录后再操作')));
			else
				die($this->message("请登录后在操作"));

		}
		$left_menu = array('index'=>'active','forum'=>'');
		
		$this->v("left_menu",$left_menu);
	}
	//发表评论
	public function Post(){
		{hook a_post_post_1}
		$this->v('title','发表评论');
		if(!IS_POST)
			return;

		//用户组权限判断
		if(!M("Usergroup")->read($this->_user['group'],'post'))
			return $this->json(array('error'=>false,'info'=>'你当前所在用户组无法发表评论'));

		{hook a_post_post_2}
		$id= intval(X("post.id"));
		if(empty($id))
			return $this->json(array('error'=>false,'info'=>'文章ID不能为空'));
		if(!isset($_POST['content']))
			return $this->json(array('error'=>false,'info'=>'内容不能为空'));
		{hook a_post_post_3}
		$content = $this->uh($_POST['content']);
		//去除image 所有属性
		$content = preg_replace("/<img.*?src=(\"|\')(.*?)\\1[^>]*>/is",'<img src="$2" />', $content);
		//去除泰文音标
		$content = preg_replace( '/\p{Thai}/u' , '' , $content );

		$tmp = strip_tags($content,'<p>');
		if(empty($tmp))
			return $this->json(array('error'=>false,'info'=>'内容不能为空'));
		{hook a_post_post_4}
		//获取文章数据
		$thread_data = S("Thread")->find('*',array('id'=>$id));
		if(!L("Forum")->is_comp($thread_data['fid'],NOW_GROUP,'post',$this->_forum[$thread_data['fid']]['json']))
			return $this->json(array('error'=>false,'info'=>'你没有权限发表'));

		{hook a_post_post_5}
		$this->tid = $id;
		$this->title = $thread_data['title'];

		//发送消息摘要
		$this->content = mb_substr(strip_tags($content), 0,100);

		{hook a_post_post_6}
		//通知@ 用户
		if(M("Usergroup")->read($this->_user['group'],'mess'))
			$content = $this->tag($content);
		$Post = S("Post");
		$Count = M("Count");
		$Post->insert(array(
			'id'	=> $Count->_get("post"),
			'tid'	=> $id,
			'uid'	=> $this->_user['id'],
			'content' => $content,
			'atime'	  => NOW_TIME
		));
		{hook a_post_post_7}
		//更新主题 回复帖子数
		S("Thread")->update(array(
			'posts[+]'=>1, //评论数+1
			'btime'=>NOW_TIME, // 最后评论过时间
			'buid'=>$this->_user['id'], //最后回复者用户ID
		),array(
			'id'=>$id //$id = 主题ID
		));
		{hook a_post_post_8}
		$User = M("User");
		$User->update_int($this->_user['id'],'posts','+');
		$User->update_int($this->_user['id'],'gold','+',$this->conf['gold_post']);
		$this->_user['posts']++;
		if($thread_data['uid'] != $this->_user['id']){
			M("Mess")->send(
				$thread_data['uid'],
				$this->_user['id'],
				'<a style="color:#478fca" href="'.WWW.'thread/'.$id.'">'.$this->_user['user'].'回复了你的主题：'.$thread_data['title'].'</a>',
				$this->content
			);
		}
		{hook a_post_post_9}


		cookie('HYBBS_HEX',L("User")->set_cookie($this->_user));

		//最近评论
		$tmp_post = $this->fc->get('post_tmp_'.md5("tmp_post".C("MD5_KEY"))) or $tmp_post= array();
		if(count($tmp_post) >= $this->conf['tmp_post'])
			unset($tmp_post[count($tmp_post)-1]);
		array_unshift($tmp_post,array(

			'tid'	=> $id,
			'title' =>$this->title,
			'atime'	=>NOW_TIME,
			'uid'	=>$this->_user['id'],
			'user'	=>$this->_user['user']
		));
		$this->fc->set('post_tmp_'.md5("tmp_post".C("MD5_KEY")),$tmp_post);
		//最近评论结束
		{hook a_post_post_v}
		return $this->json(array('error'=>true,'info'=>'发表成功'));

	}
	//发表主题
	public function Index(){
		{hook a_post_index_1}
		$this->v('title','发表主题');
        if(IS_GET){ //显示发表主题模板
			{hook a_post_index_2}
            $Forum = S("Forum");
            $data = $Forum->select("*");

			{hook a_post_index_3}
            $this->v("forum",$data);
    		$this->display('post_index');
        }elseif(IS_POST){ //POST发表主题
			{hook a_post_index_4}
			if(!M("Usergroup")->read($this->_user['group'],'thread'))
				return $this->json(array('error'=>false,'info'=>'你当前所在用户组无法发表主题'));


            $forum = intval(X("post.forum"));
            $title = trim(X("post.title"));
            //去除泰文音标
			$title = preg_replace( '/\p{Thai}/u' , '' , $title );

            $content=$this->uh(X('post.content'));
            $content=preg_replace( '/\p{Thai}/u' , '' , $content );
            {hook a_post_index_5}
			$tmp = strip_tags($content,'<p>');
            if(empty($tmp))
				return $this->json(array('error'=>false,'info'=>'内容不能为空'));

			{hook a_post_index_6}
            if(mb_strlen($title) < 5)
				return $this->json(array('error'=>false,'info'=>'标题不能少于5个字符'));

			if($forum < 0 ){
				return $this->json(array('error'=>false,'info'=>'请选择一个分类,板块'));
			}
			{hook a_post_index_7}
			//用户组在分类下的权限判断
			if(!L("Forum")->is_comp($forum,NOW_GROUP,'thread',$this->_forum[$forum]['json']))
				return $this->json(array('error'=>false,'info'=>'你没有权限发表'));

			{hook a_post_index_8}


            if(!isset($this->_forum[$forum])){
				if(empty($this->_forum[$forum]['id']))
					return $this->json(array('error'=>false,'info'=>'不存在该分类'));
			}



            $Count = M("Count");
            $id = $Count->_get('thread');
			//echo $id;

            //去除image 所有属性
            $content = preg_replace("/<img.*?src=(\"|\')(.*?)\\1[^>]*>/is",'<img src="$2" />', $content);


			{hook a_post_index_9}
            //获取所有图片地址
			$pattern="/\<img.*?src\=\"(.*?)\"[^>]*>/i";
			preg_match_all($pattern,$content,$match);
			$img = '';
			$sz=0;
			if(isset($match[1][0])){
				foreach ($match[1] as $v) {
					$img.=$v;
					$img.=",";
					if($sz>4)
						break;
					$sz++;
				}
			}

			//发送消息 摘要
			$this->content = mb_substr(strip_tags($content), 0,100);

			{hook a_post_index_10}
			// 权限判断是否可 @
			if(M("Usergroup")->read($this->_user['group'],'mess'))
				$content = $this->tag($content); //@ 用户函数

            $Thread = S("Thread");
            $Thread->insert(array(
                'id'=>$id,
                'fid'=>$forum,
                'uid'=>$this->_user['id'],
                'title'=>$title,
                'summary'=>mb_substr(strip_tags($content), 0,100),
				'atime'	=>NOW_TIME,
				'btime'	=>NOW_TIME,

				'img'	=>$img,
            ));

            $Post = S("Post");
            $Post->insert(array(
                'id'	=> $Count->_get("post"),
				'tid'	=> $id,
				'uid'	=> $this->_user['id'],
				'isthread'=> 1,
				'content' => $content,
				'atime'	  => NOW_TIME
            ));
            {hook a_post_index_11}

			$User = M("User");
			//用户增加 主题数
			$User->update_int($this->_user['id'],'threads','+');

			//用户增加 金钱
			$User->update_int($this->_user['id'],'gold','+',$this->conf['gold_thread']);

			//分类板块 帖子数量++
			M("Forum")->update_int($forum);

			$this->_user['threads']++;

			cookie('HYBBS_HEX',L("User")->set_cookie($this->_user));

			//临时文章
			$tmp_thread = $this->fc->get('thread_tmp_'.md5("tmp_thread".C("MD5_KEY"))) or $tmp_thread= array();
			{hook a_post_index_12}
			if(count($tmp_thread) >= $this->conf['tmp_thread'])
				unset($tmp_thread[count($tmp_thread)-1]);
			array_unshift($tmp_thread,array(
				'id'	=>$id,
				'title'	=>$title,
				'img'	=>$img,
				'atime'	=>NOW_TIME,
				'uid'	=>$this->_user['id'],
				'user'	=>$this->_user['user']
			));
			$this->fc->set('thread_tmp_'.md5("tmp_thread".C("MD5_KEY")),$tmp_thread);
			//临时文章结束
			//
			//临时图文
			if(!empty($img)){
				$tmp_image = $this->fc->get('image_tmp_'.md5("tmp_image".C("MD5_KEY"))) or $tmp_image= array();
				{hook a_post_index_13}
				if(count($tmp_image) >= $this->conf['tmpimage'])
					unset($tmp_image[count($tmp_image)-1]);
				array_unshift($tmp_image,array(
					'id'	=>$id,
					'title'	=>$title,
					'img'	=>$img,
					'atime'	=>NOW_TIME,
					'uid'	=>$this->_user['id'],
					'user'	=>$this->_user['user']
				));
				$this->fc->set('image_tmp_'.md5("tmp_image".C("MD5_KEY")),$tmp_image);
			}
			




			{hook a_post_index_v}
            $this->json(array('error'=>true,'info'=>'发表成功','id'=>$id));




        }

	}

	//过滤
	private function uh($str)
	{

		$farr = array(
			"/<(?)(script|style|html|body|title|link|meta)([^>]*?)>/isu",
			"/(<[^>]*)on[a-za-z]+s*=([^>]*>)/isu",
		);
		$tarr = array(
			" ",
			" ",
		);
		$str = preg_replace( $farr,$tarr,$str);
		$str = preg_replace('/style=".*?"/i', '', $str); //过滤自定义样式
		$str = preg_replace('/(<[^>]*)src="data:image\/.*?"([^>]*>)/i', '', $str); // 过滤 转码图片字节
		return $str;
	}
	//@事件
	private function tag($content){

		return preg_replace_callback('/@([^:|： @<&])+/',array($this, 'taga'),$content);
	}
	//''
	private function taga($tagStr){
		{hook a_post_taga_1}
		//print_r($tagStr);
		if(is_array($tagStr)) $tagStr = $tagStr[0];

		$tagStr = stripslashes($tagStr);
		$user = substr($tagStr,1);
		$User = M("User");
		$Mess = M("Mess");
		//echo $user,'|',$this->_user['user'];
		static $tmp_user=array(); //@发送一次
		if($user != $this->_user['user']){
			if(isset($tmp_user[$user]))
				return $tagStr;
			if($User->is_user($user)/* && isset($tmp_user[$user])*/){ //判断用户是否存在
				$tmp_user[$user]=true;
				//echo "|".$user;
				$Mess->send(

					$User->user_to_id($user),
					$this->_user['id'],

					'<a style="color:#478fca;" href="'.WWW.'thread/'.$this->tid.'">'.$this->_user['user'].'@了你 在主题:'.$this->title.'</a>',
					$this->content
				);
				//echo '存在';
				return '<span style="margin-right:10px" class="label label-primary">'.$tagStr.'</span>';
			}
		}


		//$tagStr = str_replace('@','',$tagStr);
		return $tagStr;

	}
	//图片上传
	public function upload(){
		{hook a_post_upload_1}
		if(!M("Usergroup")->read($this->_user['group'],'upload'))
			return $this->json(array("success"=>false,'msg'=>"用户组禁止上传图片!","file_path"=>''));
		header("Content-Type:text/html;charset=utf-8");
	    error_reporting( E_ERROR | E_WARNING );
	    date_default_timezone_set("Asia/chongqing");

		{hook a_post_upload_2}
		$upload = new \Lib\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小  3M
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg', ".bmp");// 设置附件上传类型
        $upload->rootPath  =      INDEX_PATH. "upload/userfile/".$this->_user['id']."/"; // 设置附件上传根目录

        $upload->replace    =   true;
        $upload->autoSub    =   false;
        $upload->saveName   =   md5($this->_user['user'] . NOW_TIME.mt_rand(1,9999)); //保存文件名
		if(!is_dir(INDEX_PATH. "upload"))
			mkdir(INDEX_PATH. "upload");
		if(!is_dir(INDEX_PATH. "upload/userfile"))
			mkdir(INDEX_PATH. "upload/userfile");
        if(!is_dir($upload->rootPath)){
        	mkdir($upload->rootPath);
        }
		{hook a_post_upload_3}
		$info   =   $upload->upload();

		{hook a_post_upload_4}
		//90 缩略图
		//$image = new \Lib\Image();
        //$image->open(INDEX_PATH. "upload/userfile/".$this->_user['id']."/".$info['upfile']['savename']);
		//$image->thumb(90, 90,\Think\Image::IMAGE_THUMB_CENTER)->save(INDEX_PATH . "upload/".$this->_user['uid']."/".$info['upfile']['savename'].".90.jpg");
		$d=array("success"=>true,'msg'=>"上传成功!","file_path"=>'');
		if(!$info) {
			$d['success']	= false;
        	$d['msg']		= $upload->getError();
		}else{
			$d['file_path'] = WWW . "/upload/userfile/".$this->_user['id']."/".$info['upfile']['savename'];

		}
		{hook a_post_upload_v}
		$this->json($d);

	}
	public function edit(){
		{hook a_post_edit_1}
		$this->v('title','编辑帖子内容');
		if(IS_POST){
			{hook a_post_edit_2}
			$id = intval(X("post.id"));
			$content=$this->uh(X('post.content'));

			$content = preg_replace( '/\p{Thai}/u' , '' , $content );
			$tmp = strip_tags($content,'<p>');
			if(empty($tmp))
				return $this->json(array('error'=>false,'info'=>'内容不能为空'));
			{hook a_post_edit_3}
			$Post = S("Post");
			$b = $Post->has(array(
				'AND'=>array(
					'id'=>$id,
					'uid'=>$this->_user['id']
				)
			));
			if(!$b && $this->_user['group'] != C("ADMIN_GROUP"))
				return $this->json(array('error'=>false,'info'=>'NO NO NO 你想太多了!'));
			{hook a_post_edit_4}
			$Post->update(array(
				'content'=>$content
			),array(
				'id'=>$id
			));

			return $this->json(array('error'=>true,'info'=>'修改成功'));
		} //End Post
		{hook a_post_edit_5}
		$id = intval(X("get.id"));
		$Post = S("Post");
		$b = $Post->has(array(
			'AND'=>array(
				'id'=>$id,
				'uid'=>$this->_user['id']
			)
		));

		if(!$b && $this->_user['group'] != C("ADMIN_GROUP"))
			return $this->message('NO NO NO 你想太多了!');
		{hook a_post_edit_6}
		$data = $Post->find("*",array(
			'id'=>$id
		));
		{hook a_post_edit_7}
		$this->v('id',$id);
		$this->v("data",$data);
        $this->display("edit_post");

	}
	//投票
	public function vote(){
		{hook a_post_vote_1}
		$id=intval(X("post.id"));
		$type=X("post.type");


		$i = $this->iptoint(CLIENT_IP);


		if($type!='thread1' && $type!='thread2' && $type!='post1' && $type!='post2' )
			return false;

		//thread,1,

		{hook a_post_vote_2}
		$Vote = S("Vote");
		$atime = $Vote->find('atime',array('AND'=>array(
			'name'=>$type,
			'ip'=>$i,
			'id'=>$id
		)));
		//$data = $Vote->where("name=\"{$type}\" AND ip={$i} AND id={$id}")->find();
		if(($atime+604800) > NOW_TIME)
			return $this->json(array("info"=>false,"content"=>"你赞过了"));

		{hook a_post_vote_3}
		if(empty($atime)){
			if($type=='thread1'){  //主题赞
					S("Thread")->update(array('goods[+]'=>1),array('id'=>$id));
			}else if($type=='thread2'){//主题踩
					S("Thread")->update(array('nos[+]'=>1),array('id'=>$id));
			}else if($type=='post1'){ // 评论赞
					S("Post")->update(array('goods[+]'=>1),array('id'=>$id));
			}else if($type=='post2'){ //评论踩
					S("Post")->update(array('nos[+]'=>1),array('id'=>$id));
			}else{

			}
			$Vote->insert(array(
				"name"=>$type,
				"ip"=>$i,
				"id"=>$id,
				"atime"=>NOW_TIME
			));

			return $this->json(array("info"=>true,"content"=>"success"));
		}
		{hook a_post_vote_4}
		return $this->json(array("info"=>false,"content"=>"你赞过了"));
	}
	//将IP转换为数字
	private	function iptoint($ip){

	    $ip_arr=explode('.',$ip);//分隔ip段
		$ipstr='';
	    foreach ($ip_arr as $value)
	    {
	        $iphex=dechex($value);//将每段ip转换成16进制
	        if(strlen($iphex)<2)//255的16进制表示是ff，所以每段ip的16进制长度不会超过2
	        {
	            $iphex='0'.$iphex;//如果转换后的16进制数长度小于2，在其前面加一个0
	        //没有长度为2，且第一位是0的16进制表示，这是为了在将数字转换成ip时，好处理
	        }
	        $ipstr.=$iphex;//将四段IP的16进制数连接起来，得到一个16进制字符串，长度为8
	    }
	    return hexdec($ipstr);//将16进制字符串转换成10进制，得到ip的数字表示
	}
	public function del(){
		{hook a_post_del_1}
		if(!IS_LOGIN)
            $this->json(array('error'=>false,'info'=>'请登录'));

		//用户组权限判断
		if(!M("Usergroup")->read($this->_user['group'],'del'))
			return $this->json(array('error'=>false,'info'=>'你当前所在用户组无法删除评论'));
		{hook a_post_del_2}
		$id = intval(X("post.id"));
        $Post = M("Post");

		//获取 评论数据
        $p_data = $Post->read($id);
        if(empty($p_data))
            return $this->json(array('error'=>false,'info'=>'不存在此评论'));
        {hook a_post_del_3}
		//获取 评论的板块ID
		$fid = M("Thread")->find('fid',array(
		    'id'=>$p_data['tid']
		));

		$arr = explode(",",$this->_forum[$fid]['forumg']);

		{hook a_post_del_4}
        //用户组不是 管理员 &&  用户不是文章作者
        if(
			($this->_user['group'] != C("ADMIN_GROUP")) &&
			($this->_user['id'] != $p_data['uid']) &&
			!array_search($this->_user['id'],$arr)
		)
            return $this->json(array('error'=>false,'info'=>'你没有权限操作这个评论'));


        $Post->del($id);
		M("Thread")->update_int($p_data['tid'],'posts','-');
		{hook a_post_del_5}
        return $this->json(array('error'=>true,'info'=>'删除成功'));
	}
	{hook a_post_fun}

}
