<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\FileTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;

/**
 * Class SharingLinkMemberTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> LINK_ID int mandatory
 * <li> MEMBER_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SharingLinkMember_Query query()
 * @method static EO_SharingLinkMember_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SharingLinkMember_Result getById($id)
 * @method static EO_SharingLinkMember_Result getList(array $parameters = [])
 * @method static EO_SharingLinkMember_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection wakeUpCollection($rows)
 */

class SharingLinkMemberTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_sharing_link_member';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID',
				[]
			))->configureTitle(Loc::getMessage('SHARING_LINK_MEMBER_ENTITY_ID_FIELD'))
				->configurePrimary()
			,
			(new IntegerField('LINK_ID',
				[]
			))->configureTitle(Loc::getMessage('SHARING_LINK_MEMBER_ENTITY_LINK_ID_FIELD'))
			,
			(new IntegerField('MEMBER_ID',
				[]
			))->configureTitle(Loc::getMessage('SHARING_LINK_MEMBER_ENTITY_MEMBER_ID_FIELD'))
			,
			(new Reference(
				'MEMBER',
				SharingLinkTable::class,
				Join::on('this.LINK_ID', 'ref.ID'),
			)),
			(new ReferenceField(
				'USER',
				UserTable::getEntity(),
				Join::on('this.MEMBER_ID', 'ref.ID'),
				['join_type' => Join::TYPE_LEFT]
			)),
			(new ReferenceField(
				'IMAGE',
				FileTable::class,
				Join::on('this.USER.PERSONAL_PHOTO', 'ref.ID'),
				['join_type' => Join::TYPE_LEFT]
			))
		];
	}
}