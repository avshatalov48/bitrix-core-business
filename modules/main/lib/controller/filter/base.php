<?php

namespace Bitrix\Main\Controller\Filter;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class Base extends \Bitrix\Main\Engine\Controller
{
	protected function getList($entityTypeId, array $filterSettingsParams, ?array $additionalParams = null)
	{
		$result = [];
		$entityFilter = \Bitrix\Main\Filter\Factory::createEntityFilter(
			$entityTypeId,
			$filterSettingsParams,
			$additionalParams
		);

		foreach($entityFilter->getFields() as $field)
		{
			$result[] = \Bitrix\Main\UI\Filter\FieldAdapter::adapt($field->toArray([
				'lightweight' => true
			]));
		}

		return $result;
	}

	protected function getField($entityTypeId, array $filterSettingsParams, $id, ?array $additionalParams = null)
	{
		$entityFilter = \Bitrix\Main\Filter\Factory::createEntityFilter(
			$entityTypeId,
			$filterSettingsParams,
			$additionalParams
		);

		$field = $entityFilter->getField($id);
		if($field)
		{
			$result = \Bitrix\Main\UI\Filter\FieldAdapter::adapt($field->toArray());
		}
		else
		{
			$this->addError(new Error(Loc::getMessage("MAIN_CONTROLLER_FILTER_FIELD_NOT_FOUND"), "MAIN_CONTROLLER_FILTER_FIELD_NOT_FOUND"));
			return null;
		}

		return $result;
	}

	public function getListAction($filterId, $componentName, $signedParameters)
	{
		return [];
	}

	public function getFieldAction($filterId, $id, $componentName, $signedParameters)
	{
		return [];
	}
}

