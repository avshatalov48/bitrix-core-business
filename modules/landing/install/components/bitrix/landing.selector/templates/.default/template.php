<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

/** @var array $arParams */
/** @var array $arResult */
/** @var \Bitrix\UI\EntitySelector\Item $folder */
/** @var \Bitrix\UI\EntitySelector\Item $landing */

\Bitrix\Main\UI\Extension::load([
	'ui.entity-selector',
	'ui.hint',
	'ui.fonts.opensans',
]);

$currentLanding = $arResult['LANDINGS'][$arParams['LANDING_ID']] ?? null;
?>
<label class="landing-selector-container" id="landing-selector">
	<div <?
		?>data-hint="<?=Loc::getMessage('LANDING_SELECTOR_HINT_SEARCH_PAGE')?>" <?
		?>data-hint-no-icon class="landing-selector-result-picture" <?
		?>id="landing-selector-picture" <?
		?>style="background-image: url(<?= $currentLanding ? \htmlspecialcharsbx($currentLanding->getAvatar()) : '/bitrix/images/landing/nopreview.jpg'?>);"></div>
	<input class="landing-selector-input-text" id="landing-selector-input" value="<?= $arParams['INPUT_VALUE']?>" />
</label>

<script>
	BX.message(<?= \CUtil::phpToJSObject(Loc::loadLanguageFile(__FILE__))?>);
	BX.ready(function()
	{
		BX.UI.Hint.init(BX('landing-selector'));

		new BX.Landing.Component.Selector({
			node: BX('landing-selector'),
			input: BX('landing-selector-input'),
			siteType: '<?= \CUtil::jsEscape($arParams['TYPE'])?>',
			siteId: <?= $arParams['SITE_ID']?>,
			folderId: <?= $arParams['FOLDER_ID']?>,
			landingId: <?= $arParams['LANDING_ID']?>,
			urlLandingAdd: '<?= $arParams['PAGE_URL_LANDING_ADD']?>',
			urlFolderAdd: '<?= $arParams['PAGE_URL_FOLDER_ADD']?>',
			urlFormAdd: '<?= $arParams['PAGE_URL_FORM_ADD']?>',
			items: [
				<?foreach ($arResult['FOLDERS'] as $folder):?>
				{
					id: <?= $folder->getId()?>,
					entityId: '<?= $folder->getEntityId()?>',
					entityType: 'folder',
					title: '<?= \CUtil::jsEscape($folder->getTitle())?>',
					avatar: '<?=$this->GetFolder()?>/images/icon-folder.svg',
					supertitle: '<?= Loc::getMessage('LANDING_SELECTOR_TYPE_FOLDER')?>',
					tabs: 'recents',
					searchable: false,
					nodeOptions: { dynamic: true },
				},
				<?endforeach;?>
				<?foreach ($arResult['LANDINGS'] as $landing):?>
				{
					id: <?= $landing->getId()?>,
					selected: <?= ((int)$landing->getId()  === $arParams['LANDING_ID']) ? 'true' : 'false'?>,
					entityId: '<?= $landing->getEntityId()?>',
					entityType: 'landing',
					title: '<?= \CUtil::jsEscape($landing->getTitle())?>',
					avatar: '<?= \CUtil::jsEscape($landing->getAvatar())?>',
					supertitle: '<?= $arParams['PAGE_URL_FORM_ADD'] ? Loc::getMessage('LANDING_SELECTOR_TYPE_FORM') : Loc::getMessage('LANDING_SELECTOR_TYPE_PAGE')?>',
					tabs: 'recents'
				},
				<?endforeach;?>
			],
			onSelect: function(event)
			{
				if (typeof event.getData().item !== 'undefined')
				{
					let item = event.getData().item;
					let dialog = item.getDialog();
					if (typeof dialog.getEntities()[0] !== 'undefined')
					{
						let options = item.getDialog().getEntities()[0].getOptions();
						let href = '<?= \CUtil::jsEscape($arParams['PAGE_URL_LANDING_VIEW'])?>';
						href = href.replace('#site_show#', options['siteId']);
						href = href.replace('#landing_edit#', item.getId());
						window.location.href = href;
					}
				}
			}
		});
	});
</script>
