<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

// TODO: Add description for class
/**
 * Class SectionConnectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SectionConnection_Query query()
 * @method static EO_SectionConnection_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SectionConnection_Result getById($id)
 * @method static EO_SectionConnection_Result getList(array $parameters = [])
 * @method static EO_SectionConnection_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection_Collection wakeUpCollection($rows)
 */
class SectionConnectionTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_calendar_section_connection';
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_ID'))
			,
			(new IntegerField('SECTION_ID'))
				->configureRequired()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_SECTION_ID'))
			,
			(new IntegerField('CONNECTION_ID'))
				->configureRequired()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_CONNECTION_ID'))
			,
			(new StringField('VENDOR_SECTION_ID'))
				->configureRequired()
				->configureSize(255)
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_VENDOR_SECTION_ID'))
			,
			(new TextField('SYNC_TOKEN'))
				->configureNullable()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_SYNC_TOKEN'))
			,
			(new TextField('PAGE_TOKEN'))
				->configureNullable()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_PAGE_TOKEN'))
			,
			(new BooleanField('ACTIVE'))
				->configureRequired()
				->configureStorageValues('N', 'Y')
				->configureDefaultValue('Y')
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_ACTIVE'))
			,
			(new DatetimeField('LAST_SYNC_DATE'))
				->configureNullable()
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_LAST_SYNC_DATE'))
			,
			(new StringField('LAST_SYNC_STATUS'))
				->configureNullable()
				->configureSize(10)
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_LAST_SYNC_STATUS'))
			,
			(new StringField('VERSION_ID'))
				->configureNullable()
				->configureSize(255)
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_VERSION'))
			,
			(new ReferenceField(
				'SECTION',
				SectionTable::class,
				Join::on('this.SECTION_ID', 'ref.ID'),
			))
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_SECTION'))
			,
			(new ReferenceField(
				'CONNECTION',
				DavConnectionTable::class,
				Join::on('this.CONNECTION_ID', 'ref.ID'),
			))
			,
			(new BooleanField('IS_PRIMARY'))
				->configureRequired()
				->configureStorageValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('CALENDAR_SYNC_SCONNECTION_FIELD_ACTIVE'))
			,
		];
	}
}
