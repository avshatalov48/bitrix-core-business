<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'sender.error_handler',
	'bitrix24.phoneverify'
]);

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

$canViewClient = $arParams['CAN_VIEW_CLIENT'];
$canPauseStartStop = $arParams['CAN_PAUSE_START_STOP'];
foreach ($arResult['ROWS'] as $index => $data)
{
	$letterId = (int)$data['ID'];
	$canEdit = $arParams['CAN_EDIT'];
	$enablePhoneVerificationForLetter =
		(! $arParams['IS_PHONE_CONFIRMED'])
		&& $arParams['IS_BX24_INSTALLED']
		&& ($data['MESSAGE_CODE'] === \Bitrix\Sender\Message\iBase::CODE_MAIL)
	;

	// user
	if ($data['USER'] && $data['USER_PATH'])
	{
		$data['USER'] = '<a href="' . htmlspecialcharsbx($data['USER_PATH']) . '" target="_blank">'
			.  htmlspecialcharsbx($data['USER'])
			. '</a>';
	}
	else
	{
		$data['USER'] = htmlspecialcharsbx($data['USER']);
	}

	// title
	if ($data['TITLE'] && $data['URLS']['EDIT'])
	{
		ob_start();
		?>
		<a class="sender-letter-list-link" onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($data['URLS']['EDIT'])?>'); return false;" href="<?=htmlspecialcharsbx($data['URLS']['EDIT'])?>">
			<?=htmlspecialcharsbx($data['TITLE'])?>
		</a>
		<div class="sender-letter-list-desc-small-black">
			<?=htmlspecialcharsbx($data['MESSAGE_NAME'])?>
		</div>
		<?
		$data['TITLE'] = ob_get_clean();
	}

	// actions
	{
		ob_start();

		$buttonCaption = ''; $buttonColor = '';
		$buttonIcon = ''; $buttonAction = '';
		$buttonTitle = '';
		if ($data['STATE']['isSent'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_IS_SENT');
			$date = $data['STATE']['dateSent'];
		}
		elseif ($data['STATE']['isStopped'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_IS_STOPPED');
			$date = $data['STATE']['dateSent'];
		}
		elseif ($data['STATE']['isPlanned'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_WILL_SEND');
			$date = $data['STATE']['datePlannedSend'];

			if ($canEdit && $data['STATE']['canStop'])
			{
				$buttonCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND');
				$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND_TITLE');
				$buttonColor = 'grey'; // red, grey
				$buttonIcon = 'play'; // play, resume
				$buttonAction = $enablePhoneVerificationForLetter
					? "BX.Bitrix24.PhoneVerify.getInstance().startVerify({callback: function (verified) { verified && BX.Sender.LetterList.send({$letterId}); }});"
					: "BX.Sender.LetterList.send({$letterId});";
			}
		}
		elseif ($data['STATE']['isSending'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_DUR_DATE_FINISH');
			$date = $data['DURATION'];

			if ($canEdit && $data['STATE']['canPause'])
			{
				$buttonCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_PAUSE');
				$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_STATE_PAUSE_TITLE');
				$buttonColor = 'grey'; // red, grey
				$buttonIcon = 'pause'; // play, resume
				$buttonAction = "BX.Sender.LetterList.pause({$letterId});";
			}
		}
		elseif ($data['STATE']['isPaused'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_IS_PAUSED');
			$date = $data['STATE']['datePause'];

			if ($canEdit && $data['STATE']['canResume'])
			{
				$buttonCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_RESUME');
				$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_STATE_RESUME_TITLE');
				$buttonColor = 'red'; // red, grey
				$buttonIcon = 'resume'; // play, resume
				$buttonAction = $enablePhoneVerificationForLetter
					? "BX.Bitrix24.PhoneVerify.getInstance().startVerify({callback: function (verified) { verified && BX.Sender.LetterList.resume({$letterId}); }});"
					: "BX.Sender.LetterList.resume({$letterId});";
			}
		}
		else
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_DUR_DATE_CREATE');
			$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_DUR_DATE_CREATE_TITLE');
			$date = $data['STATE']['dateCreate'];

			if ($canEdit && $data['STATE']['canSend'])
			{
				$buttonCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND');
				$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND_TITLE');
				$buttonColor = 'green'; // red, grey
				$buttonIcon = 'play'; // play, resume
				$buttonAction = $enablePhoneVerificationForLetter
					? "BX.Bitrix24.PhoneVerify.getInstance().startVerify({callback: function (verified) { verified && BX.Sender.LetterList.send({$letterId}); }});"
					: "BX.Sender.LetterList.send({$letterId});";
			}
		}

		$buttonAction = htmlspecialcharsbx($buttonAction);
		?>
		<div class="sender-letter-list-block-flexible">
			<?if ($buttonCaption && $canPauseStartStop):?>
			<div onclick="<?=$buttonAction?> event.stopPropagation(); return false;" class="sender-letter-list-button sender-letter-list-button-<?=$buttonColor?>" title="<?=htmlspecialcharsbx($buttonTitle)?>">
				<span class="sender-letter-list-button-icon sender-letter-list-button-icon-<?=$buttonIcon?>"></span>
					<span class="sender-letter-list-button-name">
					<?=htmlspecialcharsbx($buttonCaption)?>
				</span>
			</div>
			<?endif;?>
			<div class="sender-letter-list-desc-date">
				<div class="sender-letter-list-desc-small-grey">
					<?=htmlspecialcharsbx($dateCaption)?>
				</div>
				<div class="sender-letter-list-desc-small-grey">
					<?=htmlspecialcharsbx($date)?>
				</div>
			</div>
		</div>
		<?
		$data['ACTIONS'] = ob_get_clean();
	}

	// status
	if ($data['STATE'])
	{
		ob_start();
		?>
		<div class="sender-letter-list-desc-normal-black">
			<span class="sender-letter-list-desc-normal-text"><?=htmlspecialcharsbx($data['STATE_NAME'])?></span>
			<?if ($data['STATE']['isSendingLimitExceeded'] && !$data['STATE']['isSendingLimitTemporary']):?>
				<span class="sender-letter-list-icon-speedo" title="<?=$data['STATE']['isSendingLimitWaiting']
					? Loc::getMessage('SENDER_LETTER_LIST_SPEED_WAITING_SEND_TITLE', [
						'%day%' => $data['LIMITATION']['DAY'],
						'%time%' => $data['LIMITATION']['TIME'],
					])
					: Loc::getMessage('SENDER_LETTER_LIST_SPEED_TITLE')?>"></span>
			<?endif;?>
		</div>
		<div class="sender-letter-list-desc-normal-grey">
			<?

			if ($data['WAITING_RECIPIENT'] === 'Y')
			{
			?>
			<span class="sender-letter-list-circular-box" title="<?php echo Loc::getMessage('SENDER_DISPATCH_STATE_M')?>">
					<svg class="sender-letter-list-button-icon sender-letter-list-circular" viewBox="25 25 50 50">
						<circle class="sender-letter-list-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						<circle class="sender-letter-list-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					</svg>
				</span>
			<?
			}
			elseif ($data['STATE']['isFinished'])
			{
				$count = number_format((int) $data['COUNT']['sent'], 0, '.', ' ');
				?>
				<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_RECIPIENTS_SENT')?>">
					<span class="sender-letter-list-icon-subject"></span>
					<?=$count?>
				</span>
				<?
			}
			elseif ($data['STATE']['wasStartedSending'])
			{
				if ($data['STATE']['isSending'])
				{
					?>
					<span class="sender-letter-list-circular-box" title="<?=Loc::getMessage('SENDER_LETTER_LIST_SENDING_LOADER_TITLE')?>">
						<svg class="sender-letter-list-button-icon sender-letter-list-circular" viewBox="25 25 50 50">
							<circle class="sender-letter-list-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							<circle class="sender-letter-list-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						</svg>
					</span>
					<?
				}

				$sent = 1;
				if ($data['COUNT']['all'])
				{
					$sent = round($data['COUNT']['sent'] / $data['COUNT']['all'], 2);
				}
				$sent *= 100;
				?>
				<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_SENDING_TITLE', array('%count%' => "$sent%"))?>">
					<?if (!$data['STATE']['isSending']):?>
						<span class="sender-letter-list-icon-subject"></span>
					<?endif;?>
					<?=htmlspecialcharsbx($sent)?>%
				</span>
				<?
				if (!$data['STATE']['isSending'])
				{
					?>
					<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_DURATION')?>">
						<span class="sender-letter-list-icon-time"></span>
						<?=htmlspecialcharsbx($data['DURATION']);?>
					</span>
					<?
				}
			}
			else
			{
				$count = number_format((int) $data['COUNT']['all'], 0, '.', ' ');
				?>
				<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_RECIPIENTS_ALL')?>">
					<span class="sender-letter-list-icon-subject"></span>
					<?=$count?>
				</span>
				<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_DURATION')?>">
					<span class="sender-letter-list-icon-time"></span>
					<?=htmlspecialcharsbx($data['DURATION']);?>
				</span>
				<?
			}
			?>
		</div>
		<?
		$data['STATUS'] = ob_get_clean();
	}

	// statistics
	ob_start();
	if ($data['POSTING_ID'] && $canViewClient && $data['WAITING_RECIPIENT'] === 'N')
	{

		?>
		<a class="sender-letter-list-link"
			onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($data['URLS']['RECIPIENT'])?>'); return false;"
			href="<?=htmlspecialcharsbx($data['URLS']['RECIPIENT'])?>"
		>
			<?=Loc::getMessage('SENDER_LETTER_LIST_ROW_RECIPIENT')?>
		</a>
		<br/>
		<?
	}
	if ($data['TRACK_MAIL'] !== 'Y')
	{
		?>
		<div class="sender-letter-list-desc-small-grey">
			<?=Loc::getMessage('SENDER_LETTER_LIST_TRACKING_OFF')?>
		</div>
		<br/>
		<?
	}
	elseif ($data['HAS_STATISTICS'])
	{

		?>
		<a class="sender-letter-list-link"
			onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($data['URLS']['STAT'])?>'); return false;"
			href="<?=htmlspecialcharsbx($data['URLS']['STAT'])?>"
		>
			<?
			$views = $data['STATS']['READ'];
			$views .= '%';
			echo Loc::getMessage('SENDER_LETTER_LIST_ROW_STATS_VIEWS', ['#COUNT#' => $views])?>
		</a>,
		<a class="sender-letter-list-link"
			onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($data['URLS']['STAT'])?>'); return false;"
			href="<?=htmlspecialcharsbx($data['URLS']['STAT'])?>"
		>
			<?
			$clicks = $data['STATS']['CLICK'];
			$clicks .= '%';
			$unsub = $data['STATS']['UNSUB'];
			$unsub .= '%';
			echo Loc::getMessage('SENDER_LETTER_LIST_ROW_STATS_CLICKS', ['#COUNT#' => $clicks, '#UNSUB#' => $unsub])?>
		</a>
		<?
	}
	$data['STATS'] = ob_get_clean();

	if(isset($data['CONSENT_SUPPORT']))
	{
		ob_start();
		?>
		<span title="<?=(
			$data['CONSENT_SUPPORT']?
				Loc::getMessage("SENDER_LETTER_LIST_COMP_UI_ROW_CONSENT_TITLE_Y"):
				Loc::getMessage("SENDER_LETTER_LIST_COMP_UI_ROW_CONSENT_TITLE_N")
		)?>">
			<?=($data['CONSENT_SUPPORT']?
				Loc::getMessage("SENDER_LETTER_LIST_COMP_UI_ROW_CONSENT_SUPPORT_Y"):
				Loc::getMessage("SENDER_LETTER_LIST_COMP_UI_ROW_CONSENT_SUPPORT_N")
			)
			?>
		</span>
		<?
		$data['CONSENT_SUPPORT'] = ob_get_clean();
	}
	$actions = array();
	$actions[] = array(
		'TITLE' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_LETTER_LIST_BTN_EDIT_TITLE') : Loc::getMessage('SENDER_LETTER_LIST_BTN_VIEW_TITLE'),
		'TEXT' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_LETTER_LIST_BTN_EDIT') : Loc::getMessage('SENDER_LETTER_LIST_BTN_VIEW'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['EDIT'])."')",
		'DEFAULT' => true
	);
	if ($arParams['CAN_EDIT'])
	{
		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_BTN_COPY_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_BTN_COPY'),
			'ONCLICK' => "BX.Sender.LetterList.copy({$letterId});"
		);

		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_BTN_REMOVE_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_BTN_REMOVE'),
			'ONCLICK' => "BX.Sender.LetterList.remove({$letterId});"
		);
	}


	$stateActions = array();
	if ($data['STATE']['canSend'] && $canEdit)
	{
		$stateActions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_STATE_SEND'),
			'ONCLICK' => $enablePhoneVerificationForLetter
				? "BX.Bitrix24.PhoneVerify.getInstance().startVerify({callback: function (verified) { verified && BX.Sender.LetterList.send({$letterId}); }});"
				: "BX.Sender.LetterList.send({$letterId});",
		);
	}
	if ($data['STATE']['canPause'] && $canEdit)
	{
		$stateActions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_STATE_PAUSE_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_STATE_PAUSE'),
			'ONCLICK' => "BX.Sender.LetterList.pause({$letterId});"
		);
	}
	if ($data['STATE']['canResume'] && $canEdit)
	{
		$stateActions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_STATE_RESUME_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_STATE_RESUME'),
			'ONCLICK' => $enablePhoneVerificationForLetter
				? "BX.Bitrix24.PhoneVerify.getInstance().startVerify({callback: function (verified) { verified && BX.Sender.LetterList.resume({$letterId}); }});"
				: "BX.Sender.LetterList.resume({$letterId});",
		);
	}
	if ($data['STATE']['canStop'] && $canEdit)
	{
		$stateActions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_STATE_STOP_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_STATE_STOP'),
			'ONCLICK' => "BX.Sender.LetterList.stop({$letterId});"
		);
	}

	if (count($stateActions) > 0)
	{
		$actions[] = array('SEPARATOR' => true);
		$actions = array_merge($actions, $stateActions);
	}

	// statistics
	if ($data['HAS_STATISTICS'])
	{
		$actions[] = array('SEPARATOR' => true);
		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_BUTTON_STAT'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_BUTTON_STAT'),
			'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['STAT'])."')",
		);
	}
	if ($data['POSTING_ID'])
	{
		if (!$data['HAS_STATISTICS'])
		{
			$actions[] = array('SEPARATOR' => true);
		}
		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_LETTER_LIST_BUTTON_RECIPIENT'),
			'TEXT' => Loc::getMessage('SENDER_LETTER_LIST_BUTTON_RECIPIENT'),
			'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['RECIPIENT'])."')",
		);
	}

	$arResult['ROWS'][$index] = array(
		'id' => $data['ID'],
		'columns' => $data,
		'actions' => $actions,
		'attrs' => array('data-message-code' => $data['MESSAGE_CODE'])
	);
}

ob_start();
$APPLICATION->IncludeComponent(
	"bitrix:main.ui.filter",
	"",
	array(
		"FILTER_ID" => $arParams['FILTER_ID'],
		"GRID_ID" => $arParams['GRID_ID'],
		"FILTER" => $arResult['FILTERS'],
		"FILTER_PRESETS" => $arResult['FILTER_PRESETS'],
		'ENABLE_LIVE_SEARCH' => true,
		"ENABLE_LABEL" => true,
	)
);
$filterLayout = ob_get_clean();

$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
	array('type' => 'buttons', 'list' => [
		$arParams['CAN_EDIT']
			?
			[
				'type' => 'list',
				'id' => 'SENDER_LETTER_BUTTON_ADD',
				'caption' => Loc::getMessage('SENDER_LETTER_LIST_BTN_ADD'),
				'href' => $arParams['PATH_TO_ADD']
			]
			:
			null,
	]),
	array('type' => 'filter', 'content' => $filterLayout),
	array('type' => 'buttons', 'list' => [
		[
			'type' => 'abuses',
			'href' => $arParams['PATH_TO_ABUSES'],
		],
		[
			'type' => 'settings',
			'items' => ['import']
		]
	]),
)));


$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
if ($arParams['CAN_EDIT'])
{
	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getRemoveButton();
}

$navigation =  $arResult['NAV_OBJECT'];
$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arParams['GRID_ID'],
		"COLUMNS" => $arResult['COLUMNS'],
		"ROWS" => $arResult['ROWS'],
		'NAV_OBJECT' => $navigation,
		'PAGE_SIZES' => $navigation->getPageSizes(),
		'DEFAULT_PAGE_SIZE' => $navigation->getPageSize(),
		'TOTAL_ROWS_COUNT' => $navigation->getRecordCount(),
		'NAV_PARAM_NAME' => $navigation->getId(),
		'CURRENT_PAGE' => $navigation->getCurrentPage(),
		'PAGE_COUNT' => $navigation->getPageCount(),
		'SHOW_PAGESIZE' => true,
		'SHOW_ROW_CHECKBOXES' => $arParams['CAN_EDIT'],
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_TOTAL_COUNTER' => true,
		'ACTION_PANEL' => $controlPanel,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	)
);
?>
<script>
	BX.ready(function () {
		BX.Sender.LetterList.init(<?=Json::encode(array(
			'actionUri' => $arResult['ACTION_URI'],
			'messages' => $arResult['MESSAGES'],
			"gridId" => $arParams['GRID_ID'],
			"pathToEdit" => $arParams['PATH_TO_EDIT'],
			'mess' => array()
		))?>);
	});
</script>
