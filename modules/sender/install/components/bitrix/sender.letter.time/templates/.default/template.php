<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var \CBitrixComponentTemplate $this */
/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Integration\Bitrix24;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Main\UI\Extension;

Extension::load("ui.buttons");
Extension::load("ui.notification");

$component = $this->getComponent();
$getMessageLocal = function($messageCode, $replace = []) use ($component)
{
	/** @var \SenderLetterTimeComponent $component */
	return $component->getMessage($messageCode, $replace);
};

Bitrix24\Service::initLicensePopup();

$containerId = 'sender-letter-time';
?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-letter-time">
	<script type="text/javascript">
		BX.ready(function () {
			BX.Sender.Letter.Time.init(<?=Json::encode(array(
				'containerId' => $containerId,
				'actionUrl' => $arResult['ACTION_URL'],
				'isFrame' => $arParams['IFRAME'] == 'Y',
				'isSaved' => $arResult['IS_SAVED'],
				'isOutside' => $arParams['IS_OUTSIDE'],
				'canEdit' => $arResult['CAN_CHANGE'],
				'isSupportReiterate' => $arResult['IS_SUPPORT_REITERATE'],
				'prettyDateFormat' => PrettyDate::getDateFormat(),
				'mess' => array(
					'atTime' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_AT_TIME'),
					'time' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_TIME'),
					'defered' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_DEFERED'),
					'now' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_NOW'),
					'schedule' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_REITERATE'),
					'accept' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_ACCEPT'),
					'cancel' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_CANCEL'),
					'scheduleText' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_TEXT'),
					'scheduleTextMo' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_TEXT_MO'),
					'outsideSaveSuccess' => $getMessageLocal(
						'SENDER_LETTER_TIME_OUTSIDE_ADD_SUCCESS',
						['%path%' => $arParams['PATH_TO_LIST']]
					)
				)
			))?>);
		});
	</script>
	<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
		<?=bitrix_sessid_post()?>

		<div class="sender-letter-time-title">
			<?
			echo $getMessageLocal(
				'SENDER_LETTER_TIME_TMPL_TITLE_' . (!$arResult['CAN_CHANGE'] ? 'EXISTS' : 'NEW'),
				array(
					'%name%' => '<div class="sender-letter-time-title-highlight">"'
								. htmlspecialcharsbx($arResult['TITLE'])
								. '"</div>'
				)
			);
			?>
		</div>
		<div class="sender-letter-time-icon"></div>
		<div class="sender-letter-time-button-container">
			<div class="sender-letter-time-button" style="<?=($arResult['CAN_CHANGE'] ? 'display: none;' : '')?>">
				<span class="sender-letter-time-button-name"><?=$getMessageLocal('SENDER_LETTER_TIME_TMPL_DATE_SEND')?>:</span>
				<a class="">
					<?=htmlspecialcharsbx($arResult['DATE_SEND'])?>
				</a>
			</div>
			<div class="sender-letter-time-button" style="<?=(!$arResult['CAN_CHANGE'] ? 'display: none;' : '')?>">
				<span class="sender-letter-time-button-name"><?=$getMessageLocal('SENDER_LETTER_TIME_TMPL_ACT_SEND')?>:</span>
				<a data-role="time-selector"
					class="<?=($arResult['CAN_CHANGE'] ? 'sender-letter-time-link' : '')?>"
				></a>
				<input data-role="time-input"  type="hidden" name="LETTER_TIME" value="<?=htmlspecialcharsbx($arResult['LETTER_TIME'])?>">
			</div>
		</div>

		<input data-role="time-reiterate-days-of-week" type="hidden" name="DAYS_OF_WEEK" value="<?=htmlspecialcharsbx($arResult['DAYS_OF_WEEK'])?>">
		<input data-role="time-reiterate-times-of-day" type="hidden" name="TIMES_OF_DAY" value="<?=htmlspecialcharsbx($arResult['TIMES_OF_DAY'])?>">
		<input data-role="time-reiterate-days-of-month" type="hidden" name="DAYS_OF_MONTH" value="<?=htmlspecialcharsbx($arResult['DAYS_OF_MONTH'])?>">
		<input data-role="time-reiterate-months-of-year" type="hidden" name="MONTHS_OF_YEAR" value="<?=htmlspecialcharsbx($arResult['MONTHS_OF_YEAR'])?>">

		<?if (!empty($arResult['LIMITATION'])):?>
			<div class="sender-letter-info">
				<?=htmlspecialcharsbx($arResult['LIMITATION']['TEXT'])?>
				<?if ($arResult['LIMITATION']['SETUP_URI']):?>
					<a href="<?=htmlspecialcharsbx($arResult['LIMITATION']['SETUP_URI'])?>">
						<div class="sender-hint">
							<div class="sender-hint-icon"></div>
						</div>
					</a>
				<?endif;?>
			</div>
		<?endif;?>


		<?
		$APPLICATION->IncludeComponent(
			"bitrix:sender.ui.button.panel",
			"",
			array(
				'SAVE' => $arResult['CAN_CHANGE'] ? [] : null,
				'CLOSE' => $arResult['CAN_CHANGE'] ? null : ['URL' => $arParams['PATH_TO_LIST']],
			),
			false
		);
		?>

	</form>

	<div style="display: none;">
		<div data-role="time-reiterate" class="sender-letter-time-popup">
			<div class="sender-letter-time-popup-time-box">
				<div class="sender-letter-time-popup-time-name"><?=Loc::getMessage('SENDER_LETTER_TIME_TMPL_WEEK_DAYS_TIME')?>:</div>
				<select data-role="reiterate-times-of-day" class="sender-letter-time-popup-time-input sender-letter-time-popup-time-select">
					<?foreach ($arResult['TIME_LIST'] as $time):
						$time = htmlspecialcharsbx($time);
						?>
						<option value="<?=$time?>" <?=($time === '09:00' ? 'selected' : '')?>><?=$time?></option>
					<?endforeach?>
				</select>
			</div>

			<div class="sender-letter-time-popup-date-box">
				<div class="sender-letter-time-popup-date">
					<?
					$weekDays = [
						['id' => '1', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_MON'), 'selected' => true],
						['id' => '2', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_TUE'), 'selected' => true],
						['id' => '3', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_WED'), 'selected' => true],
						['id' => '4', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_THU'), 'selected' => true],
						['id' => '5', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_FRI'), 'selected' => true],
						['id' => '6', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_SAT'), 'selected' => true],
						['id' => '7', 'name' => Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY_SUN'), 'selected' => true],
					];
					foreach ($weekDays as $weekDay)
					{
						$dayNum = htmlspecialcharsbx($weekDay['id']);
						$dayName = htmlspecialcharsbx($weekDay['name']);
						$daySelected = (bool) $weekDay['selected'];
						?>
						<div class="sender-letter-time-popup-date-item sender-letter-time-popup-date-item-current"
							data-role="reiterate-days-of-week"
							data-value="<?=$dayNum?>"
						>
							<?=$dayName?>
						</div>
						<?
					}
					?>
				</div>

				<div class="sender-letter-time-schedule-addit">
					<a class="sender-letter-time-schedule-addit-btn"
						data-role="reiterate-additional-btn"
					>
						<?=Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_SHOW_ADDITIONAL')?>
					</a>

					<div data-role="reiterate-additional" style="display: none;">
						<div class="sender-letter-time-schedule-addit-section">
							<div class="sender-letter-time-schedule-addit-caption"><?=Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_DAY')?>:</div>
							<div>
								<?
								$rowCount = 8;
								for ($row = 0; $row < 4; $row++)
								{
									?><div class="sender-letter-time-popup-date"><?
									for ($dayNum = 1; $dayNum <= $rowCount; $dayNum++)
									{
										$num = $dayNum + $row * $rowCount;
										if ($num > 31)
										{
											$num = '';
										}
										?>
										<div class="sender-letter-time-popup-date-item"
											data-role="reiterate-days-of-month"
											data-value="<?=$num?>"
										><?=$num?></div>
										<?
									}
									?></div><?
								}
								?>
							</div>
						</div>

						<div class="sender-letter-time-schedule-addit-section">
							<div class="sender-letter-time-schedule-addit-caption"><?=Loc::getMessage('SENDER_LETTER_TIME_TMPL_SCHEDULE_MONTH')?>:</div>
							<div>
							<?
							$rowCount = 6;
							$date = \Bitrix\Main\Type\DateTime::createFromTimestamp(mktime(0,0,0,1,1,2049));
							for ($row = 0; $row < 2; $row++)
							{
								?><div class="sender-letter-time-popup-date"><?
								for ($monNum = 1; $monNum <= $rowCount; $monNum++)
								{
									$num = $monNum + $row * $rowCount;
									$name = htmlspecialcharsbx(\FormatDate('M', $date));
									$date->add("+1 months");
									?>
									<div class="sender-letter-time-popup-date-item"
										data-role="reiterate-months-of-year"
										data-value="<?=$num?>"
									><?=$name?></div>
									<?
								}
								?></div><?
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>