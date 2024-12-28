<?php

namespace Bitrix\Im\V2\Chat\Collab;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Socialnetwork\Collab\Link\LinkManager;
use Bitrix\Socialnetwork\Collab\Link\LinkParts;
use Bitrix\Socialnetwork\Collab\Link\LinkType;

abstract class Entity implements RestConvertible
{
	use ContextCustomer;

	protected int $groupId;
	protected LinkManager $linkManager;
	protected LinkParts $linkParts;

	/**
	 * @throws LoaderException
	 */
	public function __construct(int $groupId)
	{
		Loader::requireModule('socialnetwork');
		$this->groupId = $groupId;
		$this->linkManager = LinkManager::getInstance();
		$this->linkParts = new LinkParts($this->getContext()->getUserId(), $groupId);
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'counter' => $this->getCounter(),
			'url' => $this->getUrl(),
		];
	}

	public function getCounter(): int
	{
		if (!static::isAvailable())
		{
			return 0;
		}

		return $this->getCounterInternal();
	}

	public function getUrl(): string
	{
		if (!static::isAvailable())
		{
			return '';
		}

		return $this->getUrlInternal();
	}

	public function getUrlInternal(): string
	{
		return '/' . $this->linkManager->get($this->getLinkType(), $this->linkParts);
	}

	abstract protected function getLinkType(): LinkType;

	abstract public function getCounterInternal(): int;

	abstract public static function isAvailable(): bool;
}