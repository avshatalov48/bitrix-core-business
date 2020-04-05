<?
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
	IncludeModuleLangFile(__FILE__);

	if(!check_bitrix_sessid( "b_sessid" ))
	{
		die();
	}
	
	if(isset($_REQUEST["action"]) &&  $_REQUEST["action"] === 'reindex'  && isset($_REQUEST["data"]) && is_array($_REQUEST["data"]))
	{
		$interval = intval($_REQUEST["data"]["interval"]);
		$firstID = $_REQUEST["data"]["firstID"];

		$lastID = CSupportSearch::reindexAllTickets($firstID, $interval ?: 10);

		// build progress bar
		$maxID = CTicket::getMaxId();

		$progressBar = new CAdminMessage(array(
			"DETAILS" => str_replace(
				array('#LAST_ID#', '#MAX_ID#'),
				array($lastID, $maxID),
				GetMessage('SUP_SEARCH_NDX_PROGRESS_BAR')
			),
			"HTML" => true,
			"TYPE" => "PROGRESS",
			"PROGRESS_TOTAL" => $maxID,
			"PROGRESS_VALUE" => $lastID,
		));

		$progressBarHtml = $progressBar->Show();

		echo CUtil::PhpToJSObject(array(
			'LAST_ID' => $lastID,
			'BAR' => $progressBarHtml
		));

	}
	elseif( $_REQUEST['MY_AJAX'] == 'restartAgentsAJAX' )
	{
		CTicketReminder::StartAgent();
		echo json_encode( array( "ALL_OK" => "OK" ) );
	}
	else
	{
		echo '{};';
	}
	
?>