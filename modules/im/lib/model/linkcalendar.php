<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Main\Type\DateTime;

/**
 * Class LinkCalendarTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int optional
 * <li> CHAT_ID int optional
 * <li> AUTHOR_ID int optional
 * <li> DATE_CREATE datetime optional
 * <li> CALENDAR_ID int optional
 * <li> CALENDAR_TITLE int optional
 * <li> CALENDAR_DATE_FROM datetime optional
 * <li> CALENDAR_DATE_TO datetime optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkCalendar_Query query()
 * @method static EO_LinkCalendar_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkCalendar_Result getById($id)
 * @method static EO_LinkCalendar_Result getList(array $parameters = [])
 * @method static EO_LinkCalendar_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkCalendar createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkCalendar_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkCalendar wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkCalendar_Collection wakeUpCollection($rows)
 */

class LinkCalendarTable extends DataManager
{
	use DeleteByFilterTrait {
		deleteByFilter as defaultDeleteByFilter;
	}

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_calendar';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'nullable' => true
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[
				]
			),
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
				[
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
					'default_value' => static function() {
						return new DateTime();
					}
				]
			),
			'CALENDAR_ID' => new IntegerField(
				'CALENDAR_ID',
				[
				]
			),
			'CALENDAR_TITLE' => new StringField(
				'CALENDAR_TITLE',
				[
					'validation' => [__CLASS__, 'validateCalendarTitle'],
				]
			),
			'CALENDAR_DATE_FROM' => new DatetimeField(
				'CALENDAR_DATE_FROM',
				[
				]
			),
			'CALENDAR_DATE_TO' => new DatetimeField(
				'CALENDAR_DATE_TO',
				[
				]
			),
			'INDEX' => (new Reference(
				'INDEX',
				LinkCalendarIndexTable::class,
				Join::on('this.ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}

	/**
	 * Returns validators for CALENDAR_TITLE field.
	 *
	 * @return array
	 */
	public static function validateCalendarTitle(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	public static function deleteByFilter(array $filter)
	{
		LinkCalendarIndexTable::deleteByFilter($filter);
		static::defaultDeleteByFilter($filter);
	}

	public static function withSearchByTitle(Query $query, string $searchString): void
	{
		$preparedSearchString = LinkCalendarIndexTable::prepareSearchString($searchString);
		if (Content::canUseFulltextSearch($preparedSearchString))
		{
			$query->registerRuntimeField(
				(new Reference(
					'INDEX',
					LinkCalendarIndexTable::class,
					Join::on('this.ID', 'ref.ID')
				))->configureJoinType(Join::TYPE_INNER)
			);

			$query->whereMatch('INDEX.SEARCH_CONTENT', $preparedSearchString);
		}
	}
}