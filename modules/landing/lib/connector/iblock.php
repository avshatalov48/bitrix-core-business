<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Landing\Internals\HookDataTable;

class Iblock
{
	/**
	 * Gets element's url in site context.
	 * @param int|string $siteId Site id (or template code used to create site).
	 * @param int $elementId Element id.
	 * @return string
	 */
	public static function getElementUrl($siteId, $elementId): string
	{
		$url = '';

		\Bitrix\Landing\Rights::setGlobalOff();

		if (is_string($siteId))
		{
			$res = \Bitrix\Landing\Site::getList([
				'select' => ['ID'],
				'filter' => ['=TPL_CODE' => $siteId],
				'order' => ['ID' => 'desc']
			]);
			if ($row = $res->fetch())
			{
				$siteId = $row['ID'];
			}
		}

		$syspages = \Bitrix\Landing\Syspage::get($siteId);
		if (isset($syspages['catalog']))
		{
			$landing = \Bitrix\Landing\Landing::createInstance(
				$syspages['catalog']['LANDING_ID'],
				['skip_blocks' => true]
			);
			if ($landing->exist())
			{
				$url = \Bitrix\Landing\PublicAction\Utils::getIblockURL(
					$elementId,
					'detail'
				);
				$url = str_replace(
					'#system_catalog',
					$landing->getPublicUrl(),
					$url
				);
				if (mb_substr($url, 0, 1) == '/')
				{
					$url = \Bitrix\Landing\Site::getPublicUrl(
						$landing->getSiteId()
					) . $url;
				}
			}
		}

		\Bitrix\Landing\Rights::setGlobalOn();

		return $url;
	}

	/**
	 * Callback on after delete iblock's section.
	 * @param array $section Section's data.
	 * @return void
	 */
	public static function onAfterIBlockSectionDelete(array $section): void
	{
		if ($section['ID'] ?? null)
		{
			$res = HookDataTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=HOOK' => 'SETTINGS',
					'=CODE' => 'SECTION_ID',
					'=VALUE' => $section['ID']
				]
			]);
			while ($row = $res->fetch())
			{
				HookDataTable::delete($row['ID'])->isSuccess();
			}
		}
	}
}