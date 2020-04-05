<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

define('NOT_CHECK_PERMISSIONS', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

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

$error = false;
$mailboxesSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($userId);
$mailboxesReadyToSync = $mailboxesSyncManager->getNeedToBeSyncedMailboxes();
$isSuccessSync = false;
$failedToSyncMailboxId = 0;
if (!empty($mailboxesReadyToSync))
{
	$hasSuccessSync = false;
	foreach ($mailboxesReadyToSync as $mailboxId => $lastMailCheckData)
	{
		$mailboxHelper = \Bitrix\Mail\Helper\Mailbox::createInstance($mailboxId, false);
		if (!empty($mailboxHelper))
		{
			$result = $mailboxHelper->sync();
			if ($result === false)
			{
				$failedToSyncMailboxId = $mailboxId;
			}
			else
			{
				$hasSuccessSync = true;
			}
			if ($mailboxHelper->getMailbox()['SYNC_LOCK'] >= 0)
			{
				break;
			}
		}
	}
}

$unseen = max(\Bitrix\Mail\Helper\Message::getTotalUnseenCount($userId), 0);
\CUserCounter::set($userId, 'mail_unseen', $unseen, $siteId);

header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
echo json_encode([
	'result' => $error === false ? 'ok' : 'error',
	'unseen' => $unseen,
	'hasSuccessSync' => $hasSuccessSync,
	'failedToSyncMailboxId' => $failedToSyncMailboxId,
]);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
