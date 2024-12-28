<?php

namespace Bitrix\Socialnetwork\Collab\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class CollabOptionTable
 *
 * Fields:
 * <ul>
 * <li> COLLAB_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> VALUE string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Sonet
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CollabOption_Query query()
 * @method static EO_CollabOption_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CollabOption_Result getById($id)
 * @method static EO_CollabOption_Result getList(array $parameters = [])
 * @method static EO_CollabOption_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\OptionEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\OptionCollection createCollection()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\OptionEntity wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\OptionCollection wakeUpCollection($rows)
 */

class CollabOptionTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_collab_option';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('COLLAB_ID'))
				->configureRequired(),

			(new StringField('NAME'))
				->configureRequired()
				->addValidator(new LengthValidator(null, 255)),

			(new StringField('VALUE'))
				->configureRequired()
				->addValidator(new LengthValidator(null, 255)),
		];
	}

	public static function getObjectClass(): string
	{
		return OptionEntity::class;
	}

	public static function getCollectionClass(): string
	{
		return OptionCollection::class;
	}
}
