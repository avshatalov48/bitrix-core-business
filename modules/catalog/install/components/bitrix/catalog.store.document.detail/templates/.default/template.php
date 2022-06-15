<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\UI\Toolbar\Facade\Toolbar;

global $APPLICATION;

if ($arResult['DOCUMENT']['TITLE'])
{
	$APPLICATION->SetTitle($arResult['DOCUMENT']['TITLE']);
}
elseif (!$arResult['DOCUMENT'] && empty($arResult['ERROR_MESSAGES']))
{
	$APPLICATION->SetTitle(Loc::getMessage('DOC_TYPE_CREATION_PAGE_TITLE_' . $arResult['DOCUMENT_TYPE']));
}

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-background');

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;

Extension::load([
	'catalog.document-card',
	'catalog.entity-card',
	'ui.entity-selector',
	'catalog.document-model',
]);

Toolbar::deleteFavoriteStar();

if (isset($arResult['TOOLBAR_ID']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.toolbar',
		(SITE_TEMPLATE_ID === 'bitrix24' ? 'slider' : 'type2'),
		[
			'TOOLBAR_ID' => $arResult['TOOLBAR_ID'],
			'BUTTONS' => $arResult['BUTTONS'] ?? []
		],
		$component,
		['HIDE_ICONS' => 'Y']
	);
}

if ((int)$arResult['DOCUMENT']['ID'] > 0)
{
	$labelColorClass = 'ui-label-light';
	$isDocumentCancelled = $arResult['DOCUMENT']['WAS_CANCELLED'] === 'Y' && $arResult['DOCUMENT']['STATUS'] === 'N';
	if ($isDocumentCancelled)
	{
		$labelColorClass = 'ui-label-lightorange';
	}
	elseif ($arResult['DOCUMENT']['STATUS'] === 'Y')
	{
		$labelColorClass = 'ui-label-lightgreen';
	}

	if ($isDocumentCancelled)
	{
		$labelText = Loc::getMessage('DOCUMENT_STATUS_CANCELLED');
	}
	else
	{
		$labelText = Loc::getMessage('DOCUMENT_STATUS_' . $arResult['DOCUMENT']['STATUS']);
	}

	$this->SetViewTarget('in_pagetitle');
	?>
<div class="catalog-title-buttons-wrapper">
	<span id="pagetitle_btn_wrapper" class="pagetitile-button-container">
		<?php if (!$arResult['IS_MAIN_CARD_READ_ONLY']): ?>
			<span id="pagetitle_edit" class="pagetitle-edit-button"></span>
		<?php endif; ?>
		<span id="page_url_copy_btn" class="page-link-btn"></span>
	</span>
	<span class="ui-label ui-label-lg document-status-label ui-label-fill <?= $labelColorClass ?>">
		<span class="ui-label-inner">
			<?= $labelText ?>
		</span>
	</span>
</div>
<div class="catalog-title-document-type">
	<?= Loc::getMessage('DOC_TYPE_SHORT_' . $arResult['DOCUMENT_TYPE']) ?>
</div>
	<?php
	$this->EndViewTarget();
}
elseif (!empty($arResult['DROPDOWN_TYPES']))
{
	$this->SetViewTarget('in_pagetitle');
	?>
	<div id="catalog-document-type-selector" class="catalog-document-type-selector">
		<span class="catalog-document-type-selector-text" data-hint="" data-hint-no-icon><?= Loc::getMessage('DOCUMENT_TYPE_DROPDOWN', ['#TYPE#' => Loc::getMessage('DOC_TYPE_SHORT_' . $arResult['DOCUMENT_TYPE'])]) ?></span>
	</div>
	<?php
	$this->EndViewTarget();
}

$tabs = [
	[
		'id' => 'main',
		'name' => Loc::getMessage('TAB_GENERAL_TITLE'),
		'enabled' => true,
		'active' => true,
	],
	[
		'id' => 'tab_products',
		'name' => Loc::getMessage('TAB_PRODUCT_TITLE'),
		'enabled' => true,
		'active' => false,
	],
];

$guid = $arResult['GUID'];
$containerId = "{$guid}_CONTAINER";
$tabMenuContainerId = "{$guid}_TABS_MENU";
$tabContainerId = "{$guid}_TABS";

$tabContainerClassName = 'catalog-entity-section catalog-entity-section-tabs';
$tabContainerClassName .= ' ui-entity-stream-section-planned-above-overlay';
?>

<script>
	BX.Catalog.DocumentCard.DocumentCard.initializeEntityEditorFactories();
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="catalog-entity-wrap catalog-wrapper">
	<div class="<?=$tabContainerClassName?>">
		<ul id="<?=htmlspecialcharsbx($tabMenuContainerId)?>" class="catalog-entity-section-tabs-container">
			<?php
			foreach ($tabs as $tab)
			{
				$classNames = ['catalog-entity-section-tab'];

				if (isset($tab['active']) && $tab['active'])
				{
					$classNames[] = 'catalog-entity-section-tab-current';
				}
				elseif (isset($tab['enabled']) && !$tab['enabled'])
				{
					$classNames[] = 'catalog-entity-section-tab-disabled';
				}
				?>
				<li data-tab-id="<?=htmlspecialcharsbx($tab['id'])?>" class="<?=implode(' ', $classNames)?>">
					<a class="catalog-entity-section-tab-link" href="#"><?=htmlspecialcharsbx($tab['name'])?></a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<div id="<?=htmlspecialcharsbx($tabContainerId)?>" style="position: relative;">
		<?php
		foreach ($tabs as $tab)
		{
			$tabId = $tab['id'];
			$className = 'catalog-entity-section catalog-entity-section-info';
			$style = '';

			if ($tab['active'] !== true)
			{
				$className .= ' catalog-entity-section-tab-content-hide catalog-entity-section-above-overlay';
				$style = 'style="display: none;"';
			}
			?>
			<div data-tab-id="<?=htmlspecialcharsbx($tabId)?>" class="<?=$className?>" <?=$style?>>
				<?php
				$tabFolderPath = Application::getDocumentRoot().$templateFolder.'/tabs/';
				$file = new File($tabFolderPath.$tabId.'.php');

				if ($file->isExists())
				{
					include $file->getPath();
				}
				else
				{
					echo "Unknown tab {{$tabId}}.";
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>

<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);

	BX.Catalog.DocumentCard.Instance = new BX.Catalog.DocumentCard.DocumentCard(
		'<?=CUtil::JSEscape($guid)?>',
		{
			entityId: '<?=CUtil::JSEscape($arResult['DOCUMENT']['ID'])?>',
			documentType: '<?=CUtil::JSEscape($arResult['DOCUMENT_TYPE'])?>',
			documentStatus: '<?= CUtil::JSEscape($arResult['DOCUMENT']['STATUS'] ?? 'N') ?>',
			tabs: <?=CUtil::PhpToJSObject($tabs)?>,
			documentTypeSelector: document.getElementById('catalog-document-type-selector'),
			documentTypeSelectorTypes: <?= CUtil::PhpToJSObject($arResult['DROPDOWN_TYPES']) ?>,
			containerId: '<?=CUtil::JSEscape($containerId)?>',
			tabContainerId: '<?=CUtil::JSEscape($tabContainerId)?>',
			tabMenuContainerId: '<?=CUtil::JSEscape($tabMenuContainerId)?>',
			copyLinkButtonId: 'page_url_copy_btn',
			componentName: <?=CUtil::PhpToJSObject($this->getComponent()->getName()) ?>,
			signedParameters: <?=CUtil::PhpToJSObject($this->getComponent()->getSignedParameters()) ?>,
			isConductLocked: <?= CUtil::PhpToJSObject($arResult['IS_CONDUCT_LOCKED']) ?>,
			masterSliderUrl: <?= CUtil::PhpToJSObject($arResult['MASTER_SLIDER_URL']) ?>,
		}
	);

	BX.ready(function () {
		BX.Catalog.DocumentCard.Instance.adjustToolPanel();
		<?if (isset($arResult['TOOLBAR_ID'])):?>
			BX.Catalog.DocumentCard.FeedbackButton.render(
				document.getElementById('<?=CUtil::JSEscape($arResult['TOOLBAR_ID'])?>'),
				<?=CUtil::JSEscape(((int)$arResult['DOCUMENT']['ID'] <= 0))?>
			);
		<?endif;?>
	});
</script>
