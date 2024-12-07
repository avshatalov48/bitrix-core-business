<?
/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Sale;
use \Bitrix\Sale\Exchange\Integration\Admin\Link,
	\Bitrix\Sale\Exchange\Integration\Admin\Registry,
	\Bitrix\Sale\Exchange\Integration\Admin\ModeType,
	\Bitrix\Sale\Helpers\Admin\Blocks\FactoryMode,
	\Bitrix\Sale\Helpers\Admin\Blocks\BlockType;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$saleOrder = null;
$moduleId = "sale";
$errorMsgs = array();

Loc::loadMessages(__FILE__);
Bitrix\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$arUserGroups = $USER->GetUserGroupArray();
$link = Link::getInstance();
$factory = FactoryMode::create($link->getType());

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

$allowedStatusesView = array();

$isAllowView = false;
$isAllowUpdate = false;
$isAllowDelete = false;

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

/** @var Sale\Order $orderClass */
$orderClass = $registry->getOrderClassName();

//load order
if(!empty($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0)
	$saleOrder = $orderClass::load($_REQUEST["ID"]);

if($saleOrder)
{
	/** @var Sale\OrderStatus $orderStatusClass */
	$orderStatusClass = $registry->getOrderStatusClassName();

	$allowedStatusesView = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
	$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
	$allowedStatusesDelete = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
	$isAllowView = in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesView);
	$isAllowUpdate = in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesUpdate);
	$isAllowDelete = in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesDelete);
}

if(!$saleOrder || !$isAllowView)
{
	$link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER)
		->redirect();
}

$isUserResponsible = false;
$isAllowCompany = false;

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
		$link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->redirect();
	}
}

$ID = intval($_REQUEST["ID"]);
$boolLocked = $orderClass::isLocked($ID);

//Unlocking if we leave this page
if(isset($_REQUEST['unlock']) && 'Y' == $_REQUEST['unlock'])
{
	$lockStatusRes = $orderClass::getLockedStatus($ID);

	if($lockStatusRes->isSuccess())
		$lockStatusData = $lockStatusRes->getData();

	if(isset($lockStatusData['LOCK_STATUS'])
		&&
		(	$lockStatusData['LOCK_STATUS'] != $orderClass::SALE_ORDER_LOCK_STATUS_RED
			|| !isset($_REQUEST['target'])
		)
	)
	{
		$res = $orderClass::unlock($ID);

		if($res->isSuccess())
		{
			/** @var Sale\DiscountCouponsManager $discountCouponsClass */
			$discountCouponsClass = $registry->getDiscountCouponClassName();
			$discountCouponsClass::clearByOrder($ID);
		}
	}

	if(isset($_REQUEST['target']) && 'list' == $_REQUEST['target'])
	{
		$link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->redirect();
	}
	else
	{
		$link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_VIEW)
			->redirect();
	}
}

if ($boolLocked)
	$errorMsgs[] = Admin\OrderEdit::getLockingMessage($ID);
else
	$orderClass::lock($ID);

$customTabber = new CAdminTabEngine("OnAdminSaleOrderView", array("ID" => $ID));
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderViewDraggable', array('ORDER' => $saleOrder));

/** @var Sale\Order $saleOrder */
Admin\OrderEdit::initCouponsData(
	$saleOrder->getUserId(),
	$ID,
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

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

ob_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/order_history.php");
$historyContent = ob_get_contents();
ob_end_clean();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* context menu */
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SALE_OVIEW_TO_LIST"),
	"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_LIST_TITLE"),
	"LINK" => $link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER_VIEW)
		->setField('unlock','Y')
		->setField('target','list')
		->setField('ID', $ID)
		->build()
);

if ($boolLocked && $saleModulePermissions >= 'W')
{
	$aMenu[] = array(
		"TEXT" => GetMessage("SALE_OVIEW_UNLOCK"),
		"LINK" => $link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_VIEW)
			->setField('unlock','Y')
			->setField('ID', $ID)
			->build()
	);
}

if($link->getType() == ModeType::APP_LAYOUT_TYPE)
{
	if(!$boolLocked && $isAllowUpdate)
	{
		$aMenu[] = [
			"TEXT" => Loc::getMessage("SALE_OVIEW_TO_EDIT"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_EDIT_TITLE"),
			"LINK" => $link
				->create()
				->setFilterParams(false)
				->setPageByType(Registry::SALE_ORDER_EDIT)
				->setField('ID', $ID)
				->fill()
				->build(),
		];
	}
}
else
{
	if(!$boolLocked && $isAllowUpdate)
	{
		$aMenu[] = [
			"TEXT" => Loc::getMessage("SALE_OVIEW_TO_EDIT"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_EDIT_TITLE"),
			"LINK" => $link
				->create()
				->setFilterParams(false)
				->setPageByType(Registry::SALE_ORDER_EDIT)
				->setField('ID', $ID)
				->fill()
				->build(),
		];
	}

	$arSysLangs = array();
	$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
	while ($arLang = $db_lang->Fetch())
		$arSysLangs[] = $arLang["LID"];

	$arReports = array();
	$dirs = array(
		$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/",
		$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/"

	);
	foreach ($dirs as $dir)
	{
		if (file_exists($dir))
		{
			if ($handle = opendir($dir))
			{
				while (($file = readdir($handle)) !== false)
				{
					$file_contents = '';
					if ($file == "." || $file == ".." || $file == ".access.php" || isset($arReports[$file]))
						continue;
					if (is_file($dir.$file) && mb_strtoupper(mb_substr($file, -4)) == ".PHP")
					{
						$rep_title = $file;
						if ($dir == $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/")
						{
							if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file))
								$file_contents = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file);
						}

						if (empty($file_contents))
							$file_contents = file_get_contents($dir.$file);

						$rep_langs = "";
						$arMatches = array();
						if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
						{
							$arMatches[3] = Trim($arMatches[3]);
							if ($arMatches[3] <> '') $rep_title = $arMatches[3];
							$arMatches[2] = Trim($arMatches[2]);
							if ($arMatches[2] <> '') $rep_langs = $arMatches[2];
						}
						if ($rep_langs <> '')
						{
							$bContinue = true;
							foreach ($arSysLangs as $sysLang)
							{
								if (mb_strpos($rep_langs, $sysLang) !== false)
								{
									$bContinue = false;
									break;
								}
							}

							if ($bContinue)
								continue;
						}

						$defaultLink = new \Bitrix\Sale\Exchange\Integration\Admin\DefaultLink();
						$href = $defaultLink
							->setPageByType(Registry::SALE_ORDER_PRINT_NEW)
							->setField('ORDER_ID', $ID)
							->setField('doc', mb_substr($file, 0, mb_strlen($file) - 4))
							->setQuery(bitrix_sessid_get())
							->build();

						$arReports[$file] = array(
							"TEXT" => $rep_title,
							"ONCLICK" => "window.open('".$href."', '_blank');"
						);
					}
				}
			}
			closedir($handle);
		}
	}

	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SALE_OVIEW_TO_PRINT"),
		"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_PRINT_TITLE"),
		"MENU" => $arReports,
		"LINK" => $link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_PRINT)
			->setField('ID', $ID)
			->build()
	);

	$actionMenu = array();

	if ($isAllowUpdate)
	{
		$actionMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_OVIEW_ORDER_COPY"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_ORDER_COPY_TITLE"),
			"LINK" => $link
				->create()
				->setFilterParams(false)
				->fill()
				->setPageByType(Registry::SALE_ORDER_CREATE)
				->setField('SITE_ID', $saleOrder->getSiteId())
				->setField('ID', $ID)
				->setQuery(bitrix_sessid_get())
				->build()
		);
	}

	if($isAllowDelete)
	{
		$href = $link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->setField('SITE_ID', $saleOrder->getSiteId())
			->setField('ID', $ID)
			->setField('action', 'archive')
			->setQuery(bitrix_sessid_get())
			->build();

		$actionMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_OVIEW_TO_ARCHIVE"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_ARCHIVE_TITLE"),
			"LINK" => "javascript:if(confirm('".GetMessageJS("SALE_CONFIRM_ARCHIVE_MESSAGE")."')) window.location='".$href."'",
			"WARNING" => "Y"
		);

		if(!$boolLocked)
		{
			$href = $link
				->create()
				->setFilterParams(false)
				->fill()
				->setPageByType(Registry::SALE_ORDER)
				->setField('ID', $ID)
				->setField('action', 'delete')
				->setQuery(bitrix_sessid_get())
				->build();

			$actionMenu[] = array(
				"TEXT" => Loc::getMessage("SALE_OVIEW_DELETE"),
				"TITLE"=> Loc::getMessage("SALE_OVIEW_DELETE_TITLE"),
				"LINK" => "javascript:if(confirm('".GetMessageJS("SALE_OVIEW_DEL_MESSAGE")."')) window.location='".$href."'",
				"WARNING" => "Y"
			);
		}
	}

	if(!empty($actionMenu))
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_OVIEW_TO_ACTION"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_ACTION_TITLE"),
			"MENU" => $actionMenu
		);
	}
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
$formId = "sale_order_view";

$orderBasket = $factory::create(BlockType::BASKET, [
	'order'=> $saleOrder,
	'jsObjName' => "BX.Sale.Admin.OrderBasketObj",
	'idPrefix' => $basketPrefix,
	'createProductBasement' => true,
	'mode' => Admin\Blocks\OrderBasket::VIEW_MODE
]);

echo Admin\OrderEdit::getScripts($saleOrder, $formId);
echo $factory::create(BlockType::INFO)->getScripts();
echo $factory::create(BlockType::BUYER)->getScripts();
echo $factory::create(BlockType::PAYMENT)->getScripts();
echo $factory::create(BlockType::STATUS)->getScripts($saleOrder, $USER->GetID());
echo $factory::create(BlockType::ADDITIONAL)->getScripts();
echo $factory::create(BlockType::FINANCE_INFO)->getScripts();
echo $factory::create(BlockType::SHIPMENT)->getScripts();
echo $factory::create(BlockType::ANALYSIS)->getScripts();
echo $orderBasket->getScripts(true);
echo $customDraggableBlocks->getScripts();

// navigation socket
?><div id="sale-order-edit-block-fast-nav-socket"></div><?

// yellow block with brief
echo $factory::create(BlockType::INFO)->getView($saleOrder, $orderBasket);

// Problem block
?><div id="sale-adm-order-problem-block"><?
	if($saleOrder->getField("MARKED") == "Y")
	{
		echo $factory::create(BlockType::MARKER)->getView($saleOrder->getId());
	}
	?></div><?
$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
	array("DIV" => "tab_history", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_HISTORY"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_HISTORY")),
	array("DIV" => "tab_analysis", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_ANALYSIS"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_ANALYSIS"))
);

$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->AddTabs($customTabber);

$tabControl->Begin();

//TAB order --
$tabControl->BeginNextTab();
$customFastNavItems = array();
$customBlocksOrder = array();
$fastNavItems = array();

foreach($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams)
{
	$defaultBlocksOrder[] = $blockId;
	$customFastNavItems[$blockId] = $blockParams['TITLE'];
	$customBlocksOrder[] = $blockId;
}

$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
$customNewBlockIds = array_diff($customBlocksOrder, $blocksOrder);
$blocksOrder = array_merge($blocksOrder, $customNewBlockIds);

foreach($blocksOrder as $item)
{
	if(isset($customFastNavItems[$item]))
		$fastNavItems[$item] = $customFastNavItems[$item];
	else
		$fastNavItems[$item] = Loc::getMessage("SALE_OVIEW_BLOCK_TITLE_".mb_strtoupper($item));
}

$statusOnPaid = Bitrix\Main\Config\Option::get('sale', 'status_on_paid');
$statusOnAllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_allow_delivery');
$statusOnPaid2AllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_payed_2_allow_delivery');

$autoChangeStatus = 'Y';
if (empty($statusOnPaid) && (empty($statusOnAllowDelivery) || empty($statusOnPaid2AllowDelivery)))
	$autoChangeStatus = 'N';

?>
	<tr><td>
			<input type="hidden" id="ID" name="ID" value="<?=$ID?>">
			<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($saleOrder->getSiteId())?>">
			<input type="hidden" id="AUTO_CHANGE_STATUS_ON_PAID" name="AUTO_CHANGE_STATUS_ON_PAID" value="<?=$autoChangeStatus;?>">
			<?=bitrix_sessid_post()?>
			<div style="position: relative; vertical-align: top">
				<?$tabControl->DraggableBlocksStart();?>
				<?
				foreach ($blocksOrder as $blockCode)
				{
					$tabControl->DraggableBlockBegin($fastNavItems[$blockCode], $blockCode);
					echo '<a id="'.$blockCode.'" class="adm-sale-fastnav-anchor"></a>';

					$block = null;
					if(BlockType::resolveId($blockCode) && BlockType::resolveId($blockCode) !== BlockType::BASKET)
						$block = $factory::create(BlockType::resolveId($blockCode));

					switch (BlockType::resolveId($blockCode))
					{
						case BlockType::STATUS:
							echo $block::getEdit($saleOrder, $USER, true, true);
							break;
						case BlockType::BUYER:
							echo $block::getView($saleOrder);
							break;
						case BlockType::DELIVERY:
							\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
							\Bitrix\Main\UI\Extension::load('sale.admin_order');
							echo '<div id="sale-adm-order-shipments-content"><img src="/bitrix/images/sale/admin-loader.gif"/></div>';

							if ($isAllowUpdate)
							{
								echo $block::createNewShipmentButton([
									'addParams'=>$link
										->create()
										->setFilterParams(false)
										->setLang(false)
										->fill()
										->getFieldsValues()
								]);
							}

							break;
						case BlockType::FINANCE_INFO:
							echo $block::getView($saleOrder, false);
							break;
						case BlockType::PAYMENT:
							$payments = $saleOrder->getPaymentCollection();
							$index = 0;

							foreach ($payments as $payment)
								echo $block::getView($payment, $index++);

							if ($isAllowUpdate)
							{
								/** @var Admin\Blocks\OrderPayment $block*/
								echo $block::createButtonAddPayment([
									'formType'=>'view',
									'addParams'=>$link
										->create()
										->setFilterParams(false)
										->setLang(false)
										->fill()
										->getFieldsValues()
								]);
							}

							break;
						case BlockType::ADDITIONAL:
							echo $block::getView($saleOrder, $formId."_form");
							break;
						case BlockType::BASKET:
							echo $orderBasket->getView();
							echo '<div style="display: none;">'.$orderBasket->settingsDialog->getHtml().'</div>';
							break;
						default:
							echo $customDraggableBlocks->getBlockContent($blockCode, $tabControl->selectedTab);
							break;
					}

					$tabControl->DraggableBlockEnd();
				}
				?>
			</div>
		</td></tr>

<?
//--TAB order
$tabControl->EndTab();

//TAB history --
$tabControl->BeginNextTab();
?><tr><td id="order-history">
		<?=$historyContent?>
	</td></tr><?
//-- TAB history
$tabControl->EndTab();
//TAB analysis --
$tabControl->BeginNextTab();
?>
	<tr>
		<td>
			<div style="position:relative; vertical-align:top" id="sale-adm-order-analysis-content">
				<img src="/bitrix/images/sale/admin-loader.gif"/>
			</div>
		</td>
	</tr>
<?
//-- TAB analysis
$tabControl->EndTab();
$tabControl->End();

?>

	<div style="display: none;">
		<?=$orderBasket->getSettingsDialogContent();?>
	</div>

	<div style="display: none;"><?=Admin\OrderEdit::getFastNavigationHtml($fastNavItems, $formId, 'tab_order');?></div>

	<script>
		BX.ready( function(){

			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.merge(
					BX.Sale.Admin.OrderEditPage.ajaxRequests.getOrderTails("<?=$saleOrder->getId()?>", "view", "<?=$basketPrefix?>"),
					<?=\CUtil::PhpToJSObject($link
						->create()
						->setFilterParams(false)
						->setLang(false)
						->fill()
						->getFieldsValues())?>
				),
				true
			);

			BX.Sale.Admin.OrderEditPage.setFixHashCorrection();

			//place navigation data to navigation socket
			BX('sale-order-edit-block-fast-nav-socket').appendChild(
				BX('sale-order-edit-block-fast-nav')
			);
		});
	</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");