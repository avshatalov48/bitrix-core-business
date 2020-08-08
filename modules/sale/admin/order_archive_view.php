<?
/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$saleOrder = null;
$moduleId = "sale";
$errorMsgs = array();

Loc::loadMessages(__FILE__);
Bitrix\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$arUserGroups = $USER->GetUserGroupArray();

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$allowedStatusesView = array();
	
//load archived order
if(!empty($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0)
{
	$view = new Admin\Blocks\Archive\View($_REQUEST["ID"]);
	$saleOrder = $view->loadOrder();
}

if ($saleOrder)
{
	if ($saleModulePermissions == 'P')
	{
		$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

		if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID())
		{
			$isUserResponsible = true;
		}

		if (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList))
		{
			$isAllowCompany = true;
		}

		if (!$isUserResponsible && !$isAllowCompany)
		{
			LocalRedirect("/bitrix/admin/sale_order_archive.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
		}
	}
}
	
if($saleOrder)
	$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));

if(!$saleOrder || !in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesView))
	LocalRedirect("/bitrix/admin/sale_order_archive.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

$id = (int)($_REQUEST["ID"]);

$customTabber = new CAdminTabEngine("OnAdminSaleOrderView", array("ID" => $id));

$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderViewDraggable', array('ORDER' => $saleOrder));

/** @var Bitrix\Sale\Order $saleOrder */
Admin\OrderEdit::initCouponsData(
	$saleOrder->getUserId(),
	$id,
	null
);

CUtil::InitJSCore();
$APPLICATION->SetTitle(
	Loc::getMessage(
		"SALE_OVIEW_TITLE",
		array(
			"#ID#" => $saleOrder->getId(),
			"#NUM#" => $saleOrder->getField('ACCOUNT_NUMBER') <> '' ? $saleOrder->getField('ACCOUNT_NUMBER') : $saleOrder->getId(),
			"#DATE#" => $saleOrder->getDateInsert()->toString()
		)
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* context menu */
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SALE_OVIEW_TO_ARCHIVE_LIST"),
	"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_ARCHIVE_LIST_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_archive.php?target=list&ID=".$id."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
);

$allowedStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

if(!$boolLocked && in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesUpdate))
{
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SALE_OVIEW_TO_RESTORE"),
		"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_RESTORE_TITLE"),
		"LINK" => "/bitrix/admin/sale_order_create.php?restoreID=".$id."&lang=".LANGUAGE_ID."&SITE_ID=".$saleOrder->getSiteId()
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errorMsgs))
{
	$m = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => implode("<br>\n", $errorMsgs),
			"HTML" => true
	));

	echo $m->Show();
}

//prepare blocks order
$defaultBlocksOrder = array(
	"statusorder",
	"buyer",
	"delivery",
	"financeinfo",
	"payment",
	"additional",
	"basket"
);

$basketPrefix = "sale_order_basket";

$formId = "sale_order_archive";

$orderBasket = new Admin\Blocks\OrderBasket(
	$saleOrder,
	"BX.Sale.Admin.OrderBasketObj",
	$basketPrefix,
	true,
	Admin\Blocks\OrderBasket::VIEW_MODE
);

echo Admin\OrderEdit::getScripts($saleOrder, $formId);
echo Admin\Blocks\OrderInfo::getScripts();
echo Admin\Blocks\OrderBuyer::getScripts();
echo Admin\Blocks\OrderPayment::getScripts();
echo Admin\Blocks\OrderStatus::getScripts($saleOrder, $USER->GetID());
echo Admin\Blocks\OrderAdditional::getScripts();
echo Admin\Blocks\OrderFinanceInfo::getScripts();
echo Admin\Blocks\OrderShipment::getScripts();
echo Admin\Blocks\OrderAnalysis::getScripts();
echo $orderBasket->getScripts();

echo $customDraggableBlocks->getScripts();

$blocks = $view->getTemplates();

// navigation socket
?><div id="sale-order-edit-block-fast-nav-socket"></div><?

// yellow block with brief
echo Admin\Blocks\OrderInfo::getView($saleOrder, $orderBasket);

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
);

$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);

$tabControl->Begin();

//TAB order --
$tabControl->BeginNextTab();
$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);

$fastNavItems = array();

foreach($blocksOrder as $item)
	$fastNavItems[$item] = Loc::getMessage("SALE_OVIEW_BLOCK_TITLE_".ToUpper($item));

foreach($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams)
{
	$defaultBlocksOrder[] = $blockId;
	$fastNavItems[$blockId] = $blockParams['TITLE'];
}

?>
<tr>
	<td>
	<?=bitrix_sessid_post()?>
	<div style="position: relative; vertical-align: top">
		<?$tabControl->DraggableBlocksStart();?>
		<?
			foreach ($blocksOrder as $blockCode)
			{
				echo '<a id="'.$blockCode.'"  class="adm-sale-fastnav-anchor"></a>';
				$tabControl->DraggableBlockBegin($fastNavItems[$blockCode], $blockCode);
				echo $blocks[$blockCode];
				$tabControl->DraggableBlockEnd();
			}
		?>
	</div>
</td>
</tr>

<?
$tabControl->EndTab();
?>

<div style="display: none;"><?=Admin\OrderEdit::getFastNavigationHtml($fastNavItems);?></div>

<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<script type="text/javascript">
	BX.ready( function(){
		BX.Sale.Admin.OrderEditPage.setFixHashCorrection();

		//place navigation data to navigation socket
		BX('sale-order-edit-block-fast-nav-socket').appendChild(
			BX('sale-order-edit-block-fast-nav')
		);
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");