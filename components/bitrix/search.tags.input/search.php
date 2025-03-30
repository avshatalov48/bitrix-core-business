<?php define('STOP_STATISTICS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
// **************************************************************************************
if (CModule::IncludeModule('search')):
{
	$arParams = [];
	$params = explode(',', $_POST['params']);
	foreach ($params as $param)
	{
		list($key, $val) = explode(':', $param);
		$arParams[$key] = $val;
	}
	if (intval($arParams['pe']) <= 0)
	{
		$arParams['pe'] = 10;
	}
	$arResult = [];
// **************************************************************************************
	if (!empty($_POST['search']))
	{
		if (mb_strtolower($arParams['sort']) == 'name')
		{
			$arOrder = ['NAME' => 'ASC', 'CNT' => 'DESC'];
		}
		else
		{
			$arOrder = ['CNT' => 'DESC', 'NAME' => 'ASC'];
		}

		$arFilter = ['TAG' => $_POST['search']];
		if (empty($arParams['site_id'])):
			$arFilter['SITE_ID'] = SITE_ID;
		else:
			$arFilter['SITE_ID'] = $arParams['site_id'];
		endif;
		if (!empty($arParams['mid']))
		{
			$arFilter['MODULE_ID'] = $arParams['mid'];
		}
		if (!empty($arParams['pm1']))
		{
			$arFilter['PARAM1'] = $arParams['pm1'];
		}
		if (!empty($arParams['pm2']))
		{
			$arFilter['PARAM2'] = $arParams['pm2'];
		}
		if (!empty($arParams['sng']))
		{
			$arFilter['PARAMS'] = ['socnet_group' => $arParams['sng']];
		}

		$db_res = CSearchTags::GetList(
			['NAME', 'CNT'],
			$arFilter,
			$arOrder,
			$arParams['pe']);
		if ($db_res)
		{
			while ($res = $db_res->Fetch())
			{
				$arResult[] = [
					'NAME' => $res['NAME'],
					'CNT' => $res['CNT'],
				];
			}
		}
		?><?=CUtil::PhpToJSObject($arResult)?><?php
		CMain::FinalActions();
		die();
	}
}
endif;
