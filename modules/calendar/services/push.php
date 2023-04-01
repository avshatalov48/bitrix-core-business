<?php
// exit;
use Bitrix\Calendar\Sync\Managers\PushManager;
use Bitrix\Main\Loader;

define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$allowedFields = array('HTTP_X_GOOG_CHANNEL_ID' => true, 'HTTP_X_GOOG_RESOURCE_ID' => true);
$fields = array_intersect_key($_SERVER, $allowedFields);
if (empty($fields))
{
	exit;
}
foreach ($fields as $field)
{
	if (!preg_match('/^([A-z\d\-=])+$/', $field))
	{
		exit;
	}
}

$channelId = $fields['HTTP_X_GOOG_CHANNEL_ID'];
$resourceId = $fields['HTTP_X_GOOG_RESOURCE_ID'];

Loader::includeModule('calendar');
Loader::includeModule('dav');

try
{
	(new PushManager())->handlePush($channelId, $resourceId);
}
catch (\Exception $e)
{}

\Bitrix\Main\Application::getInstance()->end();
