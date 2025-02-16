<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\UI\Extension::load(["ui.label"]);

if (!function_exists('getWrapperMenu'))
{
	/**
	 * @param $items
	 * @param int $level
	 *
	 * @return string
	 */
	function getWrapperMenu($items, $level = 0)
	{
		$result = '';

		if (!empty($items) && is_array($items))
		{
			if ($level == 0)
			{
				$result = '<ul id="sidepanelMenu" class="ui-sidepanel-menu">';
			}
			else
			{
				$result = '<ul class="ui-sidepanel-submenu">';
			}

			foreach ($items as $item)
			{
				$itemCanBeActive = isset($item['CAN_BE_ACTIVE'])
					? ' ui-sidepanel-menu-canBeActive-' . $item['CAN_BE_ACTIVE']
					: '';

				if ($level == 0)
				{
					$result .= '<li class="ui-sidepanel-menu-item' .
						($item['ACTIVE'] ? ' ui-sidepanel-menu-active' : '') .
						($item['DISABLED'] ? ' ui-sidepanel-menu-disabled' : '') .
						($item['SUBMENU_OPEN'] ? ' ui-sidepanel-menu-submenuOpen' : '') .
						$itemCanBeActive .
						'">';
					$result .= '<a ';
					$result .= getLinkItemAttributes($item['ATTRIBUTES'], 'ui-sidepanel-menu-link');
					$result .= '><div class="ui-sidepanel-menu-link-text">'.$item['NAME'];
					$result .= ($item['LABEL'] ? '<span class="ui-sidepanel-menu-label"><span class="ui-label ui-label-primary ui-label-fill ui-label-xs"><span class="ui-label-inner">'.$item['LABEL'].'</span></span></span>' : '');
					$result .= '</div>';
					$result .= ($item['NOTICE'] ? '<span class="ui-sidepanel-menu-notice-icon"></span>' : '');
					$result .= '</a>';

				}
				else
				{
					$result .= '<li class="ui-sidepanel-submenu-item' .
						($item['ACTIVE'] ? ' ui-sidepanel-submenu-active' : '') .
						$itemCanBeActive .
						'">';
					$result .= '<a ';
					$result .= getLinkItemAttributes($item['ATTRIBUTES'], 'ui-sidepanel-submenu-link');
					$result .= '><div class="ui-sidepanel-menu-link-text">'.$item['NAME'].'</div>';
					$result .= ($item['NOTICE'] ? '<span class="ui-sidepanel-menu-notice-icon"></span>' : '');
					$result .= '</a>';
				}

				$result .= getWrapperMenu($item['CHILDREN'] ?? [], $level + 1);
				$result .= '</li>';
			}

			$result .= '</ul>';
		}

		return $result;
	}
}

if (!function_exists('getLinkItemAttributes'))
{
	/**
	 * @param array $attributes
	 * @param string $linkClass
	 *
	 * @return string
	 */
	function getLinkItemAttributes($attributes = array(), $linkClass = '')
	{
		$result = '';

		if (!empty($attributes))
		{
			foreach ($attributes as $code => $attribute)
			{
				if (is_array($attribute))
				{
					foreach ($attribute as $name => $value)
					{
						$result .= ' '.$code.'-'.$name.'="'.$value.'" ';
					}
				}
				elseif($code == 'class')
				{
					$result .= ' '.$code.'="'.$attribute.' '.$linkClass.'" ';
				}
				else
				{
					$result .= ' '.$code.'="'.$attribute.'" ';
				}
			}
		}

		if (empty($attributes['class']))
		{
			$result .= ' class="'.$linkClass.'"';
		}

		return $result;
	}
}
