<?php
/**
 * @var array $arResult
 * @var array $arParams
 * @var \CBitrixComponent $component
 */
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(["ui.forms", "ui.design-tokens", "ui.fonts.opensans"]);

$controlName = $arParams["~INPUT_NAME"];
$controller = "BX('votes-".$arResult["CONTROL_UID"]."')";
$pr = $controlName."_DATA";
$arVote = reset($arResult["VOTES"]);
$uid = $this->randString(6);
?><input type="hidden" name="<?=$controlName?>" value="<?=$arVote["ID"]?>" /><?php
?><input type="hidden" name="<?=$controlName?>_DATA[ID]" value="<?=$arVote["ID"]?>" /><?php
?><input type="hidden" name="<?=$controlName?>_DATA[URL]" value="<?=$arVote["URL"]?>" /><?php
$m = array(
	"VVE_QUESTION" => GetMessage("VVE_QUESTION"),
	"VVE_QUESTION_DEL" => GetMessage("VVE_QUESTION_DEL"),
	"VVE_QUESTION_MULTIPLE" => GetMessage("VVE_QUESTION_MULTIPLE"),
	"VVE_QUESTION_SORT_DOWN" => GetMessage("VVE_QUESTION_SORT_DOWN"),
	"VVE_QUESTION_SORT_UP" => GetMessage("VVE_QUESTION_SORT_UP")
);
$sQuestion = <<<HTML
<li class="vote-question<!--Q_ID--> vote-question-saved<!--/Q_ID-->" data-bx-role="question" data-bx-number="#Q#" id="question_#Q#" >
	<div class="vote-block-title-wrap">
		<label for="question_#Q#" class="vote-block-sort-up" title="{$m["VVE_QUESTION_SORT_UP"]}" data-bx-action="sortUp"></label>
		<label for="question_#Q#" class="vote-block-sort-down" title="{$m["VVE_QUESTION_SORT_DOWN"]}" data-bx-action="sortDown"></label>
		<input type="text" name="{$pr}[QUESTIONS][#Q#][QUESTION]" placeholder="{$m['VVE_QUESTION']}" value="#Q_VALUE#" class="vote-block-title" data-bx-question-field="MESSAGE"  data-bx-action="adda" />
		<label for="question_#Q#" class="vote-block-close" data-bx-action="delq" title="{$m['VVE_QUESTION_DEL']}"></label>
		<input type="hidden" name="{$pr}[QUESTIONS][#Q#][C_SORT]" data-bx-question-field="C_SORT" value="#Q_C_SORT#" />
		<input type="hidden" name="{$pr}[QUESTIONS][#Q#][QUESTION_TYPE]" value="{$arParams["QUESTION_TYPE"]}" />
		<!--Q_ID--><input type="hidden" name="{$pr}[QUESTIONS][#Q#][ID]" id="question_#Q#_id" value="#Q_ID#" /><!--/Q_ID-->
	</div>
	<ol class="vote-answers" data-bx-role="answer-list">#ANSWERS#</ol>
	<div class="vote-checkbox-wrap">
		<input type="hidden" value="0" name="{$pr}[QUESTIONS][#Q#][FIELD_TYPE]" />
		<input type="checkbox" value="1" name="{$pr}[QUESTIONS][#Q#][FIELD_TYPE]" id="field_type_#Q#" #Q_MULTY# class="vote-checkbox" onclick="BX.onCustomEvent('onClickMulti', [this])" />
		<label class="vote-checkbox-label" for="field_type_#Q#">{$m['VVE_QUESTION_MULTIPLE']}</label>
	</div>
</li>
HTML;
$sQuestion = preg_replace(array("/\t+/", "/\n/"), array(""), $sQuestion);
$sQuestionEmpty = preg_replace(array("/\<\!\-\-Q\_ID\-\-\>(.+?)\<\!\-\-\/Q\_ID\-\-\>/"), array(""), $sQuestion);
$sQuestion = preg_replace(array("/\<\!\-\-Q\_ID\-\-\>/", "/\<\!\-\-\/Q\_ID\-\-\>/"), array(""), $sQuestion);

$m = array(
	"VVE_ANS" => GetMessage("VVE_ANS"),
	"VVE_ANS_DEL" => GetMessage("VVE_ANS_DEL"),
	"VVE_ANS_SORT" => GetMessage("VVE_ANS_SORT")
);
$sAnswer = <<<HTML
<li class="vote-block-inp-wrap<!--A_ID--> vote-block-inp-wrap-saved<!--/A_ID-->" id="answer_#Q#__#A#_" data-bx-role="answer" data-bx-number="#A#">
	<label class="vote-block-sort-dd" for="answer_#Q#__#A#_" title="{$m["VVE_ANS_SORT"]}" data-bx-action="sortDD"></label>
	<input class="vote-block-inp" type="text" placeholder="{$m["VVE_ANS"]} #A_PH#" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][MESSAGE]" value="#A_VALUE#" data-bx-answer-field="MESSAGE" data-bx-action="adda" />
	<label class="vote-block-close" for="answer_#Q#__#A#_" title="{$m["VVE_ANS_DEL"]}" data-bx-action="dela"></label>
	<input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][MESSAGE_TYPE]" value="{$arParams["MESSAGE_TYPE"]}" />
	<input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][C_SORT]" data-bx-answer-field="C_SORT" value="#A_C_SORT#" />
	<input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][FIELD_TYPE]" value="#A_FIELD_TYPE#" />
	<!--A_ID--><input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][ID]" id="answer_#Q#__#A#_id" value="#A_ID#" /><!--/A_ID-->
</li>
HTML;
$sAnswer = preg_replace(array("/\t+/", "/\n/"), array(""), $sAnswer);
$sAnswerEmpty = preg_replace(array("/\<\!\-\-A\_ID\-\-\>(.+?)\<\!\-\-\/A\_ID\-\-\>/"), array(""), $sAnswer);
$sAnswer = preg_replace(array("/\<\!\-\-A\_ID\-\-\>/", "/\<\!\-\-\/A\_ID\-\-\>/"), array(""), $sAnswer);
?>
<div class="feed-add-vote-wrap" id="votes-<?=$arResult["CONTROL_UID"]?>"><?php
	if (($arParams["SHOW_TITLE"] ?? null) == "Y"):?>
		<div class="vote-header"><input type="text" name="<?=$pr?>[TITLE]" value="<?=$arVote["TITLE"]?>" /></div><?php
	endif;?>
	<div class="vote-fields">
		<?php if ($arParams["SHOW_DATE"] ?? null == "Y"):?>
		<div class="vote-field"><label><?=GetMessage("VVE_DATE")?></label><?php
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:main.calendar",
				"",
				array(
					"SHOW_INPUT"=>"Y",
					"SHOW_TIME"=>"N",
					"INPUT_NAME"=> $pr."[DATE_END]",
					"INPUT_VALUE"=>$arVote["DATE_END"]
				),
				$component,
				array("HIDE_ICONS"=>true)
			);?>
		</div>
		<?php endif; ?>
		<ol class="vote-questions" data-bx-role="question-list">
			<?php if (empty($arVote["QUESTIONS"])):?>
				<?=str_replace(
					["#Q_C_SORT#", "#Q_VALUE#", "#Q_MULTY#", "#ANSWERS#", "#Q#"],
					[
						"10", "", "",
						str_replace(
							["#A#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#", "#A_C_SORT#"],
							[0, "", 1, "0", 10],
							$sAnswerEmpty).
						str_replace(
							["#A#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#", "#A_C_SORT#"],
							[1, "", 2, "0", 20],
							$sAnswerEmpty),
						0
					],
					$sQuestionEmpty);
			else:
				$qq = 0;
				foreach($arVote["QUESTIONS"] as $arQuestion):
					$arAnswers = [];
					$arQuestion["ANSWERS"] = (is_array($arQuestion["ANSWERS"]) ? array_values($arQuestion["ANSWERS"]) : []);
					$aa = 0;
					foreach ($arQuestion["ANSWERS"] as $arAnswer)
					{
						$arAnswers[] = str_replace(
							["#A#", "#A_ID#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#", "#A_C_SORT#"],
							[$aa, $arAnswer["ID"], $arAnswer["MESSAGE"], ($aa + 1), $arAnswer["FIELD_TYPE"], $arAnswer["C_SORT"]],
							$arAnswer["ID"] > 0 ? $sAnswer : $sAnswerEmpty);
						$aa++;
					}
					?><?=str_replace(
						["#Q_C_SORT#", "#Q_VALUE#", "#Q_ID#", "#Q_MULTY#", "#ANSWERS#", "#Q#"],
						[$arQuestion["C_SORT"], $arQuestion["QUESTION"], $arQuestion["ID"], ($arQuestion["MULTI"] == "Y" ? "checked" : ""), implode("", $arAnswers), $qq],
					$arQuestion["ID"] > 0 ? $sQuestion : $sQuestionEmpty
					);
					$qq++;
				endforeach;
			endif;?></ol>
		<a class="vote-new-question-link" data-bx-action="addq" href="javascript:void(0);"><?=GetMessage("VVE_QUESTION_ADD")?></a>
	</div>
	<input id="checkbox_<?=$uid?>_switcher" class="adm-designed-checkbox <?php /*For core_admin_interface.js*/?> vote-additional-block-checkbox" type="checkbox" style="" />
	<div class="vote-additional-block">
		<div class="vote-additional-block-inner">
			<div class="ui-form-block">
				<label for="<?=$controlName?>_1" class="ui-ctl-label-text"><?=\Bitrix\Vote\Vote\Anonymity::getTitle()?></label>
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select id="<?=$controlName?>_1" class="ui-ctl-element" name="<?=$pr?>[ANONYMITY]"><?php
						foreach(\Bitrix\Vote\Vote\Anonymity::getTitledList() as $key => $val)
						{
							?><option value="<?=htmlspecialcharsbx($key)?>"<?=($key == $arVote["ANONYMITY"] ? " selected" : "")?>><?=htmlspecialcharsbx($val)?></option><?php
						}
						?></select>
				</div>
				<label for="<?=$controlName?>_2" class="ui-ctl ui-ctl-checkbox">
					<input type="checkbox" id="<?=$controlName?>_2" class="ui-ctl-element" name="<?=$pr?>[OPTIONS][]" value="<?=\Bitrix\Vote\Vote\Option::ALLOW_REVOTE?>" <?=($arVote["OPTIONS"] & \Bitrix\Vote\Vote\Option::ALLOW_REVOTE ? "checked" : "")?>>
					<div class="ui-ctl-label-text"><?=GetMessage("VVE_ALLOW_TO_CHANGE_MIND")?></div>
				</label>
				<label for="<?=$controlName?>_3" class="ui-ctl ui-ctl-checkbox" style="display:none;">
					<input type="checkbox" id="<?=$controlName?>_3" class="ui-ctl-element" name="<?=$pr?>[OPTIONS][]" value="<?=\Bitrix\Vote\Vote\Option::HIDE_RESULT?>" <?=($arVote["OPTIONS"] & \Bitrix\Vote\Vote\Option::HIDE_RESULT ? "checked" : "")?>>
					<div class="ui-ctl-label-text"><?=GetMessage("VVE_ALLOW_TO_VIEW_RESULTS")?></div>
				</label>
			</div>
		</div>
		<label for="checkbox_<?=$uid?>_switcher" class="vote-additional-block-switcher vote-checkbox-label">
			<span class="vote-additional-block-switcher-arrow"></span>
		</label>
	</div>
</div>
<script type="text/javascript">
BX.message({
	VVE_ANS_DELETE:'<?=GetMessageJS("VVE_ANS_DELETE")?>',
	VVE_QUESTION_DELETE:'<?=GetMessageJS("VVE_QUESTION_DELETE")?>',
	VOTE_TEMPLATE_QUESTION : '<?=CUtil::JSEscape($sQuestionEmpty)?>',
	VOTE_TEMPLATE_ANSWER : '<?=CUtil::JSEscape($sAnswerEmpty)?>'
});
window.__vote<?=$arResult["CONTROL_UID"]?> = function() {
	if (!!<?=$controller?> && !<?=$controller?>.loaded) {
		<?=$controller?>.loaded = true;
		BVoteC<?=$uid?> = new BVoteConstructor({
			'CID' : "<?=$arResult['CONTROL_UID']?>",
			'multiple' : <?=( $arParams['MULTIPLE'] == 'N' ? 'false' : 'true' )?>,
			'controller':  <?=$controller?>,
			'maxQ' : 0, 'maxA' : 0,
			'msg' : {}}
		);
	}
};
window.__vote<?=$arResult["CONTROL_UID"]?>();
</script>