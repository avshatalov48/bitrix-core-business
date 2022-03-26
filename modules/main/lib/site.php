<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\IO;

/**
 * Class SiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Site_Query query()
 * @method static EO_Site_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Site_Result getById($id)
 * @method static EO_Site_Result getList(array $parameters = [])
 * @method static EO_Site_Entity getEntity()
 * @method static \Bitrix\Main\EO_Site createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Site_Collection createCollection()
 * @method static \Bitrix\Main\EO_Site wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Site_Collection wakeUpCollection($rows)
 */
class SiteTable extends ORM\Data\DataManager
{
	private static $documentRootCache = [];

	public static function getDocumentRoot($siteId = null)
	{
		if ($siteId === null)
		{
			$context = Application::getInstance()->getContext();
			$siteId = $context->getSite();
		}

		if (!isset(self::$documentRootCache[$siteId]))
		{
			$ttl = (CACHED_b_lang !== false ? CACHED_b_lang : 0);

			$site = SiteTable::getRow([
				"filter" => ["=LID" => $siteId],
				"cache" => ["ttl" => $ttl],
			]);

			if ($site && ($docRoot = $site["DOC_ROOT"]) && ($docRoot <> ''))
			{
				if (!IO\Path::isAbsolute($docRoot))
				{
					$docRoot = IO\Path::combine(Application::getDocumentRoot(), $docRoot);
				}
				self::$documentRootCache[$siteId] = $docRoot;
			}
			else
			{
				self::$documentRootCache[$siteId] = Application::getDocumentRoot();
			}
		}

		return self::$documentRootCache[$siteId];
	}

	public static function getTableName()
	{
		return 'b_lang';
	}

	public static function getMap()
	{
		return array(
			'LID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'DEF' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'DIR' => array(
				'data_type' => 'string'
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
			),
			'DOC_ROOT' => array(
				'data_type' => 'string',
			),
			'DOMAIN_LIMITED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'SERVER_NAME' => array(
				'data_type' => 'string'
			),
			'SITE_NAME' => array(
				'data_type' => 'string'
			),
			'EMAIL' => array(
				'data_type' => 'string'
			),
			'CULTURE_ID' => array(
				'data_type' => 'integer',
			),
			'CULTURE' => array(
				'data_type' => 'Bitrix\Main\Localization\Culture',
				'reference' => array('=this.CULTURE_ID' => 'ref.ID'),
			),
		);
	}
}
