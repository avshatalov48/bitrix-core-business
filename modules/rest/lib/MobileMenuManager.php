<?php

namespace Bitrix\Rest;

class MobileMenuManager
{
	public static function onMobileMenuStructureBuilt(array $menu, $context): array
	{
		if (!($context instanceof \Bitrix\Mobile\Context))
		{
			return $menu;
		}

		$marketItems = self::prepareMarketItems();
		$menu = self::addMenuItems($menu, 'marketplace', $marketItems);

		return $menu;
	}

	/**
	 * @param array $menu
	 * @param string $sectionCode
	 * @param array $items
	 * @return array
	 */
	public static function addMenuItems(array $menu, string $sectionCode, array $items): array
	{
		if (empty($items))
		{
			return $menu;
		}

		$sectionIndex = null;
		foreach ($menu as $index => $section)
		{
			if (is_array($section) && isset($section['code']) && $section['code'] === $sectionCode)
			{
				$sectionIndex = $index;
				break;
			}
		}

		if ($sectionIndex !== null)
		{
			if (!isset($menu[$sectionIndex]['items']) || !is_array($menu[$sectionIndex]['items']))
			{
				$menu[$sectionIndex]['items'] = [];
			}

			$menu[$sectionIndex]['items'] = array_merge(
				$menu[$sectionIndex]['items'],
				$items
			);
		}

		return $menu;
	}

	private static function prepareMarketItems(): array
	{
		$arMenuApps = [];
		global $USER;
		$arUserGroupCode = $USER->GetAccessCodes();
		$numLocalApps = 0;

		$dbApps = \Bitrix\Rest\AppTable::getList([
			'order' => ['ID' => 'ASC'],
			'filter' => [
				"=ACTIVE" => \Bitrix\Rest\AppTable::ACTIVE,
				"=MOBILE" => \Bitrix\Rest\AppTable::ACTIVE,
			],
			'select' => [
				'ID',
				'STATUS',
				'ACCESS',
				'MENU_NAME' => 'LANG.MENU_NAME',
				'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
				'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME',
			],
		]);

		while ($apps = $dbApps->fetch())
		{
			if ($apps['STATUS'] == \Bitrix\Rest\AppTable::STATUS_LOCAL)
			{
				$numLocalApps++;
			}

			$lang = in_array(LANGUAGE_ID, ['ru', 'en', 'de']) ? LANGUAGE_ID : LangSubst(LANGUAGE_ID);
			if ($apps['MENU_NAME'] <> '' || $apps['MENU_NAME_DEFAULT'] <> '' || $apps['MENU_NAME_LICENSE'] <> '')
			{
				$appRightAvailable = false;
				if (\CRestUtil::isAdmin())
				{
					$appRightAvailable = true;
				}
				elseif (!empty($apps['ACCESS']))
				{
					$rights = explode(",", $apps['ACCESS']);
					foreach ($rights as $rightID)
					{
						if (in_array($rightID, $arUserGroupCode))
						{
							$appRightAvailable = true;
							break;
						}
					}
				}
				else
				{
					$appRightAvailable = true;
				}

				if ($appRightAvailable)
				{
					$appName = $apps["MENU_NAME"];

					if ($appName == '')
					{
						$appName = $apps['MENU_NAME_DEFAULT'];
					}
					if ($appName == '')
					{
						$appName = $apps['MENU_NAME_LICENSE'];
					}

					$arMenuApps[] = [
						'id' => $apps['ID'],
						'title' => $appName,
						'imageName' => 'market',
						'params' => [
							'url' => SITE_DIR . 'mobile/marketplace/?id=' . $apps['ID'],
						],
					];
				}
			}
		}

		return $arMenuApps;
	}
}
