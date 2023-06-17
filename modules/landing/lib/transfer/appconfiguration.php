<?php
namespace Bitrix\Landing\Transfer;

use \Bitrix\Landing\File;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Restriction;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Main\Application;
use \Bitrix\Main\Event;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Rest\Configuration;

Loc::loadMessages(__FILE__);

/**
 * Class AppConfiguration
 * @see rest/dev/configuration/readme.php
 */
class AppConfiguration
{
	/**
	 * Block and component for replace unknown rest blocks.
	 */
	const SYSTEM_BLOCK_REST_PENDING = 'system.rest.pending';
	const SYSTEM_COMPONENT_REST_PENDING = 'bitrix:landing.rest.pending';

	/**
	 * Prefix code.
	 */
	const PREFIX_CODE = 'landing_';

	/**
	 * If transfer are processing.
	 * @var bool
	 */
	protected static $processing = false;

	/**
	 * With which entities we can work.
	 * @var array
	 */
	private static $entityList = [
		'LANDING' => 500
	];

	/**
	 * Additional magic manifest.
	 * @var array
	 */
	private static $accessManifest = [
		'total',
		'landing_page',
		'landing_store',
		'landing_knowledge'
	];

	/**
	 * Context site id (now used within import).
	 * @var null
	 */
	private static $contextSiteId = null;

	/**
	 * Returns true if transfer are processing.
	 * @return bool
	 */
	public static function inProcess(): bool
	{
		return self::$processing;
	}

	/**
	 * Returns known entities.
	 * @return array
	 */
	public static function getEntityList(): array
	{
		return static::$entityList;
	}

	/**
	 * Builds manifests for each placement.
	 * @param Event $event Event instance.
	 * @return array
	 */
	public static function getManifestList(Event $event): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$additional = $request->get('additional');
		$siteId = $additional['siteId'] ?? null;
		$manifestList = [];

		foreach (self::$accessManifest as $code)
		{
			if ($code == 'total')
			{
				continue;
			}
			$langCode = mb_strtoupper(mb_substr($code, mb_strlen(self::PREFIX_CODE)));
			$manifestList[] = [
				'CODE' => $code,
				'VERSION' => 1,
				'ACTIVE' => 'Y',
				'PLACEMENT' => [$code],
				'USES' => [
					$code,
					'app',
				],
				'DISABLE_CLEAR_FULL' => 'Y',
				'DISABLE_NEED_START_BTN' => 'Y',
				'COLOR' => '#ff799c',
				'ICON' => '/bitrix/images/landing/landing_transfer.svg',
				'TITLE' => Loc::getMessage('LANDING_TRANSFER_GROUP_TITLE_' . $langCode),
				//'DESCRIPTION' => Loc::getMessage('LANDING_TRANSFER_GROUP_DESC'),
				'EXPORT_TITLE_PAGE' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_TITLE_BLOCK_' . $langCode),
				'EXPORT_TITLE_BLOCK' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_TITLE_BLOCK_' . $langCode),
				'EXPORT_ACTION_DESCRIPTION' => Loc::getMessage('LANDING_TRANSFER_EXPORT_ACTION_DESCRIPTION_' . $langCode),
				'IMPORT_TITLE_PAGE' => Loc::getMessage('LANDING_TRANSFER_IMPORT_ACTION_TITLE_BLOCK_' . $langCode),
				'IMPORT_TITLE_BLOCK' => Loc::getMessage('LANDING_TRANSFER_IMPORT_ACTION_TITLE_BLOCK_' . $langCode),
				'IMPORT_DESCRIPTION_UPLOAD' => Loc::getMessage('LANDING_TRANSFER_IMPORT_DESCRIPTION_UPLOAD_' . $langCode),
				'IMPORT_DESCRIPTION_START' => ' ',
				'IMPORT_INSTALL_FINISH_TEXT' => '',
				'IMPORT_TITLE_PAGE_CREATE' => Loc::getMessage('LANDING_TRANSFER_IMPORT_ACTION_TITLE_BLOCK_CREATE_' . $langCode),
				'REST_IMPORT_AVAILABLE' => 'Y',
				'SITE_ID' => $siteId,
				'ACCESS' => [
					'MODULE_ID' => 'landing',
					'CALLBACK' => [
						'\Bitrix\Landing\Transfer\AppConfiguration',
						'onCheckAccess'
					]
				]
			];
		}

		return $manifestList;
	}

	/**
	 * Checks access to export and import.
	 * @param string $type Export or import.
	 * @param array $manifest Manifest data.
	 * @return array
	 */
	public static function onCheckAccess(string $type, array $manifest): array
	{
		if ($manifest['CODE'] ?? null)
		{
			$siteType = substr($manifest['CODE'], strlen(AppConfiguration::PREFIX_CODE));
			\Bitrix\Landing\Site\Type::setScope($siteType);
		}

		$siteId = $manifest['SITE_ID'] ?? 0;
		if ($type === 'export')
		{
			$access = in_array(Rights::ACCESS_TYPES['read'], Rights::getOperationsForSite($siteId));
			if ($access)
			{
				$access = !Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['unexportable'], null, false, true);
			}
		}
		else
		{
			$access = Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['create'])
					&& in_array(Rights::ACCESS_TYPES['edit'], Rights::getOperationsForSite($siteId));
		}
		return [
			'result' => $access
		];
	}

	/**
	 * Preparing steps before export start.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function onInitManifest(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$type = $event->getParameter('TYPE');

		self::$processing = true;

		if (in_array($code, static::$accessManifest))
		{
			if ($type == 'EXPORT')
			{
				return Export\Site::getInitManifest($event);
			}
			else if ($type == 'IMPORT')
			{
				return Import\Site::getInitManifest($event);
			}
		}

		return null;
	}

	/**
	 * Export step.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function onEventExportController(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$manifest = $event->getParameter('MANIFEST');
		$access = array_intersect($manifest['USES'], static::$accessManifest);

		self::$processing = true;

		if (Restriction\Manager::isAllowed('limit_sites_transfer'))
		{
			if ($access && isset(static::$entityList[$code]))
			{
				return Export\Site::nextStep($event);
			}
		}

		return null;
	}

	/**
	 * Import step.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function onEventImportController(Event $event): ?array
	{
		self::$contextSiteId = $event->getParameter('RATIO')['LANDING']['SITE_ID'] ?? null;
		$code = $event->getParameter('CODE');

		self::$processing = true;

		Landing::disableCheckUniqueAddress();

		if (isset(static::$entityList[$code]))
		{
			return Import\Site::nextStep($event);
		}

		Landing::enableCheckUniqueAddress();

		return null;
	}

	/**
	 * Final step.
	 * @param Event $event Event instance.
	 * @return array
	 */
	public static function onFinish(Event $event): array
	{
		$type = $event->getParameter('TYPE');
		$code = $event->getParameter('MANIFEST_CODE');

		if (in_array($code, static::$accessManifest))
		{
			if ($type == 'EXPORT')
			{
				// rename file to download
				$context = $event->getParameter('CONTEXT_USER');
				$setting = new Configuration\Setting($context);
				$manifest = $setting->get(Configuration\Setting::SETTING_MANIFEST);
				if (!empty($manifest['SITE_ID']))
				{
					Type::setScope($manifest['SITE_TYPE']);
					$res = Site::getList([
						'select' => [
							'TITLE'
						],
						'filter' => [
							'ID' => $manifest['SITE_ID']
						]
					]);
					if ($row = $res->fetch())
					{
						$structure = new Configuration\Structure($context);
						$structure->setArchiveName(\CUtil::translit(
							trim($row['TITLE']),
							'ru',
							[
								'replace_space' => '_',
								'replace_other' => '_'
							]
						));
					}
				}
				return Export\Site::onFinish($event);
			}
			else if ($type == 'IMPORT')
			{
				return Import\Site::onFinish($event);
			}
		}

		self::$processing = false;

		return [];
	}

	/**
	 * Saves file to DB and returns id ID.
	 * @param array $file File data from getUnpackFile.
	 * @return int|null
	 */
	public static function saveFile(array $file): ?int
	{
		$checkExternal = self::$contextSiteId && ($file['ID'] ?? null);
		$externalId = $checkExternal ? self::$contextSiteId . '_' . $file['ID'] : null;

		if ($externalId)
		{
			$res = \CFile::getList([], ['EXTERNAL_ID' => $externalId]);
			if ($row = $res->fetch())
			{
				return $row['ID'];
			}
		}

		$fileId = null;
		$fileData = \CFile::makeFileArray(
			$file['PATH']
		);

		if ($fileData)
		{
			$fileData['name'] = $file['NAME'];
			$fileData['external_id'] = $externalId;

			if (\CFile::checkImageFile($fileData, 0, 0, 0, array('IMAGE')) === null)
			{
				$fileData['MODULE_ID'] = 'landing';
				$fileData['name'] = File::sanitizeFileName($fileData['name']);
				$fileId = (int)\CFile::saveFile($fileData, $fileData['MODULE_ID']);
				if (!$fileId)
				{
					$fileId = null;
				}
			}
		}

		return $fileId;
	}
}
