<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Internals;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

/**
 * Class CollabLastActivityTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CollabLastActivity_Query query()
 * @method static EO_CollabLastActivity_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CollabLastActivity_Result getById($id)
 * @method static EO_CollabLastActivity_Result getList(array $parameters = [])
 * @method static EO_CollabLastActivity_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\LastActivityEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLastActivity_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Collab\Internals\LastActivityEntity wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Collab\Internals\EO_CollabLastActivity_Collection wakeUpCollection($rows)
 */
class CollabLastActivityTable extends DataManager
{
	use DeleteByFilterTrait;
	use MergeTrait;

	public static function getTableName(): string
	{
		return 'b_sonet_collab_last_activity';
	}

	public static function getObjectClass(): string
	{
		return LastActivityEntity::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('USER_ID'))
				->configurePrimary()
				->configureAutocomplete(false),

			(new IntegerField('COLLAB_ID'))
				->configureRequired(),

			(new DatetimeField('ACTIVITY_DATE'))
				->configureDefaultValueNow(),

			(new Reference(
				'USER',
				UserTable::getEntity(),
				Join::on('this.USER_ID', 'ref.ID')
			))
				->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'COLLAB',
				WorkgroupTable::getEntity(),
				Join::on('this.COLLAB_ID', 'ref.ID')
			))
				->configureJoinType(Join::TYPE_INNER),
			(new Reference(
				'MEMBER',
				UserToGroupTable::getEntity(),
				Join::on('this.COLLAB_ID', 'ref.GROUP_ID')->whereColumn('this.USER_ID', 'ref.USER_ID')
			))
				->configureJoinType(Join::TYPE_INNER),
		];
	}
}