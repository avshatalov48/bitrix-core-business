<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
global $APPLICATION;
?>

<?if($arResult['DISPLAY_FILE_UPLOAD_RESPONCE']):?>
	<?
	$APPLICATION->RestartBuffer();
	while (@ob_end_clean());
	?>
	<script>(window.BX||top.BX)['file-async-loader']['<?=$arResult['FILE_UPLOAD_ID']?>'].<?=(empty($arResult['ERRORS']['FATAL']) ? 'uploadSuccess' : 'uploadFail')?>();</script>
	<?die();?>
<?endif?>

<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

	<?CAdminMessage::ShowMessage(array('MESSAGE' => htmlspecialcharsbx(implode(', ', $arResult['ERRORS']['FATAL'])), 'type' => 'ERROR'))?>

<?else:?>

	<?if(!empty($arResult['ERRORS']['NONFATAL'])):?>
		<?CAdminMessage::ShowMessage(array('MESSAGE' => htmlspecialcharsbx(implode(', ', $arResult['ERRORS']['NONFATAL'])), 'type' => 'ERROR'))?>
	<?endif?>

	<?
	$aTabs = array(
		array(
			"DIV" => "tab_import",
			"TAB" => Loc::getMessage("SALE_SLI_TAB_IMPORT_TITLE"), "ICON" => "sale",
			"TITLE" => Loc::getMessage("SALE_SLI_TAB_IMPORT_TITLE"),
			"ONSELECT" => "BX.locationImport.setTab('tab_import')"
		),
		array(
			"DIV" => "tab_cleanup",
			"TAB" => Loc::getMessage("SALE_SLI_TAB_CLEANUP_TITLE"),
			"ICON" => "sale",
			"TITLE" => Loc::getMessage("SALE_SLI_TAB_CLEANUP_TITLE"),
			"ONSELECT" => "BX.locationImport.setTab('tab_cleanup')"
		),
	);

	$tabControl = new CAdminTabControl("tabctrl_import", $aTabs, false, true);

	CJSCore::Init();
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_iterator.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_itemtree.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_fileasyncloader.js');
	?>

	<div id="location-import">

		<?=BeginNote()?>
			<?=Loc::getMessage('SALE_SLI_STAT_TITLE')?>:
			<ul class="bx-ui-loc-i-stat-list">
				<?foreach($arResult['STATISTICS'] as $code => $stat):?>
					<?if(strlen($stat['NAME'])):?>
						<li><?=htmlspecialcharsbx($stat['NAME'])?>: <?=intval($stat['CNT'])?></li>
					<?endif?>
				<?endforeach?>
				<script type="text/html" data-template-id="bx-ui-loc-i-stat-item">
					<li>{{type}}: {{count}}</li>
				</script>
			</ul>
			<ul>
				<li><?=Loc::getMessage('SALE_SLI_STAT_TOTAL')?>: <span class="bx-ui-loc-i-stat-all"><?=intval($arResult['STATISTICS']['TOTAL']['CNT'])?></span></li>
				<li><?=Loc::getMessage('SALE_SLI_STAT_TOTAL_GROUPS')?>: <span class="bx-ui-loc-i-stat-groups"><?=intval($arResult['STATISTICS']['GROUPS']['CNT'])?></span></li>
			</ul>
		<?=EndNote();?>

		<div class="bx-ui-loc-i-progressbar">
			<?
			CAdminMessage::ShowMessage(array(
				"TYPE" => "PROGRESS",
				"DETAILS" => '#PROGRESS_BAR#'.
					'<div class="adm-loc-i-statusbar">'.Loc::getMessage('SALE_SLI_STATUS').': <span class="bx-ui-loc-i-loader"></span>&nbsp;<span class="bx-ui-loc-i-status-text">'.Loc::getMessage('SALE_SLI_STAGE_INITIAL').'</span></div>',
				"HTML" => true,
				"PROGRESS_TOTAL" => 100,
				"PROGRESS_VALUE" => 0,
				"PROGRESS_TEMPLATE" => '<span class="bx-ui-loc-i-percents">#PROGRESS_VALUE#</span>%'
			));
			?>
		</div>

		<?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>

			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SALE_SLI_SOURCE')?></td>
			</tr>

			<tr>
				<td>
					<?=Loc::getMessage('SALE_SLI_LOCATION_SOURCE')?>
				</td>
				<td class="bx-ui-loc-i-mode-switch">
					<label><input type="radio" name="SOURCE" value="remote" checked class="bx-ui-loc-i-option" /><?=Loc::getMessage('SALE_SLI_SOURCE_REMOTE')?></label><br />
					<label><input type="radio" name="SOURCE" value="file" class="bx-ui-loc-i-option" /><?=Loc::getMessage('SALE_SLI_SOURCE_FILE')?></label></label>
				</td>
			</tr>

			<tr class="bx-ui-load-file">
				<td>
				</td>
				<td>
					<div class="bx-ui-loc-i-userfile">
						<div class="bx-ui-file-async-loader-input">
							<input type="file" name="IMPORT_FILE" />
						</div>
						<div class="bx-ui-file-async-loader-success">
							<?=Loc::getMessage('SALE_SLI_FILE_UPLOADED')?> <a href="javascript:void(0)" class="bx-ui-file-async-loader-retry"><?=Loc::getMessage('SALE_SLI_RELOAD_FILE')?></a>
						</div>
						<div class="bx-ui-file-async-loader-fail">
							<?=Loc::getMessage('SALE_SLI_FILE_UPLOAD_ERROR')?> <a href="javascript:void(0)" class="bx-ui-file-async-loader-retry"><?=Loc::getMessage('SALE_SLI_RETRY_FILE_UPLOAD')?></a>
						</div>
						<div class="bx-ui-file-async-loader-in-progress">
							<?=Loc::getMessage('SALE_SLI_FILE_IS_BEING_UPLOADED')?> ...
						</div>
					</div>

					<?=BeginNote();?>
						<?=Loc::getMessage('SALE_SLI_SOURCE_FILE_NOTES', array(
							'#ANCHOR_LOCTYPES#' => '<a href="'.$arResult['URLS']['TYPE_LIST'].'" target="_blank">', 
							'#ANCHOR_EXT_SERVS#' => '<a href="'.$arResult['URLS']['EXTERNAL_SERVICE_LIST'].'" target="_blank">',
							'#ANCHOR_END#' => '</a>'
						))?>
					<?=EndNote();?>
				</td>
			</tr>
			<tr class="bx-ui-load-remote">
				<td>
				</td>
				<td>
					<div class="adm-loc-i-selector bx-ui-loc-i-location-set">

						<?ob_start();?>

						<div class="adm-loc-i-tree-node bx-ui-item-tree-node">
							<a href="javascript:void(0)" class="adm-loc-i-selector-arrow {{EXPANDER_CLASS}}"></a>
							<label class="adm-loc-orig-label">
								<input type="checkbox" value="{{CODE}}" name="{{INPUT_NAME}}" class="bx-ui-item-tree-checkbox" />
								{{NAME}}
							</label>
							<div class="adm-loc-i-tree-panel bx-ui-item-tree-children">
								{{CHILDREN}}
							</div>
						</div>

						<?$template = ob_get_contents();?>
						<?ob_end_clean();?>

						<?=$component->renderLayOut(array(
							'LAYOUT' => $arResult['LAYOUT'],
							'TEMPLATE' => $template,
							'EXPANDER_CLASS' => 'bx-ui-item-tree-expander',
							'INPUT_NAME' => 'LOCATION_SET[]'
						))?>

					</div>
				</td>
			</tr>

			<tr class="heading bx-ui-load-remote">
				<td colspan="2"><?=Loc::getMessage('SALE_SLI_EXTRA_DATA')?></td>
			</tr>

			<tr class="bx-ui-load-remote">
				<td>
					<label for="loc-i-additional-zip"><?=Loc::getMessage('SALE_SLI_EXTRA_EXTERNAL_ZIP')?></label>
				</td>
				<td>
					<input type="checkbox" value="ZIP" name="ZIP" id="loc-i-additional-zip" class="bx-ui-loc-i-additional" checked />
				</td>
			</tr>

			<?if(in_array(LANGUAGE_ID, array('ru', 'ua'))):?>
				<tr class="bx-ui-load-remote">
					<td>
						<label for="loc-i-additional-yamarket"><?=Loc::getMessage('SALE_SLI_EXTRA_EXTERNAL_YAMARKET')?></label>
					</td>
					<td>
						<input type="checkbox" value="YAMARKET" name="YAMARKET" id="loc-i-additional-yamarket" class="bx-ui-loc-i-additional" checked />
					</td>
				</tr>
			<?endif?>

			<?/*
			<tr class="bx-ui-load-remote">
				<td>
					<label for="loc-i-additional-geocoords"><?=Loc::getMessage('SALE_SLI_EXTRA_GEOCOORDS')?></label>
				</td>
				<td>
					<input type="checkbox" value="GEODATA" name="GEODATA" id="loc-i-additional-geocoords" class="bx-ui-loc-i-additional" checked />
				</td>
			</tr>
			*/?>

			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SALE_SLI_ADDITIONAL_PARAMS')?></td>
			</tr>

			<tr class="bx-ui-load-remote">
				<td>
					<?=Loc::getMessage('SALE_SLI_LOAD_LOCATIONS_TILL_INCLUSIVELY')?>
				</td>
				<td>
					<select class="bx-ui-loc-i-option" name="DEPTH_LIMIT">
						<option value="">- <?=Loc::getMessage('SALE_SLI_DONT_LIMIT_LOCATION_DEPTH')?></option>
						<?foreach($arResult['TYPE_LEVELS'] as $id => $level):?>
							<option value="<?=intval($id)?>"<?=($level['DEFAULT'] ? ' selected' : '')?>><?=htmlspecialcharsbx($level['NAMES'])?></option>
						<?endforeach?>
					</select>
				</td>
			</tr>

			<tr class="bx-ui-load-remote">
				<td>
					<?=Loc::getMessage('SALE_SLI_LOCATION_PACK')?>
				</td>
				<td>
					<label>
						<input type="radio" name="PACK" value="standard" class="bx-ui-loc-i-option" checked />
						<?=Loc::getMessage('SALE_SLI_LOCATION_PACK_STANDARD')?>
					</label>
					<br />
					<label>
						<input type="radio" name="PACK" value="extended" class="bx-ui-loc-i-option" />
						<?=Loc::getMessage('SALE_SLI_LOCATION_PACK_EXTENDED')?>
					</label>
				</td>
			</tr>

			<?if(in_array(LANGUAGE_ID, array('ru', 'ua', 'de'))):?>
				<tr class="bx-ui-load-remote">
					<td>
						<label for="loc-i-option-exclude-country-district"><?=Loc::getMessage('SALE_SLI_EXCLUDE_AREAS')?></label>
					</td>
					<td>
						<input type="checkbox" value="1" name="EXCLUDE_COUNTRY_DISTRICT" class="bx-ui-loc-i-option" id="loc-i-option-exclude-country-district" checked="checked" />
					</td>
				</tr>
			<?endif?>

			<tr>
				<td>
					<label for="loc-i-option-drop-all"><?=Loc::getMessage('SALE_SLI_AP_DROP_STRUCTURE')?></label>
				</td>
				<td>
					<input type="checkbox" value="1" name="DROP_ALL" class="bx-ui-loc-i-option" id="loc-i-option-drop-all" />
				</td>
			</tr>

			<tr>
				<td>
					<?=Loc::getMessage('SALE_SLI_AP_TIMELIMIT')?>
				</td>
				<td>
					<input type="text" name="TIME_LIMIT" value="20" class="bx-ui-loc-i-option" />
				</td>
			</tr>

			<tr class="bx-ui-load-remote">
				<td>
					<label for="loc-i-option-integrity-preserve"><?=Loc::getMessage('SALE_SLI_AP_PRESERVE_INTEGRITY')?></label>
				</td>
				<td>
					<input type="checkbox" value="1" name="INTEGRITY_PRESERVE" class="bx-ui-loc-i-option" id="loc-i-option-integrity-preserve" checked />
				</td>
			</tr>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr>
				<td colspan="2">
					<form action="<?=$arResult['URLS']['IMPORT']?>" method="post" class="bx-ui-loc-i-delete-all-form">
						<div class="adm-btn-wrapper">
							<input type="submit" value="<?=Loc::getMessage('SALE_SLI_REMOVE_ALL')?>" class="adm-btn-save bx-ui-loc-i-delete-all" />
							<input type="hidden" name="DROP_ALL" value="1" />
							<div class="adm-btn-load-img-green"></div>
						</div>
					</form>
				</td>
			</tr>

		<?
		$tabControl->EndTab();
		$tabControl->Buttons();
		?>
			<input type="submit" class="adm-btn-save bx-ui-loc-i-button-start" value="<?=Loc::getMessage('SALE_SLI_START')?>">
		<?
		$tabControl->End();
		?>

	</div>

	<?=BeginNote();?>
		<?=Loc::getMessage('SALE_SLI_HEAVY_DUTY_NOTICE')?>
		<br /><br />
		<?=Loc::getMessage('SALE_SLI_HEAVY_DUTY_HOST_NOTICE')?>
	<?=EndNote();?>

	<script>
		BX.locationImport = new BX.Sale.component.location.import(<?=CUtil::PhpToJSObject(array(

				// common
				'url' => CHTTP::urlAddParams($arResult['URLS']['IMPORT_AJAX'], array('lang' => LANGUAGE_ID)),
				'pageUrl' => $arResult['URLS']['IMPORT'],
				'scope' => 'location-import',
				'ajaxFlag' => 'AJAX_CALL',
				'importId' => rand(99, 999),
				'firstImport' => !!$arResult['FIRST_IMPORT'],
				'statistics' => array('TOTAL' => array('CNT' => (isset($arResult['STATISTICS']['TOTAL']) ? intval($arResult['STATISTICS']['TOTAL']['CNT']) : 0))),

				'messages' => array(
					'start' => Loc::getMessage('SALE_SLI_START'),
					'stop' => Loc::getMessage('SALE_SLI_STOP'),
					'stopping' => Loc::getMessage('SALE_SLI_STOPPING'),
					'selectItems' => Loc::getMessage('SALE_SLI_CHECK_ITEMS_AND_PROCEED', array('#START#' => Loc::getMessage('SALE_SLI_START'))),
					'uploadFile' => Loc::getMessage('SALE_SLI_UPLOAD_FILE_AND_PROCEED', array('#START#' => Loc::getMessage('SALE_SLI_START'))),

					'error_occured' => Loc::getMessage('SALE_SLI_ERROR'),

					'confirm_delete' => Loc::getMessage('SALE_SLI_DELETE_ALL_CONFIRM'),
					'confirm_delete_relic' => Loc::getMessage('SALE_SLI_DELETE_ALL_CONFIRM_RELIC'),

					'stage_DOWNLOAD_FILES' => Loc::getMessage('SALE_SLI_STAGE_DOWNLOAD_FILES'),
					'stage_REBALANCE_WALK_TREE' => Loc::getMessage('SALE_SLI_STAGE_REBALANCE'),
					'stage_REBALANCE_CLEANUP_TEMP_TABLE' => Loc::getMessage('SALE_SLI_STAGE_CLEANUP_TEMP_TABLE'),
					'stage_RESTORE_INDEXES' => Loc::getMessage('SALE_SLI_STAGE_RESTORE_INDEXES'),
					'stage_DELETE_ALL' => Loc::getMessage('SALE_SLI_STAGE_DELETE_ALL'),
					'stage_PROCESS_FILES' => Loc::getMessage('SALE_SLI_STAGE_PROCESS_FILES'),
					'stage_INTEGRITY_PRESERVE' => Loc::getMessage('SALE_SLI_STAGE_INTEGRITY_PRESERVE'),
					'stage_COMPLETE' => Loc::getMessage('SALE_SLI_STAGE_COMPLETE'),
					'stage_INTERRUPTED' => Loc::getMessage('SALE_SLI_STAGE_INTERRUPTED'),
					'stage_INTERRUPTING' => Loc::getMessage('SALE_SLI_STAGE_INTERRUPTING'),
					'stage_COMPLETE_REMOVE_ALL' => Loc::getMessage('SALE_SLI_COMPLETE_REMOVE_ALL'),
					'stage_DROP_INDEXES' => Loc::getMessage('SALE_SLI_DROP_INDEXES'),
				)

		), false, false, true)?>);
	</script>

<?endif?>