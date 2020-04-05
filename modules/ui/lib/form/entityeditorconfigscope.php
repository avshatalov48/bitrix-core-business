<?php
namespace Bitrix\UI\Form;

use Bitrix\Main\Localization\Loc;

class EntityEditorConfigScope
{
	const UNDEFINED = '';
	const PERSONAL = 'P';
	const COMMON = 'C';

	private static $captions = array();

	public static function isDefined($scope)
	{
		return($scope == self::PERSONAL || $scope === self::COMMON);
	}

	public static function getCaptions()
	{
		if(!self::$captions[LANGUAGE_ID])
		{
			Loc::loadMessages(__FILE__);

			self::$captions[LANGUAGE_ID] = array(
				self::PERSONAL => Loc::getMessage('UI_ENTITY_ED_CONFIG_SCOPE_PERSONAL'),
				self::COMMON => Loc::getMessage('UI_ENTITY_ED_CONFIG_SCOPE_COMMON')
			);
		}

		return self::$captions[LANGUAGE_ID];
	}

	public static function getCaption($scope)
	{
		$captions = self::getCaptions();
		return isset($captions[$scope]) ? $captions[$scope] : "[{$scope}]";
	}
}

