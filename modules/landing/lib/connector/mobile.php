<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use \Bitrix\MobileApp\Janative;
use \Bitrix\Mobile\Auth;

Loc::loadMessages(__FILE__);

class Mobile
{
	/**
	 * Current hit is mobile.
	 * @var bool
	 */
	protected static $isMobileHit = false;

	/**
	 * Handler on build global mobile menu.
	 * @param array $menu Current mobile menu.
	 * @return array
	 */
	public static function onMobileMenuStructureBuilt($menu): array
	{
		if (!isset($menu[0]['items']) || !is_array($menu[0]['items']))
		{
			return $menu;
		}

		if (\Bitrix\Landing\Site\Type::isEnabled('knowledge'))
		{
			$menu[0]['items'][] = self::getKnowledgeMenu();
		}

		//$menu[0]['items'][] = self::getLandingMenu();

		return $menu;
	}

	/**
	 * Returns menu item for knowledge base.
	 * @return array
	 */
	private static function getKnowledgeMenu(): array
	{
		$componentId = 'knowledge.list';
		$componentVersion = Janative\Manager::getComponentVersion(
			$componentId
		);

		return [
			'sort' => 100,
			'title' => Loc::getMessage('LANDING_CONNECTOR_MB_MENU_TITLE'),
			'imageUrl' => '/bitrix/images/landing/mobile/knowledge.png?4',
			'color' => '#e597ba',
			'params' => [
				'onclick' => <<<JS
					ComponentHelper.openList({
						name: '{$componentId}',
						object: 'list',
						version: '{$componentVersion}',
						widgetParams: {title: this.title, useSearch:true}
					});
JS
			]
		];
	}

	/**
	 * Returns menu item for sites and stores.
	 * @return array
	 */
	private static function getLandingMenu(): array
	{
		return [
			'sort' => 100,
			'title' => Loc::getMessage('LANDING_CONNECTOR_MB_LANDINGS_MENU_TITLE'),
			'imageUrl' => '/bitrix/images/landing/mobile/knowledge.png',
			'color' => '#e597ba',
			'params' => [
				'onclick' => <<<JS
					PageManager.openComponent("JSLandingsComponent", {
					    scriptPath:"/mobileapp/jn/landing.list/",    
					    rootWidget:{
					       name:"layout",
					       settings:{objectName:"layoutWidget", title:"Hello World", modal: true}
					    }});
JS
			]
		];
	}

	/**
	 * Set current hit as mobile.
	 * @return void
	 */
	public static function forceMobile()
	{
		self::$isMobileHit = true;
	}

	/**
	 * Returns true, if current destination is mobile app dir.
	 * @return bool
	 */
	public static function isMobileHit(): bool
	{
		static $mobileHit = null;

		if (self::$isMobileHit)
		{
			return true;
		}

		if ($mobileHit === null)
		{
			$mobileHit = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
						&& mb_strpos(Manager::getCurDir(), SITE_DIR . 'mobile/') === 0;
		}

		return $mobileHit;
	}

	/**
	 * This code should execute on every mobile hit.
	 * @return void
	 */
	public static function prologMobileHit()
	{
		if (self::isMobileHit())
		{
			if (
				\Bitrix\Main\Loader::includeModule('mobile') &&
				\Bitrix\Main\Loader::includeModule('mobileapp')
			)
			{
				if (!defined('SKIP_MOBILEAPP_INIT'))
				{
					\CMobile::init();
					if (!Manager::getUserId())
					{
						Manager::getApplication()->restartBuffer();
						Auth::setNotAuthorizedHeaders();
						echo Json::encode(Auth::getNotAuthorizedResponse());
						die();
					}
				}
			}
			else
			{
				die();
			}
		}
	}
}
