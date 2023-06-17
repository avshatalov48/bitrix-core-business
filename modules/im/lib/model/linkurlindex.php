<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\IndexTableTrait;
use Bitrix\Im\V2\Common\MigrationStatusCheckerTrait;
use Bitrix\Im\V2\Link\Url\UrlItem;
use Bitrix\Im\V2\Entity\Url\RichData;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Filter\Helper;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Search\Content;
use Bitrix\Main\UrlPreview\UrlMetadataTable;
use Bitrix\Main\Web\Uri;

/**
 * Class MessageUrlIndexTable
 *
 * Fields:
 * <ul>
 * <li> URL_ID int mandatory
 * <li> SEARCH_CONTENT text optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkUrlIndex_Query query()
 * @method static EO_LinkUrlIndex_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkUrlIndex_Result getById($id)
 * @method static EO_LinkUrlIndex_Result getList(array $parameters = [])
 * @method static EO_LinkUrlIndex_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkUrlIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkUrlIndex_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkUrlIndex wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkUrlIndex_Collection wakeUpCollection($rows)
 */

class LinkUrlIndexTable extends DataManager
{
	use MigrationStatusCheckerTrait;
	use IndexTableTrait;

	protected const FORBIDDEN_WORDS = ['www'];

	protected static string $migrationOptionName = 'im_link_url_migration';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_url_index';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'URL_ID' => new IntegerField(
				'URL_ID',
				[
					'primary' => true,
				]
			),
			'SEARCH_CONTENT' => new TextField(
				'SEARCH_CONTENT',
				[
				]
			),
			'URL' => (new Reference(
				'URL',
				LinkUrlTable::class,
				Join::on('this.URL_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER)
		];
	}

	public static function index(int $limit = 500): void
	{
		$urlWithoutIndex = LinkUrlTable::query()
			->setSelect(['ID', 'URL', 'PREVIEW_URL_ID'])
			->where('IS_INDEXED', false)
			->setOrder(['ID' => 'ASC'])
			->setLimit($limit)
			->fetchCollection()
		;
		$urls = new \Bitrix\Im\V2\Link\Url\UrlCollection($urlWithoutIndex);
		$urls->fillMetadata(false);
		$inserts = [];
		/** @var UrlItem $url */
		foreach ($urls as $url)
		{
			if (!self::isMigrationFinished() && $url->getEntity()->getMetadata()['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC)
			{
				//Until the end of the migration, we do not get rich data about dynamic links. To do this, put a stub
				$url->getEntity()->setRichData(new RichData());
			}
			$inserts[] = [
				'URL_ID' => $url->getId(),
				'SEARCH_CONTENT' => static::generateSearchIndex($url),
			];
		}
		static::multiplyInsertWithoutDuplicate($inserts);
		static::updateIndexStatus($urlWithoutIndex->getIdList());
	}

	protected static function getBaseDataClass(): string
	{
		return LinkUrlTable::class;
	}

	private static function generateSearchIndex(UrlItem $url): string
	{

		$uri = new Uri($url->getUrl());
		$splitUrl = Helper::splitWords($uri->getHost());
		$splitUrl = array_diff($splitUrl, self::FORBIDDEN_WORDS);
		$index = $splitUrl;
		if ($url->getEntity()->isRich())
		{
			$richData = $url->getEntity()->getRichData();
			if ($richData !== null)
			{
				$splitTitle = Helper::splitWords($richData->getName());
				$index = array_merge($splitUrl, $splitTitle);
			}
		}

		return Content::prepareStringToken(implode(' ', $index));
	}
}