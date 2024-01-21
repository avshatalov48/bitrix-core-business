<?php

use Bitrix\Mail\MailServicesTable;
use Bitrix\Main\Loader;

define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (Loader::includeModule('mail'))
{
	if (isset($_REQUEST['serviceName']))
	{
		$_SESSION["MOBILE_OAUTH"] = true;

		$oauthHelper = MailServicesTable::getOAuthHelper([
			'NAME' => $_REQUEST['serviceName']
		]);
		if ($oauthHelper && $url = $oauthHelper->getUrl())
		{
			LocalRedirect($url, true);
		}
	}
	else
	{
		header('Location: bitrix24://');
	}
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
