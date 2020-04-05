<?php
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
	if (!preg_match('/^([A-z0-9\-\=])+$/', $field))
	{
		exit;
	}
}
$channelId = $fields['HTTP_X_GOOG_CHANNEL_ID'];
$resourceId = $fields['HTTP_X_GOOG_RESOURCE_ID'];

\Bitrix\Main\Loader::includeModule('calendar');

\Bitrix\Calendar\Sync\GoogleApiPush::receivePushSignal($channelId, $resourceId);
