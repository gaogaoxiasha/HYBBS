<?php

namespace Action;

use HY\Action;

class AdminAction extends HYBBS {
    public $menu_action =array();

    public function __construct(){
        parent::__construct();

        {hook a_admin_init}
        //模板分组 admin 文件夹
        $this->view = 'admin';


        if(!IS_LOGIN)
            exit('请登录前台!');
        session('[start]');
        $md5 = session('admin');
        //echo $md5.'|';
        if(empty($md5)){
            $this->login();
            exit();
        }

        $this->menu_action = array(
            'index'=>'',
            'forum'=>'',
            'user'=>'',
            'thread'=>'',
            'view'=>'',
            'op'=>'',
            'code'=>''
        );


    }


    public function index(){
        $this->menu_action['index'] = ' active open';
        $this->v("menu_action",$this->menu_action);
        {hook a_admin_index_v}
        if(IS_POST){
            $one1 = X("post.one1");
            $one2 = X("post.one2");
            $one3 = X("post.one3");
            if($one1){// tmp action view

                  $dh=opendir(TMP_PATH);
                  //找出所有".svn“ 的文件夹：
                  while ($file=readdir($dh)) {
                    if($file != '.' && $file != '..' && $file !='index.html' && !is_dir(TMP_PATH . $file)){
                        unlink(TMP_PATH . $file);
                    }
                  }




            }
            if($one2){ //plugin cache
                if(is_dir(TMP_PATH .'plugin_tmp'))
                    deldir(TMP_PATH .'plugin_tmp');
            }
            if($one3){ //data cache
                if(is_dir(TMP_PATH .'Filecache'))
                    deldir(TMP_PATH .'Filecache');
            }


            return $this->mess('清理完成');
        }

        $this->display('index');

    }
    public function login(){
        {hook a_admin_index_1}
        if($this->_user['group'] != C("ADMIN_GROUP"))
            exit('你的账号不属于管理员!');
        if(IS_GET){
            {hook a_admin_login_2}
            $this->display("login");
        }
        elseif(IS_POST){
            {hook a_admin_login_3}
            $pass = X("post.pass");

            if(L("User")->md5_md5($pass, $this->_user['salt']) == $this->_user['pass']){


                session('admin','admin');

                header('Location: /admin.html');
            }
            echo '密码错误';
        }
    }
    public function out(){
        {hook a_admin_out_v}
        session('[destroy]');
    }

    public function forum(){
        $this->menu_action['forum'] = ' active open';
        $this->v("menu_action",$this->menu_action);

        {hook a_admin_forum_1}

        if(IS_POST){

            $gn = intval(X("post.gn"));
            $id = intval(X("post.id"));
            $name = X("post.name");
            $fid = intval(X("post.fid"));

            {hook a_admin_forum_2}


            if(empty($gn) || empty($name))
                return $this->mess("参数不完整");

            $F = S("Forum");
            if($gn == '1') //添加分类
            {
                $F->insert(array('id'=>$id,"name"=>$name,'fid'=>$fid));
                return $this->mess("添加成功");
            }elseif($gn == '2'){ //修改分类
                $iid = intval(X("post.iid"));
                if($iid < 0 )
                    return $this->mess("参数不完整 Error = 22!");
                $F->update(array('id'=>$id,'name'=>$name,'fid'=>$fid),array('id'=>$iid));
                return $this->mess("修改成功");
            }
            return $this->mess("参数不完整 Error = 2");
        }else{
            {hook a_admin_forum_3}
            $F = S("Forum");
            $pageid=intval(X('get.pageid')) or $pageid=1;

            $data1 = $F->select("*");
            $data = $F->select("*",array(
                "LIMIT" => array(($pageid-1) * 10, 10)
            ));
            $count = $F->count();
            $count = (!$count)?1:$count;
            $page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

            {hook a_admin_forum_v}
            $this->v("pageid",$pageid);
            $this->v("page_count",$page_count);
            $this->v("data",$data);
            $this->v("data1",$data1);
            $this->display("forum");
        }



    }
    //用户管理
    public function user(){
        $this->menu_action['user'] = ' active open';
        $this->v("menu_action",$this->menu_action);

        {hook a_admin_user_1}
        if(IS_POST){
            $gn = intval(X("post.gn"));
            if($gn=='2'){ //添加用户

                $user = X("post.user");
                $pass = X("post.pass");
                $email = X("post.email");

                {hook a_admin_user_2}
                $User = M("User");
                if($User->is_user($user))
                    return $this->mess("账号已经存在 Error = 1!");
                if($User->is_email($email))
                    return $this->mess("邮箱已经存在 Error = 2!");

                $User->add_user($user,$pass,$email);
                {hook a_admin_user_3}
                return $this->mess("添加账号成功");

            }elseif($gn=='3'){ //修改用户
                $id = intval(X("post.id"));
                $user = X("post.user");
                $pass = X("post.pass");
                $group = X("post.group");
                $email = X("post.email");

                {hook a_admin_user_4}

                $User = M("User");
                $data = $User->read($id);

                if($data['user'] != $user){
                    if($User->is_user($user))
                        return $this->mess("账号已经存在 Error =3!");
                }

                if($data['email'] != $email){
                    if($User->is_email($email))
                        return $this->mess("邮箱已经存在 Error = 4!");
                }
                $xiu = array(
                    'user'=>$user,
                    'email'=>$email,
                    'group'=>$group

                );
                if(!empty($pass)){
                    $xiu['pass'] = L("User")->md5_md5($pass,$data['salt']);
                }
                $User->update($xiu,array('id'=>$id));
                {hook a_admin_user_5}
                return $this->mess("修改成功");


            }elseif($gn == '4'){ //删除用户
                {hook a_admin_user_6}
                $id = intval(X("post.id"));
                $User = S("User");
                $User->delete(array(
                    'id'=>$id,
                ));

                S("Thread")->delete(array('uid'=>$id));
                S("Post")->delete(array('uid'=>$id));
                S("Mess")->delete(array('uid'=>$id));
                S("Mess")->delete(array('suid'=>$id));
                return $this->json(array('error'=>true,'info'=>'删除成功'));
            }
            return;
        }

        {hook a_admin_user_7}
        $user = X("get.user");
        if(!empty($user)){ //搜索用户

            $gn = intval(X("get.gn"));

            $User = S("User");
            if($gn=="1"){
                //echo $user;

                $pageid=intval(X('get.pageid')) or $pageid=1;


                $data = $User->select("*",array(

                    "OR" => array(
                        'user[~]'=>$user,
                        "email[~]" => $user
                    ),
                    "LIMIT" => array(($pageid-1) * 10, 10)

                ));
                //print_r($data);

                $count = $User->count(array(
                    "OR" => array(
                        'user[~]'=>$user,
                        "email[~]" => $user
                    ),
                ));
        		$count = (!$count)?1:$count;
        		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

                $this->v("fj","&user=$user&gn=1");
                $this->v("pageid",$pageid);
        		$this->v("page_count",$page_count);

                $this->v('data',$data);
                return $this->display("user");
            }



        }else{
            {hook a_admin_user_8}
            $User = S("User");

            $pageid=intval(X('get.pageid')) or $pageid=1;
            $data = $User->select("*",array(
                "ORDER"=>"id DESC",
                "LIMIT" => array(($pageid-1) * 10, 10)
            ));

            $count = $User->count();
    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

            {hook a_admin_user_v}
            $this->v("fj","");
    		$this->v("pageid",$pageid);
    		$this->v("page_count",$page_count);
            $this->v('data',$data);
            $this->display("user");
        }

    }
    //用户组
    public  function usergroup(){
        $this->menu_action['user'] = ' active open';
        $this->v("menu_action",$this->menu_action);
        {hook a_admin_usergroup_1}
        if(IS_GET){
            {hook a_admin_usergroup_2}
            $data = S("Usergroup")->select("*");

            foreach ($data as &$v) {
                $v['json']=json_decode($v['json'],true);
            }
            {hook a_admin_usergroup_v}
            $this->v("data",$data);
            $this->display('usergroup');
        }elseif(IS_POST){
            {hook a_admin_usergroup_3}
            $gn = intval(X("post.gn"));
            if($gn == 1){ //添加用户组
                {hook a_admin_usergroup_4}
                S("Usergroup")->insert(array(
                    'id'=>intval(X("post.id")),
                    'name'=>X("post.name"),
                    'json'=>json_encode(array(
                        'thread'=>true,
                        'post'=>true,
                        'upload'=>true,
                        'mess'=>true,
                        'del'=>true,
                    ))
                ));
                return $this->mess("添加成功");

            }elseif($gn == 2){ //修改用户组
                {hook a_admin_usergroup_5}
                S("Usergroup")->update(array(
                    'id'=>intval(X("post.id")),
                    'name'=>X("post.name"),

                ),array(
                    'id'=>intval(X("post.iid"))
                ));
                return $this->mess("修改成功");
            }elseif($gn == 3){ //编辑权限
                {hook a_admin_usergroup_6}
                $id = intval(X("post.id"));
                $type = X("post.type");
                $b = X("post.b");
                $UG = S("Usergroup");
                $json = $UG->find("json",array(
                    'id'=>intval(X("post.id")),
                ));
                if(empty($json))
                    return $this->json(array('error'=>false,'info'=>'修改失败'));
                $data = json_decode($json,true);

                $data[$type] = $b ? 0 : 1;
                $UG->update(array(
                    'json'=>json_encode($data)
                ),array(
                    'id'=>$id
                ));
                return $this->json(array('error'=>true,'info'=>'修改成功'));

                //print_r($data);


            }
        }
    }
    //文章管理
    public function thread(){
        $this->menu_action['thread'] = ' active open';
        $this->v("menu_action",$this->menu_action);

        {hook a_admin_thread_1}
        if(IS_GET){
            {hook a_admin_thread_2}
            $Thread = S("Thread");
            $forum_data = S("Forum")->select("*");
            $pageid=intval(X('get.pageid')) or $pageid=1;
            $data = $Thread->select("*",array(
                "ORDER"=>"id DESC",
                "LIMIT" => array(($pageid-1) * 10, 10)
            ));

            $count = $Thread->count();
    		$count = (!$count)?1:$count;
    		$page_count = ($count % 10 != 0)?(intval($count/10)+1) : intval($count/10);

            {hook a_admin_thread_3}
            $User = M("User");
            $user_tmp = array();
            foreach ($data as &$vv) {
                if(empty($user_tmp[$vv['uid']])){
    				$user_tmp[$vv['uid']] = $User->id_to_user(intval($vv['uid']));
    			}
                $vv['user'] = $user_tmp[$vv['uid']];
            }
            //print_r($data);

            $forum = array();
            foreach ($forum_data as $v) {

                $forum[$v['id']]=$v;

            }

            {hook a_admin_thread_v}
            $this->v("fj","");
    		$this->v("pageid",$pageid);
    		$this->v("page_count",$page_count);
            $this->v("forum",$forum);
            $this->v('data',$data);
            $this->display('thread');
        }elseif(IS_POST){
            $gn = intval(X("post.gn"));
            if($gn == 2){ //修改文章
                {hook a_admin_thread_4}
                $id = intval(X("post.id"));
                $forum = intval(X("post.forum"));
                $title = trim(X("post.title"));
                $content=$this->uh(X('post.content'));
                $Thread = S("Thread");
                $Thread->update(array(

                    'fid'=>$forum,
                    'title'=>$title,
                    'summary'=>mb_substr(strip_tags($content), 0,100),
                ),array(
                    'id'=>$id
                ));
                S("Post")->update(array(
                    'content' => $content,
                ),array(
                    'AND'=>array(
                        'tid'=>$id,
                        'isthread'=>1
                    )
                ));
                return $this->json(array('error'=>true,'info'=>'修改成功'));
            }elseif($gn == 1){ //获取文章内容
                {hook a_admin_thread_5}
                $id = intval(X("post.id"));

                echo S("Post")->find('content',array(
                    'AND'=>array(
                        'tid'=>$id,
                        'isthread'=>1
                    )
                ));
                exit;
            }





        }

    }
    private function mess($a){
        {hook a_admin_mess_v}
        $this->v('mess',$a);
        $this->display("message");
    }
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
		$str = preg_replace('/style=".*?"/i', '', $str);
		return $str;
	}
    public function view(){
        $this->menu_action['view'] = ' active open';
        $this->v("menu_action",$this->menu_action);


        $edit = X("get.edit");

        {hook a_admin_view_1}
        if(!empty($edit)){
            $conf = file(CONF_PATH . 'conf.php');
            $arr = json_decode($conf[1],true);

            if(!is_dir(VIEW_PATH . $edit))
                return $this->mess("修改失败,{$edit} :模板不存在");

            $arr['theme']=$edit;
            file_put_contents(CONF_PATH . 'conf.php' , "<?php die(); ?>\r\n".json_encode($arr));
            return $this->mess("修改成功");
        }
        {hook a_admin_view_2}
        $ml = scandir(VIEW_PATH);
        $qj = array();
        foreach ($ml as $key=> &$v) {
            $conf_path = VIEW_PATH.'/'.$v.'/conf.php';
            if($v=='.'||$v=='..'||$v=='install'||$v=='admin'||$v=='hy_user'||$v=='hy_message'||!is_dir(VIEW_PATH . $v)){
                unset($ml[$key]);
                continue;
            }
			if(!file_exists($conf_path)){
				unset($ml[$key]);
                continue;
			}
            $qj[$key] = include $conf_path;
        }
        $this->v("qj",$qj);
        //print_r($ml);
        {hook a_admin_view_v}
        $this->v('data',$ml);
        $this->display("view");
    }
    public function viewol(){
        $this->menu_action['view'] = ' active open';
        $this->v("menu_action",$this->menu_action);



            $down = X("get.down");
            if(!empty($down)){
                if(is_dir(VIEW_PATH . $down))
                    return $this->mess('模板目录已有相同名称模板,如果你要重新下载,需要手动删除模板');
                $down_path = TMP_PATH . $down . '.zip';
                if(file_exists($down_path))
                    unlink($down_path);
                if(file_exists($down_path))
                    return $this->mess("下载模板,权限出现问题,无法删除旧压缩包,请检查目录权限");
                http_down( $down_path, "http://127.0.0.1/downview/" . $down . '.zip');

                if(!file_exists($down_path))
                    return $this->mess("没有下载到模板压缩包!");
                $zip = L("Zip");
                $zip->unzip($down_path, VIEW_PATH);
                if(is_dir(VIEW_PATH . $down))
                    return $this->mess("下载成功,请手动启动");
                return $this->mess('下载解压失败,可能压缩包不完整');
            }

        $ml = scandir(VIEW_PATH);
        $qj = array();


        foreach ($ml as $key=> &$v) {
            $conf_path = VIEW_PATH.'/'.$v.'/conf.php';
            if($v=='.'||$v=='..'||!is_dir(VIEW_PATH . $v)){
                unset($ml[$key]);
                continue;
            }
        }

        $this->v('data',json_encode($ml));
        $this->display("viewol");
    }
    public function op(){
        {hook a_admin_op_1}
        if(IS_POST){
            {hook a_admin_op_2}
            $title = X("post.title");
            $title2= X("post.title2");
            $key   = X("post.key");
            $de    = X("post.de");
            $tmpthread = intval(X("post.tmpthread"));
            $tmppost    = intval(X("post.tmppost"));
            $tmpimage    = intval(X("post.tmpimage"));
            $userview    = X("post.userview");
            $messview    = X("post.messview");
            $gold_thread    = X("post.gold_thread");
            $gold_post    = X("post.gold_post");
            $userview2    = X("post.userview2");
            $homelist    = X("post.homelist");
            $forumlist    = X("post.forumlist");
            $postlist    = X("post.postlist");
            $searchlist    = X("post.searchlist");

            

            $conf = file(CONF_PATH . 'conf.php');
            $this->conf = json_decode($conf[1],true);
            $this->conf['title']=$title;
            $this->conf['title2']=$title2;
            $this->conf['keywords']=$key;
            $this->conf['description']=$de;
            $this->conf['tmp_thread']=$tmpthread;
            $this->conf['tmp_post']=$tmppost;
            $this->conf['tmpimage']=$tmpimage;
            $this->conf['userview']=$userview;
            $this->conf['messview']=$messview;
            $this->conf['gold_thread']=$gold_thread;
            $this->conf['gold_post']=$gold_post;
            $this->conf['userview2']=$userview2;
            $this->conf['homelist']=$homelist;
            $this->conf['forumlist']=$forumlist;
            $this->conf['postlist']=$postlist;
            $this->conf['searchlist']=$searchlist;
            
            

            {hook a_admin_op_3}

            file_put_contents(CONF_PATH . 'conf.php' , "<?php die(); ?>\r\n".json_encode($this->conf));
        }
        $this->menu_action['op'] = ' active open';
        {hook a_admin_op_v}
        $this->v("menu_action",$this->menu_action);
        $this->v("conf",$this->conf);
        $this->display('op');
    }

    public function codeol(){
        $this->menu_action['code'] = ' active open';
        $this->v("menu_action",$this->menu_action);


        if(IS_POST){ // 下载压缩包
            $name = X("post.name");

            if(is_dir(PLUGIN_PATH . $name)){
                return $this->mess("当前插件已经存在,无法覆盖安装,你需要手动删除!");

            }
            $zip = L("Zip");
            //下载插件 ZIP
            $path = TMP_PATH . $name .'.zip';
            if(is_file($path))
                unlink($path);
            if(file_exists($path))
                return $this->mess("权限出现问题! 无法删除历史插件包");
            $down = "http://127.0.0.1/downplugin/".$name . '.zip';
            //echo $down;
            (http_down($path,$down));
            if(!file_exists($path))
                return $this->mess("并没有成功下载插件,可能原因如下:<br>1.tmp目录没有权限写入<br>2.下载服务器被人爆菊花了<br>3.我也不知道");


                $zip->unzip($path,PLUGIN_PATH);
            if(is_dir(PLUGIN_PATH . $name))
                return $this->mess("下载完成,请自行开启!");
            return $this->mess("呃,下载失败,具体我也不知道啥原因");
        }

        $ml = scandir(PLUGIN_PATH);
        $qj = array();

        foreach ($ml as $key=> &$v) {
            $conf_path = PLUGIN_PATH.'/'.$v.'/conf.php';
            if($v=='.'||$v=='..'||!is_dir(PLUGIN_PATH.'/'.$v) || !file_exists($conf_path)){
                unset($ml[$key]);
                continue;
            }
        }



        $this->v('data',json_encode($ml));
        $this->display('codeol');

    }
    public function code(){
        $this->menu_action['code'] = ' active open';
        $this->v("menu_action",$this->menu_action);


        if(IS_POST && !IS_AJAX){ //修改插件配置
            $name = X("post.name");
            $gn = X("post.gn");
            if($gn == 'op'){
                if(!file_exists(PLUGIN_PATH . "/{$name}/inc.php"))
                    return $this->mess("这个插件没有配置功能");

                $file = file(PLUGIN_PATH . "/{$name}/inc.php");
                $json = isset($file[1]) ? json_decode($file[1],true) : array();

                foreach ($_POST as $k => $v) {
                    $json[$k] = $v;
                }

                put_tmp_file(PLUGIN_PATH . "/{$name}/inc.php",json_encode($json));
                return $this->mess("插件修改成功");
            }elseif($gn == 'install'){
                $path = PLUGIN_PATH . "/{$name}/function.php";
                if(!file_exists($path))
                    return $this->mess('这个插件 没有安装功能');

                include $path;
                if(plugin_install()){
                    file_put_contents(PLUGIN_PATH . "/{$name}/install",'');
                    return $this->mess('安装成功');
                }
                else{
                    return $this->mess('安装失败');
                }



            }elseif($gn == 'uninstall'){
                $path = PLUGIN_PATH . "/{$name}/function.php";
                if(!file_exists($path))
                    return $this->mess('这个插件 没有安装功能');

                include $path;
                if(plugin_uninstall()){
                    if(!file_exists(PLUGIN_PATH . "/{$name}/install"))
                        return $this->mess('这个插件并没有安装,你不需要卸载');
                    unlink(PLUGIN_PATH . "/{$name}/install");
                    return $this->mess('卸载成功');
                }
                else{
                    return $this->mess('卸载失败');
                }
            }elseif($gn == 'del'){
                deldir(PLUGIN_PATH . "{$name}");
                return $this->mess('删除成功');
            }elseif($gn == 'add'){
                $name = X("post.name"); //插件名
                $name2= X("post.name2"); //插件英文名
                $user = X("post.user"); //作者
                $icon = X("post.icon"); //fa图标

                $mess = X("post.mess"); //插件描述

                $inc = X("post.inc"); //是否开启配置功能
                $fun = X("post.fun"); //是否支持函数

                if(is_dir(PLUGIN_PATH . $name2))
                    return $this->mess("已存在相同英文名的插件");
                mkdir(PLUGIN_PATH . $name2);
                file_put_contents(PLUGIN_PATH . $name2 . '/conf.php',"<?php
return array(
    'name' => '{$name}',
    'user' => '{$user}',
    'icon' => '{$icon}',
    'mess' => '{$mess}'
);");
                if($inc){
                    put_tmp_file(PLUGIN_PATH . $name2 . '/inc.php','{}');
                    file_put_contents(PLUGIN_PATH . $name2 . '/conf.html','在这里输入你的HTML表单');
                }
                if($fun){
                    file_put_contents(PLUGIN_PATH . $name2 . '/function.php','<?php
function plugin_install(){
    return true;
}
function plugin_uninstall(){
    return true;
}
                    ');
                }

                return $this->mess("插件建立成功,请打开" . PLUGIN_PATH . $name2 . '进行开发吧');
            }

            return $this->mess("未知参数1");


        }

        if(IS_AJAX){
            $update = X("post.update");
            $state = X("post.state");
            $name = X("get.name");
            $gn = X("get.gn");

            if(!empty($update)){
                if($state == 'on')
                    unlink(PLUGIN_PATH . '/' . $update . '/on');
                else
                    file_put_contents(PLUGIN_PATH . '/' . $update . '/on','');

                return $this->json(array('error'=>true,'info'=>'修改成功'));
            }elseif(!empty($name)){
                if($gn == 'op'){
                    $conf = PLUGIN_PATH . "/{$name}/conf.html";
                    if(!file_exists($conf))
                        die('这个插件没有配置功能');

                    $file = file(PLUGIN_PATH . "/{$name}/inc.php");
                    $this->v('inc',isset($file[1]) ? json_decode($file[1],true) : array());
                    C("DEBUG_PAGE",false);
                    return $this->display("plugin.{$name}::conf");
                }elseif($gn == 'install'){
                    $path = PLUGIN_PATH . "/{$name}/function.php";
                    if(!file_exists($path))
                        die('这个插件 没有安装功能');

                        die (str_replace('<?php','','<div class="alert alert-danger alert-custom alert-dismissible" role="alert">
    				    	<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
    				     	<i class="fa fa-times-circle m-right-xs"></i> <strong>警告!</strong>插件的安装与卸载可能会做一些危险动作,请慎重执行!
    				    </div><pre>'.file_get_contents($path)."</pre>"));


                    //?/C("DEBUG_PAGE",false);
                    //return;
                }elseif($gn == 'uninstall'){
                    $path = PLUGIN_PATH . "/{$name}/function.php";
                    if(!file_exists($path))
                        die('这个插件 没有安装功能');

                    die (str_replace('<?php','','<div class="alert alert-danger alert-custom alert-dismissible" role="alert">
				    	<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
				     	<i class="fa fa-times-circle m-right-xs"></i> <strong>警告!</strong>插件的安装与卸载可能会做一些危险动作,请慎重执行!
				    </div><pre>'.file_get_contents($path)."</pre>"));
                }

            }
            return $this->mess("未知参数2");


        }



        $ml = scandir(PLUGIN_PATH);
        $qj = array();

        foreach ($ml as $key=> &$v) {

            $conf_path = PLUGIN_PATH.'/'.$v.'/conf.php';
            if($v=='.'||$v=='..'||!is_dir(PLUGIN_PATH.'/'.$v) || !file_exists($conf_path)){
                unset($ml[$key]);
                continue;
            }
            $qj[$key] = include $conf_path;
            //$qj[$key]['path'] =
            if(file_exists(PLUGIN_PATH.'/'.$v.'/on'))
                $qj[$key]['on'] = true;

        }


        $this->menu_action['code'] = ' active open';
        $this->v("menu_action",$this->menu_action);
        $this->v("conf",$qj);
        $this->v('data',$ml);
        $this->display('code');

    }

    public function forumg(){
        $this->menu_action['forumg'] = ' active open';
        $this->v("menu_action",$this->menu_action);

        if(IS_POST){
            $gn = X("post.gn");

            $id = X("post.id");
            $user = X("post.user");
            if($gn == 'forumg'){
                S("Forum")->update(array(
                    'forumg'=>$user
                ),array(
                    'id'=>$id
                ));
                return $this->mess('修改完成');
            }else{
                $forum = M("Forum")->read_all();
                $arr = json_decode($forum[$id]['json'],true);
                $arr[$gn] = $user;


                S("Forum")->update(array(
                    'json'=>json_encode($arr)
                ),array(
                    'id'=>$id
                ));

            }



        }
        if(IS_AJAX){
            $id = X("get.id");
            $gn = X("get.gn");
            if($gn == 'forumg'){
                if($id > -1){
                    $user = S("Forum")->find("forumg",array(
                        'id'=>$id
                    ));
                    $this->v("user",$user);
                    $this->v("id",$id);
                    C("DEBUG_PAGE",false);
                    return $this->display("ajax_forum");
                }
            }else{
                $forum = M("Forum")->read_all();
                $arr = json_decode($forum[$id]['json'],true);
                $this->v("user",isset($arr[$gn])?$arr[$gn]:'');
                $this->v("id",$id);
                C("DEBUG_PAGE",false);
                return $this->display("ajax_forum");
            }

        }


        $Forum = S("Forum");
        $data = $Forum->select("*");

        $User = M("User");
        foreach ($data as &$v) {
            $tmp = explode(",",$v['forumg']);
            if(!count($tmp))
                continue;
            $v['user'] = array();
            foreach ($tmp as $vv) {
                $v['user'][]=$User->id_to_user(intval($vv));

            }
            //$v['user'] = $user;
            unset($tmp);
        }
        $Usergroup = M("Usergroup");
        foreach ($data as &$v) {
            $arr = json_decode($v['json'],true);
            $v['jsonarr'] = array("vforum"=>array(),'vthread'=>array(),'thread'=>array(),'post'=>array());

            if(is_array($arr)){
                foreach ($arr as $key=>$value) {
                    $v['jsonarr']["$key"]=array();
                    //分割 json
                    $tmp = explode(",",$arr["$key"]);
                    if(!count($tmp))
                        continue;

                    foreach ($tmp as $vv) {
                        $v['jsonarr']["$key"][]=$Usergroup->id_to_name(intval($vv));
                    }
                    unset($tmp);
                }
            }

            //$v['user'] = $user;


        }

        $this->v("data",$data);
        $this->display('forumg');
    }

    {hook a_admin_fun}
}
