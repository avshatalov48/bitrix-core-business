<?php

namespace Bitrix\Calendar\Internals\Counter;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class CounterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Counter_Query query()
 * @method static EO_Counter_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Counter_Result getById($id)
 * @method static EO_Counter_Result getList(array $parameters = [])
 * @method static EO_Counter_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection wakeUpCollection($rows)
 */
class CounterTable extends DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_calendar_scorer';
	}

	public static function getClass(): string
	{
		return static::class;
	}

	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'EVENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'PARENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'TYPE' => [
				'data_type' => 'string',
				'required' => true,
			],
			'VALUE' => [
				'data_type' => 'integer',
				'required' => true,
			],

			// references
			'USER' => [
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => ['=this.USER_ID' => 'ref.ID'],
			],
			'EVENT' => [
				'data_type' => 'Bitrix\Calendar\Internals\EventTable',
				'reference' => ['=this.EVENT_ID' => 'ref.ID'],
			],
		];
	}
}
