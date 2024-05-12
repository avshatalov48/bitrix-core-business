<?php
namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Internals\SharingLinkTable;
use Bitrix\Calendar\Sharing\Link\UserLink;
use Bitrix\Calendar\Sharing\Link\Factory;
use Bitrix\Calendar\Sharing\Link\UserLinkMapper;
use Bitrix\Calendar\Sharing\Link;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Sharing
{
	public const ERROR_CODE_100010 = 100010;
	public const ERROR_CODE_100020 = 100020;

	protected int $userId;

	protected const OPTION_SORT_JOINT_LINKS_BY_FREQUENT_USE = 'sortJointLinksByFrequentUse';
	protected const OPTION_SHARING_SETTINGS_COLLAPSED = 'sharingSettingsCollapsed';

	/**
	 * @param int $userId
	 */
	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	/**
	 * enabling sharing for user by creating public link for calendar
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function enable(): Result
	{
		$result = new Result();

		if(!$this->isEnabled())
		{
			Factory::getInstance()->createUserLink($this->userId);
		}
		else
		{
			$result->addError(new Error('Sharing is already enabled', 100010));
		}

		return $result;
	}

	/**
	 * disabling sharing for user by creating public link for sharing calendar
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function disable(): Result
	{
		$result = new Result();

		if ($this->isEnabled())
		{
			$userLinks = $this->getAllUserLinks();
			if (!empty($userLinks))
			{
				$userLinkMapper = new UserLinkMapper();
				foreach ($userLinks as $userLink)
				{
					$userLinkMapper->delete($userLink);
				}
			}
		}
		else
		{
			$result->addError(new Error('Sharing is already disabled', 100020));
		}

		return $result;
	}

	/**
	 * @param string|null $hash
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException|\Exception
	 */
	public function deactivateUserLink(?string $hash)
	{
		$result = new Result();

		$link = $this->getUserLinkByHash($hash);
		if (empty($link))
		{
			$result->addError(new Error('Link not found'));

			return $result;
		}

		$updateResult = SharingLinkTable::update((int)$link['ID'], [
			'ACTIVE' => 'N',
		]);
		if (!$updateResult->isSuccess())
		{
			$result->addError(new Error('Delete link error'));

			return $result;
		}

		return $result;
	}

	/**
	 * @param string|null $hash
	 * @return Result
	 * @throws \Exception
	 */
	public function increaseFrequentUse(?string $hash): Result
	{
		$result = new Result();

		$link = $this->getUserLinkByHash($hash);
		if (empty($link))
		{
			$result->addError(new Error('Link not found'));

			return $result;
		}

		$updateResult = SharingLinkTable::update((int)$link['ID'], [
			'FREQUENT_USE' => $link['FREQUENT_USE'] + 1,
		]);
		if (!$updateResult->isSuccess())
		{
			$result->addError(new Error('Update error'));

			return $result;
		}

		return $result;
	}

	/**
	 * @param array $memberIds
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function generateUserJointLink(array $memberIds): Result
	{
		$result = new Result();

		if (!$this->isEnabled())
		{
			$result->addError(new Error('Sharing is disabled', 100050));
		}

		if ($result->isSuccess())
		{
			/** @var UserLink $userJointLink */
			$userJointLink = Factory::getInstance()->createUserJointLink($this->userId, $memberIds);

			$linkArray = (new UserLinkMapper())->convertToArray($userJointLink);

			$result->setData([
				'url' => $linkArray['shortUrl'],
				'link' => $linkArray,
			]);

//			\CCalendarNotify::Send([
//				'mode' => \CCalendarNotify::NOTIFY_USERS_ADDED_TO_MULTI_LINK,
//				'userId' => $this->userId, //from
//				'guestIds' => $memberIds, //to
//				'params' => [
//					'url' => $url,
//					'linkId' => $userJointLink->getId(),
//				],
//			]);
		}

		return $result;
	}

	/**
	 * checks if user has an active public link for sharing calendar
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function isEnabled(): bool
	{
		return (bool)$this->getActiveLinkUrl();
	}

	public function getLinkInfo(): array
	{
		$linkRuleMapper = new Link\Rule\Mapper();
		$userLink = $this->getUserLink();
		if (is_null($userLink))
		{
			$linkObjectRule = new Link\Rule\UserRule($this->userId);
			$sharingRule = $linkRuleMapper->getFromLinkObjectRule($linkObjectRule);
			$sharingHash = null;
			$url = null;
		}
		else
		{
			$sharingRule = $userLink->getSharingRule();
			$sharingHash = $userLink->getHash();
			$url = Helper::getShortUrl($userLink->getUrl());
		}

		return [
			'url' => $url,
			'hash' => $sharingHash,
			'rule' => $linkRuleMapper->convertToArray($sharingRule),
		];
	}

	public function getUserInfo(): array
	{
		return [
			'id' => $this->userId,
			'name' => \CCalendar::GetUserName($this->userId),
			'avatar' => \CCalendar::GetUserAvatarSrc($this->userId),
		];
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAllUserLinkInfo(): array
	{
		$userLinks = $this->getUserJointLinks();
		$userLinkMapper = new UserLinkMapper();

		/** @var UserLink $userLink */
		return array_map(static function($userLink) use ($userLinkMapper) {
			return $userLinkMapper->convertToArray($userLink);
		}, $userLinks);
	}

	/**
	 * gets a short url for user's active public link for sharing calendar
	 *
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getActiveLinkShortUrl(): ?string
	{
		$url = $this->getActiveLinkUrl();
		if (!empty($url))
		{
			$url = Helper::getShortUrl($url);
		}

		return $url;
	}

	/**
	 * gets an url for user's active public link for sharing calendar
	 *
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getActiveLinkUrl(): ?string
	{
		$userLink = $this->getUserLink();
		return $userLink && $userLink->isActive() ? $userLink->getUrl() : null;
	}

	/**
	 * gets an active UserLink object
	 *
	 * @return UserLink|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getUserLink(): ?UserLink
	{
		return $this->getUserLinkByUserId($this->userId);
	}

	/**
	 * @return array
	 */
	public function getLinkSettings(): array
	{
		$settings = [];
		$linkInfo = $this->getLinkInfo();
		
		if (!empty($linkInfo))
		{
			$calendarSettings = \CCalendar::GetSettings();
			$settings = [
				'weekStart' => \CCalendar::GetWeekStart(),
				'workTimeStart' => $calendarSettings['work_time_start'],
				'workTimeEnd' => $calendarSettings['work_time_end'],
				'weekHolidays' => $calendarSettings['week_holidays'],
				'rule' => [
					'hash' => $linkInfo['hash'],
					'slotSize' => $linkInfo['rule']['slotSize'],
					'ranges' => $linkInfo['rule']['ranges'],
				],
			];
		}
		
		return $settings;
	}

	public function getOptions(): array
	{
		return [
			self::OPTION_SORT_JOINT_LINKS_BY_FREQUENT_USE => \CUserOptions::GetOption(
				'calendar',
				self::OPTION_SORT_JOINT_LINKS_BY_FREQUENT_USE,
				'Y',
				$this->userId,
			) === 'Y',
			self::OPTION_SHARING_SETTINGS_COLLAPSED => \CUserOptions::getOption(
				'calendar',
				self::OPTION_SHARING_SETTINGS_COLLAPSED,
				'N',
				$this->userId,
			) === 'Y',
		];
	}

	public function setSortJointLinksByFrequentUse(bool $sortByFrequentUse): void
	{
		\CUserOptions::SetOption(
			'calendar',
			self::OPTION_SORT_JOINT_LINKS_BY_FREQUENT_USE,
			$sortByFrequentUse ? 'Y' : 'N',
			false,
			$this->userId,
		);
	}

	public function setSharingSettingsCollapsed(bool $isCollapsed): void
	{
		\CUserOptions::SetOption(
			'calendar',
			self::OPTION_SHARING_SETTINGS_COLLAPSED,
			$isCollapsed ? 'Y' : 'N',
			false,
			$this->userId,
		);
	}

	/**
	 * @param int $userId
	 * @return UserLink|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getUserLinkByUserId(int $userId): ?UserLink
	{
		$userLinks = Factory::getInstance()->getUserLinks($userId);

		return !empty($userLinks) ? array_shift($userLinks) : null;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getAllUserLinks(): array
	{
		return Factory::getInstance()->getAllUserLinks($this->userId);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getUserJointLinks(): array
	{
		return Factory::getInstance()->getUserJointLinks($this->userId);
	}

	protected function getUserLinkByHash(?string $hash)
	{
		if (empty($hash))
		{
			return null;
		}

		return SharingLinkTable::query()
			->setSelect(['ID', 'HASH', 'OBJECT_ID', 'OBJECT_TYPE', 'ACTIVE', 'FREQUENT_USE'])
			->where('OBJECT_ID', $this->userId)
			->where('OBJECT_TYPE', Link\Helper::USER_SHARING_TYPE)
			->where('HASH', $hash)
			->where('ACTIVE', 'Y')
			->setLimit(1)
			->exec()->fetch()
		;
	}
}
