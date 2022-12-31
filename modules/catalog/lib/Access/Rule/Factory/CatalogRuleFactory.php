<?php

namespace Bitrix\Catalog\Access\Rule\Factory;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\Rule\Factory\RuleControllerFactory;

class CatalogRuleFactory extends RuleControllerFactory
{
	private const BASE_RULE = 'Base';

	protected function getClassName(string $action, AccessibleController $controller): ?string
	{
		$actionName = ActionDictionary::getActionRuleName($action);
		if (!$actionName)
		{
			return null;
		}

		$action = explode('_', $actionName);
		$action = array_map(fn($el) => ucfirst(mb_strtolower($el)), $action);

		$ruleClass = $this->getNamespace($controller) . implode($action) . self::SUFFIX;

		if (class_exists($ruleClass))
		{
			return $ruleClass;
		}

		return $this->getNamespace($controller) . self::BASE_RULE . self::SUFFIX;
	}
}
