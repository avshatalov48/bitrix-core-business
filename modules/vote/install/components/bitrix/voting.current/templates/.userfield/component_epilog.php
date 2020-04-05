<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull"))
{
	CPullWatch::Add($GLOBALS["USER"]->GetID(), 'VOTE_'.$arResult["VOTE_ID"]);
	?><script>BX.ready(function(){BX.PULL.extendWatch('VOTE_<?=$arResult["VOTE_ID"]?>');});</script><?
}
if ($_SERVER["REQUEST_METHOD"] == "POST" &&
	array_key_exists("VOTING.RESULT", $arResult) &&
	array_key_exists("arResult", $arResult["VOTING.RESULT"]) &&
	($questions = $arResult["VOTING.RESULT"]["arResult"]["QUESTIONS"]) &&
	!empty($questions) &&
	array_key_exists("PUBLIC_VOTE_ID", $_REQUEST) && $_REQUEST["PUBLIC_VOTE_ID"] == $arResult["VOTE_ID"] &&
	array_key_exists("vote", $_REQUEST) && strlen($_REQUEST["vote"])>0 &&
	($GLOBALS["VOTING_ID"] == $arResult["VOTE_ID"] && array_key_exists($arResult["VOTE_ID"], $_SESSION["VOTE"]["VOTES"])) &&
	CModule::IncludeModule("pull"))
{
	$result = array();
	foreach ($questions as $question)
	{
		$result[$question["ID"]] = array();
		foreach ($question["ANSWERS"] as $arAnswer)
		{
			$result[$question["ID"]][$arAnswer["ID"]] = array(
				'PERCENT' => $arAnswer["PERCENT"],
				'USERS' => $arAnswer["USERS"],
				'COUNTER' => $arAnswer["COUNTER"]
			);
		}
	}
	if (!empty($result))
	{
		CPullWatch::AddToStack('VOTE_'.$arResult["VOTE_ID"],
			Array(
				'module_id' => 'vote',
				'command' => 'voting',
				'params' => Array(
					"VOTE_ID" => $arResult["VOTE_ID"],
					"AUTHOR_ID" => $GLOBALS["USER"]->GetId(),
					"QUESTIONS" => $result
				)
			)
		);
	}
}

CJSCore::Init(array('ajax', 'popup'));
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');

$uid = $this->params["uid"];
$lastVote = intval($this->params["lastVote"]);
?>
<script type="text/javascript">
BX.ready(function(){
	BX.Vote.init({
		id : <?=$arResult["VOTE_ID"]?>,
		cid : '<?=$uid?>',
		urlTemplate : '<?=CUtil::JSEscape($arParams["~PATH_TO_USER"]);?>',
		nameTemplate : '<?=CUtil::JSEscape($arParams["~NAME_TEMPLATE"]);?>',
		url : '<?=CUtil::JSEscape(htmlspecialcharsback(POST_FORM_ACTION_URI))?>',
		startCheck : <?=$lastVote?>
	});
});
</script>
<?if ($_REQUEST["VOTE_ID"] == $arResult["VOTE_ID"] && $_REQUEST["AJAX_POST"] == "Y" && check_bitrix_sessid()):
	$res = ob_get_clean();
	$APPLICATION->RestartBuffer();
	echo $res;
	die();
endif;
?>
</div>