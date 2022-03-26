<?php

namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;

abstract class Base extends \Bitrix\Rest\Integration\View\Base
{
	protected function isNewItem($fields): bool
	{
		return (isset($fields['ID']) === false);
	}

	protected function getRewritedFields(): array
	{
		return [];
	}

	public function internalizeFieldsList($arguments, $fieldsInfo = []): array
	{
		$fields = parent::internalizeFieldsList($arguments, $fieldsInfo);

		return $this->rewriteFieldsList([
			'select'=>$fields['select'],
			'filter'=>$fields['filter'],
			'order'=>$fields['order'],
		]);
	}

	//region convert keys to snake case
	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if ($name == 'modify')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
			}
		}
		else
		{
			$arguments = parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}
	//endregion

	//region internalize fields
	public function internalizeArguments($name, $arguments): array
	{
		if ($name == 'modify')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $this->internalizeFieldsModify($fields);
		}
		else
		{
			$arguments = parent::internalizeArguments($name, $arguments);
		}
		return $arguments;
	}

	public function internalizeFieldsModify($fields): array
	{
		throw new NotImplementedException('The method internalizeFieldsModify is not implemented.');
	}
	//endregion

	//region externalize fields
	public function externalizeResult($name, $fields): array
	{
		if($name == 'modify')
		{
			$fields = $this->externalizeFieldsModify($fields);
		}
		else
		{
			$fields = parent::externalizeResult($name, $fields);
		}

		return $fields;


	}

	public function externalizeFieldsModify($fields)
	{
		throw new NotImplementedException('The method externalizeFieldsModify is not implemented.');
	}
	//endregion

	//region check fields
	public function checkArguments($name, $arguments): Result
	{
		if ($name == 'modify')
		{
			$r = $this->checkFieldsModify($arguments['fields']);
		}
		else
		{
			$r = parent::checkArguments($name, $arguments);
		}

		return $r;
	}

	public function checkFieldsModify($fields): Result
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsModify($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(' ', $required->getErrorMessages())));

		return $r;
	}

	protected function checkRequiredFieldsModify($fields): Result
	{
		throw new NotImplementedException('The method checkFieldsModify is not implemented.');
	}
	//endregion

	//region rewrite
	public function rewriteFieldsList($arguments): array
	{
		$filter = isset($arguments['filter']) ? $this->rewriteFilterFields($arguments['filter']):[];
		$select = isset($arguments['select']) ? $this->rewriteSelectFields($arguments['select']):[];
		$order = isset($arguments['order']) ? $this->rewriteOrderFields($arguments['order']):[];

		return [
			'filter'=>$filter,
			'select'=>$select,
			'order'=>$order,
		];
	}

	protected function rewriteSelectFields($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();

		foreach ($fields as $name)
		{
			$fieldsIsAlias = isset($rewriteFields[$name]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$name]['REFERENCE_FIELD']))
				{
					$result[$name] = $rewriteFields[$name]['REFERENCE_FIELD'];
				}
			}
			else
			{
				$result[] = $name;
			}
		}

		return $result;
	}

	protected function rewriteFilterFields($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();


		foreach ($fields as $rawName=>$value)
		{
			$field = \CSqlUtil::GetFilterOperation($rawName);

			$fieldsIsAlias = isset($rewriteFields[$field['FIELD']]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$field['FIELD']]['REFERENCE_FIELD']))
				{
					$originalName = $rewriteFields[$field['FIELD']]['REFERENCE_FIELD'];
					$operation = mb_substr($rawName, 0, mb_strlen($rawName) - mb_strlen($field['FIELD']));
					$result[$operation.$originalName] = $value;
				}
			}
			else
			{
				$result[$rawName] = $value;
			}
		}

		return $result;
	}

	protected function rewriteOrderFields($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();

		foreach ($fields as $name=>$value)
		{
			$fieldsIsAlias = isset($rewriteFields[$name]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$name]['REFERENCE_FIELD']))
				{
					$result[$rewriteFields[$name]['REFERENCE_FIELD']] = $value;
				}
			}
			else
			{
				$result[$name] = $value;
			}
		}

		return $result;
	}
	//endregion
}