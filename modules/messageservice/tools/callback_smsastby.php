<?
define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("messageservice"))
{
	die();
}

$smsStatuses = array();
foreach ($_POST as $smsId => $status)
{
	$statusCode = \Bitrix\MessageService\Sender\Sms\SmsAssistentBy::resolveStatus($status);

	if ($statusCode !== null)
	{
		$smsStatuses[$smsId] = $statusCode;
	}
}

if ($smsStatuses)
{
	$connection = \Bitrix\Main\Application::getConnection();
	$sqlHelper = $connection->getSqlHelper();

	$tableName = \Bitrix\MessageService\Internal\Entity\MessageTable::getTableName();

	foreach ($smsStatuses as $smsId => $status)
	{
		$connection->queryExecute(
			'UPDATE '.$tableName.' SET STATUS_ID = '.(int)$status
			.' WHERE SENDER_ID = \'smsastby\' AND EXTERNAL_ID = \''.$sqlHelper->forSql($smsId).'\''
		);
	}

	//send pull message
	if(\Bitrix\MessageService\Integration\Pull::canUse())
	{
		$query = new \Bitrix\Main\Entity\Query(\Bitrix\MessageService\Internal\Entity\MessageTable::getEntity());
		$query->setSelect(array('ID', 'STATUS_ID'));
		$query->addFilter('=SENDER_ID', 'smsastby');
		$query->addFilter('@EXTERNAL_ID', array_keys($smsStatuses));

		\Bitrix\MessageService\Integration\Pull::onMessagesUpdate($query->exec()->fetchAll());
	}
}
CMain::FinalActions();
die();