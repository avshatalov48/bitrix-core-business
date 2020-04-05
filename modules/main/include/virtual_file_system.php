<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */
/*
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteCond %{REQUEST_FILENAME} [\xC2-\xDF][\x80-\xBF] [OR]
RewriteCond %{REQUEST_FILENAME} \xE0[\xA0-\xBF][\x80-\xBF] [OR]
RewriteCond %{REQUEST_FILENAME} [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} [OR]
RewriteCond %{REQUEST_FILENAME} \xED[\x80-\x9F][\x80-\xBF] [OR]
RewriteCond %{REQUEST_FILENAME} \xF0[\x90-\xBF][\x80-\xBF]{2} [OR]
RewriteCond %{REQUEST_FILENAME} [\xF1-\xF3][\x80-\xBF]{3} [OR]
RewriteCond %{REQUEST_FILENAME} \xF4[\x80-\x8F][\x80-\xBF]{2}
RewriteRule ^(.*)$ /bitrix/virtual_file_system.php [L]
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/loader.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

$io = CBXVirtualIo::GetInstance();

$requestUri = $_SERVER["REQUEST_URI"];
if (($pos = strpos($requestUri, "?")) !== false)
	$requestUri = substr($requestUri, 0, $pos);

$requestUri = rawurldecode($requestUri);
if (!preg_match("#([\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})#", $requestUri))
{
	// Not utf-8 filename. Should be handled in the regular way.
	CHTTP::SetStatus("403 Forbidden");
	die("Filename is out of range.");
}

if (!defined("BX_UTF"))
{
	$requestUri = \Bitrix\Main\Text\Encoding::convertEncoding($requestUri, "utf-8", (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "windows-1251"));
}

$requestUri = preg_replace("/(\\.)(\\.[\\\\\\/])/is", "\\1 \\2", $requestUri);
$requestUri = preg_replace("/[\\.\\/\\\\\\x20\\x22\\x3c\\x3e\\x5c]{30,}/", " X ", $requestUri);

$requestUriAbsolute = $io->RelativeToAbsolutePath($requestUri);

$documentRoot = rtrim($_SERVER["DOCUMENT_ROOT"], "/");
$documentRootLength = strlen($documentRoot) + 1;
if ($documentRootLength >= strlen($requestUriAbsolute)
	|| substr($requestUriAbsolute, 0, $documentRootLength) !== $documentRoot."/")
{
	CHTTP::SetStatus("403 Forbidden");
	die("Path is out of range.");
}

$urlTmp = substr($requestUriAbsolute, $documentRootLength);
$urlTmp = str_replace(".", "", $urlTmp);
if (substr($urlTmp, 0, 7) == "bitrix/")
{
	CHTTP::SetStatus("403 Forbidden");
	die("Path is out of range.");
}

if (!$io->ValidatePathString($requestUriAbsolute))
{
	CHTTP::SetStatus("403 Forbidden");
	die("Path is out of range.");
}

if (!$io->FileExists($requestUriAbsolute))
{
	if ($io->DirectoryExists($requestUriAbsolute))
	{
		$requestUriAbsolute = $io->CombinePath($requestUriAbsolute, "index.php");
		if (!$io->FileExists($requestUriAbsolute))
		{
			CHTTP::SetStatus("403 Forbidden");
			die("Index file is not found.");
		}
	}
	else
	{
		CHTTP::SetStatus("404 Not Found");
		die("File is not found.");
	}
}

if (strtolower(substr($requestUriAbsolute, -4)) == ".php")
{
	$relativePath = $io->CombinePath("/", substr($requestUriAbsolute, strlen($_SERVER["DOCUMENT_ROOT"])));
	$_SERVER["REAL_FILE_PATH"] = $relativePath;

	include($io->GetPhysicalName($requestUriAbsolute));
}
else
{
	$f = $io->GetFile($requestUriAbsolute);
	$fsize = $f->GetFileSize();
	$fModTime = $f->GetModificationTime();

	$arTypes = array("jpeg"=>"image/jpeg", "jpe"=>"image/jpeg", "jpg"=>"image/jpeg", "png"=>"image/png", "gif"=>"image/gif", "bmp"=>"image/bmp");

	$ext = strtolower(substr($requestUriAbsolute, bxstrrpos($requestUriAbsolute, ".") + 1));
	if(isset($arTypes[$ext]))
	{
		header("Content-Type: ".$arTypes[$ext]);
	}
	else
	{
		$name = $io->ExtractNameFromPath($requestUri);
		header("Content-Type: application/force-download; name=\"".$name."\"");
		header("Content-Disposition: attachment; filename=\"".$name."\"");
	}
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Last-Modified: ".gmdate('D, d M Y H:i:s \G\M\T', $fModTime));
	$f->ReadFile();
}
