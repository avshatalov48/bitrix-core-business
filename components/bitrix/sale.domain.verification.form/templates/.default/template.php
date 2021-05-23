<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Extension::load(["ui.common", "ui.forms", "ui.alerts"]);

$APPLICATION->SetTitle(Loc::getMessage("SALE_DVF_TEMPLATE_TITLE"));

if ($arResult["ERRORS"])
{
	?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
		<span class="ui-alert-message"><?=implode("<br>", $arResult["ERRORS"])?></span>
	</div>
	<?php
}

if ($arResult["DOMAIN_GRID"]["VERIFIED_DOMAINS"])
{
?>
	<div class="ui-title-4"><?=Loc::getMessage("SALE_DVF_TEMPLATE_DOMAIN_LIST_TITLE")?></div>
	<div class="sale-domain-verification-grid-wrapper">
		<?php
		$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
			'GRID_ID' => $arResult["DOMAIN_GRID"]["ID"],
			'COLUMNS' => [
				[
					'id' => 'DOMAIN',
					'name' => Loc::getMessage("SALE_DVF_TEMPLATE_GRID_COLUMN_DOMAIN"),
					'sort' => 'DOMAIN',
					'default' => true,
					'resizeable' => false,
				],
				[
					'id' => 'PATH',
					'name' => Loc::getMessage("SALE_DVF_TEMPLATE_GRID_COLUMN_PATH"),
					'sort' => 'PATH',
					'default' => true,
					'resizeable' => false,
				],
			],
			'ROWS' => $arResult["DOMAIN_GRID"]["VERIFIED_DOMAINS"],
			'NAV_OBJECT' => $arResult["DOMAIN_GRID"]["NAV_OBJECT"],
			'TOTAL_ROWS_COUNT' => $arResult["DOMAIN_GRID"]["TOTAL_ROWS_COUNT"],
			'AJAX_MODE' => 'Y',
			'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
			'PAGE_SIZES' => [
				['NAME' => "5", 'VALUE' => '5'],
				['NAME' => '10', 'VALUE' => '10'],
				['NAME' => '20', 'VALUE' => '20'],
				['NAME' => '50', 'VALUE' => '50'],
				['NAME' => '100', 'VALUE' => '100']
			],
			'DEFAULT_PAGE_SIZE' => 5,
			'AJAX_OPTION_JUMP' => 'N',
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_CHECK_ALL_CHECKBOXES' => false,
			'SHOW_ROW_ACTIONS_MENU' => true,
			'SHOW_GRID_SETTINGS_MENU' => false,
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGINATION' => true,
			'SHOW_SELECTED_COUNTER' => false,
			'SHOW_TOTAL_COUNTER' => true,
			'SHOW_PAGESIZE' => true,
			'SHOW_ACTION_PANEL' => false,
			'ACTION_PANEL' => [],
			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => false,
			'ALLOW_HORIZONTAL_SCROLL' => true,
			'ALLOW_SORT' => true,
			'ALLOW_PIN_HEADER' => false,
			'AJAX_OPTION_HISTORY' => 'N'
		]);
		?>
	</div>
	<?php
}
?>
<div class="ui-title-4"><?=Loc::getMessage("SALE_DVF_TEMPLATE_DOMAIN_FORM_TITLE")?></div>
<?php
if ($arResult["SITE_LIST"])
{
	?>
	<form method="post" id="domain-verification-form" enctype="multipart/form-data" class="sale-domain-verification-wrapper">
		<input type="hidden" name="save" value="y">
		<input type="hidden" name="entity" value="<?=$arParams["ENTITY"]?>">
		<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-inline">
			<div class="ui-ctl-after ui-ctl-icon-angle"></div>
			<select class="ui-ctl-element" name="domain_validation">
			<?php
			foreach ($arResult["SITE_LIST"] as $site)
			{
				$domainName = "($site[ID]) $site[NAME]";
				if ($site["SERVER_NAME"])
				{
					$domainName .= "[$site[SERVER_NAME]]";
				}
				?>
				<option value="<?=$site["SERVER_NAME"]?>"><?=$domainName?></option>
				<?php
			}
			?>
			</select>
		</div>

		<label class="ui-ctl ui-ctl-file-btn">
			<input type="file" class="ui-ctl-element" name="file_validation">
			<div class="ui-ctl-label-text"><?=Loc::getMessage("SALE_DVF_TEMPLATE_FILE_BUTTON_TITLE")?></div>
		</label>
		<?php
		$buttons = [
			'save',
			'close' => $APPLICATION->GetCurPageParam(),
		];
		?>
	</form>
	<?php
}
else
{
	?>
	<div class="sale-domain-verification-wrapper">
	<?php
		ShowError(Loc::getMessage("SALE_DVF_TEMPLATE_DOMAIN_NOT_FOUND_ERROR"));
	?>
	</div>
	<?php
	$buttons = [
		'close' => $APPLICATION->GetCurPageParam(),
	];
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	"",
	array(
		'BUTTONS' => $buttons,
		'ALIGN' => "center"
	),
	$this->getComponent()
);
?>
<script>
	BX.Sale.DomainVerificationForm.init({
		saveButtonId: "ui-button-panel-save",
		closeButtonId: "ui-button-panel-close",
		formId: "domain-verification-form",
		signedParameters: <?=CUtil::PhpToJSObject($this->getComponent()->getSignedParameters())?>,
	});
</script>
