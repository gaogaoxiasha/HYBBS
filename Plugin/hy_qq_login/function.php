<?php
//插件安装时 执行的安装函数
function plugin_install(){
    $sql = S("Plugin");
    if(!$sql->query("
    -- 创建QQ用户表 `hy_qqlogin`

    CREATE TABLE `hy_qqlogin` (
      `openid` varchar(32) NOT NULL,
      `uid` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- 创建UID 唯一索引
    ALTER TABLE `hy_qqlogin`
    ADD UNIQUE KEY `uid` (`uid`);

    "))
        return false;
    return true;
} 
//插件卸载时 执行的安装函数
function plugin_uninstall(){
    $sql = S("Plugin");
    if(!$sql->query("DROP TABLE `hy_qqlogin`;"))
        return false;

    return true;
}
