<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * @var \Bitrix\Sale\Delivery\Services\Base $service
 */

global $tabControl, $APPLICATION;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "U")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ESDL_ACCESS_DENIED"));

/**
 * @var CDatabase $DB
 * @var CMain  $APPLICATION
 */

use Bitrix\Main\Localization\Loc,
	\Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

$requestId = intval($_GET['ID']);
$infoMessages = array();

if($requestId <= 0)
{
	$adminErrorMessages[] = Loc::getMessage('SALE_DELIVERY_REQ_DRS_WRONG_ID');
	return;
}

if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete_request_shipment" && isset($_REQUEST["SHIPMENT_ID"]) && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	if(intval($_REQUEST["SHIPMENT_ID"] > 0))
	{
		$res = Requests\Manager::deleteShipmentsFromDeliveryRequest($_REQUEST["ID"], array($_REQUEST["SHIPMENT_ID"]));

		foreach($res->getShipmentResults() as $shpRes)
		{
			if($res->isSuccess())
			{
				$infoMessages[] = Loc::getMessage(
					'SALE_DELIVERY_REQ_DRS_DEL_SHIPMENT_SUCCESS',
					array(
						'#SHIPMENT_ID#' => $shpRes->getInternalId(),
						'#REQUEST_ID#' => $requestId
				));
			}
			else
			{
				$adminErrorMessages[]  = implode("<br>\n",$res->getErrorMessages());
			}

			array_merge($infoMessages, $shpRes->getMessagesMessages());
		}

		if(!$res->isSuccess())
			$adminErrorMessages[]  = implode("<br>\n",$res->getErrorMessages());

		array_merge($infoMessages, $res->getMessagesMessages());
	}
	else
	{
		$adminErrorMessages[] = Loc::getMessage('SALE_DELIVERY_REQ_DRS_WRONG_SHP_ID');
	}
}

$tableId = 'table_delivery_request_shipment';
$oSort = new \CAdminSorting($tableId);
$lAdmin = new \CAdminList($tableId, $oSort);

$res = Requests\ShipmentTable::getList(array(
	'filter' => array(
		'=REQUEST_ID' => $requestId
	),
	'order' => array(
		'ID' => 'ASC'
	),
	'select' => array(
		'*',
		'ORDER_DATE' => 'SHIPMENT.ORDER.DATE_INSERT',
		'ORDER_NUMBER' => 'SHIPMENT.ORDER.ACCOUNT_NUMBER',
		'ORDER_ID' => 'SHIPMENT.ORDER.ID',
		'DELIVERY_ID' => 'SHIPMENT.DELIVERY_ID',
		'SHIPMENT_NUMBER' => 'SHIPMENT.ACCOUNT_NUMBER'
	)
));

$data = $res->fetchAll();
$dbRes = new \CDBResult;
$dbRes->InitFromArray($data);
$dbRecords = new \CAdminResult($dbRes, $tableId);
$dbRecords->NavStart();
$lAdmin->NavText($dbRecords->GetNavPrint(Loc::getMessage('SALE_DELIVERY_REQ_DRS_LIST')));

$header = array(
	array('id'=>'ID', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_ID'), "sort" => "", 'default' => false),
	array('id'=>'ORDER_NUMBER', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_ORDER_NUMBER'), "sort" => "", 'default' => true),
	array('id'=>'ORDER_DATE', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_ORDER_DATE'), "sort" => "", 'default' => true),
	array('id'=>'SHIPMENT_ID', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_SHIPMENT_ID'), "sort" => "", 'default' => true),
	array('id'=>'EXTERNAL_ID', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_EXTERNAL_ID'), "sort" => "", 'default' => true),
	array('id'=>'DELIVERY_ID', 'content' => Loc::getMessage('SALE_DELIVERY_REQ_DRS_F_DELIVERY_ID'), "sort" => "", 'default' => true)
);

$lAdmin->AddHeaders($header);

while ($record = $dbRecords->Fetch())
{
	$row =& $lAdmin->AddRow($record['ID'], $record);
	$row->AddField('ID', $record['ID']);
	$row->AddField('ORDER_DATE', $record['ORDER_DATE']);
	$row->AddField('EXTERNAL_ID', htmlspecialcharsbx($record['EXTERNAL_ID']));
	$row->AddField(
		'ORDER_NUMBER',
		'<a href="/bitrix/admin/sale_order_view.php?ID='.$record['ORDER_ID'].'&lang='.LANGUAGE_ID.'">'.$record['ORDER_NUMBER'].'</a>'
	);
	$row->AddField(
		'SHIPMENT_ID',
		Requests\Helper::getShipmentEditLink($record['SHIPMENT_ID'], $record['SHIPMENT_ID'], $record['ORDER_ID'])
	);

	if(intval($record['DELIVERY_ID']) >0 && $delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($record['DELIVERY_ID']))
	{
		$deliveryServiceName = $delivery->getNameWithParent();
		$row->AddField(
			'DELIVERY_ID',
			'<a href="/bitrix/admin/sale_delivery_service_edit.php?lang='.LANGUAGE_ID.'&ID='.$record['DELIVERY_ID'].'&PARENT_ID='.$delivery->getParentId().'">'.htmlspecialcharsbx($deliveryServiceName).'</a>'
		);
	}
	else
	{
		$deliveryServiceName = $record['DELIVERY_ID'];
		$row->AddField(
			'DELIVERY_ID',
			'Not found ['.$record['DELIVERY_ID'].']'
		);
	}


	if ($saleModulePermissions >= "U")
	{
		$arActions = Array();
		
		$arActions[] = 	array(
			"ICON" => "view",
			"DEFAULT" => true,
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_DRS_CONTENT'),
			"ACTION" => "javascript:BX.Sale.Delivery.Request.showShipmentContent(".$record['REQUEST_ID'].", ".$record['SHIPMENT_ID'].");"
		);
		
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_DRS_DEL'),
			"ACTION"=> "javascript:if(confirm('".Loc::getMessage('SALE_DELIVERY_REQ_DRS_DEL_CONFIRM')."')){ window.location='".
				$APPLICATION->GetCurPageParam(
					"action=delete_request_shipment&SHIPMENT_ID=".$record["SHIPMENT_ID"]."&RS_ID=".$record['ID']."&".bitrix_sessid_get().'&back_url='.urlencode($_REQUEST["back_url"]),
					array("back_url", "RS_ID", "SHIPMENT_ID")
				)."'};",
		);

		$row->AddActions($arActions);
	}
}

if($_REQUEST['table_id'] == $tableId)
	$lAdmin->CheckListMode();

if(!empty($adminErrorMessages))
{
	$adminMessage = new CAdminMessage(Array(
		"DETAILS" => implode("<br>\n", $adminErrorMessages),
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SALE_DELIVERY_REQ_DRS_ERROR'),
		"HTML"=>true
	));
	echo $adminMessage->Show();
}
if(!empty($infoMessages))
{
	$adminMessage = new CAdminMessage(
		array(
			"DETAILS" => implode("<br>\n", $infoMessages),
			"TYPE" => "OK",
			"HTML" => true
		)
	);
	echo $adminMessage->Show();
}

$lAdmin->DisplayList();