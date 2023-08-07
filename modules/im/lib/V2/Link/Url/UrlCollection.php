<?php

namespace Bitrix\Im\V2\Link\Url;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Im\Model\LinkUrlTable;
use Bitrix\Im\Model\LinkUrlIndexTable;
use Bitrix\Im\V2\Common\MigrationStatusCheckerTrait;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Result;

/**
 * @implements \IteratorAggregate<int,UrlItem>
 * @method UrlItem offsetGet($key)
 */
class UrlCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;
	use MigrationStatusCheckerTrait;

	protected static string $migrationOptionName = 'im_link_url_migration';

	public static function getCollectionElementClass(): string
	{
		return UrlItem::class;
	}

	public function save(bool $isGroupSave = false): Result
	{
		if (!static::isMigrationFinished())
		{
			return (new Result)->addError(new Entity\Url\UrlError(Entity\Url\UrlError::SAVE_BEFORE_MIGRATION_ERROR));
		}

		$saveResult = parent::save($isGroupSave);
		LinkUrlIndexTable::indexInBackground();

		return $saveResult;
	}

	public static function initByMessage(Message $message): self
	{
		$entities = \Bitrix\Im\V2\Entity\Url\UrlCollection::initByMessage($message);

		return static::linkEntityToMessage($entities, $message);
	}

	public static function find(
		array $filter,
		array $order = ['MESSAGE_ID' => 'DESC'],
		?int $limit = null,
		?Context $context = null,
		?int $offset = null
	): self
	{
		$urlOrder = ['MESSAGE_ID' => 'DESC'];

		if (isset($order['MESSAGE_ID']))
		{
			$urlOrder['MESSAGE_ID'] = $order['MESSAGE_ID'];
		}

		$query = LinkUrlTable::query()
			->setSelect(['ID', 'URL', 'DATE_CREATE', 'MESSAGE_ID', 'CHAT_ID', 'PREVIEW_URL_ID', 'AUTHOR_ID'])
			->setOrder($urlOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		if (isset($offset))
		{
			$query->setOffset($offset);
		}
		static::processFilters($query, $filter, $urlOrder);

		$urlCollection = new static($query->fetchCollection());

		return $urlCollection->fillMetadata();
	}

	public function fillMetadata(bool $withHtml = true): self
	{
		$previewUrlsIds = $this->getEntityIds();

		$entities = \Bitrix\Im\V2\Entity\Url\UrlCollection::initByPreviewUrlsIds($previewUrlsIds, $withHtml);

		foreach ($this as $url)
		{
			if ($entities->getById($url->getEntityId()) !== null)
			{
				$url->setEntity($entities->getById($url->getEntityId()));
			}
			else
			{
				$url->setEntity((new Entity\Url\UrlItem())->setUrl($url->getUrl()));
			}
		}

		return $this;
	}

	public static function deleteByMessagesIds(array $messagesIds): void
	{
		LinkUrlTable::deleteByFilter(['=MESSAGE_ID' => $messagesIds]);
	}

	public static function deleteByChatsIds(array $chatsIds): void
	{
		LinkUrlTable::deleteByFilter(['=CHAT_ID' => $chatsIds]);
	}

	public static function deleteByAuthorsIds(array $authorsIds): void
	{
		LinkUrlTable::deleteByFilter(['=AUTHOR_ID' => $authorsIds]);
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		static::processSidebarFilters($query, $filter, $order);

		if (isset($filter['SEARCH_URL']))
		{
			$query->withSearchByUrl($filter['SEARCH_URL']);
		}
	}
}