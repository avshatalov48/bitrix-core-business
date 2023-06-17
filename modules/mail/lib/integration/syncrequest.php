<?php

namespace Bitrix\Mail\Integration;

use Bitrix\Mail\Helper\Mailbox\MailboxSyncManager;
use Bitrix\Main\Event;
use Bitrix\Mail\Helper;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Loader;
use Bitrix\Main;
use Exception;

class SyncRequest
{
	/**
	 * @throws LoaderException
	 */
	public static function onRequestSyncMail(Event $event): void
	{
		global $USER;
		if (Loader::includeModule('mail') && is_object($USER) && $USER->IsAuthorized())
		{
			$userId = $USER->GetID();
			$urgent = $event->getParameter('urgent');
			if ($urgent || (new MailboxSyncManager($userId))->isMailNeedsToBeSynced())
			{
				Main\Application::getInstance()->addBackgroundJob(function () {
					self::syncMail();
				}, []);
			}
		}
	}

	/**
	 * @throws LoaderException
	 * @throws Exception
	 */
	public static function syncMail(): array
	{
		global $USER;
		$result = [
			'lastFailedToSyncMailboxId' => 0,
			'hasSuccessSync' => false,
			'unseen' => 0,
		];

		if (Loader::includeModule('mail') && is_object($USER) && $USER->IsAuthorized())
		{
			$userId = $USER->GetID();
			$mailboxesSyncManager = new MailboxSyncManager($userId);
			$mailboxesReadyToSync = $mailboxesSyncManager->getNeedToBeSyncedMailboxes();

			if (!empty($mailboxesReadyToSync))
			{
				foreach ($mailboxesReadyToSync as $mailboxId => $lastMailCheckData)
				{
					$mailboxHelper = Helper\Mailbox::createInstance($mailboxId, false);
					if (!empty($mailboxHelper))
					{
						if ($mailboxHelper->sync() === false)
						{
							$result['lastFailedToSyncMailboxId'] = $mailboxId;
						}
						else
						{
							$result['hasSuccessSync'] = true;
						}
						if ($mailboxHelper->getMailbox()['SYNC_LOCK'] >= 0)
						{
							break;
						}
					}
				}
				$unseen = max(Helper\Message::getCountersForUserMailboxes($userId, true), 0);
				$result['unseen'] = $unseen;
				\CUserCounter::set($userId, 'mail_unseen', $unseen, SITE_ID);
			}
		}
		return $result;
	}

}