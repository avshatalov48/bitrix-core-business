<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load(['ui.hint', 'sender.error_handler']);

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$canEdit = $arParams['CAN_EDIT'];
$canPauseStartStop = $arParams['CAN_PAUSE_START_STOP'];
$canViewClient = $arParams['CAN_VIEW_CLIENT'];
$canEditAdv = $arParams['CAN_EDIT_ADV'];

foreach ($arResult['ROWS'] as $index => $data)
{

	// user
	if ($data['USER'] && $data['USER_PATH'])
	{
		$data['USER'] = '<a href="' . htmlspecialcharsbx($data['USER_PATH']) . '" target="_blank">'
			.  htmlspecialcharsbx($data['USER'])
			. '</a>';
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
		elseif ($data['STATE']['isStopped'] || $data['STATE']['isError'])
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_IS_STOPPED');
			$date = $data['STATE']['dateSent'];
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
				$buttonAction = "BX.Sender.LetterList.resume({$data['ID']});";
			}
		}
		else
		{
			$dateCaption = Loc::getMessage('SENDER_LETTER_LIST_DUR_DATE_CREATE');
			$date = $data['STATE']['dateCreate'];

			if ($canEdit && $data['STATE']['canSend'])
			{
				$buttonCaption = Loc::getMessage('SENDER_LETTER_LIST_STATE_PUBLISH');
				$buttonTitle = Loc::getMessage('SENDER_LETTER_LIST_STATE_PUBLISH_TITLE');
				$buttonColor = 'green'; // red, grey
				$buttonIcon = 'play'; // play, resume
				$buttonAction = "BX.Sender.LetterList.send({$data['ID']});";
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
			<?=htmlspecialcharsbx($data['STATE_NAME'])?>
			<?if ($data['STATE']['isSendingLimitExceeded']):?>
				<span class="sender-letter-list-icon-speedo" title="<?=Loc::getMessage('SENDER_LETTER_LIST_SPEED_TITLE')?>"></span>
			<?endif;?>
			<?if ($data['STATE']['isError']):?>
				<span data-hint="<?=$data['ERROR_MESSAGE']?>" class="ui-hint"></span>
			<?endif?>
		</div>
		<div class="sender-letter-list-desc-normal-grey">
			<?
			if ($data['STATE']['isFinished'])
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
					<svg class="sender-letter-list-button-icon sender-letter-list-circular" viewBox="25 25 50 50">
						<circle class="sender-letter-list-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						<circle class="sender-letter-list-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					</svg>
					<?
				}

				$sent = 1;
				if ($data['COUNT']['all'])
				{
					$sent = round($data['COUNT']['sent'] / $data['COUNT']['all'], 2);
				}
				$sent *= 100;
				?>
				<span title="<?=Loc::getMessage('SENDER_LETTER_LIST_RECIPIENTS_SENT')?>">
					<span class="sender-letter-list-icon-subject"></span>
					<?=htmlspecialcharsbx($sent)?>%
				</span>
				<?
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
					<?=htmlspecialcharsbx($data['DURATION'])?>
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
	if ($data['POSTING_ID'] && $canViewClient)
	{

		?>
		<a class="sender-letter-list-link"
			onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($data['URLS']['RECIPIENT'])?>'); return false;"
			href="<?=htmlspecialcharsbx($data['URLS']['RECIPIENT'])?>"
		>
			<?=Loc::getMessage('SENDER_LETTER_LIST_ROW_RECIPIENT')?>
		</a>
		<?
	}
	$data['STATS'] = ob_get_clean();


	$actions = array();
	if ($arParams['CAN_EDIT'])
	{
		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_YANDEX_TOLOKA_EDIT'),
			'TEXT' =>  Loc::getMessage('SENDER_YANDEX_TOLOKA_EDIT'),
			'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['EDIT'])."')",
			'DEFAULT' => true
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
	array('type' => 'filter', 'content' => $filterLayout),
	array('type' => 'buttons', 'list' => [
		$arParams['CAN_EDIT']
			?
			[
				'type' => 'add',
				'id' => 'SENDER_LETTER_BUTTON_ADD',
				'caption' => Loc::getMessage('SENDER_YANDEX_TOLOKA_TASK_ADD'),
				'href' => $arParams['PATH_TO_ADD']
			]
			:
			null
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
	<script type="text/javascript">
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
<?