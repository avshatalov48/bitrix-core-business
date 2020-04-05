<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("Опрос");
$APPLICATION->AddChainItem("Архив опросов", "vote_list.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?>
<?
$VOTE_ID = $_REQUEST["VOTE_ID"]; // берет ID опроса из параметров страницы

// Примеры использования основных функций модуля опросов
/*
if (CModule::IncludeModule("vote"))
{
	$bIsUserVoted = IsUserVoted($VOTE_ID)	// проверяет голосовал ли уже данный посетитель (возвращает true либо false)
	$VOTE_ID = GetCurrentVote("ANKETA");	// возвращает ID текущего опроса группы ANKETA
	$VOTE_ID = GetPrevVote("ANKETA");		// возвращает ID предыдущего опроса группы ANKETA
	$VOTE_ID = GetAnyAccessibleVote();		// возвращает ID любого доступного для голосования опроса
}
*/
?>

<?$APPLICATION->IncludeComponent("bitrix:voting.form", ".default", Array(
	"VOTE_ID"	=>	$_REQUEST["VOTE_ID"],
	"VOTE_RESULT_TEMPLATE"	=>	"vote_result.php?VOTE_ID=#VOTE_ID#"
	)
);?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>
