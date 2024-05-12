<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

CJSCore::Init(array('lists'));
\Bitrix\Main\UI\Extension::load("ui.buttons");
?>

<div id="bx-lists-lcp-total-div">
	<?
		$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
		$pagetitleAlignRightContainer = "lists-align-right-container";
		if($isBitrix24Template)
		{
			$this->SetViewTarget("pagetitle", 100);
			$pagetitleAlignRightContainer = "";
		}
		elseif(!IsModuleInstalled("intranet"))
		{
			\Bitrix\Main\UI\Extension::load([
				'ui.design-tokens',
				'ui.fonts.opensans',
			]);

			$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
		}
	?>
	<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
		<? if(!$arResult['ALL_PROCESSES_INSTALL']): ?>
			<button class="ui-btn ui-btn-md ui-btn-success" id="bx-lists-lcp-install-processes"
					onclick="javascript:BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>']
							.installProcesses(this);" title="<?= GetMessage("LISTS_LCP_TEMPLATE_BUTTON_INSTALL") ?>">
				<?= GetMessage("LISTS_LCP_TEMPLATE_BUTTON_INSTALL") ?></button>
		<? endif; ?>
		<a class="ui-btn ui-btn-md ui-btn-light-border ui-btn-themes" title="<?= GetMessage("LISTS_LCP_TEMPLATE_TRANSITION_PROCESSES") ?>" id="bx-lists-lcp-transition-processes"
			href="<?= $arResult['LISTS_URL'] ?>">
			<?= GetMessage("LISTS_LCP_TEMPLATE_TRANSITION_PROCESSES") ?>
		</a>
	</div>
	<?
		if($isBitrix24Template)
		{
			$this->EndViewTarget();
		}
	?>

	<div class="bx-lists-lcp-description-page">
		<? if($arResult['ALL_PROCESSES_INSTALL']): ?>
			<?= Loc::getMessage('LISTS_LCP_TEMPLATE_DESCRIPTION_PAGE_ALL_INSTALL') ?>
		<? else: ?>
			<?= Loc::getMessage('LISTS_LCP_TEMPLATE_DESCRIPTION_PAGE') ?>
		<? endif; ?>
	</div>

	<? if(!empty($arResult['SYSTEM_PROCESSES'])): ?>
		<table class="bx-lists-lcp-table-processes">
			<caption><?= Loc::getMessage('LISTS_LCP_TEMPLATE_TITLE_SYSTEM_PROCESSES') ?></caption>
			<? foreach ($arResult['SYSTEM_PROCESSES'] as $process): ?>
				<? (isset($process['PICK_OUT'])) ? $pickOut = 'not allocated' : $pickOut = 'allocate' ?>
				<tr
					id="<?= $pickOut ?>"
					data-pick-out="<?= $pickOut ?>"
					data-file="<?= htmlspecialcharsbx($process['FILE_PATH']) ?>"
					onmousedown="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mousedown(this);"
					onmouseover="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mouseover(this);"
					onmouseout="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mouseout(this);"
				>
					<td>
						<div class="bx-lists-lcp-table-td-div">
							<p class="bx-lists-lcp-table-td-name"><?= htmlspecialcharsbx($process['NAME']) ?></p>
							<p class="bx-lists-lcp-table-td-description">
								<?= htmlspecialcharsbx($process['DESCRIPTION']) ?>
							</p>
						</div>
					</td>
				</tr>
			<? endforeach; ?>
		</table>
	<? endif; ?>
	<? if(!empty($arResult['USER_PROCESSES'])): ?>
		<table class="bx-lists-lcp-table-processes">
			<caption><?= Loc::getMessage('LISTS_LCP_TEMPLATE_TITLE_USER_PROCESSES') ?></caption>
			<? foreach ($arResult['USER_PROCESSES'] as $process): ?>
				<? (isset($process['PICK_OUT'])) ? $pickOut = 'not allocated' : $pickOut = 'allocate' ?>
				<tr
					id="<?= $pickOut ?>"
					data-pick-out="<?= $pickOut ?>"
					data-file="<?= htmlspecialcharsbx($process['FILE_PATH']) ?>"
					onmousedown="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mousedown(this);"
					onmouseover="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mouseover(this);"
					onmouseout="BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'].mouseout(this);"
					>
					<td>
						<div class="bx-lists-lcp-table-td-div">
							<p class="bx-lists-lcp-table-td-name"><?= htmlspecialcharsbx($process['NAME']) ?></p>
							<p class="bx-lists-lcp-table-td-description">
								<?= htmlspecialcharsbx($process['DESCRIPTION']) ?>
							</p>
						</div>
					</td>
				</tr>
			<? endforeach; ?>
		</table>
	<? endif; ?>

	<? if(count($arResult['USER_PROCESSES']) > 10): ?>
		<div class="bx-lists-lcp-button-block">
			<button class="ui-btn ui-btn-md ui-btn-success" id="bx-lists-lcp-install-processes"
					onclick="javascript:BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>']
							.installProcesses(this);" title="<?= GetMessage("LISTS_LCP_TEMPLATE_BUTTON_INSTALL") ?>">
				<?= GetMessage("LISTS_LCP_TEMPLATE_BUTTON_INSTALL") ?></button>
		</div>
	<? endif; ?>
</div>

<input type="hidden" id="bx-lists-lcp-site-id" value="<?= SITE_ID ?>">
<input type="hidden" id="bx-lists-lcp-lists-url" value="<?= $arResult['LISTS_URL'] ?>">

<script>
	BX(function () {
		BX.Lists['CatalogProcessesClass_<?= $arResult['RAND_STRING']?>'] = new BX.Lists.CatalogProcessesClass({
			randomString: '<?= $arResult['RAND_STRING'] ?>'
		});
	});

	BX.message({
		LISTS_LCP_TEMPLATE_STATUS_ACTION_SUCCESS: '<?= GetMessageJS('LISTS_LCP_TEMPLATE_STATUS_ACTION_SUCCESS') ?>',
		LISTS_LCP_TEMPLATE_STATUS_ACTION_ERROR: '<?= GetMessageJS('LISTS_LCP_TEMPLATE_STATUS_ACTION_ERROR') ?>',
		LISTS_LCP_TEMPLATE_PROCESS_INSTALLED: '<?= GetMessageJS('LISTS_LCP_TEMPLATE_PROCESS_INSTALLED') ?>'
	});
</script>