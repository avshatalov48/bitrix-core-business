<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

class CCalendarSceleton
{
	public static function InitJS($config = array(), $data = array(), $additionalParams = array())
	{
		global $APPLICATION;
		\Bitrix\Main\UI\Extension::load([
			'ajax',
			'window',
			'popup',
			'access',
			'date',
			'viewer',
			'color_picker',
			'sidepanel',
			'clipboard',
			'ui.alerts',
			'ui.buttons',
			'ui.buttons.icons',
			'ui.tooltip',
			'ui.entity-selector',
			'ui.forms',
			'ui.hint',
			'ui.analytics',
			'ui.confetti',
			'calendar.util',
			'calendar.entry',
			'calendar.search',
			'calendar.counters',
			'calendar.controls',
			'calendar.sliderloader',
			'calendar.sync.manager',
			'calendar.sync.interface',
			'calendar.categorymanager',
			'calendar.sharing.interface',
			'calendar.sharing.public',
		]);

		if(($config['type'] ?? null) === 'location')
		{
			\Bitrix\Main\UI\Extension::load([
				'calendar.rooms',
				'calendar.roomsmanager',
			]);
		}
		else
		{
			\Bitrix\Main\UI\Extension::load(['calendar.sectionmanager']);
		}

		if(\Bitrix\Main\Loader::includeModule('rest'))
		{
			\Bitrix\Main\UI\Extension::load('applayout');
		}

		if(\Bitrix\Main\Loader::includeModule('webservice'))
		{
			\Bitrix\Main\UI\Extension::load('stssync');
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
		<div class="calendar-main-container calendar-main-container--scope" id="<?=$config['id']?>-main-container"></div>

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
		if ($weekStart === 'MO')
		{
			return $days;
		}

		$res = [];
		$startIndex = false;

		foreach ($days as $k => $day)
		{
			if ($day[2] === $weekStart)
			{
				$startIndex = $k;
			}

			if ($startIndex !== false)
			{
				$res[] = $day;
			}
		}

		for ($i = 0; $i < $startIndex; $i++)
		{
			$res[] = $days[$i];
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

	/**
	 * @param string $title
	 * @param string $content
	 * @return bool
	 */
	public static function showCalendarGridError(string $title, string $content = ''): bool
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:calendar.grid.error",
			"",
			[
				'TITLE' => $title,
				'CONTENT' => $content,
			]
		);

		return true;
	}
}
?>