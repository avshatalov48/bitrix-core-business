<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Manager
{
	/**
	 * Restrictions map.
	 */
	const MAP = [
		'limit_sites_google_analytics' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Hook', 'isAllowed'
			]
		],
		'limit_sites_powered_by' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Hook', 'isAllowed'
			]
		],
		'limit_sites_html_js' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Hook', 'isAllowed'
			]
		],
		'limit_sites_access_permissions' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Rights', 'isAllowed'
			]
		],
		'limit_sites_transfer' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Site', 'isExportAllowed'
			]
		],
		'limit_free_domen' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Site', 'isFreeDomainAllowed'
			]
		],
		'limit_sites_number' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Site', 'isCreatingAllowed'
			],
			'scope_alias' => [
				'knowledge' => 'limit_knowledge_base_number_page',
				'group' => 'limit_knowledge_base_number_page'
			]
		],
		'limit_sites_number_page' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Landing', 'isCreatingAllowed'
			],
			'scope_alias' => [
				'knowledge' => 'limit_knowledge_base_number_page',
				'group' => 'limit_knowledge_base_number_page'
			]
		],
		'limit_knowledge_base_number_page' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Site', 'isCreatingAllowed'
			]
		],
		'limit_knowledge_base_number_page_view' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Knowledge', 'isViewAllowed'
			]
		],
		'limit_sites_dynamic_blocks' => [
			'check_callback' => [
				'\Bitrix\Landing\Restriction\Block', 'isDynamicEnabled'
			]
		]
	];

	/**
	 * Returns map's item by code.
	 * @param string $code Item code.
	 * @return array
	 */
	protected static function getMapItem(string $code): ?array
	{
		static $scopeId = null;

		if (isset(self::MAP[$code]))
		{
			if ($scopeId === null)
			{
				$scopeId = strtolower(\Bitrix\Landing\Site\Type::getCurrentScopeId());
			}
			$item = self::MAP[$code];
			$item['code'] = $item['scope_alias'][$scopeId] ?? $code;
			return $item;
		}
		return null;
	}

	/**
	 * Returns JS action for the restriction.
	 * @param string|null $code Restriction code.
	 * @return string|null
	 */
	public static function getActionCode(string $code): ?string
	{
		if ($mapItem = self::getMapItem($code))
		{
			return 'top.BX.UI.InfoHelper.show("' . $mapItem['code'] . '");';
		}
		return null;
	}

	/**
	 * Includes necessary component.
	 * @return void
	 */
	protected static function includeInformerComponent(): void
	{
		static $included = false;

		if (!$included)
		{
			$included = true;
			if (SITE_TEMPLATE_ID != 'bitrix24')
			{
				\Bitrix\Landing\Manager::getApplication()
                   ->includeComponent('bitrix:ui.info.helper', '', []);
			}
		}
	}

	/**
	 * Returns lock icon html by restriction code.
	 * @param string|null $code Restriction code.
	 * @param array $nodes Html nodes for binding click event.
	 * @return string|null
	 */
	public static function getLockIcon(?string $code, array $nodes = []): ?string
	{
		if ($mapItem = self::getMapItem($code))
		{
			self::includeInformerComponent();
			$idCode = 'landing-tariff-' . \randString(5);
			$nodes[] = $idCode;
			$script = '
				<script>
					BX.ready(function()
					{
						var nodes = ' . \CUtil::phpToJSObject($nodes) . ';
						for (var i = 0, c = nodes.length; i < c; i++)
						{
							BX.bind(BX(nodes[i]), "click", function(e)
							{
								' . self::getActionCode($code) . ' 
								BX.PreventDefault(e);
							});
						}
					});
				</script>
			';
			return $script . '<span class="tariff-lock" id="' . $idCode . '"></span>';
		}
		return null;
	}

	/**
	 * @param string|null $code Restriction code.
	 * @return string|null
	 */
	public static function getSystemErrorMessage($code): ?string
	{
		if ($mapItem = self::getMapItem($code))
		{
			return Loc::getMessage('LANDING_' . strtoupper($mapItem['code']));
		}
		return Loc::getMessage('LANDING_' . strtoupper($code));
	}

	/**
	 * Checks restriction existing by code.
	 * @param string $code Restriction code.
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isAllowed(string $code, array $params = []): bool
	{
		static $cache = [];

		if ($mapItem = self::getMapItem($code))
		{
			if (!array_key_exists($code, $cache))
			{
				$cache[$code] = call_user_func_array($mapItem['check_callback'], [$mapItem['code'], $params]);
			}
			return $cache[$code];
		}

		return true;
	}
}