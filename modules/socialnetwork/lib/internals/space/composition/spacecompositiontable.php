<?php
namespace Bitrix\Socialnetwork\Internals\Space\Composition;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\SystemException;

/**
 * Class SpaceCompositionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> SETTINGS text mandatory
 * </ul>
 *
 * @package Bitrix\Sonet
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SpaceComposition_Query query()
 * @method static EO_SpaceComposition_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SpaceComposition_Result getById($id)
 * @method static EO_SpaceComposition_Result getList(array $parameters = [])
 * @method static EO_SpaceComposition_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\Space\Composition\SpaceCompositionObject createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Space\Composition\SpaceCompositionCollection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Space\Composition\SpaceCompositionObject wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Space\Composition\SpaceCompositionCollection wakeUpCollection($rows)
 */

class SpaceCompositionTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 */
	public static function getTableName(): string
	{
		return 'b_sonet_space_composition';
	}

	/**
	 * Returns entity map definition.
	 */
	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new IntegerField('USER_ID'))
				->configureRequired(),
			(new IntegerField('SPACE_ID'))
				->configureRequired(),
			(new ArrayField('SETTINGS'))
				->configureRequired()
				->configureSerializationJson(),
		];
	}

	public static function getObjectClass(): string
	{
		return SpaceCompositionObject::class;
	}

	public static function getCollectionClass(): string
	{
		return SpaceCompositionCollection::class;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function isDataFilled(int $userId, int $spaceId): bool
	{
		$composition = static::getByIds($userId, $spaceId);

		return !is_null($composition);
	}

	public static function fill(int $userId, int $spaceId, array $settings): Result
	{
		$composition = (new SpaceCompositionObject())
			->setUserId($userId)
			->setSpaceId($spaceId)
			->setSettings($settings);

		return $composition->save();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getByIds(int $userId, int $spaceId = 0): ?SpaceCompositionObject
	{
		$query = static::query();
		$query
			->setSelect(['*'])
			->where('USER_ID', $userId)
			->where('SPACE_ID', $spaceId)
			->setLimit(1);

		return $query->exec()->fetchObject();
	}
}