<?
function SetNoKeepStatistics()
{
	@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php");
	if ($LICENSE_KEY <> '')
	{
		if ($_SESSION["SESS_NO_KEEP_STATISTIC"] == '')
		{
			$_SESSION["SESS_NO_KEEP_STATISTIC"] = $_REQUEST["no_keep_statistic_".$LICENSE_KEY];
			if ($_SESSION["SESS_NO_AGENT_STATISTIC"] == '')
			{
				$_SESSION["SESS_NO_AGENT_STATISTIC"] = $_SESSION["SESS_NO_KEEP_STATISTIC"];
			}
		}
		if ($_SESSION["SESS_NO_AGENT_STATISTIC"] == '')
		{
			$_SESSION["SESS_NO_AGENT_STATISTIC"] = $_REQUEST["no_agent_statistic_".$LICENSE_KEY];
		}
	}
}
?>