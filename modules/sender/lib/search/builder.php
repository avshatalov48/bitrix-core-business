<?php

namespace Bitrix\Sender\Search;

use Bitrix\Main\Entity;

/**
 * Class Builder
 * @package Bitrix\Sender\Search
 */
class Builder
{
	/** @var  Entity\Base $entity Entity. */
	private $entity;

	/** @var  Content $content Content. */
	private $content;

	/** @var string $fieldName Field name. */
	private $fieldName;

	/**
	 * Filter constructor.
	 *
	 * @param Entity\Base $entity Entity.
	 * @param string|null $fieldName Field name.
	 */
	public function __construct(Entity\Base $entity, $fieldName)
	{
		$this->entity = $entity;
		$this->fieldName = $fieldName;
	}

	/**
	 * Is full text index enabled.
	 *
	 * @return bool
	 */
	public function isFullTextIndexEnabled()
	{
		return $this->entity->fullTextIndexEnabled($this->fieldName);
	}

	/**
	 * Return true if entity has field.
	 *
	 * @return bool
	 */
	public function hasField()
	{
		return $this->entity->hasField($this->fieldName);
	}

	/**
	 * Get content.
	 *
	 * @return Content
	 */
	public function getContent()
	{
		if (!$this->content)
		{
			$this->content = new Content;
		}

		return $this->content;
	}

	/**
	 * Return true if ca save.
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->hasField() && $this->isFullTextIndexEnabled();
	}

	/**
	 * Save.
	 *
	 * @param int $entityId Entity ID.
	 * @return bool
	 */
	public function save($entityId)
	{
		$dataClass = $this->entity->getDataClass();
		return $dataClass::update(
			$entityId,
			array($this->fieldName => $this->content->getString())
		)->isSuccess();
	}

	/**
	 * Apply filter.
	 *
	 * @param array &$filter Filter.
	 * @param string|null $searchString Search string.
	 * @return bool
	 */
	public function applyFilter(array &$filter, $searchString)
	{
		if (!$searchString)
		{
			return false;
		}
		if (!$this->hasField())
		{
			return false;
		}

		$isFullTextEnabled = $this->isFullTextIndexEnabled();
		$operation = $isFullTextEnabled ? '*' : '*%';
		$filter["{$operation}{$this->fieldName}"] = Content::encodeText($searchString);

		return true;
	}
}