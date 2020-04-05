<?php
namespace Bitrix\Mail\Helper\Mailbox;

use COption;
use CUserOptions;

class MailboxSyncManager
{
	private $userId;
	private $mailCheckInterval;
	private $syncOptionCategory = 'global';
	private $syncOptionName = 'user_mailboxes_sync_info';

	public function __construct($userId)
	{
		$this->userId = $userId;
		$this->mailCheckInterval = COption::getOptionString('intranet', 'mail_check_period', 10) * 60;
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
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		if (empty($mailboxesOptions))
		{
			return;
		}
		unset($mailboxesOptions[$mailboxId]);
		if (empty($mailboxesOptions))
		{
			CUserOptions::deleteOption($this->syncOptionCategory, $this->syncOptionName, false, $this->userId);
		}
		else
		{
			$this->setOption($mailboxesOptions);
		}
	}

	public function setDefaultSyncData($mailboxId)
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		$mailboxesOptions[$mailboxId] = ['isSuccess' => true, 'timeStarted' => 0];
		$this->setOption($mailboxesOptions);
	}

	public function setSyncStartedData($mailboxId, $time = null)
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		$mailboxesOptions[$mailboxId] = ['isSuccess' => true, 'timeStarted' => $time !== null && (int)$time >= 0 ? (int)$time : time()];
		$this->setOption($mailboxesOptions);
	}

	public function setSyncStatus($mailboxId, $isSuccess, $time = null)
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		$mailboxesOptions[$mailboxId] = ['isSuccess' => $isSuccess, 'timeStarted' => $time !== null && (int)$time >= 0 ? (int)$time : time()];
		$this->setOption($mailboxesOptions);
	}

	private function setOption($mailboxesSyncInfo)
	{
		CUserOptions::setOption($this->syncOptionCategory, $this->syncOptionName, $mailboxesSyncInfo, false, $this->userId);
	}

	/**
	 * @return mixed
	 */
	private function getMailboxesSyncInfo()
	{
		return CUserOptions::getOption($this->syncOptionCategory, $this->syncOptionName, [], $this->userId);
	}

	public function getNextTimeToSync($lastMailCheckData)
	{
		return intval($lastMailCheckData['timeStarted']) + $this->mailCheckInterval - time();
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