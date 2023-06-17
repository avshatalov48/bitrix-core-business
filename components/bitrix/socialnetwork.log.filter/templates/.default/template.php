<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Socialnetwork\LogViewTable;

UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.buttons",
]);

if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
	$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-paddings pagetitle-toolbar-field-view');
}

$this->setFrameMode(true);

if (
	!is_array($arResult["PresetFilters"])
	&& !(
		array_key_exists("SHOW_SETTINGS_LINK", $arParams)
		&& $arParams["SHOW_SETTINGS_LINK"] === "Y"
	)
)
{
	return;
}

$isFiltered = false;
foreach (array("flt_created_by_id", "flt_group_id", "flt_to_user_id", "flt_date_datesel", "flt_show_hidden", "CREATED_BY_CODE", "TO_CODE") as $param)
{
	if (
		array_key_exists($param, $_GET)
		&& (
			(
				is_array($_GET[$param])
				&& !empty($_GET[$param])
			)
			|| (
				!is_array($_GET[$param])
				&& $_GET[$param] <> ''
				&& $_GET[$param] !== "0"
			)
		)
	)
	{
		$isFiltered = true;
		break;
	}
}

if (!is_array($arResult["PageParamsToClear"]))
{
	$arResult["PageParamsToClear"] = array();
}

if ($arResult["MODE"] === "AJAX")
{
	$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/main.post.form/templates/.default/style.css');

	ob_end_clean();
	$APPLICATION->RestartBuffer();
	?>
	<script>
		BX.ready(function(){
			oLFFilter.initFilter();
		});
	</script>
	<div id="sonet-log-filter" class="sonet-log-filter-block">
		<div class="log-filter-title"><?=GetMessage("SONET_C30_T_FILTER_TITLE")?></div>
		<form class="log-filter-form" method="GET" name="log_filter" target="_self" action="<?=POST_FORM_ACTION_URI?>">
		<input type="hidden" name="SEF_APPLICATION_CUR_PAGE_URL" value="<?=GetPagePath()?>"><?php
		?><div class="log-filter-field">
			<label class="log-filter-field-title" for="log-filter-field-created-by"><?= Loc::getMessage('SONET_C30_T_FILTER_CREATED_BY') ?></label>
			<div class="feed-add-post-destination-wrap feed-add-post-destination-filter" id="sonet-log-filter-created-by">
				<span id="sonet-log-filter-created-by-item"></span>
				<span class="feed-add-destination-input-box" style="display: inline-block;">
					<input type="text" value="" class="feed-add-destination-inp" id="filter-field-created-by" autocomplete="off">
				</span>
			</div>
		</div>
		<script>
			oLFFilter.initDestination({
				pathToAjax: '<?=CUtil::JSEscape($this->getComponent()->getPath())?>/post.ajax.php',
				userNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
				name: 'feed-filter-created-by',
				inputName: 'filter-field-created-by',
				inputContainerName: 'sonet-log-filter-created-by-item',
				resultFieldName: 'CREATED_BY_CODE',
				extranetUser: <?=($arResult["bExtranetUser"] ? 'true' : 'false')?>,
				bindNode: BX('sonet-log-filter-created-by'),
				departmentSelectDisable: true,
				items: {
					users: <?=(empty($arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"]))?>,
					department : <?=(empty($arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT']) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT']))?>,
					extranetRoot : <?=(empty($arResult["CREATED_BY_DEST"]["EXTRANET_ROOT"]) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["EXTRANET_ROOT"]))?>
				},
				itemsLast: {
					users: <?=(empty($arResult["CREATED_BY_DEST"]["LAST"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["LAST"]["USERS"]))?>
				},
				itemsSelected : <?=(empty($arResult["CREATED_BY_DEST"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]['SELECTED']))?>,
				destSort: <?=(empty($arResult["CREATED_BY_DEST"]["SORT"]) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["SORT"]))?>
			});
		</script>
		<?php
		$bChecked = (array_key_exists("flt_comments", $_REQUEST) && $_REQUEST["flt_comments"] === "Y");

		?><div class="log-filter-field" id="flt_comments_cont" style="display: <?=((int)$arParams["CREATED_BY_ID"] > 0 ? "block" : "none")?>"><input type="checkbox" class="filter-checkbox" id="flt_comments" name="flt_comments" value="Y" <?=($bChecked ? "checked" : "")?>> <label class="log-filter-field-title log-filter-field-title-checkbox" for="flt_comments"><?=GetMessage("SONET_C30_T_FILTER_COMMENTS")?></label></div><?php
		?><div class="log-filter-field">
			<label class="log-filter-field-title" for="log-filter-field-to"><?= Loc::getMessage('SONET_C30_T_FILTER_TO') ?></label>
			<div class="feed-add-post-destination-wrap feed-add-post-destination-filter" id="sonet-log-filter-to">
				<span id="sonet-log-filter-to-item"></span>
				<span class="feed-add-destination-input-box" style="display: inline-block;">
					<input type="text" value="" class="feed-add-destination-inp" id="filter-field-to" autocomplete="off">
				</span>
			</div>
		</div>
		<script>
			oLFFilter.initDestination({
				pathToAjax: '<?=CUtil::JSEscape($this->getComponent()->getPath())?>/post.ajax.php',
				userNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
				name: 'feed-filter-to',
				inputName: 'filter-field-to',
				inputContainerName: 'sonet-log-filter-to-item',
				resultFieldName: 'TO_CODE',
				extranetUser: <?=($arResult["bExtranetUser"] ? 'true' : 'false')?>,
				bindNode: BX('sonet-log-filter-to'),
				items: {
					users: <?=(empty($arResult["TO_DEST"]["ITEMS"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]["USERS"]))?>,
					sonetgroups : <?=(empty($arResult["TO_DEST"]["ITEMS"]["SONETGROUPS"])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]["SONETGROUPS"]))?>,
					department : <?=(empty($arResult["TO_DEST"]["ITEMS"]['DEPARTMENT']) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]['DEPARTMENT']))?>,
					extranetRoot : <?=(empty($arResult["TO_DEST"]["EXTRANET_ROOT"]) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["EXTRANET_ROOT"]))?>
				},
				itemsLast: {
					users: <?=(empty($arResult["TO_DEST"]["LAST"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["LAST"]["USERS"]))?>,
					sonetgroups: <?=(empty($arResult["TO_DEST"]["LAST"]["SONETGROUPS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["LAST"]["SONETGROUPS"]))?>,
					department : <?=(empty($arResult["TO_DEST"]['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]['LAST']['DEPARTMENT']))?>
				},
				itemsSelected : <?=(empty($arResult["TO_DEST"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]['SELECTED']))?>,
				itemsSelectedUndeleted : <?=(
					!empty($arResult["TO_DEST"]['SELECTED'])
					&& isset($arParams["GROUP_ID"])
					&& (int)$arParams["GROUP_ID"] > 0
					&& array_key_exists('SG'.$arParams["GROUP_ID"], $arResult["TO_DEST"]['SELECTED'])
					&& (
						empty($arParams["DESTINATION"])
						|| !in_array('SG'.$arParams["GROUP_ID"], $arParams["DESTINATION"])
					)
						? CUtil::PhpToJSObject(array('SG'.(int)$arParams["GROUP_ID"]))
						: '{}'
				)?>,
				destSort: <?=(empty($arResult["TO_DEST"]["SORT"]) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["SORT"]))?>
			});
		</script>
		<div class="log-filter-field log-filter-field-date-combobox">
			<label for="flt-date-datesel" class="log-filter-field-title"><?= Loc::getMessage('SONET_C30_T_FILTER_DATE') ?></label>
			<span class="log-filter-field-inp-container">
				<select name="flt_date_datesel" onchange="__logOnDateChange(this)" class="log-filter-field-inp" id="flt-date-datesel"><?php
				foreach($arResult["DATE_FILTER"] as $k=>$v):
					?><option value="<?=$k?>"<?php if($_REQUEST["flt_date_datesel"] == $k) echo ' selected="selected"'?>><?=$v?></option><?php
				endforeach;
				?></select>
			</span>
			<span class="log-filter-date-interval log-filter-date-interval-after log-filter-date-interval-before">
				<span id="flt_date_day_span" style="display:none">
					<span class="log-filter-field-inp-container log-filter-day-interval">
						<input type="text" name="flt_date_days" value="<?=htmlspecialcharsbx($_REQUEST["flt_date_days"])?>" class="log-filter-date-days log-filter-field-inp" size="2" />
					</span>
				</span>
				<label class="log-filter-field-title" id="flt_date_day_text_span" style="display:none"><?php echo GetMessage("SONET_C30_DATE_FILTER_DAYS")?></label>
			</span>
			<span class="log-filter-date-interval log-filter-date-interval-after log-filter-date-interval-before">
				<span class="log-filter-field-inp-container log-filter-field-inp-date" style="display:none" id="flt_date_from_span"><?php
					?><input class="log-filter-field-inp" type="text" id="flt_date_from" name="flt_date_from" value="<?=(array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : "")?>" /><?php
					?><div style="display: none;"><?php
					$APPLICATION->IncludeComponent(
						"bitrix:main.calendar",
						"",
						array(
							"SHOW_INPUT" => "N",
							"INPUT_NAME" => "flt_date_from",
							"INPUT_VALUE" => (array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : ""),
							"FORM_NAME" => "log_filter",
							"SHOW_TIME" => "N",
							"HIDE_TIMEBAR" => "Y"
						),
						$component,
						array("HIDE_ICONS"	=> true)
					);?>
					</div>
				</span><?php
				?><span class="log-filter-date-interval-hellip" style="display:none" id="flt_date_hellip_span">&hyphen;</span><?php
				?><span class="log-filter-field-inp-container log-filter-field-inp-date" style="display:none" id="flt_date_to_span"><?php
					?><input class="log-filter-field-inp" type="text" id="flt_date_to" name="flt_date_to" value="<?=(array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : "")?>" class="log-filter-date-interval-to" /><?php
					?><div style="display: none;"><?php
					$APPLICATION->IncludeComponent(
						"bitrix:main.calendar",
						"",
						array(
							"SHOW_INPUT" => "N",
							"INPUT_NAME" => "flt_date_to",
							"INPUT_VALUE" => (array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : ""),
							"FORM_NAME" => "log_filter",
						),
						$component,
						array("HIDE_ICONS"	=> true)
					);?>
					</div>
				</span>
			</span>
		</div>

		<script type="text/javascript">
			BX.ready(function(){
				__logOnDateChange(document.forms['log_filter'].flt_date_datesel);
			});
		</script>
		<?php
		if ($arParams["SUBSCRIBE_ONLY"] === "Y")
		{
			$bChecked = (array_key_exists("flt_show_hidden", $_REQUEST) && $_REQUEST["flt_show_hidden"] === "Y");
			?><div class="log-filter-field"><input type="checkbox" class="filter-checkbox" id="flt_show_hidden" name="flt_show_hidden" value="Y" <?=($bChecked ? "checked" : "")?>> <label for="flt_show_hidden"><?=GetMessage("SONET_C30_T_SHOW_HIDDEN")?></label></div><?php
		}

		?><div class="sonet-log-filter-submit"><?php
			?><span class="popup-window-button popup-window-button-create" onclick="document.forms['log_filter'].submit();"><?php
				?><span class="popup-window-button-left"></span><?php
				?><span class="popup-window-button-text"><?=GetMessage("SONET_C30_T_SUBMIT")?></span><?php
				?><span class="popup-window-button-right"></span><?php
			?></span><?php
			?><input type="hidden" name="log_filter_submit" value="Y"><?php
			if ($isFiltered)
			{
				?><a href="<?=$APPLICATION->GetCurPageParam("preset_filter_id=".(array_key_exists("preset_filter_id", $_GET) && $_GET["preset_filter_id"] <> '' ? htmlspecialcharsbx($_GET["preset_filter_id"]) : "clearall"), array("flt_created_by_id","flt_group_id","flt_to_user_id","flt_date_datesel","flt_date_days","flt_date_from","flt_date_to","flt_date_to","flt_show_hidden","skip_subscribe","preset_filter_id","sessid","bxajaxid", "log_filter_submit", "FILTER_CREATEDBY","SONET_FILTER_MODE", "set_follow_type","CREATED_BY_CODE","TO_CODE"), false)?>" class="popup-window-button popup-window-button-link popup-window-button-link-cancel"><span class="popup-window-button-link-text"><?=GetMessage("SONET_C30_T_RESET")?></span></a><?php
			}
		?></div>
		<input type="hidden" name="skip_subscribe" value="<?=(isset($_REQUEST["skip_subscribe"]) && $_REQUEST["skip_subscribe"] === "Y" ? "Y" : "N")?>">
		<input type="hidden" name="preset_filter_id" value="<?=(array_key_exists("preset_filter_id", $_GET) ? htmlspecialcharsbx($_GET["preset_filter_id"]) : "")?>" />
		</form>
	</div><?php
	die();
}

if ($arParams["USE_TARGET"] !== "N")
{
	$pageTitle = $arParams["PAGETITLE_TARGET"] ?? '';
	$targetId = $arParams["TARGET_ID"] ?? '';
	$this->SetViewTarget((
			SITE_TEMPLATE_ID === "bitrix24"
			? ($pageTitle <> '' ? $pageTitle : "pagetitle")
			: ($targetId <> '' ? $targetId : "sonet_blog_form")
		),
		50
	);
}

$isCompositeMode = defined("BITRIX24_INDEX_COMPOSITE");
$isCompositeMode === false ?: ($dynamicArea = $this->createFrame()->begin(""));

?><script>
	var lentaMenuItems = {};

	lentaMenuItems.preset = [
		{
			text : "<?=(!empty($arResult["ALL_ITEM_TITLE"]) ? $arResult["ALL_ITEM_TITLE"] : GetMessageJS("SONET_C30_PRESET_FILTER_ALL"))?>",
			className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item lenta-sort-item-selected" : "lenta-sort-item<?=(!$arResult["PresetFilterActive"] ? " lenta-sort-item-selected" : "")?>"),
			href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("preset_filter_id=clearall", array_merge($arResult["PageParamsToClear"], array("preset_filter_id"))))?>"
		},
		<?php
		$buttonName = false;
		if (is_array($arResult["PresetFilters"]))
		{
			foreach ($arResult["PresetFilters"] as $preset_filter_id => $arPresetFilter)
			{
				if ($arResult["PresetFilterActive"] == $preset_filter_id)
				{
					$buttonName = $arPresetFilter["NAME"];
				}
				?>
				, {
					text : "<?=$arPresetFilter["NAME"]?>",
					className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item" : "lenta-sort-item<?=($arResult["PresetFilterActive"] == $preset_filter_id ? " lenta-sort-item-selected" : "")?>"),
					href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("preset_filter_id=".$preset_filter_id, array_merge($arResult["PageParamsToClear"], array("preset_filter_id"))))?>"
				}
				<?php
			}
		}
		?>
	];

	lentaMenuItems.filter = [
		{
			text : "<?=GetMessageJS("SONET_C30_T_FILTER_TITLE")?>...",
			className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item" : "lenta-sort-item<?=($isFiltered ? " lenta-sort-item-selected" : "")?>"),
			onclick: function() {
				this.popupWindow.close();
				oLFFilter.ShowFilterPopup(BX("lenta-sort-button"));
			}
		}
	];

	lentaMenuItems.actions = [
		<?php
		if ($arParams["SHOW_FOLLOW"] !== "N")
		{
			?>
			{
				text : "<?=GetMessageJS("SONET_C30_SMART_FOLLOW")?>",
				className : "lenta-sort-item<?=($arResult["FOLLOW_TYPE"] === "N" ? " lenta-sort-item-selected" : "")?>",
				onclick: function (event, obItem) {
					oLFFilter.onClickMenuItem({
						menuItem: BX(obItem.layout.item),
						href: "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("set_follow_type=".($arResult["FOLLOW_TYPE"] === "Y" ? "N" : "Y"), array("set_follow_type")))?>"
					});
				}
			},
			<?php
		}

		if (
			$arParams["SHOW_EXPERT_MODE"] !== "N"
			&& class_exists('\Bitrix\Socialnetwork\LogViewTable') // socialnetwork 16.5.0
		)
		{
			?>
			{
				text : "<?=GetMessageJS("SONET_C30_SMART_EXPERT_MODE")?>",
				className : "lenta-sort-item<?=($arResult["EXPERT_MODE"] === "Y" ? " lenta-sort-item-selected" : "")?>",
				onclick: function (event, obItem) {
					oLFFilter.onClickMenuItem({
						menuItem: BX(obItem.layout.item),
						href: "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("set_expert_mode=".($arResult["EXPERT_MODE"] === "Y" ? "N" : "Y"), array("set_expert_mode")))?>"
					});
				}
			}
			<?php
		}
		?>
	];

	BX.ready(function() {
		oLFFilter.initLentaMenu({
			menuItems: lentaMenuItems
		});
	});

	<?php
	if (
		isset($arResult["SHOW_EXPERT_MODE_POPUP"])
		&& $arResult["SHOW_EXPERT_MODE_POPUP"] === "Y"
	)
	{
		?>
		BX.ready(function() {
			setTimeout(function() {
				oLFFilter.__SLFShowInfoPopup({
					bindObj: null,
					name: 'setExpertModePopup',
					type: 'EXPERT_MODE',
					reload: true,
					title: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TITLE")?>',
					description1: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TEXT1A")?>',
					description2: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TEXT2A")?>',
					descriptionImage: {
						src: '<?=CUtil::JSEscape($this->GetFolder())?>/images/expert_mode/<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_IMAGENAME")?>.png',
						width: 354,
						height: 201
					}
				});
			}, 1000);
		});
		<?php
	}

	if (
		isset($arResult["SHOW_VIDEO_TRANSFORM_POPUP"])
		&& $arResult["SHOW_VIDEO_TRANSFORM_POPUP"] === "Y"
	)
	{
		?>
		var __SLFPopupVideoTransform = null;
		var buttonPublish = null;
		<?php
		if (!empty($arResult["VIDEO_TRANSFORM_POST_ID"]))
		{
			?>
			buttonPublish = {
				type: 'light-border',
				title: '<?=GetMessageJS("SONET_C30_F_VIDEO_TRANSFORM_POPUP_BUTTON_PUBLISH_TITLE")?>',
				click: function() {
					BX.ajax({
						method: 'POST',
						dataType: 'json',
						url: BX.message('ajaxControllerURL'),
						data: {
							sessid : BX.bitrix_sessid(),
							popupType: 'VIDEO_TRANSFORM',
							action: 'PUBLISH',
							postId: <?= (int)$arResult["VIDEO_TRANSFORM_POST_ID"] ?>,
							closePopup: 'Y'
						},
						onsuccess: function(response)
						{
							if (
								typeof (response) != 'undefined'
								&& typeof (response.SUCCESS) != 'undefined'
								&& response.SUCCESS == 'Y'
							)
							{
								__SLFPopupVideoTransform.close();
								top.location = top.location.href;
							}
						}
					});
				}
			};
			<?php
		}
		?>

		BX.ready(function() {
			setTimeout(function() {
				__SLFPopupVideoTransform = oLFFilter.__SLFShowInfoPopup({
					bindObj: null,
					name: 'startVideoTransformPopup',
					type: 'VIDEO_TRANSFORM',
					reload: false,
					title: '<?=GetMessageJS("SONET_C30_F_VIDEO_TRANSFORMATION_POPUP_TITLE")?>',
					description2: '<?=GetMessageJS("SONET_C30_F_VIDEO_TRANSFORMATION_POPUP_TEXT", array(
						"#A_START#" => '<a href="'.htmlspecialcharsbx($arResult["VIDEO_TRANSFORM_POST_URL"]).'">',
						"#A_END#" => '</a>'
					))?>',
					buttonsAdd: [
						buttonPublish
					]
				});
			}, 1000);
		});
		<?php
	}
	?>
	BX.message({
		sonetLFAjaxPath: '<?=CUtil::JSEscape($arResult["AjaxURL"])?>',
		ajaxControllerURL: '<?=CUtil::JSEscape($arResult["ajaxControllerURL"])?>',
		sonetLFAllMessages: '<?=GetMessageJS("SONET_C30_PRESET_FILTER_ALL")?>',
		sonetLFDialogClose: '<?=GetMessageJS("SONET_C30_F_DIALOG_CLOSE_BUTTON")?>',
		sonetLFDialogRead: '<?=GetMessageJS("SONET_C30_F_DIALOG_READ_BUTTON")?>'
	});
</script><?php

$isCompositeMode === false ?: $dynamicArea->end();
$logCounter = (int)$arResult["LOG_COUNTER"];

if (SITE_TEMPLATE_ID !== "bitrix24")
{
	?><div id="lenta-sort-button" class="feed-filter-btn-wrap">
	<span class="feed-filter-btn" id="feed_filter_button"><?php
		?><?=($buttonName !== false ? $buttonName : GetMessage("SONET_C30_PRESET_FILTER_ALL") )?><?=($isFiltered ? " (".GetMessageJS("SONET_C30_T_FILTER_TITLE").")" : "")?><?php
		if ($buttonName === false):
			?><i id="sonet_log_counter_preset"><?= (((int)$arResult["LOG_COUNTER"] > 0 && $arParams["ENTITY_TYPE"] !== SONET_ENTITY_GROUP) ? $arResult["LOG_COUNTER"] : "") ?></i><?php
		endif;
		?></span>
	</div>
	<script>
		BX.ready(function () {
			BX.bind(BX('feed_filter_button'), 'click', function() {
				oLFFilter.showLentaMenu({
					bindElement: BX('feed_filter_button'),
					short: false,
					siteTemplateid: '<?=CUtil::JSEscape(SITE_TEMPLATE_ID)?>'
				});
			});
		});
	</script>

	<?php
}

if ($arParams["USE_TARGET"] !== "N")
{
	$this->EndViewTarget();
}

if (SITE_TEMPLATE_ID === "bitrix24")
{
	$this->SetViewTarget('inside_pagetitle', 0);
	$filterID = ($arParams["FILTER_ID"] ?? 'LIVEFEED');

	?><div class="pagetitle-container pagetitle-flexible-space" style="overflow: hidden;" id="<?=htmlspecialcharsbx($filterID)?>_filter_container"><div id="<?=htmlspecialcharsbx($filterID)?>_filter_container_max" class="pagetitle-container-max pagetitle-container-max-rounded"><?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		array(
			'THEME' => 'ROUNDED',
			'GRID_ID' => $filterID,
			'FILTER_ID' => $filterID,
			'FILTER' => $arResult["Filter"],
			'FILTER_FIELDS' => array(),
			'FILTER_PRESETS' => $arResult['PresetFiltersNew'],
			'ENABLE_LIVE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => false,
			'ENABLE_LABEL' => true,
			'COMPACT_STATE' => (
				empty($arResult["CREATED_BY_DEST"]['SELECTED'])
				&& (
					empty($arResult["TO_DEST"]['SELECTED'])
					|| (
						isset($arParams['GROUP_ID'])
						&& (int)$arParams['GROUP_ID'] > 0
					)
				)
			),
			'CONFIG' => array(
				'AUTOFOCUS' => false,
				'POPUP_BIND_ELEMENT_SELECTOR' => '#'.htmlspecialcharsbx($filterID).'_filter_container_max',
				'POPUP_OFFSET_LEFT' => 13,
				'DEFAULT_PRESET' => false
			)
		),
		$this->getComponent()
	);
	?></div></div>
	<script>
		BX.ready(function(){
			oLFFilter.initFilter({
				version: 2,
				filterId: '<?= htmlspecialcharsbx($filterID) ?>',
				minSearchStringLength: <?= (int)$arResult["ftMinTokenSize"] ?>
			});
		});
	</script>
	<?php
	$toolbarId = 'LIVEFEED_FILTER_TOOLBAR';

	?><div id="<?=htmlspecialcharsbx($toolbarId)?>" class="pagetitle-container pagetitle-align-right-container"><?php

	if (
		$arParams["SHOW_FOLLOW"] !== "N"
		|| (
			$arParams["SHOW_EXPERT_MODE"] !== "N"
			&& class_exists(LogViewTable::class)
		)
	)
	{
		?>
		<button id="feed_filter_button" class="ui-btn ui-btn-link ui-btn-filter-button ui-btn-themes"></button>
		<script>
			BX.ready(function () {
				BX.bind(BX('feed_filter_button'), 'click', function() {
					oLFFilter.showLentaMenu({
						bindElement: BX('feed_filter_button'),
						short: true,
						siteTemplateId: '<?=CUtil::JSEscape(SITE_TEMPLATE_ID)?>'
					});
				});
			});
		</script><?php
	}
	?></div><?php
	$this->EndViewTarget();
}

$isCompositeMode === false ?: ($dynamicArea = $this->createFrame()->begin(""));

if (isset($_SESSION["SL_SHOW_FOLLOW_HINT"]))
{
	unset($_SESSION["SL_SHOW_FOLLOW_HINT"]);
	?><div id="feed_filter_hint_follow" class="feed-smart-follow-hint-wrap"><?php
	?><div class="feed-smart-follow-hint"><?php
		?><?= Loc::getMessage('SONET_C30_SMART_FOLLOW_HINT') ?><?php
		?><span class="popup-window-close-icon feed-smart-follow-hint-close" id="feed_filter_hint_follow_close"></span><?php
	?></div><?php
	?></div><?php
	?><script>
		BX.ready(function () {
			BX.bind(BX('feed_filter_hint_follow_close'), 'click', function() {
				BX.addClass(BX('feed_filter_hint_follow'), 'feed-smart-follow-hint-hidden');
			});
		});
	</script><?php
}
elseif (isset($_SESSION["SL_EXPERT_MODE_HINT"]))
{
	unset($_SESSION["SL_EXPERT_MODE_HINT"]);
	?><div id="feed_filter_hint_expert" class="feed-smart-follow-hint-wrap"><?php
	?><div class="feed-smart-follow-hint"><?php
		?><?= Loc::getMessage('SONET_C30_EXPERT_MODE_HINTA') ?><?php
		?><span class="popup-window-close-icon feed-smart-follow-hint-close" id="feed_filter_hint_expert_close"></span><?php
	?></div><?php
	?></div><?php
	?><script>
	BX.ready(function () {
		BX.bind(BX('feed_filter_hint_expert_close'), 'click', function() {
			BX.addClass(BX('feed_filter_hint_expert'), 'feed-smart-follow-hint-hidden');
		});
	});
	</script><?php
}

$isCompositeMode === false ?: $dynamicArea->end();
