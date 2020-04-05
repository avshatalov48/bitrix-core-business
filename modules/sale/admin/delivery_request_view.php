<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Delivery\Requests,
	Bitrix\Sale\Delivery\Services;

Loc::loadMessages(__FILE__);
Bitrix\Main\Loader::includeModule('sale');

/** @var  CMain $APPLICATION
 * @var CDatabase $DB
 */

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "U")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSE_ACCESS_DENIED"));

$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;

$tabControlName = "tabControl";
$adminMessages = array();
$fields = array();

if($ID > 0)
{
	$res = Requests\RequestTable::getById($ID);
	$fields = $res->fetch();

	if(!$fields)
	{
		LocalRedirect('sale_delivery_request_list.php?lang='.LANGUAGE_ID);
	}
}
else
{
	LocalRedirect('sale_delivery_request_list.php?lang='.LANGUAGE_ID);
}

$aTabs = array(
	array(
		"DIV" => "edit_main",
		"TAB" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_MAIN'),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_MAIN_T')
	),
	array(
		"DIV" => "edit_body",
		"TAB" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_CONTENT'),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_CONTENT_T')
	),
	array(
		"DIV" => "edit_shipments",
		"TAB" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_SHP_LIST'),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_SHP_LIST_T')
	)
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$APPLICATION->SetTitle(Loc::getMessage('SALE_DELIVERY_REQ_VIEW_TITLE')." ".($ID > 0 ? " ID: ".$ID : ""));
\Bitrix\Sale\Delivery\Requests\Manager::initJs();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Sale\Internals\Input\Manager::initJs();

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_TO_LIST'),
		"LINK" => "/bitrix/admin/sale_delivery_request_list.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list"
	)
);

$delivery = null;
$deliveryId = intval($fields["DELIVERY_ID"]);

if ($ID > 0)
{
	$deliveryRequest = null;
	$aMenu[] = array("SEPARATOR" => "Y");

	if($deliveryId > 0)
		$delivery = Services\Manager::getObjectById($deliveryId);

	if($delivery)
		$deliveryRequest = $delivery->getDeliveryRequestHandler();

	if($deliveryRequest)
	{
		$rTypesMenu = array();

		foreach(Requests\Manager::getDeliveryRequestActions($fields['ID']) as $rCode => $rName)
		{
			$rTypesMenu[] = array(
				"TEXT" => $rName,
				"LINK" =>"javascript:BX.Sale.Delivery.Request.processRequest({action: 'actionExecute', deliveryId: ".$deliveryId.", requestAction: '".CUtil::JSEscape($rCode)."', requestId: ".$fields['ID'].", lang: '".LANGUAGE_ID."'})"
			);
		}

		if(!empty($rTypesMenu))
			$rTypesMenu[] = array("SEPARATOR" => true);

		$rTypesMenu[] = array(
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_DELETE'),
			"LINK" => "javascript:if(confirm('".Loc::getMessage('SALE_DELIVERY_REQ_VIEW_DEL_CONFIRM')."')){ window.location=\"/bitrix/admin/sale_delivery_request_list.php?lang=".LANGUAGE_ID."&action=delete&ID=".$ID."&".bitrix_sessid_get()."\"};"
		);

		$aMenu[] = array(
			"TEXT" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_ACTIONS'),
			"LINK" => 'javascript:void(0)',
			"MENU" => $rTypesMenu
		);
	}
}

$deliveryName = !!$delivery ? $delivery->getNameWithParent().' ['.$fields["DELIVERY_ID"].']' : $fields["DELIVERY_ID"];
$contentRes = Requests\Manager::getDeliveryRequestContent($fields['ID']);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($adminErrorMessages))
{
	$adminMessage = new CAdminMessage(Array(
		"DETAILS" => implode("<br>\n", $adminErrorMessages),
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SALE_DELIVERY_REQ_VIEW_ERROR'),
		"HTML"=>true
	));
	echo $adminMessage->Show();
}

?>
<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="form1" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>">
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="DELIVERY_ID" value="<?=$fields["DELIVERY_ID"]?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_ID')?>:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_DATE_INSERT')?>:</td>
		<td><?=$fields["DATE"]?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_DELIVERY_IDT')?>:</td>
		<td>
			<a href="/bitrix/admin/sale_delivery_service_edit.php?lang=ru&ID=<?=$fields["DELIVERY_ID"]?>"><?=htmlspecialcharsbx($deliveryName)?></a>
		</td>
	</tr>
<!--
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_STATUS')?>:</td>
		<td>
			<select name="STATUS" disabled>
				<option value="0"<?=$fields["STATUS"] == "0" ? ' selected' : ''?>><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_STATUS_R')?></option>
				<option value="10"<?=$fields["STATUS"] == "10" ? ' selected' : ''?>><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_STATUS_S')?></option>
				<option value="20"<?=$fields["STATUS"] == "20" ? ' selected' : ''?>><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_STATUS_P')?></option>
			</select>
		</td>
	</tr>
-->
	<tr>
		<td><?=Loc::getMessage('SALE_DELIVERY_REQ_VIEW_F_EXTERNAL_ID')?>:</td>
		<td><?=htmlspecialcharsbx($fields["EXTERNAL_ID"])?></td>
	</tr>

<?$tabControl->BeginNextTab();?>
	<?foreach($contentRes->getErrorMessages() as $item):?>
		<tr><td colspan="2" class="admin-delivery-request-confirm red"><?=htmlspecialcharsbx($item)?></td></tr>
	<?endforeach;?>
	<?foreach($contentRes->getMessagesMessages() as $item):?>
		<tr><td colspan="2" class="admin-delivery-request-confirm green"><?=htmlspecialcharsbx($item)?></td></tr>
	<?endforeach;?>
	<?$white = false;?>
	<?foreach($contentRes->getData() as $item):?>
		<tr>
			<td class="adm-sale-delivery-request-content<?=$white ? ' white' : ''?>" width="40%"><?=htmlspecialcharsbx($item["TITLE"])?>:</td>
			<td class="adm-sale-delivery-request-content<?=$white ? ' white' : ''?>" width="60%"><?=htmlspecialcharsbx($item["VALUE"])?></td>
		</tr>
	<?$white = !$white?>
	<?endforeach;?>

<?$tabControl->BeginNextTab();
	?><tr><td><?
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/delivery_request_shipment.php");
	?></td></tr>
<?$tabControl->End();?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");