<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main;

/**
 * Class RestAppTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RestApp_Query query()
 * @method static EO_RestApp_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_RestApp_Result getById($id)
 * @method static EO_RestApp_Result getList(array $parameters = array())
 * @method static EO_RestApp_Entity getEntity()
 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp createObject($setDefaultValues = true)
 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection createCollection()
 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp wakeUpObject($row)
 * @method static \Bitrix\MessageService\Internal\Entity\EO_RestApp_Collection wakeUpCollection($rows)
 */
class RestAppTable extends Main\ORM\Data\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_messageservice_rest_app';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			],
			'APP_ID' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => [__CLASS__, 'validateVarchar128'],
			],
			'CODE' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => [__CLASS__, 'validateVarchar128'],
			],
			'TYPE' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => [__CLASS__, 'validateType'],
			],
			'HANDLER' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => [__CLASS__, 'validateHandler'],
			],
			'DATE_ADD' => [
				'data_type' => 'datetime',
				'default_value' => new Main\Type\DateTime(),
			],
			'AUTHOR_ID' => [
				'data_type' => 'integer',
				'default_value' => 0,
			],
			'AUTHOR' => [
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => [
					'=this.AUTHOR_ID' => 'ref.ID'
				],
				'join_type' => 'LEFT',
			],
		];
	}

	/**
	 * @return array
	 */
	public static function validateVarchar128(): array
	{
		return [
			new Main\Entity\Validator\Length(null, 128),
		];
	}

	/**
	 * @return array
	 */
	public static function validateType(): array
	{
		return [
			new Main\Entity\Validator\Length(null, 30),
		];
	}

	/**
	 * @return array
	 */
	public static function validateHandler(): array
	{
		return [
			new Main\Entity\Validator\Length(null, 1000),
		];
	}
}