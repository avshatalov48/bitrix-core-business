<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$requiredModules = array('highloadblock');
foreach ($requiredModules as $requiredModule)
{
	if (!\Bitrix\Main\Loader::includeModule($requiredModule))
	{
		ShowError(GetMessage('F_NO_MODULE'));
		return 0;
	}
}

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

global $USER_FIELD_MANAGER;

$arResult['ERROR']  = '';

// hlblock info
$hlblock_id = $arParams['BLOCK_ID'];

if (empty($hlblock_id))
{
	$arResult['ERROR'] = GetMessage('HLBLOCK_VIEW_NO_ID');
}
else
{
	$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();
	if (empty($hlblock))
	{
		$arResult['ERROR'] = GetMessage('HLBLOCK_VIEW_404');
	}
}

// check rights
if (isset($arParams['CHECK_PERMISSIONS']) && $arParams['CHECK_PERMISSIONS'] == 'Y' && !$USER->isAdmin())
{
	$operations = HL\HighloadBlockRightsTable::getOperationsName($hlblock_id);
	if (empty($operations))
	{
		$arResult['ERROR'] = GetMessage('HLBLOCK_VIEW_404');
	}
}

if ($arResult['ERROR'] == '')
{
	$entity = HL\HighloadBlockTable::compileEntity($hlblock);

	if (!isset($arParams['ROW_KEY']) || trim($arParams['ROW_KEY']) == '')
	{
		$arParams['ROW_KEY'] = 'ID';
	}

	// row data
	$main_query = new Entity\Query($entity);
	$main_query->setSelect(array('*'));
	$main_query->setFilter(array('='.trim($arParams['ROW_KEY']) => $arParams['ROW_ID']));

	$result = $main_query->exec();
	$result = new CDBResult($result);
	$row = $result->Fetch();

	$fields = $USER_FIELD_MANAGER->getUserFieldsWithReadyData(
					'HLBLOCK_'.$hlblock['ID'],
					$row,
					LANGUAGE_ID
				);

	if (empty($row))
	{
		$arResult['ERROR'] = GetMessage('HLBLOCK_VIEW_NO_ROW');
	}

	$arResult['fields'] = $fields;
	$arResult['row'] = $row;

}

$this->IncludeComponentTemplate();