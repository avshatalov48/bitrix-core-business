<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();
if(!CModule::IncludeModule('vote'))
	return;
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", '/'.basename(__FILE__)));	
	
	
if (!is_object($DB))
	global $DB;
global $CACHE_MANAGER;	
$CACHE_MANAGER->CleanDir("b_vote_channel");
$CACHE_MANAGER->Clean("b_vote_channel_2_site");

$symbolycName = 'ANKETA_' . WIZARD_SITE_ID;

$arFieldsVC = array(
	"TIMESTAMP_X"		=> $DB->GetNowFunction(),
	"C_SORT"			=> "'100'",
	"FIRST_SITE_ID"		=> "'".WIZARD_SITE_ID ."'",
	"ACTIVE"			=> "'Y'",
	"VOTE_SINGLE"		=> "'Y'",
	"TITLE"				=> "'".$DB->ForSql(GetMessage('VOTING_INSTALL_CHANNEL_ANKETA'))."'",
	"SYMBOLIC_NAME"		=> "'".$symbolycName."'");
	
$rsVoteChan = CVoteChannel::GetList($by, $order, array('SYMBOLIC_NAME' => $symbolycName, 'SYMBOLIC_NAME_EXACT_MATCH' => 'Y'), $is_filtered);
if (!$rsVoteChan->Fetch())
{
	$ID = $DB->Insert("b_vote_channel", $arFieldsVC);
	if ($ID > 0)
	{
		$CACHE_MANAGER->CleanDir("b_vote_perm_".$ID);
		
		//site
		$DB->Query("DELETE FROM b_vote_channel_2_site WHERE CHANNEL_ID='".$ID."'", false);
		$DB->Query("INSERT INTO b_vote_channel_2_site (CHANNEL_ID, SITE_ID) VALUES ($ID, '".WIZARD_SITE_ID ."')", false);
		
		//groups
		$DB->Query("DELETE FROM b_vote_channel_2_group WHERE CHANNEL_ID='$ID'", false);
		$rsGroups = CGroup::GetList($by, $order, array());
		while ($arGroup = $rsGroups->Fetch())
		{
			$arFieldsPerm = array(
				"CHANNEL_ID"	=> "'".intval($ID)."'",
				"GROUP_ID"		=> "'".intval($arGroup["ID"])."'",
				"PERMISSION"	=> "'2'"
			);
			$DB->Insert("b_vote_channel_2_group", $arFieldsPerm);
		}
		
		$arFieldsVote = array(
			"CHANNEL_ID"		=> "'".$ID."'",
			"C_SORT"			=> "'100'",
			"ACTIVE"			=> "'Y'",
			"TIMESTAMP_X"		=> $DB->GetNowFunction(),
			"DATE_START"		=> $DB->CharToDateFunction(GetTime(mktime(0,0,0,1,1,2000),"FULL")),
			"DATE_END"			=> $DB->CharToDateFunction(GetTime(mktime(23,59,59,12,31,2030),"FULL")),
			"TITLE"				=> "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANKETA_TITLE'))."'",
			"DESCRIPTION"		=> "NULL",
			"DESCRIPTION_TYPE"	=> "'html'",
			"EVENT1"			=> "'vote'",
			"EVENT2"			=> "'anketa'",
			"EVENT3"			=> "NULL",
			"UNIQUE_TYPE"		=> "'1'",
			"KEEP_IP_SEC"		=> "'0'",
			"DELAY"				=> "'0'",
			"DELAY_TYPE"		=> "NULL",
			"TEMPLATE"			=> "'default.php'",
			"RESULT_TEMPLATE"	=> "'default.php'",
			"NOTIFY"			=> "'N'"
			);

		$VOTE_ID = $DB->Insert("b_vote", $arFieldsVote);
		
		$arFieldsQuest = array(
			"TIMESTAMP_X"		=> $DB->GetNowFunction(),
			"C_SORT"			=> "'100'",
			"ACTIVE"			=> "'Y'",
			'QUESTION_TYPE'		=> "'text'",
			'DIAGRAM'			=> "'Y'",
			'DIAGRAM_TYPE'		=> "'histogram'",
			'VOTE_ID'			=> "'$VOTE_ID'",
			'QUESTION'			=> "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_QUESTION1'))."'",
			'COUNTER'			=> "'0'",
		);
		
		$Q_ID = $DB->Insert("b_vote_question", $arFieldsQuest);
		
		$arAnswers = array(
			array(
				'C_SORT' => "'100'",
				'MESSAGE' => "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANSWER1_1'))."'",
				'FIELD_TYPE' => "'0'",
				'COLOR' => "'".$DB->ForSql('#66FF00')."'",
				'QUESTION_ID' => "'$Q_ID'",
				"TIMESTAMP_X" => $DB->GetNowFunction(),
				"ACTIVE" => "'Y'",
				'FIELD_WIDTH' => "'0'",
				'FIELD_HEIGHT' => "'0'",
			),
			array(
				'C_SORT' => "'200'",
				'MESSAGE' => "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANSWER1_2'))."'",
				'FIELD_TYPE' => "'0'",
				'COLOR' => "'".$DB->ForSql('#3333FF')."'",
				'QUESTION_ID' => "'$Q_ID'",
				"TIMESTAMP_X" => $DB->GetNowFunction(),
				"ACTIVE" => "'Y'",
				'FIELD_WIDTH' => "'0'",
				'FIELD_HEIGHT' => "'0'",
			),
			array(
				'C_SORT' => "'300'",
				'MESSAGE' => "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANSWER1_3'))."'",
				'FIELD_TYPE' => "'0'",
				'COLOR' => "'".$DB->ForSql('#FF3300')."'",
				'QUESTION_ID' => "'$Q_ID'",
				"TIMESTAMP_X" => $DB->GetNowFunction(),
				"ACTIVE" => "'Y'",
				'FIELD_WIDTH' => "'0'",
				'FIELD_HEIGHT' => "'0'",
			),
			array(
				'C_SORT' => "'400'",
				'MESSAGE' => "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANSWER1_4'))."'",
				'FIELD_TYPE' => "'0'",
				'COLOR' => "'".$DB->ForSql('#FFFF00')."'",
				'QUESTION_ID' => "'$Q_ID'",
				"TIMESTAMP_X" => $DB->GetNowFunction(),
				"ACTIVE" => "'Y'",
				'FIELD_WIDTH' => "'0'",
				'FIELD_HEIGHT' => "'0'",
			),
			array(
				'C_SORT' => "'500'",
				'MESSAGE' => "'".$DB->ForSql(GetMessage('VOTING_INSTALL_VOTE_ANSWER1_5'))."'",
				'FIELD_TYPE' => "'0'",
				'COLOR' => "'".$DB->ForSql('#339966')."'",
				'QUESTION_ID' => "'$Q_ID'",
				"TIMESTAMP_X" => $DB->GetNowFunction(),
				"ACTIVE" => "'Y'",
				'FIELD_WIDTH' => "'0'",
				'FIELD_HEIGHT' => "'0'",
			),
		);
		
		foreach ($arAnswers as $answ)
		{
			$DB->Insert("b_vote_answer", $answ);
		}
		
	}
}

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_rbottom.php", array("SYMBOLIC_NAME" => $symbolycName));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/vote/index.php", array("SYMBOLIC_NAME" => $symbolycName));

?>
