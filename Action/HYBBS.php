<?php
namespace Action;
use HY\Action;
class HYBBS extends Action {
    public $_user=array();
    public $_login=false;
    public $_theme;
    public $_forum=array();
    public $fc;
    public $conf;
    public $_group = 3;
    public $_usergroup=array();
    {hook a_hybbs_var}

    public function __construct(){

        if(isset($_SERVER['HYBBS'])) //防止多次执行 构造函数
            return;

        {hook a_hybbs_init}

        $conf = file(CONF_PATH . 'conf.php');
        $this->conf = json_decode($conf[1],true);
        $this->v("conf",$this->conf);


        if(!C('DOMAIN_NAME'))
            header("location: ./install");

        {hook a_hybbs_init_1}
        $this->_theme = $this->view = $this->conf['theme'];
        define('THEME_NAME',$this->_theme);
        define('WWW',C('DOMAIN_NAME') . '/');

        //初始化用户状态
        $this->init_user();
        $this->v("group",$this->_group);
        define("NOW_GROUP",$this->_group);
        define("IS_LOGIN",$this->_login);
        $this->v('title','页面缺少标题');
        {hook a_hybbs_init_2}

        //生成板块缓存 File
        $this->fc = L("Filecache");
        $forum = $this->fc->get('forum_'.md5("forum".C("MD5_KEY")));
        if(empty($forum) || DEBUG){ //调试模式 每次都生成缓存
            $forum = S("Forum")->select("*");
            $this->fc->set('forum_'.md5("forum".C("MD5_KEY")),$forum);
        }

        foreach ($forum as $k => $v) {
            $this->_forum[intval($v['id'])] = $v;
        }
        {hook a_hybbs_init_3}
        //生成用户组缓存
        $this->fc = L("Filecache");
        $Usergroup = $this->fc->get('group_'.md5("usergroup".C("MD5_KEY")));
        if(empty($Usergroup) || DEBUG){ //调试模式 每次都生成缓存
            $Usergroup = S("Usergroup")->select("*");
            $this->fc->set('group_'.md5("usergroup".C("MD5_KEY")),$Usergroup);
        }

        foreach ($Usergroup as $k => $v) {
            $this->_usergroup[intval($v['id'])] = $v;
        }
        //print_r($this->_forum);
        {hook a_hybbs_init_v}
        $this->v("forum",$this->_forum);
        $this->v("usergroup",$this->_usergroup);

    }
    public function init_user(){
        $cookie = cookie("HYBBS_HEX");
        if(!empty($cookie)){
            $UserLib = L("User");
            $user = $UserLib->get_cookie($cookie);


            if(!empty($user)){

                if(isset($user['id']) && isset($user['user']) && S("User")->has(array('AND'=>array('id'=>$user['id'],'user'=>$user['user'])))){
                    $this->_group = $user['group'];
                    $user['avatar'] = $this->avatar($user['user']);
                    //print_r($user);
                    $this->_user = $user;

                    $this->_login=true;
                    $this->v('user',$this->_user);

                }
            }
        }


    }
    public function message($msg,$type=false){
        {hook a_hybbs_message}

        if(IS_AJAX){
            return $this->json(array(
                'error'=>$type,
                'info'=>$msg
            ));
        }
        $this->v('title',$msg.' - 错误提示');
        $this->v("msg",$msg);
        $this->v("bool",$type);

        $conf = file(CONF_PATH . 'conf.php');
        $this->conf = json_decode($conf[1],true);

        $this->view = $this->conf['messview'];
        $this->display('message');
    }
    //获取用户头像
    public function avatar($user){
        {hook a_hybbs_avatar}
        $path = INDEX_PATH . 'upload/avatar/' . md5($user.C("MD5_KEY"));
        $path1 = 'upload/avatar/' . md5($user.C("MD5_KEY"));
        if(!file_exists($path.'.jpg'))
            return array(
                'a'=>'public/images/user.gif',
                'b'=>'public/images/user.gif',
                'c'=>'public/images/user.gif',
            );
        return array(
            "a"=>$path1."-a.jpg",
            "b"=>$path1."-b.jpg",
            "c"=>$path1."-c.jpg"
        );
    }
    {hook a_hybbs_fun}
}
