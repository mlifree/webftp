<?php
// +----------------------------------------------------------------------
// | Copyright (C) 浩天科技 www.ihotte.com admin@ihotte.com
// +----------------------------------------------------------------------
// | Licensed: ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:   左手边的回忆 QQ:858908467 E-mail:858908467@qq.com
// +----------------------------------------------------------------------
/**
 +------------------------------------------------------------------------------
 * 文件$ID ： index.php
 +------------------------------------------------------------------------------
 * 路径$ID ： index.php
 +------------------------------------------------------------------------------
 * 程序版本： 浩天 WebFTP V1.0.0 2011-10-01
 +------------------------------------------------------------------------------
 * 功能简介： 默认首页 Index
 +------------------------------------------------------------------------------
 * 注意事项： 请勿私自删除此版权信息！
 +------------------------------------------------------------------------------
 **/

require(dirname(__FILE__).'/inc/init.php');
if(!checkLogin(false, false)){
	header('Location:./login.php');
};
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WebFTP V1.3</title>
<link type="text/css" rel="stylesheet" href="static/css/main.css" />
<link type="text/css" rel="stylesheet" href="static/css/style.css" />
<link type="text/css" rel="stylesheet" href="static/css/fileico.css" />
<link type="text/css" rel="stylesheet" href="static/css/toolbar.css" />
<script type="text/javascript" src="jQuery/plugins/Rookie/Rookie.js?jQuery/plugins/Rookie/rookie.swf"></script>
<script type="text/javascript" src="jQuery/lib/jquery-1.4.2.min.js"></script>
</head>
<body id="body">
<?php require(dirname(__FILE__).'/static/template/loading.tpl.php');?>
<!---Loading...--->
<div id='loading'>正在加载...</div>
<!---Loading...--->
<div id="header">
  <h1 id="logo"><a target="_blank" href="http://www.ihotte.com/?s=My-webftp-index">WebFTP</a><i class="cline"></i><span>WebFTP</span></h1>
</div>
<div id="main">
  <div class="top"></div>
  <div class="col-sub">
    <ul id="main-menu">
      <li id="help1"><span><i class="icon"></i><a target="_self" href="javascript:getproperty();">目录属性</a></span> </li>
      <li id="help2"><span><i class="icon"></i><a target="_self" href="javascript:refresh(true);" >刷新目录</a></span> </li>
      <li id="help3"><span><i class="icon"></i><a target="_self" href="javascript:newbuild('dir',{});" >新建目录</a></span> </li>
      <li id="help4"><span><i class="icon"></i><a target="_self" href="javascript:newbuild('file',{});" >新建文件</a></span> </li>
      <!--li id="help5"><span><i class="icon"></i><a target="_self" href="javascript:upload();">上传文件</a></span> </li>
      <li id="help6"><span><i class="icon"></i><a target="_self" href="javascript:switchStyle();" rel="listStyle">切换视图</a></span> </li>
	  <li id="help6"><span><i class="icon"></i><a target="_self" href="javascript:switchCache();" rel="cacheStyle">本地缓存</a></span> </li>
	  <li id="help7"><span><i class="icon"></i><a target="_self" href="javascript:imageStyle();" rel="imageStyle">图片预览</a></span> </li>
	  <li id="help8"><span><i class="icon"></i><a target="_self" href="javascript:propertyStyle();" rel="propertyStyle">属性提示</a></span> </li>
      <li id="help6"><span><i class="icon"></i><a target="_self" href="javascript:gohome();">官方网站</a></span> </li>
	  <li id="help7"><span><i class="icon"></i><a target="_self" href="javascript:gobbs();">官方论坛</a></span> </li-->
      <li id="help9"><span><i class="icon"></i><a target="_self" href="javascript:resetpass();">修改密码</a></span> </li>
	  <!--<li id="help9"><span><i class="icon"></i><a target="_blank" href="./ftp.php">远程模式</a></span> </li>-->
      <li id="help10"><span><i class="icon"></i><a target="_self" href="javascript:loginout();">安全退出</a></span> </li>
	 
    </ul>
  </div>
  <div class="col-main">
    <?php require(dirname(__FILE__).'/static/template/list.tpl.php');?>
  </div>
  <div class="bottom"></div>
</div>
<div id="footer"></div>
<div id="mycontextMenu"></div>
<a href="javascript:;" class="go-top" id="js_go_top">返回顶部</a>


<link type="text/css" rel="stylesheet" href="jQuery/plugins/asyncbox/skins/Ext/asyncbox.css"  />
<script type="text/javascript" src="jQuery/plugins/asyncbox/AsyncBox.v1.4.js"></script>

<!-- 右键菜单 -->
<link type="text/css" rel="stylesheet" href="jQuery/plugins/contextMenu/jquery.contextMenu.css" />
<script type="text/javascript" src="jQuery/plugins/contextMenu/jquery.contextMenu.js" ></script>

<!-- 图片预览 -->
<link type="text/css" rel="stylesheet" href="jQuery/plugins/colorbox/skins/default/colorbox.css"  />
<script type="text/javascript" src="jQuery/plugins/colorbox/jquery.colorbox.js"></script>

<script type="text/javascript" src="static/js/webftp.config.php"></script>
<script type="text/javascript" src="static/js/webftp.fun.js"></script>
<script type="text/javascript" src="static/js/webftp.md5.js" ></script>
<script type="text/javascript" src="static/js/webftp.hotkeys.js" ></script>

<script type="text/javascript" src="static/js/webftp.rookie.js" ></script>
<script type="text/javascript" src="static/js/webftp.cookie.js" ></script>

<script type="text/javascript" src="static/js/webftp.menu.js"></script>
<script type="text/javascript" src="static/js/webftp.show.js"></script>
<script type="text/javascript" src="static/js/webftp.ui.js"></script>
<script type="text/javascript" src="static/js/webftp.ajax.js"></script>

<link type="text/css" rel="stylesheet" href="jQuery/plugins/TitleEdit/css/fm.css"  />
<script type="text/javascript" src="jQuery/plugins/TitleEdit/js/jquery.fm.js"></script>

<script type="text/javascript">
$(function(){initUI();});
$(function(){
    initUI();
    setTimeout(function(){
    	refresh(true);
    },650);
  	unloading();
});
</script>
<script language="javascript">

//快捷热键注册
$(function(){
	var hotkeysWindowNum   = 5;
	var hotkeysDocumentNum = 5;
	var hotkeysClickNum    = 1;

	/************************ windows 命名空间 ********************************/
	//Ctrl+a 全选/反选
	jQuery(window).bind('keydown', 'Ctrl+a', function (evt){
		if(1 === hotkeysClickNum){selectAll(); hotkeysClickNum++; return false; }else if(hotkeysWindowNum <= hotkeysClickNum){hotkeysClickNum = 1; return false;}else{hotkeysClickNum++; return false;}
	});

	//Ctrl+r 刷新
	jQuery(window).bind('keydown', 'Ctrl+r', function (evt){
		if(1 === hotkeysClickNum){	refresh(true); hotkeysClickNum++; return false; }else if(hotkeysWindowNum <= hotkeysClickNum){hotkeysClickNum = 1; return false;}else{hotkeysClickNum++; return false;}
	});

	//Ctrl+s 列表风格切换
	jQuery(window).bind('keydown', 'Ctrl+s', function (evt){
		if(1 === hotkeysClickNum){ switchStyle(); hotkeysClickNum++; return false; }else if(hotkeysWindowNum <= hotkeysClickNum){hotkeysClickNum = 1; return false;}else{hotkeysClickNum++; return false;}
	});

	//Ctrl+q 退出
	jQuery(window).bind('keydown', 'Ctrl+q', function (evt){
		if(1 === hotkeysClickNum){	loginout(); hotkeysClickNum++; return false; }else if(hotkeysWindowNum <= hotkeysClickNum){hotkeysClickNum = 1; return false;}else{hotkeysClickNum++; return false;}
	});
	
	//远程模式
	jQuery(window).bind('keydown', 'Ctrl+y',  function (evt){ 
		if(1 === hotkeysClickNum){	open('./ftp.php'); hotkeysClickNum++; return false; }else if(hotkeysWindowNum <= hotkeysClickNum){hotkeysClickNum = 1; return false;}else{hotkeysClickNum++; return false;}
	});
	


	/************************ document 命名空间 ********************************/
	//Ctrl+x 全局剪切
	jQuery(document).bind('keydown', 'Ctrl+x', function (evt){ cut(3,{});return false; });
	
	//Ctrl+c 全局复制
	jQuery(document).bind('keydown', 'Ctrl+c', function (evt){ copy(3,{});return false; });
	
	//Ctrl+v 全局粘贴
	jQuery(document).bind('keydown', 'Ctrl+v', function (evt){ paste(3,{});return false; });
	
	//Ctrl+v 全局粘贴
	jQuery(document).bind('keydown', 'Ctrl+d', function (evt){ del(3,{});return false; });

	//Alt+n 新建目录
	jQuery(document).bind('keydown', 'Alt+n',  function (evt){ newbuild('dir',{});return false; });
	
	//Alt+m 新建文件
	jQuery(document).bind('keydown', 'Alt+m',  function (evt){ newbuild('file',{});return false; });
	

});
</script>
</body>
</html>