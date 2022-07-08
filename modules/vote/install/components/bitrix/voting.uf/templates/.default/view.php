<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Vote\Vote\Option;
use Bitrix\Vote\QuestionTypes;
use Bitrix\Vote\AnswerTypes;
use Bitrix\Vote\Vote\Anonymity;

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
{
	return;
}

UI\Extension::load([ 'ui.buttons', 'ajax', 'popup', 'ui.design-tokens' ]);

$this->IncludeLangFile('view.php');
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');
$style = 'web';

if (
	(
		(
			isset($arParams['MOBILE'])
			&& $arParams['MOBILE'] === 'Y'
		)
		|| (
			defined('BX_MOBILE')
			&& BX_MOBILE === true
		)
	)
	&& is_callable([ '\Bitrix\MobileApp\Mobile', 'getApiVersion' ])
	&& \Bitrix\MobileApp\Mobile::getApiVersion() >= 1
)
{
	$style = 'mobile';
}

$frame = $this->createFrame()->begin('');
$request = (\Bitrix\Main\Context::getCurrent()->getRequest()->toArray());
/* @var \Bitrix\Vote\Attach $attach*/
foreach ($arResult['ATTACHES'] as $attach)
{
	$uid = $component->getComponentId() . $attach['ID'];
	$canParticipate = $attach->canParticipate($USER->getId());
	$voted = $attach->isVotedFor($USER->getId());
	$canRevote = (($attach['OPTIONS'] & Option::ALLOW_REVOTE) && $attach->canRevote($USER->getId())->isSuccess());
	$canReadResult = $attach->canReadResult($USER->getId())->isSuccess();
	$lastVote = 0;
	$ballot = [];
	$extras = [];
	if ($data = \Bitrix\Vote\Event::getDataFromRequest($attach['ID'], $request))
	{
		$ballot = $data['BALLOT'];
		$extras = $data['EXTRAS'];
	}
?>
<div class="bx-vote-container bx-vote-container-<?=$style?>" <?php
	?>data-bx-vote-form="<?= ($canParticipate && $voted === false ? 'shown' : 'hidden') ?>" <?php
	?>data-bx-vote-result="<?= ($canReadResult ? 'shown' : 'hidden') ?>" <?php
	?>data-bx-vote-status="<?= ($voted === false ? 'ready' : 'voted') ?>" <?php
	?>data-bx-vote-option-reparticipate="<?= ($canRevote ? 'Y' : 'N') ?>" <?php
	?>data-bx-vote-lamp="<?=($attach["LAMP"])?>" <?php
	?>id="vote-<?= $uid ?>">
	<div class="feed-notice-block feed-add-error"><span class="feed-add-info-icon"></span><span class="feed-add-info-text" data-bx-vote-role="error"></span></div>
	<form action="" method="get" class="vote-form" name="vote-form-<?= $uid ?>">
		<input type="hidden" name="attachId" value="<?= $attach["ID"] ?>"/>
		<?= bitrix_sessid_post()
		?><input id="checkbox_<?=$uid?>" class="bx-vote-checkbox" type="checkbox" <?php if ($arParams["VIEW_MODE"] === "EXTENDED"): ?> checked="checked"<?php endif;?> /><?php
		?><div class="bx-vote-body">
			<div class="bx-vote-block">
				<ol class="bx-vote-question-list">
				<?php
				foreach ($attach['QUESTIONS'] as $question)
				{
					if ($question['ACTIVE'] === 'N')
					{
						continue;
					}
					$ballotAnswers = (array_key_exists($question["ID"], $ballot) && is_array($ballot[$question["ID"]]) ? $ballot[$question["ID"]] : []);
					$foundValue = false;
					?>
					<li id="question<?= $question["ID"] ?>"<?php if ($question["REQUIRED"] === "Y"): ?> class="bx-vote-question-required"<?php endif; ?>>
						<?php
						if (!empty($question["IMAGE"]) && !empty($question["IMAGE"]["SRC"]))
						{
							?>
							<div class="bx-vote-question-image"><img src="<?=$question["IMAGE"]["SRC"]?>"/></div><?php
						}
						?>
						<div class="bx-vote-question-title"><?= FormatText($question["QUESTION"], $question["QUESTION_TYPE"]) ?></div>
						<table class="bx-vote-answer-list" cellspacing="0">
						<?php
						foreach ($question['ANSWERS'] as $answer)
						{
							if ($canReadResult === false)
							{
								$answer['COUNTER'] = 0;
								$answer['PERCENT'] = 0;
							}
							$answer['MESSAGE'] = FormatText($answer['MESSAGE'], $answer['MESSAGE_TYPE']);
							?>
							<tr data-bx-vote-answer="<?=$answer["ID"]?>" class="bx-vote-answer-item">
								<td>
									<div class="bx-vote-bar"><?php
										$checked = array_key_exists($answer['ID'], $ballotAnswers) ? 'checked="checked"' : '';
										$message = array_key_exists($answer['ID'], $ballotAnswers) ? htmlspecialcharsbx($ballotAnswers[$answer['ID']]) : '';
										switch ($answer['FIELD_TYPE'])
										{
											case AnswerTypes::RADIO:
											case AnswerTypes::DROPDOWN:
												if ($foundValue)
												{
													$checked = '';
												}
												else
												{
													$foundValue = true;
												}
												?>
												<span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap">
													<label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>">
														<input type="radio" name="<?= $answer["FIELD_NAME"] ?>" id="vote_answer_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>" value="<?= $answer["ID"] ?>" <?= $checked ?> />
														<span class="bx-vote-block-inp-substitute"></span>
													</label>
													<label for="vote_answer_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label>
												</span><?php
												break;
											case AnswerTypes::CHECKBOX:
											case AnswerTypes::MULTISELECT:
												?>
												<span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap">
													<label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["ID"] ?>">
														<input type="checkbox" name="<?=$answer["FIELD_NAME"]?>[]" value="<?= $answer["ID"] ?>" id="vote_answer_<?= $answer["ID"] ?>" <?= $checked ?> />
														<span class="bx-vote-block-inp-substitute"></span>
													</label>
													<label for="vote_answer_<?= $answer["ID"] ?>"><?= $answer["MESSAGE"] ?></label>
												</span><?php
												break;
											case AnswerTypes::TEXT:
												if ((int)$question["FIELD_TYPE"] === QuestionTypes::COMPATIBILITY)
												{
													?><span class="bx-vote-block-input-wrap bx-vote-block-text-wrap">
														<input type="text" name="<?=$answer["FIELD_NAME"]?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>" value="<?= $message ?>" size="<?= $answer["FIELD_WIDTH"] ?>" <?= $answer["~FIELD_PARAM"] ?> />
													</span><?php
												}
												else if (
													(int)$question["FIELD_TYPE"] === AnswerTypes::RADIO
													|| (int)$question["FIELD_TYPE"] === AnswerTypes::DROPDOWN
												)
												{
													?><span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap">
														<label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>">
															<input type="radio" name="<?= $answer["FIELD_NAME"] ?>"  id="vote_answer_<?= $answer["ID"] ?>" value="<?= $answer["ID"] ?>" <?= $checked ?> />
															<span class="bx-vote-block-inp-substitute"></span>
														</label>
														<label for="vote_answer_<?= $answer["ID"] ?>">
															<span class="bx-vote-block-text-field-wrap"><?=$answer["MESSAGE"]?></span>
															<input type="text" name="<?=$answer["MESSAGE_FIELD_NAME"]?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>" value="<?= $message ?>" size="<?= $answer["FIELD_WIDTH"] ?>" onfocus="BX('vote_answer_<?= $answer["ID"] ?>').checked = true;" <?= $answer["~FIELD_PARAM"] ?> />
														</label>
													</span><?php
												}
												else
												{
													?><span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap">
														<label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["ID"] ?>">
															<input type="checkbox" name="<?=$answer["FIELD_NAME"]?>[]" value="<?= $answer["ID"] ?>" id="vote_answer_<?= $answer["ID"] ?>" <?= $checked ?> />
															<span class="bx-vote-block-inp-substitute"></span>
														</label>
														<label for="vote_answer_<?= $answer["ID"] ?>">
															<span class="bx-vote-block-text-field-wrap"><?=$answer["MESSAGE"]?></span>
															<input type="text" name="<?=$answer["MESSAGE_FIELD_NAME"]?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>" value="<?= $message ?>" size="<?= $answer["FIELD_WIDTH"] ?>" onfocus="BX('vote_answer_<?= $answer["ID"] ?>').checked = true;" <?= $answer["~FIELD_PARAM"] ?> />
														</label>
													</span><?php
												}
												break;
											case AnswerTypes::TEXTAREA:
												if ((int)$question["FIELD_TYPE"] === QuestionTypes::COMPATIBILITY)
												{
													?><span><?php
														?><textarea name="<?=$answer["FIELD_NAME"]?>" id="vote_memo_<?= $answer["ID"] ?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>" <?php
														?><?= $answer["~FIELD_PARAM"] ?> ><?= $message ?></textarea><?php
													?></span><?php
												}
												else if (
													(int)$question["FIELD_TYPE"] === AnswerTypes::RADIO
													|| (int)$question["FIELD_TYPE"] === AnswerTypes::DROPDOWN
												)
												{
													?><span class="bx-vote-block-input-wrap bx-vote-block-radio-wrap">
														<label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["QUESTION_ID"] ?>_<?= $answer["ID"] ?>">
															<input type="radio" name="<?= $answer["FIELD_NAME"] ?>"  id="vote_answer_<?= $answer["ID"] ?>" value="<?= $answer["ID"] ?>" <?= $checked ?> />
															<span class="bx-vote-block-inp-substitute"></span>
														</label>
														<label for="vote_answer_<?= $answer["ID"] ?>" class="bx-vote-block-input-wrap bx-vote-block-memo-wrap">
															<span class="bx-vote-block-text-field-wrap"><?=$answer["MESSAGE"]?></span>
															<textarea name="<?=$answer["MESSAGE_FIELD_NAME"]?>" id="vote_memo_<?= $answer["ID"] ?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>"
																	  <?php
																?>onfocus="BX('vote_answer_<?= $answer["ID"] ?>').checked = true;" <?=$answer["~FIELD_PARAM"] ?> ><?php
																?><?= $message ?></textarea>
														</label>
													</span><?php
												}
												else
												{
													?><span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap"><?php
														?><label class="bx-vote-block-input-wrap-inner" for="vote_answer_<?= $answer["ID"] ?>"><?php
															?><input type="checkbox" name="<?=$answer["FIELD_NAME"]?>[]" value="<?= $answer["ID"] ?>" <?php
																?> id="vote_answer_<?= $answer["ID"] ?>" <?= $checked ?> /><?php
															?><span class="bx-vote-block-inp-substitute"></span><?php
														?></label><?php
														?><label for="vote_answer_<?= $answer["ID"] ?>">
															<span class="bx-vote-block-text-field-wrap"><?=$answer["MESSAGE"]?></span><?php
															?><input type="text" name="<?=$answer["MESSAGE_FIELD_NAME"]?>" placeholder="<?=htmlspecialcharsbx($answer["MESSAGE"])?>" <?php
																?>value="<?= $message ?>" size="<?= $answer["FIELD_WIDTH"] ?>" <?php
																?>onfocus="BX('vote_answer_<?= $answer["ID"] ?>').checked = true;" <?= $answer["~FIELD_PARAM"] ?> /><?php
														?></label><?php
													?></span><?php
												}
												break;
										}
										?>
										<div class="bx-vote-result-bar" data-bx-vote-result="bar" style="width:<?= $answer["PERCENT"] ?>%;"></div>
									</div>
								</td>
								<td>
									<span class="bx-vote-voted-users-wrap"><?php
									if ((int)$attach['ANONYMITY'] === Anonymity::ANONYMOUSLY || !$USER->IsAuthorized())
									{
										?><span class="bx-vote-voted-users" data-bx-vote-result="counter"><?= $answer["COUNTER"] ?></span><?php
									}
									else
									{
										?><a href="javascript:void(0);" class="bx-vote-voted-users" data-bx-vote-result="counter"><?= $answer["COUNTER"] ?></a><?php
									}
									?></span>
								</td>
								<td><span class="bx-vote-data-percent" data-bx-vote-result="percent"><?= $answer["PERCENT"]?>%</span></td>
							</tr>
							<?php
					}
					?>
				</table>
			</li>
			<?php
		}
		?>
			<li class="bx-vote-answer-result">
				<div class="bx-vote-answer-list-wrap">
					<div data-bx-vote-result="counter"><?=$attach["COUNTER"]?></div>
					<div><?= Loc::getMessage('VOTE_RESULTS') ?></div>
				</div>
			</li><?php
		if ((int)$attach["ANONYMITY"] === Anonymity::UNDEFINED)
		{
			$checked = (array_key_exists("VISIBLE", $extras) && $extras["VISIBLE"] === "N" ? ' checked="checked" ' : "");
			?>
			<li class="bx-vote-params">
				<div class="bx-vote-params-wrap">
					<span class="bx-vote-block-input-wrap bx-vote-block-checbox-wrap">
						<label class="bx-vote-block-input-wrap-inner" for="vote_anonymity_<?= $attach["ID"] ?>">
							<input type="checkbox" name="<?=str_replace("#ENTITY_ID#", "VISIBLE", $attach["FIELD_NAME"])?>" value="N" id="vote_anonymity_<?= $attach["ID"] ?>" <?= $checked ?> />
							<span class="bx-vote-block-inp-substitute"></span>
						</label>
						<label for="vote_anonymity_<?= $attach["ID"] ?>"><?= Loc::getMessage('VOTE_HIDE_MY_VOTE') ?></label>
					</span>
				</div>
			</li>
			<?php
		}?>
		</ol>
	</div>
	<?php
	if (isset($arResult["CAPTCHA_CODE"]) && ($voted === false || $canRevote))
	{
	?><div class="bx-vote-captcha">
		<input type="hidden" name="captcha_code" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
		<span class="vote-captcha-image">
			<img src="/bitrix/tools/captcha.php?captcha_code=<?= $arResult["CAPTCHA_CODE"] ?>"/>
		</span>
		<span class="bx-vote-captcha-input">
			<label for="captcha_word"><?= Loc::getMessage('F_CAPTCHA_PROMT') ?></label>
			<input type="text" size="20" name="captcha_word" id="captcha_word" autocomplete="off" />
		</span>
	</div><?php
	}
		?><label for="checkbox_<?=$uid?>" class="bx-vote-switcher"><span class="bx-vote-switcher-arrow"></span></label><?php
	?></div><?php
	?><div class="bx-vote-buttons"><?php
		?><button class="ui-btn ui-btn-lg ui-btn-link bx-vote-button-resubmit" data-bx-vote-button="showVoteForm"><?= Loc::getMessage('VOTE_RESUBMIT_BUTTON') ?></button><?php
		?><button class="ui-btn ui-btn-lg ui-btn-primary" data-bx-vote-button="actVoting"><?= Loc::getMessage('VOTE_SUBMIT_BUTTON') ?></button><?php
		if ($attach->canEdit($USER->GetID()))
		{
			?><span data-bx-vote-button="stopOrResume"><?php
				?><button class="ui-btn ui-btn-lg ui-btn-link bx-vote-button-stop"><?= Loc::getMessage('VOTE_STOP_BUTTON') ?></button><?php
				?><button class="ui-btn ui-btn-lg ui-btn-link bx-vote-button-resume"><?= Loc::getMessage('VOTE_RESUME_BUTTON')?></button><?php
			?></span><?php
			?><button class="ui-btn ui-btn-lg ui-btn-link" data-bx-vote-button="exportXls"><?= Loc::getMessage('VOTE_EXPORT_BUTTON') ?></button><?php
		}
	?></div><?php
	$pathToUser = ($arParams["~PATH_TO_USER"] ?: SITE_DIR . '/company/personal/user/#ID#/');
	if ($arParams['PUBLIC'])
	{
		$pathToUser = '';
	}
	?>
	<script>
		BX.ready(function() {
			BX.message({
				VOTE_ERROR_DEFAULT : '<?= CUtil::JSEscape(Loc::getMessage('VOTE_ERROR_DEFAULT')) ?>'
			});
			new BX.Vote(BX('vote-<?= $uid ?>'), {
				id: <?=$attach["ID"]?>,
				voteId: <?=$attach["VOTE_ID"]?>,
				urlTemplate: '<?= CUtil::JSEscape($pathToUser) ?>',
				nameTemplate: '<?= CUtil::JSEscape($arParams["~NAME_TEMPLATE"]) ?>'
			});
			<?php
			if ($GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull"))
			{
				\CPullWatch::Add($GLOBALS["USER"]->GetID(), 'VOTE_'.$attach["VOTE_ID"]);
			}
			?>
		});
	</script>
	</form>
</div>
<?php
}
$frame->end();
