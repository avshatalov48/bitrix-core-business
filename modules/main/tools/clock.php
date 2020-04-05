<?
IncludeModuleLangFile(__FILE__);

class CClock
{
	public static function Init(&$arParams)
	{
		if (!isset($arParams['inputId']))
			$arParams['inputId'] = 'bxclock_'.rand();
		if (!isset($arParams['inputName']))
			$arParams['inputName'] = $arParams['inputId'];
		if (!isset($arParams['step']))
			$arParams['step'] = 5;
		if ($arParams['view'] == 'select' && $arParams['step'] < 30)
			$arParams['step'] = 30;

		if ($arParams['view'] != 'inline')
			$arParams['view'] = 'input';
	}

	public static function Show($arParams)
	{
		global $APPLICATION;

		CClock::Init($arParams);
		$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');

		$inputId = htmlspecialcharsbx($arParams['inputId']);
		$inputName = htmlspecialcharsbx($arParams['inputName']);
		$initTime = htmlspecialcharsbx($arParams['initTime']);
		$inputTitle = htmlspecialcharsbx($arParams['inputTitle']);

		$jsInputId = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $arParams['inputId']);

		// Show input
		switch ($arParams['view'])
		{
			case 'label':
				?>
				<input type="hidden" id="<?=$inputId?>" name="<?=$inputName?>" value="<?=$initTime?>">
				<div class="bx-clock-label" onmouseover="this.className='bx-clock-label-over';" onmouseout="this.className='bx-clock-label';" onclick=""><? echo ($arParams['initTime']? $initTime : 'Time'); ?></div><?
				break;
			case 'select':
				?>
				<select id="<?=$inputId?>" name="<?=$inputName?>">
					<?
					for ($i = 0; $i < 24; $i++)
					{
						$h = ($i < 10) ? '0'.$i : $i;
						?><option value="<?=$h?>:00"><?=$h?>:00</option><?
						if ($arParams['step']) {?><option value="<?=$h?>:30"><?=$h?>:30</option><?}
					}
					?>
				</select>
				<?
				break;
			case 'inline':
				?>
				<input type="hidden" id="<?=$inputId?>" name="<?=$inputName?>"  value="<?=$initTime?>" />
				<div id="<?=$inputId?>_clock"></div>
				<script type="text/javascript">
					if (!window.bxClockLoaders)
					{
						window.bxClockLoaders = [];
						window.onload = function() {
							for (var i=0; i<window.bxClockLoaders.length; i++)
								setTimeout(window.bxClockLoaders[i], 20*i + 20);
							window.bxClockLoaders = null;
						}
					}

					window.bxClockLoaders.push("bxShowClock_<?=$jsInputId?>('<?=CUtil::JSEscape($arParams['inputId'])?>_clock');");
				</script>
				<?
				break;
			default: //input
				?><input id="<?=$inputId?>" <?=($arParams['inputName']? 'name="'.$inputName.'"' : '')?> type="text" value="<?=$initTime?>" size="<?=IsAmPmMode() ? 6 : 4?>" <?=($arParams['inputTitle']? 'title="'.$inputTitle.'"' : '')?> <?=($arParams['inputClass']? 'class="'.$arParams['inputClass'].'"' : '')?> autocomplete="off"/><?
				break;
		}
		// Show icon
		if ($arParams['showIcon'] !== false)
		{
			?><a href="javascript:void(0);" onclick="bxShowClock_<?=$jsInputId?>()" title="<?=GetMessage('BX_CLOCK_TITLE')?>" onmouseover="this.className='bxc-icon-hover';" onmouseout="this.className='';"><img id="<?=$inputId?>_icon" src="/bitrix/images/1.gif" class="bx-clock-icon bxc-iconkit-c"></a><?
		}

		//Init JS and append CSS
		?><script>
		function bxLoadClock_<?=$jsInputId?>(callback)
		{
			<?if($arParams['view'] != 'inline'):?>
			if (!window.JCClock && !window.jsUtils)
			{
				return setTimeout(function(){bxLoadClock_<?=$jsInputId?>(callback);}, 50);
			}
			<?endif;?>

			if (!window.JCClock)
			{
				if(!!window.bClockLoading)
				{
					return setTimeout(function(){bxLoadClock_<?=$jsInputId?>(callback);}, 50);
				}
				else
				{
					window.bClockLoading = true;
					return BX.load(
						[
							'<?=CUtil::GetAdditionalFileURL("/bitrix/js/main/clock.js")?>',
							'<?=CUtil::GetAdditionalFileURL("/bitrix/themes/.default/clock.css")?>'
						],
						function() {bxLoadClock_<?=$jsInputId?>(callback)}
					);
				}
			}

			window.bClockLoading = false;

			var obId = 'bxClock_<?=$jsInputId?>';

			window[obId] = new JCClock({
				step: <?=intval($arParams['step'])?>,
				initTime: '<?=CUtil::JSEscape($arParams['initTime'])?>',
				showIcon: <? echo $arParams['showIcon'] ? 'true' : 'false';?>,
				inputId: '<?=CUtil::JSEscape($arParams['inputId'])?>',
				iconId: '<?=CUtil::JSEscape($arParams['inputId']).'_icon'?>',
				zIndex: <?= isset($arParams['zIndex']) ? intval($arParams['zIndex']) : 0 ?>,
				AmPmMode: <? echo $arParams['am_pm_mode'] ? 'true' : 'false';?>,
				MESS: {
					Insert: '<?=GetMessageJS('BX_CLOCK_INSERT')?>',
					Close: '<?=GetMessageJS('BX_CLOCK_CLOSE')?>',
					Hours: '<?=GetMessageJS('BX_CLOCK_HOURS')?>',
					Minutes: '<?=GetMessageJS('BX_CLOCK_MINUTES')?>',
					Up: '<?=GetMessageJS('BX_CLOCK_UP')?>',
					Down: '<?=GetMessageJS('BX_CLOCK_DOWN')?>'
				}
				});

			return callback.apply(window, [window[obId]]);
		}

		function bxShowClock_<?=$jsInputId?>(id)
		{
			bxLoadClock_<?=$jsInputId?>(function(obClock)
			{
				obClock.Show(id);
			});
		}
	</script><?
	}
}
?>