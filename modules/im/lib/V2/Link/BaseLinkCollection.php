<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Collection;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Link;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\TariffLimit\DateFilterable;
use Bitrix\Im\V2\TariffLimit\FilterResult;
use Bitrix\Main\Type\DateTime;

/**
 * @extends Collection<BaseLinkItem>
 * @method BaseLinkItem offsetGet($key)
 */
abstract class BaseLinkCollection extends Collection implements LinkRestConvertible, DateFilterable
{
	/**
	 * @return string|LinkItem
	 */
	abstract public static function getCollectionElementClass(): string;

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([new Entity\User\UserPopupItem()], $excludedList);

		foreach ($this as $link)
		{
			$data->mergeFromEntity($link, $excludedList);
		}

		return $data;
	}

	public function filterByDate(DateTime $date): FilterResult
	{
		$filtered = $this->filter(static fn (BaseLinkItem $link) => $link->getDateCreate()?->getTimestamp() > $date->getTimestamp());

		return (new FilterResult())->setResult($filtered)->setFiltered($this->count() !== $filtered->count());
	}

	public function getRelatedChatId(): ?int
	{
		return $this->getAny()?->getChatId();
	}

	public static function getRestEntityName(): string
	{
		return 'list';
	}

	public function toRestFormatIdOnly(): array
	{
		$ids = [];

		foreach ($this as $item)
		{
			$ids[] = $item->toRestFormatIdOnly();
		}

		return $ids;
	}

	public function setMessageInfo(Message $message): Link
	{
		foreach ($this as $link)
		{
			$link->setMessageInfo($message);
		}

		return $this;
	}

	public function getEntityIds(): array
	{
		$ids = [];

		foreach ($this as $link)
		{
			$ids[] = $link->getEntityId();
		}

		return $ids;
	}

	/**
	 * @param Entity\EntityCollection $entities
	 * @param Message $message
	 * @return static
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function linkEntityToMessage(Entity\EntityCollection $entities, Message $message): self
	{
		$linkCollection = new static();

		foreach ($entities as $entity)
		{
			$linkItemClass = static::getCollectionElementClass();
			/** @var BaseLinkItem $linkItem */
			$linkItem = new $linkItemClass;
			$linkItem->setEntity($entity)->setMessageInfo($message);
			$linkCollection->add($linkItem);
		}

		return $linkCollection;
	}

	public function toRestFormat(array $option = []): array
	{
		$linkCollection = [];

		foreach ($this as $link)
		{
			$linkCollection[] = $link->toRestFormat($option);
		}

		return $linkCollection;
	}
}