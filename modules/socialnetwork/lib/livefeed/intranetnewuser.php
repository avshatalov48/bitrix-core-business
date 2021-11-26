<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

class IntranetNewUser extends Provider
{
	public const PROVIDER_ID = 'INTRANET_NEW_USER';
	public const CONTENT_TYPE_ID = 'INTRANET_NEW_USER';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'intranet_new_user' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider(): Provider
	{
		return new LogComment();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$ratingEntityId = $this->getEntityId();

		if ($ratingEntityId <= 0)
		{
			return;
		}

		$sourceFields = [];

		if (isset($cache[$ratingEntityId]))
		{
			$sourceFields = $cache[$ratingEntityId];
		}
		else
		{
			$userId = 0;
			$bitrix24NewUserProvider = new Bitrix24NewUser();

			$res = LogTable::getList([
				'filter' => [
					'@EVENT_ID' => array_merge($this->getEventId(), $bitrix24NewUserProvider->getEventId() ),
					'=RATING_ENTITY_ID' => $ratingEntityId
				],
				'select' => [ 'ID', 'ENTITY_ID' ]
			]);
			if ($logEntry = $res->fetch())
			{
				$userId = $logEntry['ENTITY_ID'];
			}

			if ($userId > 0)
			{
				$res = UserTable::getList(array(
					'filter' => [
						'=ID' => $userId
					],
					'select' => [
						'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'UF_DEPARTMENT'
					]
				));
				if ($user = $res->fetch())
				{
					$userName = \CUser::formatName(
						\CSite::getNameFormat(),
						$user,
						true,
						false
					);
					$user['FULL_NAME'] = $userName;

					$user['DEPARTMENT_NAME'] = '';
					if (
						is_array($user['UF_DEPARTMENT'])
						&& !empty($user['UF_DEPARTMENT'])
						&& Loader::includeModule('iblock')
					)
					{
						$res = SectionTable::getList([
							'filter' => [
								'ID' => $user['UF_DEPARTMENT']
							],
							'select' => [ 'ID', 'NAME' ]
						]);
						if ($sectionFields = $res->fetch())
						{
							$user['DEPARTMENT_NAME'] = $sectionFields['NAME'];
						}
					}

					$sourceFields = array_merge($user, [ 'LOG_ENTRY' => $logEntry ]);
					$cache[$ratingEntityId] = $sourceFields;
				}
			}
		}

		if (empty($sourceFields))
		{
			return;
		}

		$this->setLogId($sourceFields['LOG_ENTRY']['ID']);
		$this->setSourceFields($sourceFields);
		$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_INTRANET_NEW_USER_TITLE', [
			'#USER_NAME#' => $sourceFields['FULL_NAME']
		]));
	}

	public function getPinnedTitle(): string
	{
		$result = '';

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$result = Option::get('main', 'site_name');
		}
		else
		{
			if (empty($this->sourceFields))
			{
				$this->initSourceFields();
			}

			$sourceFields = $this->getSourceFields();
			if (empty($sourceFields))
			{
				return $result;
			}

			$result = $sourceFields['DEPARTMENT_NAME'];
		}

		return $result;
	}

	public function getPinnedDescription()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$sourceFields = $this->getSourceFields();
		if (empty($sourceFields))
		{
			return $result;
		}

		return Loc::getMessage('SONET_LIVEFEED_INTRANET_NEW_USER_PINNED_DESCRIPTION', [
			'#USER_NAME#' => $sourceFields['FULL_NAME']
		]);
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getLiveFeedUrl(): string
	{
		$pathToLogEntry = '';

		$logId = $this->getLogId();
		if ($logId)
		{
			$pathToLogEntry = Option::get('socialnetwork', 'log_entry_page', '', $this->getSiteId());
			if (!empty($pathToLogEntry))
			{
				$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, array("log_id" => $logId));
			}
		}

		return $pathToLogEntry;
	}
}