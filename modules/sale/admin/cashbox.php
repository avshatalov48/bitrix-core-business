<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

Loc::loadMessages(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");

$APPLICATION->SetTitle(Loc::getMessage('SALE_CASHBOX_PAGE_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
	<div class="adm-block-wrapper">
		<div class="adm-cashbox-container">
			<div class="adm-promo-title adm-promo-subtitle">
				<span class="adm-promo-title-item"><?=Loc::getMessage('SALE_CASHBOX_TITLE_FZ');?></span>
			</div>
			<div class="adm-promo-title adm-promo-main-title">
				<span class="adm-promo-title-item"><?=Loc::getMessage('SALE_CASHBOX_TITLE');?></span>
			</div>
		</div><!--adm-cashbox-container-->

		<div class="adm-cashbox-container">
			<div class="adm-cashbox-container-logo"></div>
		</div>

		<div class="adm-cashbox-container">
			<div class="adm-promo-title">
				<span class="adm-promo-title-item"><?=Loc::getMessage('SALE_CASHBOX_COMPLIES_FZ')?></span>
			</div>
			<div class="adm-cashbox-advantage">
				<span class="adm-cashbox-advantage-item advantage-item-1"><?=Loc::getMessage('SALE_CASHBOX_SOLUTION_ITEM_1')?></span>
				<span class="adm-cashbox-advantage-item advantage-item-2"><?=Loc::getMessage('SALE_CASHBOX_SOLUTION_ITEM_2')?></span>
				<span class="adm-cashbox-advantage-item advantage-item-3"><?=Loc::getMessage('SALE_CASHBOX_SOLUTION_ITEM_3')?></span>
			</div><!--adm-cashbox-advantage-->
			<div class="adm-cashbox-border"></div>
		</div><!--adm-cashbox-container-->

		<div class="adm-cashbox-container">
			<div class="adm-promo-title">
				<span class="adm-promo-title-item"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_TITLE')?></span>
			</div>
			<div class="adm-cashbox-desc"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION')?></div>
			<ul class="adm-cashbox-list adm-cashbox-inner">
				<li class="adm-cashbox-list-item cashbox-list-item-1"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_1')?></li>
				<li class="adm-cashbox-list-item cashbox-list-item-2"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_2')?></li>
				<li class="adm-cashbox-list-item cashbox-list-item-3"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_3')?></li>
				<li class="adm-cashbox-list-item cashbox-list-item-4"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_4')?></li>
				<li class="adm-cashbox-list-item cashbox-list-item-5">
					<a class="adm-cashbox-list-load-link" href="http://fs.atol.ru/SitePages/%D0%A6%D0%B5%D0%BD%D1%82%D1%80%20%D0%B7%D0%B0%D0%B3%D1%80%D1%83%D0%B7%D0%BA%D0%B8.aspx?raz1=%D0%9F%D1%80%D0%BE%D0%B3%D1%80%D0%B0%D0%BC%D0%BC%D0%BD%D0%BE%D0%B5+%D0%BE%D0%B1%D0%B5%D1%81%D0%BF%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B5&amp;raz2=%D0%94%D0%A2%D0%9E" target="_blank"><?=Loc::getMessage('SALE_CASHBOX_LOAD')?></a>
					<div class="adm-cashbox-list-item-showhint">
						<?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_5')?>
						<div class="adm-cashbox-list-item-hint">?</div>
						<div class="cashbox-list-help-block">
							<div class="cashbox-list-help-block-inner">
								<div class="sale-discount-list-block">
									<div class="cashbox-list-help-block-title"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_5_HELP_DRIVER_1')?></div>
									<div class="cashbox-list-help-block-info">
										<?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_5_HELP_DRIVER_1_DESC')?>
									</div>
									<br>
									<div class="cashbox-list-help-block-title"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_5_HELP_DRIVER_2')?></div>
									<div class="cashbox-list-help-block-info">
										<?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_5_HELP_DRIVER_2_DESC')?>
									</div>
								</div>
							</div>
						</div>
					</div>					
				</li>
				<li class="adm-cashbox-list-item cashbox-list-item-6">
					<a class="adm-cashbox-list-load-link" href="https://www.1c-bitrix.ru/download/1c-bitrix-kassi.php" target="_blank"><?=Loc::getMessage('SALE_CASHBOX_LOAD')?></a>
					<?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_6')?>
				</li>
				<li class="adm-cashbox-list-item cashbox-list-item-7"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_7')?></li>
				<li class="adm-cashbox-list-item cashbox-list-item-8"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_STEP_8')?></li>
			</ul>
			<div class="adm-cashbox-border"></div>
		</div><!--adm-cashbox-container-->

		<div class="adm-cashbox-container adm-plug-block adm-button-container">
			<span class="adm-button adm-button-blue adm-button-main" onclick="BX.Sale.Cashbox.connectToKKM(this);"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_TO_ESHOP')?></span>
		</div><!--adm-cashbox-container-->

		<div class="adm-cashbox-container adm-cashbox-container-connect-instruction" id="container-instruction">
			<div class="adm-promo-title">
				<span class="adm-promo-title-item"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_2_TITLE')?></span>
			</div>
			<div style="margin-bottom: 50px;">
				<ul class="adm-cashbox-list2 adm-cashbox-inner">
					<li class="adm-cashbox-list-item cashbox-list-item-1"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_APP_STEP_1')?><br> <b id="cashbox-url"></b></li>
					<li class="adm-cashbox-list-item cashbox-list-item-2"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_APP_STEP_2')?></li>
					<li class="adm-cashbox-list-item cashbox-list-item-3"><?=Loc::getMessage('SALE_CASHBOX_CONNECT_INSTRUCTION_APP_STEP_3')?></li>
				</ul>
			</div>
		</div>
	</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>