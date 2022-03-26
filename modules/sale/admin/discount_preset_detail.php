<?
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
Main\Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/panel/sale/preset.css");

$enableRestrictedGroupsMode = ($adminSidePanelHelper->isPublicSidePanel()
	&& Main\Loader::includeModule('crm')
	&& Main\Loader::includeModule('bitrix24')
);

$presetManager = \Bitrix\Sale\Discount\Preset\Manager::getInstance();
$presetManager->enableRestrictedGroupsMode($enableRestrictedGroupsMode);

if(!empty($_GET['DISCOUNT_ID']))
{
	$discountId = $_GET['DISCOUNT_ID'];
	$discountFields = Internals\DiscountTable::getById($discountId)->fetch();

	if(!$discountFields || empty($discountFields['PRESET_ID']))
	{
		return;
	}
	$preset = $presetManager->getPresetById($discountFields['PRESET_ID']);
	$preset->setDiscount($discountFields);
}
elseif(!empty($_GET['PRESET_ID']))
{
	$preset = $presetManager->getPresetById($_GET['PRESET_ID']);
}
else
{
	return;
}

if(!$preset)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_ERROR_NOT_FOUND_PRESET'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if(!empty($_GET['action']))
{
	$preset->executeAjaxAction($_GET['action']);
}

$preset->exec();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($preset->hasErrors())
{
	$errorsText = array();
	foreach($preset->getErrors() as $error)
	{
		$errorsText[] = $error->getMessage();
	}

	$message = new CAdminMessage(implode("<br>", $errorsText));
	echo $message->Show();
}
if(!empty($_GET['from_list']))
{
	$contextMenuItems = array();
	$contextListButtonParams = array();

	if($_GET['from_list'] === 'discount')
	{
		$discountUrl = "sale_discount.php?lang=".LANGUAGE_ID;
		$discountUrl = $adminSidePanelHelper->editUrlToPublicPage($discountUrl);
		$contextListButtonParams["TEXT"] = Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_DISCOUNT_LIST');
		$contextListButtonParams["LINK"] = $discountUrl;
		$contextListButtonParams["ICON"] = "btn_list";
		$contextMenuItems = array($contextListButtonParams);
	}
	elseif($_GET['from_list'] === 'preset')
	{
		$presetListUrl = $selfFolderUrl."sale_discount_preset_list.php?lang=".LANGUAGE_ID;
		$presetListUrl = $adminSidePanelHelper->editUrlToPublicPage($presetListUrl);
		$contextListButtonParams["TEXT"] = Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_PRESET_DISCOUNT_LIST');
		$contextListButtonParams["LINK"] = $presetListUrl;
		$contextListButtonParams["ICON"] = "btn_list";
		$contextMenuItems = array($contextListButtonParams);
	}
	elseif($_GET['from_list'] === 'coupon')
	{
		$couponListUrl = $selfFolderUrl."sale_discount_coupons.php?lang=".LANGUAGE_ID;
		$contextListButtonParams["TEXT"] = Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_COUPON_LIST');
		$contextListButtonParams["LINK"] = $couponListUrl;
		$contextListButtonParams["ICON"] = "btn_list";
		$contextMenuItems = array($contextListButtonParams);
	}
	$contextMenu = new CAdminContextMenu($contextMenuItems);
	$contextMenu->Show();
}
$APPLICATION->SetTitle($preset->getTitle());
?>
<div class="adm-white-container">
	<h2 class="adm-white-container-title"><?= htmlspecialcharsbx($preset->getTitle()) ?></h2>
	<div class="sale-discount-wrapper">
		<p style="margin-bottom: 30px;"><?= htmlspecialcharsbx($preset->getStepDescription()) ?></p>
		<div class="sale-discount-container-box" id="sale_discount_preset_section_box">
			<div class="sale-discount-title-container">
				<div class="sale-discount-title-num"><?= htmlspecialcharsbx($preset->getStepNumber()) ?></div>
				<div class="sale-discount-title-text"><?= htmlspecialcharsbx($preset->getStepTitle()) ?></div>
				<div class="clb"></div>
			</div>
			<div class="sale-discount-content-container">
				<?= $preset->getView(); ?>
			</div>
		</div>
	</div>
	<div style="margin-top: 20px">
		<? if($preset->hasPrevStep()){ ?>
		<a href="javascript:  BX('__run_prev_step').value = 'Y';BX.submit(document.forms['__preset_form'])" style="margin-right: 10px;" class="adm-btn adm-btn-grey"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_PREV_STEP') ?></a>
		<? } ?>
		<? if(!$preset->isLastStep()) {?>
		<a href="javascript: BX.submit(document.forms['__preset_form'])" class="adm-btn adm-btn-grey"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_NEXT_STEP') ?></a>
		<?
			} else {
			$listDiscountLink = $selfFolderUrl.'sale_discount.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_DISCOUNT_ID' => $preset::className(),
					'apply_filter' => 'Y'
				));
			$listDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($listDiscountLink);
		?>
		<a href="<?= $listDiscountLink ?>" class="adm-btn adm-btn-grey" target="_top"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_DETAIL_PRESET_DISCOUNT_GO_TO_LIST') ?></a>
		<? } ?>
	</div>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");