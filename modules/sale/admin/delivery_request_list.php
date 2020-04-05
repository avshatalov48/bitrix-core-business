<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use	\Bitrix\Main\Localization\Loc,
	\Bitrix\Sale\Delivery\Requests,
	\Bitrix\Sale\Delivery\Services;

\Bitrix\Main\Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($saleModulePermissions < "U")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_delivery_request_batch";
$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

if(($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	foreach ($ids as $id)
	{
		if(intval($id) <= 0)
			continue;

		switch($_REQUEST['action'])
		{
			case 'delete':
				$res = Requests\Manager::deleteDeliveryRequest($id);

				if($res->isSuccess())
				{
					$lAdmin->AddActionSuccessMessage(Loc::getMessage('SALE_DELIVERY_REQ_LIST_DELETE', array('#ID#' => $id)));
				}
				else
				{
					$lAdmin->AddGroupError(
						Loc::getMessage(
							'SALE_DELIVERY_REQ_LIST_DELETE_ERROR',
							array('#ID#' => $id))."\n".implode("\n",$res->getErrorMessages())."\n",
						$id
					);
				}

				break;

			default:
				$lAdmin->AddGroupError(Loc::getMessage('SALE_DELIVERY_REQ_LIST_UNKNOWN_ACTION', array('#ACTION#' => $_REQUEST['action'])), $id);
				break;
		}
	}
}

if(!isset($by))
	$by = 'ID';
if(!isset($order))
	$order = 'ASC';

$lAdmin->InitFilter(array(
	"find_id",
	"find_delivery_id",
//	"find_status",
	"find_external_id",
	"find_external_date_insert_from",
	"find_external_date_insert_to",
));

$filter = array();

if(intval($find_id) > 0) $filter["=ID"] = intval($find_id);
if(!empty($find_delivery_id) && is_array($find_delivery_id)) $filter["=DELIVERY_ID"] = $find_delivery_id;
//if(strlen(trim($find_status)) > 0) $filter["=STATUS"] = trim($find_status);
if(strlen(trim($find_external_id)) > 0) $filter["=EXTERNAL_ID"] = trim($find_external_id);
if(strval(trim($find_external_date_insert_from)) != '')
{
	$filter[">=DATE"] = trim($find_external_date_insert_from);
}

if(strval(trim($find_external_date_insert_to)) != '')
{
	if($arDate = ParseDateTime($find_external_date_insert_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(strlen($find_external_date_insert_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$find_external_date_insert_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$filter["<=DATE"] = $find_external_date_insert_to;
	}
	else
	{
		$find_external_date_insert_to = "";
	}
}

$deliveryList = array();

$res = \Bitrix\Sale\Delivery\Services\Table::getList(array(
	'filter' => array('ACTIVE' => 'Y'),
	'select' => array('ID', 'NAME')
));

while($row = $res->fetch())
{
	if(!($deliveryRequestHandler = Requests\Manager::getDeliveryRequestHandlerByDeliveryId($row['ID'])))
		continue;

	$handlingDeliveryServiceId = $deliveryRequestHandler->getHandlingDeliveryServiceId();

	if(!isset($deliveryList[$handlingDeliveryServiceId]))
	{
		$handlingDelivery = Services\Manager::getObjectById($handlingDeliveryServiceId);

		if($handlingDelivery)
			$deliveryList[$handlingDeliveryServiceId] = $handlingDelivery->getNameWithParent().' ['.$handlingDeliveryServiceId.']';
	}
}

$backUrl = urlencode($APPLICATION->GetCurPageParam());

$aHeaders = array(
	array("id"=>"ID", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_ID'), "sort"=>"ID", "default"=>true),
	array("id"=>"DATE", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_DATE_INSERT'), "sort"=>"DATE", "default"=>false),
	array("id"=>"DELIVERY_ID", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_DELIVERY_ID'), "default"=>true),
//	array("id"=>"STATUS", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_STATUS'), "default"=>true),
	array("id"=>"EXTERNAL_ID", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_EXTERNAL_ID'), "default"=>true),
	array("id"=>"ORDERS_NUMBER", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_ORDERS_NUMBER'), "default"=>false),
	array("id"=>"SHIPMENTS_NUMBER", "content"=>Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_SHIPMENTS_NUMBER'), "default"=>false)
);

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-delivery-requests");

$lAdmin->AddHeaders($aHeaders);
$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$glParams = array(
	'order' => array(strtoupper($by) => $order),
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
);

if(!empty($filter))
	$glParams['filter'] = $filter;

$resRequestList = Requests\RequestTable::getList($glParams);

$nav->setRecordCount($resRequestList->getCount());
$lAdmin->setNavigation($nav, Loc::getMessage("PAGES"));

while($fields = $resRequestList->fetch())
{
	$row =&$lAdmin->AddRow($fields['ID'], $fields);
	$row->AddViewField("ID", $fields['ID']);
	$row->AddViewField("DATE", $fields['DATE']);

	if($delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($fields['DELIVERY_ID']))
		$deliveryServiceName = $delivery->getNameWithParent().' ['.$fields['DELIVERY_ID'].']';
	else
		$deliveryServiceName = $fields['DELIVERY_ID'];

	$row->AddViewField(
		'DELIVERY_ID',
		'<a href="/bitrix/admin/sale_delivery_service_edit.php?lang='.LANGUAGE_ID.'&ID='.$fields['DELIVERY_ID'].'&PARENT_ID='.$delivery->getParentId().'">'.htmlspecialcharsbx($deliveryServiceName).'</a>'
	);

//	$row->AddViewField("STATUS", $fields['STATUS']);
	$row->AddViewField("EXTERNAL_ID", htmlspecialcharsbx($fields['EXTERNAL_ID']));

	if(in_array("ORDERS_NUMBER", $arVisibleColumns) || in_array("SHIPMENTS_NUMBER", $arVisibleColumns))
	{
		$shipmentNumbers = '';
		$orderNumbers = '';

		$reqRes = Requests\ShipmentTable::getList(array(
			'filter' => array(
				'=REQUEST_ID' => $fields['ID']
			),
			'select' => array(
				'SHIPMENT_ID',
				'SHIPMENT_NUMBER' => 'SHIPMENT.ACCOUNT_NUMBER',
				'ORDER_ID' => 'SHIPMENT.ORDER.ID',
				'ORDER_NUMBER' => 'SHIPMENT.ORDER.ACCOUNT_NUMBER',
			)
		));

		while($req = $reqRes->fetch())
		{
			if(strlen($shipmentNumbers) > 0)
				$shipmentNumbers .= ', ';

			$shipmentNumbers .= Requests\Helper::getShipmentEditLink($req['SHIPMENT_ID'], $req['SHIPMENT_ID'], $req['ORDER_ID']);

			if(strlen($orderNumbers) > 0)
				$orderNumbers .= ', ';

			$orderNumbers .= '<a href="/bitrix/admin/sale_order_view.php?ID='.$req['ORDER_ID'].'&lang='.LANGUAGE_ID.'">'.
				$req['ORDER_NUMBER'].'</a>';
		}

		$row->AddViewField("ORDERS_NUMBER", $orderNumbers);
		$row->AddViewField("SHIPMENTS_NUMBER", $shipmentNumbers);
	}

	if ($saleModulePermissions >= "U")
	{
		$arActions = array();

		$arActions[] = 	array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_LIST_EDIT'),
			"ACTION" => $lAdmin->ActionRedirect("sale_delivery_request_view.php?lang=".LANGUAGE_ID."&ID=".$fields['ID'])
		);

		$arActions[] = 	array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_LIST_DEL'),
			"ACTION" => "if(confirm('".Loc::getMessage('SALE_DELIVERY_REQ_LIST_DEL_CONFIRM')."')) ".$lAdmin->ActionDoGroup($fields['ID'], "delete")
		);

		$row->AddActions($arActions);
	}
}

$lAdmin->AddGroupActionTable(array(
	'delete' => Loc::getMessage('SALE_DELIVERY_REQ_LIST_DEL')
));
$lAdmin->AddAdminContextMenu(array());
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('SALE_DELIVERY_REQ_LIST_TITLE'));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_ID'),
		Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_DELIVERY_ID'),
//		Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_STATUS'),
		Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_EXTERNAL_ID'),
		Loc::getMessage('SALE_DELIVERY_REQ_LIST_DATE_INSERT')
	)
);
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$oFilter->Begin();?>
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_ID')?>:</td>
		<td><input type="text" name="find_id" size="40" value="<?= htmlspecialcharsbx($find_name)?>"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_DELIVERY_ID')?>:</td>
		<td>
			<select name="find_delivery_id[]" multiple size="3" class="adm-select-multiple">
				<?foreach($deliveryList as $deliveryId => $deliveryName):?>
					<option value="<?=$deliveryId?>"<?=(is_array($find_delivery_id) && in_array($deliveryId, $find_delivery_id) ? ' selected' : '')?>><?=htmlspecialcharsbx($deliveryName)?></option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
<!--
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_STATUS')?>:</td>
		<td><input type="text" name="find_status" size="40" value="<?= htmlspecialcharsbx($find_status)?>"><?=ShowFilterLogicHelp()?></td>
	</tr>
-->
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_EXTERNAL_ID')?>:</td>
		<td><input type="text" name="find_external_id" size="40" value="<?= htmlspecialcharsbx($find_external_id)?>"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_LIST_F_DATE_INSERT')?>:</td>
		<td>
			<?=CalendarPeriod(
				"find_external_date_insert_from",
				$find_external_date_insert_from,
				"find_external_date_insert_to",
				$find_external_date_insert_to,
				"form1",
				"Y")
			?>
		</td>
	</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"form1"));
$oFilter->End();
?>
	</form>
<?

$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
