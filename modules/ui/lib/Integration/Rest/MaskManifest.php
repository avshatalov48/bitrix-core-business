<?php
namespace Bitrix\UI\Integration\Rest;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Event;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

class MaskManifest
{
	private const USER_MASKS_MANIFEST_CODE = 'ui_masks';
	private const SYSTEM_MASKS_MANIFEST_CODE = 'ui_masks_system';

	public const CODE = 'ui_masks';

	private static $manifestList = [
		self::USER_MASKS_MANIFEST_CODE,
		self::SYSTEM_MASKS_MANIFEST_CODE
	];
	private static $entityList = [
		'UI_MASK' => 10000,
	];

	/**
	 * Returns list of available manifests.
	 */
	public static function onRestApplicationConfigurationGetManifest(): array
	{
		return [[
			'CODE' => static::USER_MASKS_MANIFEST_CODE,
			'VERSION' => 1,
			'ACTIVE' => 'Y',
			'USES' => [
				'ui_masks',
			],
			'DISABLE_CLEAR_FULL' => 'N',
			'DISABLE_NEED_START_BTN' => 'Y',
			'SKIP_CLEARING' => 'Y',
			'PLACEMENT' => [],
			'TITLE' => Loc::getMessage('UI_REST_MAIN_TITLE_PAGE'),
			'DESCRIPTION' => Loc::getMessage('UI_REST_MAIN_DESCRIPTION_PAGE'),
			'IMPORT_TITLE_PAGE' => Loc::getMessage('UI_REST_IMPORT_TITLE_PAGE'),
			'IMPORT_TITLE_BLOCK' => Loc::getMessage('UI_REST_IMPORT_TITLE_BLOCK'),
			'IMPORT_DESCRIPTION_UPLOAD' => Loc::getMessage('UI_REST_IMPORT_ACTION_DESCRIPTION'),
			'IMPORT_DESCRIPTION_START' => Loc::getMessage('UI_REST_IMPORT_DESCRIPTION_START'),
			'EXPORT_TITLE_PAGE' => Loc::getMessage('UI_REST_EXPORT_TITLE_PAGE'),
			'EXPORT_TITLE_BLOCK' => Loc::getMessage('UI_REST_EXPORT_TITLE_BLOCK'),
			'EXPORT_ACTION_DESCRIPTION' => Loc::getMessage('UI_REST_EXPORT_ACTION_DESCRIPTION'),
			'IMPORT_FINISH_DESCRIPTION' => Loc::getMessage('UI_REST_IMPORT_FINISH_DESCRIPTION'),
			'IMPORT_INSTALL_FINISH_TEXT' => '',
			'REST_IMPORT_AVAILABLE' => 'Y',
			'ACCESS' => [
				'MODULE_ID' => 'ui',
				'CALLBACK' => [
					static::class,
					'onCheckAccess'
				]
			]
		]];
	}
	/**
	 * Checks access to export and import.
	 * @param string $type Export or import.
	 * @param array $manifest Manifest data.
	 * @return array
	 */
	public static function onCheckAccess(string $type, array $manifest): array
	{
		return [
			'result' => true
		];
	}

	/**
	 * Step before active actions: import/export.
	 */
	public static function OnRestApplicationConfigurationGetManifestSetting(Event $event): ?array
	{
		$manifestCode = $event->getParameter('CODE');
		if (!in_array($manifestCode, static::$manifestList))
		{
			return null;
		}

		$result =  [
			'SETTING' => $event->getParameter('SETTINGS'),
			'NEXT' => false
		];

		return $result;
	}

	/**
	 * Returns entity list with sorting
	 */
	public static function onRestApplicationConfigurationEntity(): array
	{
		return static::$entityList;
	}

	public static function OnRestApplicationConfigurationExport(Event $event)
	{
		//region Check manifests intersection
		$manifest = $event->getParameter('MANIFEST');
		$intersection = array_intersect($manifest['USES'], static::$manifestList);
		if (!$intersection)
		{
			return null;
		}
		//endregion

		$entityCode = $event->getParameter('CODE');
		if ($entityCode === 'UI_MASK')
		{
			return MaskExport::fulfill($event);
		}
		return null;
	}

	public static function onRestApplicationConfigurationImport(Event $event): ?array
	{
		//region Check manifests intersection
		$manifest = $event->getParameter('IMPORT_MANIFEST');
		$intersection = array_intersect($manifest['USES'], static::$manifestList);
		if (!$intersection)
		{
			return null;
		}
		//endregion
		$entityCode = $event->getParameter('CODE');
		// TODO: Remove 2 string
		// $event->setParameter('APP_ID', 6);
		// $event->setParameter('CONTEXT', 'app6');
		if ($entityCode === 'UI_MASK')
		{
			if (preg_match('/app(\d+)/is', $event->getParameter('CONTEXT')))
			{
				return MaskImportApp::fulfill($event);
			}
			return MaskImportPersonal::fulfill($event);
		}
		return null;
	}

	public static function bind()
	{
		$eventManager = Main\EventManager::getInstance();
		foreach ([
			'onRestApplicationConfigurationGetManifest',
			'onRestApplicationConfigurationGetManifestSetting',
			'onRestApplicationConfigurationExport',
			'onRestApplicationConfigurationEntity',
			'onRestApplicationConfigurationImport',
		] as $eventCode)
		{
			$eventManager->registerEventHandler('rest', $eventCode, 'ui', static::class, $eventCode);
		}
	}
}