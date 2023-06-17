<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (is_array($arResult['VALUE']) && !empty($arResult['VALUE']))
{
	if(!CModule::IncludeModule("highloadblock"))
		return;

	global $USER_FIELD_MANAGER;

	$userfields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$arParams['arUserField']['SETTINGS']['HLBLOCK_ID'], 0, LANGUAGE_ID);

	foreach ($userfields as $_userfield)
	{
		if ($_userfield['ID'] == $arParams['arUserField']['SETTINGS']['HLFIELD_ID'])
		{
			$userfield = $_userfield;
			break;
		}
	}

	if ($userfield)
	{
		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($arParams['arUserField']['SETTINGS']['HLBLOCK_ID'])->fetch();

		$hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
		$rows = $hlDataClass::getList(array(
			'select' => array('ID', $userfield['FIELD_NAME']),
			'filter' => array('=ID' => $arResult['VALUE'])
		))->fetchAll();

		$newValue = array();

		foreach ($rows as &$row)
		{
			if ($userfield['FIELD_NAME'] == 'ID')
			{
				$row['VALUE'] = $row['ID'];
			}
			else
			{
				$newValue[] = $USER_FIELD_MANAGER->getListView($userfield, $row[$userfield['FIELD_NAME']]);
			}
		}

		$arResult['VALUE'] = $newValue;
	}
}
