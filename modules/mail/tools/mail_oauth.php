<?php

use Bitrix\Mail;
use Bitrix\Mail\Helper\OAuth;
use Bitrix\Main\Loader;

define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (Loader::includeModule('mail'))
{

	parse_str($_REQUEST['state'], $state);

	if ($helper = Mail\Helper\OAuth::getInstance($state['service']))
	{
		if (isset($_SESSION["MOBILE_OAUTH"]) && $_SESSION["MOBILE_OAUTH"])
		{
			$helper->handleResponse($state, OAuth::MOBILE_TYPE);
		}
		else
		{
			$helper->handleResponse($state);
		}
	}
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
