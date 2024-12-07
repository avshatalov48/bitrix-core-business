<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage highloadblock
 * @copyright  2001-2014 1C-Bitrix
 */

namespace Bitrix\Highloadblock;

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\DB\MssqlConnection,
	Bitrix\Main\Entity;

Main\Localization\Loc::loadLanguageFile(__FILE__);

/**
 * Class description
 * @package    bitrix
 * @subpackage highloadblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_HighloadBlock_Query query()
 * @method static EO_HighloadBlock_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_HighloadBlock_Result getById($id)
 * @method static EO_HighloadBlock_Result getList(array $parameters = [])
 * @method static EO_HighloadBlock_Entity getEntity()
 * @method static \Bitrix\Highloadblock\HighloadBlock createObject($setDefaultValues = true)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlock_Collection createCollection()
 * @method static \Bitrix\Highloadblock\HighloadBlock wakeUpObject($row)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlock_Collection wakeUpCollection($rows)
 */
class HighloadBlockTable extends Entity\DataManager
{
	private const ENTITY_ID_PREFIX = 'HLBLOCK_';

	private const ENTITY_ID_MASK = '/^HLBLOCK_(\d+)$/';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_hlblock_entity';
	}

	public static function getObjectClass()
	{
		return HighloadBlock::class;
	}

	public static function getMap()
	{
		IncludeModuleLangFile(__FILE__);

		$sqlHelper = Application::getConnection()->getSqlHelper();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName')
			),
			'TABLE_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTableName')
			),
			'FIELDS_COUNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'(SELECT COUNT(ID) FROM b_user_field WHERE b_user_field.ENTITY_ID = '.
						$sqlHelper->getConcatFunction("'".self::ENTITY_ID_PREFIX."'", $sqlHelper->castToChar('%s')).')',
					'ID'
				)
			),
			'LANG' => new Entity\ReferenceField(
				'LANG',
				'Bitrix\Highloadblock\HighloadBlockLangTable',
				array('=this.ID' => 'ref.ID', 'ref.LID' => new Main\DB\SqlExpression('?', LANGUAGE_ID))
			),
		);

		return $fieldsMap;
	}

	/**
	 * @param array $data
	 *
	 * @return Entity\AddResult
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function add(array $data)
	{
		$result = parent::add($data);

		if (!$result->isSuccess(true))
		{
			return $result;
		}

		// create table in db
		$connection = Application::getConnection();
		$dbtype = $connection->getType();
		$sqlHelper = $connection->getSqlHelper();

		if ($dbtype == 'mysql')
		{
			$connection->query('
				CREATE TABLE '.$sqlHelper->quote($data['TABLE_NAME']).' (ID int(11) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID))
			');
		}
		elseif ($dbtype == 'pgsql')
		{
			$connection->query('
				CREATE TABLE '.$sqlHelper->quote($data['TABLE_NAME']).' (ID serial NOT NULL, PRIMARY KEY (ID))
			');
		}
		elseif ($dbtype == 'mssql')
		{
			$connection->query('
				CREATE TABLE '.$sqlHelper->quote($data['TABLE_NAME']).' (ID int NOT NULL IDENTITY (1, 1),
				CONSTRAINT '.$data['TABLE_NAME'].'_ibpk_1 PRIMARY KEY (ID))
			');
		}
		elseif ($dbtype == 'oracle')
		{
			$connection->query('
				CREATE TABLE '.$sqlHelper->quote($data['TABLE_NAME']).' (ID number(11) NOT NULL, PRIMARY KEY (ID))
			');

			$connection->query('
				CREATE SEQUENCE sq_'.$data['TABLE_NAME'].'
			');

			$connection->query('
				CREATE OR REPLACE TRIGGER '.$data['TABLE_NAME'].'_insert
					BEFORE INSERT
					ON '.$sqlHelper->quote($data['TABLE_NAME']).'
					FOR EACH ROW
						BEGIN
						IF :NEW.ID IS NULL THEN
							SELECT sq_'.$data['TABLE_NAME'].'.NEXTVAL INTO :NEW.ID FROM dual;
						END IF;
					END;
			');
		}
		else
		{
			throw new Main\SystemException('Unknown DB type');
		}

		return $result;
	}

	/**
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return Entity\UpdateResult
	 */
	public static function update($primary, array $data)
	{
		global $USER_FIELD_MANAGER;

		// get old data
		$oldData = static::getByPrimary($primary)->fetch();

		// update row
		$result = parent::update($primary, $data);

		if (!$result->isSuccess(true))
		{
			return $result;
		}

		// rename table in db
		if (isset($data['TABLE_NAME']) && $data['TABLE_NAME'] !== $oldData['TABLE_NAME'])
		{
			$connection = Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$connection->renameTable($oldData['TABLE_NAME'], $data['TABLE_NAME']);

			if ($connection instanceof MssqlConnection)
			{
				// rename constraint
				$connection->query(sprintf(
					"EXEC sp_rename %s, %s, 'OBJECT'",
					$sqlHelper->quote($oldData['TABLE_NAME'].'_ibpk_1'),
					$sqlHelper->quote($data['TABLE_NAME'].'_ibpk_1')
				));
			}

			// rename also uf multiple tables and its constraints, sequences, and triggers
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			foreach ($USER_FIELD_MANAGER->getUserFields(static::compileEntityId($oldData['ID'])) as $field)
			{
				if ($field['MULTIPLE'] == 'Y')
				{
					$oldUtmTableName = static::getMultipleValueTableName($oldData, $field);
					$newUtmTableName = static::getMultipleValueTableName($data, $field);

					$connection->renameTable($oldUtmTableName, $newUtmTableName);
				}
			}
		}

		return $result;
	}

	/**
	 * @param mixed $primary
	 *
	 * @return Main\DB\Result|Entity\DeleteResult
	 */
	public static function delete($primary)
	{
		global $USER_FIELD_MANAGER;

		// get old data
		$hlblock = static::getByPrimary($primary)->fetch();

		// remove row
		$result = parent::delete($primary);

		if (!$result->isSuccess(true))
		{
			return $result;
		}

		// get file fields
		$fileFieldList = array();
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		$fields = $USER_FIELD_MANAGER->getUserFields(static::compileEntityId($hlblock['ID']));

		foreach ($fields as $name => $field)
		{
			if ($field['USER_TYPE']['BASE_TYPE'] === 'file')
			{
				$fileFieldList[] = $name;
			}
		}

		// delete files
		if (!empty($fileFieldList))
		{
			$oldEntity = static::compileEntity($hlblock);

			$query = new Entity\Query($oldEntity);

			// select file ids
			$query->setSelect($fileFieldList);

			// if they are not empty
			$filter = array('LOGIC' => 'OR');

			foreach ($fileFieldList as $fileField)
			{
				$filter['!'.$fileField] = false;
			}

			$query->setFilter($filter);

			// go
			$iterator = $query->exec();

			while ($row = $iterator->fetch())
			{
				foreach ($fileFieldList as $fileField)
				{
					if (!empty($row[$fileField]))
					{
						if (is_array($row[$fileField]))
						{
							foreach ($row[$fileField] as $value)
							{
								\CFile::delete($value);
							}
						}
						else
						{
							\CFile::delete($row[$fileField]);
						}
					}
				}
			}
			unset($row, $iterator);
		}

		$connection = Application::getConnection();

		foreach ($fields as $field)
		{
			// delete from uf registry
			if ($field['USER_TYPE']['BASE_TYPE'] === 'enum')
			{
				$enumField = new \CUserFieldEnum;
				$enumField->DeleteFieldEnum($field['ID']);
			}

			$connection->query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = ".$field['ID']);
			$connection->query("DELETE FROM b_user_field WHERE ID = ".$field['ID']);

			// if multiple - drop utm table
			if ($field['MULTIPLE'] == 'Y')
			{
				$utmTableName = static::getMultipleValueTableName($hlblock, $field);
				$connection->dropTable($utmTableName);
			}
		}

		// clear uf cache
		$managedCache = Application::getInstance()->getManagedCache();
		if(CACHED_b_user_field !== false)
		{
			$managedCache->cleanDir("b_user_field");
		}

		// clear langs
		$res = HighloadBlockLangTable::getList(array(
			'filter' => array('ID' => $primary)
		));
		while ($row = $res->fetch())
		{
			HighloadBlockLangTable::delete([
				'ID' => $row['ID'],
				'LID' => $row['LID'],
			]);
		}

		// clear rights
		$res = HighloadBlockRightsTable::getList(array(
			'filter' => array('HL_ID' => $primary)
		));
		while ($row = $res->fetch())
		{
			HighloadBlockRightsTable::delete($row['ID']);
		}

		// drop hl table
		$connection->dropTable($hlblock['TABLE_NAME']);

		return $result;
	}

	/**
	 * @param array|int|string $hlblock Could be a block, ID or NAME of block.
	 * @return array|null
	 */
	public static function resolveHighloadblock($hlblock)
	{
		if (!is_array($hlblock))
		{
			if (is_int($hlblock) || is_numeric(mb_substr($hlblock, 0, 1)))
			{
				// we have an id
				$hlblock = HighloadBlockTable::getById($hlblock)->fetch();
			}
			elseif (is_string($hlblock) && $hlblock !== '')
			{
				// we have a name
				$hlblock = HighloadBlockTable::query()->addSelect('*')->where('NAME', $hlblock)->exec()->fetch();
			}
			else
			{
				$hlblock = null;
			}
		}
		if (empty($hlblock))
			return null;

		if (!isset($hlblock['ID']))
			return null;
		if (!isset($hlblock['NAME']) || !preg_match('/^[a-z0-9_]+$/i', $hlblock['NAME']))
			return null;
		if (empty($hlblock['TABLE_NAME']))
			return null;

		return $hlblock;
	}

	/**
	 * @param array|int|string $hlblock Could be a block, ID or NAME of block.
	 * @param bool $force force recompile if entity already exists
	 *
	 * @return Main\ORM\Entity
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function compileEntity($hlblock, bool $force = false)
	{
		global $USER_FIELD_MANAGER;

		$rawBlock = $hlblock;
		$hlblock = static::resolveHighloadblock($hlblock);
		if (empty($hlblock))
		{
			throw new Main\SystemException(sprintf(
				"Invalid highloadblock description '%s'.", mydump($rawBlock)
			));
		}
		unset($rawBlock);

		if (class_exists($hlblock['NAME'] . 'Table') && !$force)
		{
			return Main\ORM\Entity::getInstance($hlblock['NAME']);
		}

		// generate entity & data manager
		$fieldsMap = array();

		// add ID
		$fieldsMap['ID'] = array(
			'data_type' => 'integer',
			'primary' => true,
			'autocomplete' => true
		);

		// build datamanager class
		$entityName = $hlblock['NAME'];
		$entityDataClass = $hlblock['NAME'].'Table';

		if (class_exists($entityDataClass))
		{
			// rebuild if it's already exists
			Main\ORM\Entity::destroy($entityDataClass);
		}
		else
		{
			$entityTableName = $hlblock['TABLE_NAME'];

			// make with an empty map
			$eval = '
				class '.$entityDataClass.' extends '.__NAMESPACE__.'\DataManager
				{
					public static function getTableName()
					{
						return '.var_export($entityTableName, true).';
					}

					public static function getMap()
					{
						return '.var_export($fieldsMap, true).';
					}

					public static function getHighloadBlock()
					{
						return '.var_export($hlblock, true).';
					}
				}
			';

			eval($eval);
		}

		// then configure and attach fields
		/** @var \Bitrix\Main\Entity\DataManager $entityDataClass */
		$entity = $entityDataClass::getEntity();

		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		$uFields = $USER_FIELD_MANAGER->getUserFields(static::compileEntityId($hlblock['ID']));

		foreach ($uFields as $uField)
		{
			if ($uField['MULTIPLE'] == 'N')
			{
				// just add single field
				$params = array(
					'required' => $uField['MANDATORY'] == 'Y'
				);
				$field = $USER_FIELD_MANAGER->getEntityField($uField, $uField['FIELD_NAME'], $params);
				$entity->addField($field);
				foreach ($USER_FIELD_MANAGER->getEntityReferences($uField, $field) as $reference)
				{
					$entity->addField($reference);
				}
			}
			else
			{
				// build utm entity
				static::compileUtmEntity($entity, $uField);
			}
		}

		return Main\ORM\Entity::getInstance($entityName);
	}

	/**
	 * @param string|int $id
	 * @return string
	 */
	public static function compileEntityId($id)
	{
		return self::ENTITY_ID_PREFIX.$id;
	}

	public static function OnBeforeUserTypeAdd($field)
	{
		if (preg_match(self::ENTITY_ID_MASK, $field['ENTITY_ID'], $matches))
		{
			if (mb_substr($field['FIELD_NAME'], -4) == '_REF')
			{
				/**
				 * postfix _REF reserved for references to other highloadblocks
				 * @see CUserTypeHlblock::getEntityReferences
				 */
				global $APPLICATION;

				$APPLICATION->ThrowException(
					Main\Localization\Loc::getMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_FIELD_NAME_REF_RESERVED')
				);

				return false;
			}
			else
			{
				return array('PROVIDE_STORAGE' => false);
			}
		}

		return true;
	}

	public static function onAfterUserTypeAdd($field)
	{
		global $APPLICATION, $USER_FIELD_MANAGER;

		if (preg_match(self::ENTITY_ID_MASK, $field['ENTITY_ID'], $matches))
		{
			$field['USER_TYPE'] = $USER_FIELD_MANAGER->getUserType($field['USER_TYPE_ID']);

			// get entity info
			$hlblockId = $matches[1];
			$hlblock = HighloadBlockTable::getById($hlblockId)->fetch();

			if (empty($hlblock))
			{
				$APPLICATION->throwException(sprintf(
					'Entity "'.static::compileEntityId('%s').'" wasn\'t found.', $hlblockId
				));

				return false;
			}

			// get usertype info
			$sqlColumnType = $USER_FIELD_MANAGER->getUtsDBColumnType($field);

			// create field in db
			$connection = Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();

			$connection->query(sprintf(
				'ALTER TABLE %s ADD %s %s',
				$sqlHelper->quote($hlblock['TABLE_NAME']), $sqlHelper->quote($field['FIELD_NAME']), $sqlColumnType
			));

			if ($field['MULTIPLE'] == 'Y')
			{
				// create table for this relation
				$hlentity = static::compileEntity($hlblock, true);
				$utmEntity = Entity\Base::getInstance(HighloadBlockTable::getUtmEntityClassName($hlentity, $field));

				$utmEntity->createDbTable();

				// add indexes
				$connection->query(sprintf(
					'CREATE INDEX %s ON %s (%s)',
					$sqlHelper->quote('IX_UTM_HL'.$hlblock['ID'].'_'.$field['ID'].'_ID'),
					$sqlHelper->quote($utmEntity->getDBTableName()),
					$sqlHelper->quote('ID')
				));

				$connection->query(sprintf(
					'CREATE INDEX %s ON %s (%s)',
					$sqlHelper->quote('IX_UTM_HL'.$hlblock['ID'].'_'.$field['ID'].'_VALUE'),
					$sqlHelper->quote($utmEntity->getDBTableName()),
					$sqlHelper->quote('VALUE')
				));
			}

			return array('PROVIDE_STORAGE' => false);
		}

		return true;
	}

	public static function OnBeforeUserTypeDelete($field)
	{
		global $USER_FIELD_MANAGER;

		if (preg_match(self::ENTITY_ID_MASK, $field['ENTITY_ID'], $matches))
		{
			// get entity info
			$hlblockId = $matches[1];
			$hlblock = HighloadBlockTable::getById($hlblockId)->fetch();

			if (empty($hlblock))
			{
				// non-existent or zombie. let it go
				return array('PROVIDE_STORAGE' => false);
			}

			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			$fieldType = $USER_FIELD_MANAGER->getUserType($field["USER_TYPE_ID"]);

			if ($fieldType['BASE_TYPE'] == 'file')
			{
				// if it was file field, then delete all files
				$entity = static::compileEntity($hlblock);

				/** @var DataManager $dataClass */
				$dataClass = $entity->getDataClass();

				$rows = $dataClass::getList(array('select' => array($field['FIELD_NAME'])));

				while ($oldData = $rows->fetch())
				{
					if (empty($oldData[$field['FIELD_NAME']]))
					{
						continue;
					}

					if(is_array($oldData[$field['FIELD_NAME']]))
					{
						foreach($oldData[$field['FIELD_NAME']] as $value)
						{
							\CFile::delete($value);
						}
					}
					else
					{
						\CFile::delete($oldData[$field['FIELD_NAME']]);
					}
				}
			}

			// drop db column
			$connection = Application::getConnection();
			$connection->dropColumn($hlblock['TABLE_NAME'], $field['FIELD_NAME']);

			// if multiple - drop utm table
			if ($field['MULTIPLE'] == 'Y')
			{
				$utmTableName = static::getMultipleValueTableName($hlblock, $field);
				$connection->dropTable($utmTableName);
			}

			return array('PROVIDE_STORAGE' => false);
		}

		return true;
	}

	protected static function compileUtmEntity(Entity\Base $hlentity, $userfield)
	{
		global $USER_FIELD_MANAGER;

		// build utm entity
		/** @var DataManager $hlDataClass */
		$hlDataClass = $hlentity->getDataClass();
		$hlblock = $hlDataClass::getHighloadBlock();

		$utmClassName = static::getUtmEntityClassName($hlentity, $userfield);
		$utmTableName = static::getMultipleValueTableName($hlblock, $userfield);

		if (class_exists($utmClassName.'Table'))
		{
			// rebuild if it already exists
			Entity\Base::destroy($utmClassName.'Table');
			$utmEntity = Entity\Base::getInstance($utmClassName);
		}
		else
		{
			// create entity from scratch
			$utmEntity = Entity\Base::compileEntity($utmClassName, array(), array(
				'table_name' => $utmTableName,
				'namespace' => $hlentity->getNamespace()
			));
		}

		// main fields
		$utmValueField = $USER_FIELD_MANAGER->getEntityField($userfield, 'VALUE');

		$utmEntityFields = array(
			new Entity\IntegerField('ID'),
			$utmValueField
		);

		// references
		$references = $USER_FIELD_MANAGER->getEntityReferences($userfield, $utmValueField);

		foreach ($references as $reference)
		{
			$utmEntityFields[] = $reference;
		}

		foreach ($utmEntityFields as $field)
		{
			$utmEntity->addField($field);
		}

		// add original entity reference
		$referenceField = new Entity\ReferenceField(
			'OBJECT',
			$hlentity,
			array('=this.ID' => 'ref.ID')
		);

		$utmEntity->addField($referenceField);

		// add short alias for back-reference
		$aliasField = new Entity\ExpressionField(
			$userfield['FIELD_NAME'].'_SINGLE',
			'%s',
			$utmEntity->getFullName().':'.'OBJECT.VALUE',
			array(
					'data_type' => get_class($utmEntity->getField('VALUE')),
					'required' => $userfield['MANDATORY'] == 'Y'
				)
		);

		$hlentity->addField($aliasField);

		// add aliases to references
		/*foreach ($references as $reference)
		{
			// todo after #44924 is resolved
			// actually no. to make it work expression should support linking to references
		}*/

		// add serialized cache-field
		$cacheField = new Main\ORM\Fields\ArrayField($userfield['FIELD_NAME'], array(
			'required' => $userfield['MANDATORY'] == 'Y'
		));

		Main\UserFieldTable::setMultipleFieldSerialization($cacheField, $userfield);

		$hlentity->addField($cacheField);

		return $utmEntity;
	}

	public static function getUtmEntityClassName(Entity\Base $hlentity, $userfield)
	{
		return $hlentity->getName().'Utm'.Main\Text\StringHelper::snake2camel($userfield['FIELD_NAME']);
	}

	public static function getMultipleValueTableName($hlblock, $userfield)
	{
		return $hlblock['TABLE_NAME'].'_'.mb_strtolower($userfield['FIELD_NAME']);
	}

	public static function validateName()
	{
		return array(
			new Entity\Validator\Unique,
			new Entity\Validator\Length(
				null,
				100,
				array('MAX' => GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_NAME_FIELD_LENGTH_INVALID'))
			),
			new Entity\Validator\RegExp(
				'/^[A-Z][A-Za-z0-9]*$/',
				GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_NAME_FIELD_REGEXP_INVALID')
			),
			new Entity\Validator\RegExp(
				'/(?<!Table)$/i',
				GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_NAME_FIELD_TABLE_POSTFIX_INVALID')
			)
		);
	}

	public static function validateTableName()
	{
		return array(
			new Entity\Validator\Unique,
			new Entity\Validator\Length(
				null,
				64,
				array('MAX' => GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_TABLE_NAME_FIELD_LENGTH_INVALID'))
			),
			new Entity\Validator\RegExp(
				'/^[a-z0-9_]+$/',
				GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_TABLE_NAME_FIELD_REGEXP_INVALID')
			),
			array(__CLASS__, 'validateTableExisting')
		);
	}

	public static function validateTableExisting($value, $primary, array $row, Entity\Field $field)
	{
		$checkName = null;

		if (empty($primary))
		{
			// new row
			$checkName = $value;
		}
		else
		{
			// update row
			$oldData = static::getByPrimary($primary)->fetch();

			if ($value != $oldData['TABLE_NAME'])
			{
				// table name has been changed for existing row
				$checkName = $value;
			}
		}

		if (!empty($checkName))
		{
			if (Application::getConnection()->isTableExists($checkName))
			{
				return GetMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_TABLE_NAME_ALREADY_EXISTS',
					array('#TABLE_NAME#' => $value)
				);
			}
		}

		return true;
	}
}