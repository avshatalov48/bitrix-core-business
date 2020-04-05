<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
$controlName = $arParams["~INPUT_NAME"];
$controller = "BX('votes-".$arResult["CONTROL_UID"]."')";

$pr = $controlName."_DATA";
$arVote = reset($arResult["VOTES"]);
$arVote["DATE_END"] = (!$arVote["ID"] && !$arParams["bVarsFromForm"] ? GetTime((time() + 30*86400)) : $arVote["DATE_END"]);

$uid = $this->randString(6);

?><input type="hidden" name="<?=$controlName?>" value="<?=$arVote["ID"]?>" /><?
?><input type="hidden" name="<?=$controlName?>_DATA[ID]" value="<?=$arVote["ID"]?>" /><?
?><input type="hidden" name="<?=$controlName?>_DATA[URL]" value="<?=$arVote["URL"]?>" /><?

$m = array(
	"VVE_QUESTION" => GetMessage("VVE_QUESTION"),
	"VVE_QUESTION_DEL" => GetMessage("VVE_QUESTION_DEL"),
	"VVE_QUESTION_MULTIPLE" => GetMessage("VVE_QUESTION_MULTIPLE")
);
$sQuestion = <<<HTML
<li class="vote-question">
	<div class="vote-block-title-wrap">
		<input type="text" name="{$pr}[QUESTIONS][#Q#][QUESTION]"  placeholder="{$m['VVE_QUESTION']}" id="question_#Q#" value="#Q_VALUE#" class="vote-block-title adda" />
		<label for="question_#Q#" class="vote-block-close delq" title="{$m['VVE_QUESTION_DEL']}"></label>
		<input type="hidden" name="{$pr}[QUESTIONS][#Q#][QUESTION_TYPE]" value="{$arParams["QUESTION_TYPE"]}" />
		<!--Q_ID--><input type="hidden" name="{$pr}[QUESTIONS][#Q#][ID]" id="question_#Q#" value="#Q_ID#" /><!--/Q_ID-->
	</div>
	<ol class="vote-answers">#ANSWERS#</ol>
	<div class="vote-checkbox-wrap">
		<input type="checkbox" value="Y" name="{$pr}[QUESTIONS][#Q#][MULTI]" id="multi_#Q#" #Q_MULTY# class="vote-checkbox" onclick="BX.onCustomEvent('onClickMulti', [this])" />
		<label class="vote-checkbox-label" for="multi_#Q#">{$m['VVE_QUESTION_MULTIPLE']}</label>
	</div>
</li>
HTML;
$sQuestion = preg_replace(array("/\t+/", "/\n/"), array(""), $sQuestion);
$sQuestionEmpty = preg_replace(array("/\<\!\-\-Q\_ID\-\-\>(.+?)\<\!\-\-\/Q\_ID\-\-\>/"), array(""), $sQuestion);
$m = array(
	"VVE_ANS" => GetMessage("VVE_ANS"),
	"VVE_ANS_DEL" => GetMessage("VVE_ANS_DEL")
);
$sAnswer = <<<HTML
<li class="vote-block-inp-wrap">
	<input class="vote-block-inp adda" type="text" placeholder="{$m["VVE_ANS"]} #A_PH#" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][MESSAGE]" id="answer_#Q#__#A#_" value="#A_VALUE#" />
	<label class="vote-block-close dela" for="answer_#Q#__#A#_" title="{$m["VVE_ANS_DEL"]}"></label>
	<input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][MESSAGE_TYPE]" value="{$arParams["MESSAGE_TYPE"]}" />
	<input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][FIELD_TYPE]" data-bx-answer-field="field-type" value="#A_FIELD_TYPE#" />
	<!--A_ID--><input type="hidden" name="{$pr}[QUESTIONS][#Q#][ANSWERS][#A#][ID]" id="answer_#Q#__#A#_" value="#A_ID#" /><!--/A_ID-->
</li>
HTML;
$sAnswer = preg_replace(array("/\t+/", "/\n/"), array(""), $sAnswer);
$sAnswerEmpty = preg_replace(array("/\<\!\-\-A\_ID\-\-\>(.+?)\<\!\-\-\/A\_ID\-\-\>/"), array(""), $sAnswer);
?>
<div class="feed-add-vote-wrap" id="votes-<?=$arResult["CONTROL_UID"]?>"><?
	if ($arParams["SHOW_TITLE"] == "Y"):?>
		<div class="vote-header"><input type="text" name="<?=$pr?>[TITLE]" value="<?=$arVote["TITLE"]?>" /></div><?
	endif;?>
	<div class="vote-fields">
		<?if ($arParams["SHOW_DATE"] == "Y"):?>
		<div class="vote-field"><label><?=GetMessage("VVE_DATE")?></label><?
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
		<?endif; ?>
		<ol class="vote-questions"><?
	if (empty($arVote["QUESTIONS"]))
	{
		?><?=str_replace(
			array("#Q_VALUE#", "#Q_MULTY#", "#ANSWERS#", "#Q#"),
			array("", "",
				str_replace(
					array("#A#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#"),
					array(0, "", 1, "0"),
					$sAnswerEmpty).
				str_replace(
					array("#A#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#"),
					array(1, "", 2, "0"),
					$sAnswerEmpty),
				0),
			$sQuestionEmpty);?><?
	}
	else
	{
		foreach($arVote["QUESTIONS"] as $qq => $arQuestion)
		{
			$arAnswers = array();
			$arQuestion["ANSWERS"] = (is_array($arQuestion["ANSWERS"]) ? array_values($arQuestion["ANSWERS"]) : array());
			foreach ($arQuestion["ANSWERS"] as $aa => $arAnswer)
			{
				$arAnswers[] = str_replace(
					array("#A#", "#A_ID#", "#A_VALUE#", "#A_PH#", "#A_FIELD_TYPE#"),
					array($aa, $arAnswer["ID"], $arAnswer["MESSAGE"], ($aa + 1), $arAnswer["FIELD_TYPE"]),
					$sAnswer);
			}
			?><?=str_replace(
				array("#Q_VALUE#", "#Q_ID#", "#Q_MULTY#", "#ANSWERS#", "#Q#"),
				array($arQuestion["QUESTION"], $arQuestion["ID"], ($arQuestion["MULTI"] == "Y" ? "checked" : ""), implode("", $arAnswers), $qq),
				$sQuestion
			);?><?
		}
	}
		?></ol>
		<a class="vote-new-question-link addq" href="javascript:void(0);"><?=GetMessage("VVE_QUESTION_ADD")?></a>
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