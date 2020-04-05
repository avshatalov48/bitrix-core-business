<?php
namespace Bitrix\Landing\Landing;

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\File;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Loader;

class UrlPreview
{
	/**
	 * Gets preview for landing.
	 * @param int $landingId Landing id.
	 * @return array
	 */
	public static function getPreview($landingId)
	{
		static $cached = [];

		if (array_key_exists($landingId, $cached))
		{
			return $cached[$landingId];
		}

		$result = [];
		$landingId = intval($landingId);

		$res = Landing::getList([
			'select' => [
				'ID',
				'TITLE',
				'=SITE_TYPE' => 'SITE.TYPE'
			],
			'filter' => [
				'ID' => $landingId
			]
		]);
		if ($row = $res->fetch())
		{
			$landing = Landing::createInstance(0);
			$row['URL'] = $landing->getPublicUrl($landingId);
			$row['DESCRIPTION'] = '';
			$row['PICTURE'] = '';

			// gets title and description with hight priority
			$hookData = Hook::getForLandingRow($row['ID']);
			if (isset($hookData['METAOG']['TITLE']))
			{
				$row['TITLE'] = $hookData['METAOG']['TITLE'];
				if (isset($hookData['METAOG']['DESCRIPTION']))
				{
					$row['DESCRIPTION'] = $hookData['METAOG']['DESCRIPTION'];
				}
			}
			else if (isset($hookData['METAMAIN']['TITLE']))
			{
				$row['TITLE'] = $hookData['METAMAIN']['TITLE'];
				if (isset($hookData['METAMAIN']['DESCRIPTION']))
				{
					$row['DESCRIPTION'] = $hookData['METAMAIN']['DESCRIPTION'];
				}
			}
			if (isset($hookData['METAOG']['IMAGE']))
			{
				$row['PICTURE'] = $hookData['METAOG']['IMAGE'];
				if (intval($row['PICTURE']) > 0)
				{
					$row['PICTURE'] = File::getFilePath($row['PICTURE']);
				}
			}

			$result = $row;
		}

		$cached[$landingId] = $result;

		return $cached[$landingId];
	}

	/**
	 * Returns landing id by site path.
	 * @param string $code Site path.
	 * @return int|null
	 */
	public static function getPreviewByCode($code)
	{
		return self::detectByCode($code);
	}

	/**
	 * Gets landing id by url part.
	 * @param string $code Url part.
	 * @return int|null
	 */
	protected static function detectByCode($code)
	{
		$id = null;
		$siteCode = $folderCode = $pageCode = null;

		$code = trim(trim($code), '/');
		$urlParts = explode('/', $code);

		// parse url
		if (isset($urlParts[0]))
		{
			$siteCode = '/' . $urlParts[0] . '/';
		}
		else
		{
			return $id;
		}
		if (isset($urlParts[1]) && isset($urlParts[2]))
		{
			$folderCode = $urlParts[1];
			$pageCode = $urlParts[2];
		}
		else if (isset($urlParts[1]))
		{
			$pageCode = $urlParts[1];
		}

		// fill filter
		$filter = [];
		$runtime = [];
		if ($pageCode)
		{
			$filter['=CODE'] = $pageCode;
		}
		else // try get index page of site
		{
			$res = Site::getList([
				'select' => [
					'LANDING_ID_INDEX'
				],
				'filter' => [
					'=CODE' => $siteCode
				]
			]);
			if ($row = $res->fetch())
			{
				$id = $row['LANDING_ID_INDEX'];
			}
			return $id;
		}
		if ($folderCode)
		{
			$filter['=FOLDER_PAGE.CODE'] = $folderCode;
			$runtime = [
				new Entity\ReferenceField(
					'FOLDER_PAGE',
					'\Bitrix\Landing\Internals\LandingTable',
					[
						'this.FOLDER_ID' => 'ref.ID'
					]
				)
			];
		}
		else
		{
			$filter['FOLDER_ID'] = false;
		}
		if ($siteCode)
		{
			$filter['=SITE.CODE'] = $siteCode;
		}

		if (!$filter)
		{
			return $id;
		}

		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => $filter,
			'runtime' => $runtime
		]);
		if ($row = $res->fetch())
		{
			$id = $row['ID'];
		}

		return $id;
	}

	/**
	 * Detect page by codes array.
	 * @param array $params Params array.
	 * @return int|null
	 */
	protected static function routeCodes($params)
	{
		static $detected = [];

		$urlCodes = [];
		$expectedCodes = ['siteCode', 'folderCode', 'pageCode'];

		if (!isset($params['siteCode']))
		{
			return null;
		}

		foreach ($expectedCodes as $code)
		{
			if (isset($params[$code]) && is_string($params[$code]))
			{
				$urlCodes[] = $params[$code];
			}
		}

		if ($urlCodes)
		{
			$url = implode('/', $urlCodes);
			if (!array_key_exists($url, $detected))
			{
				$detected[$url] = self::detectByCode($url);
			}
			return $detected[$url];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Prepares params for different scopes.
	 * @param array &$params Params.
	 * @return void
	 */
	protected static function prepareParams(array &$params)
	{
		if (
			!isset($params['siteCode']) ||
			$params['siteCode'] != 'group'
		)
		{
			return;
		}

		$params['scope'] = $params['siteCode'];

		if (
			isset($params['additionalCode']) &&
			isset($params['pageCode']) &&
			isset($params['folderCode'])
		)
		{
			$params['siteCode'] = $params['folderCode'];
			$params['folderCode'] = $params['pageCode'];
			$params['pageCode'] = $params['additionalCode'];
		}
		else if (isset($params['folderCode']))
		{
			$params['siteCode'] = $params['folderCode'];
			unset($params['folderCode']);
		}
		else if (isset($params['pageCode']))
		{
			$params['siteCode'] = $params['pageCode'];
			unset($params['pageCode']);
		}
	}

	/**
	 * Returns HTML code for page preview.
	 * @param array $params Expected keys: siteCode[, folderCode, pageCode].
	 * @return string
	 */
	public static function buildPreview(array $params)
	{
		global $APPLICATION;

		self::prepareParams($params);

		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		$landingId = self::routeCodes($params);

		if ($landingId)
		{
			ob_start();
			$APPLICATION->includeComponent(
				'bitrix:landing.socialnetwork.preview',
				'',
				[
					'LANDING_ID' => $landingId
				]
			);
			return ob_get_clean();
		}
		return null;
	}

	/**
	 * Returns attach to display in the messenger.
	 * @param array $params Expected keys: siteCode[, folderCode, pageCode].
	 * @return \CIMMessageParamAttach | false
	 */
	public static function getImAttach(array $params)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		self::prepareParams($params);

		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		$landingId = self::routeCodes($params);

		if ($landingId)
		{
			$preview = self::getPreview($landingId);
			if ($preview)
			{
				$attach = new \CIMMessageParamAttach(1, '#E30000');
				$attach->addLink([
					'NAME' => $preview['TITLE'],
					'DESC' => $preview['DESCRIPTION'],
					'LINK' => $preview['URL'],
					'PREVIEW' => $preview['PICTURE']
				]);
				return $attach;
			}
		}

		return false;
	}

	/**
	 * Returns true if current user has read access to the page.
	 * @param array $params Expected keys: siteCode[, folderCode, pageCode].
	 * @param int $userId Current user's id.
	 * @return bool
	 */
	public static function checkUserReadAccess(array $params, $userId)
	{
		self::prepareParams($params);

		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		$landingId = self::routeCodes($params);
		return $landingId !== null;
	}
}