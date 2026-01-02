<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Helper\Mailbox\MailboxSyncManager;
use Bitrix\Mail\Internals\MailEntityOptionsTable;
use Bitrix\Mail\MailServicesTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Mail\Internals\MailboxAccessTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Mail\Helper\User\UserProvider;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type;
use Bitrix\Main\Mail\Internal;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Bitrix24\MailCounter;
use Bitrix\Main\ModuleManager;

class MailboxSettingsGridHelper
{
	protected const DEFAULT_PAGE_SIZE = 20;
	protected const DEFAULT_UNSEEN_LIMIT = 999;

	private UserProvider $userProvider;

	public function __construct()
	{
		$this->userProvider = new UserProvider();
	}

	/**
	 * @return array<array{
	 *     ID: int,
	 *     USERS_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     OWNER_DATA: array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     },
	 *     EMAIL: string,
	 *     COUNTERS: array{
	 *         EMAIL: array{
	 *             value: int,
	 *             isOverLimit: bool
	 *         }
	 *     },
	 *     SENT_LIMITS_AND_COUNTERS: array{
	 *         daily_sent: int,
	 *         monthly_sent: int,
	 *         daily_limit: int|null,
	 *         monthly_limit: int|null
	 *     },
	 *     LAST_ACTIVITY: int|null,
	 *     MAILBOX_NAME: string,
	 *     CRM_ENABLED: 'Y'|'N',
	 *     HAS_ERROR: bool,
	 *     CRM_LEAD_RESP_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     SENDER_NAME: string,
	 *     VOLUME_MB: string
	 * }>
	 */
	public function getGridData(int $limit, int $offset, array $filterData = []): array
	{
		$mailboxes = $this->getMailboxesWithOwners($limit, $offset, $filterData);

		if (empty($mailboxes))
		{
			return [];
		}

		$mailboxIds = array_column($mailboxes, 'ID');
		$emails = array_column($mailboxes, 'EMAIL');

		$emailLimitsAndCounters = $this->getEmailLimitsAndCounters($emails);
		$userAccessData = $this->getUserAccessData($mailboxIds);

		$allUserIds = [];
		foreach ($userAccessData as $usersForMailbox)
		{
			foreach ($usersForMailbox as $userAccess)
			{
				$allUserIds[] = $userAccess['USER_ACCESS_ID'];
			}
		}

		$ownerIds = [];
		foreach ($mailboxes as &$mailbox)
		{
			$mailbox['SENT_LIMITS_AND_COUNTERS'] = $emailLimitsAndCounters[$mailbox['EMAIL']];

			$options = $mailbox['OPTIONS'] ?? [];
			$crmLeadResp = $options['crm_lead_resp'] ?? [];

			foreach ($crmLeadResp as $userId)
			{
				$allUserIds[] = $userId;
			}

			if ($mailbox['OWNER_ID'])
			{
				$allUserIds[] = $mailbox['OWNER_ID'];
				$ownerIds[] = $mailbox['OWNER_ID'];
			}
		}
		unset($mailbox);

		$errorMailboxIds = MailboxSyncManager::getMailboxesWithConnectionErrorForUsers(array_unique($ownerIds));
		$errorMailboxIdsMap = array_flip($errorMailboxIds);

		$allUserIds = array_unique(array_filter($allUserIds));
		$users = $this->userProvider->getUsersInfo($allUserIds);

		$rows = [];
		foreach ($mailboxes as $mailbox)
		{
			$userAccessList = $userAccessData[$mailbox['ID']] ?? [];
			$usersData = [];

			foreach ($userAccessList as $userAccess)
			{
				if (isset($users[$userAccess['USER_ACCESS_ID']]))
				{
					$usersData[$userAccess['USER_ACCESS_ID']] = $users[$userAccess['USER_ACCESS_ID']];
				}
			}

			$dataFromOptions = $this->extractDataFromOptions($mailbox);
			$crmLeadRespIds = $dataFromOptions['crmLeadResp'];
			$crmLeadRespData = [];

			foreach ($crmLeadRespIds as $userId)
			{
				if (isset($users[$userId]))
				{
					$crmLeadRespData[] = $users[$userId];
				}
			}

			$ownerData = null;
			if ($mailbox['OWNER_ID'] && isset($users[$mailbox['OWNER_ID']]))
			{
				$ownerData = $users[$mailbox['OWNER_ID']];
			}

			$rows[] = $this->prepareGridRow($mailbox, $usersData, $crmLeadRespData, $ownerData, $errorMailboxIdsMap);
		}

		return $rows;
	}

	/**
	 * @return array<array{
	 *     ID: int,
	 *     USERS_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     OWNER_DATA: array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     },
	 *     EMAIL: string,
	 *     COUNTERS: array{
	 *         EMAIL: array{
	 *             value: int,
	 *             isOverLimit: bool
	 *         }
	 *     },
	 *     SENT_LIMITS_AND_COUNTERS: array{
	 *         daily_sent: int,
	 *         monthly_sent: int,
	 *         daily_limit: int|null,
	 *         monthly_limit: int|null
	 *     },
	 *     LAST_ACTIVITY: int|null,
	 *     MAILBOX_NAME: string,
	 *     CRM_ENABLED: 'Y'|'N',
	 *     CRM_LEAD_RESP_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     SENDER_NAME: string,
	 *     VOLUME_MB: string
	 * }>
	 */
	public function getGridDataWithOrmParams(array $ormParams, array $filterData = []): array
	{
		$limit = $ormParams['limit'] ?? self::DEFAULT_PAGE_SIZE;
		$offset = $ormParams['offset'] ?? 0;

		return $this->getGridData($limit, $offset, $filterData);
	}

	public function getTotalCount(array $filterData = []): int
	{
		$query = $this->buildMailboxesWithOwnersQuery();
		$this->applyFilterToQuery($query, $filterData);

		$query->setSelect([new ExpressionField('CNT', 'COUNT(DISTINCT %s)', 'ID')]);

		$result = $query->exec()->fetch();

		return (int)($result['CNT'] ?? 0);
	}

	/**
	 * @return array{
	 *     daily_limits: array<string, int|null>,
	 *     monthly_limit: int|null
	 * }
	 */
	public function getEmailLimits(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		$monthlyLimit = null;
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$monthlyLimit = $this->getMonthlySendingQuota();
		}

		$dailyLimits = $this->getEmailDailyLimits($emails);

		return [
			'daily_limits' => $dailyLimits,
			'monthly_limit' => $monthlyLimit,
		];
	}

	/**
	 * @return array<string, array{
	 *     daily_sent: int,
	 *     monthly_sent: int,
	 *     daily_limit: int|null,
	 *     monthly_limit: int|null
	 * }>
	 */
	public function getEmailLimitsAndCounters(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		$dailyCounters = $this->getDailySentCount($emails);
		$monthlyCounters = $this->getMonthlySentCount($emails);
		$limits = $this->getEmailLimits($emails);

		$result = [];
		foreach ($emails as $email)
		{
			$result[$email] = [
				'daily_sent' => $dailyCounters[$email] ?? 0,
				'monthly_sent' => $monthlyCounters[$email] ?? 0,
				'daily_limit' => $limits['daily_limits'][$email] ?? null,
				'monthly_limit' => $limits['monthly_limit'] ?? null,
			];
		}

		return $result;
	}

	public static function rebindSenders(int $mailboxId, int $newOwnerId): void
	{
		$senders =
			SenderTable::query()
				->setSelect(['ID'])
				->where('PARENT_MODULE_ID', 'mail')
				->where('PARENT_ID', $mailboxId)
				->fetchCollection()
		;

		foreach ($senders as $sender)
		{
			$sender->setUserId($newOwnerId);
		}

		$senders->save();
	}

	/**
	 * @return array<array{
	 *     ID: int,
	 *     USERS_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     OWNER_DATA: array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     },
	 *     EMAIL: string,
	 *     COUNTERS: array{
	 *         EMAIL: array{
	 *             value: int,
	 *             isOverLimit: bool
	 *         }
	 *     },
	 *     SENT_LIMITS_AND_COUNTERS: array{
	 *         daily_sent: int,
	 *         monthly_sent: int,
	 *         daily_limit: int|null,
	 *         monthly_limit: int|null
	 *     },
	 *     LAST_ACTIVITY: int|null,
	 *     MAILBOX_NAME: string,
	 *     CRM_ENABLED: 'Y'|'N',
	 *     HAS_ERROR: bool,
	 *     CRM_LEAD_RESP_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     SENDER_NAME: string,
	 *     VOLUME_MB: string
	 * }>
	 */
	private function prepareGridRow(
		array $mailbox,
		array $usersData,
		array $crmLeadRespData = [],
		User\User $ownerData = null,
		array $errorMailboxIdsMap = [],
	): array
	{
		$dataFromOptions = $this->extractDataFromOptions($mailbox);

		$usersFormattedData = [];
		foreach ($usersData as $userData)
		{
			$usersFormattedData[] = $userData->toArray();
		}

		$crmLeadRespFormattedData = [];
		foreach ($crmLeadRespData as $userData)
		{
			$crmLeadRespFormattedData[] = $userData->toArray();
		}

		$ownerFormattedData = [
			'ID' => $mailbox['OWNER_ID'],
			'NAME' => $mailbox['OWNER_NAME'],
			'LAST_NAME' => $mailbox['OWNER_LAST_NAME'],
			'LOGIN' => $mailbox['OWNER_LOGIN'],
		];

		if ($ownerData)
		{
			$ownerFormattedData = $ownerData->toArray();
		}

		$actualCount = (int)$mailbox['UNSEEN_CNT'];
		$volumeMb = round((float)$mailbox['TOTAL_VOLUME_BYTES'] / (1024 * 1024), 2);

		return [
			'ID' => (int)$mailbox['ID'],
			'USERS_DATA' => $usersFormattedData,
			'OWNER_DATA' => $ownerFormattedData,
			'EMAIL' => $mailbox['EMAIL'],
			'SERVICE_NAME' => $mailbox['SERVICE_NAME'],
			'COUNTERS' => [
				'EMAIL' => [
					'value' => min($actualCount, self::DEFAULT_UNSEEN_LIMIT),
					'isOverLimit' => $actualCount > self::DEFAULT_UNSEEN_LIMIT,
				],
			],
			'SENT_LIMITS_AND_COUNTERS' => $mailbox['SENT_LIMITS_AND_COUNTERS'],
			'LAST_ACTIVITY' => $mailbox['LAST_ACTIVITY'] ? $mailbox['LAST_ACTIVITY']->getTimestamp() : null,
			'MAILBOX_NAME' => $mailbox['NAME'],
			'CRM_ENABLED' => $dataFromOptions['crmEnabled'],
			'CRM_LEAD_RESP_DATA' => $crmLeadRespFormattedData,
			'SENDER_NAME' => $mailbox['USERNAME'],
			'VOLUME_MB' => Loc::getMessage(
				'MAIL_MAILBOX_LIST_COLUMN_VOLUME_MB_DATA_TYPE',
				['#AMOUNT_OF_DATA#' => $volumeMb],
			),
			'HAS_ERROR' => isset($errorMailboxIdsMap[$mailbox['ID']]),
		];
	}

	/**
	 * @return array{
	 *     crmEnabled: 'Y'|'N',
	 *     crmLeadResp: array<int>
	 * }
	 */
	private function extractDataFromOptions(array $mailbox): array
	{
		$options = $mailbox['OPTIONS'];
		$crmEnabled = $this->isCrmIntegrationEnabled($mailbox) ? 'Y' : 'N';
		$crmLeadResp = [];

		if ($crmEnabled === 'Y')
		{
			$crmLeadResp = $options['crm_lead_resp'] ?? [];
		}

		return [
			'crmEnabled' => $crmEnabled,
			'crmLeadResp' => $crmLeadResp,
		];
	}

	private function isCrmIntegrationEnabled(array $mailbox): bool
	{
		$flags = $mailbox['OPTIONS']['flags'] ?: [];
		$crmEnabledFlag = 'crm_connect';

		if (in_array($crmEnabledFlag, $flags, true))
		{
			return true;
		}

		return !empty($mailbox['__crm']);
	}

	private function buildMailboxesWithOwnersQuery(): Query
	{
		$typeFile = \Bitrix\Disk\Internals\ObjectTable::TYPE_FILE;
		$deletedTypeNone = \Bitrix\Disk\Internals\ObjectTable::DELETED_TYPE_NONE;
		$moduleId = 'mail';
		$entityType = 'Bitrix\\\Mail\\\Disk\\\ProxyType\\\Mail';

		$sqlTotalVolumeBytes = <<<SQL
		(
			SELECT COALESCE(SUM(F.FILE_SIZE), 0)
			FROM b_mail_message MM
			LEFT JOIN b_mail_msg_attachment MA ON MA.MESSAGE_ID = MM.ID
			LEFT JOIN b_file F ON F.ID = MA.FILE_ID
			WHERE MM.MAILBOX_ID = %s
		)
		+
		(
			SELECT COALESCE(SUM(COALESCE(O.SIZE, 0) + COALESCE(PF.FILE_SIZE, 0)), 0)
			FROM b_disk_storage S
			LEFT JOIN b_disk_object O ON O.STORAGE_ID = S.ID AND O.TYPE = {$typeFile} AND O.DELETED_TYPE = {$deletedTypeNone}
			LEFT JOIN b_file PF ON PF.ID = O.PREVIEW_ID
			WHERE S.ENTITY_ID = %s AND S.MODULE_ID = '{$moduleId}' AND S.ENTITY_TYPE = '{$entityType}'
		)
		SQL;

		$query =
			MailboxTable::query()
				->registerRuntimeField(
					new \Bitrix\Main\ORM\Fields\ExpressionField(
						'TOTAL_VOLUME_BYTES',
						$sqlTotalVolumeBytes,
						['ID', 'ID'],
					),
				)
				->registerRuntimeField(
					'OWNER',
					[
						'data_type' => UserTable::class,
						'reference' => [
							'=this.USER_ID' => 'ref.ID',
						],
						'join_type' => 'LEFT',
					],
				)
				->registerRuntimeField(
					'ENTITY_OPTIONS',
					[
						'data_type' => MailEntityOptionsTable::class,
						'reference' => [
							'=this.ID' => 'ref.MAILBOX_ID',
							'=ref.PROPERTY_NAME' => ['?', 'SYNC_STATUS'],
							'=ref.ENTITY_TYPE' => ['?', 'MAILBOX'],
						],
						'join_type' => 'LEFT',
					],
				)
				->registerRuntimeField(
					new \Bitrix\Main\ORM\Fields\ExpressionField(
						'UNSEEN_CNT',
						'(SELECT COALESCE(SUM(VALUE), 0) FROM b_mail_counter WHERE ENTITY_TYPE = "MAILBOX" AND ENTITY_ID = %s)',
						['ID'],
					),
				)
				->registerRuntimeField(
					'SERVICE',
					[
						'data_type' => MailServicesTable::class,
						'reference' => [
							'=this.SERVICE_ID' => 'ref.ID',
						],
						'join_type' => 'LEFT',
					],
				)
				->setSelect([
					'ID',
					'NAME',
					'USERNAME',
					'EMAIL',
					'SERVICE_NAME' => 'SERVICE.NAME',
					'OPTIONS',
					'OWNER_ID' => 'USER_ID',
					'OWNER_NAME' => 'OWNER.NAME',
					'OWNER_LAST_NAME' => 'OWNER.LAST_NAME',
					'OWNER_LOGIN' => 'OWNER.LOGIN',
					'LAST_ACTIVITY' => 'ENTITY_OPTIONS.DATE_INSERT',
					'TOTAL_VOLUME_BYTES',
					'UNSEEN_CNT',
				])
				->where('ACTIVE', 'Y')
		;

		return $query;
	}

	/**
	 * @return array<int, array<array{
	 *     MAILBOX_ID: string,
	 *     ACCESS_CODE: string,
	 *     USER_ACCESS_ID: string
	 * }>>
	 */
	private function getUserAccessData(array $mailboxIds): array
	{
		$query =
			MailboxAccessTable::query()
				->registerRuntimeField(
					'USER_ACCESS_ID_FIELD',
					new ExpressionField(
						'USER_ACCESS_ID_FIELD',
						'SUBSTRING(%s, 2)',
						'ACCESS_CODE',
					),
				)
				->setSelect([
					'MAILBOX_ID',
					'ACCESS_CODE',
					'USER_ACCESS_ID' => 'USER_ACCESS_ID_FIELD',
				])
				->whereIn('MAILBOX_ID', $mailboxIds)
				->where('TASK_ID', 0)
		;

		$result = $query->exec();
		$userAccessData = [];

		while ($row = $result->fetch())
		{
			$userAccessData[$row['MAILBOX_ID']][] = $row;
		}

		return $userAccessData;
	}

	/**
	 * @return array<string, int>
	 */
	private function getDailySentCount(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		$date = new Type\Date();

		$query =
			Internal\SenderSendCounterTable::query()
				->setSelect([
					'EMAIL',
					'CNT',
				])
				->where('DATE_STAT', $date)
				->whereIn('EMAIL', $emails)
		;

		$result = $query->fetchAll();
		$counters = [];

		foreach ($result as $item)
		{
			$counters[$item['EMAIL']] = (int)($item['CNT'] ?? 0);
		}

		return $counters;
	}

	/**
	 * @return array<string, int>
	 */
	private function getMonthlySentCount(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		$date = new Type\Date(date("01.m.Y"), "d.m.Y");

		$query =
			Internal\SenderSendCounterTable::query()
				->setSelect([
					'EMAIL',
					'TOTAL_CNT' => new ExpressionField('TOTAL_CNT', 'SUM(CNT)'),
				])
				->where('DATE_STAT', '>=', $date)
				->whereIn('EMAIL', $emails)
				->addGroup('EMAIL')
		;

		$result = $query->fetchAll();
		$counters = [];

		foreach ($result as $item)
		{
			$counters[$item['EMAIL']] = (int)$item['TOTAL_CNT'];
		}

		return $counters;
	}

	/**
	 * @return array<string, int|null>
	 */
	private function getEmailDailyLimits(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		$validEmails = [];
		$limits = [];

		foreach ($emails as $email)
		{
			$address = new Address($email);
			if ($address->validate())
			{
				$validEmail = $address->getEmail();
				$validEmails[] = $validEmail;
				$limits[$validEmail] = null;
			}
			else
			{
				$limits[$email] = null;
			}
		}

		if (empty($validEmails))
		{
			return $limits;
		}

		$query =
			SenderTable::query()
				->setSelect([
					'EMAIL',
					'OPTIONS',
					'ID',
				])
				->where('IS_CONFIRMED', true)
				->whereIn('EMAIL', $validEmails)
				->setOrder([
					'EMAIL' => 'ASC',
					'ID' => 'DESC',
				])
		;

		$result = $query->fetchAll();
		$processedEmails = [];

		foreach ($result as $item)
		{
			$email = $item['EMAIL'];

			if (!isset($processedEmails[$email]) && isset($item['OPTIONS']['smtp']['limit']))
			{
				$limit = (int)$item['OPTIONS']['smtp']['limit'];
				$limits[$email] = max($limit, 0);
				$processedEmails[$email] = true;
			}
		}

		return $limits;
	}

	private function getMonthlySendingQuota(): ?int
	{
		static $limit = null;

		if (is_null($limit))
		{
			$counter = new MailCounter();
			$limit = $counter->getLimit();
		}

		return $limit;
	}

	/**
	 * @return array<array{
	 *     ID: int,
	 *     USERS_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     OWNER_DATA: array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     },
	 *     EMAIL: string,
	 *     COUNTERS: array{
	 *         EMAIL: array{
	 *             value: int,
	 *             isOverLimit: bool
	 *         }
	 *     },
	 *     SENT_LIMITS_AND_COUNTERS: array{
	 *         daily_sent: int,
	 *         monthly_sent: int,
	 *         daily_limit: int|null,
	 *         monthly_limit: int|null
	 *     },
	 *     LAST_ACTIVITY: int|null,
	 *     MAILBOX_NAME: string,
	 *     CRM_ENABLED: 'Y'|'N',
	 *     CRM_LEAD_RESP_DATA: array<array{
	 *         id: int,
	 *         name: string,
	 *         avatar: array{
	 *             src: string,
	 *             width: int,
	 *             height: int,
	 *             size: int,
	 *         },
	 *         pathToProfile: string,
	 *     }>,
	 *     SENDER_NAME: string,
	 *     VOLUME_MB: string
	 * }>
	 */
	private function getMailboxesWithOwners(
		int $limit,
		int $offset,
		array $filterData,
	): array
	{
		$query = $this->buildMailboxesWithOwnersQuery();
		$query->setLimit($limit);
		$query->setOffset($offset);
		$query->setOrder(['ID' => 'DESC']);

		$this->applyFilterToQuery($query, $filterData);

		return $query->fetchAll();
	}

	private function applyFilterToQuery(Query $query, array $filterData): void
	{
		if (empty($filterData))
		{
			return;
		}

		foreach ($filterData as $field => $value)
		{
			if (empty($value) && $value !== '0')
			{
				continue;
			}

			switch ($field)
			{
				case 'EMAIL':
					if (is_string($value))
					{
						$query->whereLike('EMAIL', '%' . $value . '%');
					}

					break;

				case 'SENDER_NAME':
					if (is_string($value))
					{
						$query->whereLike('USERNAME', '%' . $value . '%');
					}

					break;

				case 'OWNER':
					if (is_array($value) && !empty($value))
					{
						$userIds = $this->extractUserIdsFromFilterValue($value);
						if (!empty($userIds))
						{
							$query->whereIn('USER_ID', $userIds);
						}
					}

					break;

				case 'LAST_SYNC_datesel':
					if (!empty($value) && is_string($value))
					{
						$lastSyncFrom = $filterData['LAST_SYNC_from'];
						if (!empty($lastSyncFrom))
						{
							try
							{
								$dateFrom = new Type\DateTime($lastSyncFrom);
								$query->where('ENTITY_OPTIONS.DATE_INSERT', '>=', $dateFrom);
							}
							catch (\Exception $e)
							{
							}
						}

						$lastSyncTo = $filterData['LAST_SYNC_to'];
						if (!empty($lastSyncTo))
						{
							try
							{
								$dateTo = new Type\DateTime($lastSyncTo);
								$query->where('ENTITY_OPTIONS.DATE_INSERT', '<=', $dateTo);
							}
							catch (\Exception $e)
							{
							}
						}

					}

					break;

				case 'CRM_INTEGRATION':
					if ($value === 'Y')
					{
						$query->whereExpr("OPTIONS REGEXP 's:5:\"flags\";a:[0-9]+:\\\\{.*s:11:\"crm_connect\".*\\\\}'", []);
					}
					elseif ($value === 'N')
					{
						$query->whereExpr(
							'(OPTIONS NOT LIKE "%%s:5:\"flags\"%%" OR OPTIONS NOT REGEXP "s:5:\"flags\";a:[0-9]+:\\\\{.*s:11:\"crm_connect\".*\\\\}")', [],
						);
					}

					break;

				case 'ACCESS_USERS':
					if (is_array($value) && !empty($value))
					{
						$userIds = $this->extractUserIdsFromFilterValue($value, 'U');

						if (!empty($userIds))
						{
							$query->registerRuntimeField(
								'ACCESS_FILTER',
								[
									'data_type' => MailboxAccessTable::class,
									'reference' => [
										'=this.ID' => 'ref.MAILBOX_ID',
									],
									'join_type' => 'INNER',
								],
							);
							$query->whereIn('ACCESS_FILTER.ACCESS_CODE', $userIds);
						}
					}

					break;

				case 'CRM_QUEUE':
					if (is_array($value) && !empty($value))
					{
						$userIds = $this->extractUserIdsFromFilterValue($value);

						if (!empty($userIds))
						{
							$conditions = [];
							foreach ($userIds as $id)
							{
								$conditions[] = "OPTIONS REGEXP 's:13:\"crm_lead_resp\";a:[0-9]+:\\\\{.*i:[0-9]+;i:$id;.*\\\\}'";
							}

							if ($conditions)
							{
								$query->whereExpr('(' . implode(' OR ', $conditions) . ')', []);
							}
						}
					}

					break;

				case 'DISK_SIZE_numsel':
					if (empty($value))
					{
						break;
					}

					$bytesInMb = 1024 * 1024;

					$fromMb = null;
					$fromBytes = null;
					$toBytes = null;

					if (is_numeric($filterData['DISK_SIZE_from']))
					{
						$fromMb = (float)$filterData['DISK_SIZE_from'];
						$fromBytes = (float)$filterData['DISK_SIZE_from'] * $bytesInMb;
					}

					if(is_numeric($filterData['DISK_SIZE_to']))
					{
						$toBytes = (float)$filterData['DISK_SIZE_to'] * $bytesInMb;
					}

					switch ($value)
					{
						case 'more':
							if ($fromBytes !== null)
							{
								$query->having('TOTAL_VOLUME_BYTES', '>', $fromBytes);
							}

							break;
						case 'less':
							if ($toBytes !== null)
							{
								$query->having('TOTAL_VOLUME_BYTES', '<', $toBytes);
							}

							break;
						case 'exact':
							if ($fromBytes !== null)
							{
								$lowerBoundBytes = ($fromMb - 0.005) * $bytesInMb;
								$upperBoundBytes = ($fromMb + 0.005) * $bytesInMb;

								$query->having('TOTAL_VOLUME_BYTES', '>=', $lowerBoundBytes);
								$query->having('TOTAL_VOLUME_BYTES', '<', $upperBoundBytes);
							}

							break;
						case 'range':
							if ($fromBytes !== null && $toBytes !== null)
							{
								$query->having('TOTAL_VOLUME_BYTES', '>=', $fromBytes);
								$query->having('TOTAL_VOLUME_BYTES', '<=', $toBytes);
							}

							break;
					}
				default:
					break;
			}
		}
	}

	private function extractUserIdsFromFilterValue(array $values, ?string $accessCode = ''): array
	{
		$userIds = [];
		foreach ($values as $value)
		{
			if (is_string($value) && str_starts_with($value, 'U'))
			{
				$userIds[] = $accessCode . (int)substr($value, 1);
			}
			elseif (is_numeric($value))
			{
				$userIds[] = $accessCode . (int)$value;
			}
		}

		return array_filter(array_unique($userIds));
	}
}
