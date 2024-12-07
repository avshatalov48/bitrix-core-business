<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UrlPreview\UrlMetadataTable;

/**
 * Class MessageUrlTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int optional
 * <li> CHAT_ID int optional
 * <li> URL string(2000) optional
 * <li> PREVIEW_URL_ID int optional
 * <li> SEARCH_INDEX text optional
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkUrl_Query query()
 * @method static EO_LinkUrl_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkUrl_Result getById($id)
 * @method static EO_LinkUrl_Result getList(array $parameters = [])
 * @method static EO_LinkUrl_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkUrl createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkUrl_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkUrl wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkUrl_Collection wakeUpCollection($rows)
 */

class LinkUrlTable extends DataManager
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
		return 'b_im_link_url';
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
				[]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[]
			),
			'URL' => new StringField(
				'URL',
				[
					'validation' => [__CLASS__, 'validateUrl'],
				]
			),
			'PREVIEW_URL_ID' => new IntegerField(
				'PREVIEW_URL_ID',
				[]
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
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
				[]
			),
			'IS_INDEXED' => new BooleanField(
				'IS_INDEXED',
				[
					'required' => true,
					'values' => array('N', 'Y'),
					'default' => 'N',
					'default_value' => false,
				]
			),
			'PREVIEW_URL' => (new Reference(
				'PREVIEW_URL',
				UrlMetadataTable::class,
				Join::on('this.PREVIEW_URL_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_LEFT),
			'MESSAGE' => (new Reference(
				'MESSAGE',
				MessageTable::class,
				Join::on('this.MESSAGE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
			'CHAT' => (new Reference(
				'CHAT',
				ChatTable::class,
				Join::on('this.CHAT_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
			'AUTHOR' => (new Reference(
				'AUTHOR',
				\Bitrix\Main\UserTable::class,
				Join::on('this.AUTHOR_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER)
		];
	}

	/**
	 * Returns validators for URL field.
	 *
	 * @return array
	 */
	public static function validateUrl(): array
	{
		return [
			new LengthValidator(null, 2000),
		];
	}

	public static function deleteByFilter(array $filter)
	{
		LinkUrlIndexTable::deleteByParentFilter($filter);
		static::defaultDeleteByFilter($filter);
	}

	public static function withSearchByUrl(Query $query, string $searchString): void
	{
		$preparedSearchString = LinkUrlIndexTable::prepareSearchString($searchString);
		if (Content::canUseFulltextSearch($preparedSearchString))
		{
			$query->registerRuntimeField(
				(new Reference(
					'INDEX',
					LinkUrlIndexTable::class,
					Join::on('this.ID', 'ref.URL_ID')
				))->configureJoinType(Join::TYPE_INNER)
			);

			$query->whereMatch('INDEX.SEARCH_CONTENT', $preparedSearchString);
		}
	}
}