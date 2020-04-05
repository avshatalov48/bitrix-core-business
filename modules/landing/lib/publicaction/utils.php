<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Error;
use Bitrix\Landing\Node\Component;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Main\UrlPreview\UrlPreview;

class Utils
{
	const CATALOG_SECTION_IMAGE = '/bitrix/images/landing/folder.svg';
	const TYPE_CATALOG = 'catalog';
	const TYPE_CATALOG_ELEMENT = 'element';
	const TYPE_CATALOG_SECTION = 'section';
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
	 * @param null $query
	 * @param string $type
	 * @return PublicActionResult|\CIBlockResult|int
	 */
	public static function catalogSearch($query = null, $type = self::TYPE_CATALOG_ALL)
	{
		$iblockId = Component::getIblockParams('id');

		$data = [];

		$filter = [
			'IBLOCK_ID' => $iblockId,
			array_merge(
				['LOGIC' => 'AND'],
				array_map(function($fragment) {
					return ['%NAME' => trim($fragment)];
				}, explode(' ', trim($query)))
			)
		];


		if ($type === self::TYPE_CATALOG_ALL ||
			$type === self::TYPE_CATALOG_ELEMENT)
		{
			$order = [];
			$groupBy = false;
			$navParams = false;
			$select = ["ID", "NAME", "IBLOCK_SECTION_ID", "DETAIL_PICTURE"];

			$result = \CIBlockElement::getList($order, $filter, $groupBy, $navParams, $select);

			while ($item = $result->Fetch())
			{
				$image = \CFile::GetPath($item["DETAIL_PICTURE"]);
				$chain = [];
				static::makeCatalogEntityNavChain(
					$item['IBLOCK_SECTION_ID'],
					$chain
				);

				$data[] = [
					'name' => trim($item['NAME']),
					'id' => $item['ID'],
					'image' => $image,
					'type' => self::TYPE_CATALOG,
					'subType' => self::TYPE_CATALOG_ELEMENT,
					'chain' => $chain
				];
			}
		}

		if ($type === self::TYPE_CATALOG_ALL ||
			$type === self::TYPE_CATALOG_SECTION)
		{
			$order = [];
			$filter = ['IBLOCK_ID' => $iblockId, '%NAME' => trim($query)];
			$count = false;
			$select = ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE'];

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
					'image' => self::CATALOG_SECTION_IMAGE,
					'type' => self::TYPE_CATALOG,
					'subType' => self::TYPE_CATALOG_SECTION,
					'chain' => !empty($chain) ? $chain : ['/']
				];
			}
		}

		$result = new PublicActionResult();
		$result->setResult($data);

		return $result;
	}


	/**
	 * Makes nav chain of catalog entity
	 * @param int $sectionId
	 * @param array $chain
	 */
	protected static function makeCatalogEntityNavChain($sectionId, &$chain)
	{
		if ($sectionId !== null)
		{
			$section = \CIBlockSection::getByID($sectionId)->fetch();
			array_unshift($chain, trim($section['NAME']));

			static::makeCatalogEntityNavChain(
				$section['IBLOCK_SECTION_ID'],
				$chain
			);
		}
	}


	/**
	 * Gets catalog element by id
	 * @param $elementId
	 * @return PublicActionResult
	 */
	public static function getCatalogElement($elementId)
	{
		$elementRes = \CIBlockElement::getById($elementId);
		$response = new PublicActionResult();

		if ($elementRes)
		{
			$element = $elementRes->fetch();
			$image = \CFile::GetPath($element["DETAIL_PICTURE"]);
			$chain = [];
			static::makeCatalogEntityNavChain(
				$element['IBLOCK_SECTION_ID'],
				$chain
			);

			$response->setResult([
				'id' => $element['ID'],
				'name' => trim($element['NAME']),
				'image' => $image,
				'type' => self::TYPE_CATALOG,
				'subType' => self::TYPE_CATALOG_ELEMENT,
				'chain' => $chain
			]);

			return $response;
		}

		$response->setError(new Error());
		return $response;
	}

	/**
	 * Gets catalog section by id
	 * @param int $sectionId
	 * @return PublicActionResult
	 */
	public static function getCatalogSection($sectionId)
	{
		$elementRes = \CIBlockSection::getById($sectionId);
		$response = new PublicActionResult();

		if ($elementRes)
		{
			$element = $elementRes->fetch();
			$chain = [];
			static::makeCatalogEntityNavChain(
				$element['IBLOCK_SECTION_ID'],
				$chain
			);

			$response->setResult([
				'id' => $element['ID'],
				'name' => trim($element['NAME']),
				'image' => self::CATALOG_SECTION_IMAGE,
				'type' => self::TYPE_CATALOG,
				'subType' => self::TYPE_CATALOG_SECTION,
				'chain' => $chain
			]);

			return $response;
		}

		$response->setError(new Error());
		return $response;
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