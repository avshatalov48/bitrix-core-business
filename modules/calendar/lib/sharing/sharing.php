<?php
namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Sharing\Link\UserLink;
use Bitrix\Calendar\Sharing\Link\Factory;
use Bitrix\Calendar\Sharing\Link\UserLinkMapper;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Sharing
{
	public const DEFAULT_SHARING_SLOT_LENGTH_IN_MINUTES = 60;
	public const ERROR_CODE_100010 = 100010;
	public const ERROR_CODE_100020 = 100020;

	protected int $userId;

	/**
	 * @param int $userId
	 */
	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	/**
	 * enabling sharing for user by creating public link for calendar
	 * @return Result
	 */
	public function enable(): Result
	{
		$result = new Result();

		if(!$this->isEnabled())
		{
			$sharingLinkFactory = new Factory();
			$sharingLinkFactory->createUserLink($this->userId);
		}
		else
		{
			$result->addError(new Error('Sharing is already enabled', 100010));
		}

		return $result;
	}

	/**
	 * disabling sharing for user by creating public link for sharing calendar
	 * @return Result
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
	 * checks if user has an active public link for sharing calendar
	 * @return bool
	 */
	public function isEnabled(): bool
	{
		return (bool)$this->getActiveLinkUrl();
	}

	/**
	 * gets a short url for user's active public link for sharing calendar
	 * @return string|null
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
	 * @return string|null
	 */
	public function getActiveLinkUrl(): ?string
	{
		$userLink = $this->getUserLink();
		return $userLink && $userLink->isActive() ? $userLink->getUrl(): null;
	}

	/**
	 * gets an active UserLink object
	 * @return UserLink|null
	 */
	public function getUserLink(): ?UserLink
	{
		return $this->getUserLinkByUserId($this->userId);
	}

	/**
	 * @param int $userId
	 * @return UserLink|null
	 */
	protected function getUserLinkByUserId(int $userId): ?UserLink
	{
		$userLinks = $this->getAllUserLinksByUserId($userId);

		return !empty($userLinks) ? array_shift($userLinks) : null;
	}

	/**
	 * @return array
	 */
	protected function getAllUserLinks(): array
	{
		return $this->getAllUserLinksByUserId($this->userId);
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	protected function getAllUserLinksByUserId(int $userId): array
	{
		$sharingLinkFactory = new Factory();

		return $sharingLinkFactory->getUserLinks($userId);
	}
}
