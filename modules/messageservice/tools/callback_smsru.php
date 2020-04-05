<?
define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!isset($_POST['data']) || !is_array($_POST['data']) || !CModule::IncludeModule("messageservice"))
	die();

$smsStatuses = array();
foreach ($_POST["data"] as $entry)
{
	$lines = explode("\n",$entry);
	if (sizeof($lines) < 3 || $lines[0] !== 'sms_status')
	{
		continue;
	}

	$smsId = $lines[1];
	$statusCode = \Bitrix\MessageService\Sender\Sms\SmsRu::resolveStatus($lines[2]);

	if ($statusCode === null || !preg_match('|[0-9]{6}\-[0-9]+|', $smsId))
	{
		continue;
	}

	$smsStatuses[$smsId] = $statusCode;
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
			.' WHERE SENDER_ID = \'smsru\' AND EXTERNAL_ID = \''.$sqlHelper->forSql($smsId).'\''
		);
	}

	//send pull message
	if(\Bitrix\MessageService\Integration\Pull::canUse())
	{
		$query = new \Bitrix\Main\Entity\Query(\Bitrix\MessageService\Internal\Entity\MessageTable::getEntity());
		$query->setSelect(array('ID', 'STATUS_ID'));
		$query->addFilter('=SENDER_ID', 'smsru');
		$query->addFilter('@EXTERNAL_ID', array_keys($smsStatuses));

		\Bitrix\MessageService\Integration\Pull::onMessagesUpdate($query->exec()->fetchAll());
	}
}
echo '100'; // SMS.RU required success answer code
CMain::FinalActions();
die();