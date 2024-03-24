<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\IndexTableTrait;
use Bitrix\Im\V2\Link\Calendar\CalendarCollection;
use Bitrix\Im\V2\Link\Calendar\CalendarItem;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Search\Content;

/**
 * Class LinkCalendarIndexTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SEARCH_CONTENT text optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkCalendarIndex_Query query()
 * @method static EO_LinkCalendarIndex_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkCalendarIndex_Result getById($id)
 * @method static EO_LinkCalendarIndex_Result getList(array $parameters = [])
 * @method static EO_LinkCalendarIndex_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkCalendarIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkCalendarIndex_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkCalendarIndex wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkCalendarIndex_Collection wakeUpCollection($rows)
 */

class LinkCalendarIndexTable extends DataManager
{
	use IndexTableTrait
	{
		indexInBackground as private defaultIndexInBackground;
	}

	static private array $toIndexIds = [];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_calendar_index';
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
					//'title' => Loc::getMessage('LINK_CALENDAR_INDEX_ENTITY_ID_FIELD'),
				]
			),
			'SEARCH_CONTENT' => new TextField(
				'SEARCH_CONTENT',
				[
					//'title' => Loc::getMessage('LINK_CALENDAR_INDEX_ENTITY_SEARCH_CONTENT_FIELD'),
				]
			),
		];
	}

	protected static function getBaseDataClass(): string
	{
		return LinkCalendarTable::class;
	}

	public static function indexInBackground(array $ids): void
	{
		if (!empty($ids))
		{
			array_push(static::$toIndexIds, ...$ids);
		}

		static::defaultIndexInBackground();
	}

	public static function index(array $ids = []): void
	{
		$toIndexIds = array_merge(static::$toIndexIds, $ids);
		$toIndexIds = array_unique($toIndexIds);
		if (empty($toIndexIds))
		{
			return;
		}
		$linkWithoutIndex = LinkCalendarTable::query()
			->setSelect(['*'])
			->whereIn('ID', $toIndexIds)
			->fetchCollection()
		;
		$links = new CalendarCollection($linkWithoutIndex);
		$links->fillCalendarData();
		$inserts = [];
		foreach ($links as $link)
		{
			$inserts[] = [
				'ID' => $link->getId(),
				'SEARCH_CONTENT' => static::generateSearchIndex($link),
			];
		}
		static::multiplyInsertWithoutDuplicate($inserts);
	}

	private static function generateSearchIndex(CalendarItem $link): string
	{
		return Content::prepareStringToken($link->getTitle());
	}
}