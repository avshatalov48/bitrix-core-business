<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Demos as DemoCore;
use \Bitrix\Landing\PublicActionResult;

class Cloud
{
	/**
	 * Get blocks from repository.
	 * @return PublicActionResult
	 */
	public static function getRepository()
	{
		return Block::getRepository(null, true);
	}

	/**
	 * Returns demo sites or pages.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param bool $isPage Returns templates for page section or not.
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	protected static function getDemoItemList(string $type, bool $isPage, array $filter = []): PublicActionResult
	{
		if ($isPage)
		{
			$result = Demos::getPageList($type)->getResult();
		}
		else
		{
			$result = Demos::getSiteList($type)->getResult();
		}

		// we need only used in public templates
		if (
			$result &&
			isset($filter['used_in_public']) &&
			$filter['used_in_public'] == 'Y'
		)
		{
			$resultNotPublic = $result;
			$res = Site::getList([
				'select' => [
					'ID', 'TITLE', 'TPL_CODE'
				],
				'filter' => [
					'=ACTIVE' => 'Y',
					'=TPL_CODE' => array_keys($resultNotPublic)
				]
			]);
			while ($row = $res->fetch())
			{
				unset($resultNotPublic[$row['TPL_CODE']]);
			}
			foreach ($resultNotPublic as $key => $foo)
			{
				unset($result[$key]);
			}
		}

		// we need't local templates, only from rest
		if (
			$result &&
			isset($filter['only_rest']) &&
			$filter['only_rest'] == 'Y'
		)
		{
			foreach ($result as $key => $item)
			{
				if (!$item['REST'])
				{
					unset($result[$key]);
				}
			}
		}

		$actionResult = new PublicActionResult;
		$actionResult->setResult($result);

		return $actionResult;
	}

	/**
	 * Returns demo sites.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	public static function getDemoSiteList(string $type, array $filter = []): PublicActionResult
	{
		return self::getDemoItemList($type, false, $filter);
	}

	/**
	 * Returns demo pages.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	public static function getDemoPageList(string $type, array $filter = []): PublicActionResult
	{
		return self::getDemoItemList($type, true, $filter);
	}

	/**
	 * Get preview of url by code.
	 * @param string $code Code of page.
	 * @param string $type Code of content.
	 * @return PublicActionResult
	 */
	public static function getUrlPreview($code, $type)
	{
		return Demos::getUrlPreview($code, $type);
	}

	/**
	 * Returns template items for the application.
	 * @param string $appCode Application code.
	 * @return PublicActionResult
	 */
	public static function getAppItems(string $appCode): PublicActionResult
	{
		return Demos::getList([
			'filter' => [
				'=APP_CODE' => $appCode
			]
		]);
	}

	/**
	 * Returns single item manifest.
	 * @param int $id Application item id.
	 * @return PublicActionResult
	 */
	public static function getAppItemManifest(int $id): PublicActionResult
	{
		$result = new PublicActionResult;

		$template = DemoCore::getList([
			'filter' => ['ID' => $id]
		])->fetch();

		if ($template)
		{
			$template['MANIFEST'] = unserialize($template['MANIFEST']);
			$result->setResult($template);
		}

		return $result;
	}
}