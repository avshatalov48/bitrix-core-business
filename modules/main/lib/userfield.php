<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Entity representation of UserFields.
 * @package bitrix
 * @subpackage main
 */
class UserFieldTable extends ORM\Data\DataManager
{
	// to use in uts serialized fields
	const MULTIPLE_DATE_FORMAT = 'Y-m-d';
	const MULTIPLE_DATETIME_FORMAT = 'Y-m-d H:i:s';

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'string'
			),
			'FIELD_NAME' => array(
				'data_type' => 'string'
			),
			'USER_TYPE_ID' => array(
				'data_type' => 'string'
			),
			'XML_ID' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'MULTIPLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'MANDATORY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'SHOW_FILTER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'SHOW_IN_LIST' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'EDIT_IN_LIST' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'IS_SEARCHABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'SETTINGS' => array(
				'data_type' => 'text',
				'serialized' => true
			)
		);
	}

	/**
	 * @param array $data
	 *
	 * @return \Bitrix\Main\ORM\Data\AddResult|void
	 * @throws NotImplementedException
	 */
	public static function add(array $data)
	{
		throw new NotImplementedException('Use \CUserTypeEntity API instead.');
	}

	/**
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return \Bitrix\Main\ORM\Data\UpdateResult|void
	 * @throws NotImplementedException
	 */
	public static function update($primary, array $data)
	{
		throw new NotImplementedException('Use \CUserTypeEntity API instead.');
	}

	/**
	 * @param mixed $primary
	 *
	 * @return ORM\Data\DeleteResult|void
	 * @throws NotImplementedException
	 */
	public static function delete($primary)
	{
		throw new NotImplementedException('Use \CUserTypeEntity API instead.');
	}

	/**
	 * @param ORM\Entity $entity
	 * @param          $ufId
	 *
	 * @throws ArgumentException
	 */
	public static function attachFields(ORM\Entity $entity, $ufId)
	{
		global $USER_FIELD_MANAGER;

		$utsFields = array();
		$utsFieldNames = array();

		$utmFields = array();
		$utmFieldNames = array();

		$fields = $USER_FIELD_MANAGER->getUserFields($ufId);

		foreach ($fields as $field)
		{
			if ($field['MULTIPLE'] === 'Y')
			{
				$utmFields[] = $field;
				$utmFieldNames[$field['FIELD_NAME']] = true;
			}
			else
			{
				$utsFields[] = $field;
				$utsFieldNames[$field['FIELD_NAME']] = true;
			}
		}

		if (!empty($utsFields) || !empty($utmFields))
		{
			// create uts entity & put fields into it
			$utsEntity = static::createUtsEntity($entity, $utsFields, $utmFields);

			// create reference to uts entity
			$utsReference = new ORM\Fields\Relations\Reference('UTS_OBJECT', $utsEntity->getDataClass(), array(
				'=this.ID' => 'ref.VALUE_ID'
			));

			$entity->addField($utsReference);

			// add UF_* aliases
			foreach ($fields as $userfield)
			{
				$utsFieldName = $userfield['FIELD_NAME'];

				/** @var \Bitrix\Main\ORM\Fields\ScalarField $utsField */
				$utsField = $utsEntity->getField($utsFieldName);

				$aliasField = new ORM\Fields\UserTypeField(
					$utsFieldName,
					'%s',
					'UTS_OBJECT.'.$utsFieldName,
					array('data_type' => get_class($utsField))
				);

				if ($userfield['MULTIPLE'] == 'Y')
				{
					$aliasField->configureMultiple();
					static::setMultipleFieldSerialization($aliasField, $userfield);
				}

				$entity->addField($aliasField);
			}


			if (!empty($utsFields))
			{
				foreach ($utsFields as $utsField)
				{
					/** @var \Bitrix\Main\ORM\Fields\ScalarField $utsEntityField */
					$utsEntityField = $utsEntity->getField($utsField['FIELD_NAME']);

					foreach ($USER_FIELD_MANAGER->getEntityReferences($utsField, $utsEntityField) as $reference)
					{
						// rewrite reference from this.field to this.uts_object.field
						$referenceDesc = static::rewriteUtsReference($reference->getReference());

						$aliasReference = new ORM\Fields\Relations\Reference(
							$reference->getName(),
							$reference->getRefEntityName(),
							$referenceDesc
						);

						$entity->addField($aliasReference);
					}
				}
			}

			if (!empty($utmFields))
			{
				// create utm entity & put base fields into it
				$utmEntity = static::createUtmEntity($entity, $utmFields);

				// add UF_* aliases
				foreach ($utmFieldNames as $utmFieldName => $true)
				{
					/** @var \Bitrix\Main\ORM\Fields\ScalarField $utmField */
					$utmField = $utmEntity->getField($utmFieldName);

					$aliasField = new ORM\Fields\ExpressionField(
						$utmFieldName.'_SINGLE',
						'%s',
						$utmEntity->getFullName().':PARENT_'.$utmFieldName.'.'.$utmField->getColumnName(),
						array('data_type' => get_class($utmField))
					);

					$entity->addField($aliasField);
				}
			}
		}
	}

	/**
	 * @param ORM\Entity $srcEntity
	 * @param array      $utsFields
	 * @param array      $utmFields
	 *
	 * @return ORM\Entity
	 * @throws ArgumentException
	 */
	protected static function createUtsEntity(ORM\Entity $srcEntity, array $utsFields, array $utmFields)
	{
		global $USER_FIELD_MANAGER;

		// get namespace & class
		/** @var \Bitrix\Main\ORM\Data\DataManager $utsClassFull */
		$utsClassFull = static::getUtsEntityClassNameBySrcEntity($srcEntity);
		$utsClassPath = explode('\\', ltrim($utsClassFull, '\\'));

		$utsNamespace = join('\\', array_slice($utsClassPath, 0, -1));
		$utsClass = end($utsClassPath);

		// get table name
		$utsTable = static::getUtsEntityTableNameBySrcEntity($srcEntity);

		// base fields
		$fieldsMap = array(
			'VALUE_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'PARENT' => array(
				'data_type' => $srcEntity->getDataClass(),
				'reference' => array(
					'=this.VALUE_ID' => 'ref.ID'
				)
			)
		);

		// initialize entity
		if (class_exists($utsNamespace."\\".$utsClass))
		{
			ORM\Entity::destroy($utsNamespace."\\".$utsClass);
			$entity = ORM\Entity::getInstance($utsNamespace."\\".$utsClass);

			foreach ($fieldsMap as $fieldName => $field)
			{
				$entity->addField($field, $fieldName);
			}
		}
		else
		{
			$entity = ORM\Entity::compileEntity($utsClass, $fieldsMap, array(
				'namespace' => $utsNamespace, 'table_name' => $utsTable
			));
		}

		foreach ($utsFields as $utsField)
		{
			$field = $USER_FIELD_MANAGER->getEntityField($utsField);
			$entity->addField($field);

			foreach ($USER_FIELD_MANAGER->getEntityReferences($utsField, $field) as $reference)
			{
				$entity->addField($reference);
			}
		}

		foreach ($utmFields as $utmField)
		{
			// add serialized utm cache-fields
			$cacheField = new ORM\Fields\TextField($utmField['FIELD_NAME']);
			static::setMultipleFieldSerialization($cacheField, $utmField);
			$entity->addField($cacheField);
		}

		return $entity;
	}

	/**
	 * @param ORM\Fields\Field       $entityField
	 * @param ORM\Fields\Field|array $fieldAsType
	 *
	 * @throws ArgumentException
	 */
	public static function setMultipleFieldSerialization(ORM\Fields\Field $entityField, $fieldAsType)
	{
		global $USER_FIELD_MANAGER;

		if (!($fieldAsType instanceof ORM\Fields\Field))
		{
			$fieldAsType = $USER_FIELD_MANAGER->getEntityField($fieldAsType);
		}

		if ($fieldAsType instanceof ORM\Fields\DatetimeField)
		{
			$entityField->addSaveDataModifier(array(__CLASS__, 'serializeMultipleDatetime'));
			$entityField->addFetchDataModifier(array(__CLASS__, 'unserializeMultipleDatetime'));
		}
		elseif ($fieldAsType instanceof ORM\Fields\DateField)
		{
			$entityField->addSaveDataModifier(array(__CLASS__, 'serializeMultipleDate'));
			$entityField->addFetchDataModifier(array(__CLASS__, 'unserializeMultipleDate'));
		}
		else
		{
			$entityField->setSerialized();
		}
	}

	public static function rewriteUtsReference($referenceDesc)
	{
		$new = array();

		foreach ($referenceDesc as $k => $v)
		{
			if (is_array($v))
			{
				$new[$k] = static::rewriteUtsReference($v);
			}
			else
			{
				$k = str_replace('this.', 'this.UTS_OBJECT.', $k);
				$new[$k] = $v;
			}
		}

		return $new;
	}

	protected static function getUtsEntityClassNameBySrcEntity(ORM\Entity $srcEntity)
	{
		return $srcEntity->getFullName().'UtsTable';
	}

	protected static function getUtsEntityTableNameBySrcEntity(ORM\Entity $srcEntity)
	{
		return 'b_uts_'.strtolower($srcEntity->getUfId());
	}

	/**
	 * @param ORM\Entity $srcEntity
	 * @param array      $utmFields
	 *
	 * @return ORM\Entity
	 * @throws ArgumentException
	 */
	protected static function createUtmEntity(ORM\Entity $srcEntity, array $utmFields)
	{
		global $USER_FIELD_MANAGER;

		/** @var \Bitrix\Main\ORM\Data\DataManager $utmClassFull */
		$utmClassFull = static::getUtmEntityClassNameBySrcEntity($srcEntity);
		$utmClassPath = explode('\\', ltrim($utmClassFull, '\\'));

		$utmNamespace = join('\\', array_slice($utmClassPath, 0, -1));
		$utmClass = end($utmClassPath);

		// get table name
		$utmTable = static::getUtmEntityTableNameBySrcEntity($srcEntity);

		// collect fields
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'VALUE_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'PARENT' => array(
				'data_type' => $srcEntity->getDataClass(),
				'reference' => array(
					'=this.VALUE_ID' => 'ref.ID'
				)
			),
			'FIELD_ID' => array(
				'data_type' => 'integer'
			),

			// base values fields
			'VALUE' => array(
				'data_type' => 'text'
			),
			'VALUE_INT' => array(
				'data_type' => 'integer'
			),
			'VALUE_DOUBLE' => array(
				'data_type' => 'float'
			),
			'VALUE_DATE' => array(
				'data_type' => 'datetime'
			)
		);

		// initialize entity
		if (class_exists($utmNamespace."\\".$utmClass))
		{
			ORM\Entity::destroy($utmNamespace."\\".$utmClass);
			$entity = ORM\Entity::getInstance($utmNamespace."\\".$utmClass);

			foreach ($fieldsMap as $fieldName => $field)
			{
				$entity->addField($field, $fieldName);
			}
		}
		else
		{
			$entity = ORM\Entity::compileEntity($utmClass, $fieldsMap, array(
				'namespace' => $utmNamespace, 'table_name' => $utmTable
			));
		}

		// add utm fields being mapped on real column name
		foreach ($utmFields as $utmField)
		{
			$field = $USER_FIELD_MANAGER->getEntityField($utmField);

			if ($field instanceof ORM\Fields\IntegerField)
			{
				$columnName = 'VALUE_INT';
			}
			elseif ($field instanceof ORM\Fields\FloatField)
			{
				$columnName = 'VALUE_DOUBLE';
			}
			elseif ($field instanceof ORM\Fields\DateField || $field instanceof ORM\Fields\DatetimeField)
			{
				$columnName = 'VALUE_DATE';
			}
			else
			{
				$columnName = 'VALUE';
			}

			$field->setColumnName($columnName);

			$entity->addField($field);

			foreach ($USER_FIELD_MANAGER->getEntityReferences($utmField, $field) as $reference)
			{
				$entity->addField($reference);
			}

			// add back-reference
			$refField = new ORM\Fields\Relations\Reference(
				'PARENT_'.$utmField['FIELD_NAME'],
				$srcEntity->getDataClass(),
				array('=this.VALUE_ID' => 'ref.ID', '=this.FIELD_ID' => array('?i', $utmField['ID']))
			);

			$entity->addField($refField);
		}

		return $entity;
	}

	protected static function getUtmEntityClassNameBySrcEntity(ORM\Entity $srcEntity)
	{
		return $srcEntity->getFullName().'UtmTable';
	}

	protected static function getUtmEntityTableNameBySrcEntity(ORM\Entity $srcEntity)
	{
		return 'b_utm_'.strtolower($srcEntity->getUfId());
	}

	/**
	 * @param Type\DateTime[] $value
	 *
	 * @return string
	 */
	public static function serializeMultipleDatetime($value)
	{
		if (is_array($value) || $value instanceof \Traversable)
		{
			$tmpValue = array();

			foreach ($value as $k => $singleValue)
			{
				/** @var Type\DateTime $singleValue */
				$tmpValue[$k] = $singleValue->format(static::MULTIPLE_DATETIME_FORMAT);
			}

			return serialize($tmpValue);
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 * @throws ObjectException
	 */
	public static function unserializeMultipleDatetime($value)
	{
		if (strlen($value))
		{
			$value = unserialize($value);

			foreach ($value as &$singleValue)
			{
				try
				{
					//try new independent datetime format
					$singleValue = new Type\DateTime($singleValue, static::MULTIPLE_DATETIME_FORMAT);
				}
				catch (ObjectException $e)
				{
					//try site format
					$singleValue = new Type\DateTime($singleValue);
				}
			}
		}

		return $value;
	}

	/**
	 * @param Type\Date[] $value
	 *
	 * @return string
	 */
	public static function serializeMultipleDate($value)
	{
		if (is_array($value) || $value instanceof \Traversable)
		{
			$tmpValue = array();

			foreach ($value as $k => $singleValue)
			{
				/** @var Type\Date $singleValue */
				$tmpValue[$k] = $singleValue->format(static::MULTIPLE_DATE_FORMAT);
			}

			return serialize($tmpValue);
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 * @throws ObjectException
	 */
	public static function unserializeMultipleDate($value)
	{
		if (strlen($value))
		{
			$value = unserialize($value);

			foreach ($value as &$singleValue)
			{
				try
				{
					//try new independent datetime format
					$singleValue = new Type\Date($singleValue, static::MULTIPLE_DATE_FORMAT);
				}
				catch (ObjectException $e)
				{
					//try site format
					$singleValue = new Type\Date($singleValue);
				}
			}
		}

		return $value;
	}
}
