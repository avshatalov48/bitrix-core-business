<?php
define('STOP_STATISTICS', 'Y');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/prolog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/include.php';
/** @var CMain $APPLICATION */

/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($APPLICATION->GetGroupRight('workflow') >= 'R')
{
	session_write_close();
	$did = $request['did'];
	$fname = $request['fname'];
	$wf_path = $request['wf_path'];
	$site = $request['site'];
	$src = CWorkflow::GetFileContent($did, $fname, $wf_path, $site);
	$ext = mb_strtolower(GetFileExtension($fname));
	$arrExt = explode(',', mb_strtolower(CFile::GetImageExtensions()));
	if (in_array($ext, $arrExt))
	{
		if ($ext == 'jpg')
		{
			$ext = 'jpeg';
		}
		header('Content-type: image/' . $ext);
		header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		header('Pragma: public');
		echo $src;
		die;
	}
	echo TxtToHTML($src);
}
die;
