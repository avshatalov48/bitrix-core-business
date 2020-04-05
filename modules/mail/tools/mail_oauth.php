<?php

use Bitrix\Mail;

define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (\Bitrix\Main\Loader::includeModule('mail'))
{
	parse_str($_REQUEST['state'], $state);

	if ($helper = Mail\Helper\OAuth::getInstance($state['service']))
	{
		$helper->handleResponse($state);
	}
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
