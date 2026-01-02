<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;

/**
 * Class QueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Queue_Query query()
 * @method static EO_Queue_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Queue_Result getById($id)
 * @method static EO_Queue_Result getList(array $parameters = [])
 * @method static EO_Queue_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobModel createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobCollectionModel createCollection()
 * @method static \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobModel wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Model\JobCollectionModel wakeUpCollection($rows)
 */
class QueueTable extends DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_sonet_onboarding_queue';
	}

	public static function getObjectClass(): string
	{
		return JobModel::class;
	}

	public static function getCollectionClass(): string
	{
		return JobCollectionModel::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('COLLAB_ID'))
				->configureRequired(),

			(new IntegerField('USER_ID'))
				->configureRequired(),

			(new StringField('TYPE'))
				->configureRequired(),

			(new DatetimeField('CREATED_DATE'))
				->configureDefaultValueNow(),

			(new DatetimeField('NEXT_EXECUTION'))
				->configureRequired(),

			(new DatetimeField('PROCESSED_DATE'))
				->configureNullable(),

			(new BooleanField('IS_PROCESSED'))
				->configureValues(0, 1)
				->configureDefaultValue(0),

			(new ReferenceField(
				'USER',
				UserTable::getEntity(),
				['this.USER_ID', 'ref.ID']
			))->configureJoinType(Join::TYPE_INNER),
		];
	}
}
