<?
namespace Bitrix\Sale\Delivery\AdminPage\DeliveryExtraServiceEdit
{
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();

	/**
	 * @var \Bitrix\Sale\Delivery\Services\Base $service
	 */

	global $tabControl, $APPLICATION, $service;

	$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

	if ($saleModulePermissions < "W")
		$APPLICATION->AuthForm(Loc::getMessage("SALE_ESDL_ACCESS_DENIED"));

	/**
	 * @var CDatabase $DB
	 * @var CMain  $APPLICATION
	 */

	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Delivery\ExtraServices;
	use Bitrix\Sale\Delivery\Services;

	Loc::loadMessages(__FILE__);

	$ID = intval($_GET['ID']);
	global $srvStrError;

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete_extra_service" && isset($_REQUEST["ES_ID"]) && $saleModulePermissions == "W" && check_bitrix_sessid())
	{
		if(intval($_REQUEST["ES_ID"]) > 0)
		{
			$res = ExtraServices\Table::delete(intval($_REQUEST["ES_ID"]));

			if(!$res->isSuccess())
				$srvStrError .= Loc::getMessage("SALE_ESDE_ERROR_DELETE").' '.implode("<br>\n",$res->getErrorMessages());
		}
		else
		{
			$srvStrError .= Loc::getMessage("SALE_ESDE_ERROR_ID");
		}
	}

	$tableId = 'table_delivery_extra_service';
	$oSort = new \CAdminSorting($tableId);
	$lAdmin = new \CAdminList($tableId, $oSort);
	$esClasses = ExtraServices\Manager::getClassesList();

	$res = \Bitrix\Sale\Delivery\ExtraServices\Table::getList(array(
		'filter' => array(
			'=DELIVERY_ID' => $ID,
			'=CLASS_NAME' => $esClasses
		),
		'select' => array('ID', 'CODE', 'NAME', 'DESCRIPTION', 'CLASS_NAME', 'RIGHTS', 'ACTIVE', 'SORT'),
		'order' => array('SORT' => 'ASC', 'ID' => 'DESC')
	));

	$data = $res->fetchAll();
	$dbRes = new \CDBResult;
	$dbRes->InitFromArray($data);
	$dbRecords = new \CAdminResult($dbRes, $tableId);
	$dbRecords->NavStart();
	$lAdmin->NavText($dbRecords->GetNavPrint(Loc::getMessage('SALE_ESDL_LIST')));

	$header = array(
		array('id'=>'ID', 'content'=>Loc::getMessage('SALE_ESDL_COL_ID'), "sort"=>"", 'default'=>true),
		array('id'=>'CODE', 'content'=>Loc::getMessage('SALE_ESDL_COL_CODE'), "sort"=>"", 'default'=>false),
		array('id'=>'NAME', 'content'=>Loc::getMessage('SALE_ESDL_COL_NAME'), "sort"=>"", 'default'=>true),
		array('id'=>'SORT', 'content'=>Loc::getMessage('SALE_ESDL_COL_SORT'), "sort"=>"", 'default'=>true),
		array('id'=>'RIGHTS', 'content'=>Loc::getMessage('SALE_ESDL_COL_RIGHTS'), "sort"=>"", 'default'=>false),
		array('id'=>'ACTIVE', 'content'=>Loc::getMessage('SALE_ESDL_COL_ACTIVE'), "sort"=>"", 'default'=>true),
		array('id'=>'CLASS_NAME', 'content'=>Loc::getMessage('SALE_ESDL_COL_CLASS_NAME'), "sort"=>"", 'default'=>true),
		array('id'=>'DESCRIPTION', 'content'=>Loc::getMessage('SALE_ESDL_COL_DESCRIPTION'), "sort"=>"", 'default'=>true),
	);

	$lAdmin->AddHeaders($header);

	while ($record = $dbRecords->Fetch())
	{
		$link = 'sale_delivery_eservice_edit.php?ID='.$record['ID'].'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam().'&back_url='.urlencode($APPLICATION->GetCurPageParam());
		$row =& $lAdmin->AddRow($record['ID'], $record, $link, '');
		$row->AddField('ID', '<a href="'.$link.'">'.$record['ID'].'</a>');
		$row->AddField('CODE', htmlspecialcharsbx($record['CODE']));
		$row->AddField('NAME', htmlspecialcharsbx($record['NAME']));
		$row->AddField('SORT', intval($record['SORT']));
		$row->AddField('RIGHTS', $record['RIGHTS']);
		$row->AddField('ACTIVE', $record['ACTIVE'] == "Y" ? Loc::getMessage('SALE_ESDL_YES') : Loc::getMessage('SALE_ESDL_NO'));

		if(strlen($record['CLASS_NAME']) > 0 && is_callable($record['CLASS_NAME'].'::getClassTitle'))
			$className = $record['CLASS_NAME']::getClassTitle();
		else
			$className = "";

		$row->AddField('CLASS_NAME', $className);
		$row->AddField('DESCRIPTION', htmlspecialcharsbx($record['DESCRIPTION']));

		if ($saleModulePermissions >= "W")
		{
			$arActions = Array();
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => Loc::getMessage("SALE_ESDL_EDIT_DESCR"),
				"LINK" => $link,
				"DEFAULT" => true
			);
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON"=>"delete",
				"TEXT"=>Loc::getMessage("SALE_ESDL_DELETE"),
				"ACTION"=> "javascript:if(confirm('".Loc::getMessage("SALE_ESDL_CONFIRM_DEL_MESSAGE")."')) window.location='".
					$APPLICATION->GetCurPageParam(
						"action=delete_extra_service&ES_ID=".$record['ID']."&".bitrix_sessid_get().'&back_url='.urlencode($_REQUEST["back_url"]),
						array("back_url", "ES_ID")
					)."';",
			);

			$row->AddActions($arActions);
		}
	}

	if ($saleModulePermissions == "W")
	{
		$aContext = array();

		$addButtonParams =  array(
			"TEXT" => Loc::getMessage("SALE_ESDL_BUTTON_ADD_NEW"),
			"LINK" => 'sale_delivery_eservice_edit.php?lang='.LANGUAGE_ID.'&DELIVERY_ID='.$ID.'&'.$tabControl->ActiveTabParam().'&back_url='.urlencode($APPLICATION->GetCurPageParam()),
			"TITLE" => Loc::getMessage("SALE_ESDL_BUTTON_ADD_NEW"),
			"ICON" => "btn_new"
		);

		$menu = array();

		if($service && $embeddedList = $service->getEmbeddedExtraServicesList())
		{

			foreach($embeddedList as $code => $eserviceParams)
			{
				$menu[] = array(
					'TEXT' => $eserviceParams["NAME"],
					'LINK' => 'sale_delivery_eservice_edit.php?lang='.LANGUAGE_ID.'&DELIVERY_ID='.$ID.'&'.$tabControl->ActiveTabParam().'&ES_CODE='.$code.'&back_url='.urlencode($APPLICATION->GetCurPageParam()),
				);
			}

			sortByColumn($menu, array("TEXT" => SORT_ASC));

			$menu[] =  array(
				'SEPARATOR' => true,
			);
		}

		/** @var  \Bitrix\Sale\Delivery\ExtraServices\Base $esClass */
		foreach(ExtraServices\Manager::getClassesList() as $esClass)
		{
			if($esClass == '\Bitrix\Sale\Delivery\ExtraServices\String')
				continue;

			if($esClass::isEmbeddedOnly())
				continue;

			$menu[] =  array(
				'TEXT' => $esClass::getClassTitle(),
				"LINK" => 'sale_delivery_eservice_edit.php?lang='.LANGUAGE_ID.'&DELIVERY_ID='.$ID.'&'.$tabControl->ActiveTabParam().'&CLASS_NAME='.urlencode($esClass).'&back_url='.urlencode($APPLICATION->GetCurPageParam()),
			);
		}

		$addButtonParams["MENU"] = $menu;
		$aContext[] = $addButtonParams;
		$lAdmin->AddAdminContextMenu($aContext, false);
	}

	if($_REQUEST['table_id'] == $tableId)
		$lAdmin->CheckListMode();

	$lAdmin->DisplayList();
}