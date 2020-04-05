<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$id = $arParams['id'];?>
<div id="bxec_add_ed_<?=$id?>" class="bxec-popup" style="width:380px;">
	<?if(CCalendarSceleton::CheckBitrix24Limits($arParams)):?>
	<div class="bxec-popup-row" style="text-align:center;">
		<div class="bxec-txt" id="<?=$id?>_add_ed_per_text"></div>
	</div>

	<div id="event-simple-tz-def-wrap<?=$id?>" class="bxec-popup-timezone bxec-popup-timezone-simple bxec-tz-wrap" style="display: none;">
		<span class="bxec-field-label-edev">
			<label><?= GetMessage('EC_EVENT_ASK_TZ')?></label>
		</span>
		<select id="event-simple-tz-def<?=$id?>" class="calendar-select calendar-tz-select" name="default_tz" style="width: 280px;">
				<option value=""> - </option>
				<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
					<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
				<?endforeach;?>
		</select>
		<span id="event-tz-simple-def-tip<?=$id?>" class="bxec-popup-tip-btn"></span>
	</div>

	<div class="bxec-popup-row">
		<span class="bxec-field-label-2"><label for="<?=$id?>_add_ed_name"><b><?= GetMessage('EC_T_NAME')?>:</b></label></span>
		<span class="bxec-field-val-2 bxec-field-title-inner" style="width: 240px;"><input type="text" id="<?=$id?>_add_ed_name" /></span>
	</div>

	<?if($arParams['bIntranet'] && ($arParams['type'] != 'user' || $arParams['inPersonalCalendar'])):?>
		<div class="bxec-popup-row">
		<span class="bxec-field-label-2">
			<label for="<?=$id?>_add_ed_acc"><?=GetMessage('EC_ACCESSIBILITY_S')?>:</label>
		</span>
		<span  class="bxec-field-val-2">
			<select id="<?=$id?>_add_ed_acc" style="max-width: 250px;">
				<option value="busy" ><?=GetMessage('EC_ACCESSIBILITY_B')?></option>
				<option value="quest"><?=GetMessage('EC_ACCESSIBILITY_Q')?></option>
				<option value="free"><?=GetMessage('EC_ACCESSIBILITY_F')?></option>
				<option value="absent"><?=GetMessage('EC_ACCESSIBILITY_A')?> (<?=GetMessage('EC_ACC_EX')?>)</option>
			</select>
		</span>
		</div>
	<?endif;?>
	<div class="bxec-popup-row">
		<span class="bxec-field-label-2"><label for="<?=$id?>_add_ed_calend_sel"><?=GetMessage('EC_T_CALENDAR')?>:</label></span>
		<span class="bxec-field-val-2"><select id="<?=$id?>_add_ed_calend_sel" style="max-width: 250px;"></select></span>
		<span id="<?=$id?>_add_sect_sel_warn" class="bxec-warn" style="display: none;"><?=GetMessage('EC_T_CALEN_DIS_WARNING')?></span>
	</div>
	<?endif;/*if(CCalendarSceleton::CheckBitrix24Limits($arParams))*/?>
</div>