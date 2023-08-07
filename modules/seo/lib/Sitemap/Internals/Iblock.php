<?php

namespace Bitrix\Seo\Sitemap\Internals;

use Bitrix\Main\Entity;

class IblockTable extends Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';

	const TYPE_ELEMENT = 'E';
	const TYPE_SECTION = 'S';

	protected static $iblockCache = array();

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_seo_sitemap_iblock';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'SITEMAP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'SITEMAP' => array(
				'data_type' => 'Bitrix\Seo\Sitemap\Internals\SitemapTable',
				'reference' => array('=this.SITEMAP_ID' => 'ref.ID'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\IblockTable',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

	/**
	 * Clears all iblock links on sitemap settings deletion.
	 *
	 * @param int $sitemapId Sitemap settings ID.
	 *
	 * @return void
	 */
	public static function clearBySitemap($sitemapId)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$query = $connection->query("
DELETE
FROM ".self::getTableName()."
WHERE SITEMAP_ID='".intval($sitemapId)."'
");
	}

	/**
	 * Returns array of data for sitemap update due to some iblock action.
	 *
	 * @param array $fields Iblock element or section fields array.
	 * @param string $itemType IblockTable::TYPE_ELEMENT || IblockTable::TYPE_SECTION.
	 *
	 * @return array Array of sitemap settings
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getByIblock($fields, $itemType)
	{
		$sitemaps = array();

		if(!isset(self::$iblockCache[$fields['IBLOCK_ID']]))
		{
			self::$iblockCache[$fields['IBLOCK_ID']] = array();

			$dbRes = self::getList(array(
				'filter' => array(
					'IBLOCK_ID' => $fields['IBLOCK_ID']
				),
				'select' => array('SITEMAP_ID',
					'SITE_ID' => 'SITEMAP.SITE_ID', 'SITEMAP_SETTINGS' => 'SITEMAP.SETTINGS',
					'IBLOCK_CODE' => 'IBLOCK.CODE', 'IBLOCK_XML_ID' => 'IBLOCK.XML_ID',
					'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
					'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
				),
			));

			while($res = $dbRes->fetch())
			{
				self::$iblockCache[$fields['IBLOCK_ID']][] = $res;
			}
		}

		foreach(self::$iblockCache[$fields['IBLOCK_ID']] as $res)
		{
			$sitemapSettings = unserialize($res['SITEMAP_SETTINGS'], ['allowed_classes' => false]);

			$add = false;

			if($itemType == self::TYPE_SECTION)
			{
				$add = self::checkSection(
					$fields['ID'],
					$sitemapSettings['IBLOCK_SECTION_SECTION'][$fields['IBLOCK_ID']],
					$sitemapSettings['IBLOCK_SECTION'][$fields['IBLOCK_ID']]
				);
			}
			else
			{
				if(is_array($fields['IBLOCK_SECTION']) && count($fields['IBLOCK_SECTION']) > 0)
				{
					foreach($fields['IBLOCK_SECTION'] as $sectionId)
					{
						$add = self::checkSection(
							$sectionId,
							$sitemapSettings['IBLOCK_SECTION_ELEMENT'][$fields['IBLOCK_ID']],
							$sitemapSettings['IBLOCK_ELEMENT'][$fields['IBLOCK_ID']]
						);

						if($add)
						{
							break;
						}
					}
				}
				else
				{
					$add = $sitemapSettings['IBLOCK_ELEMENT'][$fields['IBLOCK_ID']] == 'Y';
				}
			}

			if($add)
			{
				$sitemaps[] = array(
					'IBLOCK_CODE' => $res['IBLOCK_CODE'],
					'IBLOCK_XML_ID' => $res['IBLOCK_XML_ID'],
					'DETAIL_PAGE_URL' => $res['DETAIL_PAGE_URL'],
					'SECTION_PAGE_URL' => $res['SECTION_PAGE_URL'],
					'SITE_ID' => $res['SITE_ID'],
					'PROTOCOL' => $sitemapSettings['PROTO'] == 1 ? 'https' : 'http',
					'DOMAIN' => $sitemapSettings['DOMAIN'],
					'ROBOTS' => $sitemapSettings['ROBOTS'],
					'SITEMAP_DIR' => $sitemapSettings['DIR'],
					'SITEMAP_FILE' => $sitemapSettings['FILENAME_INDEX'],
					'SITEMAP_FILE_IBLOCK' => $sitemapSettings['FILENAME_IBLOCK'],
				);
			}
		}

		return $sitemaps;
	}

	/**
	 * Checks if section $sectionId should be added to sitemap.
	 *
	 * @param int $sectionId Section ID.
	 * @param array $sectionSettings Sitemap section settings array.
	 * @param bool $defaultValue Default value for situation of settings absence.
	 *
	 * @return bool
	 */
	public static function checkSection($sectionId, $sectionSettings, $defaultValue)
	{
		$value = $defaultValue;

		if(is_array($sectionSettings) && count($sectionSettings) > 0)
		{
			while ($sectionId > 0)
			{
				if(isset($sectionSettings[$sectionId]))
				{
					$value = $sectionSettings[$sectionId];
					break;
				}

				$dbRes = \CIBlockSection::getList(array(), array('ID' => $sectionId), false, array('ID', 'IBLOCK_SECTION_ID'));
				$section = $dbRes->fetch();

				$sectionId = $section["IBLOCK_SECTION_ID"];
			}
		}

		return $value === 'Y';
	}
}
