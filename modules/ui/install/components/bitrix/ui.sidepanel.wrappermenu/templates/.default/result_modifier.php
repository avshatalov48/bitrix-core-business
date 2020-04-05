<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


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
				if ($level == 0)
				{
					$result .= '<li class="ui-sidepanel-menu-item'.($item['ACTIVE'] ? ' ui-sidepanel-menu-active' : '').'">';
					$result .= '<a ';
					$result .= getLinkItemAttributes($item['ATTRIBUTES'], 'ui-sidepanel-menu-link');
					$result .= '><div class="ui-sidepanel-menu-link-text">'.$item['NAME'].'</div></a>';
				}
				else
				{
					$result .= '<li class="ui-sidepanel-submenu-item'.($item['ACTIVE'] ? ' ui-sidepanel-submenu-active' : '').'">';
					$result .= '<a ';
					$result .= getLinkItemAttributes($item['ATTRIBUTES'], 'ui-sidepanel-submenu-link');
					$result .= '><div class="ui-sidepanel-menu-link-text">'.$item['NAME'].'</div></a>';
				}

				$result .= getWrapperMenu($item['CHILDREN'], $level + 1);
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
