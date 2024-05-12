<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
	'date',
]);


$presets = isset($arParams['~FILTER_PRESETS']) ? $arParams['~FILTER_PRESETS'] : array();
$savedItems = isset($arResult['OPTIONS'])
	&& isset($arResult['OPTIONS']['filters'])
	&& is_array($arResult['OPTIONS']['filters'])
	? $arResult['OPTIONS']['filters'] : array();

//HACK: Setup filter omitted names (me be lost if preset changed by user)
if(isset($savedItems['filter_default']))
{
	unset($savedItems['filter_default']);
}

foreach($savedItems as $itemKey => &$item)
{
	if(!isset($item['name']) && isset($presets[$itemKey]))
	{
		$preset = $presets[$itemKey];
		$item['name'] = isset($preset['name']) ? $preset['name'] : $itemKey;
	}
}
unset($item);

$fields = isset($arParams['FILTER']) ? $arParams['FILTER'] : array();
$values = isset($arResult['FILTER']) ? $arResult['FILTER'] : array();

$infos = array();
//Visibility for default filter
$visibilityMap = isset($arResult['FILTER_ROWS']) ? $arResult['FILTER_ROWS'] : array();

$gridID = $arParams['GRID_ID'];
$gridIDLc = mb_strtolower($gridID);
$filterID = "{$gridID}_FILTER";
$formName = "filter_{$gridID}";
$containerID = "flt_wrapper_{$gridIDLc}";
$fieldContainerPrefix = "flt_field_{$gridIDLc}_";
$fieldDelimiterContainerPrefix = "flt_field_delim_{$gridIDLc}_";
$tabPrefix = "flt_tab_{$gridIDLc}_";

$filterInfo = isset($arParams['FILTER_INFO']) ? $arParams['FILTER_INFO'] : array();
$isFilterApplied = isset($filterInfo['IS_APPLIED']) ? $filterInfo['IS_APPLIED'] : false;
$currentFilterID = isset($filterInfo['ID']) ? $filterInfo['ID'] : '';
if($currentFilterID !== '' && isset($savedItems[$currentFilterID]))
{
	$currentFilter = $savedItems[$currentFilterID];
	$filterVisibilityMap = array();
	$filterVisibileRows = explode(',', isset($currentFilter['filter_rows']) ? $currentFilter['filter_rows'] : '');
	foreach($filterVisibileRows as $fieldID)
	{
		$fieldID = trim($fieldID);
		if($fieldID !== '')
		{
			$filterVisibilityMap[$fieldID] = true;
		}
	}

	if(empty($filterVisibilityMap))
	{
		$filterVisibilityMap = array();
		$filterFieldIDs = array_keys(isset($currentFilter['fields']) ? $currentFilter['fields'] : array());
		foreach($filterFieldIDs as $filterFieldID)
		{
			//We have to remove filter suffixes from field ID.
			$fieldID = preg_replace('/_[a-z]+$/', '', $filterFieldID);
			$filterVisibilityMap[$fieldID] = true;
		}
	}
	$visibilityMap = $filterVisibilityMap;
}

$visibileFieldCount = 0;
foreach($visibilityMap as $fieldVisibility)
{
	if($fieldVisibility)
	{
		$visibileFieldCount++;
	}
}

$options = CUserOptions::GetOption('bizproc.interface.grid.filter', mb_strtolower($filterID));
if(!$options)
{
	$options = array(
		'rows' => '',
		'presetsDeleted' => '',
		'isFolded' => 'Y'
	);
}

$isFilterFolded = $options['isFolded'] !== 'N';
$presetsDeleted = isset($options['presetsDeleted']) ? explode(',', $options['presetsDeleted']) : array();

if(!function_exists('__BizprocInterfaceFilterRenderField'))
{
	function __BizprocInterfaceFilterRenderField(&$field, &$values, &$infos, $options = array())
	{
		if(!is_array($options))
		{
			$options = array();
		}

		$fieldID = $field['id'];
		$fieldIDEnc = htmlspecialcharsbx($fieldID);


		$infos[$fieldID] = array(
			'id' => $fieldID,
			'name' => isset($field['name']) ? $field['name'] : $fieldID,
			'type' => isset($field['type']) ? $field['type'] : '',
			'params' => isset($field['params']) ? $field['params'] : array(),
			'isVisible' =>  isset($options['IS_VISIBLE']) ? $options['IS_VISIBLE'] : false
		);

		//Setup default attributes
		if(!isset($field['params']) || !is_array($field['params']))
		{
			$field['params'] = array();
		}

		if($field['type'] == '' || $field['type'] == 'text')
		{
			if($field['params']['size'] == '')
			{
				$field['params']['size'] = '30';
			}
		}
		elseif($field['type'] == 'date')
		{
			if (empty($field['params']['size']))
			{
				$field['params']['size'] = '10';
			}
		}
		elseif($field['type'] == 'number')
		{
			if($field['params']['size'] == '')
			{
				$field['params']['size'] = '8';
			}
		}

		$params = '';
		foreach($field['params'] as $p => $v)
		{
			$params .= ' '.$p.'="'.$v.'"';
		}
		$params = htmlspecialcharsbx($params);

		$value = isset($values[$fieldID]) ? $values[$fieldID] : '';

		switch($field["type"])
		{
			case 'custom':
				{
					$wrapperClass = mb_strpos($fieldID, 'UF_') === 0 ? 'bx-user-field-wrap' : 'bx-input-wrap';
					echo '<div class="', $wrapperClass,'">',
						isset($field['value']) ? $field['value'] : '',
						'</div>';
				}
				break;
			case 'checkbox':
				{
					echo '<div class="bx-input-wrap">',
						'<input type="hidden" name="', $fieldIDEnc, '" value="N"/>',
						'<input class="filter-checkbox" type="checkbox" id="', $fieldIDEnc,
						'" name="', $fieldIDEnc,
						'" value="Y"',
						$value == 'Y' ? ' checked="checked"' : '',
						$params, '/>',
						'</div>';
				}
				break;
			case 'list':
				{
					if(!is_array($value))
					{
						$value = array($value);
					}

					$opts = isset($field['items']) && is_array($field['items']) ? $field['items'] : array();
					$isMultiple = isset($field['params']['multiple']);
					if(!$isMultiple)
					{
						echo '<span class="bx-select-wrap">',
							'<select class="bx-select" id="', $fieldIDEnc, '" name="', $fieldIDEnc,'"',
							$params, '>';

						$alreadySelected = '';
						foreach($opts as $k => $v)
						{
							$isSelected = !$alreadySelected && in_array($k, $value);
							if($isSelected)
							{
								$alreadySelected = true;
							}

							echo '<option value="', htmlspecialcharsbx($k), '"',
								$isSelected ? ' selected="selected"' : '', '>',
								htmlspecialcharsbx($v), '</option>';
						}
						unset($option);
						echo '</select></span>';
					}
					else
					{
						echo '<span class="bx-select-wrap-multiple">',
							'<select class="bx-select-multiple" id="', $fieldIDEnc, '" name="', $fieldIDEnc,'[]"',
							$params, '>';

						$isSelected = $value[0] == '';
						echo '<option value=""',
							$isSelected ? ' selected="selected"' : '', '>',
							htmlspecialcharsbx(GetMessage("BP_INTERFACE_FILTER_LIST_VALUE_NOT_SELECTED")), '</option>';

						foreach($opts as $k => $v)
						{
							$isSelected = in_array($k, $value);
							echo '<option value="', htmlspecialcharsbx($k), '"',
								$isSelected ? ' selected="selected"' : '', '>',
								htmlspecialcharsbx($v), '</option>';
						}
						unset($option);
						echo '</select></span>';
					}
				}
				break;
			case 'date':
				{
					$dateSelectorID = "{$fieldID}_datesel";
					$dateSelectorValue = isset($values[$dateSelectorID]) ? $values[$dateSelectorID] : '';
					echo '<span class="bx-select-wrap">',
						'<select class="bx-select bx-filter-date-interval-select" id="', htmlspecialcharsbx($dateSelectorID), '" name="', htmlspecialcharsbx($dateSelectorID),'"',
						'>';
					if(isset($options['DATE_FILTER']))
					{
						foreach($options['DATE_FILTER'] as $k => $v)
						{
							echo '<option value="', htmlspecialcharsbx($k), '"',
								($dateSelectorValue === $k) ? ' selected="selected"' : '', '>',
								htmlspecialcharsbx($v), '</option>';
						}
					}
					echo '</select></span>';

					$dayInputID = "{$fieldID}_days";
					$dateInputValue = isset($values[$dayInputID]) ? $values[$dayInputID] : '';
					echo '<div class="bx-input-wrap bx-filter-date-days" style="display:none;">',
						'<input type="text" class="bx-input"',
						' name="', htmlspecialcharsbx($dayInputID), '"',
						' value="',  htmlspecialcharsbx($dateInputValue), '"',
						'/></div>';

					$fromInputID = "{$fieldID}_from";
					$fromInputValue = isset($values[$fromInputID]) ? $values[$fromInputID] : '';

					echo '<div class="bx-input-wrap bx-filter-calendar-inp bx-filter-calendar-first bx-filter-date-from" style="display:none;">',
						'<input type="text" class="bx-input bx-input-date"',
						' name="', htmlspecialcharsbx($fromInputID), '"',
						' value="',  htmlspecialcharsbx($fromInputValue), '"',
						'/>';

					echo '<span class="bx-calendar-icon"></span>';
					echo '</div>';

					echo '<span class="bx-filter-calendar-separate" style="display:none;"></span>';

					$toInputID = "{$fieldID}_to";
					$toInputValue = isset($values[$toInputID]) ? $values[$toInputID] : '';

					echo '<div class="bx-input-wrap bx-filter-calendar-inp bx-filter-calendar-first bx-filter-date-to" style="display:none;">',
						'<input type="text" class="bx-input bx-input-date"',
						' name="', htmlspecialcharsbx($toInputID), '"',
						' value="',  htmlspecialcharsbx($toInputValue), '"',
						'/>';

					echo '<span class="bx-calendar-icon"></span>';
					echo '</div>';
				}
				break;
			case 'quick':
				{
					$selectorID = "{$fieldID}_list";
					echo '<div class="bx-input-wrap">',
						'<input type="text" class="bx-input"',
						' id="', $fieldIDEnc, '"',
						' name="', $fieldIDEnc, '"',
						' value="',  htmlspecialcharsbx($value), '"',
						$params, '/></div>';
					echo '<span class="bx-select-wrap">',
						'<select class="bx-select" id="', htmlspecialcharsbx($selectorID), '" name="', htmlspecialcharsbx($selectorID), '"',
						'>';
					$opts = isset($field['items']) && is_array($field['items']) ? $field['items'] : array();
					foreach($opts as $k => $v)
					{
						$isSelected = isset($values[$selectorID]) && $values[$selectorID] == $k;
						echo '<option value="', htmlspecialcharsbx($k), '"',
							$isSelected ? ' selected="selected"' : '', '>',
							htmlspecialcharsbx($v), '</option>';
					}
					echo '</select></span>';
				}
				break;
			case 'number':
				{
					$headName = "{$fieldID}_from";
					echo '<div class="bx-input-wrap">',
						'<input type="text" class="bx-input"',
						' id="', $fieldIDEnc, '"',
						' name="', htmlspecialcharsbx($headName), '"',
						' value="',  isset($values[$headName]) ? htmlspecialcharsbx($values[$headName]) : '' , '"',
						$params, '/></div>';

					echo '<span class="bx-filter-text-wrap">&hellip;</span>';

					$tailName = "{$fieldID}_to";
					echo '<div class="bx-input-wrap">',
						'<input type="text" class="bx-input"',
						//' id="', $fieldIDEnc, '"',
						' name="', htmlspecialcharsbx($tailName), '"',
						' value="',  isset($values[$headName]) ? htmlspecialcharsbx($values[$tailName]) : '' , '"',
						$params, '/></div>';
				}
				break;
			case 'user':
				$filterID = $fieldIDEnc;
				$nameFormat = CSite::GetNameFormat(false);
				$userID = (int)$value;
				$userName = '';
				if ($userID > 0)
				{
					$dbUser = CUser::GetList(
						'id',
						'asc',
						array('ID'=> $userID),
						array(
							'FIELDS'=> array(
								'ID',
								'LOGIN',
								'EMAIL',
								'NAME',
								'LAST_NAME',
								'SECOND_NAME'
							)
						)
					);
					$user = $dbUser ? $dbUser->Fetch() : null;
					if (is_array($user))
					{
						$userName = CUser::FormatName($nameFormat, $user, true, false);
					}
				}

				$ID = "{$filterID}_SEARCH";
				$searchInputID = "{$filterID}_NAME";
				$searchInputName = "{$filterID}_name";

				$dataInputID = $filterID;
				$dataInputName = $filterID;

				$componentName = "{$filterID}_SEARCH".uniqid();
				$siteID = SITE_ID;

				$user = array('ID' => $userID, 'NAME' => $userName);
				$zIndex = 0;

				echo '<div class="bx-input-wrap"><span class="bizproc-user-search-filter-clean"></span><input type="text" class="bx-input" id="', htmlspecialcharsbx($searchInputID) ,'" name="', htmlspecialcharsbx($searchInputName), '" autocomplete="off">',
				'<input type="hidden" id="', htmlspecialcharsbx($dataInputID),'" name="', htmlspecialcharsbx($dataInputName), '" value=""></div>';

				$delay = 100;

				echo '<script>',
				'BX.ready(function(){',
				'BX.CrmUserSearchPopup.deletePopup("', $ID, '");',
				'BX.CrmUserSearchPopup.create("', $ID, '", { searchInput: BX("', CUtil::JSEscape($searchInputID), '"), dataInput: BX("', CUtil::JSEscape($dataInputID),'"), componentName: "', CUtil::JSEscape($componentName),'", user: ', CUtil::PhpToJSObject(array_change_key_case($user, CASE_LOWER)) ,', zIndex: ', $zIndex,' }, ', $delay,');',
				'}); </script>';

				$GLOBALS['APPLICATION']->IncludeComponent(
					'bitrix:intranet.user.selector.new',
					'',
					array(
						'MULTIPLE' => 'N',
						'NAME' => $componentName,
						'INPUT_NAME' => $searchInputID,
						'SHOW_EXTRANET_USERS' => 'NONE',
						'POPUP' => 'Y',
						'SITE_ID' => $siteID,
						'NAME_TEMPLATE' => $nameFormat
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);

				break;
			default:
				{
					echo '<div class="bx-input-wrap">',
						'<input type="text" class="bx-input"',
						' id="', $fieldIDEnc, '"',
						' name="', $fieldIDEnc, '"',
						' value="',  htmlspecialcharsbx($value), '"',
						$params, '/></div>';
				}
		}
	}
}

$isHidden = isset($arParams['HIDE_FILTER']) ? $arParams['HIDE_FILTER'] : false;
?><form name="<?=htmlspecialcharsbx($formName)?>" action="" method="GET">
	<?
	foreach($arResult["GET_VARS"] as $var=>$value):
		if (in_array($var, array('grid_filter_id', 'apply_filter', 'save')))
			continue;
		if(is_array($value)):
			foreach($value as $k=>$v):
				if(is_array($v))
					continue;
				?>
				<input type="hidden" name="<?=htmlspecialcharsbx($var)?>[<?=htmlspecialcharsbx($k)?>]" value="<?=htmlspecialcharsbx($v)?>">
			<?
			endforeach;
		else:
			?>
			<input type="hidden" name="<?=htmlspecialcharsbx($var)?>" value="<?=htmlspecialcharsbx($value)?>">
		<?
		endif;
	endforeach;
	if (!empty($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"])):?>
	<input type="hidden" name="SEF_APPLICATION_CUR_PAGE_URL" value="<?=htmlspecialcharsbx($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"])?>">
	<?endif?>
	<div class="bp-main-wrap-flat"<?=$isHidden ? ' style="display:none;"' : ''?>>
		<div id="<?=htmlspecialcharsbx($containerID)?>" class="bx-filter-wrap">
			<div class="bx-filter-wrap<?=$isFilterApplied ? ' bx-current-filter' : ''?><?=$isFilterFolded ? ' bx-filter-folded' : ''?>">
				<table class="bx-filter-main-table">
					<tr>
						<td class="bx-filter-main-table-cell">
							<div class="bx-filter-tabs-block" id="filter-tabs"><?
								$isActive = !$isFilterFolded
									? (!$isFilterApplied || $currentFilterID === '')
									: ($isFilterApplied && $currentFilterID === '');
								?><span id="<?=htmlspecialcharsbx("{$tabPrefix}filter_default")?>" class="bx-filter-tab<?=$isActive ? ' bx-filter-tab-active' : ''?><?=$isFilterApplied && $currentFilterID === '' ? ' bx-current-filter-tab' : ''?>"><?= GetMessage('BP_INTERFACE_FILTER_CURRENT') ?></span><?
								foreach($savedItems as $itemID => &$item):
									if(!in_array($itemID, $presetsDeleted, true)):
										$isActive = $isFilterApplied && $currentFilterID === $itemID;
										?><span id="<?=htmlspecialcharsbx("{$tabPrefix}{$itemID}")?>" class="bx-filter-tab<?=$isActive ? ' bx-filter-tab-active bx-current-filter-tab' : ''?>"><?= htmlspecialcharsbx($item['name'])?></span><?
									endif;
								endforeach;
								unset($item);
								?><span class="bx-filter-tab bx-filter-add-tab" title="<?=htmlspecialcharsbx(GetMessage('BP_INTERFACE_FILTER_ADD'))?>"></span>
								<span class="bx-filter-switcher-tab">
									<span class="bx-filter-switcher-tab-icon"></span>
								</span>
								<span class="bx-filter-tabs-block-underlay"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td class="bx-filter-main-table-cell">
							<div id="<?= $containerID ?>-block" class="bx-filter-content<?=$visibileFieldCount > 1 ? '' : ' bx-filter-content-first'?>"<?=$isFilterFolded ? ' style="height: 0;"' : ''?>>
								<div id="<?= $containerID ?>-inner" class="bx-filter-content-inner">
									<div class="bx-filter-content-table-wrap">
										<table class="bx-filter-content-table"><?
										foreach($fields as &$field):
											$fieldID = $field['id'];
											$fieldContainerID = "{$fieldContainerPrefix}{$fieldID}";
											$delimiterContainerID = "{$fieldDelimiterContainerPrefix}{$fieldID}";
											$isVisible = isset($visibilityMap[$fieldID]) ? $visibilityMap[$fieldID] : false;
											?><tr class="bx-filter-item-row" id="<?=htmlspecialcharsbx($fieldContainerID)?>"<?=$isVisible ? '' : ' style="display:none;"'?>>
												<td class="bx-filter-item-left"><?=htmlspecialcharsbx(isset($field['name']) ? $field['name'] : $fieldID)?>:</td>
												<td class="bx-filter-item-center">
													<div class="bx-filter-alignment">
														<div class=" bx-filter-box-sizing"><?
															__BizprocInterfaceFilterRenderField(
																$field,
																$values,
																$infos,
																array(
																	'IS_VISIBLE' => $isVisible,
																	'DATE_FILTER' => isset($arResult['DATE_FILTER']) ? $arResult['DATE_FILTER'] : null,
																	'FORM_NAME' => $formName,
																	'COMPONENT' => $component
																)
															);
														?></div>
													</div>
												</td>
												<td class="bx-filter-item-right">
													<span class="bx-filter-item-delete"<?=$visibileFieldCount > 1 ? '' : ' style="display:none;"'?>></span>
												</td>
											</tr>
											<tr id="<?=htmlspecialcharsbx($delimiterContainerID)?>"<?=$isVisible ? '' : ' style="display:none;"'?>>
												<td class="delimiter" colspan="3">
													<div class="empty"></div>
												</td>
											</tr><?
										endforeach;
										unset($field);
										?></table>
									</div>
									<div class="bx-filter-bottom-separate"<?=$visibileFieldCount > 1 ? '' : ' style="display:none;"'?>></div>
									<div class="bx-filter-bottom">
										<input value="<?=htmlspecialcharsbx(GetMessage('BP_INTERFACE_FILTER_FIND'))?>" name="set_filter" type="button"/>
										<input value="<?=htmlspecialcharsbx(GetMessage('BP_INTERFACE_FILTER_CANCEL'))?>" name="reset_filter" type="button"/>
										<input value="" name="grid_filter_id" type="hidden"/>
										<input value="" name="apply_filter" type="hidden"/>
										<input value="" name="clear_filter" type="hidden"/>
										<div class="bx-filter-setting-block">
											<span class="bx-filter-setting" title="<?=htmlspecialcharsbx(GetMessage('BP_INTERFACE_FILTER_SETTINGS'))?>"></span>
											<span class="bx-filter-add-button" title="<?=htmlspecialcharsbx(GetMessage('BP_INTERFACE_FILTER_ADD_FIELD'))?>"></span>
										</div>
									</div>
								</div>
							<div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</form><?

//Prepare default rows
$filterRows = isset($arResult['OPTIONS']['filter_rows']) ? $arResult['OPTIONS']['filter_rows'] : '';
if(!(is_string($filterRows) && $filterRows !== ''))
{
	$fieldIDs = array();
	foreach($fields as &$field)
	{
		$fieldID = $field['id'];
		if(isset($visibilityMap[$fieldID]) && $visibilityMap[$fieldID])
		{
			$fieldIDs[] = $fieldID;
		}
	}
	unset($field);
	$filterRows = implode(',', $fieldIDs);
}

?><script>
	BX.ready(
			function()
			{
				BX.InterfaceGridFilter.messages =
					{
						"showAll": "<?=GetMessageJS("BP_INTERFACE_FILTER_SHOW_ALL")?>",
						"hideAll": "<?=GetMessageJS("BP_INTERFACE_FILTER_HIDE_ALL")?>",
						"saveAs": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE_AS")?>",
						"save": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE")?>",
						"delete": "<?=GetMessageJS("BP_INTERFACE_FILTER_DELETE")?>",
						"saveAsDialogTitle": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE_AS_DIALOG_TITLE")?>",
						"saveAsDialogFieldName": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE_AS_DIALOG_FIELD_NAME")?>",
						"defaultFilterName": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE_AS_DIALOG_FIELD_NAME_DEFAULT")?>",
						"buttonSave": "<?=GetMessageJS("BP_INTERFACE_FILTER_SAVE")?>",
						"buttonCancel": "<?=GetMessageJS("BP_INTERFACE_FILTER_CANCEL")?>",
						"buttonMinimize": "<?=GetMessageJS("BP_INTERFACE_FILTER_MINIMIZE")?>",
						"buttonMaximize": "<?=GetMessageJS("BP_INTERFACE_FILTER_MAXIMIZE")?>",
						"buttonDeleteField": "<?=GetMessageJS("BP_INTERFACE_FILTER_DELETE_FIELD")?>"
					};

				var filter = BX.InterfaceGridFilter.create(
						"<?=CUtil::JSEscape($filterID)?>",
						BX.BizprocParamBag.create(
								{
									"gridId": "<?=CUtil::JSEscape($gridID)?>",
									"serviceUrl": "<?='/bitrix/components/bitrix/main.interface.grid/settings.php?'.bitrix_sessid_get()?>",
									"containerId": "<?=CUtil::JSEscape($containerID)?>",
									"mainBlock": "<?= $containerID ?>-block",
									"innerBlock": "<?= $containerID ?>-inner",
									"formName": "<?=CUtil::JSEscape($formName)?>",
									"fieldContainerPrefix": "<?=CUtil::JSEscape($fieldContainerPrefix)?>",
									"fieldDelimiterContainerPrefix": "<?=CUtil::JSEscape($fieldDelimiterContainerPrefix)?>",
									"itemContainerPrefix": "<?=CUtil::JSEscape($tabPrefix)?>",
									"currentTime": <?=(time() + date('Z') + CTimeZone::GetOffset())?>,
									"fieldInfos": <?=CUtil::PhpToJSObject($infos)?>,
									"itemInfos":<?=CUtil::PhpToJSObject($savedItems)?>,
									"isApplied":<?=$isFilterApplied ? 'true' : 'false'?>,
									"currentValues":<?=CUtil::PhpToJSObject($values)?>,
									"currentItemId": "<?=CUtil::JSEscape($currentFilterID === '' ? 'filter_default' : $currentFilterID)?>",
									"defaultItemId": "filter_default",
									"defaultVisibleRows": "<?=$filterRows?>",
									"isFolded": <?=$isFilterFolded ? 'true' : 'false'?>,
									"presetsDeleted": <?=CUtil::PhpToJSObject($presetsDeleted)?>
								}
						)
					);
			}
		);
</script>