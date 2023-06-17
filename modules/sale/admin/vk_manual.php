<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage("SALE_VK_MANUAL_TITLE"));
require_once($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

//ONLY RUSSIAN!!!
//todo: translate to other language
if (defined('LANG') && LANG != 'ru')
{
	echo BeginNote();
	echo '<p>' . Loc::getMessage("SALE_VK_MANUAL_ONLY_RUSSIAN") . '</p>';
	echo '<p>' . Loc::getMessage("SALE_VK_MANUAL_ONLY_RUSSIAN_2") . '</p>';
	echo '<img src="/bitrix/images/sale/vk/vk_only_russian.png" alt="">';
	echo EndNote();
}
else
{

//prepare TABS
	$arrTabs = array(
		array(
			"DIV" => "vk_manual_connection",
			"TAB" => Loc::getMessage("SALE_VK_MANUAL_TAB__CONNECTION"),
			"TITLE" => Loc::getMessage("SALE_VK_MANUAL_TAB__CONNECTION_DESC"),
		),
		array(
			"DIV" => "vk_manual_export",
			"TAB" => Loc::getMessage("SALE_VK_MANUAL_TAB__EXPORT"),
			"TITLE" => Loc::getMessage("SALE_VK_MANUAL_TAB__EXPORT_DESC"),
		),
		array(
			"DIV" => "vk_manual_products",
			"TAB" => Loc::getMessage("SALE_VK_MANUAL_TAB__PRODUCTS"),
			"TITLE" => Loc::getMessage("SALE_VK_MANUAL_TAB__PRODUCTS_DESC"),
		),
		array(
			"DIV" => "vk_manual_running",
			"TAB" => Loc::getMessage("SALE_VK_MANUAL_TAB__RUNNING"),
			"TITLE" => Loc::getMessage("SALE_VK_MANUAL_TAB__RUNNING_DESC"),
		),
		array(
			"DIV" => "vk_manual_features",
			"TAB" => Loc::getMessage("SALE_VK_MANUAL_TAB__FEATURES"),
			"TITLE" => Loc::getMessage("SALE_VK_MANUAL_TAB__FEATURES_DESC"),
		),
	);
	$tabControl = new CAdminTabControl("tabControl", $arrTabs);

	?>

	<?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>


	<!--		CONNECTION tab-->
	<tr>
		<td>
			<div style="max-width: 950px;">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_TITLE_1") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_1") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_2") ?> <?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_3") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_3a") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_TITLE_3") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_4", array(
						'#A1' => '<a href="https://vk.com/apps?act=manage">',
						'#A2' => '</a>',
					)); ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_5") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_6") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_1.png" alt="">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_TITLE_4") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_8") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_3.png" alt="">

				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_8a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_8b_") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_3a.png" alt="">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_TITLE_2_") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_7_") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_2.png" alt="">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_7a_") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_7b_") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_7c") ?></p>
				<br>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__CONNECTION_9_") ?></p>
			</div>
		</td>
	</tr>


	<!--		EXPORT tab-->
	<?php $tabControl->BeginNextTab(); ?>

	<tr>
		<td>
			<div style="max-width: 950px;">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_0") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_4.png" alt="">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_TITLE_4") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_10") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_TITLE_1") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_1") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_1a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_1b") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_TITLE_2") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_2") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_2a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_2b") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_2c") ?></p>

				<br>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__EXPORT_5") ?></p>
			</div>
		</td>
	</tr>


	<!--		SECTIONS tab-->
	<?php $tabControl->BeginNextTab(); ?>
	<tr>
		<td>
			<div style="max-width: 950px;">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_1") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_1a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_1b") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_5.png" alt="">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_TITLE_1") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_2") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_2a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_2b") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_TITLE_2") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_3") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_3a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_3b") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_3c") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_6.png" alt="">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_4") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_5") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_7") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_8") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_9") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_10") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_10a") ?></p>
				<br>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__PRODUCTS_11") ?></p>
			</div>
		</td>
	</tr>


	<!--		RUNNING tab-->
	<?php $tabControl->BeginNextTab(); ?>
	<tr>
		<td>
			<div style="max-width: 950px;">
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_0") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_3") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_4") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_5") ?></h3>
				<img src="/bitrix/images/sale/vk/vk_man_9.png" alt="">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_5a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_6") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__RUNNING_7") ?></p>
			</div>
		</td>
	</tr>


	<!--		FEATURES tab-->
	<?php $tabControl->BeginNextTab(); ?>
	<tr>
		<td>
			<div style="max-width: 950px;">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_1") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_2") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_3") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_4") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_5") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_6") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_7") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_8") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_9") ?></p>
				<h3><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_10") ?></h3>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_11") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_12") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_12a") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_13") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_10.png" alt="">
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_14") ?></p>
				<p><?= Loc::getMessage("SALE_VK_MANUAL__FEATURES_15") ?></p>
				<img src="/bitrix/images/sale/vk/vk_man_11.png" alt="">
			</div>
		</td>
	</tr>


	<?php
	$tabControl->End();
	?>

<? }    //end language change (only russian)?>

<? require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");