<?php


namespace Bitrix\Sale\Rest;


use Bitrix\Sale\Controller\Controller;
use Bitrix\Sale\Rest\Synchronization\LoggerDiag;
use Bitrix\Sale\Result;

class Internalizer extends ModificationFieldsBase
{
	public function __construct($name, $arguments, $controller, $data = [], $scope = '')
	{
		$this->setFormat([
			self::TO_WHITE_LIST,
			self::TO_SNAKE,
			self::CHECK_REQUIRED
		]);

		parent::__construct($name, $arguments, $controller, $data, $scope);
	}

	/**
	 * @return Result
	 */
	public function process()
	{
		$r = new Result();

		$arguments = $this->getArguments();

		if(in_array(self::TO_SNAKE, $this->format))
		{
			$arguments = $this->convertToSnakeCase($arguments);
		}

		if(in_array(self::CHECK_REQUIRED, $this->format))
		{
			$check = $this->check($arguments);
			if(!$check->isSuccess())
			{
				$r->addErrors($check->getErrors());
			}
		}

		if($r->isSuccess())
		{
			if(in_array(self::TO_WHITE_LIST, $this->format))
			{
				$arguments = $this->internalize($arguments);
			}
		}


		return $r->setData(['data'=>$r->isSuccess()?$arguments:null]);
	}

	protected function convertToSnakeCase($arguments=[])
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_CAMEL2SNAKE_FIELDS_BEFORE', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		if ($name == 'list')
		{
			if(isset($arguments['select']))
			{
				$fields = $arguments['select'];
				if(!empty($fields))
					$arguments['select'] = $entity->convertKeysToSnakeCaseSelect($fields);
			}

			if(isset($arguments['filter']))
			{
				$fields = $arguments['filter'];
				if(!empty($fields))
					$arguments['filter'] = $entity->convertKeysToSnakeCaseFilter($fields);
			}

			if(isset($arguments['order']))
			{
				$fields = $arguments['order'];
				if(!empty($fields))
					$arguments['order'] = $entity->convertKeysToSnakeCaseOrder($fields);
			}
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify'
			|| $name == 'add'
			|| $name == 'update'
			|| $name == 'tryadd'
			|| $name == 'tryupdate'
			|| $name == 'trymodify')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $entity->convertKeysToSnakeCaseFields($fields);
			}
		}
		else
		{
			$arguments = $entity->convertKeysToSnakeCaseArguments($name, $arguments);
		}

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_CAMEL2SNAKE_FIELDS_AFTER', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		return $arguments;
	}

	private function internalize($arguments)
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_PREPARE_FIELDS_BEFORE', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		if($name == 'add')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsAdd($fields);
		}
		elseif ($name == 'update')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsUpdate($fields);
		}
		elseif ($name == 'list')
		{
			$fields = $entity->internalizeFieldsList([
				'select'=>$arguments['select'],
				'filter'=>$arguments['filter'],
				'order'=>$arguments['order'],
			]);

			$fields = $entity->rewriteFieldsList([
				'select'=>$fields['select'],
				'filter'=>$fields['filter'],
				'order'=>$fields['order'],
			]);

			$arguments['select'] = $fields['select'];
			$arguments['filter'] = $fields['filter'];
			$arguments['order'] = $fields['order'];
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsModify($fields);
		}
		elseif ($name == 'tryadd')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsTryAdd($fields);
		}
		elseif ($name == 'tryupdate')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsUpdate($fields);
		}
		elseif ($name == 'trymodify')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsTryModify($fields);
		}
		else
		{
			$arguments = $entity->internalizeArguments($name, $arguments);
		}

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_PREPARE_FIELDS_AFTER', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		return $arguments;
	}

	protected function check($arguments)
	{
		$r = new Result();

		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		if($name == 'add')
		{
			$r = $entity->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'update')
		{
			$r = $entity->checkFieldsUpdate($arguments['fields']);
		}
		elseif ($name == 'list'){}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify')
		{
			$r = $entity->checkFieldsModify($arguments['fields']);
		}
		elseif ($name == 'tryadd')
		{
			$r = $entity->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'tryupdate')
		{
			$r = $entity->checkFieldsUpdate($arguments['fields']);
		}
		elseif ($name == 'trymodify')
		{
			$r = $entity->checkFieldsModify($arguments['fields']);
		}
		else
		{
			$r = $entity->checkArguments($name, $arguments);
		}

		return $r;
	}
}