<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Error;
use \Bitrix\Landing\Node\Component;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Main\Loader;
use \Bitrix\Main\UrlPreview\UrlPreview;

class Utils
{
	/**
	 * Entity type catalog.
	 */
	const TYPE_CATALOG = 'catalog';

	/**
	 * Entity type catalog element.
	 */
	const TYPE_CATALOG_ELEMENT = 'element';

	/**
	 * Entity type section.
	 */
	const TYPE_CATALOG_SECTION = 'section';

	/**
	 * Entity type any.
	 */
	const TYPE_CATALOG_ALL = 'all';

	/**
	 * Get meta-data by URL.
	 * @param string $url Url.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getUrlPreview($url)
	{
		$result = new PublicActionResult();

		if (is_string($url) && $url !== '')
		{
			$urlPreview = new UrlPreview();
			$result->setResult(
				$urlPreview->getMetadataByUrl($url)
			);
		}
		else
		{
			$error = new Error;
			$error->addError('EMPTY_URL', 'Empty URL');
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Search in the catalog.
	 * @param null $query Query string.
	 * @param string $type Search type.
	 * @param int $iblock Iblock id optional.
	 * @param int $siteId Site id optional.
	 * @return PublicActionResult|\CIBlockResult|int
	 */
	public static function catalogSearch($query = null, $type = self::TYPE_CATALOG_ALL, $iblock = null, $siteId = null)
	{
		$publicResult = new PublicActionResult();

		if (!$iblock)
		{
			$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
				$siteId
			);
			$iblockId = $settings['IBLOCK_ID'];
		}
		else
		{
			$iblockId = $iblock;
		}

		if (!Loader::includeModule('iblock'))
		{
			$publicResult->setResult([]);
			return $publicResult;
		}

		$data = [];

		// make some magic
		$filter = [
			'IBLOCK_ID' => $iblockId,
			array_merge(
				['LOGIC' => 'AND'],
				array_map(function($fragment) {
					return ['%NAME' => trim($fragment)];
				}, explode(' ', trim($query)))
			)
		];

		// search in all catalog or in element
		if (
			$type === self::TYPE_CATALOG_ALL ||
			$type === self::TYPE_CATALOG_ELEMENT)
		{
			$order = [];
			$groupBy = false;
			$navParams = false;
			$select = [
				'ID', 'NAME', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE'
			];

			$result = \CIBlockElement::getList(
				$order, $filter, $groupBy, $navParams, $select
			);
			while ($item = $result->fetch())
			{
				$chain = [];
				static::makeCatalogEntityNavChain(
					$item['IBLOCK_SECTION_ID'],
					$chain
				);
				$data[] = [
					'name' => $item['NAME'],
					'id' => $item['ID'],
					'image' => \CFile::getPath($item['DETAIL_PICTURE']),
					'type' => self::TYPE_CATALOG,
					'subType' => self::TYPE_CATALOG_ELEMENT,
					'chain' => $chain
				];
			}
		}
		// search in all catalog or in section
		if (
			$type === self::TYPE_CATALOG_ALL ||
			$type === self::TYPE_CATALOG_SECTION
		)
		{
			$order = [];
			$select = [
				'ID', 'NAME', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE'
			];
			$filter = [
				'IBLOCK_ID' => $iblockId, '%NAME' => trim($query)
			];
			$count = false;

			$sectResult = \CIBlockSection::GetList($order, $filter, $count, $select);
			while ($item = $sectResult->Fetch())
			{
				$chain = [];
				static::makeCatalogEntityNavChain(
					$item['IBLOCK_SECTION_ID'],
					$chain
				);
				$data[] = [
					'name' => trim($item['NAME']),
					'id' => $item['ID'],
					'image' => '',
					'type' => self::TYPE_CATALOG,
					'subType' => self::TYPE_CATALOG_SECTION,
					'chain' => !empty($chain) ? $chain : ['/']
				];
			}
		}


		$publicResult->setResult($data);

		return $publicResult;
	}


	/**
	 * Makes nav chain of catalog entity.
	 * @param int $sectionId Section id.
	 * @param array $chain Chain array.
	 * @return void
	 */
	protected static function makeCatalogEntityNavChain($sectionId, array &$chain)
	{
		if ($sectionId !== null)
		{
			$section = self::getCatalogEntity(
				$sectionId,
				self::TYPE_CATALOG_SECTION
			);
			array_unshift($chain, $section['name']);
			static::makeCatalogEntityNavChain(
				$section['sectionId'],
				$chain
			);
		}
	}

	/**
	 * Gets catalog element or section by id.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return array
	 */
	protected static function getCatalogEntity($entityId, $entityType)
	{
		$result = null;

		if (Loader::includeModule('iblock'))
		{
			$isElement = $entityType == self::TYPE_CATALOG_ELEMENT;
			if ($isElement)
			{
				$res = \CIBlockElement::getList(
					array(),
					array(
						'ID' => $entityId,
						'CHECK_PERMISSIONS' => 'Y'
					),
					false,
					array(
						'nTopCount' => 1
					),
					array(
						'ID', 'NAME', 'DETAIL_PICTURE', 'IBLOCK_SECTION_ID'
					)
				);
			}
			else
			{
				$res = \CIBlockSection::getList(
					array(),
					array(
						'ID' => $entityId,
						'CHECK_PERMISSIONS' => 'Y'
					),
					false,
					array(
						'ID', 'NAME', 'IBLOCK_SECTION_ID'
					),
					array(
						'nTopCount' => 1
					)
				);
			}
			if ($entity = $res->fetch())
			{
				$chain = array();
				static::makeCatalogEntityNavChain(
					$entity['IBLOCK_SECTION_ID'],
					$chain
				);
				$result = array(
					'id' => $entity['ID'],
					'name' => $entity['NAME'],
					'sectionId' => $entity['IBLOCK_SECTION_ID'],
					'image' => $isElement
								? \CFile::getPath($entity['DETAIL_PICTURE'])
								: '',
					'type' => self::TYPE_CATALOG,
					'subType' => $entityType,
					'chain' => $chain
				 );
			}
		}

		return $result;
	}


	/**
	 * Gets catalog element by id.
	 * @param int $elementId Element id.
	 * @return PublicActionResult
	 */
	public static function getCatalogElement($elementId)
	{
		$response = new PublicActionResult();

		$element = self::getCatalogEntity(
			$elementId,
			self::TYPE_CATALOG_ELEMENT
		);

		if ($element)
		{
			$response->setResult($element);
		}
		else
		{
			$response->setError(new Error());
		}

		return $response;
	}

	/**
	 * Gets catalog section by id.
	 * @param int $sectionId Section id.
	 * @return PublicActionResult
	 */
	public static function getCatalogSection($sectionId)
	{
		$response = new PublicActionResult();

		$element = self::getCatalogEntity(
			$sectionId,
			self::TYPE_CATALOG_SECTION
		);

		if ($element)
		{
			$response->setResult($element);
		}
		else
		{
			$response->setError(new Error());
		}

		return $response;
	}

	/**
	 * Build element/section url.
	 * @param int $elementId Element / section id.
	 * @param string $urlType Type of url (section / detail).
	 * @return string
	 */
	public static function getIblockURL($elementId, $urlType)
	{
		static $urls = array();
		static $settings = array();

		$key = $urlType . '_' . $elementId;

		if (isset($urls[$key]))
		{
			return $urls[$key];
		}

		if (!\Bitrix\Main\Loader::includeModule('iblock'))
		{
			return $urls[$key];
		}

		if (empty($settings))
		{
			$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite();
		}

		$urls[$key] = '#system_catalog';
		$iblockId = $settings['IBLOCK_ID'];

		// build url
		if ($urlType == 'detail' || $urlType == 'element')
		{
			// element additional info
			$res = \Bitrix\Iblock\ElementTable::getList(array(
				'select' => array(
					'ID', 'CODE', 'IBLOCK_SECTION_ID'
				),
				'filter' => array(
					'ID' => $elementId,
					'IBLOCK_ID' => $iblockId
				)
			));
			if (!($element = $res->fetch()))
			{
				return $urls[$key];
			}
			// build url
			$urls[$key] .= 'item/' . $element['CODE'] . '/';
		}
		elseif ($urlType == 'section')
		{
			$res = \CIBlockSection::getNavChain(
				$iblockId,
				$elementId
			);
			while ($row = $res->fetch())
			{
				$urls[$key] .= $row['CODE'] . '/';
			}
		}

		return $urls[$key];
	}

	/**
	 * Save some internal settings.
	 * @param array $settings Settings array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function saveSettings(array $settings)
	{
		static $internal = true;

		$result = new PublicActionResult();
		$result->setResult(true);

		foreach ($settings as $key => $value)
		{
			\Bitrix\Main\Config\Option::set('landing', $key, $value);
		}

		return $result;
	}
}