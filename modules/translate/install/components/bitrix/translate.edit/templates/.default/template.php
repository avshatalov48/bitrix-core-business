<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @global \CMain $APPLICATION
 * @var \CBitrixComponentTemplate $this
 * @var \TranslateEditComponent $component
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Translate;

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
			if ($i === 0)
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


	// view mode
	$showUntranslated = $arParams['SHOW_UNTRANSLATED'];
	if ($showUntranslated)
	{
		$dataTitle = Loc::getMessage('TR_EDIT_SHOW_UNTRANSLATED');
	}
	else // $showAll
	{
		$dataTitle = Loc::getMessage('TR_EDIT_SHOW_ALL');
	}

	?>
		</div>
		<div class="adm-toolbar-panel-align-right">
			<button id="bx-translate-mode-menu-view-anchor" class="ui-btn ui-btn-dropdown ui-btn-default">
				<?= $dataTitle ?>
			</button>

			<button id="bx-translate-extra-menu-anchor" class="ui-btn ui-btn-default ui-btn-icon-download"></button>
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


$dataUselessTitle = Loc::getMessage('TR_DIFF_USELESS');
$dataMoreTitle = Loc::getMessage('TR_DIFF_MORE');
$dataLessTitle = Loc::getMessage('TR_DIFF_LESS');
$fileEmptyEthalonTitle = Loc::getMessage('TR_ERROR_ETHALON_FILE_NOT_FOUND');
$fileEmptyTitle = Loc::getMessage('TR_ERROR_TRANSLATION_FILE_NOT_FOUND');
$fileOkTitle = Loc::getMessage('TR_TRANSLATION_FILE_OK');

$formatIconWarning = static function ($title)
{
	return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-warning" title="'. $title. '"></span>';
};
$formatIconError = static function ($title)
{
	return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-error" title="'. $title. '"></span>';
};
$formatIconOk = static function ($title)
{
	return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-ok" title="'. $title. '"></span>';
};
$formatDeficiencyExcessRounded = static function ($deficiency, $excess, $isObligatory) use ($dataMoreTitle, $dataLessTitle, $dataUselessTitle)
{
	if ($deficiency > 0 && $excess > 0 && $isObligatory)
	{
		return
			'<span class="translate-error">'.
			'<span title="'. $dataLessTitle. '" class="translate-error-less">'. $deficiency. '</span>'.
			'<span class="translate-error-split"></span>'.
			'<span title="'. $dataMoreTitle. '" class="translate-error-more">'. $excess. '</span>'.
			'</span>';
	}
	if ($deficiency > 0 && $isObligatory)
	{
		return '<span class="translate-error"><span title="'. $dataLessTitle. '" class="translate-error-less">'. $deficiency. '</span></span>';
	}
	if ($excess > 0)
	{
		if ($isObligatory)
		{
			return '<span class="translate-error"><span title="'. $dataMoreTitle. '" class="translate-error-more">'. $excess. '</span></span>';
		}
		return '<span class="translate-error"><span title="'. $dataUselessTitle. '" class="translate-error-more">'. $excess. '</span></span>';
	}
	return '';
};



//-------------------------------------------------------------------------------------
//region Form
?>
<form
	id="bx-translate-editor-<?= $arParams['TAB_ID'] ?>"
	method="post"
	accept-charset="<?= $arParams['CURRENT_LANG'] ?>"
	action="<?=$APPLICATION->GetCurPage()?>?viewMode=<?=$arResult['VIEW_MODE']?>&file=<?=htmlspecialcharsbx($arResult['PATH'])?>&lang=<?=$arParams['CURRENT_LANG']?>">
	<?=bitrix_sessid_post()?>

	<div class="translate-edit">

		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_FILENAME')?></div>
			<div class="value read"><?= htmlspecialcharsbx(basename($arResult['FILE_PATH'])) ?></div>
		</div>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_FILEPATH')?></div>
			<div class="value read"><?= htmlspecialcharsbx($arResult['FILE_PATH']) ?></div>
		</div>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_TOTAL_MESSAGES')?></div>
			<div class="value read"><?= $arResult['DIFFERENCES'][$arParams['CURRENT_LANG']]['TOTAL'] ?></div>
		</div>
		<? if (!empty($arResult['LANG_SETTINGS']['languages'])): ?>
			<div class="translate-edit-row">
				<div class="title"><?= Loc::getMessage('TR_OBLIGATORY_LANGS')?></div>
				<div class="value read"><?= implode(', ', $arResult['LANG_SETTINGS']['languages']) ?></div>
			</div>
		<? endif ?>
		<div class="translate-edit-row">
			<div class="title"><?= Loc::getMessage('TR_PHRASE_COUNT')?></div>
			<div class="value">
				<table class="internal">
					<tr class="heading">
						<?
						$w = round(100 / count($arResult['DIFFERENCES']));
						foreach ($arResult['DIFFERENCES'] as $langId => $diff)
						{
							?><td style="width:<?= $w ?>%"><?= $langId ?></td><?
						}
						?>
					</tr>
					<tr>
						<?
						$ethalonExists = ($arResult['DIFFERENCES'][$arParams['CURRENT_LANG']]['TOTAL'] > 0);

						foreach ($arResult['DIFFERENCES'] as $langId => $diff)
						{
							$isObligatory = true;
							if (!empty($arResult['LANG_SETTINGS']['languages']))
							{
								$isObligatory = in_array($langId, $arResult['LANG_SETTINGS']['languages'], true);
							}

							?>
							<td>
								<?
								if ($diff["TOTAL"] === 0)
								{
									if ($langId === $arParams['CURRENT_LANG'])
									{
										if ($isObligatory)
										{
											echo $formatIconWarning($fileEmptyEthalonTitle);
										}
									}
									elseif (!$ethalonExists)
									{
										if ($isObligatory)
										{
											echo $formatIconWarning($fileEmptyTitle);
										}
									}
									else
									{
										if ($isObligatory)
										{
											echo $formatIconError($fileEmptyTitle);
										}
									}
								}
								else
								{
									echo $diff["TOTAL"];
								}

								echo $formatDeficiencyExcessRounded($diff["LESS"], $diff["MORE"], $isObligatory);

								?>
							</td><?
						}
						?>
					</tr>
				</table>
			</div>
		</div>
		<?
		$tabIndex = 100;
		foreach ($arResult['MESSAGES'] as $phraseId => $phrases)
		{
			$highlightMore = false;
			$highlightLess = false;


			if (!in_array($phraseId, $arResult['DIFFERENCES'][$arParams['CURRENT_LANG']]['CODES'], true))
			{
				$highlightMore = true;
			}
			if (!$highlightMore)
			{
				foreach ($arResult['DIFFERENCES'] as $langId => $differences)
				{
					$isObligatory = true;
					if (!empty($arResult['LANG_SETTINGS']['languages']))
					{
						$isObligatory = in_array($langId, $arResult['LANG_SETTINGS']['languages'], true);
					}

					if (!in_array($phraseId, $differences['CODES'], true))
					{
						$highlightLess = $isObligatory;
						break;
					}
				}
			}

			$hashKey = preg_replace("/[^a-z1-9_]+/i", '', $phraseId);
			$fldDel = $component->generateFieldName($phraseId, '', 'DEL');
			$tabIndex ++;

			$highlightClass = $highlightLess ? 'error-less' : ($highlightMore ? 'error-more' : '');

			?>
			<a name="<?= $hashKey ?>"></a>
			<div class="translate-edit-row code" rel="<?= $phraseId ?>">
				<div class="title">ID:</div>
				<div class="value read <?= $highlightClass ?>"><?
					echo $phraseId;
				?></div>
				<div class="manage">
					<label for="DEL_<?= $tabIndex ?>"><?= Loc::getMessage("TRANS_DELETE")?></label>
					<input type="checkbox" name="<?= $fldDel ?>" data-code="<?= $phraseId ?>" value="Y" id="DEL_<?= $tabIndex ?>" class="eraser adm-designed-checkbox <?= $highlightClass ?>">
					<label class="eraser-label adm-designed-checkbox-label" for="DEL_<?= $tabIndex ?>" tabindex="<?= $tabIndex ?>"></label>
				</div>
			</div>
			<?
			foreach ($arResult['LANGUAGES'] as $langId)
			{
				if (!isset($phrases[$langId]) || !is_string($phrases[$langId]) || (empty($phrases[$langId]) && $phrases[$langId] !== '0'))
				{
					$phrases[$langId] = '';
				}

				$isCompatible = in_array($langId, $arResult['COMPATIBLE_LANGUAGES'], true);
				$fldName = $component->generateFieldName($phraseId, $langId);

				$lineCnt = 1;
				if (mb_strpos($phrases[$langId], "\n") !== false)
				{
					$lineCnt = mb_substr_count($phrases[$langId], "\n");
				}
				$length = mb_strlen($phrases[$langId]);


				?>
				<div class="translate-edit-row phrase" rel="<?= $phraseId ?>">
					<div class="title">[<?= $langId ?>]&nbsp;<?= $arResult['LANGUAGES_TITLE'][$langId] ?>:</div>
					<?
					if ($hasPermissionEdit && $isCompatible)
					{
						$tabIndex ++;
						?>
						<div class="value editable" tabindex="<?= $tabIndex ?>" data-fld="<?= $fldName ?>" data-code="<?= $phraseId ?>" data-lng="<?= $langId ?>" data-lines="<?= $lineCnt ?>" data-length="<?= $length ?>"><?
						echo Translate\Text\StringHelper::htmlSpecialChars($phrases[$langId]);
						?></div>
						<?
					}
					else
					{
						?>
						<div class="value disabled" <? if (!$isCompatible):?>title="<?= Loc::getMessage('TR_UNCOMPATIBLE_ENCODING') ?>" <? endif ?>><?
						echo Translate\Text\StringHelper::htmlSpecialChars($phrases[$langId]);
						?></div>
						<?
					}
					?>
				</div>
				<?
			}
		}
		?>
	</div>
	<?

	if ($hasPermissionEdit)
	{
		$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
			'BUTTONS' =>
				[
					[
						'TYPE' => 'save',
						'ONCLICK' => 'BX.Translate.Editor.save(true); return false;',
					],
					[
						'TYPE' => 'apply',
						'ONCLICK' => 'BX.Translate.Editor.save(false); return false;',
					],
					[
						'TYPE' => 'cancel',
						'ONCLICK' => 'BX.Translate.Editor.cancel(); return false;',
					],
					[
						'TYPE' => 'custom',
						'LAYOUT' =>
							'<div id="bx-translate-delete-menu-anchor" class="translate-edit-button-panel-block-right">'.
								'<div class="ui-btn ui-btn-dropdown ui-btn-default ui-btn-icon-remove">'. Loc::getMessage('TR_EDIT_DELETE') .'</div>'.
							'</div>'
					],
				],
			'ALIGN' => 'left'
		]);
	}

?>
</form>
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
				'mode' => ((defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? 'admin' : 'public'),
				'filePath' => $arResult['FILE_PATH'],
				'editLink' => $arResult['LINK_EDIT'],
				'linkBack' => $arResult['LINK_BACK'],
				'messages' => [
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCompleted' => Loc::getMessage('TR_DLG_REQUEST_COMPLETED'),
					'Delete' => Loc::getMessage('TR_EDIT_DELETE'),
					'DeleteAll' => Loc::getMessage('TRANS_DELETE_ALL'),
					'DeleteEthalon' => Loc::getMessage('TRANS_DELETE_CURRENT'),
				],
				'viewMode' => $arParams['VIEW_MODE'],
				'viewModeMenu' => [
					[
						'id'  => \TranslateEditComponent::VIEW_MODE_SHOW_ALL,
						'text'  => Loc::getMessage('TR_EDIT_SHOW_ALL'),
						'className'  => 'translate-view-mode-counter '. ($arParams['VIEW_MODE'] === \TranslateEditComponent::VIEW_MODE_SHOW_ALL ? 'menu-popup-item-accept' : ''),
						'href' => $arResult['LINK_EDIT']. '&viewMode='.\TranslateEditComponent::VIEW_MODE_SHOW_ALL,
					],
					[
						'id' => \TranslateEditComponent::VIEW_MODE_UNTRANSLATED,
						'text' => Loc::getMessage('TR_EDIT_SHOW_UNTRANSLATED'),
						'className' => 'translate-view-mode-counter '. ($arParams['VIEW_MODE'] === \TranslateEditComponent::VIEW_MODE_UNTRANSLATED ? 'menu-popup-item-accept' : ''),
						'href' => $arResult['LINK_EDIT']. '&viewMode='.\TranslateEditComponent::VIEW_MODE_UNTRANSLATED,
					],
					($arResult['ALLOW_EDIT_SOURCE'] ? [
						'id'  => \TranslateEditComponent::VIEW_MODE_SOURCE_VIEW,
						'text'  => Loc::getMessage('TR_FILE_SHOW'),
						'className'  => 'translate-view-mode-counter',
						'href' => $arResult['LINK_SHOW_SOURCE'],
					] : null),
					($arResult['ALLOW_EDIT_SOURCE'] ? [
						'id'  => \TranslateEditComponent::VIEW_MODE_SOURCE_EDIT,
						'text'  => Loc::getMessage('TR_FILE_EDIT'),
						'className'  => 'translate-view-mode-counter',
						'href' => $arResult['LINK_EDIT_SOURCE'],
					] : null)
				],
				'extraMenu' => [
					[
						'id' => 'translate-export-csv',
						'text' => Loc::getMessage('TR_EDIT_EXPORT_CSV'),
						'onclick' => "BX.Translate.ProcessManager.getInstance('export').showDialog();"
					]
				],
				'deleteMenu' => [
					[
						'id' => 'translate-delete-all',
						'text' => Loc::getMessage('TRANS_DELETE_ALL'),
						'onclick' => "BX.Translate.Editor.toggleDelete('all');",
						'className'  => 'translate-view-mode-counter',
					],
					[
						'id' => 'translate-delete-ethalon',
						'text' => Loc::getMessage('TRANS_DELETE_CURRENT'),
						'onclick' => "BX.Translate.Editor.toggleDelete('ethalon');",
						'className'  => 'translate-view-mode-counter',
					]
				],
				'highlightPhrase' => (isset($arParams['HIGHLIGHT_PHRASE_ID']) ? $arParams['HIGHLIGHT_PHRASE_ID'] : ''),

			))?>);

			<?
			// Export dialog
			if ($hasPermissionEdit)
			{
				?>
				BX.Translate.ProcessManager.create(<?=Json::encode([
					'id' => 'export',
					'controller' => 'bitrix:translate.controller.export.csv',
					'messages' => [
						'DialogTitle' => Loc::getMessage("TR_EXPORT_CSV_DLG_TITLE"),
						'DialogSummary' => Loc::getMessage("TR_EXPORT_CSV_DLG_SUMMARY"),
						'DialogStartButton' => Loc::getMessage('TR_EXPORT_CSV_DLG_BTN_START'),
						'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
						'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
						'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
						'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
						'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
						'RequestCanceled' => Loc::getMessage('TR_EXPORT_CSV_DLG_CANCELED'),
						'RequestCompleted' => Loc::getMessage('TR_EXPORT_CSV_DLG_COMPLETED'),
						'DialogExportDownloadButton' => Loc::getMessage('TR_EXPORT_DLG_DOWNLOAD'),
						'DialogExportClearButton' => Loc::getMessage('TR_EXPORT_DLG_CLEAR'),
					],
					'queue' => [
						[
							'action' => \Bitrix\Translate\Controller\Export\Csv::ACTION_EXPORT,
							'controller' => 'bitrix:translate.controller.export.csv',
							'title' => Loc::getMessage('TR_EXPORT_CSV_DLG_TITLE'),
						],
					],
					'params' => [
						'path' => $arResult['FILE_PATH'],
					],
					'sToken' => 's'. time(),
					'optionsFields' => [
						'collectUntranslated' => [
							'name' => 'collectUntranslated',
							'type' => 'checkbox',
							'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_UNTRANSLATED'),
							'value' => 'N'
						],
						'convertEncoding' => [
							'name' => 'convertEncoding',
							'type' => 'checkbox',
							'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_CONVERT_UTF8'),
							'value' => ((Main\Localization\Translation::useTranslationRepository() || Translate\Config::isUtfMode()) ? 'Y' : 'N'),
						],
						'languages' => [
							'name' => 'languages',
							'type' => 'select',
							'multiple' => 'Y',
							'size' => (count($arResult['LANGUAGES_TITLE']) >= 15 ? '15' : count($arResult['LANGUAGES_TITLE']) + 1),
							'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES'),
							'list' => array_merge(['all' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES_ALL')], $arResult['LANGUAGES_TITLE']),
							'value' => 'all',
						]
					]
				])?>);
				<?
			}
			?>
		});
	</script>
	<?
}

