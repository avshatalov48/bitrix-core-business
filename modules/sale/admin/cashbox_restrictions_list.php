<?
namespace Bitrix\Sale\Cashbox\AdminPage\Restrictions
{
	use Bitrix\Main\Application;
	use Bitrix\Main\Loader;
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Internals\Input;
	use Bitrix\Sale\Cashbox\Restrictions\Manager;

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();

	global $APPLICATION;
	Loader::includeModule('sale');
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

	$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
	if ($saleModulePermissions < "W")
		$APPLICATION->AuthForm(Loc::getMessage("SALE_ESDL_ACCESS_DENIED"));

	Loc::loadMessages(__FILE__);

	$instance = Application::getInstance();
	$context = $instance->getContext();

	$id = (int)$_GET['ID'];
	$tableId = 'table_cashbox_restrictions';
	$oSort = new \CAdminSorting($tableId);
	$lAdmin = new \CAdminSubList($tableId, $oSort, '/bitrix/admin/sale_cashbox_restriction_list.php?ID='.$id);

	$restrictionClassNames = Manager::getClassesList();

	$data = Manager::getRestrictionsList($id);

	$dbRes = new \CDBResult;
	$dbRes->InitFromArray($data);
	$dbRecords = new \CAdminResult($dbRes, $tableId);
	$dbRecords->NavStart();
	$lAdmin->NavText($dbRecords->GetNavPrint(Loc::getMessage('SALE_RDL_LIST')));

	$header = array(
		array('id'=>'ID', 'content'=>Loc::getMessage('SALE_RDL_COL_ID'), "sort"=>"", 'default'=>true),
		array('id'=>'SORT', 'content'=>Loc::getMessage('SALE_RDL_COL_SORT'), "sort"=>"", 'default'=>true),
		array('id'=>'CLASS_NAME', 'content'=>Loc::getMessage('SALE_RDL_COL_CLASS_NAME'), "sort"=>"", 'default'=>true),
		array('id'=>'PARAMS', 'content'=>Loc::getMessage('SALE_RDL_COL_PARAMS'), "sort"=>"", 'default'=>true),
	);

	$lAdmin->AddHeaders($header);

	$restrictionClassNamesUsed = array();

	while ($record = $dbRecords->Fetch())
	{
		if(strlen($record['CLASS_NAME']) > 0)
		{
			$restrictionClassNamesUsed[] = $record['CLASS_NAME'];

			if(is_callable($record['CLASS_NAME'].'::getClassTitle'))
				$className = $record['CLASS_NAME']::getClassTitle();
			else
				$className = $record['CLASS_NAME'];
		}
		else
			$className = "";

		if(!$record["PARAMS"])
			$record["PARAMS"] = array();

		$paramsStructure = $record['CLASS_NAME']::getParamsStructure($id);
		$record["PARAMS"] = $record['CLASS_NAME']::prepareParamsValues($record["PARAMS"], $id);

		$editAction = "BX.Sale.Cashbox.getRestrictionParamsHtml({".
			"class: '".\CUtil::JSEscape($record["CLASS_NAME"]).
			"',cashboxId: ".$id.
			",title: '".$className.
			"',restrictionId: ".$record["ID"].
			",params: ".\CUtil::PhpToJSObject($record["PARAMS"]).
			",sort: ".$record["SORT"].
			",lang: '".$context->getLanguage()."'".
		"});";

		$row =& $lAdmin->AddRow($record['ID'], $record);
		$row->AddField('ID', '<a href="javascript:void(0);" onclick="'.$editAction.'">'.$record['ID'].'</a>');
		$row->AddField('SORT', $record['SORT']);
		$row->AddField('CLASS_NAME', $className);

		$paramsField = '';

		foreach($paramsStructure as $name => $params)
		{
			$html = Input\Manager::getViewHtml($params, (isset($record["PARAMS"][$name]) ? $record["PARAMS"][$name] : null));
			if ($html)
				$paramsField .= (isset($params["LABEL"]) && strlen($params["LABEL"]) > 0 ? $params["LABEL"].': ' : '').$html.'<br>';
		}

		$row->AddField('PARAMS', $paramsField);

		if ($saleModulePermissions >= "W")
		{
			$arActions = array();
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => Loc::getMessage("SALE_RDL_EDIT_DESCR"),
				"ACTION" => $editAction,
				"DEFAULT" => true
			);
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage("SALE_RDL_DELETE"),
				"ACTION" => "javascript:if(confirm('".Loc::getMessage("SALE_RDL_CONFIRM_DEL_MESSAGE")."')) BX.Sale.Cashbox.deleteRestriction(".$record["ID"].",".$id.");"
			);

			$row->AddActions($arActions);
		}
	}

	if ($saleModulePermissions == "W")
	{
		$restrictionsMenu = array();

		foreach($restrictionClassNames as $class)
		{
			if(strlen($class) <= 0)
				continue;

			if(in_array($class, $restrictionClassNamesUsed))
				continue;

			if (!$class::getParamsStructure($id))
				continue;

			$restrictionsMenu[] = array(
				"TEXT" => $class::getClassTitle(),
				"ACTION" => "BX.Sale.Cashbox.getRestrictionParamsHtml({".
					"class: '".\CUtil::JSEscape($class).
					"',cashboxId: ".$id.
					",title: '".$class::getClassTitle().
					"',lang: '".$context->getLanguage()."'".
				"});"
			);
		}

		$aContext = array();

		if(!empty($restrictionsMenu))
		{
			$aContext[] = array(
				"TEXT" => Loc::getMessage("SALE_RDL_BUT_ADD_NEW"),
				"TITLE" => Loc::getMessage("SALE_RDL_BUT_ADD_NEW"),
				"ICON" => "btn_new",
				"MENU" => $restrictionsMenu
			);
		}

		$lAdmin->AddAdminContextMenu($aContext, false);
	}

	if($_REQUEST['table_id'] == $tableId)
		$lAdmin->CheckListMode();

	$lAdmin->DisplayList();
}