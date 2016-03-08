<?php
namespace HY;

class HY
{
    public static $_CLASS = array();
    //private static $_INCLUDE = array();
    public static function start()
    {
        spl_autoload_register('HY\\HY::autoload');
        //register_shutdown_function('HY\HY::fatalError');
        //set_error_handler('HY\HY::appError');
        //set_exception_handler('HY\HY::appException');
        if (DEBUG) {
            error_reporting(E_ALL | E_STRICT);
            //error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
            @ini_set('display_errors', 'ON');
        } else {
            error_reporting(E_ALL & ~E_NOTICE);
        }
        set_error_handler('HY\\HY::hy_error');
        set_exception_handler('HY\\HY::hy_exception');
        //header("Content-Type: text/html; charset=UTF-8");
        //hook::tree(PLUGIN_PATH);
        //print_r(hook::$file);
        $config = (include CONF_PATH . 'config.php');
        isset($config['var_left_tpl']) or $config['var_left_tpl'] = '{';
        isset($config['var_right_tpl']) or $config['var_right_tpl'] = '}';
        isset($config['tpl_suffix']) or $config['tpl_suffix'] = '.html';
        isset($config['url_suffix']) or $config['url_suffix'] = '.html';
        isset($config['url_explode']) or $config['url_explode'] = '/';
        isset($config['tmp_del_time']) or $config['tmp_del_time'] = 0;
        isset($config['tmphtml_del_time']) or $config['tmphtml_del_time'] = 0;
        isset($config['DEBUG_PAGE']) or $config['DEBUG_PAGE'] = false;
        isset($config['HOOK_SUFFIX']) or $config['HOOK_SUFFIX'] = ".hook";
        //isset($config['GZIP']) or $config['GZIP'] = false;
        define('EXT',$config['url_suffix']);
        define('EXP',$config['url_explode']);
        include LIB_PATH . 'function.php';
        C($config);
        isset($_SERVER['PATH_INFO']) or $_SERVER['PATH_INFO'] = '';
        $url = ltrim(strtolower($_SERVER['PATH_INFO']), '/');
        if (isset($_GET['s']) && empty($url)) {
            $url = ltrim(strtolower($_GET['s']), '/');
        }

        $class = '';
        $Action = 'Index';
        $_Action = 'Index';
        $_Fun = 'Index';

        $_GET['HY_URL']=array('Index','Index');

        if (empty($url)) {
            $class = '\\Action\\IndexAction';
        } else {
            $info = str_replace($config['url_suffix'], '', $url);
            $info = $_GET['HY_URL'] = explode(C('url_explode'), $info);

            $Action = isset($info[0]) ? $info[0] : 'Index';
            $Fun = isset($info[1]) ? $info[1] : 'Index';
            $Action = $Action == '' ? 'Index' : $Action;
            $Fun = $Fun == '' ? 'Index' : $Fun;
            for ($i = 2; $i < count($info); $i++) {
                $_GET[$info[$i++]] = isset($info[$i]) ? $info[$i] : '';
            }
            if(isset($config['HY_URL']['action'])){
                $z = array_search($Action,$config['HY_URL']['action']);
                if($z){
                    $Action = $z;
                    if(isset($config['HY_URL']['method'][$z])){
                        $b = array_search($Fun,$config['HY_URL']['method'][$z]);
                        if($b)
                            $Fun=$b;
                    }

                }
            }






            $_Action = $Action = ucfirst($Action);
            $_Fun = $Fun = ucfirst($Fun);
            $class = "\\Action\\{$_Action}Action";
        }
        define('ACTION_NAME', $_Action);
        define('METHOD_NAME', $_Fun);

        if (!file_exists(ACTION_PATH . "{$Action}.php")) {
            if (!file_exists(ACTION_PATH . 'Empty.php')) {
                throw new \Exception("{$Action}控制器不存在!");
            } else {
                $class = '\\Action\\EmptyAction';
            }
        }
        if (file_exists(MYLIB_PATH . 'function.php')) {
            include MYLIB_PATH . 'function.php';
        }

        $module = new $class();
        if (!method_exists($module, $_Fun) || !preg_match('/^[A-Za-z](\/|\w)*$/',$_Fun)) {
            //类方法不存在
            if (!method_exists($module, '_empty')) {
                throw new \Exception("你的{$class}没有存在{$_Fun}操作方法");
            }
            $_Fun = '_empty';
        }
        $method = new \ReflectionMethod($module, $_Fun);
        if ($method->isPublic() && !$method->isStatic()) {
            //公开函数 非静态
            $class = new \ReflectionClass($module);
            $method->invoke($module);
        }
        $GLOBALS['END_TIME'] = microtime(TRUE);
        //echo DEBUG;
        if (DEBUG) {
            $DEBUG_SQL = DEBUG_SQL::$logs;
            if (empty($url)) {
                $url = '/';
            } else {
                $url = '/' . $url;
            }
        }
        if (DEBUG && C('DEBUG_PAGE')) {
            //$DEBUG_INCLUDE = self::$_INCLUDE;
            $DEBUG_CLASS = self::$_CLASS;
            require HY_PATH . 'View/Debug.php';
        }
    }
    public static function hy_exception($e){
        // 避免死循环
        DEBUG && ($_SERVER['exception'] = 1);
        //log::write($e->getMessage().' File: '.$e->getFile().' ['.$e->getLine().']');

        $s = '';
        if (DEBUG) {
            try {
                $s = exception::to_html($e);
            } catch (Exception $e) {
                $s = get_class($e) . ' thrown within the exception handler. Message: ' . $e->getMessage() . ' on line ' . $e->getLine();
            }
        } else {
            $s = $e->getMessage();
        }
        echo $s;
        die;
    }
    public static function hy_error($errno, $errstr, $errfile, $errline){

        if(isset($_SERVER['ob_start'])){
            unset($_SERVER['ob_start']);
            ob_end_clean();
        }

        // 防止死循环
        $errortype = array(E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parsing Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Runtime Notice');
        $errnostr = isset($errortype[$errno]) ? $errortype[$errno] : 'Unknonw';
        // 运行时致命错误，直接退出。并且 debug_backtrace()
        $s = "[{$errnostr}] : {$errstr} in File {$errfile}, Line: {$errline}";
        // 抛出异常，记录到日志
        //echo $errstr;
        if (DEBUG && empty($_SERVER['exception'])) {

            $s = str_replace("Notice",'通知',$s);
            $s = str_replace("Warning",'警告',$s);
            $s = str_replace("Line",'错误行',$s);


            //$s = str_replace("Undefined variable",'变量未定义',$s);
            $s = str_replace("in File",'错误来自于文件:',$s);

            //如果你看到这条注释,请看顶部的错误信息, 非以上代码问题
            throw new \Exception($s);
        } else { // 继续运行

        }


        return 0;
    }
    public static function autoload($class){
        $info = explode('\\', $class);
        //echo $class."\r\n";
        if (count($info) != 2) {
            return false;
        }
        if (isset(self::$_CLASS[$class])) {
            //加载过
            return;
        }
        if ($info[0] == 'Lib') {
            $file = MYLIB_PATH . $info[1] . '.php';
        } elseif ($info[0] == 'Model') {
            $file = MODEL_PATH . str_replace('Model', '', $info[1]) . '.php';
        } elseif ($info[0] == 'Action') {
            $file = ACTION_PATH . str_replace('Action', '', $info[1]) . '.php';
            if (PLUGIN_ON) {
                //插件机制
                $file1 = TMP_PATH . MD5('Action/' . $info[1]) . '.php';
                if (!is_file($file1) || DEBUG) {
                    // 临时Action不存在
                    //include_once LIB_PATH . 'hook.php';
                    if (!is_file($file)) {
                        throw new \Exception('控制器 ' . $class . ' 不存在!');
                    }
                    $code = file_get_contents($file);
                    hook::put(hook::encode($code), $file1);
                }
                $file = $file1;
            }
        } elseif ($info[0] == 'HY' && $info[1] == 'Model') {
            include HY_PATH . 'HY_SQL.php';
            $file = HY_PATH . 'Model.php';
        } elseif ($info[0] == 'HY' && $info[1] == 'Action') {
            $file = HY_PATH . 'Action.php';
        } elseif ($info[0] == 'HY' && $info[1] == 'Tpl') {
            $file = HY_PATH . 'Tpl.php';
        } elseif ($info[0] == 'HY' && $info[1] == 'FileCache') {
            $file = HY_PATH . 'FileCache.php';
        } elseif ($info[0] == 'HY' && $info[1] == 'hook') {
            $file = LIB_PATH . 'hook.php';
        } elseif ($info[0] == 'HY' && $info[1] == 'exception') {
            $file = LIB_PATH . 'exception.php';
        }
        if (empty($file)) {
            return false;
        }
        //echo $class."<br>";
        //echo $file."|<br>";
        $path = realpath($file);
        if (!is_file($path)) {
            throw new \Exception('类库不存在 : ' . $class);
        }
        include_once $path;
        self::$_CLASS[$class] = true;
    }
}
class DEBUG_SQL{
    public static $logs = array();
    public static function SQL_LOG($log){
        array_push(self::$logs, $log);
    }
}
