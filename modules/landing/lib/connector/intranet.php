<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Landing\Rights;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Binding;
use \Bitrix\Landing\Restriction;
use \Bitrix\Intranet\Binding\Menu;

Loc::loadMessages(__FILE__);

class Intranet
{
	/**
	 * Service component paths.
	 */
	const PATH_SERVICE_LIST = 'kb/binding/menu/';

	/**
	 * Returns one service menu item for bind entity.
	 * @param string $bindCode Binding code.
	 * @return array
	 */
	protected static function getMenuItemBind(string $bindCode): array
	{
		$setItems = [];
		if (
			Rights::hasAdditionalRight('extension', null, false, true)
			&& (
				!Loader::includeModule('intranet')
				|| Restriction\ToolAvailabilityManager::getInstance()->check('knowledge_base')
			)
		)
		{
			$setItems[] = [
				'id' => 'landing_bind',
				'system' => true,
				'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_BIND_TITLE'),
				'onclick' => 'BX.SidePanel.Instance.open(\'' . SITE_DIR . self::PATH_SERVICE_LIST .
					'?menuId=' . $bindCode . '\', {allowChangeHistory: false});',
				'sectionCode' => Menu::SECTIONS['knowledge']
			];
			if (Rights::hasAdditionalRight('create', null, false, true))
			{
				$setItems[] = [
					'id' => 'landing_create',
					'system' => true,
					'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_BIND_CREATE_TITLE'),
					'onclick' => 'BX.SidePanel.Instance.open(\'' . SITE_DIR . self::PATH_SERVICE_LIST .
						'?menuId=' . $bindCode . '&create=Y\', {allowChangeHistory: false});',
					'sectionCode' => Menu::SECTIONS['knowledge']
				];
			}
		}
		return $setItems;
	}

	/**
	 * Returns one service menu item for unbind entity.
	 * @param string $bindCode Binding code.
	 * @param string $entityId Entity id.
	 * @param string $title Custom title.
	 * @return array
	 */
	protected static function getMenuItemUnbind(string $bindCode, string $entityId, string $title): array
	{
		return [
			'id' => 'landing_unbind_' . $entityId,
			'system' => true,
			'text' => $title,
			'onclick' => 'BX.Landing.Connector.Intranet.unbindMenuItem("' . $bindCode . '", "' . $entityId . '", "' . \CUtil::JSEscape($title) . '");'
		];
	}

	/**
	 * Returns menu items for different binding places in Intranet.
	 * @param \Bitrix\Main\Event $event Event instance.
	 * @return array
	 */
	public static function onBuildBindingMenu(\Bitrix\Main\Event $event): array
	{
		\CJSCore::init('sidepanel');
		\Bitrix\Landing\Site\Type::setScope(
			\Bitrix\Landing\Site\Type::SCOPE_CODE_KNOWLEDGE
		);

		$bindings = Binding\Menu::getList(null);

		// associate different bindings
		$bindingsAssoc = [];
		foreach ($bindings as $binding)
		{
			if (!isset($bindingsAssoc[$binding['BINDING_ID']]))
			{
				$bindingsAssoc[$binding['BINDING_ID']] = [];
			}
			$bindingsAssoc[$binding['BINDING_ID']][] = $binding;
		}
		$bindings = $bindingsAssoc;
		unset($bindingsAssoc);

		// init vars
		$items = [];
		$bindingMap = Menu::getMap();

		// build binding map
		foreach ($bindingMap as $sectionCode => $bindingSection)
		{
			foreach ($bindingSection['items'] as $itemCode => $foo)
			{
				$menuItems = [];
				$unbindItems = [];
				$bindingCode = $sectionCode . ':' . $itemCode;
				if (isset($bindings[$bindingCode]))
				{
					foreach ($bindings[$bindingCode] as $bindingItem)
					{
						$menuItems[] = [
							'id' => 'landing_' . $bindingItem['ENTITY_TYPE'] . $bindingItem['ENTITY_ID'],
				  			'text' => \htmlspecialcharsbx($bindingItem['TITLE']),
				  			'href' => $bindingItem['PUBLIC_URL'],
				  			'sectionCode' => Menu::SECTIONS['knowledge']
						];
						$unbindItems[] = self::getMenuItemUnbind(
							$bindingCode,
							$bindingItem['ENTITY_TYPE'] . '_' . $bindingItem['ENTITY_ID'],
							$bindingItem['TITLE']
						);
					}
				}
				$menuItems = array_merge(
					$menuItems,
					self::getMenuItemBind($bindingCode)
				);
				if (isset($bindings[$bindingCode]) && Rights::hasAdditionalRight('extension', null, false, true))
				{
					$menuItems[] = [
						'id' => 'landing_unbind',
						'extension' => 'landing.connector.intranet',
						'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_HIDE_TITLE'),
						'items' => $unbindItems,
						'sectionCode' => Menu::SECTIONS['knowledge']
					];
				}
				$items[] = [
					'bindings' => [
						$sectionCode => [
							'include' => [
								$itemCode
							]
						]
					],
					'items' => $menuItems
				];
			}
		}

		\Bitrix\Landing\Site\Type::clearScope();

		return $items;
	}
}
