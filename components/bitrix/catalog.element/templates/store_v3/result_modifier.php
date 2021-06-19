<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

if (!empty($arResult['OFFERS']) && is_array($arResult['OFFERS']))
{
	$arResult['SHOW_OFFERS_PROPS'] = false;
	foreach ($arResult['OFFERS'] as $item)
	{
		if (empty($item['DISPLAY_PROPERTIES']))
		{
			continue;
		}
		if (
			count($item['DISPLAY_PROPERTIES']) === 1
			&& isset($item['DISPLAY_PROPERTIES']['MORE_PHOTO'])
		)
		{
			continue;
		}
		$arResult['SHOW_OFFERS_PROPS'] = true;
		break;
	}
	unset($item);
}