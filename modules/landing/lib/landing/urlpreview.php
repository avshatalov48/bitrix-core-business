<?php
namespace Bitrix\Landing\Landing;

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Loader;

class UrlPreview
{
	/**
	 * Returns preview for landing.
	 * @param int $landingId Landing id.
	 * @return array
	 */
	public static function getPreview(int $landingId): array
	{
		static $cached = [];

		if (array_key_exists($landingId, $cached))
		{
			return $cached[$landingId];
		}

		$result = [];

		$res = Landing::getList([
			'select' => [
				'ID',
				'TITLE',
				'SITE_TYPE' => 'SITE.TYPE'
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
	 * Returns HTML code for page preview.
	 * @param array $params Params data.
	 * @return string|null
	 */
	public static function buildPreview(array $params): ?string
	{
		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		if (!isset($params['URL']))
		{
			$params['URL'] = Manager::getPublicationPath() . $params['URL'];
		}

		$landingId = self::resolveLandingId($params['URL']);
		if ($landingId)
		{
			ob_start();
			Manager::getApplication()->includeComponent(
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

		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		if (!isset($params['URL']))
		{
			$params['URL'] = Manager::getPublicationPath() . ($params['knowledgeCode'] ?? '');
		}

		$landingId = self::resolveLandingId($params['URL']);

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

	public static function getImRich(array $params)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		if (!class_exists('\Bitrix\Im\V2\Entity\Url\RichData'))
		{
			return false;
		}

		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		if (!isset($params['URL']))
		{
			$params['URL'] = Manager::getPublicationPath() . ($params['knowledgeCode'] ?? '');
		}

		$landingId = self::resolveLandingId($params['URL']);

		if ($landingId)
		{
			$preview = self::getPreview($landingId);
			if ($preview)
			{
				$rich = new \Bitrix\Im\V2\Entity\Url\RichData();
				$rich
					->setName($preview['TITLE'])
					->setDescription($preview['DESCRIPTION'])
					->setLink($preview['URL'])
					->setPreviewUrl($preview['PICTURE'])
					->setType(\Bitrix\Im\V2\Entity\Url\RichData::LANDING_TYPE)
				;
				return $rich;
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
	public static function checkUserReadAccess(array $params, int $userId): bool
	{
		if (isset($params['scope']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$params['scope']
			);
		}

		if (!isset($params['URL']))
		{
			$params['URL'] = Manager::getPublicationPath() . ($params['knowledgeCode'] ?? '');
		}

		return self::resolveLandingId($params['URL']) !== null;
	}

	/**
	 * Resolve site id by landing path.
	 * @param string $landingPath Landing url.
	 * @return int|null
	 */
	protected static function resolveSiteId(string $landingPath): ?int
	{
		$publicPath = Manager::getPublicationPath();

		if ($landingPath[0] !== '/')
		{
			$urlParts = parse_url($landingPath);
			$landingPath = $urlParts['path'] ?? '';
		}

		if (mb_strpos($landingPath, $publicPath) === 0)
		{
			$landingPath = mb_substr($landingPath, mb_strlen($publicPath));
			$pathChunks = explode('/', $landingPath);
			if (!empty($pathChunks[0]))
			{
				$res = Site::getList([
					'select' => [
						'ID'
					],
					'filter' => [
						'=CODE' => '/' . $pathChunks[0] . '/'
					]
				]);
				if ($row = $res->fetch())
				{
					return $row['ID'];
				}
			}
		}

		return null;
	}

	/**
	 * Resolve landing id by landing path.
	 * @param string $landingPath Landing url.
	 * @return int|null
	 */
	public static function resolveLandingId(string $landingPath): ?int
	{
		$landingId = null;

		if ($landingPath[0] !== '/')
		{
			$urlParts = parse_url($landingPath);
			$landingPath = $urlParts['path'] ?? '';
		}

		if ($landingPath)
		{
			$siteId = self::resolveSiteId($landingPath);
			if ($siteId)
			{
				$landingId = Landing::resolveIdByPublicUrl($landingPath, $siteId);
			}
		}

		return $landingId;
	}
}