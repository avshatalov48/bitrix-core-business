<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Hook\Page\Settings;

Loc::loadMessages(__FILE__);

class Menu
{
	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, \Bitrix\Landing\Block $block = NULL, array $params = array())
	{
		// add attrs to work TWO MENUS together
		if (!isset($manifest['attrs']) || !is_array($manifest['attrs']))
		{
			$manifest['attrs'] = array();
		}
		if (!isset($manifest['attrs']['.navbar-collapse']))
		{
			$manifest['attrs']['.navbar-collapse'] = array(
				array(
					'hidden' => true,
					'attribute' => 'id',
				),
			);
		}
		if (!isset($manifest['attrs']['button.navbar-toggler']))
		{
			$manifest['attrs']['button.navbar-toggler'] = array(
				array(
					'hidden' => true,
					'attribute' => 'aria-controls',
				),
				array(
					'hidden' => true,
					'attribute' => 'data-target',
				),
			);
		}
		
		// add callbacks
		$manifest['callbacks'] = array(
			'afterAdd' => function (\Bitrix\Landing\Block &$block) use($params)
			{
				$manifest = $block->getManifest();
				$needSave = false;

				// autogenerate MENU
				// predefine params
				if (isset($params['selector']))
				{
					$selector = $params['selector'];
				}
				else
				{
					$selector = '.landing-block-node-menu-list-item-link';
				}
				$count = isset($params['count']) ? $params['count'] : 5;
				$source = isset($params['source']) ? $params['source'] : null;

				// new menu items
				$menuItems = array();

				if (isset($manifest['nodes'][$selector]))
				{
					// fill menu
					if ($source == 'catalog')
					{
						$site = $block->getSite();
						if ($site['TYPE'] == 'STORE')
						{
							$menuItems = self::getCatalogMenu(
								$block->getSiteId(),
								$count
							);
						}
					}
					else if ($source == 'personal')
					{
						$menuItems = self::getPersonalMenu();
					}
					
					// save new items
					if (!empty($menuItems))
					{
						$block->updateNodes(array(
							$selector => $menuItems
						));
						$needSave = true;
					}
				}

				// to work TWO MENUS together
				// todo: check in manifest
				$navbarCollapseSection =
					isset($params['navbarCollapseSection']) ? $params['navbarCollapseSection'] : '.navbar-collapse';
				$navbarTogglerButton = isset($params['navbarTogglerButton']) ?
					$params['navbarTogglerButton'] :
					'button.navbar-toggler';
				
				if (
					isset($manifest['attrs'][$navbarCollapseSection])
					&& isset($manifest['attrs'][$navbarTogglerButton])
				)
				{
					$newId = "navBar" . $block->getId();
					$block->setAttributes(array(
						$navbarCollapseSection => array('id' => $newId),
						$navbarTogglerButton => array(
							'aria-controls' => $newId,
							'data-target' => "#".$newId,
						),
					));
					$needSave = true;
				}
				
				// SAVE
				if($needSave)
				{
					$block->save();
				}
			},
		);

		return $manifest;
	}

	/**
	 * Gets catalog items for menu.
	 * @param int $siteId Site id.
	 * @param int $count Elements count.
	 * @return array
	 */
	protected static function getCatalogMenu($siteId, $count)
	{
		$menuItems = array();

		if (!\Bitrix\Main\Loader::includeModule('iblock'))
		{
			return $menuItems;
		}

		$settings = Settings::getDataForSite($siteId);
		if ($settings['IBLOCK_ID'])
		{
			$res = \CIBlockSection::getList(
				array(),
				array(
					'IBLOCK_ID' => $settings['IBLOCK_ID'],
					'SECTION_ID' => $settings['SECTION_ID']
						? $settings['SECTION_ID']
						: false
				),
				false,
				array(
					'ID', 'NAME'
				),
				array(
					'nTopCount' => $count
				)
			);
			while ($row = $res->fetch())
			{
				$menuItems[] = array(
					'text' => $row['NAME'],
					'href' => '#catalogSection' . $row['ID'],
					'attrs' => array(
						'data-url' => '#catalogSection' . $row['ID']
					)
				);
			}
		}

		return $menuItems;
	}

	/**
	 * Get personal menu.
	 * @return array
	 */
	protected static function getPersonalMenu()
	{
		return array(
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_PERSONAL'),
				'href' => '#system_personal'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_ORDERS'),
				'href' => '#system_personal?SECTION=orders'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_ACCOUNT'),
				'href' => '#system_personal?SECTION=account'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_PRIVATE'),
				'href' => '#system_personal?SECTION=private'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_ORDERS_HISTORY'),
				'href' => '#system_personal?SECTION=orders&filter_history=Y'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_PROFILE'),
				'href' => '#system_personal?SECTION=profile'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_CART'),
				'href' => '#system_cart'
			),
			array(
				'text' => Loc::getMessage('LANDING_BLOCK_ST_PERSONAL_SUBSCRIBE'),
				'href' => '#system_personal?SECTION=subscribe'
			)
		);
	}
}
