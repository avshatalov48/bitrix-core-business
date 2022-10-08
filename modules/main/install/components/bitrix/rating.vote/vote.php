<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("PUBLIC_AJAX_MODE", true); 

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$entityTypeId 	= $_REQUEST['ENTITY_TYPE_ID'];
$entityId 		= intval($_REQUEST['ENTITY_ID']);
$voteAction 	= $_REQUEST['VOTE_ACTION'] == 'plus' ? 'plus' : 'minus';
$userId 		= $USER->GetId();

$arRatingResult = CRatings::GetRatingVoteResult($entityTypeId, $entityId, $USER->GetId());
if (empty($arRatingResult)) 
{
	$arRatingResult['USER_HAS_VOTED'] = $USER->IsAuthorized() ? "N" : "Y";
	$arRatingResult['TOTAL_VALUE'] = 0;
	$arRatingResult['TOTAL_VOTES'] = 0;
	$arRatingResult['TOTAL_POSITIVE_VOTES'] = 0;
	$arRatingResult['TOTAL_NEGATIVE_VOTES'] = 0;
}

$path = str_replace(array("\\", "//"), "/", __DIR__."/lang/".LANGUAGE_ID."/vote.php");
include_once($path);
$resultValue  = $arRatingResult['TOTAL_VALUE'];
$resultStatus = $resultValue < 0 ? 'minus' : 'plus';
$resultTitle  = sprintf($MESS["RATING_COMPONENT_DESC"], $arRatingResult['TOTAL_VOTES'], $arRatingResult['TOTAL_POSITIVE_VOTES'], $arRatingResult['TOTAL_NEGATIVE_VOTES']);

echo '{"result" : "true", "resultValue" : "'.$resultValue.'", "resultStatus" : "'.$resultStatus.'", "resultTitle" : "'.$resultTitle.'"}';

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>