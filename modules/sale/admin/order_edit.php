<?
/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Sale;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin\Blocks;
use \Bitrix\Sale\Exchange\Integration\Admin\Link,
	\Bitrix\Sale\Exchange\Integration\Admin\Registry,
	\Bitrix\Sale\Exchange\Integration\Admin\ModeType,
	\Bitrix\Sale\Helpers\Admin\Blocks\FactoryMode,
	\Bitrix\Sale\Helpers\Admin\Blocks\BlockType;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

Bitrix\Main\Loader::includeModule('sale');
$moduleId = "sale";
$result = new \Bitrix\Sale\Result();
$order = null;
Loc::loadMessages(__FILE__);
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$arUserGroups = $USER->GetUserGroupArray();
$boolLocked = false;
$link = Link::getInstance();
$factory = FactoryMode::create($link->getType());

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

/** @var Sale\Order $orderClass */
$orderClass = $registry->getOrderClassName();

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

/** @var Sale\Order $order */
if(!isset($_REQUEST["ID"]) || intval($_REQUEST["ID"]) <= 0)
	$link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER)
		->redirect();

$ID = intval($_REQUEST["ID"]);
$boolLocked = $orderClass::isLocked($ID);

if($boolLocked)
	$link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER_VIEW)
		->setField('ID', $ID)
		->redirect();

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
		$link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->redirect();
	else
		$link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_EDIT)
			->setField('ID', $ID)
			->redirect();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

//load order

$boolLocked = $orderClass::isLocked($ID);

if ($boolLocked)
{
	$result->addError(
		new \Bitrix\Main\Entity\EntityError(
			OrderEdit::getLockingMessage($ID)
		)
	);
}

$order = $orderClass::load($_REQUEST["ID"]);
if(!$order)
{
	$link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER)
		->redirect();
}

$orderStatusClass = $registry->getOrderStatusClassName();

$allowedStatusesUpdate = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

if(!in_array($order->getField("STATUS_ID"), $allowedStatusesUpdate))
	$link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER_VIEW)
		->setField('ID', $ID)
		->redirect();

$isUserResponsible = false;
$isAllowCompany = false;

if ($saleModulePermissions == 'P')
{
	$userCompanyList = array();
	$groups = $USER->GetUserGroupArray();

	$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
	{
		$isUserResponsible = true;
	}

	if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
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

$userId = isset($_POST["USER_ID"]) ? intval($_POST["USER_ID"]) : $order->getUserId();

OrderEdit::initCouponsData(
	$userId,
	$ID,
	isset($_POST["OLD_USER_ID"]) ? intval($_POST["USER_ID"]) : null
);

if(!$boolLocked)
	$orderClass::lock($ID);

$customTabber = new CAdminTabEngine("OnAdminSaleOrderEdit", array("ID" => $ID));
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderEditDraggable', array('ORDER' => $order));

$isSavingOperation = $_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["apply"]) || isset($_POST["save"]));
$isRefreshDataAndSaveOperation = ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["refresh_data_and_save"]) && $_POST["refresh_data_and_save"] == "Y");
$isNeedFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation && !$isRefreshDataAndSaveOperation;

//save order params
if (($isSavingOperation || $isNeedFieldsRestore || $isRefreshDataAndSaveOperation)
	&& ($saleModulePermissions >= "U" || ($saleModulePermissions == "P" && ($isAllowCompany === true || $isUserResponsible === true)))
	&& check_bitrix_sessid()
	&& $result->isSuccess()
)
{
	if(($isSavingOperation || $isRefreshDataAndSaveOperation))
	{
		if($isSavingOperation)
			OrderEdit::$isTrustProductFormData = true;

		if($isRefreshDataAndSaveOperation)
			OrderEdit::$isRefreshData = true;

		$res = OrderEdit::editOrderByFormData($_POST, $order, $USER->GetID(), true, $_FILES, $result);

		if($res)
			$order = $res;

		if($res)
		{
			if (!$customTabber->Check())
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString();
				else
					$errorMessage .= "Custom tabber check unknown error!";

				$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
			}

			if (!$customDraggableBlocks->check())
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString();
				else
					$errorMessage .= "Custom draggable block check unknown error!";

				$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
			}

			$res = OrderEdit::saveCoupons($order->getUserId(), $_POST);

			if(!$res)
				$result->addError(new \Bitrix\Main\Entity\EntityError("Can't save coupons!"));

			$discount = $order->getDiscount();

			if ($isRefreshDataAndSaveOperation)
			{
				/** @var Sale\DiscountCouponsManager $discountCouponsClass */
				$discountCouponsClass = $registry->getDiscountCouponClassName();

				$discountCouponsClass::clearApply(true);
				$discountCouponsClass::useSavedCouponsForApply(true);
				$discount->setOrderRefresh(true);
				$discount->setApplyResult(array());
				/** @var \Bitrix\Sale\Basket $basket */
				if (!($basket = $order->getBasket()))
					throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');

				$res = $basket->refreshData(array('PRICE', 'COUPONS'));

				if(!$res->isSuccess())
					$result->addErrors($res->getErrors());

			}

			$res = $discount->calculate();
			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());
			else
			{
				$discountData = $res->getData();
				if (!empty($discountData) && is_array($discountData))
				{
					$t = $order->applyDiscount($discountData);
					if (!$t->isSuccess())
					{
						$result->addErrors($t->getErrors());
					}
					unset($t);
				}
				unset($discountData);
			}

			if ($isRefreshDataAndSaveOperation && !$order->isCanceled() && !$order->isPaid())
			{
				/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
				if (($paymentCollection = $order->getPaymentCollection()) && count($paymentCollection) == 1)
				{
					/** @var \Bitrix\Sale\Payment $payment */
					if (($payment = $paymentCollection->rewind()) && !$payment->isPaid())
					{
						$payment->setFieldNoDemand('SUM', $order->getPrice());
					}
				}
			}

			if ($result->isSuccess())
			{
				$res = $order->verify();
				if ($res->isSuccess())
				{
					$res = $order->save();
					if (!$res->isSuccess())
					{
						$result->addErrors($res->getErrors());
					}
				}
				else
				{
					$result->addErrors($res->getErrors());
				}

				if ($result->isSuccess())
				{
					\Bitrix\Sale\Provider::resetTrustData($order->getSiteId());
					if(isset($_POST["BUYER_PROFILE_ID"]))
					{
						$profResult = OrderEdit::saveProfileData(intval($_POST["BUYER_PROFILE_ID"]), $order, $_POST);

						if(!$profResult->isSuccess())
							$result->addErrors($profResult->getErrors());
					}

					if (!$customTabber->Action())
					{
						if ($ex = $APPLICATION->GetException())
							$errorMessage .= $ex->GetString();
						else
							$errorMessage .= "Custom tabber action unknown error!";

						$result->addError(new \Bitrix\Main\Error($errorMessage));
					}

					if (!$customDraggableBlocks->action())
					{
						if ($ex = $APPLICATION->GetException())
							$errorMessage .= $ex->GetString();
						else
							$errorMessage .= "Custom draggable block action unknown error!";

						$result->addError(new \Bitrix\Main\Error($errorMessage));
					}

					if(!$result->isSuccess())
						$_SESSION['SALE_ORDER_EDIT_ERROR'] = implode('<br>\n',$result->getErrorMessages());

					if(isset($_POST["save"]))
					{
						if ($orderClass::isLocked($ID))
							$orderClass::unlock($ID);

						$link
							->create()
							->setFilterParams(false)
							->fill()
							->setPageByType(Registry::SALE_ORDER_EDIT)
							->setField('unlock','Y')
							->setField('target','list')
							->setField('ID', $ID)
							->redirect();
					}
					else
					{
						$link
							->create()
							->setFilterParams(false)
							->fill()
							->setPageByType(Registry::SALE_ORDER_EDIT)
							->setField('ID', $ID)
							->redirect();
					}
				}
			}
		}
		else
		{
			$result->addError(new \Bitrix\Main\Error(Loc::getMessage('SOE_ORDER_UPDATE_ERROR')));
		}
	}
}

CUtil::InitJSCore();

$APPLICATION->SetTitle(
	Loc::getMessage(
		"NEWO_TITLE_EDIT",
		array(
			"#ID#" => $order->getId(),
			"#NUM#" => $order->getField('ACCOUNT_NUMBER') <> '' ? $order->getField('ACCOUNT_NUMBER') : $order->getId(),
			"#DATE#" => $order->getDateInsert()->toString()
		)
	)
);
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* context menu */
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SOE_TO_LIST"),
	"TITLE"=> Loc::getMessage("SOE_TO_LIST_TITLE"),
	"LINK" => $link
		->create()
		->setFilterParams(false)
		->fill()
		->setPageByType(Registry::SALE_ORDER_EDIT)
		->setField('unlock','Y')
		->setField('target','list')
		->setField('ID', $ID)
		->build()
);

if ($boolLocked && $saleModulePermissions >= 'W')
{
	$aMenu[] = array(
		"TEXT" => GetMessage("SOE_TO_UNLOCK"),
		"LINK" => $link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_EDIT)
			->setField('unlock','Y')
			->setField('ID', $ID)
			->build()
	);
}

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SOE_ORDER_VIEW"),
	"TITLE"=> Loc::getMessage("SOE_ORDER_VIEW_TITLE"),
	"LINK" => $link
		->create()
		->setFilterParams(false)
		->fill()->setPageByType(Registry::SALE_ORDER_VIEW)
		->setField('ID', $ID)
		->build()
);

if($link->getType() == ModeType::APP_LAYOUT_TYPE)
{
	//nothing
}
else
{
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
							"ONCLICK" => "window.open('".$href."', '_blank');",
						);
					}
				}
			}
			closedir($handle);
		}
	}

	$aMenu[] = array(
		"TEXT" => Loc::getMessage("NEWO_TO_PRINT"),
		"TITLE"=> Loc::getMessage("NEWO_TO_PRINT_TITLE"),
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

	$disabledRefresh = false;
	if ($order && $order->getId() > 0)
	{
		$shipmentCollection = $order->getShipmentCollection();

		if ($shipmentCollection->isShipped())
		{
			$disabledRefresh = true;
		}
	}

	if (!$disabledRefresh)
	{
		$actionMenu[] = array(
			"TEXT" => Loc::getMessage("SOE_ORDER_REFRESH"),
			"TITLE"=> Loc::getMessage("SOE_ORDER_REFRESH_TITLE"),
			"ONCLICK" => "if(confirm('".GetMessageJS("SOE_ORDER_REFRESH_CONFIRM")."')) BX.Sale.Admin.OrderEditPage.onRefreshOrderDataAndSave();"
		);
	}

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
		"TEXT" => Loc::getMessage("NEWO_ORDER_DELETE"),
		"TITLE"=> Loc::getMessage("NEWO_ORDER_DELETE_TITLE"),
		"ONCLICK" => "if(confirm('".GetMessageJS("NEWO_CONFIRM_DEL_MESSAGE")."')) window.location='".$href."'"
	);

	if(!empty($actionMenu))
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SOE_TO_ACTION"),
			"TITLE"=> Loc::getMessage("SOE_TO_ACTION_TITLE"),
			"MENU" => $actionMenu
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

//prepare blocks order
$defaultBlocksOrder = array(
	"statusorder",
	"buyer",
	"delivery",
	"payment",
	"additional",
	"basket"
);

// errors
$message = "";

if(!empty($_SESSION['SALE_ORDER_EDIT_ERROR']))
{
	$message = $_SESSION['SALE_ORDER_EDIT_ERROR']."<br>\n";
	unset($_SESSION['SALE_ORDER_EDIT_ERROR']);
}

if(!$result->isSuccess() && !$isNeedFieldsRestore)
	foreach($result->getErrors() as $error)
		$message .= $error->getMessage()."<br>\n";

if(!empty($message))
{
	$admMessage = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => $message,
		"HTML" => true
	));

	echo $admMessage->Show();
}

$formId = "sale_order_edit";
$basketPrefix = "sale_order_basket";

$orderBasket = new Blocks\OrderBasket(
	$order,
	"BX.Sale.Admin.OrderBasketObj",
	$basketPrefix
);

$orderBasket->setSettingsShowPropsVisible(true);

///

$defTails = $result->isSuccess() && !$isNeedFieldsRestore;

echo OrderEdit::getScripts($order, $formId);
echo Blocks\OrderInfo::getScripts();
echo Blocks\OrderBuyer::getScripts();
echo Blocks\OrderPayment::getScripts();
echo Blocks\OrderAdditional::getScripts();
echo Blocks\OrderStatus::getScripts($order, $USER->GetID());
echo Blocks\OrderFinanceInfo::getScripts();
echo Blocks\OrderShipment::getScripts();
echo $orderBasket->getScripts($defTails);
echo $customDraggableBlocks->getScripts();

// navigation socket
?><div id="sale-order-edit-block-fast-nav-socket"></div><?

// yellow block with brief
echo Blocks\OrderInfo::getView($order, $orderBasket);

// Problem block
?><div id="sale-adm-order-problem-block"><?
	if($order->getField("MARKED") == "Y")
	{
		echo Blocks\OrderMarker::getView($order->getId());
	}
	?></div><?

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
	array("DIV" => "tab_analysis", "TAB" => Loc::getMessage("SALE_TAB_ANALYSIS"), "TITLE" => Loc::getMessage("SALE_TAB_ANALYSIS"))
);

$url = $link
	->create()
	->setPage($APPLICATION->GetCurPage())
	->setFilterParams(false)
	->setField('ID', $ID)
	->fill()
	->build();

?><form method="POST" action="<?=$url?>" name="sale_order_edit_form" id="sale_order_edit_form" enctype="multipart/form-data"><?

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
			$fastNavItems[$item] = Loc::getMessage("SALE_BLOCK_TITLE_".mb_strtoupper($item));
	}

?>
<tr><td>
	<input type="hidden" id="ID" name="ID" value="<?=$ID?>">
	<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($order->getSiteId())?>">
	<input type="hidden" id="OLD_USER_ID" name="OLD_USER_ID" value="<?=$order->getUserId()?>">
	<input type="hidden" name="BASKET_PREFIX" value="<?=$basketPrefix?>">
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
					echo $block::getEdit($order, $USER, false, false);
					break;
				case BlockType::BUYER:
					echo $block::getEdit($order);
					break;
				case BlockType::DELIVERY:
					if($defTails)
					{
						\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
						\Bitrix\Main\UI\Extension::load('sale.admin_order');
						echo '<div id="sale-adm-order-shipments-content"><img src="/bitrix/images/sale/admin-loader.gif"/></div>';
					}
					else
					{
						$shipments = $order->getShipmentCollection();
						$index = 0;
						/** @var \Bitrix\Sale\Shipment  $shipment*/
						foreach ($shipments as $shipment)
						{
							if(!$shipment->isSystem())
							{
								echo $block::getView(
									$shipment,
									$index++,
									'edit'
								);
							}
						}
					}

					break;
				case BlockType::PAYMENT:

					$payments = $order->getPaymentCollection();
					$index = 0;

					foreach ($payments as $payment)
						echo $block::getView($payment, ++$index, 'edit');

					break;
				case BlockType::ADDITIONAL:
					echo $block::getEdit($order, $formId."_form", 'ORDER');
					break;
				case BlockType::BASKET:
					echo $orderBasket->getEdit($defTails);
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
$tabControl->EndTab();
//--TAB order

//TAB analysis --
$tabControl->BeginNextTab();
?>
<tr>
	<td>
		<?if($defTails):?>
			<div style="position:relative; vertical-align:top" id="sale-adm-order-analysis-content">
				<img src="/bitrix/images/sale/admin-loader.gif"/>
			</div>
		<?else:?>
			<?=Blocks\OrderAnalysis::getView($order, $orderBasket);?>
		<?endif;?>
	</td>
</tr>
<?
$tabControl->EndTab();
//-- TAB analysis

$tabControl->Buttons(
	array(
		"disabled" => true, //while tails are not loaded.
		"back_url" => $link
			->create()
			->setFilterParams(false)
			->fill()
			->setPageByType(Registry::SALE_ORDER_EDIT)
			->setField('ID', $ID)
			->setField('unlock', 'Y')
			->setField('target', 'list')
			->build()
	)
);

$tabControl->End();
?>

</form>
<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<div style="display: none;"><?=OrderEdit::getFastNavigationHtml($fastNavItems, $formId, 'tab_order');?></div>

<?if(!$result->isSuccess() || $isNeedFieldsRestore):?>
	<script>
		BX.ready( function(){
			BX.Sale.Admin.OrderEditPage.restoreFormData(
				<?=CUtil::PhpToJSObject(OrderEdit::restoreFieldsNames(
					array_diff_key($_POST, array("USER_ID" => true))
				));
				?>
			);
			BX.Sale.Admin.OrderEditPage.enableFormButtons('sale_order_edit_form');
		});
	</script>
<?else:?>
	<script>
		BX.ready( function(){
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.merge(
					BX.Sale.Admin.OrderEditPage.ajaxRequests.getOrderTails("<?=$order->getId()?>", "edit", "<?=$basketPrefix?>"),
					<?=\CUtil::PhpToJSObject($link
						->create()
						->setFilterParams(false)
						->setLang(false)
						->fill()
						->getFieldsValues())?>
				),
				true
			);

			BX.addCustomEvent('onAfterSaleOrderTailsLoaded', function(){
				BX.Sale.Admin.OrderEditPage.enableFormButtons('sale_order_edit_form');
			});

			BX.Sale.Admin.OrderEditPage.setFixHashCorrection();

			//place navigation data to navigation socket
			BX('sale-order-edit-block-fast-nav-socket').appendChild(
				BX('sale-order-edit-block-fast-nav')
			);
		});
	</script>
<?endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");