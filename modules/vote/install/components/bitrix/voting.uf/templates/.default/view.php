<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI;
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CVoteUfComponent $component*/
if (empty($arResult['ATTACHES']))
	return;

UI\Extension::load("ui.buttons");

$this->IncludeLangFile("view.php");

CJSCore::Init(array('ajax', 'popup'));
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');
$style = "web";
if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 &&
	defined("BX_MOBILE") && BX_MOBILE === true)
	$style = "mobile";
$frame = $this->createFrame()->begin("");
/* @var \Bitrix\Vote\Attachment\Attach $attach*/
foreach ($arResult['ATTACHES'] as $attach)
{
	$uid = $component->getComponentId() . $attach["ID"];
	$canParticipate = $attach->canParticipate($USER->getId());
	$voted = $attach->isVotedFor($USER->getId());
	$lastVote = 0;
?>
<div class="bx-vote-container bx-vote-container-<?=$style?>" <?
	?>data-bx-vote-form="<?=($canParticipate && $voted === false ? "shown" : "hidden")?>" <?
	?>data-bx-vote-result="<?=($canParticipate && $voted === false ? "hidden" : "shown")?>" <?
	?>data-bx-vote-status="<?=($voted === false ? "ready" : "voted")?>" <?
	?>data-bx-vote-lamp="<?=($attach["LAMP"])?>" <?
	?>id="vote-<?= $uid ?>">
	<div style="color:red;" data-bx-vote-role="error"></div>
	<form action="" method="get" class="vote-form" name="vote-form-<?= $uid ?>">
		<input type="hidden" name="attachId" value="<?= $attach["ID"] ?>"/>
		<?= bitrix_sessid_post()
	?><input id="checkbox_<?=$uid?>" class="bx-vote-checkbox" type="checkbox" <?if ($arParams["VIEW_MODE"] == "EXTENDED"): ?> checked="checked"<?endif;?> /><?
	?><div class="bx-vote-body">
	<div class="bx-vote-block">
		<ol class="bx-vote-question-list">
		<?
		foreach ($attach["QUESTIONS"] as $question):
			?>
			<li id="question<?= $question["ID"] ?>"<? if ($question["REQUIRED"] == "Y"): ?> class="bx-vote-question-required"<? endif; ?>>
				<? if (!empty($question["IMAGE"]) && !empty($question["IMAGE"]["SRC"])): ?>
					<div class="bx-vote-question-image"><img src="<?= $question["IMAGE"]["SRC"] ?>"/>
					</div><? endif; ?>
				<div class="bx-vote-question-title"><?= FormatText($question["QUESTION"], $question["QUESTION_TYPE"]) ?></div>
				<table class="bx-vote-answer-list" cellspacing="0">
					<? foreach ($question["ANSWERS"] as $answer):
						$answer["MESSAGE"] = FormatText($answer["MESSAGE"], $answer["MESSAGE_TYPE"]);?>
						<tr data-bx-vote-answer="<?=$answer["ID"]?>" class="bx-vote-answer-item">
							<td>
								<div class="bx-vote-bar"><?
									switch ($answer["FIELD_TYPE"]):
										case 0://radio
											$value = "";
											if (!isset($question["answerIsFound"]) && isset($_REQUEST['vote_radio_' . $question["ID"]]) && $_REQUEST["vote_radio_" . $question["ID"]] == $answer["ID"])
											{
												$question["answerIsFound"] = $value = 'checked="checked"';
											}
											?><span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap"><?
												?><label class="bx-vote-block-input-wrap-inner" for="vote_radio_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?
													?><input type="radio" name="<?= $answer["FIELD_NAME"] ?>" <?
														?>id="vote_radio_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>" <?
														?>value="<?= $answer["ID"] ?>" <?= $value ?> /><?
													?><span class="bx-vote-block-inp-substitute"></span><?
												?></label><?
												?><label for="vote_radio_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
											?></span><?
											break;
										case 1://checkbox
											$value = "";
											if (is_array($_REQUEST["vote_checkbox_" . $question["ID"]]) && in_array($answer["ID"], $_REQUEST["vote_checkbox_" . $question["ID"]]))
											{
												$value = 'checked="checked"';
											}
											?><span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap"><?
												?><label class="bx-vote-block-input-wrap-inner" for="vote_checkbox_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?
													?><input type="checkbox" name="<?=$answer["FIELD_NAME"]?>[]" value="<?= $answer["ID"] ?>" <?
														?> id="vote_checkbox_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>" <?= $value ?> /><?
													?><span class="bx-vote-block-inp-substitute"></span><?
												?></label><?
												?><label for="vote_checkbox_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
											?></span><?
											break;
										case 2://select
											$value = "";
											if (!isset($question["answerIsFound"]) && isset($_REQUEST['vote_dropdown_' . $question["ID"]]) && $_REQUEST["vote_dropdown_" . $question["ID"]] == $answer["ID"])
											{
												$question["answerIsFound"] = $value = 'checked="checked"';
											}
											?><span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap"><?
												?><label class="bx-vote-block-input-wrap-inner" for="vote_dropdown_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?
													?><input type="radio" name="<?= $answer["FIELD_NAME"] ?>" <?
														?>id="vote_dropdown_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>" <?
														?>value="<?= $answer["ID"] ?>" <?= $value ?> /><?
													?><span class="bx-vote-block-inp-substitute"></span><?
												?></label><?
												?><label for="vote_dropdown_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
											?></span><?
											break;
										case 3://multiselect
											$value = "";
											if (is_array($_REQUEST["vote_multiselect_" . $question["ID"]]) && in_array($answer["ID"], $_REQUEST["vote_multiselect_" . $question["ID"]]))
											{
												$value = 'checked="checked"';
											}
											?><span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap"><?
												?><label class="bx-vote-block-input-wrap-inner" for="vote_multiselect_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?
													?><input type="checkbox" name="<?=$answer["FIELD_NAME"]?>[]" value="<?= $answer["ID"] ?>" id="vote_multiselect_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>" <?= $value ?> /><?
													?><span class="bx-vote-block-inp-substitute"></span><?
													?></label><?
												?><label for="vote_multiselect_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
											?></span><?
											break;
										case 4://text field
											$value = htmlspecialcharsbx($_REQUEST["vote_field_" . $answer["ID"]]);
											?><span class="bx-vote-block-input-wrap bx-vote-block-text-wrap"><?
												?><label for="vote_field_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
												?><input type="text" name="<?=$answer["FIELD_NAME"]?>" id="vote_field_<?= $answer["ID"] ?>" <?
													?>value="<?= $value ?>" size="<?= $answer["FIELD_WIDTH"] ?>" <?= $answer["~FIELD_PARAM"] ?> /><?
											?></span><?
											break;
										case 5://memo
											?><span class="bx-vote-block-input-wrap bx-vote-block-memo-wrap"><?
												?><label for="vote_memo_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label><?
												?><textarea name="<?=$answer["FIELD_NAME"]?>" id="vote_memo_<?= $answer["ID"] ?>" <?
													?><?= $answer["~FIELD_PARAM"] ?> cols="<?= $answer["FIELD_WIDTH"] ?>" <?
													?>rows="<?= $answer["FIELD_HEIGHT"] ?>"><?= htmlspecialcharsbx($_REQUEST["vote_memo_" . $answer["ID"]]) ?></textarea><?
											?></span><?
											break;
									endswitch;
									?>
									<div class="bx-vote-result-bar" data-bx-vote-result="bar" style="width:<?= $answer["PERCENT"] ?>%;"></div>
								</div>
							</td>
							<td>
				<span class="bx-vote-voted-users-wrap"><?
					?><a href="javascript:void(0);" class="bx-vote-voted-users" data-bx-vote-result="counter"><?= $answer["COUNTER"] ?></a><?
					?></span>
							</td>
							<td><span class="bx-vote-data-percent" data-bx-vote-result="percent"><?= $answer["PERCENT"]?>%</span></td>
						</tr>
					<?endforeach; ?>
				</table>
			</li>
		<?endforeach; ?>
			<li class="bx-vote-answer-result">
				<div class="bx-vote-answer-list-wrap">
					<div data-bx-vote-result="counter"><?=$attach["COUNTER"]?></div>
					<div><?=GetMessage("VOTE_RESULTS")?></div>
				</div>
			</li>
		</ol>
	</div>
<?
	if (isset($arResult["CAPTCHA_CODE"]) && ($voted === false || ($voted == 8 && $USER->isAuthorized())))
	{
	?><div class="bx-vote-captcha">
		<input type="hidden" name="captcha_code" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
		<span class="vote-captcha-image">
			<img src="/bitrix/tools/captcha.php?captcha_code=<?= $arResult["CAPTCHA_CODE"] ?>"/>
		</span>
		<span class="bx-vote-captcha-input">
			<label for="captcha_word"><?= GetMessage("F_CAPTCHA_PROMT") ?></label>
			<input type="text" size="20" name="captcha_word" id="captcha_word" />
		</span>
	</div><?
	}
		?><label for="checkbox_<?=$uid?>" class="bx-vote-switcher"><span class="bx-vote-switcher-arrow"></span></label><?
	?></div><?

	?><div class="bx-vote-buttons"><?
		if ($canParticipate)
		{
			?><button class="ui-btn ui-btn-lg ui-btn-link" data-bx-vote-button="showVoteForm"><?=GetMessage("VOTE_RESUBMIT_BUTTON")?></button><?
			?><button class="ui-btn ui-btn-lg ui-btn-primary" data-bx-vote-button="actVoting"><?= GetMessage("VOTE_SUBMIT_BUTTON") ?></button><?
		}
		?><button class="ui-btn ui-btn-lg ui-btn-link" data-bx-vote-button="showResults"><?=GetMessage("VOTE_RESULTS_BUTTON")?></button><?
		if ($attach->canEdit($USER->GetID()))
		{
			?><span href="#" data-bx-vote-button="stopOrResume"><?
				?><button class="ui-btn ui-btn-lg ui-btn-link"><?=GetMessage("VOTE_STOP_BUTTON")?></button><?
				?><button class="ui-btn ui-btn-lg ui-btn-link"><?=GetMessage("VOTE_RESUME_BUTTON")?></button><?
			?></span><?
			?><button class="ui-btn ui-btn-lg ui-btn-link" data-bx-vote-button="exportXls"><?=GetMessage("VOTE_EXPORT_BUTTON")?></button><?
		}
	?></div><?
?>
<script type="text/javascript">
BX.ready(function() {
	BX.message({
		VOTE_ERROR_DEFAULT : '<?=GetMessageJS("VOTE_ERROR_DEFAULT")?>'
	});
	new BX.Vote(BX('vote-<?= $uid ?>'), {
		id: <?=$attach["ID"]?>,
		voteId: <?=$attach["VOTE_ID"]?>,
		urlTemplate: '<?=CUtil::JSEscape($arParams["~PATH_TO_USER"] ?: "/company/personal/user/#ID#/");?>',
		nameTemplate: '<?=CUtil::JSEscape($arParams["~NAME_TEMPLATE"]);?>'
	});
<?
if ($GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull"))
{
	\CPullWatch::Add($GLOBALS["USER"]->GetID(), 'VOTE_'.$attach["VOTE_ID"]);
}
?>
});
</script>
	</form>
</div>
<?
}
$frame->end();