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

	<?foreach($arResult['ERRORS']['FATAL'] as $error):?>
		<?=ShowError($error)?>
	<?endforeach?>

<?else:?>

	<?if(!empty($arResult['ERRORS']['NONFATAL'])):?>
		<?foreach($arResult['ERRORS']['NONFATAL'] as $error):?>
			<?=ShowError($error)?>
		<?endforeach?>
	<?endif?>

	<?
	CJSCore::Init();
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_iterator.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_itemtree.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_fileasyncloader.js');
	?>

	<div id="location-import">

		<div class="bx-sli-note" style="max-width: 250px">

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

		</div>

		<div class="bx-ui-loc-i-progressbar bx-sli-progressbar">

			<div class="bx-import-progressbar">
				<div style="width: 500px;" class="instal-progress-bar-outer">
					<div class="instal-progress-bar-alignment">
						<div style="width: 0px;" class="instal-progress-bar-inner adm-progress-bar-inner">
							<div style="width: 500px;" class="instal-progress-bar-inner-text"><span class="bx-ui-loc-i-percents">0</span>%</div>
						</div>
						<span class="instal-progress-bar-span"><span class="bx-ui-loc-i-percents">0</span>%</span>
					</div>
				</div>

				<div class="bx-import-pb-statusbar">
					<?=Loc::getMessage('SALE_SLI_STATUS')?>: <span class="bx-ui-loc-i-loader"></span>&nbsp;<span class="bx-ui-loc-i-status-text"><?=Loc::getMessage('SALE_SLI_STAGE_INITIAL')?></span>
				</div>
			</div>

		</div>

		<?
		$arTabs = array(
			'tab_params' => array(
				'id' => 'tab_import',
				'name' => Loc::getMessage('SALE_SLI_TAB_IMPORT_TITLE'),
				'icon' => '',
				'onselect_callback' => 'BX.locationImport.setTab'
			),
			'tab_cleanup' => array(
				'id' => 'tab_cleanup',
				'name' => Loc::getMessage('SALE_SLI_TAB_CLEANUP_TITLE'),
				'icon' => '',
				'onselect_callback' => 'BX.locationImport.setTab'
			)
		);

		$remoteClassName = 	'bx-ui-load-remote';
		$fileClassName = 	'bx-ui-load-file';

		$arTabs['tab_params']['fields'] = array();
		$arTabs['tab_params']['fields'][] = array(
			'id' => 'heading_import_source',
			'name' => Loc::getMessage('SALE_SLI_SOURCE'),
			'type' => 'section'
		);

		//////////////////////////////////////
		// source selector

		ob_start();
		?>
			<label><input type="radio" name="SOURCE" value="remote" checked class="bx-ui-loc-i-option" /><?=Loc::getMessage('SALE_SLI_SOURCE_REMOTE')?></label><br />
			<label><input type="radio" name="SOURCE" value="file" class="bx-ui-loc-i-option" /><?=Loc::getMessage('SALE_SLI_SOURCE_FILE')?></label></label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_source_selector',
			'name' => Loc::getMessage('SALE_SLI_LOCATION_SOURCE'),
			'type' => 'custom',
			'value' => $customHtml,
			'class' => 'bx-ui-loc-i-mode-switch'
		);

		//////////////////////////////////////
		// source file selector

		ob_start();
		?>
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

			<div class="bx-sli-note">
				<?=Loc::getMessage('SALE_SLI_SOURCE_FILE_NOTES', array(
					'#ANCHOR_LOCTYPES#' => '<a href="'.$arResult['URLS']['TYPE_LIST'].'" target="_blank">', 
					'#ANCHOR_EXT_SERVS#' => '<a href="'.$arResult['URLS']['EXTERNAL_SERVICE_LIST'].'" target="_blank">',
					'#ANCHOR_END#' => '</a>'
				))?>
			</div>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_source_file',
			'name' => '',
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $fileClassName
		);

		//////////////////////////////////////
		// source remote selector

		ob_start();
		?>

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

		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_source_remote',
			'name' => '',
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);

		//////////////////////////////////////
		// extra data heading

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'heading_import_extradata',
			'name' => Loc::getMessage('SALE_SLI_EXTRA_DATA'),
			'type' => 'section',
			'class' => $remoteClassName
		);

		//////////////////////////////////////
		// extra data: zip

		ob_start();
		?>
			<label>
				<input type="checkbox" value="ZIP" name="ZIP" id="loc-i-additional-zip" class="bx-ui-loc-i-additional" checked />
				<?=Loc::getMessage('SALE_SLI_EXTRA_EXTERNAL_ZIP')?>
			</label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_extradata_zip',
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);

		if(in_array(LANGUAGE_ID, array('ru', 'ua')))
		{
			//////////////////////////////////////
			// extra data: yamarket

			ob_start();
			?>
				<label>
					<input type="checkbox" value="YAMARKET" name="YAMARKET" id="loc-i-additional-yamarket" class="bx-ui-loc-i-additional" checked />
					<?=Loc::getMessage('SALE_SLI_EXTRA_EXTERNAL_YAMARKET')?>
				</label>
			<?
			$customHtml = ob_get_contents();
			ob_end_clean();

			$arTabs['tab_params']['fields'][] = array(
				'id' => 'import_extradata_yamarket',
				'type' => 'custom',
				'value' => $customHtml,
				'class' => $remoteClassName
			);
		}

		//////////////////////////////////////
		// extra data: geo

		/*
		ob_start();
		?>
			<label>
				<input type="checkbox" value="GEODATA" name="GEODATA" id="loc-i-additional-geocoords" class="bx-ui-loc-i-additional" checked />
				<?=Loc::getMessage('SALE_SLI_EXTRA_GEOCOORDS')?>
			</label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_extradata_geo',
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);
		*/

		//////////////////////////////////////
		// additional data heading

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'heading_import_additionaldata',
			'name' => Loc::getMessage('SALE_SLI_ADDITIONAL_PARAMS'),
			'type' => 'section'
		);

		//////////////////////////////////////
		// additional data: import depth

		ob_start();
		?>
			<select name="DEPTH_LIMIT" class="bx-ui-loc-i-option">
				<option value="">-- <?=Loc::getMessage('SALE_SLI_DONT_LIMIT_LOCATION_DEPTH')?></option>
				<?foreach($arResult['TYPE_LEVELS'] as $id => $level):?>
					<option value="<?=$id?>"<?=($level['DEFAULT']? ' selected': '')?>><?=htmlspecialcharsbx($level['NAMES'])?></option>
				<?endforeach?>
			</select>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_depth',
			'name' => Loc::getMessage('SALE_SLI_LOAD_LOCATIONS_TILL_INCLUSIVELY'),
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);

		//////////////////////////////////////
		// additional data: pack type

		ob_start();
		?>
			<label>
				<input type="radio" name="PACK" value="standard" class="bx-ui-loc-i-option" checked />
				<?=Loc::getMessage('SALE_SLI_LOCATION_PACK_STANDARD')?>
			</label>
			<br />
			<label>
				<input type="radio" name="PACK" value="extended" class="bx-ui-loc-i-option" />
				<?=Loc::getMessage('SALE_SLI_LOCATION_PACK_EXTENDED')?>
			</label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_pack',
			'name' => Loc::getMessage('SALE_SLI_LOCATION_PACK'),
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);

		if(in_array(LANGUAGE_ID, array('ru', 'ua', 'de')))
		{
			//////////////////////////////////////
			// additional data: exclude country district

			ob_start();
			?>
				<label>
					<input type="checkbox" value="1" name="EXCLUDE_COUNTRY_DISTRICT" class="bx-ui-loc-i-option" />
					<?=Loc::getMessage('SALE_SLI_EXCLUDE_AREAS')?>
				</label>
			<?
			$customHtml = ob_get_contents();
			ob_end_clean();

			$arTabs['tab_params']['fields'][] = array(
				'id' => 'import_exclude_areas',
				'type' => 'custom',
				'value' => $customHtml,
				'class' => $remoteClassName
			);
		}

		//////////////////////////////////////
		// additional data: cleanup before

		ob_start();
		?>
			<label>
				<input type="checkbox" value="1" name="DROP_ALL" class="bx-ui-loc-i-option" id="loc-i-option-drop-all" />
				<?=Loc::getMessage('SALE_SLI_AP_DROP_STRUCTURE')?>
			</label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_cleanup_before',
			'type' => 'custom',
			'value' => $customHtml,
			'class' => $remoteClassName
		);

		//////////////////////////////////////
		// additional data: time limit

		ob_start();
		?>
			<input type="text" name="TIME_LIMIT" value="20" class="bx-ui-loc-i-option" />
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_time_limit',
			'name' => Loc::getMessage('SALE_SLI_AP_TIMELIMIT'),
			'type' => 'custom',
			'value' => $customHtml
		);

		/////////////////////////////////////////
		// additional data: preserve integrity

		ob_start();
		?>
			<label>
				<input type="checkbox" value="1" name="INTEGRITY_PRESERVE" class="bx-ui-loc-i-option" id="loc-i-option-integrity-preserve" checked />
				<?=Loc::getMessage('SALE_SLI_AP_PRESERVE_INTEGRITY')?>
			</label>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_params']['fields'][] = array(
			'id' => 'import_integrity',
			'type' => 'custom',
			'value' => $customHtml
		);

		/////////////////////////////////////////
		// delete all form

		ob_start();
		?>
			<form action="<?=$arResult['URLS']['IMPORT']?>" method="post" class="bx-ui-loc-i-delete-all-form">
				<div class="adm-btn-wrapper">
					<input type="submit" value="<?=Loc::getMessage('SALE_SLI_REMOVE_ALL')?>" class="adm-btn-save bx-ui-loc-i-delete-all" />
					<input type="hidden" name="DROP_ALL" value="1" />
					<div class="adm-btn-load-img-green"></div>
				</div>
			</form>
		<?
		$customHtml = ob_get_contents();
		ob_end_clean();

		$arTabs['tab_cleanup']['fields'][] = array(
			'id' => 'import_cleanup',
			'type' => 'custom',
			'value' => $customHtml
		);

		/////////////////////////////////////////
		// custom buttons

		ob_start();
		?>

		<input type="submit" class="adm-btn-save bx-ui-loc-i-button-start" value="<?=Loc::getMessage('SALE_SLI_START')?>">

		<?
		$formCustomHtml = ob_get_contents();
		ob_end_clean();

		$APPLICATION->IncludeComponent(
			'bitrix:main.interface.form',
			'',
			array(
				'FORM_ID' => 'PUBLIC_LOCATION_IMPORT',
				'TABS' => $arTabs,
				'BUTTONS' => array(
					'standard_buttons' => false,
					'custom_html' => $formCustomHtml
				),
				'DATA' => $arResult['LOC'],
				'SHOW_SETTINGS' => 'N',
				'SHOW_FORM_TAG' => 'N',
				'CAN_EXPAND_TABS' => 'N'
			),
			$component,
			array('HIDE_ICONS' => 'Y')
		);
		?>

	</div>

	<script>
		BX.locationImport = new BX.locationImport(<?=CUtil::PhpToJSObject(array(

			// common
			'url' => $arResult['URLS']['IMPORT_AJAX'],
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

	<div class="bx-sli-note">
		<?=Loc::getMessage('SALE_SLI_HEAVY_DUTY_NOTICE')?>
	</div>

<?endif?>