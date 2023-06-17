<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

define('NOT_CHECK_PERMISSIONS', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

global $USER;
if (!is_object($USER) || !$USER->IsAuthorized())
{
	return;
}

$userId = $USER->GetID();

session_write_close();

$siteId = SITE_ID;
if (isset($_REQUEST['SITE_ID']))
{
	$site = \CSite::getById($_REQUEST['SITE_ID'])->fetch();

	if (empty($site))
	{
		return;
	}

	$siteId = $site['LID'];
}

\Bitrix\Main\Loader::includeModule('mail');

$result = \Bitrix\Mail\Integration\SyncRequest::syncMail();

header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
echo json_encode([
	'unseen' => $result['unseen'],
	'hasSuccessSync' => $result['hasSuccessSync'],
	'failedToSyncMailboxId' => $result['lastFailedToSyncMailboxId'],
]);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
