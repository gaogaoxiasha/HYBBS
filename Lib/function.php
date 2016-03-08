<?php
//计算时间间隔
function humandate($timestamp) {
	$seconds = $_SERVER['REQUEST_TIME'] - $timestamp;
	if($seconds > 31536000) {
		return date('Y-n-j', $timestamp);
	} elseif($seconds > 2592000) {
		return floor($seconds / 2592000).'月前';
	} elseif($seconds > 86400) {
		return floor($seconds / 86400).'天前';
	} elseif($seconds > 3600) {
		return floor($seconds / 3600).'小时前';
	} elseif($seconds > 60) {
		return floor($seconds / 60).'分钟前';
	} else {
		return $seconds.'秒前';
	}
}

//获取插件配置 数据
function get_plugin_inc($plugin_name){

	if(!is_file(PLUGIN_PATH . "{$plugin_name}/inc.php"))
		return false;
	//echo PLUGIN_PATH . "{$plugin_name}/inc.php";
	$path = PLUGIN_PATH . "{$plugin_name}/inc.php";
	$file = file($path);
	return json_decode($file[1],true);
}
//获取插件安装状态
function get_plugin_install_state($plugin_name){
	if(!is_file(PLUGIN_PATH . "{$plugin_name}/install"))
		return false;
	return true;
}
function is_plugin_function($name){
	if(!is_file(PLUGIN_PATH . "{$name}/function.php"))
		return false;
	return true;
}
//删除目录
function deldir($dir) {

  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }

  closedir($dh);
  if(rmdir($dir)) {
    return true;
  } else {
    return false;
  }
}
//计算两时间相隔天数
function diffBetweenTwoDays ($day1, $day2)
{
  $second1 = ($day1);
  $second2 = ($day2);

  if ($second1 < $second2) {
    $tmp = $second2;
    $second2 = $second1;
    $second1 = $tmp;
  }
  return intval(($second1 - $second2) / 86400);
}
//下载文件 参数  保存路劲文件名 , 参数 下载地址
function http_down($save_to,$file_url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch,CURLOPT_URL,$file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file_content = curl_exec($ch);
    curl_close($ch);



    $downloaded_file = fopen($save_to, 'w');
    fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);

}
