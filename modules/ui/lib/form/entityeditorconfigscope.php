<?php
namespace Bitrix\UI\Form;

use Bitrix\Main\Localization\Loc;
use Bitrix\Ui\EntityForm\Scope;

class EntityEditorConfigScope
{
	const UNDEFINED = '';
	const PERSONAL = 'P';
	const COMMON = 'C';
	const CUSTOM = 'CUSTOM';

	private static $captions = array();

	public static function isDefined(string $scope): bool
	{
		return (in_array($scope, [self::PERSONAL, self::COMMON, self::CUSTOM], true));
	}

	/**
	 * @param string $entityTypeId
	 * @param string $moduleId
	 * @return mixed
	 */
	public static function getCaptions(string $entityTypeId = '', ?string $moduleId = null): array
	{
		if(!self::$captions[LANGUAGE_ID])
		{
			Loc::loadMessages(__FILE__);

			self::$captions[LANGUAGE_ID] = array(
				self::PERSONAL => Loc::getMessage('UI_ENTITY_ED_CONFIG_SCOPE_PERSONAL'),
				self::COMMON => Loc::getMessage('UI_ENTITY_ED_CONFIG_SCOPE_COMMON')
			);

			if ($entityTypeId && $customScopes = Scope::getInstance()->getUserScopes($entityTypeId, $moduleId))
			{
				self::$captions[LANGUAGE_ID] = array_merge(
					self::$captions[LANGUAGE_ID], ['CUSTOM' => $customScopes]
				);
			}
		}

		return self::$captions[LANGUAGE_ID];
	}

	/**
	 * @param string $scope
	 * @param string $moduleId
	 * @param string $entityTypeId
	 * @param int|null $scopeId
	 * @return string
	 */
	public static function getCaption(
		string $scope,
		string $entityTypeId = '',
		?int $scopeId = null,
		?string $moduleId = null
	): string
	{
		$captions = self::getCaptions($entityTypeId, $moduleId);
		if ($scope === self::CUSTOM && $entityTypeId && $scopeId)
		{
			return $captions[$scope][$scopeId]['NAME'];
		}
		return ($captions[$scope] ?? "[{$scope}]");
	}
}

