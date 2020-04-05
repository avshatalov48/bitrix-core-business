<?php

//define('NO_KEEP_STATISTIC', 'Y');
//define('NO_AGENT_STATISTIC','Y');
//define('NO_AGENT_CHECK', true);
//define('DisableEventsCheck', true);

define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);
//define('BX_SECURITY_SESSION_READONLY', true);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

$error   = false;
$backurl = false;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

CModule::includeModule('mail');

if ($token = $request->get('token'))
{
	if ($userRelation = Bitrix\Mail\UserRelationsTable::getByPrimary($token)->fetch())
	{
		$forceLogin = false;

		if ($USER->isAuthorized() && $USER->getId() != $userRelation['USER_ID'])
		{
			$forceLogin = Bitrix\Main\UserTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=ID' => $USER->getId(),
					'=EXTERNAL_AUTH_ID' => 'email'
				)
			))->fetch() ? true : false;
		}

		if (!$USER->isAuthorized() || $forceLogin)
			Bitrix\Mail\User::login();

		if ($USER->isAuthorized())
		{
			$link    = $userRelation['ENTITY_LINK'];
			$backurl = $userRelation['BACKURL'];
		}
		else
		{
			$error = 403;
		}
	}
	else
	{
		$error = 404;
	}
}
else
{
	$error = 400;
}

$APPLICATION->restartBuffer();

header('Content-Type: application/x-javascript; charset=UTF-8');
echo json_encode(array(
	'result'  => $error === false ? $link : 'error',
	'error'   => CharsetConverter::convertCharset($error, SITE_CHARSET, 'UTF-8'),
	'backurl' => $backurl
));

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
