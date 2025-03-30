<?php
define('STOP_STATISTICS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';
if (CModule::IncludeModule('search'))
{
	if (!empty($_REQUEST['search']))
	{
		$arResult = [];
		$order = CUserOptions::GetOption('search_tags', 'order', 'CNT');
		if ($_REQUEST['order_by'] == 'NAME')
		{
			$arOrder = ['NAME' => 'ASC'];
			if ($order != 'NAME')
			{
				CUserOptions::SetOption('search_tags', 'order', 'NAME');
			}
		}
		else
		{
			$arOrder = ['CNT' => 'DESC', 'NAME' => 'ASC'];
			if ($order != 'CNT')
			{
				CUserOptions::SetOption('search_tags', 'order', 'CNT');
			}
		}
		$db_res = CSearchTags::GetList(
			['NAME', 'CNT'],
			['TAG' => $_REQUEST['search'], 'SITE_ID' => $_REQUEST['site_id']],
			$arOrder,
		10);
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
	}
}
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php';
