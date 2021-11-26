<?
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Order;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Exchange\Integration\Admin\Link,
	\Bitrix\Sale\Exchange\Integration\Admin\Registry,
	\Bitrix\Sale\Exchange\Integration\Admin\ModeType,
	\Bitrix\Sale\Helpers\Admin\Blocks\FactoryMode,
	\Bitrix\Sale\Helpers\Admin\Blocks\BlockType;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$moduleId = "sale";
global $USER;
Bitrix\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CUtil::InitJSCore();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/formrecovery.php");
Asset::getInstance()->addCss('/bitrix/themes/.default/sale.css');

/** @var null|\Bitrix\Sale\Order $saleOrder */
$saleOrder = null;
$request = Application::getInstance()->getContext()->getRequest();
$lang = Application::getInstance()->getContext()->getLanguage();
$siteId = Application::getInstance()->getContext()->getSite();
$orderId = intval($request->get('order_id'));
$paymentId = intval($request->get('payment_id'));
$new = $paymentId <= 0;
$tableId = "order_payment_edit_info";
$backUrl = $request->get('backurl');

$link = Link::getInstance();
$factory = FactoryMode::create($link->getType());

$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

/** @var Order $orderClass */
$orderClass = $registry->getOrderClassName();

if($orderId <= 0 || !($saleOrder = $orderClass::load($orderId)))
{
	$link
		->create()
		->fill()
		->setPageByType(Registry::SALE_ORDER)
		->redirect();
}

$allowedOrderStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$allowedOrderStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

$allowUpdate = $allowDelete = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesUpdate);
$allowView = in_array($saleOrder->getField("STATUS_ID"), $allowedOrderStatusesView);

$payment = null;
$errors = array();
$fields = array();

if ($paymentId > 0)
{
	$paymentCollection = $saleOrder->getPaymentCollection();
	$payment = $paymentCollection->getItemById($paymentId);

	if (!$payment)
	{
		$link
			->create()
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->redirect();
	}
}

$isUserResponsible = false;
$isAllowCompany = false;

if ($saleModulePermissions == 'P')
{
	$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID()
		|| ($payment && $payment->getField('RESPONSIBLE_ID') == $USER->GetID()))
	{
		$isUserResponsible = true;
	}

	if ((in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList)
		|| ($payment && in_array($payment->getField('COMPANY_ID'), $userCompanyList))))
	{
		$isAllowCompany = true;
	}

	if (!$isUserResponsible && !$isAllowCompany)
	{
		$link
			->create()
			->fill()
			->setPageByType(Registry::SALE_ORDER)
			->redirect();
	}
}

if ($request->get('delete') == 'Y' && check_bitrix_sessid())
{
	if(!$allowDelete)
	{
		$link
			->create()
			->fill()
			->setPageByType(Registry::SALE_ORDER_PAYMENT)
			->redirect();
	}

	if ($payment)
	{
		$delResult = $payment->delete();
		if (!$delResult->isSuccess())
		{
			$errors = $delResult->getErrorMessages();
		}
		else
		{
			$result = $saleOrder->save();
			if ($result->isSuccess())
			{
				if ($backUrl)
				{
					$link
						->create()
						->setRequestUri($backUrl)
						->redirect();
				}
				else
				{
					$link
						->create()
						->fill()
						->setPageByType(Registry::SALE_ORDER_PAYMENT)
						->redirect();
				}
			}
			else
			{
				$errors = $result->getErrorMessages();
			}
		}

	}
}

if ($request->isPost() && check_bitrix_sessid() && $request->get('update'))
{
	if(!$allowUpdate)
	{
		if (isset($_POST["apply"]))
		{
			$link
				->create()
				->setPageByType(Registry::SALE_ORDER_PAYMENT_EDIT)
				->setField('order_id', $orderId)
				->setField('payment_id', $paymentId)
				->setField('backurl', $backUrl)
				->redirect();
		}
		else
		{
			$link
				->create()
				->setPageByType(Registry::SALE_ORDER_PAYMENT)
				->redirect();
		}
	}

	/**
	 * @var $result \Bitrix\Main\Entity\Result;
	 */
	$result = $factory::create(BlockType::PAYMENT)->updateData($saleOrder, $request->get('PAYMENT'));
	$data = $result->getData();
	$payment = array_shift($data['PAYMENT']);

	if ($result->isSuccess())
	{
		$saveResult = $saleOrder->save();

		if ($saveResult->isSuccess())
		{
			$paymentId = $payment->getId();

			if ($request->getPost("apply") == '')
			{
				if ($backUrl)
				{
					$link
						->create()
						->setRequestUri($backUrl)
						->redirect();
				}
				else
				{
					$link
						->create()
						->fill()
						->setPageByType(Registry::SALE_ORDER_PAYMENT)
						->redirect();
				}
			}
			else
			{
				$link
					->create()
					->setPageByType(Registry::SALE_ORDER_PAYMENT_EDIT)
					->setField('order_id', $orderId)
					->setField('payment_id', $paymentId)
					->setQuery(urlencode($backUrl))
					->fill()
					->redirect();
			}
		}
		else
		{
			$errors = $saveResult->getErrorMessages();
		}
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}
else
{
	if ($paymentId == 0)
	{
		$payment = $saleOrder->getPaymentCollection()->createItem();
	}

	if (!$payment)
	{
		$link
			->create()
			->setPageByType(Registry::SALE_ORDER_PAYMENT)
			->fill()
			->redirect();
	}
}

if ((!$allowView && !$allowUpdate) || $orderClass::isLocked($orderId))
{
	$link
		->create()
		->fill()
		->setPageByType(Registry::SALE_ORDER_PAYMENT)
		->redirect();
}

$companyParams = array(
	'select' => array('ID', 'NAME')
);

if ($paymentId)
	$title = str_replace("#ID#", $paymentId, GetMessage("EDIT_ORDER_PAYMENT"));
else
	$title = GetMessage("NEW_ORDER_PAYMENT");
$APPLICATION->SetTitle($title);

if ($paymentId > 0)
{
	global $historyEntity;

	$historyEntity = array(
		'ENTITY' => 'PAYMENT',
		'ENTITY_ID' => $paymentId
	);
	$_GET['ID'] = $orderId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/order_history.php");
	$historyContent = ob_get_contents();
	ob_end_clean();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SOPE_PAYMENT_TRANSITION"),
	"TITLE"=> Loc::getMessage("SOPE_PAYMENT_TRANSITION_TITLE"),
	"LINK" => $link
		->create()
		->fill()
		->setPageByType(Registry::SALE_ORDER_VIEW)
		->setField('ID', $orderId)
		->build()
);

if (!$new)
{
	if ($allowDelete)
	{
		$href = $link
			->create()
			->setPageByType(Registry::SALE_ORDER_PAYMENT_EDIT)
			->setField('order_id', $orderId)
			->setField('payment_id', $paymentId)
			->setField('delete', 'Y')
			->setQuery(bitrix_sessid_get())
			->fill()
			->build();

		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SOPE_PAYMENT_DELETE"),
			"TITLE" => Loc::getMessage("SOPE_PAYMENT_DELETE_TITLE"),
			"LINK" => "javascript:void(0)",
			"ONCLICK" => "if(confirm('".Loc::getMessage('SOPE_PAYMENT_DELETE_MESSAGE')."')) window.location.href='".$href."'"
		);
	}
}

if($link->getType() == ModeType::APP_LAYOUT_TYPE)
{
	//do nothing
}
else
{
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SOPE_PAYMENT_LIST"),
		"TITLE"=> Loc::getMessage("SOPE_PAYMENT_LIST_TITLE"),
		"LINK" => $link
			->create()
			->setPageByType(Registry::SALE_ORDER_PAYMENT)
			->fill()
			->build()
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errors))
	CAdminMessage::ShowMessage(implode("<br>\n", $errors));

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => GetMessage("SOP_TAB_PAYMENT"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y")
);
if ($paymentId > 0)
{
	$aTabs[] = array("DIV" => "tab_history", "TAB" => GetMessage("SOP_TAB_HISTORY"), "TITLE" => GetMessage("SOP_TAB_HISTORY"));
	$aTabs[] = array("DIV" => "tab_analysis", "TAB" => GetMessage("SOP_TAB_ANALYSIS"), "TITLE" => GetMessage("SOP_TAB_ANALYSIS"));
}

$action = $link
	->create()
	->setPage($APPLICATION->GetCurPage())
	->setLang($lang)
	->setField('order_id', $orderId)
	->setField('paymentId', $paymentId)
	->setField('backurl', $backUrl)
	->setQuery($urlForm)
	->fill()
	->build();

?><form method="POST" action="<?=$action?>" name="<?=$tableId?>_form" id="<?=$tableId?>_form"><?

$tabControl = new CAdminTabControlDrag($tableId, $aTabs, $moduleId, false, true);
$tabControl->Begin();
//TAB order --
$tabControl->BeginNextTab();

//prepare blocks order
$defaultBlocksOrder = array(
	"financeinfo",
	"payment",
	"buyer",
	"additional"
);

$statusOnPaid = Bitrix\Main\Config\Option::get('sale', 'status_on_paid');
$statusOnAllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_allow_delivery');
$statusOnPaid2AllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_payed_2_allow_delivery');

if (empty($statusOnPaid) && (empty($statusOnAllowDelivery) || empty($statusOnPaid2AllowDelivery)))
	$defaultBlocksOrder[] = 'statusorder';

$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_ajaxer.js");
echo $factory::create(BlockType::ADDITIONAL)->getScripts();
echo $factory::create(BlockType::PAYMENT)->getScripts();
echo \Bitrix\Sale\Helpers\Admin\OrderEdit::getScripts($saleOrder, $tableId);
?>

<input type="hidden" name="update" value="Y">
<input type="hidden" name="lang" id="lang" value="<?=$lang;?>">
<input type="hidden" name="order_id" id="order_id" value="<?=$orderId;?>">
<?
	echo bitrix_sessid_post();
	$paymentCount = 1;
?>
<tr>
	<td>
		<div style="position: relative; vertical-align: top">
			<?$tabControl->DraggableBlocksStart();?>
			<?
				foreach ($blocksOrder as $blockCode)
				{
					$tabControl->DraggableBlockBegin(GetMessage("SALE_BLOCK_TITLE_".toUpper($blockCode)), $blockCode);

					if(BlockType::isDefined(BlockType::resolveId($blockCode)))
					{
						$block = $factory::create(BlockType::resolveId($blockCode));

						switch (BlockType::resolveId($blockCode))
						{
							case BlockType::FINANCE_INFO:
								echo $block::getView($saleOrder, $new);
								break;
							case BlockType::PAYMENT:
								$index = 1;
								echo $block::getEdit($payment, $index, $_POST['PAYMENT'][$index]);
								break;
							case BlockType::BUYER:
								echo $block::getView($saleOrder);
								break;
							case BlockType::ADDITIONAL:
								echo $block::getEdit($payment, $tableId."_form", 'PAYMENT[1]');
								break;
							case BlockType::STATUS:
								/** @var \Bitrix\Sale\Helpers\Admin\Blocks\OrderStatus $block */
								echo $block::getEditSimple($USER->GetID(), 'PAYMENT[1][ORDER_STATUS_ID]', $saleOrder->getField('STATUS_ID'));
								break;
						}
					}

					$tabControl->DraggableBlockEnd();
				}
			?>
		</div>
	</td>
</tr>

</form>
<?
//--TAB order
if ($paymentId > 0):
	//TAB history --
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td id="order-history"><?=$historyContent; ?></td>
	</tr>
<?
	//-- TAB history

	//TAB analysis --
	$tabControl->BeginNextTab();

	?>
	<tr>
		<td>
			<div style="position:relative; vertical-align:top">
				<?

				$orderBasket = $factory::create(BlockType::BASKET, [
					'order'=> $saleOrder,
					'jsObjName' => "BX.Sale.Admin.OrderBasketObj",
					'idPrefix' => "sale_order_basket",
					'createProductBasement' => true,
					'mode' => \Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket::VIEW_MODE
				]);
				echo $factory::create(BlockType::ANALYSIS)->getView($saleOrder, $orderBasket, true, $paymentId);
				?>
			</div>
		</td>
	</tr>
	<?

	//-- TAB analysis

endif;

$tabControl->Buttons(
	array(
		"disabled" => !$allowUpdate,
		"back_url" => $backUrl
	)
);

$tabControl->End();

?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>