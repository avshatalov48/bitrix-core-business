<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

//region Action Panel
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
{
	$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getEditButton();
}
//endregion
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage('MESSAGESERVICE_CONFIG_SENDER_LIMIT_TITLE'));

\Bitrix\Main\UI\Extension::load(['date', 'ui.fonts.opensans']);

$messageSuffix = (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '_ADMIN' : '';
?>
<div class="messageservice-view-progress messageservice-view-progress-show"><?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_LIMIT_TIP'.$messageSuffix)?></div>
<?
$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID"                   => $arResult["GRID_ID"],
		"COLUMNS"                   => $arResult["COLUMNS"],
		"ROWS"                      => $arResult["ROWS"],
		"SHOW_CHECK_ALL_CHECKBOXES" => true,
		"SHOW_ROW_CHECKBOXES"       => true,
		"SHOW_SELECTED_COUNTER"     => false,
		"SHOW_GRID_SETTINGS_MENU"   => true,
		"TOTAL_ROWS_COUNT"          => count($arResult["ROWS"]),
		'AJAX_ID'                   => isset($arParams['AJAX_ID']) ? $arParams['AJAX_ID'] : '',
		'AJAX_MODE'                 => "Y",
		"AJAX_OPTION_JUMP"          => "N",
		"AJAX_OPTION_STYLE"         => "N",
		"AJAX_OPTION_HISTORY"       => "N",
		'ALLOW_COLUMNS_SORT'        => false,
		'ALLOW_ROWS_SORT'        => false,
		'ALLOW_INLINE_EDIT' => true,
		'EDITABLE' => true,
		'SHOW_NAVIGATION_PANEL' => false,
		"SHOW_ACTION_PANEL" => true,
		"ACTION_PANEL" => $controlPanel,
	),
	$component
);

$inTimeMessage = explode('#TIME#', GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_IN_TIME'));

?>
<div class="messageservice-retry-description">
	<p><?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_DESCRIPTION')?>: <b data-role="retry-label"></b>
		<a href="#" data-role="retry-edit"><?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_CHANGE')?></a></p>
</div>
<div class="messageservice-retry-description">
	<div class="messageservice-retry-form" data-role="retry-form">
		<p>
			<label>
			<input type="radio" name="auto" data-role="retry-radio-1"> <?=htmlspecialcharsbx($inTimeMessage[0])?>
				<input value="" class="messageservice-retry-input messageservice-retry-time" data-role="retry-time"> <?=htmlspecialcharsbx($inTimeMessage[1])?>, <?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_TZ')?>
			<select class="messageservice-retry-input messageservice-retry-tz" data-role="retry-tz">
				<?foreach ($arResult['TZ_LIST'] as $tzid => $name):?>
					<option value="<?=htmlspecialcharsbx($tzid)?>" <?if ($tzid == $arResult['RETRY_TIME']['tz']):?> selected<?endif;?>>
						<?=htmlspecialcharsbx($name)?></option>
				<?endforeach;?>
			</select>
			</label>
		</p>
		<p>
			<label>
				<input type="radio" name="auto" data-role="retry-radio-2"> <?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_AUTO')?>
			</label>
		</p>
		<button type="submit" class="webform-small-button webform-small-button-accept" data-role="retry-save">
			<span class="webform-small-button-text"><?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_SAVE')?></span>
		</button>
		<button type="submit" class="webform-small-button webform-small-cancel" data-role="retry-cancel">
			<span class="webform-small-button-text"><?=GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_CANCEL')?></span>
		</button>
	</div>
</div>
<script>
	BX.ready(function()
	{
		BX.message(<?=\Bitrix\Main\Web\Json::encode(array(
			'MSGSRV_RT_L_AUTO' => GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_AUTO'),
			'MSGSRV_RT_L_IN_TIME' => GetMessage('MESSAGESERVICE_CONFIG_SENDER_RETRY_IN_TIME'),
		))?>);

		var labelNode = document.querySelector('[data-role="retry-label"]');
		var editNode = document.querySelector('[data-role="retry-edit"]');
		var radio1Node = document.querySelector('[data-role="retry-radio-1"]');
		var radio2Node = document.querySelector('[data-role="retry-radio-2"]');
		var timeNode = document.querySelector('[data-role="retry-time"]');
		var tzNode = document.querySelector('[data-role="retry-tz"]');
		var saveNode = document.querySelector('[data-role="retry-save"]');
		var cancelNode = document.querySelector('[data-role="retry-cancel"]');
		var formNode = document.querySelector('[data-role="retry-form"]');

		var unFormatTime = function(time)
		{
			var v = parseTimeValue(time);
			var q = time.split(/[\s:]+/);
			if (q.length == 3)
			{
				var mt = q[2];
				if (mt == 'pm' && q[0] < 12)
					q[0] = parseInt(q[0], 10) + 12;

				if (mt == 'am' && q[0] == 12)
					q[0] = 0;

			}
			return v.h * 3600 + v.i * 60;
		};

		var parseTimeValue = function(time)
		{
			var q = time.split(/[\s:]+/);
			if (q.length == 3)
			{
				var mt = q[2];
				if (mt == 'pm' && q[0] < 12)
					q[0] = parseInt(q[0], 10) + 12;

				if (mt == 'am' && q[0] == 12)
					q[0] = 0;

			}
			return {h: parseInt(q[0], 10), i:  parseInt(q[1], 10)};
		};


		var formatTime = function(h, i)
		{
			var date = new Date();
			date.setHours(h || 9);
			date.setMinutes(i || 0);

			var dateFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')).replace(/:?\s*s/, ''),
				timeFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')).replace(/:?\s*s/, ''),
				str1 = BX.date.format(dateFormat, date),
				str2 = BX.date.format(timeFormat, date);
			return BX.util.trim(str2.replace(str1, ''));
		};

		var refreshUI = function()
		{
			if (retryTime.auto)
			{
				radio2Node.checked = true;
				labelNode.textContent = BX.message('MSGSRV_RT_L_AUTO');
			}
			else
			{
				radio1Node.checked = true;
				labelNode.textContent = BX.message('MSGSRV_RT_L_IN_TIME').replace('#TIME#', timeNode.value);
			}
		};

		var retryTime = <?=\Bitrix\Main\Web\Json::encode($arResult['RETRY_TIME'])?>;

		timeNode.value = formatTime(retryTime.h, retryTime.i);

		refreshUI();

		var clockInstance = new BX.CClockSelector({
			start_time: unFormatTime(timeNode.value),
			node: timeNode,
			callback: function (v)
			{
				timeNode.value = v;
				clockInstance.closeWnd();
				radio1Node.checked = true;
			}
		});

		BX.bind(timeNode, 'click', function()
		{
			clockInstance.Show();
		});
		BX.bind(tzNode, 'bxchange', function()
		{
			radio1Node.checked = true;
		});

		BX.bind(editNode, 'click', function(e)
		{
			e.preventDefault();
			BX.addClass(formNode, 'messageservice-retry-form-visible');
		});

		BX.bind(saveNode, 'click', function()
		{
			var btn = this;
			var timeInfo = parseTimeValue(timeNode.value);

			BX.addClass(btn, 'webform-small-button-wait');

			retryTime = {
				auto: radio2Node.checked ? 1 : 0,
				h: timeInfo.h,
				i: timeInfo.i,
				tz: tzNode.value
			};

			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: '/bitrix/components/bitrix/messageservice.config.sender.limits/ajax.php',
				data: {
					sessid: BX.bitrix_sessid(),
					site_id: '<?=(defined('ADMIN_SECTION') && ADMIN_SECTION === true ? '' : CUtil::JSEscape(SITE_ID))?>',
					retry_time: retryTime,
					action: 'set_retry_time'
				},
				onsuccess: function (response)
				{
					if (!response.success)
					{
						window.alert(response.errors[0]);
					}

					BX.removeClass(formNode, 'messageservice-retry-form-visible');
					BX.removeClass(btn, 'webform-small-button-wait');
					refreshUI();
				}
			});
		});
		BX.bind(cancelNode, 'click', function()
		{
			BX.removeClass(formNode, 'messageservice-retry-form-visible');
		});
	})
</script>
