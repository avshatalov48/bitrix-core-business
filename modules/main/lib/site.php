<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM\Fields;

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
	protected const CACHE_TTL = 86400;

	protected static $documentRootCache = [];

	public static function getDocumentRoot($siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = Application::getInstance()->getContext()->getSite();
		}

		if (!isset(self::$documentRootCache[$siteId]))
		{
			$site = static::getRow([
				"filter" => ["=LID" => $siteId],
				"cache" => ["ttl" => static::CACHE_TTL],
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
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		return array(
			'LID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ID' => array(
				'data_type' => 'string',
				'expression' => array('%s', 'LID'),
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
				'data_type' => 'string',
			),
			'DIR' => array(
				'data_type' => 'string',
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
				'data_type' => 'string',
			),
			'SITE_NAME' => array(
				'data_type' => 'string',
			),
			'EMAIL' => array(
				'data_type' => 'string',
			),
			'CULTURE_ID' => array(
				'data_type' => 'integer',
			),
			'CULTURE' => array(
				'data_type' => 'Bitrix\Main\Localization\Culture',
				'reference' => array('=this.CULTURE_ID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
			'LANGUAGE' => array(
				'data_type' => 'Bitrix\Main\Localization\Language',
				'reference' => array('=this.LANGUAGE_ID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
			(new Fields\ExpressionField('DIR_LENGTH', $helper->getLengthFunction('%s'), 'DIR')),
			(new Fields\ExpressionField('DOC_ROOT_LENGTH', $helper->getIsNullFunction($helper->getLengthFunction('%s'), '0'), 'DOC_ROOT')),
		);
	}

	public static function getByDomain(string $host, string $directory)
	{
		$site = null;

		$sites = static::getList([
			'select' => ['*'],
			'filter' => ['=ACTIVE' => 'Y'],
			'order' => [
				'DIR_LENGTH' => 'DESC',
				'DOMAIN_LIMITED' => 'DESC',
				'SORT' => 'ASC',
			],
			'cache' => ['ttl' => static::CACHE_TTL],
		])->fetchAll();

		$result = SiteDomainTable::getList([
			'select' => ['LD_LID' => 'LID', 'LD_DOMAIN' => 'DOMAIN'],
			'order' => ['DOMAIN_LENGTH' => 'DESC'],
			'cache' => ['ttl' => static::CACHE_TTL],
		]);

		$domains = [];
		while ($row = $result->fetch())
		{
			$domains[$row['LD_LID']][] = $row;
		}

		$join = [];
		foreach ($sites as $row)
		{
			//LEFT JOIN
			$left = true;
			//LEFT JOIN b_lang_domain LD ON L.LID=LD.LID
			if (array_key_exists($row['LID'], $domains))
			{
				foreach ($domains[$row['LID']] as $dom)
				{
					//AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE CONCAT('%', LD.DOMAIN)
					if (strcasecmp(mb_substr(".".$host, -mb_strlen("." . $dom['LD_DOMAIN'])), "." . $dom['LD_DOMAIN']) == 0)
					{
						$join[] = $row + $dom;
						$left = false;
					}
				}
			}
			if ($left)
			{
				$join[] = $row + ['LD_LID' => '', 'LD_DOMAIN' => ''];
			}
		}

		$rows = [];
		foreach ($join as $row)
		{
			//WHERE ('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)
			if ($row['LD_LID'] != '' || strcasecmp(mb_substr($directory, 0, mb_strlen($row['DIR'])), $row['DIR']) == 0)
			{
				$rows[] = $row;
			}
		}

		foreach ($rows as $row)
		{
			if (
				(strcasecmp(mb_substr($directory, 0, mb_strlen($row['DIR'])), $row['DIR']) == 0)
				&& (($row['DOMAIN_LIMITED'] == 'Y' && $row['LD_LID'] != '') || $row['DOMAIN_LIMITED'] != 'Y')
			)
			{
				$site = $row;
				break;
			}
		}

		if ($site === null)
		{
			foreach ($rows as $row)
			{
				if (strncasecmp($directory, $row['DIR'], mb_strlen($directory)) == 0)
				{
					$site = $row;
					break;
				}
			}
		}

		if($site === null)
		{
			foreach ($rows as $row)
			{
				if (($row['DOMAIN_LIMITED'] == 'Y' && $row['LD_LID'] != '') || $row['DOMAIN_LIMITED'] != 'Y')
				{
					$site = $row;
					break;
				}
			}
		}

		if ($site === null && !empty($rows))
		{
			$site = $rows[0];
		}

		if ($site === null)
		{
			$site = static::getList([
				'select' => ['*'],
				'filter' => ['=ACTIVE' => 'Y'],
				'order' => [
					'DEF' => 'DESC',
					'SORT' => 'ASC',
				],
				'cache' => ['ttl' => static::CACHE_TTL],
			])->fetch();
		}

		if ($site)
		{
	  		// unset fields added from left join
			unset($site['LD_LID'], $site['LD_DOMAIN']);
		}

		return $site;
	}

	public static function getDefaultSite(array $selectFields = ['*']): ?EO_Site
	{
		return static::getList([
			'select' => $selectFields,
			'filter' => ['=DEF' => 'Y', '=ACTIVE' => 'Y'],
			'limit' => 1,
			'cache' => ['ttl' => static::CACHE_TTL],
		])->fetchObject();
	}

	public static function getDefaultLanguageId(): ?string
	{
		// using the same cache
		return static::getDefaultSite(['LID', 'LANGUAGE_ID'])?->getLanguageId();
	}

	public static function getDefaultSiteId(): ?string
	{
		// using the same cache
		return static::getDefaultSite(['LID', 'LANGUAGE_ID'])?->getLid();
	}

	public static function cleanCache(): void
	{
		parent::cleanCache();
		self::$documentRootCache = [];
	}
}
