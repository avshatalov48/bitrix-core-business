<?php
namespace Bitrix\Landing\Transfer;

use \Bitrix\Landing\File;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Restriction;
use \Bitrix\Landing\Site\Type;
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
				'USES' => [$code],
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
				'IMPORT_DESCRIPTION_START' => ' '
			];
		}

		return $manifestList;
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
		$code = $event->getParameter('CODE');

		self::$processing = true;

		if (isset(static::$entityList[$code]))
		{
			return Import\Site::nextStep($event);
		}

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
							LANGUAGE_ID,
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
		$fileId = null;
		$fileData = \CFile::makeFileArray(
			$file['PATH']
		);
		if ($fileData)
		{
			$fileData['name'] = $file['NAME'];
		}

		if ($fileData)
		{
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
