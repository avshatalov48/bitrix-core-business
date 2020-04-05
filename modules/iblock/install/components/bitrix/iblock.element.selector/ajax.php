<?php
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

//todo move this ajax handler to component class

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

		$minPermission = 'R';
		if (isset($_REQUEST['admin']) && is_string($_REQUEST['admin']))
		{
			if ($_REQUEST['admin'] == 'Y')
				$minPermission = 'S';
		}

		$searchString = trim($_REQUEST['string']);
		$filter = array(
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => $minPermission
		);
		$iblockId = 0;
		if (isset($_REQUEST['iblockId']) && is_string($_REQUEST['iblockId']))
			$iblockId = (int)$_REQUEST['iblockId'];
		if ($iblockId > 0)
			$filter['IBLOCK_ID'] = $iblockId;
		if(is_numeric($searchString))
		{
			$filter['=ID'] = intval($searchString);
		}
		else
		{
			$filter['?NAME'] = $searchString;
		}

		$queryElementObject = CIBlockElement::GetList(
			['NAME' => 'ASC'], $filter, false, false, ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID']);
		while ($element = $queryElementObject->fetch())
		{
			$url = '';
			if (!empty($_REQUEST['template_url']))
			{
				$socnetGroupId = null;
				$queryIblockObject = \CIBlock::getList([], ['ID' => $element['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N']);
				while ($iblock = $queryIblockObject->fetch())
				{
					$socnetGroupId = $iblock['SOCNET_GROUP_ID'];
				}

				$sectionId = $element['IBLOCK_SECTION_ID'] ?: 0;
				$socnetGroupId = $socnetGroupId ?: 0;

				$url = str_replace(
					['#list_id#', '#section_id#', '#element_id#', '#group_id#'],
					[$element['IBLOCK_ID'], $sectionId, $element['ID'], $socnetGroupId],
					$_REQUEST['template_url']
				);
			}

			$elements[] = [
				'ID' => $element['ID'],
				'NAME' => '['.$element['ID'].'] '.$element['NAME'],
				'URL' => $url,
			];
		}

		break;
	}
}

header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode(array_values(array_filter($elements)));
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();