<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-trigger-chain';

$dictionaryTimeList = array(
	array(
		'TYPE' => 'MI',
		'TEXT' => Loc::getMessage("SENDER_TRIGGER_CHAIN_DICT_TIME_MI"),
		'VALUE' => 1,
	),
	array(
		'TYPE' => 'HO',
		'TEXT' => Loc::getMessage("SENDER_TRIGGER_CHAIN_DICT_TIME_HO"),
		'VALUE' => 60,
	),
	array(
		'TYPE' => 'DA',
		'TEXT' => Loc::getMessage("SENDER_TRIGGER_CHAIN_DICT_TIME_DA"),
		'VALUE' => 60*24,
	),
	array(
		'TYPE' => 'WE',
		'TEXT' => Loc::getMessage("SENDER_TRIGGER_CHAIN_DICT_TIME_WE"),
		'VALUE' => 60*24*7,
	),
	array(
		'TYPE' => 'MO',
		'TEXT' => Loc::getMessage("SENDER_TRIGGER_CHAIN_DICT_TIME_MO"),
		'VALUE' => 60*24*30,
	),
);
?>
<script>
	BX.ready(function () {
		BX.Sender.Letter.Chain.init(<?=Json::encode([
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'campaignId' => $arParams['ID'],
			'dictionaryTimeList' => $dictionaryTimeList,
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'pathToLetterEdit' => $arParams['PATH_TO_LETTER_EDIT'],
			'mess' => [
				'moveUp' => Loc::getMessage('SENDER_TRIGGER_CHAIN_BTN_MOVE_UP'),
				'moveDown' => Loc::getMessage('SENDER_TRIGGER_CHAIN_BTN_MOVE_DOWN'),
				'remove' => Loc::getMessage('SENDER_TRIGGER_CHAIN_BTN_REMOVE'),
			]
		])?>);
	});
</script>

<?
$getSenderItemContainer = function (array $letter = []) use ($arParams)
{
	ob_start();
	?>
	<div data-role="letter"
		data-letter-id="<?=htmlspecialcharsbx($letter['ID'])?>"
		class="sender-trigger-chain-container-letter"
	>
		<div class="sender-trigger-status-mailing-time">
			<span data-role="letter-time-thr" ><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_THR")?></span>
			<span data-role="letter-time-now" style="display: none;"><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_NOW")?></span>
			<span data-role="letter-time"
				data-time-value="<?=intval($letter['TIME_SHIFT'])?>"
				class="sender_letter_container_time_text"
			>-</span>
			<?=GetMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_AFTER")?>
			<span class="sender-trigger-start-after-event">
				<?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_EVENT")?>
			</span>
			<span class="sender-trigger-start-after-letter">
				<?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_LETTER")?>
			</span>
			&nbsp;&nbsp;
			<a data-role="letter-time-btn"
				class="sender-link-email"
			>
				<?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_TIME_CHANGE")?>
			</a>
		</div>
		<div class="sender_letter_container"
			<div class="sender_letter_container_head">
				<div data-role="letter-menu" class="sender_letter_container_move">
					<div class="sender_letter_container_burger"></div>
				</div>
				<div class="sender_letter_container_sorter_view">
					<span class="sender_letter_container_sorter_icon">
						<span data-role="letter-num" class="sender_letter_container_sorter_text"></span>
					</span>
				</div>
				<h3>
					<span data-role="letter-title" class="sender_letter_container_caption">
						<?=htmlspecialcharsbx($letter['TITLE'])?>
					</span>
				</h3>
				<span class="sender_letter_container-info">
					<span class="sender_letter_container-create">
						<?=Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_CREATED")?>
					</span>
					<span>
						<?
						echo Loc::getMessage("SENDER_TRIGGER_CHAIN_FIELD_CREATED_TEXT", array(
							'%DATE_CREATE%' => '<span data-role="letter-date">'
								. htmlspecialcharsbx($letter['DATE_INSERT'])
								. '</span>',
							'%AUTHOR%' => '<a data-role="letter-user" target="_blank" class="sender_letter_container-author" '
								. 'href="/bitrix/admin/user_edit.php?ID='.htmlspecialcharsbx($letter['USER_ID']).'&lang='.LANGUAGE_ID.'">'
								. htmlspecialcharsbx($letter['USER_NAME']) . ' ' . htmlspecialcharsbx($letter['USER_LAST_NAME'])
								.'</a>',
						));
						?>
					</span>
				</span>

				<a data-role="letter-btn-edit"
					onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($letter['URLS']['EDIT'])?>');"
					class="ui-btn ui-btn-sm ui-btn-light-border"
				>
					<?if($arParams['CAN_EDIT']):?>
						<?=Loc::getMessage('SENDER_TRIGGER_CHAIN_LETTER_EDIT')?>
					<?else:?>
						<?=Loc::getMessage('SENDER_TRIGGER_CHAIN_LETTER_VIEW')?>
					<?endif;?>
				</a>

			</div>
	</div>
	<?

	return ob_get_clean();
}
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-trigger-chain-wrap">

	<script data-role="template-letter" type="text/html">
		<?=$getSenderItemContainer([
			'ID' => '',
			'TITLE' => '',
			'DATE_INSERT' => '',
			'USER_ID' => '',
			'USER_NAME' => '',
			'USER_LAST_NAME' => '',
		])?>
	</script>

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(
			array('type' => 'feedback')
		)),
	)));
	?>

	<div id="SENDER_TIME_DIALOG" class="sender-time-dialog">
		<b><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_TIME_DIALOG_TITLE")?></b> <br><br>
		<select id="SENDER_TIME_DIALOG_TYPE">
			<?foreach($dictionaryTimeList as $timeItem):?>
				<option	value="<?=$timeItem['TYPE']?>"><?=$timeItem['TEXT']?></option>
			<?endforeach;?>
		</select>

		<input type="text" id="SENDER_TIME_DIALOG_VALUE" value="">
		<br><br>
		<input type="button" id="SENDER_TIME_DIALOG_BTN_SAVE" value="<?=Loc::getMessage("SENDER_TRIGGER_CHAIN_TIME_DIALOG_BTN_APPLY")?>" class="adm-btn">
		<a href="javascript: void(0);" id="SENDER_TIME_DIALOG_BTN_CANCEL" class=""><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_TIME_DIALOG_BTN_CANCEL")?></a>
	</div>


	<div class="sender-trigger-status-mailing">
		<div class="sender-trigger-status-mailing-title"><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_LIST_EVENT_START")?></div>
		<div class="sender-mailing-group-container sender-mailing-group-add">
			<span class="sender-mailing-group-container-title">
				<span><?=htmlspecialcharsbx($arResult['ROW']['TRIGGER_FIELDS']['START']['NAME'])?></span>
			</span>
		</div>
	</div>

	<div data-role="letters" class="trigger_chain">
		<?
		$i = 0;
		foreach($arResult['LETTERS'] as $letter):
			$i++;

			echo $getSenderItemContainer($letter);

		endforeach;
		?>

	</div>
	<div class="sender-trigger-add-letter">
		<a class="ui-btn ui-btn-primary ui-btn-icon-add"
			onclick="BX.Sender.Page.open('<?=CUtil::JSEscape($arParams['PATH_TO_LETTER_ADD'])?>');"
		><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_LIST_ADD")?></a>
	</div>

	<div class="sender-trigger-status-mailing sender-trigger-status-mailing-finish">
		<div class="sender-trigger-status-mailing-title"><?=Loc::getMessage("SENDER_TRIGGER_CHAIN_LIST_EVENT_END")?></div>
		<div class="sender-mailing-group-container sender-mailing-group-ok">
			<span class="sender-mailing-group-container-title">
				<span>
					<?
					if($arResult['ROW']['TRIGGER_FIELDS']['END']['NAME'] <> '')
						echo htmlspecialcharsbx($arResult['ROW']['TRIGGER_FIELDS']['END']['NAME']);
					else
						echo Loc::getMessage("SENDER_TRIGGER_CHAIN_TRIGGER_NAME_DEFAULT");
					?>
				</span>
			</span>
		</div>
	</div>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:sender.ui.button.panel",
		"",
		array(
			'CLOSE' => array(
				'URL' => $arParams['PATH_TO_LIST'],
			),
		),
		false
	);
	?>

</div>