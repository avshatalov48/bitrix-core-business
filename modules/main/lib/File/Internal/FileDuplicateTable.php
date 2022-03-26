<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Main\File\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileDuplicateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileDuplicate_Query query()
 * @method static EO_FileDuplicate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FileDuplicate_Result getById($id)
 * @method static EO_FileDuplicate_Result getList(array $parameters = [])
 * @method static EO_FileDuplicate_Entity getEntity()
 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection createCollection()
 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate wakeUpObject($row)
 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection wakeUpCollection($rows)
 */
class FileDuplicateTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_file_duplicate';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("DUPLICATE_ID"))
				->configurePrimary(true),

			(new Fields\IntegerField("ORIGINAL_ID"))
				->configurePrimary(true),

			(new Fields\IntegerField("COUNTER"))
				->configureDefaultValue(1),

			(new Fields\BooleanField("ORIGINAL_DELETED"))
				->configureValues("N", "Y")
				->configureDefaultValue("N"),
		];
	}

	/**
	 * @param array $insertFields
	 * @param array $updateFields
	 */
	public static function merge(array $insertFields, array $updateFields)
	{
		$conn = static::getEntity()->getConnection();

		$keyFields = ["DUPLICATE_ID", "ORIGINAL_ID"];

		$sql = $conn->getSqlHelper()->prepareMerge(static::getTableName(), $keyFields, $insertFields, $updateFields);

		$conn->queryExecute(current($sql));

		static::getEntity()->cleanCache();
	}

	/**
	 * @param int $originalId
	 */
	public static function markDeleted($originalId)
	{
		$originalId = (int)$originalId;
		$conn = static::getEntity()->getConnection();
		$table = static::getTableName();

		$conn->query("
			update {$table} 
			set ORIGINAL_DELETED = 'Y' 
			where ORIGINAL_ID = {$originalId} and ORIGINAL_DELETED = 'N'
		");

		static::getEntity()->cleanCache();
	}
}
