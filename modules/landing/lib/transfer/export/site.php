<?php
namespace Bitrix\Landing\Transfer\Export;

use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Site as SiteCore;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Template;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\PublicAction;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Landing\Transfer\AppConfiguration;
use \Bitrix\Rest\Marketplace;
use \Bitrix\Main\Event;

class Site
{
	/**
	 * Maximum sites for export.
	 */
	const MAX_SITE_FOR_EXPORT = 10;

	/**
	 * File name for step when called site meta data.
	 */
	const FILENAME_EXPORT_STEP_META = 'page_#site_id#_00';

	/**
	 * File name for step when called ste page.
	 */
	const FILENAME_EXPORT_STEP_PAGE = 'page_#site_id#_10_#landing_id#';

	/**
	 * Returns export url for the site.
	 * @param string $type Site type.
	 * @param int $siteId Site id.
	 * @return string
	 */
	public static function getUrl(string $type, int $siteId): string
	{
		if (!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return '';
		}
		return Marketplace\Url::getConfigurationExportElementUrl(
			AppConfiguration::PREFIX_CODE . strtolower($type), $siteId
		);
	}

	/**
	 * Returns applications id by it codes.
	 * @param array $appCodes Applications codes.
	 * @return array
	 */
	protected static function getRestAppIds(array $appCodes): array
	{
		$appIds = [];

		$res = \Bitrix\Rest\AppTable::getList([
			'select' => [
				'ID', 'CODE'
			],
			'filter' => [
				'=CODE' => $appCodes
			]
		]);
		while ($row = $res->fetch())
		{
			$appIds[$row['CODE']] = $row['ID'];
		}

		return $appIds;
	}

	/**
	 * Returns prepare manifest settings for export.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function getInitManifest(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$siteType = substr($code, strlen(AppConfiguration::PREFIX_CODE));
		$siteId = (int)$event->getParameter('ITEM_CODE');
		Type::setScope($siteType);

		// set uses app to main manifest
		$usedApp = PublicAction::getRestStat(
			true, false,
			$siteId ? ['SITE_ID' => $siteId] : []
		);
		$usedApp = array_merge(
			array_keys($usedApp[PublicAction::REST_USAGE_TYPE_BLOCK]),
			array_keys($usedApp[PublicAction::REST_USAGE_TYPE_PAGE])
		);
		$usedApp = array_unique($usedApp);
		if ($usedApp)
		{
			$usedApp = array_values(self::getRestAppIds($usedApp));
		}

		// get finish id if it is total export
		if ($siteId)
		{
			return [
				'SETTING' => [
					'SITE_ID' => $siteId,
					'SITE_TYPE' => $siteType,
					'APP_USES_REQUIRED' => $usedApp
				],
				'NEXT' => false
			];
		}
		$lastSiteId = 0;
		$res = SiteCore::getList([
			'select' => [
				'ID'
			],
			'order' => [
				'ID' => 'asc'
			],
			'limit' => self::MAX_SITE_FOR_EXPORT
		]);
		while ($row = $res->fetch())
		{
			$lastSiteId = $row['ID'];
		}
		return [
			'SETTING' => [
				'FINISH_ID' => $lastSiteId,
				'APP_USES_REQUIRED' => $usedApp
			],
			'NEXT' => false
		];
	}

	/**
	 * Get all site's folders in new format.
	 * @param int $siteId Site id.
	 * @return array
	 */
	private static function getFolders(int $siteId): array
	{
		$folders = [];

		foreach (SiteCore::getFolders($siteId) as $folder)
		{
			$folders[$folder['ID']] = [
				'PARENT_ID' => $folder['PARENT_ID'],
				'ACTIVE' => $folder['ACTIVE'],
				'TITLE' => $folder['TITLE'],
				'CODE' => $folder['CODE'],
				'INDEX_ID' => $folder['INDEX_ID']
			];
		}

		return $folders;
	}

	/**
	 * Exports site meta information.
	 * @param int $siteId Site id.
	 * @return array|null
	 */
	protected static function exportSiteMeta(int $siteId): ?array
	{
		$files = [];

		$site = SiteCore::getList([
			'filter' => [
				'ID' => $siteId
			]
		])->fetch();
		if (!$site)
		{
			return null;
		}

		$site['DATE_CREATE'] = (string)$site['DATE_CREATE'];
		$site['DATE_MODIFY'] = (string)$site['DATE_MODIFY'];
		$site['SYS_PAGES'] = \Bitrix\Landing\Syspage::get($siteId);
		$site['FOLDERS_NEW'] = self::getFolders($siteId);

		// layout templates
		$site['TEMPLATES'] = [];
		$res = Template::getList([
			'select' => [
				'ID', 'XML_ID'
			]
		]);
		while ($row = $res->fetch())
		{
			$site['TEMPLATES'][$row['ID']] = $row['XML_ID'];
		}

		// site layout template
		$site['TEMPLATE_REF'] = [];
		if ($site['TPL_ID'])
		{
			$site['TEMPLATE_REF'] = TemplateRef::getForSite($siteId);
		}

		// additional fields
		$hookFields = [];
		foreach (Hook::getForSite($siteId) as $hookCode => $hook)
		{
			if ($hookCode == 'SETTINGS')
			{
				continue;
			}
			foreach ($hook->getFields() as $fCode => $field)
			{
				$hookCodeFull = $hookCode . '_' . $fCode;
				$hookFields[$hookCodeFull] = $field->getValue();
				if (!$hookFields[$hookCodeFull])
				{
					unset($hookFields[$hookCodeFull]);
				}
				else if (in_array($hookCodeFull, Hook::HOOKS_CODES_FILES))
				{
					if ($hookFields[$hookCodeFull] > 0)
					{
						$files[] = ['ID' => $hookFields[$hookCodeFull]];
					}
				}
			}
		}
		$site['ADDITIONAL_FIELDS'] = $hookFields;

		return [
			'FILE_NAME' => str_replace('#site_id#', $siteId, self::FILENAME_EXPORT_STEP_META),
			'CONTENT' => $site,
			'FILES' => $files
		];
	}

	/**
	 * Process one export step.
	 * @param Event $event Event instance.
	 * @return array
	 */
	public static function nextStep(Event $event): array
	{
		$settings = $event->getParameter('SETTING');
		$manifest = $event->getParameter('MANIFEST');
		$next = $event->getParameter('NEXT');
		$itemCode = (int)$event->getParameter('ITEM_CODE');
		$siteType = substr($manifest['CODE'], strlen(AppConfiguration::PREFIX_CODE));

		Type::setScope($siteType);
		Hook::setEditMode();
		LandingCore::setEditMode();

		if (!$next || $next === 'false'/*bug fix*/)
		{
			$next = [
				'ID' => 0,
				'EXPORTED_SITES_META' => []
			];
		}
		else
		{
			$next = unserialize(htmlspecialcharsback($next), ['allowed_classes' => false]);
		}

		$defaultReturn = [
			'NEXT' => false
		];
		$filter = [
			'>ID' => $next['ID']
		];

		// limit top border, if sites too much
		if (isset($settings['FINISH_ID']))
		{
			$filter['<=SITE_ID'] = $settings['FINISH_ID'];
		}
		// limit current step
		if ($itemCode)
		{
			$filter['SITE_ID'] = $itemCode;
		}

		// pages export
		$res = LandingCore::getList([
			'select' => [
				'ID', 'SITE_ID'
			],
			'filter' => $filter,
			'order' => [
				'ID' => 'asc'
			],
			'limit' => 1
		]);
		if ($row = $res->fetch())
		{
			if (!in_array($row['SITE_ID'], $next['EXPORTED_SITES_META']))
			{
				$exportSiteMeta = self::exportSiteMeta($row['SITE_ID']);
				if (!$exportSiteMeta)
				{
					return $defaultReturn;
				}
				$next['EXPORTED_SITES_META'][] = $row['SITE_ID'];
				$exportSiteMeta['NEXT'] = serialize($next);
				// we'll repeat current step
				return $exportSiteMeta;
			}
			$exportLanding = Landing::exportLanding(
				$row['ID'],
				self::FILENAME_EXPORT_STEP_PAGE
			);
			if (!$exportLanding)
			{
				return $defaultReturn;
			}
			$next['ID'] = $row['ID'];
			$exportLanding['NEXT'] = serialize($next);
			return $exportLanding;
		}

		return $defaultReturn;
	}

	/**
	 * Final step.
	 * @param Event $event Event instance.
	 * @return array
	 */
	public static function onFinish(Event $event): array
	{
		return [];
	}
}