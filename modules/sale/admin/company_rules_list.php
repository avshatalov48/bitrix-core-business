<?
namespace Bitrix\Sale\Company\AdminPage\CompanyRules
{
	use Bitrix\Main\Application;
	use Bitrix\Main\Loader;
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Internals\Input;
	use Bitrix\Sale\Services\Base;
	use \Bitrix\Sale\Services\Company;

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/services/company/inputs.php");

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
	$tableId = 'table_company_rules';
	$oSort = new \CAdminSorting($tableId);
	$lAdmin = new \CAdminSubList($tableId, $oSort, '/bitrix/admin/sale_company_rules_list.php?ID='.$id);

	$ruleClassNames = Company\Restrictions\Manager::getClassesList();
	$data = Company\Restrictions\Manager::getRestrictionsList($id);

	$dbRes = new \CDBResult;
	$dbRes->InitFromArray($data);
	$dbRecords = new \CAdminResult($dbRes, $tableId);
	$dbRecords->NavStart();
	$lAdmin->NavText($dbRecords->GetNavPrint(Loc::getMessage('SALE_COMPANY_RULES_LIST')));

	$header = array(
		array('id'=>'ID', 'content'=>Loc::getMessage('SALE_COMPANY_RULE_COL_ID'), "sort"=>"", 'default'=>true),
		array('id'=>'SORT', 'content'=>Loc::getMessage('SALE_COMPANY_RULE_COL_SORT'), "sort"=>"", 'default'=>true),
		array('id'=>'CLASS_NAME', 'content'=>Loc::getMessage('SALE_COMPANY_RULE_COL_CLASS_NAME'), "sort"=>"", 'default'=>true),
		array('id'=>'PARAMS', 'content'=>Loc::getMessage('SALE_COMPANY_RULE_COL_PARAMS'), "sort"=>"", 'default'=>true),
	);

	$lAdmin->AddHeaders($header);

	$ruleClassNamesUsed = array();

	while ($record = $dbRecords->Fetch())
	{
		if ($record['CLASS_NAME'])
		{
			$ruleClassNamesUsed[] = $record['CLASS_NAME'];

			if(is_callable($record['CLASS_NAME'].'::getClassTitle'))
				$className = $record['CLASS_NAME']::getClassTitle();
			else
				$className = $record['CLASS_NAME'];
		}
		else
		{
			$className = "";
		}

		if (!$record["PARAMS"])
			$record["PARAMS"] = array();

		$paramsStructure = $record['CLASS_NAME']::getParamsStructure($id);
		$record["PARAMS"] = $record['CLASS_NAME']::prepareParamsValues($record["PARAMS"], $id);

		$editAction = "BX.Sale.Company.getRuleParamsHtml({".
			"class: '".\CUtil::JSEscape($record["CLASS_NAME"]).
			"',companyId: ".$id.
			",title: '".$className.
			"',ruleId: ".$record["ID"].
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
				$paramsField .= (isset($params["LABEL"]) && $params["LABEL"] <> '' ? $params["LABEL"].': ' : '').$html.'<br>';
		}

		$row->AddField('PARAMS', $paramsField);

		if ($saleModulePermissions >= "W")
		{
			$arActions = array();
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => Loc::getMessage("SALE_COMPANY_RULE_EDIT_DESC"),
				"ACTION" => $editAction,
				"DEFAULT" => true
			);
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage("SALE_COMPANY_RULE_DELETE"),
				"ACTION" => "javascript:if(confirm('".Loc::getMessage("SALE_COMPANY_RULES_DEL_MESSAGE")."')) BX.Sale.Company.deleteRule(".$record["ID"].",".$id.");"
			);

			$row->AddActions($arActions);
		}
	}

	if ($saleModulePermissions == "W")
	{
		$rulesMenu = array();

		/** @var Base\Restriction $class */
		foreach ($ruleClassNames as $class)
		{
			if (!$class)
				continue;

			if (in_array($class, $ruleClassNamesUsed))
				continue;

			$rulesMenu[] = array(
				"TEXT" => $class::getClassTitle(),
				"ACTION" => "BX.Sale.Company.getRuleParamsHtml({".
					"class: '".\CUtil::JSEscape($class).
					"',companyId: ".$id.
					",title: '".$class::getClassTitle().
					"',lang: '".$context->getLanguage()."'".
				"});"
			);
		}

		$aContext = array();

		if (!empty($rulesMenu))
		{
			$aContext[] = array(
				"TEXT" => Loc::getMessage("SALE_COMPANY_RULES_BUT_ADD_NEW"),
				"TITLE" => Loc::getMessage("SALE_COMPANY_RULES_BUT_ADD_NEW"),
				"ICON" => "btn_new",
				"MENU" => $rulesMenu
			);
		}

		$lAdmin->AddAdminContextMenu($aContext, false);
	}

	if ($_REQUEST['table_id'] == $tableId)
		$lAdmin->CheckListMode();

	$lAdmin->DisplayList();
}