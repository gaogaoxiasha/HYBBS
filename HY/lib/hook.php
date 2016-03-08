<?php
namespace HY;
class hook {
    static public $file = array();

    static public function init_file(){
        self::tree(PLUGIN_PATH);
        //print_r(self::$file);
    }
    static public function encode($code){ //code contents
        //echo $code;
        //echo '插件开启\r\n';
        if(empty(self::$file))
            self::init_file();
        $content = preg_replace_callback('/{hook (.+?)}/is','self::parseTag',$code);
        return $content;
    }
    static public function parseTag($tagStr){
        $tag = isset($tagStr[1]) ? $tagStr[1] : '';
        //echo $tag."\r\n";
        $content='';
        if(isset(self::$file[$tag])){
            foreach (self::$file[$tag] as $v) {
                $content.=file_get_contents($v);
            }
        }
        return $content;

    }
    //写入缓存
    static public function put($contents,$path){
        file_put_contents($path,$contents);
    }

    static public function tree($directory)
    {

    	$list = scandir($directory); // 得到该文件下的所有文件和文件夹
    	foreach($list as $file){//遍历
    		$file_location=$directory."/".$file;//生成路径
    		if(is_dir($file_location) && $file!="." &&$file!=".."){ //判断是不是文件夹

    			self::tree($file_location); //继续遍历

    		}else{
                //echo self::exec($file)."\r\n";
                if(!is_dir($file_location) && self::exec($file) == 'hook' && $file != 'on' && $file != 'install'){
                    //echo "$file_location\r\n";
                    $sy = self::unexe($file);
                    // for ($i=0; $i < 250; $i++) {
                    //     $ss.=$i;
                    // }


                    if(PLUGIN_ON_FILE){ //开启插件是否开启机制
                        if(!is_file(str_replace('//', '/', dirname($file_location)) .'/on'))
                            continue;

                    }
                    if(isset(self::$file[$sy]))
                        self::$file[$sy][]=str_replace('//', '/', $file_location);
                    else
                        self::$file[$sy] = array(str_replace('//', '/', $file_location));
                }

                //echo self::exec($file_location);

            }



    	}

    }
    //获取后缀
    static public function exec($filename){
        return substr(strrchr($filename, '.'), 1);
    }
    //删除后缀
    static public function unexe($name){
        return str_replace(C("HOOK_SUFFIX"),'',$name);
    }



}
