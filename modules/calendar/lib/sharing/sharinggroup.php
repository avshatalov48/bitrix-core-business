<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Sharing\Link\Factory;
use Bitrix\Calendar\Sharing\Link\GroupLink;
use Bitrix\Calendar\Sharing\Link\GroupLinkMapper;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class SharingGroup
{
	private int $groupId;
	private int $userId;

	public function __construct(int $groupId, int $userId)
	{
		$this->groupId = $groupId;
		$this->userId = $userId;
	}

	public function enable(): Result
	{
		$result = new Result();

		if (!$this->isEnabled())
		{
			Factory::getInstance()->createGroupLink($this->groupId, $this->userId);
		}
		else
		{
			$result->addError(new Error('Sharing is already enabled', 100010));
		}

		return $result;
	}

	public function disable(): Result
	{
		$result = new Result();

		if ($this->isEnabled())
		{
			$groupLinks = $this->getAllGroupLinks();
			if (!empty($groupLinks))
			{
				$groupLinkMapper = new GroupLinkMapper();
				foreach ($groupLinks as $groupLink)
				{
					$groupLinkMapper->delete($groupLink);
				}
			}
		}
		else
		{
			$result->addError(new Error('Sharing is already disabled', 100020));
		}

		return $result;
	}

	public function isEnabled(): bool
	{
		return (bool)$this->getActiveLinkUrl();
	}

	public function getActiveLinkUrl(): ?string
	{
		$groupLink = $this->getGroupLink();

		return $groupLink && $groupLink->isActive() ? $groupLink->getUrl() : null;
	}

	public function getGroupLink(): ?GroupLink
	{
		return $this->getGroupLinkByGroupId($this->groupId, $this->userId);
	}

	public function getLinkInfo(): array
	{
		$linkRuleMapper = new Link\Rule\Mapper();
		$groupLink = $this->getGroupLink();
		if (is_null($groupLink))
		{
			$linkObjectRule = new Link\Rule\GroupRule($this->groupId);
			$sharingRule = $linkRuleMapper->getFromLinkObjectRule($linkObjectRule);
			$sharingHash = null;
			$url = null;
		}
		else
		{
			$sharingRule = $groupLink->getSharingRule();
			$sharingHash = $groupLink->getHash();
			$url = Helper::getShortUrl($groupLink->getUrl());
		}

		return [
			'url' => $url,
			'hash' => $sharingHash,
			'rule' => $linkRuleMapper->convertToArray($sharingRule),
		];
	}

	public function generateGroupJointLink(array $memberIds): Result
	{
		$result = new Result();

		if (!$this->isEnabled())
		{
			$result->addError(new Error('Sharing is disabled', 100050));
		}

		if ($result->isSuccess())
		{
			/** @var GroupLink $groupJointLink */
			$groupJointLink = Factory::getInstance()->createGroupJointLink($this->groupId, $memberIds);

			$linkArray = (new GroupLinkMapper())->convertToArray($groupJointLink);

			$result->setData([
				'url' => $linkArray['shortUrl'],
				'link' => $linkArray,
			]);
		}

		return $result;
	}

	protected function getAllGroupLinks(): array
	{
		return Factory::getInstance()->getGroupLinks($this->groupId, $this->userId);
	}

	protected function getGroupLinkByGroupId(int $groupId, int $userId): ?GroupLink
	{
		$groupLinks = Factory::getInstance()->getGroupLinks($groupId, $userId);

		return !empty($groupLinks) ? array_shift($groupLinks) : null;
	}
}
