<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @global CMain $APPLICATION */
/** @var CBitrixSaleLocationImportComponent $component */
/** @var array $arParams */
/** @var array $arResult */

global $APPLICATION;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


if (!empty($arResult['ERRORS']['FATAL'])):
	CAdminMessage::ShowMessage([
		'MESSAGE' => htmlspecialcharsbx(implode(
			', ',
			$arResult['ERRORS']['FATAL']
		)),
		'type' => 'ERROR',
	]);
else:
	if (!empty($arResult['ERRORS']['NONFATAL'])):
		CAdminMessage::ShowMessage([
			'MESSAGE' => htmlspecialcharsbx(implode(
				', ',
				$arResult['ERRORS']['NONFATAL']
			)),
			'type' => 'ERROR',
		]);
	endif;

	$aTabs = [
		[
			"DIV" => "tab_reindex",
			"TAB" => Loc::getMessage("SALE_SLRI_TAB_REINDEX_TITLE"), "ICON" => "sale",
			"TITLE" => Loc::getMessage("SALE_SLRI_TAB_REINDEX_TITLE_SETTINGS"),
			"ONSELECT" => "BX.locationReindexInstance.setTab('tab_reindex')",
		],
	];

	$tabControl = new CAdminTabControl("tabctrl_reindex", $aTabs, true, true);

	CJSCore::Init();
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_iterator.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_itemtree.js');
	?>

	<div id="location-reindex">

		<div class="bx-ui-loc-ri-progressbar">
			<?php
			CAdminMessage::ShowMessage([
				"TYPE" => "PROGRESS",
				"DETAILS" => '#PROGRESS_BAR#'.
					'<div class="adm-loc-ri-statusbar">'.Loc::getMessage('SALE_SLRI_STATUS').': <span class="bx-ui-loc-ri-loader"></span>&nbsp;<span class="bx-ui-loc-ri-status-text">'.Loc::getMessage('SALE_SLRI_STAGE_INITIAL').'</span></div>',
				"HTML" => true,
				"PROGRESS_TOTAL" => 100,
				"PROGRESS_VALUE" => 0,
				"PROGRESS_TEMPLATE" => '<span class="bx-ui-loc-ri-percents">#PROGRESS_VALUE#</span>%',
			]);
			?>
		</div>

		<?php
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>

			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SALE_SLRI_SETTINGS_BASE')?></td>
			</tr>

			<tr>
				<td>
					<?=Loc::getMessage('SALE_SLRI_TYPES4INDEX_2')?>
				</td>
				<td>
					<select multiple class="bx-ui-loc-ri-option" name="TYPES">
						<option value=""<?=($arResult['TYPES_UNSELECTED'] ? ' selected' : '')?>>- <?=Loc::getMessage('SALE_SLRI_ALL_TYPES_2')?></option>
						<?php
						foreach($arResult['TYPES'] as $id => $type):
							?>
							<option value="<?=intval($id)?>"<?=($type['SELECTED'] ? ' selected' : '')?>><?=htmlspecialcharsbx($type['NAME'])?></option>
							<?php
						endforeach;
						?>
					</select>
				</td>
			</tr>

			<tr>
				<td>
					<?=Loc::getMessage('SALE_SLRI_LANGS4INDEX_2')?>
				</td>
				<td>
					<select multiple class="bx-ui-loc-ri-option" name="LANG">
						<option value=""<?=($arResult['LANGS_UNSELECTED'] ? ' selected' : '')?>>- <?=Loc::getMessage('SALE_SLRI_ALL_LANGS_2')?></option>
						<?php
						foreach($arResult['LANGS'] as $id => $lang):
							?>
							<option value="<?=htmlspecialcharsbx($id)?>"<?=($lang['SELECTED'] ? ' selected' : '')?>><?=htmlspecialcharsbx($lang['NAME'])?></option>
							<?php
						endforeach;
						?>
					</select>
				</td>
			</tr>

			<tr>
				<td>
					<?=Loc::getMessage('SALE_SLRI_AP_TIMELIMIT')?>
				</td>
				<td>
					<input type="text" name="TIME_LIMIT" value="20" class="bx-ui-loc-ri-option" />
				</td>
			</tr>

		<?php
		$tabControl->Buttons();
		?>
			<input type="submit" class="adm-btn-save bx-ui-loc-ri-button-start" value="<?=Loc::getMessage('SALE_SLRI_START')?>">
		<?php
		$tabControl->End();
		?>

	</div>

	<?=BeginNote();?>
		<?=Loc::getMessage('SALE_SLRI_HEAVY_DUTY_NOTICE')?>
		<br /><br />
		<?=Loc::getMessage('SALE_SLRI_HEAVY_DUTY_HOST_NOTICE')?>
	<?=EndNote();?>

	<script>
		BX.locationReindexInstance = new BX.Sale.component.location.reindex(<?=CUtil::PhpToJSObject(array(

				// common
				'url' => $arResult['URLS']['AJAX_URL'],
				'scope' => 'location-reindex',
				'ajaxFlag' => 'AJAX_CALL',
				'importId' => rand(99, 999),

				'messages' => array(
					'start' => Loc::getMessage('SALE_SLRI_START'),
					'stop' => Loc::getMessage('SALE_SLRI_STOP'),
					'stopping' => Loc::getMessage('SALE_SLRI_STOPPING'),

					'error_occured' => Loc::getMessage('SALE_SLRI_ERROR'),
					'sure_reindex' => Loc::getMessage('SALE_SLRI_SURE_REINDEX'),

					'stage_CLEANUP' => Loc::getMessage('SALE_SLRI_STAGE_CLEANUP'),
					'stage_CREATE_DICTIONARY' => Loc::getMessage('SALE_SLRI_STAGE_CREATE_DICTIONARY'),
					'stage_RESORT_DICTIONARY' => Loc::getMessage('SALE_SLRI_STAGE_RESORT_DICTIONARY'),
					'stage_CREATE_SEARCH_INDEX' => Loc::getMessage('SALE_SLRI_STAGE_CREATE_SEARCH_INDEX'),
					'stage_CREATE_SITE2LOCATION_INDEX' => Loc::getMessage('SALE_SLRI_STAGE_CREATE_SITE2LOCATION_INDEX'),
					'stage_RESTORE_DB_INDEXES' => Loc::getMessage('SALE_SLRI_STAGE_RESTORE_DB_INDEXES'),
					'stage_COMPLETE' => Loc::getMessage('SALE_SLRI_STAGE_COMPLETE'),

					'stage_INTERRUPTED' => Loc::getMessage('SALE_SLRI_STAGE_INTERRUPTED'),
					'stage_INTERRUPTING' => Loc::getMessage('SALE_SLRI_STAGE_INTERRUPTING'),
				)

		), false, false, true)?>);
	</script>

<?php
endif;