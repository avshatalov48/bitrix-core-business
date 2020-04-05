<?php
namespace Bitrix\Sale\Archive\Recovery;

use \Bitrix\Main\Result,
	\Bitrix\Sale\Archive;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
abstract class Builder implements Buildable, ArchivePackable
{
	/** @var Archive\Order $order*/
	protected $order = null;
	/** @var PackedField $packedOrder*/
	protected $packedOrder = null;
	protected $entitiesFields = [];
	protected $packedBasketItems = [];

	public function setPackedOrder(PackedField $field)
	{
		$this->packedOrder = $field;
	}

	public function addPackedBasketItem($id, PackedField $field)
	{
		$this->packedBasketItems[$id] = $field;
	}

	public function tryUnpack()
	{
		return new Result();
	}

	public function setEntityFields($name, array $value = null)
	{
		if (!is_array($this->entitiesFields[$name]))
		{
			$this->entitiesFields[$name] = [];
		}
		$this->entitiesFields[$name] = $value;
	}

	abstract public function buildOrder();
}	