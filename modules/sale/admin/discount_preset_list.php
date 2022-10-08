<?php
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
Main\Loader::includeModule('sale');
Main\Loader::includeModule('ui');

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);
$APPLICATION->SetAdditionalCSS("/bitrix/panel/sale/preset.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$enableRestrictedGroupsMode = ($adminSidePanelHelper->isPublicSidePanel()
	&& Main\Loader::includeModule('crm')
	&& Main\Loader::includeModule('bitrix24')
);

$presetManager = \Bitrix\Sale\Discount\Preset\Manager::getInstance();
$presetManager->enableRestrictedGroupsMode($enableRestrictedGroupsMode);

$productsPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_PRODUCTS);
$deliveryPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_DELIVERY);
$paymentPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_PAYMENT);
$otherPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_OTHER);

$APPLICATION->SetTitle(Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_TITLE'));

if ($adminSidePanelHelper->getPublicPageProcessMode())
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.helper',
		'',
		[]
	);
}
?>
	<div class="sale-discount-list-wrapper">
		<?php if($productsPresets){ ?>
		<div class="sale-discount-list-container products open /*close*/">
			<!-- BLOCK TITLE container -->
			<div class="sale-discount-list-title-container">
				<div class="sale-discount-list-action"></div>
				<div class="sale-discount-list-title-icon"></div>
				<div class="sale-discount-list-title-line"></div>
				<h2 class="sale-discount-list-title"><?= $presetManager->getCategoryName($presetManager::CATEGORY_PRODUCTS) ?></h2>
			</div>
			<!--  -->
			<div class="sale-discount-list-content-container">
				<div class="sale-discount-list-content-container-blocks">

					<?php
			foreach($productsPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();

				$clickMode = false;
				$createDiscountLink = '';
				$targetHref = '';
				switch($preset->getAvailableState())
				{
					case Sale\Discount\Preset\BasePreset::AVAILABLE_STATE_ALLOW:
						$createDiscountLink = $selfFolderUrl . 'sale_discount_preset_detail.php?' . http_build_query([
							'from_list' => 'preset',
							'lang' => LANGUAGE_ID,
							'PRESET_ID' => $preset::className(),
						]);
						$createDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($createDiscountLink);
						$targetHref = $adminSidePanelHelper->isPublicFrame() ? 'target="_top"': "";
						break;
					case Sale\Discount\Preset\BasePreset::AVAILABLE_STATE_TARIFF:
						$helpLink = $preset->getAvailableHelpLink();
						if (!empty($helpLink))
						{
							$clickMode = $helpLink['TYPE'] === 'ONCLICK';
							$createDiscountLink = $helpLink['LINK'];
						}
						break;
				}
				$listDiscountLink = $selfFolderUrl."sale_discount.php?".http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_DISCOUNT_ID' => $preset::className(),
					'apply_filter' => 'Y'
				));
				$listDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($listDiscountLink);
			?>
			<!-- BLOCK CONTENT -->
					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<?php if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<?php } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<?php
								if ($createDiscountLink !== ''):
									if ($clickMode):
									?>
										<a href="#" onclick="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
									<?php
									else:
									?>
										<a <?=$targetHref?> href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
									<?php
									endif;
								endif;
								?>
								<?php if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>" target="_top"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<?php } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php if($deliveryPresets){ ?>
		<div class="sale-discount-list-container delivery open /*close*/">
			<!-- BLOCK TITLE container -->
			<div class="sale-discount-list-title-container">
				<div class="sale-discount-list-action"></div>
				<div class="sale-discount-list-title-icon"></div>
				<div class="sale-discount-list-title-line"></div>
				<h2 class="sale-discount-list-title"><?= $presetManager->getCategoryName($presetManager::CATEGORY_DELIVERY) ?></h2>
			</div>
			<!--  -->
			<div class="sale-discount-list-content-container">
				<div class="sale-discount-list-content-container-blocks">

					<?php
			foreach($deliveryPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = $selfFolderUrl.'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = $selfFolderUrl."sale_discount.php?".http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_DISCOUNT_ID' => $preset::className(),
					'apply_filter' => 'Y'
				));
				$listDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($listDiscountLink);
				$createDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($createDiscountLink);
				$targetHref = $adminSidePanelHelper->isPublicFrame() ? 'target="_top"': "";
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<?php if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<?php } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a <?=$targetHref?> href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<?php if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>" target="_top"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<?php } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php if($paymentPresets){ ?>
		<div class="sale-discount-list-container payment open /*close*/">
			<!-- BLOCK TITLE container -->
			<div class="sale-discount-list-title-container">
				<div class="sale-discount-list-action"></div>
				<div class="sale-discount-list-title-icon"></div>
				<div class="sale-discount-list-title-line"></div>
				<h2 class="sale-discount-list-title"><?= $presetManager->getCategoryName($presetManager::CATEGORY_PAYMENT) ?></h2>
			</div>
			<!--  -->
			<div class="sale-discount-list-content-container">
				<div class="sale-discount-list-content-container-blocks">

					<?php
			foreach($paymentPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = $selfFolderUrl.'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = $selfFolderUrl."sale_discount.php?".http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_DISCOUNT_ID' => $preset::className(),
					'apply_filter' => 'Y'
				));
				$listDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($listDiscountLink);
				$createDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($createDiscountLink);
				$targetHref = $adminSidePanelHelper->isPublicFrame() ? 'target="_top"': "";
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<?php if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<?php } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a <?=$targetHref?> href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<?php if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>" target="_top"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<?php } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php if($otherPresets){ ?>
		<div class="sale-discount-list-container others open /*close*/">
			<!-- BLOCK TITLE container -->
			<div class="sale-discount-list-title-container">
				<div class="sale-discount-list-action"></div>
				<div class="sale-discount-list-title-icon"></div>
				<div class="sale-discount-list-title-line"></div>
				<h2 class="sale-discount-list-title"><?= $presetManager->getCategoryName($presetManager::CATEGORY_OTHER) ?></h2>
			</div>
			<!--  -->
			<div class="sale-discount-list-content-container">
				<div class="sale-discount-list-content-container-blocks">

					<?php
			foreach($otherPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = $selfFolderUrl.'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = $selfFolderUrl."sale_discount.php?".http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_DISCOUNT_ID' => $preset::className(),
					'apply_filter' => 'Y'
				));
				$listDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($listDiscountLink);
				$createDiscountLink = $adminSidePanelHelper->editUrlToPublicPage($createDiscountLink);
				$targetHref = $adminSidePanelHelper->isPublicFrame() ? 'target="_top"': "";
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<?php if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<?php } ?>
									<?php if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<?php } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a <?=$targetHref?> href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<?php if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>" target="_top"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<?php } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
