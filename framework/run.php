<?php
/*
*	@run.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited.
*	
*	This library is free software; you can redistribute it and/or
*	modify it under the terms of the GNU Lesser General Public
*	License as published by the Free Software Foundation; either
*	version 2.1 of the License, or (at your option) any later version.

*	This library is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*	GNU Lesser General Public License for more details.
*/

if(!defined('APP_NAME'))
{
	exit('Undefined APP_NAME');
}

define('APP_PATH', BASE_PATH .'/'. APP_NAME . '/');

$config = include APP_PATH .'config.php';

if($config['SHOW_ERROR'] == 1)
{
	@ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
}
else
{
	@ini_set('display_errors', 0);
	error_reporting(0);
}

if(isset($config['TIME_LIMIT']))
{
	set_time_limit($config['TIME_LIMIT']);
}

if(isset($config['TIMEZONE']))
{
	date_default_timezone_set($config['TIMEZONE']);
}

define('CORE_PATH', str_replace('\\','/',dirname(__FILE__)));
require(CORE_PATH .'/core.php');

if(!get_magic_quotes_gpc())
{
	!empty($_GET) && $_GET = addslashes_deep($_GET);
	!empty($_POST) && $_POST = addslashes_deep($_POST);
	!empty($_COOKIE) && $_COOKIE = addslashes_deep($_COOKIE);
}

if($_REQUEST['session_id'])
{
	session_id(trim($_REQUEST['session_id']));
}
session_start();

$router = array();
if(file_exists(APP_PATH .'router.php'))
{
	$router = include APP_PATH .'router.php';
}
$_uri = router($router);

define('MODULE', $_uri['module']);
define('ACTION', $_uri['action']);

require(CORE_PATH .'/db.php');
$db = new db();
if(isset($config['DB_HOST']) && isset($config['DB_USER']) && isset($config['DB_PSWD']))
{
	$db_host = $config['DB_HOST'].(isset($config['DB_PORT']) ? ':'.$config['DB_PORT'] : '');
	$db->connect($db_host, $config['DB_USER'], $config['DB_PSWD']);
	if(isset($config['DB_NAME']))
	{
		$db->select_db($config['DB_NAME']);
	}
	if(isset($config['DB_PREFIX']))
	{
		$db->prefix($config['DB_PREFIX']);
	}
}

$script = APP_PATH .'action/'.MODULE.'Action.php';
if(!file_exists($script))
{
	error_404('Can not find the action '.MODULE.'Action.php');
}
else
{
	require($script);
	$class = MODULE.'Action';
	$method = ACTION;
	if($method != $class)
	{
		$run = new $class();
		if(method_exists($run, $method)){
			$run->$method();
		}else{
			error_404("Call to undefined method($method) in the class($class)");
		}
	}
	else
	{
		error_404("The action name($method) must be not the same as the module name($class)");
	}
}

if($db->conn)
{
	$db->close();
}
?>