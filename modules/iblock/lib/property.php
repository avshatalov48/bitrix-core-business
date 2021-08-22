<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock;

use Bitrix\Iblock\ORM\ElementEntity;
use Bitrix\Iblock\ORM\ElementV1Entity;
use Bitrix\Iblock\ORM\PropertyToField;
use Bitrix\Iblock\ORM\ValueStorageEntity;
use Bitrix\Iblock\ORM\ValueStorageTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\SystemException;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class Property extends EO_Property
{
	/** @var ValueStorageEntity */
	protected $valueEntity;

	/**
	 * Generates personal property entity
	 *
	 * @param ElementEntity $elementEntity
	 *
	 * @return Entity|null
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public function getValueEntity($elementEntity = null)
	{
		if ($this->valueEntity === null)
		{
			if ($elementEntity === null)
			{
				$elementEntity = IblockTable::compileEntity(
					IblockTable::getByPrimary($this->getIblockId(), [
						'select' => ['ID', 'API_CODE']
					])->fetchObject()
				);
			}

			/** @var DataManager $entityClassName */
			$entityClassName = 'IblockProperty'.$this->getId().'Table';
			$entityClass = '\\'.IblockTable::DATA_CLASS_NAMESPACE.'\\'.$entityClassName;

			if (class_exists($entityClass, false))
			{
				// already compiled
				return $this->valueEntity = $entityClass::getEntity();
			}

			$valueTableName = $this->getMultiple()
				? $elementEntity->getMultiValueTableName()
				: $elementEntity->getSingleValueTableName();

			if ($this->getVersion() == 1 || ($this->getVersion() == 2 && $this->getMultiple()))
			{
				switch ($this->getPropertyType())
				{
					case PropertyTable::TYPE_NUMBER:
					case PropertyTable::TYPE_SECTION:
					case PropertyTable::TYPE_ELEMENT:
					case PropertyTable::TYPE_FILE:
						$realValueColumnName = 'VALUE_NUM';
						break;

					case PropertyTable::TYPE_LIST:
						$realValueColumnName = 'VALUE_ENUM';
						break;

					case PropertyTable::TYPE_STRING:
					default:
						$realValueColumnName = 'VALUE';
				}

				$realDescriptionColumnName = 'DESCRIPTION';

				// fields for PropertyValue entity
				$fields = [
					(new IntegerField('ID'))
						->configurePrimary()
						->configureAutocomplete(),

					(new IntegerField('IBLOCK_ELEMENT_ID')),
					(new IntegerField('IBLOCK_PROPERTY_ID')),
				];
			}
			elseif ($this->getVersion() == 2 && !$this->getMultiple())
			{
				// single value
				$realValueColumnName = 'PROPERTY_'.$this->getId();
				$realDescriptionColumnName = 'DESCRIPTION_'.$this->getId();

				// fields for PropertyValue entity
				$fields = [
					(new IntegerField('IBLOCK_ELEMENT_ID'))
						->configurePrimary()
				];
			}
			else
			{
				throw new SystemException('Unknown property type');
			}

			// construct PropertyValue entity
			$this->valueEntity = Entity::compileEntity(
				$entityClassName,
				$fields,
				[
					'namespace' => IblockTable::DATA_CLASS_NAMESPACE,
					'table_name' => $valueTableName,
					'parent' => ValueStorageTable::class,
				]
			);

			// add value field
			PropertyToField::attachField($this, $this->valueEntity);

			// set real column name
			$this->valueEntity->getField('VALUE')->configureColumnName($realValueColumnName);

			// add generic value field
			if ($realValueColumnName !== 'VALUE'
				&& (
					$elementEntity instanceof ElementV1Entity // all v1 props
					|| $this->getMultiple() // multiple v2 props
				)
			)
			{
				$this->valueEntity->addField(
					(new StringField(ValueStorageTable::GENERIC_VALUE_FIELD_NAME))->configureColumnName('VALUE')
				);
			}

			// add description
			if ($this->getWithDescription())
			{
				$this->valueEntity->addField(
					(new StringField('DESCRIPTION'))
						->configureColumnName($realDescriptionColumnName)
				);
			}
		}

		return $this->valueEntity;
	}
}
