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

	protected function getRewriteFields(): array
	{
		return [];
	}

	public function internalizeFieldsList($arguments, $fieldsInfo = []): array
	{
		$fields = parent::internalizeFieldsList($arguments, $fieldsInfo);

		return $this->rewriteFieldsListAsAliases([
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
	function externalizeFieldsGet($fields, $fieldsInfo = []): array
	{
		$fields = $this->rewriteFieldsGetAsOrigFields($fields);
		return parent::externalizeFieldsGet($fields, $fieldsInfo);
	}

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
	final protected function getOrigNameField($name)
	{
		$rewriteFields = $this->getRewriteFields();
		foreach ($rewriteFields as $origName=>$rewriteField)
		{
			$alias = $rewriteField['REFERENCE_FIELD'];
			if($name === $alias)
			{
				return $origName;
			}
		}
		return null;
	}

	final protected function rewriteFieldsAsOrigFields($fields): array
	{
		$result = [];
		foreach ($fields as $name=>$value)
		{
			$origName = $this->getOrigNameField($name);

			if($origName)
			{
				$result[$origName] = $value;
			}
			else
			{
				$result[$name] = $value;
			}
		}
		return $result;
	}

	final protected function rewriteFieldsGetAsOrigFields($fields): array
	{
		return $this->rewriteFieldsAsOrigFields($fields);
	}

	public function rewriteFieldsListAsAliases($arguments): array
	{
		$filter = isset($arguments['filter']) ? $this->rewriteFilterFieldsAsAliases($arguments['filter']):[];
		$select = isset($arguments['select']) ? $this->rewriteSelectFieldsAsAliases($arguments['select']):[];
		$order = isset($arguments['order']) ? $this->rewriteOrderFieldsAsAliases($arguments['order']):[];

		return [
			'filter'=>$filter,
			'select'=>$select,
			'order'=>$order,
		];
	}

	protected function rewriteSelectFieldsAsAliases($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewriteFields();

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

	protected function rewriteFilterFieldsAsAliases($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewriteFields();


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

	protected function rewriteOrderFieldsAsAliases($fields): array
	{
		$result = [];
		$rewriteFields = $this->getRewriteFields();

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