<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\UI\Extension;

/** @noinspection PhpUndefinedClassInspection */
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleCrmSiteMaster $component
 * @var string $templateFolder
 */

Loc::loadMessages(__FILE__);
$messages = Loc::loadLanguageFile(__FILE__);

if ($arResult["ERROR"]["ORDER"])
{
	Extension::load(array("ui.buttons", "ui.forms", "ui.progressbar", "ui.fonts.opensans", "ui.alerts"));
	?>
	<div class="adm-crm-site-master-grid">
		<div class="adm-crm-site-master-title"><?=Loc::getMessage("SALE_CSM_TEMPLATE_ORDER_CONVERTER_ERROR_TITLE")?></div>
		<div class="adm-crm-site-master-content">
			<div class="adm-crm-site-master-warning">
				<img class="adm-crm-site-master-warning-image" src="<?=$component->getPath()?>/wizard/images/warning.svg" alt="">
			</div>
			<div class="ui-alert ui-alert-warning ui-alert-text-center ui-alert-inline ui-alert-icon-warning">
				<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_TEMPLATE_ORDER_CONVERTER_ERROR_INFO")?></span>
			</div>
			<div class="ui-alert ui-alert-danger ui-alert-text-center ui-alert-inline ui-alert-icon-danger">
				<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_TEMPLATE_ORDER_CONVERTER_ERROR_DESCR")?></span>
			</div>
			<div class="adm-crm-site-master-grid-content">
				<?php
				$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
					'GRID_ID' => 'order_error_list',
					'COLUMNS' => [
						[
							'id' => 'ORDER_ID',
							'name' => Loc::getMessage("SALE_CSM_TEMPLATE_GRID_COLUMN_ID"),
							'sort' => 'ORDER_ID',
							'default' => true,
							'resizeable' => false,
						],
						[
							'id' => 'ERROR',
							'name' => Loc::getMessage("SALE_CSM_TEMPLATE_GRID_COLUMN_ERROR"),
							'sort' => 'ERROR',
							'default' => true,
							'resizeable' => false,
						],
					],
					'ROWS' => $arResult["ERROR"]["ORDER"],
					'NAV_OBJECT' => $arResult["GRID"]["NAV_OBJECT"],
					'TOTAL_ROWS_COUNT' => $arResult["GRID"]["TOTAL_ROWS_COUNT"],
					'AJAX_MODE' => 'Y',
					'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
					'PAGE_SIZES' => [
						['NAME' => "5", 'VALUE' => '5'],
						['NAME' => '10', 'VALUE' => '10'],
						['NAME' => '20', 'VALUE' => '20'],
						['NAME' => '50', 'VALUE' => '50'],
						['NAME' => '100', 'VALUE' => '100']
					],
					'AJAX_OPTION_JUMP'          => 'N',
					'SHOW_ROW_CHECKBOXES'       => false,
					'SHOW_CHECK_ALL_CHECKBOXES' => false,
					'SHOW_ROW_ACTIONS_MENU'     => false,
					'SHOW_GRID_SETTINGS_MENU'   => false,
					'SHOW_NAVIGATION_PANEL'     => true,
					'SHOW_PAGINATION'           => true,
					'SHOW_SELECTED_COUNTER'     => false,
					'SHOW_TOTAL_COUNTER'        => true,
					'SHOW_PAGESIZE'             => true,
					'SHOW_ACTION_PANEL'         => false,
					'ACTION_PANEL'              => [],
					'ALLOW_COLUMNS_SORT'        => true,
					'ALLOW_COLUMNS_RESIZE'      => false,
					'ALLOW_HORIZONTAL_SCROLL'   => true,
					'ALLOW_SORT'                => true,
					'ALLOW_PIN_HEADER'          => false,
					'AJAX_OPTION_HISTORY'       => 'N'
				]);
				?>
			</div>
			<div class="ui-btn-container ui-btn-container-center">
				<form name="update-order" method="get">
					<button type="submit" class="ui-btn ui-btn-primary" name="update-order" value="y">
						<?=Loc::getMessage("SALE_CSM_TEMPLATE_BUTTON_UPDATE_ORDER")?>
					</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}
else
{
	echo $arResult["CONTENT"];
	?>
	<script>
		BX.message(<?=CUtil::PhpToJSObject($messages)?>);
		BX.Sale.CrmSiteMasterComponent.init({
			wizardSteps: <?=CUtil::PhpToJSObject($arResult["WIZARD_STEPS"])?>,
			formId: '<?=$component->getWizard()->GetFormName()?>',
			documentRoot: '<?=htmlspecialcharsbx(CUtil::addslashes($_SERVER["DOCUMENT_ROOT"]))?>',
			siteNameId: "CRM_SITE",
			docRootId: "DOC_ROOT",
			docRootLinkId: "DOC_ROOT_LINK",
			createSiteId: "create_site",
			keyInputBlockId: "check_key",
			keyId: "id_key",
			keyButtonId: "id_key_btn",
			confirmationCheckboxId: "confirmation_done",
			currentStepId: '<?=htmlspecialcharsbx(CUtil::addslashes($component->getWizard()->GetCurrentStepID()))?>',
			nextButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetNextButtonID())?>',
			prevButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetPrevButtonID())?>',
			cancelButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetCancelButtonID())?>',
			finishButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetFinishButtonID())?>'
		});
	</script>
	<?php
}
