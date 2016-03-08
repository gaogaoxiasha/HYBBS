<?php
namespace Action;
use HY\Action;

use PDO;

class InstallAction extends Action {
    public function index(){





        $this->view = 'install';
        $this->display('index');
    }
    public function ing(){
        include  HY_PATH . "HY_SQL.php";
        $sql = new \HY\HY_SQL(array(
            // 必须配置项
            'database_type' => X("post.sqltype"),
            'database_name' => X("post.name"),
            'server' => X("post.ip"),
            'username' => X("post.user"),
            'password' => X("post.pass"),
            'charset' => 'utf8',
            // 可选参数
            'port' => X("post.port"),
            // 可选，定义表的前缀
            'prefix' => 'hy_',
        ));

        


        $content = file_get_contents(INDEX_PATH . 'Conf/config.back');
        $content = str_replace("MYSQL_NAME",X("post.name"),$content);
        $content = str_replace("MYSQL_IP",X("post.ip"),$content);
        $content = str_replace("MYSQL_USER",X("post.user"),$content);
        $content = str_replace("MYSQL_PASS",X("post.pass"),$content);
        $content = str_replace("MYSQL_PORT",X("post.port"),$content);
        $content = str_replace("http://127.0.0.1",X("post.www"),$content);
        $content = str_replace("sql_typee",X("post.sqltype"),$content);

        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<16;$i++){
        $str.=$strPol[rand(0,$max)];
        }
        $content = str_replace("1234567890",$str,$content);


        file_put_contents(INDEX_PATH . 'Conf/config.php',$content);
        //file_put_contents(INDEX_PATH . 'Conf/conf.json',json_encode(array('theme'=>'HYBBS')));
        $d = $sql->exec("

DROP TABLE IF EXISTS hy_count;
DROP TABLE IF EXISTS hy_forum;
DROP TABLE IF EXISTS hy_mess;
DROP TABLE IF EXISTS hy_post;
DROP TABLE IF EXISTS hy_thread;
DROP TABLE IF EXISTS hy_user;
DROP TABLE IF EXISTS hy_usergroup;
DROP TABLE IF EXISTS hy_vote;


CREATE TABLE `hy_count` (
  `name` varchar(12) NOT NULL,
  `v` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



INSERT INTO `hy_count` (`name`, `v`) VALUES
('post', 0),
('thread', 0);


CREATE TABLE `hy_forum` (
  `id` int(11) NOT NULL,
  `fid` int(11) NOT NULL DEFAULT '-1',
  `name` varchar(12) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `forumg` text NOT NULL,
  `json` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `hy_forum` (`id`, `fid`, `name`, `count`) VALUES
(0, -1, '默认分类', 0),
(1, -1, '分类1', 0),
(2, -1, '分类2', 0),
(3, -1, '分类3', 0);


CREATE TABLE `hy_mess` (
  `uid` int(11) NOT NULL,
  `suid` int(11) NOT NULL DEFAULT '0',
  `atime` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `mess` varchar(100) NOT NULL,
  `view` smallint(1) NOT NULL DEFAULT '0',
   KEY `uid` (`uid`),
   KEY `suid` (`suid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `hy_post` (
  `id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `isthread` smallint(1) NOT NULL DEFAULT '0',
  `content` longtext NOT NULL,
  `atime` int(11) NOT NULL,
  `goods` int(11) DEFAULT '0',
  `nos` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
   UNIQUE KEY `id` (`id`),
   KEY `tid` (`tid`),
   KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `hy_thread` (
  `id` int(11) UNSIGNED NOT NULL,
  `fid` int(11) NOT NULL,
  `uid` int(11) UNSIGNED NOT NULL COMMENT 'user_id',
  `title` char(128) NOT NULL,
  `summary` char(100) NOT NULL,
  `atime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `btime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `buid`  int(10) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT 'view_size',
  `posts` int(11) NOT NULL DEFAULT '0' COMMENT 'post_size',
  `goods` int(11) NOT NULL DEFAULT '0',
  `nos` int(11) NOT NULL DEFAULT '0',
  `img` text NOT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
   UNIQUE KEY `id` (`id`),
   KEY `uid` (`uid`),
   KEY `fid` (`fid`),
   KEY `top` (`top`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `hy_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(18) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `email` varchar(100) NOT NULL,
  `salt` varchar(8) NOT NULL,
  `threads` int(11) UNSIGNED NOT NULL,
  `posts` int(11) UNSIGNED NOT NULL,
  `atime` int(11) UNSIGNED NOT NULL,
  `group` smallint(2) NOT NULL DEFAULT '0',
  `gold` int(11) NOT NULL DEFAULT '0' COMMENT '金钱',
  UNIQUE KEY `id` (`id`),
  KEY `user` (`user`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;


INSERT INTO `hy_user` (`id`, `user`, `pass`, `email`, `salt`, `threads`, `posts`, `atime`, `group`) VALUES
(1, '".X("post.username")."', '".L("User")->md5_md5(X("post.password"),"81584444")."', 'admin@hyyyp.com', '81584444', 0, 0, ".NOW_TIME.", 1);

CREATE TABLE `hy_usergroup` (
  `id` int(11) NOT NULL,
  `name` varchar(12) NOT NULL,
  `json` varchar(120) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `hy_usergroup` (`id`, `name`, `json`) VALUES
(1, '管理员', '{\"thread\":1,\"post\":1,\"upload\":1,\"mess\":1,\"del\":1}'),
(2, '新用户', '{\"thread\":1,\"post\":1,\"mess\":1,\"upload\":1,\"del\":1}'),
(3, '游客', '{\"thread\":1,\"post\":1,\"upload\":true,\"mess\":1,\"del\":1}');



CREATE TABLE `hy_vote` (
  `name` char(10) NOT NULL,
  `ip` char(10) NOT NULL,
  `id` int(11) NOT NULL,
  `atime` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



");
if(!is_int($d))
  echo '安装失败';

      rename(ACTION_PATH . '/Install.php' , ACTION_PATH . '/Install.php.back');
      return header("location: ../");



        //echo X("post.name");
    }

}
