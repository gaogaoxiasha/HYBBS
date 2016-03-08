<?php
namespace Lib;

class User{
    //用户名检查 , 仅允许数字与字母 长度5-18   去前后空
	public function check_user(&$username){
		/*$user = strtolower(trim($user));
		if (!ctype_alnum($user)){
  			return '违法，只能使用数字与字母组合！ 切勿输入非法符号与中文！';
		} elseif(mb_strlen($user) > 18 || mb_strlen($user) < 5){
			return '抱歉，账号长度仅支持 最少 5 个字符 最大 18 个字符。';
		}
		return '';*/
		$username = trim($username);
		$username = strtolower($username);
		if(empty($username)) {
			return '用户名不能为空。';
		} elseif(mb_strlen($username) > 18 || mb_strlen($username) < 2) {
			return '用户名长度不符合标准:'.mb_strlen($username);
		} elseif(str_replace(array("\t", "\r", "\n", ' ', '　', ',', '，', '-'), '', $username) != $username) {
			return '用户名中不能含有空格和 , - 等字符';
		} elseif(!preg_match('#^[\w\'\-\x7f-\xff]+$#', $username)) {
			return '用户名只允许: 数字,字母,中文. 不允许任何标点符号!';
		} elseif(htmlspecialchars($username) != $username) {
			return '用户名中不能含有HTML字符（尖括号）';
		}
		if(($error = $this->have_badword($username))) {
			return '包含敏感词：'.$error;
		}
		return '';
	}
    public function check_pass($pass){
		if(strlen($pass) < 5)
			return false;
		return true;
	}
	public function check_email(&$email) {
		//$emaildefault = array('admin', 'system');
		if(empty($email)) {
			return 'EMAIL 不能为空';
		//} elseif(utf8::strlen($email) > 32) {
		//	return 'Email 长度不能大于 32 个字符。';
		} elseif(!preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) {
			return 'Email 格式不对';
		} elseif(mb_strlen($email) < 6) {
			return 'Email 太短';
		//} elseif(str_replace($emaildefault, '', $email) != $email) {
		//	return 'Email 含有非法关键词';
		}

		// hook usre_model_check_email_end.php
		return '';
	}
	public function md5_md5($s, $salt = '') {
		return md5(md5($s).$salt);
	}
	public function set_cookie($data){
		return L("Encrypt")->encrypt(json_encode($data),C("MD5_KEY"));
	}
	public function get_cookie($cookie){
		$json = L("Encrypt")->decrypt($cookie,C("MD5_KEY"));
		return json_decode($json,true);
	}
    public function have_badword($user){
		$badword = array("操","草泥马","操你","妈逼","caonima","nimabi");
		if(!empty($badword)) {
			foreach($badword as $v) {
				if(strpos($user, $v) !== FALSE) {
					return $v;
				}
			}
		}
		return '';
	}
}
