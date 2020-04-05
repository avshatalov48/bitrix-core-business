<?php
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!check_bitrix_sessid() || !CModule::includeModule('iblock'))
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
	die();
}

global $APPLICATION;

$elements = array();

switch($_REQUEST['mode'])
{
	case 'search':
	{
		CUtil::JSPostUnescape();
		$APPLICATION->RestartBuffer();

		$searchString = trim($_REQUEST['string']);
		$filter = array(
			'IBLOCK_ID' => intval($_REQUEST['iblockId']),
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R'
		);
		if(is_numeric($searchString))
		{
			$filter['=ID'] = intval($searchString);
		}
		else
		{
			$filter['?NAME'] = $searchString;
		}

		$queryObject = CIBlockElement::getList(array('NAME' => 'ASC'), $filter, false, false, array('ID', 'NAME'));
		while($element = $queryObject->fetch())
		{
			$elements[] = array(
				'ID' => $element['ID'],
				'NAME' => $element['NAME']
			);
		}

		break;
	}
}

header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode(array_values(array_filter($elements)));
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();