<?php

namespace Bitrix\UI\Toolbar\Facade;

use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Manager;

/**
 * Class Toolbar
 * @package Bitrix\UI\Toolbar\Facade
 * @method static addButton($button, $location = ButtonLocation::RIGHT);
 * @see \Bitrix\UI\Toolbar\Toolbar::addButton
 * @method static addFilter($options = []);
 * @see \Bitrix\UI\Toolbar\Toolbar::addFilter
 * @method static hasFavoriteStar();
 * @see \Bitrix\UI\Toolbar\Toolbar::hasFavoriteStar
 * @method static addFavoriteStar();
 * @see \Bitrix\UI\Toolbar\Toolbar::addFavoriteStar
 * @method static deleteFavoriteStar();
 * @see \Bitrix\UI\Toolbar\Toolbar::deleteFavoriteStar
 * @method static getId();
 * @see \Bitrix\UI\Toolbar\Toolbar::getId
 * @method static getFilter();
 * @see \Bitrix\UI\Toolbar\Toolbar::getFilter
 * @method static renderAfterTitleButtons();
 * @see \Bitrix\UI\Toolbar\Toolbar::renderAfterTitleButtons
 * @method static renderRightButtons();
 * @see \Bitrix\UI\Toolbar\Toolbar::renderRightButtons
 * @method static renderAfterFilterButtons();
 * @see \Bitrix\UI\Toolbar\Toolbar::renderAfterFilterButtons
 * @method static renderFilterRightButtons();
 * @see \Bitrix\UI\Toolbar\Toolbar::renderFilterRightButtons
 * @method static setTitleMinWidth($width);
 * @see \Bitrix\UI\Toolbar\Toolbar::setTitleMinWidth
 * @method static getTitleMinWidth();
 * @see \Bitrix\UI\Toolbar\Toolbar::getTitleMinWidth
 * @method static setTitleMaxWidth($width);
 * @see \Bitrix\UI\Toolbar\Toolbar::setTitleMaxWidth
 * @method static getTitleMaxWidth();
 * @see \Bitrix\UI\Toolbar\Toolbar::getTitleMaxWidth
 * @method static deleteButtons(\Closure $closure)
 * @see \Bitrix\UI\Toolbar\Toolbar::deleteButtons
 * @method static shuffleButtons(\Closure $closure, $buttonLocation)
 * @see \Bitrix\UI\Toolbar\Toolbar::shuffleButtons
 * @method static getButtons()
 * @see \Bitrix\UI\Toolbar\Toolbar::getButtons
 */
final class Toolbar
{
	const DEFAULT_ID = 'default-toolbar';

	public static function __callStatic($name, $arguments)
	{
		$manager = Manager::getInstance();
		$toolbar = $manager->getToolbarById(self::DEFAULT_ID)?: $manager->createToolbar(self::DEFAULT_ID, []);
		if (!$toolbar)
		{
			//or exception?
			return null;
		}

		return call_user_func_array([$toolbar, $name], $arguments);
	}
}