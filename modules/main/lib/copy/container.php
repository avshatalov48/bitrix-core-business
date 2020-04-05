<?php
namespace Bitrix\Main\Copy;

use Bitrix\Main\Type\Dictionary;

/**
 * Class stores a data that is needed in the process of copying the entity.
 *
 * @package Bitrix\Main\Copy
 */
class Container
{
	protected $entityId;
	protected $copiedEntityId;
	protected $parentId;
	protected $dictionary;

	public function __construct($entityId)
	{
		$this->entityId = (int) $entityId;

		$this->dictionary = new Dictionary();
	}

	/**
	 * Returns the id of the parent entity that is being copied.
	 *
	 * @return integer
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}

	/**
	 * Writes a copied entity id.
	 *
	 * @param integer $id A copied entity id.
	 */
	public function setCopiedEntityId($id)
	{
		$this->copiedEntityId = (int) $id;
	}

	/**
	 * Returns a copied entity id.
	 *
	 * @return integer|null
	 */
	public function getCopiedEntityId()
	{
		return $this->copiedEntityId;
	}

	/**
	 * Writes a parent id.
	 *
	 * @param integer $parentId A copied entity id.
	 */
	public function setParentId($parentId)
	{
		$this->parentId = (int) $parentId;
	}

	/**
	 * Returns a parent id.
	 *
	 * @return integer|null
	 */
	public function getParentId()
	{
		return $this->parentId;
	}

	/**
	 * Writes a dictionary.
	 *
	 * @param Dictionary $dictionary
	 */
	public function setDictionary(Dictionary $dictionary)
	{
		$this->dictionary = $dictionary;
	}

	/**
	 * Returns a dictionary.
	 * @return Dictionary
	 */
	public function getDictionary()
	{
		return $this->dictionary;
	}
}