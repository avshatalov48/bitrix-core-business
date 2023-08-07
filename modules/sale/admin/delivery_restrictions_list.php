<?php
namespace Bitrix\Sale\Delivery\AdminPage\DeliveryRestrictions
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();

	global $APPLICATION;

	/** @global \CAdminPage $adminPage */
	global $adminPage;
	/** @global \CAdminSidePanelHelper $adminSidePanelHelper */
	global $adminSidePanelHelper;

	$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
	if ($saleModulePermissions < "W")
		$APPLICATION->AuthForm(Loc::getMessage("SALE_ESDL_ACCESS_DENIED"));

	/**
	 * @var \CDatabase $DB
	 * @var \CMain  $APPLICATION
	 */

	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Delivery\Restrictions;
	use Bitrix\Sale\Internals\Input;

	Loc::loadMessages(__FILE__);

	$urlPublicMode = (($adminPage->publicMode || $adminSidePanelHelper->isPublicSidePanel()) ? 'Y' : 'N');

	$ID = intval($_GET['ID']);
	$tableId = 'table_delivery_restrictions';
	$oSort = new \CAdminSorting($tableId);
	$lAdmin = new \CAdminList($tableId, $oSort);
	$restrictionClassNames = Restrictions\Manager::getClassesList();

	$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'filter' => array(
			'=SERVICE_ID' => $ID,
			'=SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_SHIPMENT
		),
		'select' => array('ID', 'CLASS_NAME', 'SORT', 'PARAMS'),
		'order' => array('SORT' => 'ASC', 'ID' => 'DESC')
	));

	$data = $res->fetchAll();
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
		if(empty($record['CLASS_NAME']) || !class_exists($record['CLASS_NAME']))
			continue;

		if(!is_subclass_of($record['CLASS_NAME'], 'Bitrix\Sale\Services\Base\Restriction'))
			continue;

		if($record['CLASS_NAME'] <> '')
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

		$editAction = "BX.Sale.Delivery.getRestrictionParamsHtml({".
			"class: '".\CUtil::JSEscape($record["CLASS_NAME"]).
			"',deliveryId: ".$ID.
			",title: '".$className.
			"',restrictionId: ".$record["ID"].
			",params: ".htmlspecialcharsbx(\CUtil::PhpToJSObject($record["PARAMS"])).
			",sort: ".$record["SORT"].
			",lang: '".LANGUAGE_ID."'".
		"});";

		$row =& $lAdmin->AddRow($record['ID'], $record);
		$row->AddField('ID', '<a href="javascript:void(0);" onclick="'.$editAction.'">'.$record['ID'].'</a>');
		$row->AddField('SORT', $record['SORT']);
		$row->AddField('CLASS_NAME', $className);

		$paramsStructure = $record['CLASS_NAME']::getParamsStructure($ID);
		$record["PARAMS"] = $record['CLASS_NAME']::prepareParamsValues($record["PARAMS"], $ID);

		$paramsField = "";

		foreach($paramsStructure as $name => $params)
		{
			$paramsField .= (isset($params["LABEL"]) && $params["LABEL"] <> '' ? $params["LABEL"].": " : "").
				Input\Manager::getViewHtml($params, ($record["PARAMS"][$name] ?? null)).
				"<br>";
		}

		$row->AddField('PARAMS', $paramsField);

		if ($saleModulePermissions >= "W")
		{
			$arActions = Array();
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
				"ACTION" => "javascript:if(confirm('".Loc::getMessage("SALE_RDL_CONFIRM_DEL_MESSAGE")."')) BX.Sale.Delivery.deleteRestriction(".$record["ID"].",".$ID.", '" . $urlPublicMode . "');"
			);

			$row->AddActions($arActions);
		}
	}

	if ($saleModulePermissions == "W")
	{
		$restrictionsMenu = array();

		foreach($restrictionClassNames as $class)
		{
			if($class == '')
				continue;

			if(in_array($class, $restrictionClassNamesUsed))
				continue;

			$classTitle = is_callable($class.'::getClassTitle') ? $class::getClassTitle() : $class;

			$restrictionsMenu[] = array(
				"TEXT" => $classTitle,
				"ACTION" => "BX.Sale.Delivery.getRestrictionParamsHtml({".
					"class: '".\CUtil::JSEscape($class).
					"',deliveryId: ".$ID.
					",publicMode: '" . $urlPublicMode . "'".
					",title: '".$classTitle.
					"',lang: '".LANGUAGE_ID."'".
				"});"
			);
		}

		$aContext = array();

		if(!empty($restrictionsMenu))
		{
			sortByColumn($restrictionsMenu, array("TEXT" => SORT_ASC));

			$aContext[] = array(
				"TEXT" => Loc::getMessage("SALE_RDL_BUT_ADD_NEW"),
				"TITLE" => Loc::getMessage("SALE_RDL_BUT_ADD_NEW"),
				"ICON" => "btn_new",
				"MENU" => $restrictionsMenu
			);
		}

		$lAdmin->AddAdminContextMenu($aContext, false);
	}

	if (isset($_REQUEST['table_id']) && $_REQUEST['table_id'] === $tableId)
	{
		$lAdmin->CheckListMode();
	}

	$lAdmin->DisplayList();
}
