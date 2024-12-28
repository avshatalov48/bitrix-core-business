<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
global $APPLICATION, $USER_FIELD_MANAGER;

$id = $arParams['id'];
$event = $arParams['event'];
$isSocialNetworkEnabled = $arParams['bSocNet'];
$isExternalUser = \Bitrix\Main\Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser();
$isCrmEnabled = \Bitrix\Main\ModuleManager::isModuleInstalled('crm');
$hiddenFields = $arParams['hiddenFields'] ?? [];

$fieldsList = [
	'description' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_DESCRIPTION_COLUMN')],
	'reminder' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_REMINDER_COLUMN')],
	'rrule' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_RRULE_COLUMN')],
	'color' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_COLOR_COLUMN')],
	'section' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_SECTIONS_COLUMN')],
	'accessibility' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_ACCESSIBILITY_COLUMN')],
	'location' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_LOCATION_COLUMN')],
	'private' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_PRIVATE_COLUMN')],
];

if (!$isSocialNetworkEnabled)
{
	unset($fieldsList['accessibility'], $fieldsList['private']);
}

$isOpenEvent = $arParams['type'] === Dictionary::CALENDAR_TYPE['open_event'];
if ($isOpenEvent)
{
	unset($fieldsList['accessibility'], $fieldsList['private']);
	$fieldsList = [
		...$fieldsList,
		'category' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_CATEGORY_COLUMN')],
		'max_attendees' => ['title' => Loc::getMessage('EC_EDIT_SLIDER_MAX_ATTENDEES_COLUMN')],
	];
}

$UF = CCalendarEvent::GetEventUserFields($event);

if (isset($UF['UF_CRM_CAL_EVENT']) && !$isExternalUser)
{
	$fieldsList['crm'] = array('title' => Loc::getMessage('EC_EDIT_SLIDER_CRM_COLUMN'));
}

$showAdditionalBlock = false;
foreach ($fieldsList as $k => $field)
{
	$fieldsList[$k]['pinned'] = in_array($k, $arResult['FORM_USER_SETTINGS']['pinnedFields']);
	$fieldsList[$k]['hidden'] = in_array($k, $hiddenFields);
	if(!$fieldsList[$k]['pinned'] && !$fieldsList[$k]['hidden'])
	{
		$showAdditionalBlock = true;
	}
}

$event['UF_CRM_CAL_EVENT'] = $UF['UF_CRM_CAL_EVENT'] ?? null;
if (empty($event['UF_CRM_CAL_EVENT']['VALUE']))
{
	$event['UF_CRM_CAL_EVENT'] = false;
}

$event['UF_WEBDAV_CAL_EVENT'] = $UF['UF_WEBDAV_CAL_EVENT'] ?? null;
if (empty($event['UF_WEBDAV_CAL_EVENT']['VALUE']))
{
	$event['UF_WEBDAV_CAL_EVENT'] = false;
}

$userId = CCalendar::GetCurUserId();
$arParams['event'] = $event;
$arParams['UF'] = $UF;

$showDestination = !$isOpenEvent;
// TODO: remove this check, planner enabled always
$showPlanner = true;
$showSection = !$isOpenEvent;
$defaultSectionId = $isOpenEvent && isset($arParams['sections'][0]['ID']) ? $arParams['sections'][0]['ID'] : 0;
?>
<div class="webform-buttons calendar-form-buttons-fixed">
	<div class="calendar-form-footer-container">
		<button id="<?=$id?>_save" class="ui-btn ui-btn-success"><?= Loc::getMessage('EC_EDIT_SLIDER_SAVE_EVENT_BUTTON')?></button>
		<button  id="<?=$id?>_close" class="ui-btn ui-btn-link"><?= Loc::getMessage('EC_EDIT_SLIDER_CANCEL_BUTTON')?></button>
	</div>
</div>
<div class="calendar-slider-calendar-wrap calendar-slider-calendar-wrap-edit">
	<div class="calendar-slider-header">
		<div class="calendar-head-area">
			<div class="calendar-head-area-inner">
				<div class="calendar-head-area-title">
					<span id="<?=$id?>_title" class="calendar-head-area-title-name"><?= !empty($event['ID']) ? Loc::getMessage('EC_EDIT_SLIDER_EDIT_TITLE') : Loc::getMessage('EC_EDIT_SLIDER_NEW_TITLE')?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="calendar-slider-workarea">
		<div class="calendar-slider-sidebar"></div>
		<div class="calendar-slider-content">
			<div id="<?=$id?>_form_wrap" class="calendar-form">
				<form enctype="multipart/form-data" method="POST" name="calendar_entry_edit" id="<?=$id?>_form">
					<input type="hidden" value="0" name="id"/>
					<input type="hidden" name="location" value=""/>
					<input id="<?=$id?>_section" type="hidden" name="section" value="<?=$defaultSectionId?>"/>
					<input id="<?=$id?>_color" type="hidden" name="color" value=""/>
					<input id="<?=$id?>_event_current_date_from" type="hidden" name="current_date_from" value="0"/>
					<input id="<?=$id?>_event_rec_edit_mode" type="hidden" name="rec_edit_mode" value="0"/>
					<input id="<?=$id?>_exclude_users" type="hidden" name="exclude_users" value=""/>
					<input id="<?=$id?>_do_check_occupancy" type="hidden" name="doCheckOccupancy" value="Y"/>

					<div class="calendar-info pinned">
						<div class="calendar-info-panel">
							<div class="calendar-info-panel-important">
								<input name="importance" type="checkbox" id="<?=$id?>_important" value="high">
								<label for="<?=$id?>_important"><?= Loc::getMessage('EC_EDIT_SLIDER_IMPORTANT_EVENT')?></label>
							</div>
							<div class="calendar-info-panel-title">
								<input name="name" id="<?=$id?>_entry_name" type="text" placeholder="<?= Loc::getMessage('EC_EDIT_SLIDER_NAME_PLACEHOLDER')?>">
								<div class="calendar-field-title-fade --edit-form" id="<?=$id?>_input_fade"></div>
							</div>
						</div>

						<div data-bx-block-placeholer="description" class="calendar-field-placeholder calendar-info-panel-description">
							<?if ($fieldsList["description"]["pinned"]):?>
								<div class="js-calendar-field-name" style="display: none;"><?= Loc::getMessage('EC_EDIT_SLIDER_DESCRIPTION_COLUMN')?></div>
								<?$APPLICATION->IncludeComponent(
									"bitrix:main.post.form",
									"",
									[
										"FORM_ID" => "calendar_entry_edit",
										"SHOW_MORE" => "Y",
										"PARSER" => [
											"Bold", "Italic", "Underline", "Strike", "ForeColor",
											"FontList", "FontSizeList", "RemoveFormat", "Quote",
											"Code", "CreateLink",
											"Image", "UploadFile",
											"InputVideo",
											"Table", "Justify", "InsertOrderedList",
											"InsertUnorderedList",
											"Source", "MentionUser"
										],
										"BUTTONS" => IsModuleInstalled('disk') ? [
											"Copilot",
											"UploadFile",
											"CreateLink",
											"InputVideo",
											"Quote"
										] : [
											"Copilot",
											"CreateLink",
											"InputVideo",
											"Quote"
										],
										"TEXT" => [
											"ID" => $id.'_edit_ed_desc',
											"NAME" => "desc",
											"VALUE" => $event['DESCRIPTION'] ?? null,
											"HEIGHT" => "160px"
										],
										"UPLOAD_WEBDAV_ELEMENT" => $arParams['UF']['UF_WEBDAV_CAL_EVENT'] ?? null,
										"UPLOAD_FILE_PARAMS" => array("width" => 400, "height" => 400),
										"FILES" => [
											"VALUE" => [],
											"DEL_LINK" => '',
											"SHOW" => "N"
										],
										"SMILES" => [
											"VALUE" => []
										],
										"LHE" => [
											"id" => $arParams['id'].'_entry_slider_editor',
											"jsObjName" => $arParams['id'].'_entry_slider_editor',
											"height" => 120,
											"documentCSS" => "",
											"fontSize" => "14px",
											"lazyLoad" => false,
											"setFocusAfterShow" => false,
											'isCopilotImageEnabledBySettings' => \Bitrix\Calendar\Integration\AI\Settings::isImageAvailable(),
											'isCopilotTextEnabledBySettings' => \Bitrix\Calendar\Integration\AI\Settings::isTextAvailable(),
											'copilotParams' => [
												'moduleId' => 'calendar',
												'contextId' => 'calendar_description',
												'category' => 'calendar',
											],
											'isMentionUnavailable' => true,
										],
									],
									false,
									[
										"HIDE_ICONS" => "Y"
									]
								);?>
								<span data-bx-fixfield="description" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
							<?endif;?>
						</div>
					</div>

					<div id="<?=$id?>_main_block_wrap" class="calendar-options pinned">
						<!--region Time-->
						<div id="<?=$id?>_datetime_container" class="calendar-options-item calendar-options-item-border calendar-options-item-datetime" >
							<div class="calendar-options-item-column-left">
								<div class="calendar-options-item-name"><?= Loc::getMessage('EC_EDIT_SLIDER_TIME_COLUMN')?></div>
							</div>
							<div id="<?=$id?>_datetime_editor" class="calendar-options-item-column-right">
								<div class="calendar-options-item-column-one">
									<div class="calendar-options-item-column-one-item">
										<span class="calendar-event-date">
											<label class="calendar-event-date-label calendar-event-date-label-datetime" for="<?=$id?>_date_from"><?= Loc::getMessage('EC_EDIT_SLIDER_DATETIME_FROM')?></label>
											<label class="calendar-event-date-label calendar-event-date-label-date" for="<?=$id?>_date_from"><?= Loc::getMessage('EC_EDIT_SLIDER_DATE_FROM')?></label>
											<span class="calendar-field-container calendar-field-container-select-calendar">
												<span class="calendar-field-block">
													<input name="date_from" type="text" id="<?=$id?>_date_from" class="calendar-field calendar-field-datetime">
												</span>
											</span>
										</span>
										<span class="calendar-event-time">
											<span class="calendar-field-container calendar-field-container-select">
												<span class="calendar-field-block calendar-field-select calendar-field">
													<?CClock::Show(array(
														'inputId' => $id.'_time_from',
														'inputName' => 'time_from',
														'inputClass' => 'calendar-field calendar-field-datetime-menu',
														'showIcon' => false,
														'zIndex' => 3100
													));?>
												</span>
											</span>
										</span>
									</div>
									<span class="calendar-event-mdash"></span>
									<div class="calendar-options-item-column-one-item">
										<span class="calendar-event-date">
											<label class="calendar-event-date-label calendar-event-date-label-datetime" for="<?=$id?>_date_to"><?= Loc::getMessage('EC_EDIT_SLIDER_DATETIME_TO')?></label>
											<label class="calendar-event-date-label calendar-event-date-label-date" for="<?=$id?>_date_to"><?= Loc::getMessage('EC_EDIT_SLIDER_DATE_TO')?></label>
											<span class="calendar-field-container calendar-field-container-select-calendar">
												<span class="calendar-field-block">
													<input name="date_to" type="text" id="<?=$id?>_date_to" class="calendar-field calendar-field-datetime">
												</span>
											</span>
										</span>
										<span class="calendar-event-time">
											<span class="calendar-field-container calendar-field-container-select">
												<span class="calendar-field-block calendar-field-select calendar-field">
													<?CClock::Show(array(
														'inputId' => $id.'_time_to',
														'inputName' => 'time_to',
														'inputClass' => 'calendar-field calendar-field-datetime-menu',
														'showIcon' => false,
														'zIndex' => 3100
													));?>
												</span>
											</span>
										</span>
									</div>
									<span class="calendar-event-full-day">
										<input name="skip_time" type="checkbox" id="<?=$id?>_date_full_day" value="Y">
										<label style="display: inline-block;" for="<?=$id?>_date_full_day"><?= Loc::getMessage('EC_EDIT_SLIDER_FULL_DAY')?></label>
									</span>
								</div>
								<div id="<?=$id?>_timezone_wrap" class="calendar-options-timezone">
									<span id="<?=$id?>_timezone_btn" class="calendar-options-timezone-collapse-btn"><?= Loc::getMessage('EC_EDIT_SLIDER_TIMEZONE')?></span>
									<div id="<?=$id?>_timezone_inner_wrap" class="calendar-options-timezone-collapse">
										<div class="calendar-options-timezone-inner">
											<span class="calendar-event-timezone">
												<span class="calendar-field-container calendar-field-container-select">
													<span class="calendar-field-block">
														<select id="<?=$id?>_timezone_from" class="calendar-field calendar-field-select" name="tz_from">
															<option value=""> - </option>
															<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
																<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
															<?endforeach;?>
														</select>
													</span>
												</span>
											</span>
											<span class="calendar-event-mdash"></span>
											<span class="calendar-event-timezone">
												<span class="calendar-field-container calendar-field-container-select">
													<span class="calendar-field-block">
														<select id="<?=$id?>_timezone_to" class="calendar-field calendar-field-select" name="tz_to">
															<option value=""> - </option>
															<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
																<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
															<?endforeach;?>
														</select>
													</span>
												</span>
											</span>
											<span id="<?=$id?>_timezone_hint" class="calendar-event-quest">?</span>
										</div>
									</div>
								</div>
								<div id="<?=$id?>_timezone_default_wrap" style="display: none;">
									<label class="calendar-event-timezone-label" for="<?=$id?>_timezone_default"><?= Loc::getMessage('EC_EDIT_SLIDER_DEFAULT_TIMEZONE_TITLE')?></label>
									<div class="calendar-options-timezone-inner">
										<div class="calendar-event-timezone calendar-options-timezone-default">
											<span class="calendar-field-container calendar-field-container-select" style="">
												<span class="calendar-field-block">
													<select id="<?=$id?>_timezone_default" class="calendar-field calendar-field-select" name="tz_def">
														<option value=""> - </option>
														<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
															<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
														<?endforeach;?>
													</select>
												</span>
											</span>
										</div>
										<span id="<?=$id?>_timezone_default_hint" class="calendar-event-quest">?</span>
									</div>
								</div>
							</div>
						</div>
						<!--endregion-->

						<?if($isOpenEvent):?>
						<!--region max_attendees-->
						<?$field = "max_attendees";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
							<?if (!$fieldsList[$field]["pinned"])
							{
								ob_start();
							}?>
							<div class="calendar-options-item calendar-options-item-border calendar-event-max-attendees">
								<div class="calendar-options-item-column-left calendar-options-item-column-left-max-attendees">
									<div class="calendar-options-item-name js-calendar-field-name">
										<?=Loc::getMessage('EC_EDIT_SLIDER_MAX_ATTENDEES_FIELD')?>
									</div>
								</div>
								<div class="calendar-options-item-column-right">
									<div class="calendar-options-item-column-one">
										<div class="calendar-field-container calendar-field-container-repeat">
											<div class="calendar-field-block">
												<input name="max_attendees" type="text" id="<?=$id?>_max_attendees" class="calendar-field">
											</div>
										</div>
									</div>
								</div>
								<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
							</div>
							<?if (!$fieldsList[$field]["pinned"])
							{
								$fieldsList[$field]["html"] = ob_get_contents();
								ob_end_clean();
							}?>
						</div>
						<!--endregion-->
						<?endif;?>

						<?if($isOpenEvent):?>
						<!--region category-->
						<?$field = "category";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
							<?if (!$fieldsList[$field]["pinned"])
							{
								ob_start();
							}?>
							<div id="<?=$id?>_category_selector">
								<div class="calendar-options-item calendar-options-item-border calendar-options-item-destination">

									<div class="calendar-options-item-column-left">
										<div class="calendar-options-item-name js-calendar-field-name"  id="<?=$id?>_category_title_wrap">
											<?=Loc::getMessage('EC_EDIT_SLIDER_CATEGORY_FIELD')?>
										</div>
									</div>
									<div class="calendar-options-item-column-right">
										<div id="tag-selector-6541"></div>
										<div class="calendar-category-selector-wrap"></div>
									</div>
								</div>
							</div>
							<?if (!$fieldsList[$field]["pinned"])
							{
								$fieldsList[$field]["html"] = ob_get_contents();
								ob_end_clean();
							}?>
						</div>
						<!--endregion-->
						<?endif;?>

						<!--region reminder-->
						<?$field = "reminder";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
						<?if (!($fieldsList[$field]["pinned"]))
						{
							ob_start();
						}?>
						<div class="calendar-options-item calendar-options-item-border calendar-options-item-notification">
							<div class="calendar-options-item-column-left">
								<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_REMINDER_COLUMN')?></div>
							</div>
							<div class="calendar-options-item-column-right">
								<div class="calendar-options-item-column-one">
									<div class="calendar-field-container calendar-field-container-text">
										<div class="calendar-field-block" id="<?=$id?>_reminder"></div>
									</div>
								</div>
							</div>
							<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
						</div>
						<?if (!$fieldsList[$field]["pinned"])
						{
							$fieldsList[$field]["html"] = ob_get_contents();
							ob_end_clean();
						}?>
						</div>
						<!--endregion-->

						<?php if($showSection):?>
						<!--region section-->
						<?$field = "section";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
						<?if (!$fieldsList[$field]["pinned"])
						{
							ob_start();
						}?>
						<div class="calendar-options-item calendar-options-item-border calendar-options-item-calendar">
							<div class="calendar-options-item-column-left">
								<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_SECTIONS_COLUMN')?></div>
							</div>
							<div class="calendar-options-item-column-right">
								<div class="calendar-options-item-column-one">
									<div class="calendar-field-container calendar-field-container-select">
										<div class="calendar-field-block" id="<?=$id?>_section_wrap"></div>
									</div>
								</div>
							</div>
							<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
						</div>
						<?if (!$fieldsList[$field]["pinned"])
						{
							$fieldsList[$field]["html"] = ob_get_contents();
							ob_end_clean();
						}?>
						</div>
						<!--endregion-->
						<?php endif;?>

						<!--region RRule-->
						<?$field = "rrule";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
						<?if (!$fieldsList[$field]["pinned"] || $fieldsList[$field]['hidden'])
						{
							ob_start();
						}?>
						<div class="calendar-options-item calendar-options-item-border calendar-options-item-repeat">
							<div class="calendar-options-item-column-left">
								<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_RRULE_COLUMN')?></div>
							</div>
							<div class="calendar-options-item-column-right">
								<div  class="calendar-rrule-type-none" id="<?=$id?>_rrule_wrap">
									<div class="calendar-options-item-column-one">

										<div class="calendar-field-container calendar-field-container-repeat">
											<div class="calendar-field-block">

												<div class="calendar-options-sub-item">
													<div class="calendar-options-item-column-left">
														<div class="calendar-field-container">
															<select name="EVENT_RRULE[FREQ]" class="calendar-field calendar-field-select"  id="<?=$id?>_rrule_type">
																<option value="NONE"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_NONE')?></option>
																<option value="DAILY"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_DAILY')?></option>
																<option value="WEEKLY"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_WEEKLY')?></option>
																<option value="MONTHLY"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_MONTHLY')?></option>
																<option value="YEARLY"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_YEARLY')?></option>
															</select>
														</div>
													</div>
													<div class="calendar-options-item-column-right">
														<div class="calendar-field-container calendar-field-block-text" style="text-align: right;">
															<span class="calendar-rrule-daily"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_DAILY_1')?></span>
															<span class="calendar-rrule-weekly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_WEEKLY_1')?></span>
															<span class="calendar-rrule-monthly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_MONTHLY_1')?></span>
															<span class="calendar-rrule-yearly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_YEARLY_1')?></span>
														</div>
														<div class="calendar-field-container calendar-field-container-select">
															<span class="calendar-field-block calendar-rrule-count">
																<select id="<?=$id?>_rrule_count" class="calendar-field calendar-field-select" name="EVENT_RRULE[INTERVAL]">
																	<?for ($i = 1; $i < 36; $i++):?>
																		<option value="<?=$i?>"><?=$i?></option>
																	<?endfor;?>
																</select>
															</span>
														</div>
														<div class="calendar-field-block-text">
															<span class="calendar-rrule-daily"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_DAILY_2')?></span>
															<span class="calendar-rrule-weekly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_WEEKLY_2')?></span>
															<span class="calendar-rrule-monthly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_MONTHLY_2')?></span>
															<span class="calendar-rrule-yearly"><?=Loc::getMessage('EC_EDIT_SLIDER_REPEAT_EVERY_YEARLY_2')?></span>
														</div>
													</div>
												</div>

												<div class="calendar-options-sub-item calendar-week-day-container">
													<?foreach(array('MO','TU','WE','TH','FR','SA','SU') as $day):?>
													<label class="calendar-options-day"><input id="<?=$id?>_rrule_byday_<?= $day?>" name="EVENT_RRULE[BYDAY][]" class="calendar-options-day-checkbox" type="checkbox" value="<?= $day?>"><?= Loc::getMessage('EC_EDIT_SLIDER_'.$day)?></label>
													<?endforeach;?>
												</div>
											</div>
										</div>
									</div>

									<div class="calendar-rrule-endson">
										<hr class="calendar-filed-separator">

										<div class="calendar-options-item-column-one">
											<div class="calendar-field-container calendar-field-container-repeat">
												<div class="calendar-field-block">

													<div class="calendar-options-sub-item">
														<div class="calendar-options-item-column-left">
															<div class="calendar-options-item-name"><?=Loc::getMessage('EC_EDIT_SLIDER_ENDING')?></div>

														</div>
														<div class="calendar-options-item-column-right">
															<div>
																<span class="calendar-field-container">
																	<span class="calendar-field-block">
																		<label class="calendar-field-checkbox-label">
																			<input id="<?=$id?>_endson_never" class="calendar-field-checkbox" name="rrule_endson" type="radio" checked="checked" value="never">
																			<?=Loc::getMessage('EC_EDIT_SLIDER_ENDS_ON_NEVER')?></label>
																	</span>
																</span>
															</div>
														</div>
													</div>

													<div class="calendar-options-sub-item">
														<div class="calendar-options-item-column-left"></div>
														<div class="calendar-options-item-column-right">
															<span class="calendar-field-container">
																<span class="calendar-field-block">
																	<label for="<?=$id?>_endson_count" style="padding-left: 22px;">
																		<input id="<?=$id?>_endson_count" class="calendar-field-checkbox" name="rrule_endson" type="radio" value="count">
																		<?= Loc::getMessage('EC_EDIT_SLIDER_ENDS_ON_COUNT', array('#COUNT#' => '<input class="calendar-field calendar-field-string calendar-repeat-endson-input" id="'.$id.'event-endson-count-input" type="text" name="EVENT_RRULE[COUNT]" placeholder="10">'))?>
																	</label>
																</span>
															</span>
														</div>
													</div>

													<div class="calendar-options-sub-item">
														<div class="calendar-options-item-column-left"></div>
														<div class="calendar-options-item-column-right">
															<span class="calendar-field-container">
																<span class="calendar-field-block">
																	<label class="calendar-field-checkbox-label" style="">
																		<input id="<?=$id?>_endson_date" class="calendar-field-checkbox" name="rrule_endson" type="radio" value="until">
																	</label>
																</span>
															</span>
															<div class="calendar-field-container calendar-field-container-datetime">
																<div class="calendar-field-block calendar-field-block-left" style="margin-left: -8px">
																	<input id="<?=$id?>_endson_date_input" name="EVENT_RRULE[UNTIL]" type="text" class="calendar-field calendar-field-datetime" placeholder="<?= Loc::getMessage('EC_EDIT_SLIDER_ENDS_ON_UNTIL_PLACEHOLDER')?>">
																</div>
															</div>
														</div>
													</div>

												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
							<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
						</div>
						<?if (!$fieldsList[$field]["pinned"] || $fieldsList[$field]['hidden'])
						{
							if (!$fieldsList[$field]['hidden'])
							{
								$fieldsList[$field]["html"] = ob_get_contents();
							}
							ob_end_clean();
						}?>
						</div>
						<!--endregion-->

						<!--region Location-->
						<?$field = "location";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
						<?if (!$fieldsList[$field]["pinned"])
						{
							ob_start();
						}?>
						<div class="calendar-options-item calendar-options-item-border calendar-event-location">
							<div class="calendar-options-item-column-left calendar-options-item-column-left-location">
								<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_LOCATION_COLUMN')?></div>
							</div>
							<div class="calendar-options-item-column-right">
								<div class="calendar-options-item-column-one calendar-options-item-column-location">
									<div class="calendar-field-container calendar-field-container-select" id="<?=$id?>_location_wrap"></div>
								</div>
							</div>
							<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
						</div>
						<?if (!$fieldsList[$field]["pinned"])
						{
							$fieldsList[$field]["html"] = ob_get_contents();
							ob_end_clean();
						}?>
						</div>
						<!--endregion-->

						<?php if ($showDestination):?>
						<!--region Destination-->
						<div id="<?=$id?>_attendees_selector">
							<div class="calendar-options-item calendar-options-item-border calendar-options-item-destination" style="border-bottom: none;">

								<div class="calendar-options-item-column-left">
									<div class="calendar-options-item-name js-calendar-field-name"  id="<?=$id?>_attendees_title_wrap"><?= Loc::getMessage('EC_EDIT_SLIDER_ATTENDEES_COLUMN')?></div>
								</div>
								<div class="calendar-options-item-column-right">
									<div id="tag-selector-654"></div>
									<div class="calendar-attendees-selector-wrap"></div>
								</div>
							</div>
						</div>
						<!--endregion-->
						<?php endif;?>

						<?php if($showPlanner):?>
						<!--region planner-->
						<div class="calendar-options-item-planner calendar-options-item-border">
							<div class="calendar-options-item-column-planner">
								<div class="calendar-edit-planner-wrap" id="<?=$id?>_planner_wrap">
									<?CCalendarPlanner::Init(array(
										'id' => $id.'_slider_planner'
									));?>
								</div>
								<div class="calendar-edit-planner-wrap" id="<?=$id?>_planner_outer_wrap">
								</div>
								<div class="calendar-edit-planner-additional-settings-wrap" id="<?=$id?>_more_outer_wrap">
									<div id="<?=$id?>_more_wrap" class="calendar-edit-planner-additional-settings">
										<div class="calendar-field-container calendar-field-container-checkbox" style="display: none;">
											<div class="calendar-field-block">
												<label type="text" class="calendar-field-checkbox-label">
													<input name="allow_invite" type="checkbox" class="calendar-field-checkbox" value="Y">
													<?= Loc::getMessage('EC_EDIT_SLIDER_ALLOW_INVITE_LABEL')?>
												</label>
											</div>
										</div>
										<div class="calendar-field-container calendar-field-container-checkbox">
											<div class="calendar-field-block">
												<label type="text" class="calendar-field-checkbox-label">
													<input name="meeting_notify" type="checkbox" class="calendar-field-checkbox" value="Y">
													<?= Loc::getMessage('EC_EDIT_SLIDER_NOTIFY_STATUS_LABEL')?>
												</label>
											</div>
										</div>
										<?if (!empty($event['ID'])):?>
										<div class="calendar-field-container calendar-field-container-checkbox">
											<div class="calendar-field-block">
												<label type="text" class="calendar-field-checkbox-label">
													<input name="meeting_reinvite" id="<?=$id?>_allow_invite" type="checkbox" class="calendar-field-checkbox" value="Y">
													<?= Loc::getMessage('EC_EDIT_SLIDER_REINVITE_LABEL')?>
												</label>
											</div>
										</div>
										<?endif;?>
										<div class="calendar-field-container calendar-field-container-checkbox calendar-hide-members-wrap" style="display: none">
											<div class="calendar-field-block">
												<label type="text" class="calendar-field-checkbox-label">
													<input name="hide_guests" type="checkbox" class="calendar-field-checkbox" value="Y">
													<?= Loc::getMessage('EC_EDIT_SLIDER_HIDE_GUEST_NAMES')?>
													<span class="calendar-hide-members-helper" data-hint="<?= Loc::getMessage('EC_EDIT_SLIDER_HIDE_GUEST_NAMES_HINT')?>"></span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--endregion-->
						<?php endif;?>

						<!--region Color-->
						<?$field = "color";?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
							<?if (!$fieldsList[$field]["pinned"])
							{
								ob_start();
							}?>
							<div class="calendar-options-item calendar-options-item-border calendar-options-item-colorpicker">
								<div class="calendar-options-item-column-left">
									<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_COLOR_COLUMN')?></div>
								</div>
								<div class="calendar-options-item-column-right">
									<div class="calendar-options-item-column-one">
										<div class="calendar-field-container calendar-field-container-colorpicker">
											<div class="calendar-field-block">
												<ul class="calendar-field-colorpicker" id="<?=$id?>_color_selector_wrap">
												</ul>
											</div>
										</div>
									</div>
								</div>
								<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
							</div>
							<?if (!$fieldsList[$field]["pinned"])
							{
								$fieldsList[$field]["html"] = ob_get_contents();
								ob_end_clean();
							}?>
						</div>
						<!--endregion-->

						<!--region Private-->
						<?$field = "private";
						if (isset($fieldsList[$field]))
						{
						?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
							<?if (!$fieldsList[$field]["pinned"])
							{
								ob_start();
							}?>
							<div class="calendar-options-item calendar-options-item-border calendar-event-private">
								<div class="calendar-options-item-column-left">
									<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_PRIVATE_COLUMN')?></div>
								</div>
								<div class="calendar-options-item-column-right">
									<div class="calendar-options-item-column-one">
										<div class="calendar-field-container calendar-field-container-checkbox">
											<div class="calendar-field-block">
												<label type="text" class="calendar-field-checkbox-label">
													<input name="private_event" id="<?=$id?>_private" type="checkbox" class="calendar-field-checkbox" value="Y">
													<?= Loc::getMessage('EC_EDIT_SLIDER_PRIVATE_LABEL')?>
												</label>
												<div class="calendar-field-container-checkbox-description"><?= Loc::getMessage('EC_EDIT_SLIDER_PRIVATE_HINT')?></div>
											</div>
										</div>
									</div>
								</div>
								<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
							</div>
							<?if (!$fieldsList[$field]["pinned"])
							{
								$fieldsList[$field]["html"] = ob_get_contents();
								ob_end_clean();
							}?>
						</div>
						<?}?>
						<!--endregion-->

						<!--region accessibility-->
						<? $field = "accessibility";
							if (isset($fieldsList[$field]) && !$fieldsList[$field]['hidden'])
							{
							?>
							<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
								<?if (!$fieldsList[$field]["pinned"])
								{
									ob_start();
								}?>
								<div class="calendar-options-item calendar-options-item-border calendar-event-location">
									<div class="calendar-options-item-column-left">
										<div class="calendar-options-item-name js-calendar-field-name"><?= Loc::getMessage('EC_EDIT_SLIDER_ACCESSIBILITY_COLUMN')?></div>
									</div>
									<div class="calendar-options-item-column-right">
										<div class="calendar-options-item-column-one">
											<div class="calendar-field-container calendar-field-container-select">
												<div class="calendar-field-block">
													<select class="calendar-field calendar-field-select" id="<?=$id?>_accessibility" name="accessibility">
														<option value="busy"><?=GetMessage('EC_EDIT_SLIDER_ACC_B')?></option>
														<option value="quest"><?=GetMessage('EC_EDIT_SLIDER_ACC_Q')?></option>
														<option value="free"><?=GetMessage('EC_EDIT_SLIDER_ACC_F')?></option>
														<?if (!CCalendar::IsBitrix24()
															|| COption::GetOptionString("bitrix24",  "absence_limits_enabled", "") != "Y"
															|| \Bitrix\Bitrix24\Feature::isFeatureEnabled("absence")):?>
															<option value="absent"><?=GetMessage('EC_EDIT_SLIDER_ACC_A')?> (<?=GetMessage('EC_EDIT_SLIDER_ACC_EX')?>)</option>
														<?endif;?>
													</select>
												</div>
											</div>
										</div>
									</div>
									<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
								</div>
								<?if (!$fieldsList[$field]["pinned"])
								{
									$fieldsList[$field]["html"] = ob_get_contents();
									ob_end_clean();
								}?>
							</div>
						<?}?>
						<!--endregion-->

						<!--region crm-->
						<?$field = "crm";
						if ($isCrmEnabled && isset($fieldsList[$field]))
						{
							$crmUF = $UF['UF_CRM_CAL_EVENT'];
						?>
						<div data-bx-block-placeholer="<?= $field?>" class="calendar-field-placeholder">
							<?if (!$fieldsList[$field]["pinned"] || $fieldsList[$field]['hidden'])
							{
								ob_start();
							}?>
							<div class="calendar-options-item calendar-options-item-border">
								<div class="calendar-options-item-column-left">
									<div class="calendar-options-item-name js-calendar-field-name"><?= htmlspecialcharsbx($crmUF["EDIT_FORM_LABEL"])?></div>
								</div>
								<div class="calendar-options-item-column-right">
									<div class="calendar-options-item-column-one">
										<div class="calendar-options-uf-crm-cont" id="<?=$id?>-uf-crm-wrap">
											<?/*$APPLICATION->IncludeComponent(
												"bitrix:system.field.edit",
												$crmUF["USER_TYPE"]["USER_TYPE_ID"],
												array(
													"bVarsFromForm" => false,
													"arUserField" => $crmUF,
													"form_name" => 'event_edit_form'
												), null, array("HIDE_ICONS" => "Y")
											); */?>
										</div>
									</div>
								</div>
								<span data-bx-fixfield="<?= $field?>" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
							</div>
							<?if (!$fieldsList[$field]["pinned"] || $fieldsList[$field]['hidden'])
							{
								if (!$fieldsList[$field]['hidden'])
								{
									$fieldsList[$field]["html"] = ob_get_contents();
								}
								ob_end_clean();
							}?>
						</div>
						<?}?>
						<!--endregion-->
					</div>

					<div  id="<?=$id?>_additional_block_wrap" class="calendar-additional-block<?= ($showAdditionalBlock ? '' : ' calendar-additional-block-hidden')?>">
						<div id="<?=$id?>_additional_switch" class="calendar-additional-alt">
							<div class="calendar-additional-alt-more"><?= Loc::getMessage('EC_EDIT_SLIDER_ADDITIONAL_TITLE')?></div>
							<div id="<?=$id?>_additional_pinned_names" class="calendar-additional-alt-promo">
								<?foreach ($fieldsList as $fieldId => $field)
								{
									if(!$field["pinned"] && !$field['hidden'])
									{
										?>
										<span data-bx-field-id="<?= $fieldId?>" class="calendar-additional-alt-promo-text"><?= $field["title"]?></span>
										<?
									}
								}?>
							</div>
						</div>
						<div  id="<?=$id?>_additional_block" class="calendar-options calendar-options-more calendar-openable-block invisible">
							<div data-bx-block-placeholer="description" class="calendar-field-additional-placeholder"
								<?if ($fieldsList["description"]["pinned"]){echo 'style="display: none;"';}?>>
								<!--region Description-->
									<div class="calendar-options-item calendar-options-item-border calendar-options-item-destination">
										<div class="calendar-options-item-column-left">
											<div class="calendar-options-item-name"><?= Loc::getMessage('EC_EDIT_SLIDER_DESCRIPTION_COLUMN')?></div>
										</div>
										<div class="calendar-options-item-column-right">
											<div class="calendar-options-item-column-one">
												<div class="calendar-field-container calendar-field-container-textarea">
													<div id="<?=$id?>_description_additional_wrap" data-bx-block-placeholer="description" class="calendar-field-block calendar-info-panel-description">
													<?if (!$fieldsList["description"]["pinned"]):?>
														<div class="js-calendar-field-name" style="display: none;"><?= Loc::getMessage('EC_EDIT_SLIDER_DESCRIPTION_COLUMN')?></div>
														<?$APPLICATION->IncludeComponent(
															"bitrix:main.post.form",
															"",
															[
																"FORM_ID" => "event_edit_form",
																"SHOW_MORE" => "Y",
																"PARSER" => [
																	"Bold", "Italic", "Underline", "Strike", "ForeColor",
																	"FontList", "FontSizeList", "RemoveFormat", "Quote",
																	"Code", "CreateLink",
																	"Image", "UploadFile",
																	"InputVideo",
																	"Table", "Justify", "InsertOrderedList",
																	"InsertUnorderedList",
																	"Source", "MentionUser"
																],
																"BUTTONS" => IsModuleInstalled('disk') ? [
																	"Copilot",
																	"UploadFile",
																	"CreateLink",
																	"InputVideo",
																	"Quote"
																] : [
																	"Copilot",
																	"CreateLink",
																	"InputVideo",
																	"Quote"
																],
																"TEXT" => [
																	"ID" => $id.'_edit_ed_desc',
																	"NAME" => "desc",
																	"VALUE" => $event['DESCRIPTION'] ?? null,
																	"HEIGHT" => "160px"
																],
																"UPLOAD_WEBDAV_ELEMENT" => $arParams['UF']['UF_WEBDAV_CAL_EVENT'],
																"UPLOAD_FILE_PARAMS" => array("width" => 400, "height" => 400),
																"FILES" => [
																	"VALUE" => [],
																	"DEL_LINK" => '',
																	"SHOW" => "N"
																],
																"SMILES" => [
																	"VALUE" => []
																],
																"LHE" => [
																	"id" => $arParams['id'].'_entry_slider_editor',
																	"jsObjName" => $arParams['id'].'_entry_slider_editor',
																	"height" => 120,
																	"documentCSS" => "",
																	"fontSize" => "14px",
																	"lazyLoad" => false,
																	"setFocusAfterShow" => false,
																	'isCopilotImageEnabledBySettings' => \Bitrix\Calendar\Integration\AI\Settings::isImageAvailable(),
																	'isCopilotTextEnabledBySettings' => \Bitrix\Calendar\Integration\AI\Settings::isTextAvailable(),
																	'copilotParams' => [
																		'moduleId' => 'calendar',
																		'contextId' => 'calendar_description',
																		'category' => 'calendar',
																	],
																	'isMentionUnavailable' => true,
																],
															],
															false,
															[
																"HIDE_ICONS" => "Y"
															]
														);?>
														<span data-bx-fixfield="description" class="calendar-option-fixedbtn" title="<?= Loc::getMessage('EC_EDIT_SLIDER_FIX_FIELD')?>"></span>
													<?endif;?>
													</div>
												</div>
											</div>
										</div>
									</div>
								<!--endregion-->
							</div>

							<?
							foreach ($fieldsList as $fieldId => $field)
							{
								if ($fieldId !== 'description')
								{
									?>
									<div data-bx-block-placeholer="<?= $fieldId ?>"
										 class="calendar-field-additional-placeholder"><?
									if(!$field["pinned"] && !$field['hidden'] && ($field["html"] ?? null))
									{
										echo $field["html"];
									}
									?></div><?
								}
							}
							?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
