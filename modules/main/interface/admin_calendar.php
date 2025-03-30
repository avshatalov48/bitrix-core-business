<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

class CAdminCalendar
{
	const PERIOD_EMPTY = "NOT_REF";
	const PERIOD_DAY = "day";
	const PERIOD_WEEK = "week";
	const PERIOD_MONTH = "month";
	const PERIOD_QUARTER = "quarter";
	const PERIOD_YEAR = "year";
	const PERIOD_EXACT = "exact";
	const PERIOD_BEFORE = "before";
	const PERIOD_AFTER = "after";
	const PERIOD_INTERVAL = "interval";

	private static function InitPeriodList($arPeriodParams = array())
	{
		$arPeriod = array(
			self::PERIOD_EMPTY => GetMessage("admin_lib_calend_no_period"),
			self::PERIOD_DAY => GetMessage("admin_lib_calend_day"),
			self::PERIOD_WEEK => GetMessage("admin_lib_calend_week"),
			self::PERIOD_MONTH => GetMessage("admin_lib_calend_month"),
			self::PERIOD_QUARTER => GetMessage("admin_lib_calend_quarter"),
			self::PERIOD_YEAR => GetMessage("admin_lib_calend_year"),
			self::PERIOD_EXACT => GetMessage("admin_lib_calend_exact"),
			self::PERIOD_BEFORE => GetMessage("admin_lib_calend_before"),
			self::PERIOD_AFTER => GetMessage("admin_lib_calend_after"),
			self::PERIOD_INTERVAL => GetMessage("admin_lib_calend_interval")
		);

		if (empty($arPeriodParams) || !is_array($arPeriodParams))
			return $arPeriod;

		$arReturnPeriod = array();

		foreach ($arPeriodParams as $periodName => $lPhrase)
		{
			if (isset($arPeriod[$periodName]))
				$arReturnPeriod[$periodName] = $lPhrase;
			elseif (isset($arPeriod[$lPhrase]))
				$arReturnPeriod[$lPhrase] = $arPeriod[$lPhrase];
		}

		if (empty($arReturnPeriod))
			$arReturnPeriod = $arPeriod;
		return $arReturnPeriod;
	}

	public static function ShowScript()
	{
		\Bitrix\Main\UI\Extension::load('ui.date-picker');
	}

	public static function Calendar($sFieldName, $sFromName="", $sToName="", $bTime=false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		ob_start();
		$APPLICATION->IncludeComponent('bitrix:main.calendar', '', array(
			'RETURN' => 'Y',
			'SHOW_INPUT' => 'N',
			'INPUT_NAME' => $sFieldName,
			'SHOW_TIME' => $bTime ? 'Y' : 'N'
		), null, array('HIDE_ICONS' => 'Y'));
		$res = ob_get_contents();
		ob_end_clean();

		return $res;
	}

	public static function CalendarDate($sFieldName, $sValue="", $size="10", $bTime=false)
	{
		\Bitrix\Main\UI\Extension::load('ui.date-picker');

		// component can't set 'size' param
		return '
	<div class="adm-input-wrap adm-input-wrap-calendar">
		<input class="adm-input adm-input-calendar" type="text" name="'.$sFieldName.'" size="'.(intval($size)+3).'" value="'.htmlspecialcharsbx($sValue).'">
		<button type="button" class="adm-calendar-icon" title="'.GetMessage("admin_lib_calend_title").'"></button>
		<script>
			(function() {
				const input = document.querySelector(`input[name="' . $sFieldName . '"]`);
				const button = input.nextElementSibling;
				let picker = null;
				const getPicker = () => {
					if (picker === null)
					{
						picker = new BX.UI.DatePicker.DatePicker({
							targetNode: input,
							inputField: input,
							enableTime: '.($bTime ? "true" : "false").',
							useInputEvents: false,
						});
					}

					return picker;
				};

				BX.Event.bind(button, "click", () => getPicker().show());
			})();
		</script>
	</div>';

	}

	/**
	 * @param string $sFromName
	 * @param string $sToName
	 * @param string $sFromVal
	 * @param string $sToVal
	 * @param bool $bSelectShow
	 * @param int $size
	 * @param bool $bTime
	 * @param bool|array $arPeriod
	 * @param string $periodValue
	 * @return string
	 */
	public static function CalendarPeriodCustom($sFromName, $sToName, $sFromVal="", $sToVal="", $bSelectShow=false, $size=10, $bTime=false, $arPeriod = false, $periodValue = '')
	{
		$arPeriodList = self::InitPeriodList($arPeriod);

		return self::GetPeriodHtml($sFromName, $sToName, $sFromVal, $sToVal, $bSelectShow, $size, $bTime, $arPeriodList, $periodValue);
	}

	/**
	 * @param string $sFromName
	 * @param string $sToName
	 * @param string $sFromVal
	 * @param string $sToVal
	 * @param bool $bSelectShow
	 * @param int $size
	 * @param bool $bTime
	 * @return string
	 */
	public static function CalendarPeriod($sFromName, $sToName, $sFromVal="", $sToVal="", $bSelectShow=false, $size=10, $bTime=false)
	{
		$arPeriodList = self::InitPeriodList();

		return self::GetPeriodHtml($sFromName, $sToName, $sFromVal, $sToVal, $bSelectShow, $size, $bTime, $arPeriodList);
	}

	/**
	 * @param $sFromName
	 * @param $sToName
	 * @param string $sFromVal
	 * @param string $sToVal
	 * @param bool $bSelectShow
	 * @param int $size
	 * @param bool $bTime
	 * @param $arPeriod
	 * @param string $periodValue
	 * @return string
	 */
	private static function GetPeriodHtml($sFromName, $sToName, $sFromVal, $sToVal, $bSelectShow, $size, $bTime, $arPeriod, $periodValue = '')
	{
		\Bitrix\Main\UI\Extension::load('ui.date-picker');

		$size = (int)$size;

		$s = '
		<div class="adm-calendar-block adm-filter-alignment">
			<div class="adm-filter-box-sizing">';

		if($bSelectShow)
		{
			$sPeriodName = $sFromName."_FILTER_PERIOD";
			$sDirectionName = $sFromName."_FILTER_DIRECTION";

			$arDirection = array(
				"previous"=>GetMessage("admin_lib_calend_previous"),
				"current"=>GetMessage("admin_lib_calend_current"),
				"next"=>GetMessage("admin_lib_calend_next")
			);

			$s .= '<span class="adm-select-wrap adm-calendar-period" ><select class="adm-select adm-calendar-period" id="'.$sFromName.'_calendar_period" name="'.$sPeriodName.'" onchange="BX.CalendarPeriod.OnChangeP(this);" title="'.GetMessage("admin_lib_calend_period_title").'">';

			$currentPeriod = '';
			if (isset($GLOBALS[$sPeriodName]))
				$currentPeriod = (string)$GLOBALS[$sPeriodName];
			$periodValue = (string)$periodValue;
			if ($periodValue != '')
				$currentPeriod = $periodValue;
			foreach($arPeriod as $k => $v)
			{
					$k = ($k != "NOT_REF" ? $k : "");
					$s .= '<option value="'.$k.'"'.(($currentPeriod != '' && $currentPeriod == $k) ? " selected":"").'>'.$v.'</option>';
			}
			unset($currentPeriod);

			$s .='</select></span>';

			$currentDirection = '';
			if (isset($GLOBALS[$sDirectionName]))
				$currentDirection = (string)$GLOBALS[$sDirectionName];
			$s .= '<span class="adm-select-wrap adm-calendar-direction" style="display: none;"><select class="adm-select adm-calendar-direction" id="'.$sFromName.'_calendar_direct" name="'.$sDirectionName.'" onchange="BX.CalendarPeriod.OnChangeD(this);"  title="'.GetMessage("admin_lib_calend_direct_title").'">';
			foreach($arDirection as $k => $v)
					$s .= '<option value="'.$k.'"'.($currentDirection == $k ? " selected":"").'>'.$v.'</option>';
			unset($currentDirection);

			$s .='</select></span>';
		}

		$s .=
			'<div class="adm-input-wrap adm-calendar-inp adm-calendar-first" style="display: ' .($bSelectShow ? 'none' : 'inline-block').';">'.
			'<input type="text" class="adm-input adm-calendar-from" id="'.$sFromName.'_calendar_from" name="'.$sFromName.'" size="'.($size+5).'" value="'.htmlspecialcharsbx($sFromVal).'">'.
			'<button type="button" class="adm-calendar-icon" title="'.GetMessage("admin_lib_calend_title").'"></button>'.
		'</div>
		<span class="adm-calendar-separate" style="display: '.($bSelectShow ? 'none' : 'inline-block').'"></span>'.
		'<div class="adm-input-wrap adm-calendar-second" style="display: '.($bSelectShow ? 'none' : 'inline-block').';">'.
			'<input type="text" class="adm-input adm-calendar-to" id="'.$sToName.'_calendar_to" name="'.$sToName.'" size="'.($size+5).'" value="'.htmlspecialcharsbx($sToVal).'">'.
			'<button type="button" class="adm-calendar-icon" title="'.GetMessage("admin_lib_calend_title").'"></button>'.
		'</div>'.
		'<script>
			(function() {
				const inputFrom = document.getElementById("' . $sFromName . '_calendar_from");
				const buttonFrom = inputFrom.nextElementSibling;
				const inputTo = document.getElementById("' . $sToName . '_calendar_to");
				const buttonTo = inputTo.nextElementSibling;

				let pickerFrom = null;
				let pickerTo = null;
				const getPickerFrom = () => {
					if (pickerFrom === null)
					{
						pickerFrom = new BX.UI.DatePicker.DatePicker({
							targetNode: inputFrom,
							inputField: inputFrom,
							useInputEvents: false,
							enableTime: '.($bTime ? "true" : "false").',
						});
					}

					return pickerFrom;
				};

				const getPickerTo = () => {
					if (pickerTo === null)
					{
						pickerTo = new BX.UI.DatePicker.DatePicker({
							targetNode: inputTo,
							inputField: inputTo,
							useInputEvents: false,
							enableTime: '.($bTime ? "true" : "false").',
						});
					}

					return pickerTo;
				};

				BX.Event.bind(buttonFrom, "click", () => getPickerFrom().show());
				BX.Event.bind(buttonTo, "click", () => getPickerTo().show());
			})();

			window["'.$sFromName.'_bTime"] = '.($bTime ? "true" : "false").';';

		if($bSelectShow)
			$s .='BX.CalendarPeriod.Init(BX("'.$sFromName.'_calendar_from"), BX("'.$sToName.'_calendar_to"), BX("'.$sFromName.'_calendar_period"));';

		$s .='
		</script>
		</div>
		</div>';

		return $s;
	}
}
