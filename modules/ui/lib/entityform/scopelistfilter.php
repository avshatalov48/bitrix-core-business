<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;

class ScopeListFilter
{
	private const SETTINGS_ENTITY_FORM_SCOPE_KEY = 'entityFormScope';
	private const SETTINGS_SCOPE_LIST_FILTER_CLASS_KEY = 'scopeListFilter';

	public function __construct(
	) {
	}

	public static function getInstance($moduleId): static
	{
		$configuration = Configuration::getInstance($moduleId);

		$value = $configuration->get(static::SETTINGS_ENTITY_FORM_SCOPE_KEY);
		if (
			is_array($value)
			&& isset($value[static::SETTINGS_SCOPE_LIST_FILTER_CLASS_KEY])
			&& Loader::includeModule($moduleId)
			&& is_a($value[static::SETTINGS_SCOPE_LIST_FILTER_CLASS_KEY], self::class, true)
		) {
			return new $value[static::SETTINGS_SCOPE_LIST_FILTER_CLASS_KEY]();
		}

		return new self();
	}

	public function prepareFilter(string $entityTypeId, bool $isAdminForEntity, bool $excludeEmptyAccessCode, Scope $scope): array
	{
		$filter = [];
		if (!$isAdminForEntity)
		{
			$filter['@ID'] = $scope->getScopesIdByUser();
		}

		$filter['@ENTITY_TYPE_ID'] = ($scope->getEntityTypeIdMap()[$entityTypeId] ?? [$entityTypeId]);

		if ($excludeEmptyAccessCode)
		{
			$filter['!=ACCESS_CODE'] = '';
		}

		return $filter;
	}

	public function prepareEntityEditorFilter(string $entityTypeId, bool $isAdminForEntity, Scope $scope): array
	{
		$filter['!=ACCESS_CODE'] = '';

		if (!$isAdminForEntity)
		{
			$filter['@ID'] = $scope->getScopesIdByUser();
		}

		$filter['@ENTITY_TYPE_ID'] = ($scope->getEntityTypeIdMap()[$entityTypeId] ?? [$entityTypeId]);

		return $filter;
	}
}
