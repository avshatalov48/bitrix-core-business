<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arParams
 * @var array $arResult
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 * @global \CDatabase $DB
 * @var \CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var string $componentPath
 * @var \TranslateEditComponent|\CBitrixComponent $component
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadLanguageFile(__DIR__. '/template.php');
Loc::loadLanguageFile(__FILE__);

$isAjax = $arResult['IS_AJAX_REQUEST'];
$hasPermissionEdit = $arResult['ALLOW_EDIT'];


if (!$isAjax)
{
	?>
	<div class="adm-toolbar-panel-container adm-detail-toolba">
		<div class="adm-toolbar-panel-flexible-space">
	<?

	// chain
	if (!empty($arResult['CHAIN']))
	{
		foreach($arResult['CHAIN'] as $i => $chalk)
		{
			if ($i == 0)
			{
				?><a href="<?= $chalk['link'] ?>" title="<?= Loc::getMessage('TRANS_CHAIN_FOLDER_ROOT') ?>">..</a>&nbsp;/&nbsp;<?
			}
			else
			{
				if ($i > 1)
				{
					?>&nbsp;/ <?
				}
				?><a href="<?= $chalk['link'] ?>" title="<?= Loc::getMessage('TRANS_CHAIN_FOLDER') ?>"><?= $chalk['title'] ?></a><?
			}
		}
	}


	?>
		</div>
		<div class="adm-toolbar-panel-align-right">
			<button id="bx-translate-mode-menu-view-anchor" class="ui-btn ui-btn-dropdown ui-btn-default">
				<?= Loc::getMessage('TR_FILE_SHOW') ?>
			</button>
		</div>
	</div>
	<?
}

if (!empty($arResult['ERROR_MESSAGE']))
{
	?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
		<span class="ui-alert-message"><?= $arResult['ERROR_MESSAGE'] ?></span>
	</div>
	<?
}




//-------------------------------------------------------------------------------------
//region Form
?>
<div id="bx-translate-editor-<?= $arParams['TAB_ID'] ?>">
	<div class="translate-edit translate-edit-source">

		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_FILENAME')?></div>
			<div class="value read"><?= htmlspecialcharsbx(basename($arResult['FILE_PATH'])) ?></div>
		</div>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_FILEPATH')?></div>
			<div class="value read"><?= htmlspecialcharsbx($arResult['FILE_PATH']) ?></div>
		</div>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_PHRASE_COUNT')?></div>
			<div class="value read"><?= $arResult['FILE_PHRASE_COUNT'] ?></div>
		</div>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_LIST_COLUMN_LANGUAGE_ID')?></div>
			<div class="value read"><?= $arResult['FILE_LANGUAGE'] ?></div>
		</div>

		<div class="translate-edit-row source">
			<div class="title"><?= Loc::getMessage('TR_FILE_SOURCE_CODE')?></div>
		</div>
		<div class="translate-edit-row source">
			<div class="value">

				<?

				highlight_string($arResult['FILE_SOURCE']);

				?>
			</div>
		</div>

		<?
		$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
			'BUTTONS' =>
				[
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<span class="ui-btn ui-btn-success ui-btn-disabled" onclick="return false;">'.Loc::getMessage('TR_BUTTON_PANEL_SAVE').'</span>'
					],
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<span class="ui-btn ui-btn-primary ui-btn-disabled" onclick="return false;">'.Loc::getMessage('TR_BUTTON_PANEL_APPLY').'</span>'
					],
					[
						'TYPE' => 'cancel',
						'ONCLICK' => 'BX.Translate.Editor.cancel(); return false;',
					],
				],
			'ALIGN' => 'left'
		]);
		?>
	</div>
</div>
<?

//endregion




if (!$isAjax)
{
	?>
	<script type="text/javascript">
		BX.ready(function(){

			BX.Translate.Editor.init(<?=Json::encode(array(
				'id' => 'bx-translate-editor-'. $arParams['TAB_ID'],
				'controller' => 'bitrix:translate.controller.editor.file',
				'tabId' => (string)$arParams['TAB_ID'],
				'mode' => ((defined('ADMIN_SECTION') && ADMIN_SECTION == true) ? 'admin' : 'public'),
				'filePath' => $arResult['FILE_PATH'],
				'editLink' => $arResult['LINK_EDIT'],
				'linkBack' => $arResult['LINK_BACK'],
				'viewMode' => $arParams['VIEW_MODE'],
				'viewModeMenu' => [
					[
						'id'  => \TranslateEditComponent::VIEW_MODE_SHOW_ALL,
						'text'  => Loc::getMessage('TR_EDIT_SHOW_ALL'),
						'className'  => 'translate-view-mode-counter',
						'href' => $arResult['LINK_EDIT']. '&viewMode='.\TranslateEditComponent::VIEW_MODE_SHOW_ALL,
					],
					[
						'id' => \TranslateEditComponent::VIEW_MODE_UNTRANSLATED,
						'text' => Loc::getMessage('TR_EDIT_SHOW_UNTRANSLATED'),
						'className' => 'translate-view-mode-counter',
						'href' => $arResult['LINK_EDIT']. '&viewMode='.\TranslateEditComponent::VIEW_MODE_UNTRANSLATED,
					],
					($arResult['ALLOW_EDIT_SOURCE'] ? [
						'id'  => \TranslateEditComponent::VIEW_MODE_SOURCE_VIEW,
						'text'  => Loc::getMessage('TR_FILE_SHOW'),
						'className'  => 'translate-view-mode-counter menu-popup-item-accept',
						'href' => $arResult['LINK_SHOW_SOURCE'],
					] : null),
					($arResult['ALLOW_EDIT_SOURCE'] ? [
						'id'  => \TranslateEditComponent::VIEW_MODE_SOURCE_EDIT,
						'text'  => Loc::getMessage('TR_FILE_EDIT'),
						'className'  => 'translate-view-mode-counter',
						'href' => $arResult['LINK_EDIT_SOURCE'],
					] : null)
				],
			))?>);

		});
	</script>
	<?
}

