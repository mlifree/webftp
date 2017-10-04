<?php
require_once(dirname(__FILE__).'/inc/init.php');
G('_run_start');
function write_user_data($name, $password, $group){
	if(isset($name) && !empty($name) && isset($password) && !empty($password)){
		$user['defender'] = '<?php if(!defined("INC_ROOT")){die("Forbidden Access");};?>';
		$user['file']     = DATA_USER_PATH.md5($name).'.php';
		file_put_contents($user['file'], $user['defender'].serialize(array('name'=>$name,'password'=>md5($password), 'group'=>$group)));
	}
}
function write_group_data($name, $auth = array()){
	if(isset($name) && !empty($name)){
		$group['defender'] = '<?php if(!defined("INC_ROOT")){die("Forbidden Access");};?>';
		$group['file']     = DATA_GROUP_PATH.md5($name).'.php';
		file_put_contents($group['file'], $group['defender'].serialize(array('name'=>$name,'auth'=>$auth)));
	}
}
/*
//写入用户、分组数据(部署后请务必删除)
write_user_data('admin', 'admin', 'admin');
write_group_data('admin', array('*','list'));
			
write_user_data('demo', 'demo', 'demo');
write_group_data('demo', array('list','imageview'));
*/

$action = (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) ? trim($_REQUEST['action']) : 'in';
if('out' == $action){
	//session_destroy();
	unset($_SESSION['user_name']); 		
	unset($_SESSION['user_password']);	
	unset($_SESSION['user_auth']);
	
	setcookie(C('COOKIE_PREFIX').'user_name','',time()-3600*10);
	setcookie(C('COOKIE_PREFIX').'user_password','',time()-3600*10);
	header('Location:./login.php?action=in');
}elseif('in' == $action){
	if(checkLogin(false)){
		header('Location:./');die();
	}else{
		require(dirname(__FILE__).'/static/template/login.tpl.php');
	}	
}elseif('check' == $action){//登陆检测
    $info             = array();
	$info['name']     = trim($_POST['login']['name']);
	$info['password'] = trim($_POST['login']['password']);
	
	$data             = array();
	$data['user']     = DATA_USER_PATH.md5($info['name']).'.php';
	$data['group']    = DATA_GROUP_PATH.md5($info['name']).'.php';
	$data['defender'] = '<?php if(!defined("INC_ROOT")){die("Forbidden Access");};?>';
		
	if(empty($info['name']) || empty($info['password'])){
	    $error_msg = '账户、密码不能为空！';
	}elseif(file_exists($data['user'])){	
		//提取用户、分组数据
	    $user_admin = unserialize(str_ireplace($data['defender'], '', file_get_contents($data['user'])));
		$group_admin = unserialize(str_ireplace($data['defender'], '', file_get_contents($data['group'])));

		if(md5($info['password']) === $user_admin['password']){			
			$_SESSION['user_name']     = $user_admin['name']; 
			setcookie(C('COOKIE_PREFIX').'user_name', $user_admin['name'], time()+C('COOKIE_EXPIRE'));
			
			$_SESSION['user_password'] = $user_admin['password'];
			setcookie(C('COOKIE_PREFIX').'user_password', $user_admin['password'], time()+C('COOKIE_EXPIRE'));
			
			$_SESSION['user_auth'] = implode('|', $group_admin['auth']);
			setcookie(C('COOKIE_PREFIX').'user_auth', implode('|', $group_admin['auth']), time()+C('COOKIE_EXPIRE'));
			
		    header('Location:./');
		}else{
		    $error_msg = '密码错误！';
		}
	}else{	
	    $error_msg = '账户不存在！';
	}
	require(dirname(__FILE__).'/static/template/login.tpl.php');
}elseif('resetpass' == $action){//重置密码
	$type = trim($_REQUEST['type']);
	if('check' == $type){
		$oldpassword = trim($_REQUEST['oldpassword']);
		if(!empty($oldpassword) && md5($oldpassword) == $_SESSION['user_password']){
			$data['statusCode'] = 200;
			$data['message']    = '<font color="green">验证成功：</font><font color="red">原始密码正确</font><br />';
		}else{
			$data['statusCode'] = 300;
			$data['message']    = '<font color="blue">验证失败：</font><font color="red">原始密码错误</font><br />';
		}			
	}elseif('update' == $type){
		$user             = array();
		$user['data']     = DATA_USER_PATH.md5($_SESSION['user_name']).'.php';
		$user['defender'] = '<?php if(!defined("INC_ROOT")){die("Forbidden Access");};?>';
		$newpassword = trim($_REQUEST['newpassword']);
		if(file_exists($user['data'])){		
			if(!empty($newpassword)){
				$user_admin = unserialize(str_ireplace($user['defender'], '', file_get_contents($user['data'])));
				$user_admin['password'] = md5($newpassword);
				file_put_contents($user['data'], $user['defender'].serialize($user_admin));
				$data['statusCode'] = 200;
				$data['message']    = '<font color="green">修改成功：</font><font color="red">密码修改成功</font><br />';
			}else{
				$data['statusCode'] = 300;
				$data['message']    = '<font color="blue">修改失败：</font><font color="red">密码修改失败</font><br />';
			}			
		}else{
			$data['statusCode'] = 300;
			$data['message']    = '<font color="blue">修改失败：</font><font color="red">密码修改失败</font><br />';
		}	
	}else{
		$data['statusCode'] = 300;
		$data['message']    = '<font color="blue">操作失败：</font><font color="red">未知操作指令</font><br />';
	}
	$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
	exit(json_encode($data));
}else{
  exit('<center>Forbidden Access！</center>');
}

?>