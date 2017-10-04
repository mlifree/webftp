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
 * 文件$ID ：  do.php
 +------------------------------------------------------------------------------
 * 路径$ID ：  do.php
 +------------------------------------------------------------------------------
 * 程序版本： 浩天 WebFTP V1.0.0 2011-10-01
 +------------------------------------------------------------------------------
 * 功能简介： 全局操作接口
 +------------------------------------------------------------------------------
 * 注意事项： 请勿私自删除此版权信息！
 +------------------------------------------------------------------------------
 **/
 
//系统初始化
require(dirname(__FILE__).'/inc/init.php');
G('_run_start');

//显示中文目录文件（会降低性能）
define('ZH_PATH_VIEW',  C('ZH_PATH_VIEW'));
//开启图片预览
define('IMG_FILE_VIEW', C('IMG_FILE_VIEW'));

$action = $_REQUEST['action'];
define('APP_ACTION', $action);
//权限检测
if('upload' === $action && 'update' !== $_REQUEST['type'] || 'editfile' === $action && 'save' !== $_REQUEST['type']){
	if(!checkLogin(false,true)){
		die('<center><font color="red">登陆超时或没有相应权限！</font></center>');
	}
}elseif('upload' === $action && 'update' === $_REQUEST['type']){
	
}else{
	checkLogin(true,true);
}
if($action=="list"){//文件列表
	$path = array();
	$path['root']    = str_replace('\\','/',realpath(trim(C('ROOT_PATH'),'/').'/').'/');
	$path['_SELF_']  = str_replace('\\','/',realpath(dirname(__FILE__)).'/');
	if(ZH_PATH_VIEW){		
		$_REQUEST['path']     = trim(str_replace(array('%2F','+'),array('/',' '),urlencode($_REQUEST['path'])),'/').'/';
		$path['current']      = str_replace('\\','/',realpath(u2g(urldecode($_REQUEST['path'])))).'/';
		$path['parent']       = str_replace('\\','/',g2u(realpath(dirname($path['current'])))).'/';		
		$path['current_utf8'] = trim(g2u($path['current']));
		//返回数据
		$data = array();
		$data['path']['root']    = trim(C('ROOT_PATH'),'/').'/';
		$data['path']['current'] = $data['path']['root'].str_replace($path['root'],'',$path['current_utf8']);
		$data['path']['parent']  = (strlen($path['parent']) < strlen($path['root']))?($data['path']['current']):($data['path']['root'].str_replace($path['root'],'',$path['parent']));        

	}else{
	    $path['current_utf8'] = str_replace('\\','/',realpath($_REQUEST['path']).'/');	    
		$path['parent']  = str_replace('\\','/',realpath(dirname($path['current_utf8'])).'/');
		$path['current'] = $path['current_utf8'];
		
		//返回数据
		$data = array();
		$data['path']['root']    = trim(C('ROOT_PATH'),'/').'/';
		$data['path']['current'] = $data['path']['root'].str_replace($path['root'],'',$path['current']);
		$data['path']['parent']  = (strlen($path['parent']) < strlen($path['root']))?($data['path']['current']):($data['path']['root'].str_replace($path['root'],'',$path['parent']));        
	}	
	if(strlen($path['current_utf8']) < strlen($path['root'])){
		$data = array();
		$data['statusCode'] = 300;
		$data['message'] = 'Sorry, 你无权查看'.$path['current'].'目录！';
		exit(json_encode($data));
	}elseif(is_dir($path['current'])){
		$Base = new Base();
		$sdir = array();$sfile = array();$size = array();		
		$Base->show_dir($path['current'],$sdir,$sfile,$size);		
		$data['statusCode'] = 200;
		$data['message'] = 'Success！';		
		$data['dirs'] = array();
		$data['files']= array();
		for($i=0;$i<count($sdir);$i++){
			$dir_arr_temp = array();
			if(in_array($data['path']['current'].$sdir[$i].'/', C('LIST_CONF.DISPLAY_NOTALLOW'))) continue;
            if(!ZH_PATH_VIEW){
			    $dir_arr_temp['name']  = $sdir[$i];			
			}else{
			    $dir_arr_temp['name']  = g2u($sdir[$i]);
			}				
			$dir_arr_temp['size']  = '暂不提供';
			$dir_arr_temp['mtime'] = date('Y-m-d H:i:s',filemtime($path['current'].$sdir[$i]));
			$dir_arr_temp['chmod'] = substr(sprintf('%o', @fileperms($path['current'].$sdir[$i])), -4);
			$data['dirs'][]        = $dir_arr_temp;
		}
		for($i=0;$i<count($sfile);$i++){
			$file_arr_temp = array();
			if(in_array($data['path']['current'].$sfile[$i], C('LIST_CONF.DISPLAY_NOTALLOW'))) continue;
			if(!ZH_PATH_VIEW){
			    $file_arr_temp['name']  = $sfile[$i];		
			}else{
			    $file_arr_temp['name']  = g2u($sfile[$i]);
			}
			$file_arr_temp['size']  = dealsize($size[$i]);
			$file_arr_temp['mtime'] = date('Y-m-d H:i:s', @filemtime($path['current'].$sfile[$i]));
			$file_arr_temp['ext']   = get_ext($sfile[$i]);
			$file_arr_temp['chmod'] = substr(sprintf('%o', @fileperms($path['current'].$sfile[$i])), -4);
			$data['files'][]        = $file_arr_temp;
		}
		
        //处理文件列表排序		
		if(!empty($_POST['order'])){
			$order = explode('|', $_POST['order']);//type(name、size、ext、mtime)|sort(asc、desc)
			//目录排序
			if(0<count($data['dirs'])){
				$arr = array();
				foreach ($data['dirs'] as $key => $value){
					$arr['name'][$key]   = $value['name'];
					$arr['size'][$key]   = $value['size'];				
					$arr['ext'][$key]    = $value['name'];
					$arr['mtime'][$key]  = $value['mtime'];
				}
				if('desc' == $order[1]){
					array_multisort($arr[$order[0]], SORT_DESC, $data['dirs']);
				}else{
					array_multisort($arr[$order[0]], SORT_ASC, $data['dirs']);
				}
			}
			//文件排序
			if(0<count($data['files'])){
				$arr = array();
				foreach ($data['files'] as $key => $value) {			
					if(stripos($value['size'], 'KB')){
						$arr['size'][$key]  = ((float)$value['size'])*1024;
					}elseif(stripos($value['size'], 'MB')){
						$arr['size'][$key]  = ((float)$value['size'])*1024*1024;
					}elseif(stripos($value['size'], 'Byte')){
						$arr['size'][$key] = ((float)$value['size'])*1;
					}else{
						$arr['size'][$key]   = $value['size'];
					}				
					$arr['name'][$key]   = $value['name'];
					$arr['ext'][$key]    = $value['ext'];
					$arr['mtime'][$key]  = $value['mtime'];
				}
				if('desc' == $order[1]){
					array_multisort($arr[$order[0]], SORT_DESC, $data['files']);
				}else{
					array_multisort($arr[$order[0]], SORT_ASC, $data['files']);
				}
			}
		}
		$data['runtime'] = G('_run_start','_run_end',6);		
		exit(json_encode($data));
	}else{
		$data = array();
		$data['statusCode'] = 300;
		$data['message'] = 'Sorry,未知错误,无法打开你请求的目录:'.$path['current'].'！';
		exit(json_encode($data));
	}
}elseif('downfile' == $action){//文件下载
    $type = trim($_REQUEST['type']);
	switch($type){
		case 1:{//下载单个文件
			$file = u2g(trim($_REQUEST['file']));
			if(file_exists($file) && is_file($file)){
				header('Content-type: application/force-download');
				header('Content-Disposition: attachment; filename='.g2u(basename($file)));
				header('Content-length: '.filesize($file));
				readfile($file);
				die();
			}else{
				exit("<script>alert('文件不存在!!');</script>");
			}
		}
		break;
		case 2:{ //下载单个目录
			$arr = array();
			$dir = u2g(trim($_REQUEST['dir'],'/').'/');
			$filename  = str_replace(array(realpath(dirname($dir)),'/','\\'),'',realpath($dir)).'-'.date('m-d').'.zip';			
			if(is_dir($dir)){
				if(is_file(DATA_CACHE_PATH.$filename)){
					@unlink(DATA_CACHE_PATH.$filename);
				}
			    require(INC_ROOT.'PclZip.class.php');
		        $Zip = new PclZip(DATA_CACHE_PATH.$filename);
				$arr[] = $dir;
			    if($Zip->create($arr,'down')){
			        header('Content-type: application/force-download');
			        header('Content-Disposition: attachment; filename='.$filename);
			        header('Content-length:'.filesize(DATA_CACHE_PATH.$filename));
					readfile(DATA_CACHE_PATH.$filename);
					@unlink(DATA_CACHE_PATH.$filename);
			        die();
				}else{
				    exit('<script>alert("下载失败!!");</script>');
				}
			}else{
			    exit("<script>alert('目录不存在!!');</script>");
			}
		}
		break;
		case 3:{ //混合下载
		    $arr = array();
			$files = u2g(trim($_REQUEST['files']));
			$arr    = explode('|',$files);
			$filename = 'down-'.date('m-d-h').'.zip';
			require(INC_ROOT.'PclZip.class.php');
		    $Zip = new PclZip(DATA_CACHE_PATH.$filename);
			if($Zip->create($arr, 'down')){
			    header('Content-type: application/force-download');
			    header('Content-Disposition: attachment; filename='.$filename);
			    header('Content-length:'.filesize(DATA_CACHE_PATH.$filename));
				readfile(DATA_CACHE_PATH.$filename);
				@unlink(DATA_CACHE_PATH.$filename);
			    exit();
			}else{
				exit('<script>alert("下载失败!!");</script>');
			}
		}
		break;
	}
}elseif('unzip' == $action){//ZIP解压
	$path    = u2g(trim($_REQUEST['path'],'/').'/');
	$name    = u2g(trim($_REQUEST['name']));
	$remove  = (int)trim($_REQUEST['remove']);
	$unzippath = u2g(trim($_REQUEST['unzippath'],'/').'/');	
	if(file_exists($path.$name) && is_file($path.$name)){
		require(INC_ROOT.'PclZip.class.php');
		$zip = new PclZip($path.$name);
		$result = $zip->extract($path.(('./' == $unzippath)?'':$unzippath), $remove);
		$data = array();
		if($result){
			$data['statusCode'] = 200;
			$list = $zip->listContent();
			$fold = 0; $fil = 0; $tot_comp = 0; $tot_uncomp = 0;
			foreach($list as $key=>$val){if ($val['folder']=='1') {++$fold;}else{++$fil;$tot_comp += $val['compressed_size'];$tot_uncomp += $val['size'];}}
			G('_unzip_end');
			$data['message']  = '<font color="green">解压目标文件：</font><font color="red"> '.g2u($name).'</font><br />';
			$data['message'] .= '<font color="green">解压文件详情：</font><font color="red">共'.$fold.' 个目录，'.$fil.' 个文件</font><br />';
			$data['message'] .= '<font color="green">压缩文档大小：</font><font color="red">'.dealsize($tot_comp).'</font><br />';
			$data['message'] .= '<font color="green">解压文档大小：</font><font color="red">'.dealsize($tot_uncomp).'</font><br />';
			$data['message'] .= '<font color="green">解压总计耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
		}else{
			$data['statusCode'] = 300;
			$data['message']   .= '<font color="blue">解压失败：</font><font color="red">'.$zip->errorInfo(true).'</font><br />';
			$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
		}
		exit(json_encode($data));
	}else{
		$data = array();
		$data['statusCode'] = 300;
		$data['message'] = 'Sorry,未知错误,无法解压:'.$path.$name.'文件！';
		exit(json_encode($data));
	}
}elseif('viewzip' == $action){//ZIP预览
	$path    = u2g(trim($_REQUEST['path'],'/').'/');
	$name    = u2g(trim($_REQUEST['name']));
	$file    = $path.$name;
    if(file_exists($file) && is_file($file)){
		require(INC_ROOT.'PclZip.class.php');
		$zip = new PclZip($file);
		$list = $zip->listContent();
		$data = array();
		if($list){
			$data['statusCode'] = 200;			
			$fold = 0; $fil = 0; $tot_comp = 0; $tot_uncomp = 0;
			foreach($list as $key=>$val){if ($val['folder']=='1') {++$fold;}else{++$fil;$tot_comp += $val['compressed_size'];$tot_uncomp += $val['size'];}}
			$data['message']  = '<font color="green">解压目标文件：</font><font color="red"> '.g2u($name).'</font><br />';
			$data['message'] .= '<font color="green">解压文件详情：</font><font color="red">共'.$fold.' 个目录，'.$fil.' 个文件</font><br />';
			$data['message'] .= '<font color="green">压缩文档大小：</font><font color="red">'.dealsize($tot_comp).'</font><br />';
			$data['message'] .= '<font color="green">解压文档大小：</font><font color="red">'.dealsize($tot_uncomp).'</font><br />';
			$data['message'] .= '<font color="green">文件列表：</font><br />';
			foreach($list as $key => $val){
				$data['message'] .= '<font color="red">'.$key.'=>'.($val['folder']?'目录:':'文件:').g2u($val['filename']).' </font><br />';
			}
		}else{
			$data['statusCode'] = 300;
			$data['message']   .= '<font color="blue">解压失败：</font><font color="red">'.$zip->errorInfo(true).'</font><br />';
		}
		$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
		exit(json_encode($data));
	}else{
		$data = array();
		$data['statusCode'] = 300;
		$data['message'] = 'Sorry,未知错误,无法预览:'.$file.'文件！';
		exit(json_encode($data));
	}
}elseif('zip' == $action){//ZIP压缩
        $data   = array();
		$files  = u2g(trim($_REQUEST['files']));
		$arr    = explode('|',$files);
		if(1 == $_REQUEST['type'] && is_file($arr[0])){
			$info = pathinfo($arr[0]);
			$path = $info['dirname'];
			$name = $info['basename'];
		    $zipname = $path.'/'.$name.date('-m-d').'.zip';
		}elseif(2 == $_REQUEST['type'] && is_dir($arr[0])){
			$path = dirname($arr[0]);
			$name = trim(str_replace(dirname($arr[0]), '', $arr[0]),'/');
		    $zipname = $path.'/'.$name.date('-m-d').'.zip';
		}else{
		    $path = dirname($arr[0]);
			$name = trim(str_replace(dirname(dirname($arr[0])), '', dirname($arr[0])),'/');
		    $zipname = $path.'/'.$name.date('-m-d').'.zip';
		}
		require(INC_ROOT.'PclZip.class.php');
		$Zip = new PclZip($zipname);
		if(!file_exists($zipname) && $Zip->create($arr, 'zip')){
			$data['statusCode'] = 200;	
		    $list = $Zip->listContent();
		    if($list){					
				$fold = 0; $fil = 0; $tot_comp = 0; $tot_uncomp = 0;
				foreach($list as $key=>$val){if ($val['folder']=='1') {++$fold;}else{++$fil;$tot_comp += $val['compressed_size'];$tot_uncomp += $val['size'];}}
				$data['message']  = '<font color="green">压缩目标文件：</font><font color="red"> '.g2u($zipname).'</font><br />';
				$data['message'] .= '<font color="green">压缩文件详情：</font><font color="red">共'.$fold.' 个目录，'.$fil.' 个文件</font><br />';
				$data['message'] .= '<font color="green">压缩文档大小：</font><font color="red">'.dealsize($tot_comp).'</font><br />';
				$data['message'] .= '<font color="green">解压文档大小：</font><font color="red">'.dealsize($tot_uncomp).'</font><br />';
				$data['message'] .= '<font color="green">压缩执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
				$data['message'] .= '<font color="green">压缩文件列表：</font><br />';
				foreach($list as $key => $val){
				    if(10 > $key){$key = '00'.$key;}elseif(100 > $key){$key = '0'.$key;}
					$data['message'] .= '<font color="red">'.$key.'=>'.($val['folder']?'目录:':'文件:').g2u($val['filename']).' </font><br />';
				}
			}
		}else{
			$data['statusCode'] = 300;
			if(file_exists($zipname)){$error = $zipname.'已经存在！';}else{$error = $Zip->errorInfo(true);}
			$data['message']    = '<font color="blue">压缩失败：</font><font color="red">'.$error.'</font><br />';
			$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
		}
		
		exit(json_encode($data));
}elseif('delete' == $action){
	$type = trim($_REQUEST['type']);
	$data = array();
	switch($type){
		case 1:{//单个文件删除
			$file = u2g(trim($_REQUEST['file']));		
			if(file_exists($file) && @unlink($file)){
				$data['statusCode'] = 200;
				$data['message']    = '<font color="green">成功删除：</font><font color="red"> '.g2u($file).'</font><br />';
			}else{
				$data['statusCode'] = 300;
				$data['message']    = '<font color="blue">删除失败：</font><font color="red"> '.g2u($file).'</font><br />';
			}
		}break;
		case 2:{//单个目录删除
			$dir  = u2g(trim($_REQUEST['dir']));
			$info = array('dir'=>0,'file'=>0);$err = array('dir'=>array(),'file'=>array());
			$Base = new Base();
			if($Base->del_dir($dir,$info,$err)){
				$data['statusCode']  = 200;
				$data['message']     = '<font color="green">成功删除：</font><font color="red"> '.g2u($dir).'</font><br />';				
				$data['message']    .= '<font color="green">总计删除：</font><font color="red">'.$info['dir'].' 个目录，'.$info['file'].' 个文件</font><br />';
			
			}else{
				$data['statusCode']  = 300;
				$data['message']     = '<font color="blue">删除失败：</font><font color="red"> '.g2u($dir).'</font><br />';
				foreach($err['dir'] as $val){
				    $data['message'].= '<font color="blue">删除失败：</font><font color="red"> '.g2u($val).'</font><br />';
				}
				foreach($err['file']  as $val){
				    $data['message'].= '<font color="blue">删除失败：</font><font color="red"> '.g2u($val).'</font><br />';
				}
			}	
		}break;
		case 3:{//混合删除
			$files  = explode('|',u2g(trim($_REQUEST['files'])));	
			$nfile = 0; $ndir = 0; $message = '';
			$Base  = new Base();
			foreach($files as $f){ 
			    $info = array('dir'=>0,'file'=>0);$err = array('dir'=>array(),'file'=>array());
			    if(is_dir($f) && $Base->del_dir($f,$info,$err)){
				  	$ndir += $info['dir'];	$nfile += $info['file'];
					foreach($err['dir'] as $val){
				        $data['message'].= '<font color="blue">删除失败：</font><font color="red"> '.g2u($val).'</font><br />';
				    }
				    foreach($err['file']  as $val){
				        $data['message'].= '<font color="blue">删除失败：</font><font color="red"> '.g2u($val).'</font><br />';
				    }
			    }elseif(is_file($f) && unlink($f)){
				    ++$nfile;
			    }else{
					$message .= '<font color="blue">删除失败：</font><font color="red"> '.g2u($f).'</font><br />';
			    }			
			}
			$data['statusCode'] = 200;
			$data['message']    = '<font color="green">总计删除：</font><font color="red">'.$ndir.'目录，'.$nfile.'个文件</font><br />';
            $data['message']   .= $message;			
		}break;
	}
	$data['message']    .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';		
	exit(json_encode($data));
}elseif('property' == $action){//目录详情
        $data = array();
		$path  = u2g(trim($_REQUEST['path'],'/').'/');	
        $Base  = new Base();
        $info  = $Base->getProperty($path);
		$data['statusCode'] = 200;
		$data['message']  = '<font color="green">当前目录：</font><font color="red">'.g2u($path).'</font><br />';
		$data['message'] .= '<font color="green">目录详情：</font><font color="red">共'.$info['dir'].' 个目录，'.$info['file'].' 个文件</font><br />';
		$data['message'] .= '<font color="green">总计大小：</font><font color="red">'.dealsize($info['size']).'</font><br />';
		$data['message'] .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
        exit(json_encode($data));
}elseif('upload' == $action){//批量上传文件
    require(ROOT.'static/template/upload.tpl.php');
}elseif('editfile' == $action){//文本编辑
    if('save' !== $_REQUEST['type']){ 
		require(ROOT.'static/template/editfile.tpl.php');   
	}elseif('save' == $_REQUEST['type']){		
		$file    = u2g($_REQUEST['file']);
		$code    = urldecode(trim($_REQUEST['code']));		
		$charset = trim($_REQUEST['charset']);
		$newname = trim($_REQUEST['newname']);		
		$data = array();
		$data['message']  = '<font color="green">目标文件：</font><font color="red">'.g2u($file).'</font><br />';
		if(file_exists($file)){
			$oldcharset = get_encode($file);
			if('UTF-8' == $charset && 'GB2312' == $oldcharset){
				$code = $code;
			}elseif('UTF-8' == $charset && 'UTF-8' == $oldcharset){
				$code = $code;
			}elseif('UTF-8' == $charset && 'UTF-8 BOM' == $oldcharset){
				$code = stripBOM($code);//处理UTF-8 BOM 文件头
			}elseif('GB2312' == $charset && 'GB2312' == $oldcharset){
				$code = $code;
			}elseif('GB2312' == $charset && 'UTF-8' == $oldcharset){
				$code = $code;
			}elseif('GB2312' == $charset && 'UTF-8 BOM' == $oldcharset){
				$code = $code;
			}
			if(!empty($newname)){
				$file = dirname($file).'/'.$newname;
				if(!file_exists($file)){					
					$fp     = @fopen($file, 'w+');
					$result = file_put_contents($file, $code);
					if($fp && $result){
						$data['statusCode'] = 200;
						$data['message']    = '<font color="green">保存成功：</font><font color="red">文件已经保存</font><br />';
					}else{
						$data['statusCode'] = 300;
						$data['message']    = '<font color="blue">保存失败：</font><font color="red">错误原因未知</font><br />';
					}
				}else{
					$data['statusCode'] = 300;
					$data['message']    = '<font color="blue">另存失败：</font><font color="red">'.g2u($file).'已存在</font><br />';
				}
			}elseif(empty($newname) && file_put_contents($file, $code)){
				$data['statusCode'] = 200;
				$data['message']    = '<font color="green">保存成功：</font><font color="red">文件已经保存</font><br />';
				$data['message']   .= '<font color="green">编码变更：</font><font color="red">'.$oldcharset.'=>'.$charset.'</font><br />';				
			}else{
				$data['statusCode'] = 300;
				$data['message']    = '<font color="blue">保存失败：</font><font color="red">错误原因未知</font><br />';
			}
		}else{
			$data['statusCode'] = 300;	
			$data['message']  = '<font color="green">保存失败：</font><font color="red">目标文件不存在</font><br />';
		}	    	
		$data['message'] .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
		exit(json_encode($data));
	}
}elseif($action=="paste"){//文件移动、复制、粘贴
	$path_from  = u2g(trim($_REQUEST['path_from'],'/').'/');
	$path_to    = u2g(trim($_REQUEST['path_to'],'/').'/');
	$files      = explode('|',u2g(trim($_REQUEST['files'])));
	$type       = trim($_REQUEST['type']);
	if('cut' == $type){$cut = true;}else{$cut = false;}
	$cover      = (bool)trim($_REQUEST['cover']);
	$temp = array('from'=>array(),'to'=>array()); $coverfiles = $info = $data = array();
	foreach($files as $val){
	    if(is_file($path_from.$val)){
		    $temp['from'][] = $path_from.$val;
			$temp['to'][]   = $path_to.$val;
		}elseif(is_dir($path_from.$val)){
		   	$temp['from'][]  = $path_from.$val;
			$temp['to'][]    = $path_to.$val;
		}else{
		}
	}
	$Base = new Base();
	$Base->copy($temp['from'],$temp['to'],$cover,$cut,$coverfiles, $info);
	$data['message']  = '<font color="green">目录变更：</font><font color="red">'.g2u($path_from).'</font><font color="blue">  =>  </font><font color="red">'.g2u($path_to).'</font><br />';
	if (!$cover && is_array($coverfiles) && !empty($coverfiles)){
		foreach ($coverfiles as $i){
		    $data['statusCode'] = 201;
			$data['data'] = array('type'=>$type,'path_from'=>$path_from,'path_to'=>$path_to,'files'=>trim($_REQUEST['files']),'cover'=>$cover);
		    $coverfile = str_replace($path_from,'',$i);
			$data['message'] .= '<font color="blue">覆盖文件：</font><font color="red">'.g2u($coverfile).'</font><br />';
		}
	}else{
		$data['statusCode'] = 200;
		$data['message'] .= '<font color="green">变更详情：</font><font color="red">共'.(($cut)?'移动':'复制').'目录'.$info['dir'].'个,文件'.$info['file'].'个</font><br />';
		$data['message'] .= '<font color="green">总计大小：</font><font color="red">'.dealsize($info['size']).'</font><br />';	
	}	
	$data['message'] .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
    exit(json_encode($data));
}elseif('chmod' == $action){
	$type = trim($_REQUEST['type']);
	$data = array();
	switch($type){
		case 3:{//批量权限修改
			if('show' == trim($_REQUEST['mode'])){require(ROOT.'static/template/chmodfile.tpl.php');exit();}
			$files = explode('|',u2g(trim($_REQUEST['files'])));
			$chmods = $chmod = ((int)trim($_REQUEST['chmod']));
			$deep  = (bool)trim($_REQUEST['deep']);
			require(CONF_ROOT.'chmod.conf.php');
			$Base = new Base();
			$nfile = 0; $ndir = 0; $message = '';	            
            foreach($files as $f){ 
				if($deep && is_dir($f)){
				    $info = $err = array();
				    $Base->chmod($f,$chmod,$info,$err);
					$nfile += $info['file'];$ndir += $info['dir'];
					foreach($err['dir'] as $val)
						$message .= '<font color="blue">修改失败：</font><font color="red"> '.g2u($val).'</font><br />';
					foreach($err['file'] as $val)
						$message .= '<font color="blue">修改失败：</font><font color="red"> '.g2u($val).'</font><br />';
				}else{
					if(is_dir($f)){$ndir++;}else{$nfile++;}
					if(!@chmod($f,$chmod)){$message .= '<font color="blue">修改失败：</font><font color="red"> '.g2u($f).'</font><br />';}					
				}
			}
			$data['statusCode'] = 200;
			$data['message']    = '<font color="green">权限变更：</font><font color="red"> 0'.$chmods.'</font><br />';
			$data['message']   .= '<font color="green">总计修改：</font><font color="red">'.$ndir.'个目录，'.$nfile.'个文件</font><br />';
			$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
            $data['message']   .= $message;			
		}break;
	}
	exit(json_encode($data));
}elseif('rename' == $action){
	$path    = u2g(trim($_REQUEST['path'], '/').'/');
	$oldname = $path.u2g(trim($_REQUEST['oldname'], '/'));
	$newname = $path.u2g(trim($_REQUEST['newname'], '/'));
	$data = array();
    if(!file_exists($oldname) || file_exists($newname)){
		$data['statusCode'] = 300;
		$data['message']    = '<font color="blue">命名失败：</font><font color="red">源文件不存在或新文件名和已文件有冲突</font><br />';
	}else{
	    $result = (bool)@rename($oldname, $newname);
		if($result){
			$data['statusCode'] = 200;
			$data['message']    = '<font color="green">命名成功：</font><font color="red">'.g2u(basename($oldname)).'</font><font color="blue">  =>  </font><font color="red">'.g2u(basename($newname)).'</font><br />';
		}else{
			$data['statusCode'] = 300;
			$data['message']    = '<font color="blue">命名失败：</font><font color="red">错误原因未知</font><br />';
		}
	}
	$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
	exit(json_encode($data));
}elseif('newbuild' == $action){
    $path = u2g(trim($_REQUEST['path'], '/').'/');
	$type = u2g(trim($_REQUEST['type']));
	$name = $path.u2g(trim($_REQUEST['name']));
	$data = array();
	if('file' == $type){
		if(is_file($name)){
			$data['statusCode'] = 300;
			$data['message']    = '<font color="blue">新建失败：</font><font color="red">文件已存在</font><br />';	
		}else{
			$result = (bool)fopen($name, 'a+');
			file_put_contents($name, 'newfile at '.date('Y-m-d H:i:s'));
			if($result){
				$data['statusCode'] = 200;
				$data['message']    = '<font color="green">新建成功：</font><font color="red">'.g2u(basename($name)).'</font><br />';
			}else{
				$data['statusCode'] = 300;
				$data['message']    = '<font color="blue">新建失败：</font><font color="red">错误原因未知</font><br />';
			}
		}	
	}elseif('dir' == $type){
		if(is_dir($name)){
			$data['statusCode'] = 300;
			$data['message']    = '<font color="blue">新建失败：</font><font color="red">目录已存在</font><br />';	
		}else{
			$result = (bool)@mkdir($name, 0755);
			if($result){
				$data['statusCode'] = 200;
				$data['message']    = '<font color="green">新建成功：</font><font color="red">'.g2u(basename($name)).'</font><br />';
			}else{
				$data['statusCode'] = 300;
				$data['message']    = '<font color="blue">新建失败：</font><font color="red">错误原因未知</font><br />';
			}
		}	
	}else{
		$data['statusCode'] = 300;
		$data['message']    = '<font color="blue">操作失败：</font><font color="red">未知操作指令</font><br />';
	}
	$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';
	exit(json_encode($data));	
}elseif('imageview' == $action){
	$file = u2g(trim($_REQUEST['file']));
	require(INC_ROOT.'Image.class.php');
	$Image = new Image();
	$thumbFile = DATA_CACHE_PATH.substr(md5($file),2,12).'.'.get_ext($file);
	if(false !== strpos($file, 'data/Cache/')){
		$Image->showImg(DATA_CACHE_PATH.basename($file),'',120,100);//die();
	}else{
		if(!is_file($thumbFile)){
			$Image->thumb( $file, $thumbFile, get_ext($file), 120, 100, true);
		}
		if(!$Image->showImg($thumbFile,'',120,100)){
			$Image->showImg(DATA_PUBLIC_PATH.'nothumb.png','',120,100);
		}
		if(C('CACHE_DATA_DEL')){unlink($thumbFile);}
	}
	
}elseif('codeExplorer' == $action){//批量上传文件
    require(ROOT.'static/template/upload.tpl.php');
}else{
    $data['statusCode'] = 300;
	$data['message']    = '<font color="green">错误命令：</font><font color="red">未知API</font><br />';
	$data['message']   .= '<font color="green">执行耗时：</font><font color="red">'.G('_run_start','_run_end',6).' 秒</font><br />';           
	exit(json_encode($data));
}
?>