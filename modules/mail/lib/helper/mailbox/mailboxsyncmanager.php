<?php
namespace Bitrix\Mail\Helper\Mailbox;

use COption;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Mail\Internals\MailEntityOptionsTable;
use Bitrix\Mail\MailFilterTable;
use Bitrix\Main\Loader;

class MailboxSyncManager
{
	private $userId;
	private $mailCheckInterval;

	public function __construct($userId)
	{
		$this->userId = $userId;
		$this->mailCheckInterval = COption::getOptionString('intranet', 'mail_check_period', 10) * 60;
	}

	public static function checkSyncWithCrm(int $mailboxId): bool
	{
		if (Loader::includeModule('crm'))
		{
			return (bool)MailFilterTable::getCount([
				'=MAILBOX_ID' => $mailboxId,
				'=ACTION_TYPE' => 'crm_imap',
			]);
		}

		return false;
	}

	public function getFailedToSyncMailboxes()
	{
		$mailboxes = [];
		$mailboxesSyncInfo = $this->getMailboxesSyncInfo();

		foreach ($mailboxesSyncInfo as $mailboxId => $lastMailCheckData)
		{
			if (!$lastMailCheckData['isSuccess'])
			{
				$mailboxes[$mailboxId] = $lastMailCheckData;
			}
		}
		return $mailboxes;
	}

	public function getSuccessSyncedMailboxes()
	{
		$mailboxesToSync = [];
		$mailboxesSyncInfo = $this->getMailboxesSyncInfo();

		foreach ($mailboxesSyncInfo as $mailboxId => $lastMailCheckData)
		{
			if ($lastMailCheckData['isSuccess'])
			{
				$mailboxesToSync[$mailboxId] = $lastMailCheckData;
			}
		}
		return $mailboxesToSync;
	}

	/*
	 *	It's time for synchronization for at least one mailbox.
	 */
	public function isMailNeedsToBeSynced()
	{
		return count($this->getNeedToBeSyncedMailboxes()) > 0;
	}

	/*
	 *	Returns mailboxes that are recommended to be synchronized.
	 */
	public function getNeedToBeSyncedMailboxes()
	{
		$mailboxesSyncData = $this->getSuccessSyncedMailboxes();
		$mailboxesToSync = [];
		foreach ($mailboxesSyncData as $mailboxId => $lastMailCheckData)
		{
			if ($lastMailCheckData['timeStarted'] >= 0 && (time() - intval($lastMailCheckData['timeStarted']) >= $this->mailCheckInterval))
			{
				$mailboxesToSync[$mailboxId] = $lastMailCheckData;
			}
		}
		return $mailboxesToSync;
	}

	public function getMailCheckInterval()
	{
		return $this->mailCheckInterval;
	}

	public function deleteSyncData($mailboxId)
	{
		$filter = [
			'=MAILBOX_ID' => $mailboxId,
			'=ENTITY_TYPE' => 'MAILBOX',
			'=ENTITY_ID' => $mailboxId,
			'=PROPERTY_NAME' => 'SYNC_STATUS',
		];

		return MailEntityOptionsTable::deleteList($filter);
	}

	public function setDefaultSyncData($mailboxId)
	{
		$this->saveSyncStatus($mailboxId, true, 0);
	}

	private function buildTimeForSyncStatus($time): int
	{
		if($time !== null && (int)$time >= 0)
		{
			return (int)$time;
		}

		return time();
	}

	public function setSyncStartedData($mailboxId, $time = null)
	{
		$this->saveSyncStatus($mailboxId, true, $this->buildTimeForSyncStatus($time));
	}

	public function setSyncStatus($mailboxId, $isSuccess, $time = null)
	{
		$this->saveSyncStatus($mailboxId, $isSuccess, $this->buildTimeForSyncStatus($time));
	}

	private function saveSyncStatus($mailboxID, $status, $date)
	{
		$filter = [
			'=MAILBOX_ID' => $mailboxID,
			'=ENTITY_TYPE' => 'MAILBOX',
			'=ENTITY_ID' => $mailboxID,
			'=PROPERTY_NAME' => 'SYNC_STATUS',
		];

		$keyRow = [
			'MAILBOX_ID' => $mailboxID,
			'ENTITY_TYPE' => 'MAILBOX',
			'ENTITY_ID' => $mailboxID,
			'PROPERTY_NAME' => 'SYNC_STATUS',
		];

		$fields = $keyRow;

		$fields['VALUE'] = $status;
		$fields['DATE_INSERT'] = DateTime::createFromTimestamp($date);

		if(MailEntityOptionsTable::getCount($filter))
		{
			MailEntityOptionsTable::update(
				$keyRow,
				[
					'DATE_INSERT' => $fields['DATE_INSERT'],
					'VALUE' => $fields['VALUE'],
				],
			);
		}
		else
		{
			MailEntityOptionsTable::add(
				$fields
			);
		}
	}

	public function getMailboxSyncInfo($mailboxID)
	{
		$dateLastOpening = \Bitrix\Mail\Internals\MailEntityOptionsTable::getList(
			[
				'select' => [
					'VALUE',
					'DATE_INSERT',
				],
				'filter' => [
					'=MAILBOX_ID' => $mailboxID,
					'=ENTITY_TYPE' => 'MAILBOX',
					'=ENTITY_ID' => $mailboxID,
					'=PROPERTY_NAME' => 'SYNC_STATUS',
				],
				'limit' => 1,
			]
		)->fetch();

		if(isset($dateLastOpening['VALUE']))
		{
			return [
				'isSuccess' => (bool)$dateLastOpening['VALUE'],
				'timeStarted' => $dateLastOpening['DATE_INSERT']->getTimestamp(),
			];
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function getMailboxesSyncInfo()
	{
		$mailboxesSyncInfo = [];

		$userMailboxIds = array_keys(MailboxTable::getUserMailboxes());
		foreach ($userMailboxIds as $id)
		{
			$id = (int)$id;
			$mailboxSyncInfo = $this->getMailboxSyncInfo($id);
			if($mailboxSyncInfo !== false)
			{
				$mailboxesSyncInfo[$id] = $mailboxSyncInfo;
			}
		}
		return $mailboxesSyncInfo;
	}

	/**
	 * @deprecated Use \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager::getTimeBeforeNextSync()
	 */
	public function getNextTimeToSync($lastMailCheckData)
	{
		return intval($lastMailCheckData['timeStarted']) + $this->mailCheckInterval - time();
	}

	/*
	 * Returns the time remaining until the required recommended mail synchronization.
	 * If it's time to synchronize, it will return 0.
	 */
	public function getTimeBeforeNextSync()
	{
		$mailboxesSuccessSynced = $this->getSuccessSyncedMailboxes();
		$timeBeforeNextSyncMailboxes = [];

		foreach ($mailboxesSuccessSynced as $mailboxId => $lastMailCheckData)
		{
			$timeBeforeNextSyncMailboxes[] = intval($lastMailCheckData['timeStarted']) + $this->mailCheckInterval - time();
		}

		return !empty($timeBeforeNextSyncMailboxes) && min($timeBeforeNextSyncMailboxes) > 0 ? min($timeBeforeNextSyncMailboxes) : 0;
	}

	/**
	 * @return null|int
	 */
	public function getFirstFailedToSyncMailboxId()
	{
		$mailboxesIdsFailedToSync = array_keys($this->getFailedToSyncMailboxes());
		return !empty($mailboxesIdsFailedToSync) && count($mailboxesIdsFailedToSync) > 0
			? (int)$mailboxesIdsFailedToSync[0]
			: null;
	}

	public function getLastMailboxSyncIsSuccessStatus($mailboxId)
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		if (!(isset($mailboxesOptions[$mailboxId]) && array_key_exists('isSuccess', $mailboxesOptions[$mailboxId])))
		{
			return null;
		}
		return $mailboxesOptions[$mailboxId]['isSuccess'];
	}

	public function getLastMailboxSyncTime($mailboxId)
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		if (!(isset($mailboxesOptions[$mailboxId]) && array_key_exists('timeStarted', $mailboxesOptions[$mailboxId])))
		{
			return null;
		}
		return $mailboxesOptions[$mailboxId]['timeStarted'];
	}
}