<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Result;
use Bitrix\Sale\Controller\Controller;

final class Internalizer extends ModificationFieldsBase
{
	public function __construct(ViewManager $manager, $data = [])
	{
		$this->setFormat([
			self::TO_WHITE_LIST,
			self::TO_SNAKE ,
			self::CHECK_REQUIRED
		]);

		parent::__construct($manager, $data);
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
		$view = $this->getView($controller);

		if ($name == 'list')
		{
			if(isset($arguments['select']))
			{
				$fields = $arguments['select'];
				if(!empty($fields))
					$arguments['select'] = $view->convertKeysToSnakeCaseSelect($fields);
			}

			if(isset($arguments['filter']))
			{
				$fields = $arguments['filter'];
				if(!empty($fields))
					$arguments['filter'] = $view->convertKeysToSnakeCaseFilter($fields);
			}

			if(isset($arguments['order']))
			{
				$fields = $arguments['order'];
				if(!empty($fields))
					$arguments['order'] = $view->convertKeysToSnakeCaseOrder($fields);
			}
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'add'
			|| $name == 'update')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $view->convertKeysToSnakeCaseFields($fields);
			}
		}
		else
		{
			$arguments = $view->convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	private function internalize($arguments)
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$view = $this->getView($controller);

		if($name == 'add')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $view->internalizeFieldsAdd($fields);
		}
		elseif ($name == 'update')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $view->internalizeFieldsUpdate($fields);
		}
		elseif ($name == 'list')
		{
			$fields = $view->internalizeFieldsList([
				'select'=>$arguments['select'],
				'filter'=>$arguments['filter'],
				'order'=>$arguments['order'],
			]);

			$arguments['select'] = $fields['select'];
			$arguments['filter'] = $fields['filter'];
			$arguments['order'] = $fields['order'];
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		else
		{
			$arguments = $view->internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	private function canonicalize($arguments)
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$view = $this->getView($controller);

		if($name == 'add')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $view->canonicalizeFieldsAdd($fields);
		}
		elseif ($name == 'update')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $view->canonicalizeFieldsUpdate($fields);
		}
		elseif ($name == 'list')
		{
			$fields = $view->canonicalizeFieldsList([
				'select'=>$arguments['select'],
				'filter'=>$arguments['filter'],
				'order'=>$arguments['order'],
			]);

			$arguments['select'] = $fields['select'];
			$arguments['filter'] = $fields['filter'];
			$arguments['order'] = $fields['order'];
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		else
		{
			$arguments = $view->canonicalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	protected function check($arguments)
	{
		$r = new Result();

		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$view = $this->getView($controller);

		if($name == 'add')
		{
			$r = $view->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'update')
		{
			$r = $view->checkFieldsUpdate($arguments['fields']);
		}
		elseif ($name == 'list')
		{
			$r = $view->checkFieldsList($arguments);
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		else
		{
			$r = $view->checkArguments($name, $arguments);
		}

		return $r;
	}
}