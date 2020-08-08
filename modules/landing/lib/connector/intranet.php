<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Binding;
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
	protected static function getMenuItemBind($bindCode)
	{
		return [
			[
				'id' => 'landing_bind',
				'system' => true,
				'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_BIND_TITLE'),
				'onclick' => 'BX.SidePanel.Instance.open(\'' . SITE_DIR . self::PATH_SERVICE_LIST .
							 '?menuId=' . $bindCode . '\', {allowChangeHistory: false});'
			],
			[
				'id' => 'landing_create',
				'system' => true,
				'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_BIND_CREATE_TITLE'),
				'onclick' => 'BX.SidePanel.Instance.open(\'' . SITE_DIR . self::PATH_SERVICE_LIST .
							 '?menuId=' . $bindCode . '&create=Y\', {allowChangeHistory: false});'
			]
		];
	}

	/**
	 * Returns one service menu item for unbind entity.
	 * @param string $bindCode Binding code.
	 * @param string $entityId Entity id.
	 * @param string $title Custom title.
	 * @return array
	 */
	protected static function getMenuItemUnbind($bindCode, $entityId,  $title)
	{
		static $functionInjected = false;

		if (!$functionInjected)
		{
			$functionInjected = true;
			\Bitrix\Main\UI\Extension::load('ui.dialogs.messagebox');
			\Bitrix\Main\Page\Asset::getInstance()->addString('
				<script type="text/javascript">
					function landingBindingMenu(bindCode, entityId)
					{
						BX.UI.Dialogs.MessageBox.confirm(
							"' . Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_ALERT_MESSAGE') . '",
							"' . Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_ALERT_TITLE') . '", 
							function() 
							{
								BX.ajax({
									url: "' . SITE_DIR . self::PATH_SERVICE_LIST . '",
									method: "POST",
									data: {
										action: "unbind",
										param: entityId,
										menuId: bindCode,
										sessid: BX.message("bitrix_sessid"),
										actionType: "json"
									},
									dataType: "json",
									onsuccess: function(data)
									{
										if (data)
										{
											top.window.location.reload();
										}
									}.bind(this)
								});
							},
							"' . Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_ALERT_BUTTON') . '"
						);
					}
				</script>
			');
		}

		return [
			'text' => $title,
			'onclick' => 'landingBindingMenu("' . $bindCode . '", "' . $entityId . '");'
		];
	}

	/**
	 * Returns menu items for different binding places in Intranet.
	 * @param \Bitrix\Main\Event $event Event instance.
	 * @return array
	 */
	public static function onBuildBindingMenu(\Bitrix\Main\Event $event)
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
					}
				}
				$menuItems = array_merge(
					$menuItems,
					self::getMenuItemBind($bindingCode)
				);
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
