<?php
define('IM_AJAX_INIT', true);
define('PUBLIC_AJAX_MODE', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('STOP_STATISTICS', true);

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

if(!\Bitrix\Main\Loader::includeModule('disk'))
{
	die;
}
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if ($request->get('FILE_ID') && $request->get('SIGN'))
{
	$diskFileId = (int)$request->get('FILE_ID');
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$sign = htmlspecialcharsbx($request->get('SIGN'));

	try
	{
		$sign = (int)$signer->unsign($sign);
	}
	catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
	{
		CHTTP::SetStatus('404 Not Found');
	}
}

if ($diskFileId === $sign)
{
	$file = \Bitrix\Disk\File::getById($diskFileId);
	if ($file !== null)
	{
		$fileId = $file->getFileId();
		if ($fileId > 0)
		{
			CFile::ViewByUser($fileId);
		}
	}
}
else
{
	CHTTP::SetStatus('404 Not Found');
}

CMain::FinalActions();
die();