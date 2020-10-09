<?
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
	array_key_exists("PUBLIC_VOTE_ID", $_REQUEST) && intval($_REQUEST["PUBLIC_VOTE_ID"]) > 0 && 
	array_key_exists("vote", $_REQUEST) && $_REQUEST["vote"] <> ''
	)
{
	if (CModule::IncludeModule("vote"))
		CVote::keepVoting();
}
?>