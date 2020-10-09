<?php

namespace Bitrix\Main\UserField\Internal;

/**
 * @deprecated
 */
abstract class TypeFactory
{
	protected $itemEntities = [];

	/**
	 * @return TypeDataManager
	 */
	abstract public function getTypeDataClass(): string;

	/**
	 * @return PrototypeItemDataManager
	 */
	abstract public function getItemPrototypeDataClass(): string;

	abstract public function getCode(): string;

	/**
	 * @param mixed $type
	 * @return PrototypeItemDataManager
	 */
	public function getItemDataClass($type): string
	{
		return $this->getItemEntity($type)->getDataClass();
	}

	public function getItemEntity($type): \Bitrix\Main\ORM\Entity
	{
		$typeData = $this->getTypeDataClass()::resolveType($type);
		if(!empty($typeData) && isset($this->itemEntities[$typeData['ID']]))
		{
			return $this->itemEntities[$typeData['ID']];
		}

		$entity = $this->getTypeDataClass()::compileEntity($type);
		if($entity)
		{
			$this->itemEntities[$typeData['ID']] = $entity;
		}

		return $entity;
	}

	/**
	 * @return Item
	 */
	public function getItemParentClass(): string
	{
		return Item::class;
	}

	public function getUserFieldEntityPrefix(): string
	{
		$code = $this->getCode();
		return static::getPrefixByCode($code).'_';
	}

	public function getUserFieldEntityId(int $typeId): string
	{
		return $this->getUserFieldEntityPrefix().$typeId;
	}

	public static function getCodeByPrefix(string $prefix): string
	{
		return mb_strtolower($prefix);
	}

	public static function getPrefixByCode(string $code): string
	{
		return mb_strtoupper($code);
	}
}