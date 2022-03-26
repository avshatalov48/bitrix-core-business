<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var array $arParams */

if ($arResult['ERRORS'])
{
	?>
	<div class="ui-alert ui-alert-danger"><span class="ui-alert-message"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
		?></span></div><?php
}
if ($arResult['FATAL'])
{
	return;
}

Loc::loadMessages(__FILE__);

Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));

\Bitrix\Main\UI\Extension::load([
	'landing_master',
	'sidepanel',
	'ui.forms',
	'ui.alerts',
	'ui.switcher'
]);

$row = $arResult['FOLDER'];
?>

<script type="text/javascript">
	BX.ready(function(){
		<?if ($arParams['SUCCESS_SAVE'] && !$arResult['ERRORS']):?>
		top.BX.onCustomEvent('BX.Landing.Filter:apply');
		if (typeof top.BX.SidePanel !== 'undefined')
		{
			setTimeout(function() {
				top.BX.SidePanel.Instance.close();
			}, 300);
		}
		<?endif;?>
	});
</script>
<div class="landing-folder-edit__workarea landing-folder-edit__scope">
	<form action="<?= POST_FORM_ACTION_URI?>" method="post">
		<?= bitrix_sessid_post()?>
		<input type="hidden" name="fields[SAVE_FORM]" value="Y" />

		<div id="landing-folder-edit__editable-title" class="landing-folder-edit__section --without-bg">
			<div class="landing-folder-edit__section--text-title" style="white-space: nowrap;" data-landing-edit-text><?= $row['TITLE']['CURRENT'] ?></div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w50" style="display: none !important;" data-landing-edit-input>
				<input type="text" name="fields[TITLE]" class="ui-ctl-element" value="<?= $row['TITLE']['CURRENT'] ?>" />
			</div>
			<div class="landing-folder-edit__section--icon --edit" for="landing-folder-edit__editable-title" data-landing-edit-control>
				<i></i>
			</div>
		</div>

		<div class="landing-folder-edit__section --inline">
			<div class="landing-folder-edit__section--title"><?= Loc::getMessage('LANDING_TPL_FIELD_CODE')?></div>
			<div id="landing-folder-edit__editable-path" class="landing-folder-edit__section--content --inline --path-link">
				<div class="landing-folder-edit__section--text-link"><?= rtrim($arResult['SITE_PATH'], '/') . htmlspecialcharsbx($arResult['FOLDER_PATH'])?></div>
				<div class="landing-folder-edit__section--text --space-around" data-landing-edit-text><?= $row['CODE']['CURRENT'] ?></div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-inline landing-folder-edit__section-ui-input" style="display: none !important;" data-landing-edit-input>
					<input type="text" name="fields[CODE]" class="ui-ctl-element" value="<?= $row['CODE']['CURRENT'] ?>" />
				</div>
				<div class="landing-folder-edit__section--icon --edit" for="landing-folder-edit__editable-path" data-landing-edit-control>
					<i></i>
				</div>
			</div>
		</div>

		<div class="landing-folder-edit__section">
			<div class="landing-folder-edit__section--title"><?= Loc::getMessage('LANDING_TPL_FIELD_INDEX_ID')?></div>
			<div class="landing-folder-edit__section--content --inline --padding">
				<?if ($arResult['FOLDER_EMPTY']):?>
					<div class="landing-folder-edit__section--text-link" style="margin-right: 5px"><?= Loc::getMessage('LANDING_TPL_FOLDER_IS_EMPTY')?></div>
					<a id="landing-folder-index-create" href="#" class="landing-folder-edit__section--text --link"><?= Loc::getMessage('LANDING_TPL_FOLDER_ADD_PAGE')?></a>
				<?else:?>
					<div class="landing-folder-edit__section--wrapper">
						<?if ($arResult['INDEX_LANDING']):?>
							<a id="landing-folder-index-link" class="landing-folder-edit__section--text --link --link-icon" href="<?= str_replace('#landing_edit#', $arResult['INDEX_LANDING']['ID'], $arParams['PAGE_URL_LANDING_VIEW'])?>" target="_top"><?= htmlspecialcharsbx($arResult['INDEX_LANDING']['TITLE'])?></a>
						<?else:?>
							<a id="landing-folder-index-link" class="landing-folder-edit__section--text --link zz" href="#" target="_top"></a>
						<?endif;?>

						<span id="landing-folder-select-index" class="landing-folder-edit__section--text --link"><?= Loc::getMessage('LANDING_TPL_FOLDER_SELECT_PAGE')?></span>

						<input type="hidden" name="fields[INDEX_ID]" id="landing-folder-index" value="<?= $arResult['INDEX_LANDING']['ID'] ?? $row['INDEX_ID']['CURRENT']?>" />

						<div id="landing-folder-metaog-group" style="display: <?= ($arResult['INDEX_LANDING']['ID'] || $row['INDEX_ID']['CURRENT']) ? 'block' : 'none'?>; padding-top: 18px">
							<div class="landing-folder-edit__section--text-link" style="margin-bottom: 8px"><?= Loc::getMessage('LANDING_TPL_FIELD_PREVIEW')?></div>
							<div class="landing-folder-edit__preview">
								<div id="landing-folder-picture">
								</div>
							</div>
						</div>
					</div>
				<?endif;?>
			</div>
		</div>
		<div id="landing-folder-index-metablock" class="landing-folder-edit__section --without-margin" style="display: <?= $arResult['INDEX_LANDING'] ? 'flex' : 'none'?>;">
			<div class="landing-folder-edit__section--title">
			<?/*
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-wa">
					<input type="checkbox" class="ui-ctl-element" id="landing-folder-edit__rich-url--toggler">
					<div class="ui-ctl-label-text"></div>
				</label>
			*/?>
			</div>
			<div class="landing-folder-edit__section--content --inline --padding-bottom">
				<div class="landing-folder-edit__section--wrapper">
					<?/*
					<div class="landing-folder-edit__preview-switcher">
						<div class="landing-folder-edit__preview-switcher--title"></div>
						<div class="landing-folder-edit__preview-switcher--control">
							<div class="ui-switcher"></div>
						</div>
					</div>
					*/?>
					<div class="landing-folder-edit__rich-url --show" id="landing-folder-edit__rich-url--wrapper">
						<div class="landing-folder-edit__rich-url--wrapper">
							<div class="landing-folder-edit__section--wrapper --margin-bottom">
								<div class="landing-folder-edit__section--text-link --margin-bottom"><?= Loc::getMessage('LANDING_TPL_FIELD_METAOG_TITLE')?></div>
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
									<input type="text" name="fields[METAOG_TITLE]" id="landing-folder-metaog-title" class="ui-ctl-element" value="<?= htmlspecialcharsbx($arResult['INDEX_META']['METAOG_TITLE'] ?: $arResult['INDEX_LANDING']['TITLE'])?>" placeholder="<?= Loc::getMessage('LANDING_TPL_FIELD_METAOG_TITLE')?>"/>
								</div>
							</div>
							<div class="landing-folder-edit__section--wrapper">
								<div class="landing-folder-edit__section--text-link --margin-bottom"><?= Loc::getMessage('LANDING_TPL_FIELD_METAOG_DESCRIPTION')?></div>
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
									<input type="text" name="fields[METAOG_DESCRIPTION]" id="landing-folder-metaog-description" class="ui-ctl-element" value="<?= htmlspecialcharsbx($arResult['INDEX_META']['METAOG_DESCRIPTION'] ?: $arResult['INDEX_LANDING']['DESCRIPTION'])?>" placeholder="<?= Loc::getMessage('LANDING_TPL_FIELD_METAOG_DESCRIPTION')?>" />
								</div>
							</div>
							<input type="hidden" name="fields[METAOG_IMAGE]" id="landing-folder-metaog-image" value="<?= htmlspecialcharsbx($arResult['INDEX_META']['~METAOG_IMAGE'])?>" />
							<input type="hidden" id="landing-folder-metaog-image-src" value="<?= htmlspecialcharsbx($arResult['INDEX_META']['METAOG_IMAGE'])?>" />
						</div>
					</div>
				</div>
			</div>
		</div>
		<?$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
			'BUTTONS' => [
				[
					'type' => 'custom',
					'layout' => '<button id="ui-button-panel-save" name="save" value="Y" class="ui-btn ui-btn-success ui-btn-round">'.GetMessage('LANDING_TPL_BUTTON_SAVE').'</button>'
				],
				'cancel'
			]
		]);?>
	</form>
</div>

<script type="text/javascript">
	BX.ready(function()
	{
		// rich url toggler

		var editableSelectors = document.querySelectorAll('[data-landing-edit-control]');
		var editModeClass = 'landing-folder-edit__edit-mode';

		function updateTextValue(editableText, input)
		{
			if (editableText.innerText !== input.value)
			{
				editableText.innerText = input.value;
			}
		}

		function onEditMode(selectorNode, editableText, inputNode, editIconNode)
		{
			BX.hide(editableText);
			BX.hide(editIconNode);
			BX.show(inputNode);
			var input = inputNode.querySelector('.ui-ctl-element');
			input.value = editableText.innerText;
			input.focus();

			var adjustBlur = function()
			{
				offEditMode(selectorNode, editableText, inputNode, editIconNode);
				BX.unbind(input, 'blur', adjustBlur);
				updateTextValue(editableText, input);
			}

			var adjustKeyDown = function(ev)
			{
				if (ev.keyCode === 27) // Esc
				{
					offEditMode(selectorNode, editableText, inputNode, editIconNode);
					BX.unbind(input, 'keydown', adjustKeyDown);
					ev.stopPropagation();
					return;
				}

				if (event.keyCode === 13) // Enter
				{
					updateTextValue(editableText, input);
					offEditMode(selectorNode, editableText, inputNode, editIconNode);
					BX.unbind(input, 'keydown', adjustKeyDown);
					ev.stopPropagation();
					ev.preventDefault()
				}
			}

			BX.bind(input, 'keydown', adjustKeyDown);
			BX.bind(input, 'blur', adjustBlur);
		}

		function offEditMode(selectorNode, editableText, inputNode, editIconNode)
		{
			BX.show(editableText);
			BX.show(editIconNode);
			inputNode.setAttribute('style', 'display: none !important')
		}

		for (var i = 0; i < editableSelectors.length; i++)
		{
			var editIconNode = editableSelectors[i];

			BX.bind(editIconNode, 'click', function()
			{
				var selectorNode = document.getElementById(this.getAttribute('for'));
				var editableText = selectorNode.querySelector('[data-landing-edit-text]');
				var inputNode = selectorNode.querySelector('[data-landing-edit-input]');

				if (selectorNode.classList.contains(editModeClass))
				{
					selectorNode.classList.remove(editModeClass);
				}
				else
				{
					onEditMode(selectorNode, editableText, inputNode, this);
				}
			});
		}

		BX.UI.Switcher.initByClassName();
		new BX.Landing.Component.FolderEdit({
			siteId: <?= $row['SITE_ID']['CURRENT'] ?: 0?>,
			indexLandingId: <?= $arResult['INDEX_LANDING']['ID'] ?? 0?>,
			siteType: '<?= $arParams['TYPE']?>',
			folderId: <?= $row['ID']['CURRENT'] ?: 0?>,
			selectorCreateIndex: BX('landing-folder-index-create'),
			selectorIndexMetaBlock: BX('landing-folder-index-metablock'),
			selectorSelect: BX('landing-folder-select-index'),
			selectorPageLink: BX('landing-folder-index-link'),
			selectorFieldId: BX('landing-folder-index'),
			selectorPreviewBlock: BX('landing-folder-metaog-group'),
			selectorPreviewTitle: BX('landing-folder-metaog-title'),
			selectorPreviewDescription: BX('landing-folder-metaog-description'),
			selectorPreviewPicture: BX('landing-folder-metaog-image'),
			selectorPreviewSrcPicture: BX('landing-folder-metaog-image-src'),
			selectorPreviewPictureWrapper: BX('landing-folder-picture'),
			pathToLandingEdit: '<?= \CUtil::jsEscape($arParams['PAGE_URL_LANDING_VIEW'])?>',
			pathToLandingCreate: '<?= \CUtil::jsEscape($arParams['~PAGE_URL_LANDING_EDIT'])?>'
		});
	});
</script>
