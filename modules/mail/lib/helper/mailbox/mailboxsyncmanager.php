<?php
namespace Bitrix\Mail\Helper\Mailbox;

use COption;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Mail\Internals\MailEntityOptionsTable;
use Bitrix\Mail\MailFilterTable;
use Bitrix\Main\Loader;
use Bitrix\Mail\Helper;

class MailboxSyncManager
{
	private $userId;
	private $mailCheckInterval;

	const MIN_INTERVAL_BETWEEN_CONNECTION_ATTEMPTS = 300;
	const MAX_CONNECTION_ATTEMPTS_BEFORE_UNAVAILABLE = 3;

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

	public function deleteSyncData($mailboxId): \Bitrix\Main\DB\Result
	{
		return MailEntityOptionsTable::deleteList($this->getOptionFilter($mailboxId, MailEntityOptionsTable::SYNC_STATUS_PROPERTY_NAME));
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

	public function setSyncStatus(int $mailboxId, bool $isSuccess, ?int $time = null): void
	{
		$this->saveSyncStatus($mailboxId, $isSuccess, $this->buildTimeForSyncStatus($time));

		if ($isSuccess)
		{
			$this->removeConnectErrorCache($mailboxId);
		}
	}

	public function getCachedConnectionStatus(int $mailboxId): bool
	{
		$lastMailboxSyncStatus = $this->getLastMailboxSyncIsSuccessStatus($mailboxId);

		if ($lastMailboxSyncStatus)
		{
			$this->removeConnectErrorCache($mailboxId);

			return true;
		}

		$mailboxHelper = Helper\Mailbox::createInstance($mailboxId, false);

		if (!$mailboxHelper)
		{
			// The mailbox was legally disabled,
			// therefore the connection should be considered successful to avoid future notifications and error outputs.
			return true;
		}

		if ($mailboxHelper->isAuthenticated())
		{
			$this->removeConnectErrorCache($mailboxId);

			return true;
		}

		$error = $this->getConnectError($mailboxId);

		$dateAttempt = new DateTime();
		$connectErrorAttempt = isset($error['VALUE']) ? (int) $error['VALUE'] + 1 : 0;

		if ($connectErrorAttempt > 0)
		{
			if ($error['DATE_INSERT'] <= (clone $dateAttempt)->add('- '.self::MIN_INTERVAL_BETWEEN_CONNECTION_ATTEMPTS.' seconds'))
			{
				$this->updateConnectError($mailboxId, $connectErrorAttempt, $dateAttempt);
			}
		}
		else
		{
			$this->addConnectError($mailboxId);
		}

		if ($connectErrorAttempt >= self::MAX_CONNECTION_ATTEMPTS_BEFORE_UNAVAILABLE)
		{
			return false;
		}

		return true;
	}

	private function getOptionFilter(int $mailboxId, string $propertyName): array
	{
		return [
			'=MAILBOX_ID' => $mailboxId,
			'=ENTITY_TYPE' => MailEntityOptionsTable::MAILBOX_TYPE_NAME,
			'=ENTITY_ID' => $mailboxId,
			'=PROPERTY_NAME' => $propertyName,
		];
	}

	private function removeConnectErrorCache(int $mailboxId): void
	{
		MailEntityOptionsTable::deleteList($this->getOptionFilter($mailboxId, MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME));
	}

	private function getConnectError(int $mailboxId): array|null
	{
		return MailEntityOptionsTable::getRow([
			'select' => [
				'DATE_INSERT',
				'VALUE',
			],
			'filter' => $this->getOptionFilter($mailboxId, MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME),
		]);
	}

	private function updateConnectError(int $mailboxId, int $attemptNumber, DateTime $dateAttempt = new DateTime()): void
	{
		MailEntityOptionsTable::update(
			[
				'MAILBOX_ID' => $mailboxId,
				'ENTITY_TYPE' => MailEntityOptionsTable::MAILBOX_TYPE_NAME,
				'ENTITY_ID' => $mailboxId,
				'PROPERTY_NAME' => MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME,
			],
			[
				'DATE_INSERT' => $dateAttempt,
				'VALUE' => $attemptNumber,
			],
		);
	}

	private function addConnectError(int $mailboxId, DateTime $dateAttempt = new DateTime()): void
	{
		MailEntityOptionsTable::insertIgnore(
			$mailboxId,
			$mailboxId,
			MailEntityOptionsTable::MAILBOX_TYPE_NAME,
			MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME,
			1,
			$dateAttempt
		);
	}

	private function saveSyncStatus(int $mailboxId, bool $status, int $timestamp): void
	{
		$date = DateTime::createFromTimestamp($timestamp);

		if(MailEntityOptionsTable::getCount($this->getOptionFilter($mailboxId, MailEntityOptionsTable::SYNC_STATUS_PROPERTY_NAME)))
		{
			MailEntityOptionsTable::update(
				[
					'MAILBOX_ID' => $mailboxId,
					'ENTITY_TYPE' => MailEntityOptionsTable::MAILBOX_TYPE_NAME,
					'ENTITY_ID' => $mailboxId,
					'PROPERTY_NAME' => MailEntityOptionsTable::SYNC_STATUS_PROPERTY_NAME,
				],
				[
					'DATE_INSERT' => $date,
					'VALUE' => $status,
				],
			);
		}
		else
		{
			MailEntityOptionsTable::insertIgnore(
				$mailboxId,
				$mailboxId,
				MailEntityOptionsTable::MAILBOX_TYPE_NAME,
				MailEntityOptionsTable::SYNC_STATUS_PROPERTY_NAME,
				$status,
				$date
			);
		}
	}

	/**
	 * @param int|null $userId
	 * @return int[]
	 */
	public function getCachedMailboxesIdsWithConnectionError(?int $userId = null): array
	{
		global $USER;

		if (!$userId && is_object($USER) && $USER->isAuthorized())
		{
			$userId = (int)$USER->getId();
		}

		if (!$userId)
		{
			return [];
		}

	    static $userMailboxesIdsWithConnectionError = [];

	    if (isset($userMailboxesIdsWithConnectionError[$userId]))
		{
	        return $userMailboxesIdsWithConnectionError[$userId];
	    }

	    $userMailboxesIdsWithConnectionError[$userId] = [];

	    $userMailboxIds = array_keys(MailboxTable::getUserMailboxes($userId, true));

	    if (empty($userMailboxIds))
		{
	        return $userMailboxesIdsWithConnectionError[$userId];
	    }

	    $mailboxesWithConnectionError = MailEntityOptionsTable::getList(
	        [
	            'select' => [
	                'ENTITY_ID',
	                'VALUE',
	            ],
	            'filter' => [
	                '=ENTITY_TYPE' => MailEntityOptionsTable::MAILBOX_TYPE_NAME,
	                '=ENTITY_ID' => $userMailboxIds,
	                '=PROPERTY_NAME' => MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME,
	            ],
	        ]
	    );

	    while ($error = $mailboxesWithConnectionError->fetch())
		{
	        if ((int)$error['VALUE'] >= self::MAX_CONNECTION_ATTEMPTS_BEFORE_UNAVAILABLE)
			{
	            $userMailboxesIdsWithConnectionError[$userId][] = (int)$error['ENTITY_ID'];
	        }
	    }

	    return $userMailboxesIdsWithConnectionError[$userId];
	}

	/**
	 * @param array $userIds
	 * @return int[]
	 */
	public static function getMailboxesWithConnectionErrorForUsers(array $userIds): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$query = MailEntityOptionsTable::query()
			->registerRuntimeField(
				'MAILBOX',
				[
					'data_type' => MailboxTable::class,
					'reference' => [
						'=this.ENTITY_ID' => 'ref.ID',
					],
					'join_type' => 'INNER',
				],
			)
			->setSelect([
				'ENTITY_ID',
			])
			->where('ENTITY_TYPE', '=', MailEntityOptionsTable::MAILBOX_TYPE_NAME)
			->where('PROPERTY_NAME', '=', MailEntityOptionsTable::CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME)
			->where('VALUE', '>=', self::MAX_CONNECTION_ATTEMPTS_BEFORE_UNAVAILABLE)
			->whereIn('MAILBOX.USER_ID', $userIds)
		;

		$result = $query->exec();

		$mailboxIdsWithError = [];
		while ($row = $result->fetch())
		{
			$mailboxIdsWithError[] = (int)$row['ENTITY_ID'];
		}

		return $mailboxIdsWithError;
	}

	public function getMailboxesSyncInfo(): array
	{
		static $mailboxesSyncInfo = null;

		if (is_null($mailboxesSyncInfo))
		{
			$mailboxesSyncInfo = [];

			$userMailboxIds = array_keys(MailboxTable::getUserMailboxes(onlyIds: true));

			if (empty($userMailboxIds))
			{
				return $mailboxesSyncInfo;
			}

			$datesLastOpening = MailEntityOptionsTable::getList(
				[
					'select' => [
						'ENTITY_ID',
						'VALUE',
						'DATE_INSERT',
					],
					'filter' => [
						'=ENTITY_TYPE' => MailEntityOptionsTable::MAILBOX_TYPE_NAME,
						'=ENTITY_ID' => $userMailboxIds,
						'=PROPERTY_NAME' => MailEntityOptionsTable::SYNC_STATUS_PROPERTY_NAME,
					],
				]
			)->fetchAll();

			foreach ($datesLastOpening as $date)
			{
				if (isset($date['VALUE']))
				{
					$mailboxesSyncInfo[(int)$date['ENTITY_ID']] = [
						'isSuccess' => (bool)$date['VALUE'],
						'timeStarted' => $date['DATE_INSERT']->getTimestamp(),
					];
				}
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
	public function getFirstFailedToSyncMailboxId(): ?int
	{
		return $this->getCachedMailboxesIdsWithConnectionError()[0] ?? null;
	}

	/**
	 * Returns the status of the last sync.
	 * If the status could not be found out, null will be returned.
	 *
	 * @param int $mailboxId
	 * @return bool|null
	 */
	public function getLastMailboxSyncIsSuccessStatus(int $mailboxId): ?bool
	{
		$mailboxesOptions = $this->getMailboxesSyncInfo();
		if (!(isset($mailboxesOptions[$mailboxId]) && array_key_exists('isSuccess', $mailboxesOptions[$mailboxId])))
		{
			return null;
		}

		if (isset($mailboxesOptions[$mailboxId]['isSuccess']))
		{
			return (bool)$mailboxesOptions[$mailboxId]['isSuccess'];
		}

		return null;
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