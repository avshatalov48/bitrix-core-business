<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;


class CCalendarSceleton
{
	public static function InitJS($config = array(), $data = array(), $additionalParams = array())
	{
		global $APPLICATION;
		CJSCore::Init(array('ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest','color_picker', 'sidepanel', 'clipboard', 'tooltip'));

		if(\Bitrix\Main\Loader::includeModule('webservice'))
		{
			CJSCore::Init(array('stssync'));
		}

		if (\Bitrix\Main\Loader::includeModule('bitrix24') && !in_array(\CBitrix24::getLicenseType(), array('company', 'demo', 'edu', 'bis_inc', 'nfr')))
		{
			\CBitrix24::initLicenseInfoPopupJS();
		}

		?>
		<script>
			top.BXCRES = {};
			(function(window) {
				if (!window.BXEventCalendar)
				{
					var BXEventCalendar = {
						instances: {},

						Show: function(config, data, additionalParams)
						{
							BX.ready(function()
							{
								BXEventCalendar.instances[config.id] = new window.BXEventCalendar.Core(config, data, additionalParams);
							});
						},
						Get: function(id)
						{
							return BXEventCalendar.instances[id] || false;
						}
					};

					window.BXEventCalendar = BXEventCalendar;
				}
				BX.onCustomEvent(window, "onBXEventCalendarInit");
			})(window);
		</script><?

		CUtil::InitJSCore(array('event_calendar'));

		$config['weekStart'] = CCalendar::GetWeekStart();
		$config['weekDays'] = self::GetWeekDaysEx($config['weekStart']);
		$config['days'] = self::GetWeekDays();
		$config['month'] = array(Loc::getMessage('EC_JAN'), Loc::getMessage('EC_FEB'), Loc::getMessage('EC_MAR'), Loc::getMessage('EC_APR'), Loc::getMessage('EC_MAY'), Loc::getMessage('EC_JUN'), Loc::getMessage('EC_JUL'), Loc::getMessage('EC_AUG'), Loc::getMessage('EC_SEP'), Loc::getMessage('EC_OCT'), Loc::getMessage('EC_NOV'), Loc::getMessage('EC_DEC'));
		$config['month_r'] = array(Loc::getMessage('EC_JAN_R'), Loc::getMessage('EC_FEB_R'), Loc::getMessage('EC_MAR_R'), Loc::getMessage('EC_APR_R'), Loc::getMessage('EC_MAY_R'), Loc::getMessage('EC_JUN_R'), Loc::getMessage('EC_JUL_R'), Loc::getMessage('EC_AUG_R'), Loc::getMessage('EC_SEP_R'), Loc::getMessage('EC_OCT_R'), Loc::getMessage('EC_NOV_R'), Loc::getMessage('EC_DEC_R'));

		$APPLICATION->SetAdditionalCSS("/bitrix/js/calendar/cal-style.css");
		?>
		<div class="calendar-main-container" id="<?=$config['id']?>-main-container"></div>
		<script type="text/javascript">
		window.BXEventCalendar.Show(
			<?= Json::encode($config)?>,
			<?= Json::encode($data)?>,
			<?= Json::encode($additionalParams)?>
		);
		</script>
		<?
	}

	public static function GetWeekDays()
	{
		return array(
			array(Loc::getMessage('EC_MO_F'), Loc::getMessage('EC_MO'), 'MO'),
			array(Loc::getMessage('EC_TU_F'), Loc::getMessage('EC_TU'), 'TU'),
			array(Loc::getMessage('EC_WE_F'), Loc::getMessage('EC_WE'), 'WE'),
			array(Loc::getMessage('EC_TH_F'), Loc::getMessage('EC_TH'), 'TH'),
			array(Loc::getMessage('EC_FR_F'), Loc::getMessage('EC_FR'), 'FR'),
			array(Loc::getMessage('EC_SA_F'), Loc::getMessage('EC_SA'), 'SA'),
			array(Loc::getMessage('EC_SU_F'), Loc::getMessage('EC_SU'), 'SU')
		);
	}

	public static function GetWeekDaysEx($weekStart = 'MO')
	{
		$days = self::GetWeekDays();
		if ($weekStart == 'MO')
			return $days;
		$res = array();
		$start = false;
		while(list($k, $day) = each($days))
		{
			if ($day[2] == $weekStart)
			{
				$start = !$start;
				if (!$start)
					break;
			}
			if ($start)
				$res[] = $day;

			if ($start && $k == 6)
				reset($days);
		}
		return $res;
	}

	public static function GetAccessHTML($binging = 'calendar_section', $id = false)
	{
		if ($id === false)
			$id = 'bxec-'.$binging;
		$arTasks = CCalendar::GetAccessTasks($binging);
		?>
		<span style="display:none;">
		<select id="<?= $id?>" class="bxec-task-select">
			<?foreach ($arTasks as $taskId => $task):?>
				<option value="<?=$taskId?>"><?= htmlspecialcharsex($task['title']);?></option>
			<?endforeach;?>
		</select>
		</span>
		<?
	}

	public static function GetUserfieldsEditHtml($eventId, $url = '')
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
		if (!$USER_FIELDS || count($USER_FIELDS) == 0)
			return;

		$url = CHTTP::urlDeleteParams($url, array("action", "sessid", "bx_event_calendar_request", "event_id", "reqId"));
		$url = $url.(strpos($url,'?') === false ? '?' : '&').'action=userfield_save&bx_event_calendar_request=Y&'.bitrix_sessid_get();
?>
<form method="post" name="calendar-event-uf-form<?=$eventId?>" action="<?= $url?>" enctype="multipart/form-data" encoding="multipart/form-data">
<input name="event_id" type="hidden" value="" />
<input name="reqId" type="hidden" value="" />
<table cellspacing="0" class="bxc-prop-layout">
	<?foreach ($USER_FIELDS as $arUserField):?>
		<tr>
			<td class="bxc-prop"><?= htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
			<td class="bxc-prop">
				<?$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$arUserField["USER_TYPE"]["USER_TYPE_ID"],
					array(
						"bVarsFromForm" => false,
						"arUserField" => $arUserField,
						"form_name" => "calendar-event-uf-form".$eventId
					), null, array("HIDE_ICONS" => "Y")
				);?>
			</td>
		</tr>
	<?endforeach;?>
</table>
</form>
<?
	}

	public static function GetUserfieldsViewHtml($eventId)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
		if (!$USER_FIELDS || count($USER_FIELDS) == 0)
			return;
		$bFound = false;

		foreach ($USER_FIELDS as $arUserField)
		{
			if ($arUserField['VALUE'] == "" || (is_array($arUserField['VALUE']) && !count($arUserField['VALUE'])))
				continue;

			if (!$bFound)
			{
				$bFound = true;
				?><table cellspacing="0" class="bxc-prop-layout"><?
			}
			?>

			<tr>
				<td class="bxc-prop-name"><?= htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
				<td class="bxc-prop-value">
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.view",
						$arUserField["USER_TYPE"]["USER_TYPE_ID"],
						array("arUserField" => $arUserField),
						null,
						array("HIDE_ICONS"=>"Y")
					);?>
				</td>
			</tr>
		<?
		}

		if ($bFound)
		{
			?></table><?
		}
	}

	public static function DisplayColorSelector($id, $key = 'sect', $colors = false)
	{
		if (!$colors)
		{
			$colors = array(
				'#DAA187','#78D4F1','#C8CDD3','#43DAD2','#EECE8F','#AEE5EC','#B6A5F6','#F0B1A1','#82DC98','#EE9B9A',
				'#B47153','#2FC7F7','#A7ABB0','#04B4AB','#FFA801','#5CD1DF','#6E54D1','#F73200','#29AD49','#FE5957'
			);
		}

		?>
		<div  class="bxec-color-inp-cont">
			<input class="bxec-color-inp" id="<?=$id?>-<?=$key?>-color-inp"/>
			<a  id="<?=$id?>-<?=$key?>-text-color-inp" href="javascript:void('');" class="bxec-color-text-link"><?= Loc::getMessage('EC_TEXT_COLOR')?></a>
		</div>
		<div class="bxec-color-cont" id="<?=$id?>-<?=$key?>-color-cont">
		<?foreach($colors as $i => $color):?><span class="bxec-color-it"><a id="<?=$id?>-<?=$key?>-color-<?=$i?>" style="background-color:<?= $color?>" href="javascript:void(0);"></a></span><?endforeach;?>
		</div>
		<?
	}

	public static function CheckBitrix24Limits($params)
	{
		global $APPLICATION;
		$result = !CCalendar::IsBitrix24() || CBitrix24BusinessTools::isToolAvailable(CCalendar::GetCurUserId(), "calendar");
		if (!$result)
		{
			?><div id="<?=$params['id']?>-bitrix24-limit" class="bxec-b24-limit-wrap"><?
			$APPLICATION->IncludeComponent("bitrix:bitrix24.business.tools.info", "", array("SHOW_TITLE" => "Y"));
			?></div><?
		}
		return $result;
	}
}
?>