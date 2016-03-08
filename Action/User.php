<?php
namespace Action;
use HY\Action;

class UserAction extends HYBBS {
    public $menu_action;
    public function __construct(){
		parent::__construct();
        {hook a_user_init}

        $this->view = $this->conf['userview'];



    }
    public function Edit(){
        {hook a_user_edit_1}
        if(!IS_LOGIN)
            return $this->message('请登录');

        $pass1 = X("post.pass1");
        $pass2 = X("post.pass2");
        {hook a_user_edit_2}
        if($pass1 != $pass2)
            return $this->message("两次密码不一致");
        $UserLib = L("User");
        if(!$UserLib->check_pass($pass1))
            return $this->message('密码不符合规则');
        {hook a_user_edit_3}
        $newpass = $UserLib->md5_md5($pass1,$this->_user['salt']);
        $this->_user['pass'] = $newpass;
        S("User")->update(array(
            'pass'=>$this->_user['pass']
        ),array(
            'id'=>$this->_user['id']
        ));
        {hook a_user_edit_4}
        cookie('HYBBS_HEX',$UserLib->set_cookie($this->_user));
        return $this->message("修改成功",true);

    }


    //登录账号
    public function Login(){
        //cookie("test",34);
        {hook a_user_login_1}
        $this->v("title","登录页面");
        if(IS_LOGIN)
            return $this->message("你都已经登录了,登录那么多次干嘛");

        if(IS_GET){
            {hook a_user_login_2}
            $this->view = $this->conf['userview2'];
            $this->display('user_login');
        }
        elseif(IS_POST){
            $user = X("post.user");
            $pass = X("post.pass");

            $UserLib = L("User");
            {hook a_user_login_3}

            $msg = $UserLib->check_user($user);
            //检查用户名格式是否正确
            if(!empty($msg))
                return $this->message($msg);

            if(!$UserLib->check_pass($pass))
                return $this->message('密码不符合规则');
            {hook a_user_login_4}
            $User = M("User");
            if(!$User->is_user($user))
                return $this->message("账号不存在!");

            $data = $User->find("*",array('user'=>$user));
            {hook a_user_login_5}
            if(!empty($data)){
                $UserLib = L("User");
                if($data['pass'] == $UserLib->md5_md5($pass,$data['salt'])){

                    cookie('HYBBS_HEX',$UserLib->set_cookie($data));
                    $this->init_user();
                    return $this->message("登录成功 !",true);
                }else{
                    return $this->message("密码错误!");
                }
            }else{
                return $this->message('账号数据不存在!');
            }
        }
        {hook a_user_login_6}
    }
    //注册账号
    public function Add(){

        {hook a_user_add_1}

        $this->v("title","注册用户");
        if(IS_LOGIN)
            return $this->message("你都已经登录了,还注册那么多账号干嘛");
        if(IS_GET){
            {hook a_user_add_2}
            $this->view = $this->conf['userview2'];
            $this->display('user_add');
        }
        elseif(IS_POST){
            $user = X("post.user");
            $pass1 = X("post.pass1");
            $pass2 = X("post.pass2");
            $email = X("post.email");
            {hook a_user_add_3}
            if($pass1 != $pass2)
                return $this->message("两次密码不一致");

            $UserLib = L("User");
            $msg = $UserLib->check_user($user);
            //检查用户名格式是否正确
            if(!empty($msg))
                return $this->message($msg);

            if(!$UserLib->check_pass($pass1))
                return $this->message('密码不符合规则');

            {hook a_user_add_4}

            $msg = $UserLib->check_email($email);

            if(!empty($msg))
                return $this->message($msg);

            {hook a_user_add_5}
            $User = M("User");
            if($User->is_user($user))
                return $this->message("账号已经存在!");

            if($User->is_email($email))
                return $this->message("邮箱已经存在!");


            {hook a_user_add_6}
            $User->add_user($user,$pass1,$email);


            cookie('HYBBS_HEX',$UserLib->set_cookie(
                            $User->read(
                                $User->user_to_id($user)
                            )
                        )
            );
            return $this->message("账号注册成功",true);
        }
        {hook a_user_add_7}
    }
    public function ava(){
        {hook a_user_ava_1}
        $this->v("title","更改头像");
        if(!IS_LOGIN) return $this->message("请登录后操作 Error =1 !");

        {hook a_user_ava_2}
        $id = $this->_user['id'];
        if(empty($id)) return $this->message("请重新登录 Error =2  !");


        L("Upload");
        {hook a_user_ava_3}
        $upload = new \Lib\Upload();
        $upload->maxSize   =     3145728 ;// 设置附件上传大小  3M
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     INDEX_PATH . 'upload/avatar/'; // 设置附件上传根目录
        $upload->saveExt    =   "jpg";
        $upload->replace    =   true;
        $upload->autoSub    =   false;
        $upload->saveName   =   md5($this->_user['user'].C("MD5_KEY"));
        if(!is_dir(INDEX_PATH. "upload"))
			mkdir(INDEX_PATH. "upload");
        if(!is_dir($upload->rootPath))
            mkdir($upload->rootPath);
        {hook a_user_ava_4}
        $info   =   $upload->upload();

        if(!$info)
            return $this->message("上传失败!");

        {hook a_user_ava_5}
        $image = new \Lib\Image();
        $image->open(INDEX_PATH . 'upload/avatar/'.$upload->saveName.".jpg");
        // 生成一个缩放后填充大小150*150的缩略图并保存为thumb.jpg
        $image->thumb(250, 250,$image::IMAGE_THUMB_CENTER)->save(INDEX_PATH . 'upload/avatar/'.$upload->saveName."-a.jpg");
        $image->thumb(150, 150,$image::IMAGE_THUMB_CENTER)->save(INDEX_PATH . 'upload/avatar/'.$upload->saveName."-b.jpg");
        $image->thumb(50  , 50,$image::IMAGE_THUMB_CENTER)->save(INDEX_PATH . 'upload/avatar/'.$upload->saveName."-c.jpg");
        //$image->thumb(150, 150,\Think\Image::IMAGE_THUMB_CENTER)
        {hook a_user_ava_v}
        return $this->message("上传成功!",true);

    }
    public function out(){
        {hook a_user_out_v}
        $this->v("title","注销用户");
        cookie('HYBBS_HEX',null);
        $this->init_user();
        $this->message('退出成功',true);
    }
    public function isuser(){
        {hook a_user_isuser_v}
        $user = X("post.user");
        $bool = M("User")->is_user($user);
        return $this->json(array('error'=>$bool));
    }
    public function isemail(){
        {hook a_user_isemail_v}
        $email = X("post.email");
        $bool = M("User")->is_email($email);
        return $this->json(array('error'=>$bool));
    }

    {hook a_user_fun}
}
