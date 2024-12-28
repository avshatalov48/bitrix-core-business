<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Socialnetwork\Internals\Group\GroupEntity;
use Bitrix\Socialnetwork\Internals\Group\GroupEntityCollection;

Loc::loadMessages(__FILE__);

/**
 * Class WorkgroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Workgroup_Query query()
 * @method static EO_Workgroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Workgroup_Result getById($id)
 * @method static EO_Workgroup_Result getList(array $parameters = [])
 * @method static EO_Workgroup_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\Group\GroupEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Group\GroupEntityCollection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Group\GroupEntity wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Group\GroupEntityCollection wakeUpCollection($rows)
 */
class WorkgroupTable extends Entity\DataManager
{
	public const AUTO_MEMBERSHIP_YES = 'Y';
	public const AUTO_MEMBERSHIP_NO = 'N';

	public static function getObjectClass(): string
	{
		return GroupEntity::class;
	}

	public static function getCollectionClass(): string
	{
		return GroupEntityCollection::class;
	}

	public static function getAutoMembershipValuesAll(): array
	{
		return [self::AUTO_MEMBERSHIP_NO, self::AUTO_MEMBERSHIP_YES];
	}

	public static function getTableName(): string
	{
		return 'b_sonet_group';
	}

	public static function getUfId(): string
	{
		return 'SONET_GROUP';
	}

	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'SITE_ID' => [
				'data_type' => 'string',
			],
			'SUBJECT_ID' => [
				'data_type' => 'integer',
			],
			'WORKGROUP_SUBJECT' => [
				'data_type' => '\Bitrix\Socialnetwork\WorkgroupSubject',
				'reference' => ['=this.SUBJECT_ID' => 'ref.ID'],
			],
			'NAME' => [
				'data_type' => 'string',
				'save_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getSaveModificator'],
				'fetch_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getFetchModificator'],
			],
			'DESCRIPTION' => [
				'data_type' => 'text',
				'save_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getSaveModificator'],
				'fetch_data_modification' => ['\Bitrix\Main\Text\Emoji', 'getFetchModificator'],
			],
			'KEYWORDS' => [
				'data_type' => 'string',
			],
			'CLOSED' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'VISIBLE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'OPENED' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'DATE_CREATE' => [
				'data_type' => 'datetime',
			],
			'DATE_UPDATE' => [
				'data_type' => 'datetime',
			],
			'DATE_ACTIVITY' => [
				'data_type' => 'datetime',
			],
			'IMAGE_ID' => [
				'data_type' => 'integer',
			],
			'AVATAR_TYPE' => [
				'data_type' => 'string',
			],
			'OWNER_ID' => [
				'data_type' => 'integer',
			],
			'WORKGROUP_OWNER' => [
				'data_type' => '\Bitrix\Main\User',
				'reference' => ['=this.OWNER_ID' => 'ref.ID'],
			],
			'INITIATE_PERMS' => [
				'data_type' => 'string',
			],
			'NUMBER_OF_MEMBERS' => [
				'data_type' => 'integer',
			],
			'NUMBER_OF_MODERATORS' => [
				'data_type' => 'integer',
			],
			'PROJECT' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'PROJECT_DATE_START' => [
				'data_type' => 'datetime',
			],
			'PROJECT_DATE_FINISH' => [
				'data_type' => 'datetime',
			],
			'SEARCH_INDEX' => [
				'data_type' => 'text',
			],
			'LANDING' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'SCRUM_OWNER_ID' => [
				'data_type' => 'integer',
			],
			'SCRUM_MASTER_ID' => [
				'data_type' => 'integer',
			],
			'SCRUM_SPRINT_DURATION' => [
				'data_type' => 'integer',
			],
			'SCRUM_TASK_RESPONSIBLE' => [
				'data_type' => 'string',
				'values' => ['A', 'M'],
			],
			(new StringField('TYPE'))
				->configureNullable()
				->configureDefaultValue(null),

			(new OneToMany('SITES', WorkgroupSiteTable::class, 'GROUP'))
				->configureJoinType(Join::TYPE_LEFT),

			(new OneToMany('MEMBERS', UserToGroupTable::class, 'GROUP'))
				->configureJoinType(Join::TYPE_LEFT),
		];
	}

	public static function add(array $data): void
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}

	public static function update($primary, array $data): void
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}

	public static function delete($primary): void
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}
}
