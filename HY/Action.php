<?php
namespace HY;

abstract class Action
{
    protected $var = array();
    public $Tpl;
    public $view = '';
    //模板分组
    //编译获取HTML
    protected function GetHtml($file_name)
    {
        $View = $this->view ? $this->view . '/' : '';
        $tmp_path = TMP_PATH . '/' . md5($View . $file_name) . '.php';
        $plugin_name = '';
        $plugin_view = '';
        if(PLUGIN_ON){ //插件开启
            $t = explode(".",$file_name);
            if(isset($t[0]) && isset($t[1])){
                if($t[0] == 'plugin'){
                    $t = explode("::",$t[1]);
                    if(isset($t[0]) && isset($t[1])){

                        if(!is_dir(TMP_PATH . '/plugin_tmp'))
                            mkdir(TMP_PATH . '/plugin_tmp');

                        $plugin_name = $t[0];
                        $plugin_view = $t[1];
                        //define('PLUIN_TMP_PATH',TMP_PATH . '/plugin_tmp');
                        $tmp_path = TMP_PATH . "/plugin_tmp/{$plugin_name}_{$plugin_view}.php";

                        //echo $tmp_path;

                    }
                }
            }
            //throw new \Exception('控制器 ' . $class . ' 不存在!');
        }
        if (!file_exists($tmp_path) || DEBUG) {
            //写入缓存文件
            $tpl_path = VIEW_PATH . $View . $file_name . C('tpl_suffix');
            if(!empty($plugin_name) && !empty($plugin_view)){

                $tpl_path = PLUGIN_PATH . "/{$plugin_name}/{$plugin_view}". C('tpl_suffix');
                //echo $tpl_path;
            }
            if (!file_exists($tpl_path)) {
                throw new \Exception('模板不存在(file_path): ' . $View . $file_name . C('tpl_suffix'));
            }
            $content = file_get_contents($tpl_path);
            //获取 模板文件
            $this->Tpl = new \HY\Tpl();
            $this->Tpl->view = $this->view;
            $content = $this->Tpl->init($content, $this->var);
            //' unlink("'.TMP_PATH . $file_name.C("tpl_suffix").'")'.
            put_tmp_file($tmp_path, $content);
        }
        //print_r($content);
        $_SERVER['ob_start'] = true;
        ob_start();
        ob_implicit_flush(0);
        extract($this->var, EXTR_OVERWRITE);
        include $tmp_path;

        $content = ob_get_clean();
        // if(C("GZIP")){
        //     if(function_exists('gzencode')){
        //         header("Content-Encoding: gzip");
        //         $content = gzencode($content, 5);
        //         //header("Content-Length: ".strlen($content));
        //     }
        // }


        return $content;
    }
    
    //生成HTML
    protected function ShowHtml($file_name, $del_time = null)
    {
        $content = '';
        if (!$del_time) {
            $del_time = C('tmphtml_del_time');
        }
        $FileCache = new \HY\FileCache('tmphtml');
        if ($del_time) {
            $tmp_time = $FileCache->read($file_name);
            //echo $tmp_time;
            if ($_SERVER['time'] > $tmp_time + $del_time) {
                unlink(TMPHTML_PATH . $file_name . C('tpl_suffix'));
            }
        }
        if (!file_exists(TMPHTML_PATH . $file_name . C('tpl_suffix')) || DEBUG) {
            //$content = '<?php if('.($_SERVER['time'] + 60).' <  $_SERVER[\'time\']){} ';
            $FileCache->put($file_name);
            $content .= $this->GetHtml($file_name);
            file_put_contents(TMPHTML_PATH . $file_name . C('tpl_suffix'), $content);
        } else {
            $content = file_get_contents(TMPHTML_PATH . $file_name . C('tpl_suffix'));
        }
        echo $content;
    }
    protected function display($file_name, $html = false)
    {
        $content = $this->GetHtml($file_name);
        echo $content;
    }
    protected function v($name, $value = '')
    {
        if (is_array($name)) {
            $this->var = array_merge($this->var, $name);
        } else {
            $this->var[$name] = $value;
        }
    }
    public function json($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        die(json_encode($data));
    }
    public function jsonp($data, $fun = '')
    {
        header('Content-Type:application/json; charset=utf-8');
        if (empty($fun)) {
            $fun = X('get.jsoncallback');
        }
        die($fun . '(' . json_encode($data) . ');');
    }
}
