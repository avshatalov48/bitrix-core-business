<?php

namespace Bitrix\Im\V2\Link\Url;

use Bitrix\Im\Model\EO_LinkUrl;
use Bitrix\Im\Model\LinkUrlIndexTable;
use Bitrix\Im\Model\LinkUrlTable;
use Bitrix\Im\V2\Common\MigrationStatusCheckerTrait;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Im\V2\Result;

class UrlItem extends BaseLinkItem
{
	use MigrationStatusCheckerTrait;

	protected static string $migrationOptionName = 'im_link_url_migration';

	protected string $url;

	/**
	 * @param int|array|EO_LinkUrl|null $source
	 */
	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	public static function getEntityClassName(): string
	{
		return Entity\Url\UrlItem::class;
	}

	public function save(): Result
	{
		if (!static::isMigrationFinished())
		{
			return new Result();
		}

		$result = parent::save();
		LinkUrlIndexTable::indexInBackground();

		return $result;
	}

	public function delete(): Result
	{
		LinkUrlIndexTable::delete($this->getPrimaryId());

		return parent::delete();
	}

	public static function getDataClass(): string
	{
		return LinkUrlTable::class;
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'PREVIEW_URL_ID';
	}

	protected static function mirrorDataEntityFields(): array
	{
		$additionalFields = [
			'URL' => [
				'field' => 'url',
				'set' => 'setUrl', /** @see UrlItem::setUrl */
				'get' => 'getUrl', /** @see UrlItem::getUrl */
			]
		];

		return array_merge(parent::mirrorDataEntityFields(), $additionalFields);
	}

	//region Setters & getters

	/**
	 * @return Entity|\Bitrix\Im\V2\Entity\Url\UrlItem
	 */
	public function getEntity(): \Bitrix\Im\V2\Entity\Url\UrlItem
	{
		return $this->entity;
	}

	/**
	 * @param RestEntity|\Bitrix\Im\V2\Entity\Url\UrlItem $entity
	 * @return static
	 */
	public function setEntity(RestEntity $entity): self
	{
		if (!($entity instanceof \Bitrix\Im\V2\Entity\Url\UrlItem))
		{
			throw new ArgumentTypeException(get_class($entity));
		}
		$this->setUrl($entity->getUrl());

		return parent::setEntity($entity);
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$this->url = $url;
		return $this;
	}

	//endregion
}