<?php
function A($name){
    $class="\Action\\{$name}Action";
    $obj = new $class;
    return $obj;
}
function X($name){
    $data = explode(".",$name);
    if(count($data) == 2){
        $v = $data[1];
        if($data[0]=='get'){
            return isset($_GET[$v])?$_GET[$v]:'';
        }elseif($data['0']=='post'){
            return isset($_POST[$v])?$_POST[$v]:'';;
        }elseif($data['0']=='session'){
            return isset($_SESSION[$v])?$_SESSION[$v]:'';;
        }elseif($data['0']=='cookie'){
            return isset($_COOKIE[$v])?$_COOKIE[$v]:'';;
        }elseif($data['0']=='server'){
            return isset($_SERVER[$v])?$_SERVER[$v]:'';;
        }
    }
    return '';
}
//实例Model
function S($name){

    $obj = new \HY\Model;
    $obj->table = strtolower($name);
    return $obj;
}
//SQL实例
function M($name){
    $class="\Model\\{$name}Model";
    $obj = new $class;
    $obj->table = strtolower($name);
    return $obj;
}

//实例 Lib库
function L($name){

    //include MYLIB_PATH . $name . ".php";
    $class = "Lib\\{$name}";
    $obj = new $class;

    return $obj;
}

//获取设置 配置文件
function C($name=null, $value=null,$default=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}


function cookie($name='', $value='',$expire=0) {
    $name = str_replace('.', '_', $name);
    if ('' === $value) {
        if(isset($_COOKIE[$name])){

            $value =    $_COOKIE[$name];
            return $value;
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600,'/');
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie

            $expire = !empty($expire) ? time() + intval($expire) : 0;
            setcookie($name, $value, $expire,'/');
            $_COOKIE[$name] = $value;
        }
    }
    return null;
}
function session($name='',$value='') {

    if('' === $value){
        if(''===$name){
            // 获取全部的session
            return $_SESSION;
        }elseif(0===strpos($name,'[')) { // session 操作
            if('[pause]'==$name){ // 暂停session
                session_write_close();
            }elseif('[start]'==$name){ // 启动session
                session_start();
            }elseif('[destroy]'==$name){ // 销毁session
                $_SESSION =  array();
                session_unset();
                session_destroy();
            }elseif('[regenerate]'==$name){ // 重新生成id
                session_regenerate_id();
            }
        }elseif(0===strpos($name,'?')){ // 检查session
            $name   =  substr($name,1);
            if(strpos($name,'.')){ // 支持数组
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2]);
            }else{
                return isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){ // 清空session

            $_SESSION = array();


        }else{
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }
        }
    }elseif(is_null($value)){ // 删除session
        if(strpos($name,'.')){
            list($name1,$name2) =   explode('.',$name);

                unset($_SESSION[$name1][$name2]);

        }else{

                unset($_SESSION[$name]);

        }
    }else{ // 设置session
		if(strpos($name,'.')){
			list($name1,$name2) =   explode('.',$name);

				$_SESSION[$name1][$name2]  =  $value;

		}else{

				$_SESSION[$name]  =  $value;

		}
    }
    return null;
}
function put_tmp_file($path,$content){
    file_put_contents($path,"<?php !defined('HY_PATH') && exit('HY_PATH not defined.'); ?>\r\n" . $content);

}
//URL生成
function URL($action,$method,$age='',$ext=''){

    $action_arr = C("HY_URL.action");
    $method_arr = C("HY_URL.method");
    if(preg_match('/^[A-Za-z](\/|\w)*$/',$action))
        $url=(isset($action_arr[$action])?$action_arr[$action]:$action);
    else
        $url = $action;
    if(preg_match('/^[A-Za-z](\/|\w)*$/',$method))
        $url.=(isset($method_arr[$action][$method]) ? EXP.$method_arr[$action][$method] : ($method==''?'':EXP.$method)). ($age==''?'':''.$age) ;
    else
        $url.=($method==''?'':EXP.$method) . ($age==''?'':''.$age) ;


    return $url . (empty($ext)?EXT:$ext);


}


 ?>
