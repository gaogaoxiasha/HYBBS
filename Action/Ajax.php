<?php
namespace Action;
use HY\Action;

class AjaxAction extends HYBBS {

    public function __construct() {
		parent::__construct();
        {hook a_ajax_init}
        $this->view = 'admin';
    }
    //获取用户信息JSON
    //传入 GET [UID] 用户ID
    public function userjson(){
        {hook a_ajax_userjson_1}
    	$data = array('error'=>true);
    	$uid = intval(X("get.uid"));
    	if(!$uid)
    		return $this->json(array('error'=>false,'info'=>'缺少用户UID参数'));
        {hook a_ajax_userjson_2}
    	$User = M("User");
    	if(!$User->is_id($uid))
    		return $this->json(array('error'=>false,'info'=>'输入的UID用户不存在'));
        {hook a_ajax_userjson_3}
    	$ud = $User->read($uid);
    	$data['user'] = $ud['user'];
    	$data['avatar'] = $this->avatar($ud['user']);
    	$data['atime_str'] = humandate($ud['atime']);
    	$data['threads'] = $ud['threads'];
    	$data['posts'] = $ud['posts'];
    	$data['group'] = $ud['group'];
    	$data['groupname'] = $this->_usergroup[$ud['group']]['name'];
    	$data['gold'] = $ud['gold'];
    	$data['href'] = WWW . URL('my',$data['user']);
        {hook a_ajax_userjson_v}
    	return $this->json($data);
    }
    {hook a_ajax_fun}
}
