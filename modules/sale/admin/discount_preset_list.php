<?
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
Main\Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/panel/sale/preset.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$presetManager = \Bitrix\Sale\Discount\Preset\Manager::getInstance();
$productsPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_PRODUCTS);
$deliveryPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_DELIVERY);
$paymentPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_PAYMENT);
$otherPresets = $presetManager->getPresetsByCategory($presetManager::CATEGORY_OTHER);

$APPLICATION->SetTitle(Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_TITLE'));
?>
	<div class="sale-discount-list-wrapper">
		<? if($productsPresets){ ?>
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

			<?
			foreach($productsPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = 'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = 'sale_discount.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'filter_preset_id' => $preset::className(),
				));
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<? if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<? } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<? if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<? } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<? } ?>
				</div>
			</div>
		</div>
		<? } ?>
		<? if($deliveryPresets){ ?>
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

			<?
			foreach($deliveryPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = 'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = 'sale_discount.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'filter_preset_id' => $preset::className(),
				));
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<? if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<? } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<? if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<? } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<? } ?>
				</div>
			</div>
		</div>
		<? } ?>
		<? if($paymentPresets){ ?>
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

			<?
			foreach($paymentPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = 'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = 'sale_discount.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'filter_preset_id' => $preset::className(),
				));
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<? if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<? } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<? if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<? } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<? } ?>
				</div>
			</div>
		</div>
		<? } ?>
		<? if($otherPresets){ ?>
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

			<?
			foreach($otherPresets as $preset)
			{
				$extendedDescription = $preset->getExtendedDescription();
				$createDiscountLink = 'sale_discount_preset_detail.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'PRESET_ID' => $preset::className(),
				));
				$listDiscountLink = 'sale_discount.php?' . http_build_query(array(
					'from_list'=> 'preset',
					'lang' => LANGUAGE_ID,
					'filter_preset_id' => $preset::className(),
				));
			?>
			<!-- BLOCK CONTENT -->

					<div class="sale-discount-list-responsive-block">
						<div class="sale-discount-list-block">
							<div class="sale-discount-list-block-title"><?= $preset->getTitle() ?></div>
							<div class="sale-discount-list-block-info">
								<dl>
									<? if($extendedDescription['DISCOUNT_TYPE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_TYPE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_TYPE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_VALUE']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_VALUE') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_VALUE'] ?></dd>
									<? } ?>
									<? if($extendedDescription['DISCOUNT_CONDITION']){ ?>
									<dt><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_TITLE_CONDITION') ?>:</dt><dd><?= $extendedDescription['DISCOUNT_CONDITION'] ?></dd>
									<? } ?>
								</dl>
							</div>
							<div class="sale-discount-list-block-btn">
								<a href="<?= $createDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_CREATE_BY_PRESET') ?></a>
								<? if($presetManager->hasCreatedDiscounts($preset)){ ?>
								<a href="<?= $listDiscountLink ?>"><?= Loc::getMessage('SALE_DISCOUNT_PRESET_LIST_ITEM_LIST_BY_PRESET') ?></a>
								<? } ?>
							</div>
						</div>
					</div>

			<!--  -->
			<? } ?>
				</div>
			</div>
		</div>
		<? } ?>
	</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");