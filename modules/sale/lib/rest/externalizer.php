<?php


namespace Bitrix\Sale\Rest;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Sale\Result;

/**
 * Class Externalizer
 * @package Bitrix\Sale\Rest
 * экстерналайзер для работы с именованными списками|ответами
 */
class Externalizer extends ModificationFieldsBase
	implements Arrayable
{
	public function __construct($name, $arguments, $controller, $data = [], $scope = '')
	{
		$this->setFormat([
			self::TO_WHITE_LIST,
			self::TO_CAMEL,
			self::SORTING_KEYS
		]);

		parent::__construct($name, $arguments, $controller, $data, $scope);
	}

	public function process()
	{
		$r = new Result();

		$data = $this->getData();
		$id = $this->getIdList($data);

		$data = $data[$id];
		if(in_array(self::TO_WHITE_LIST, $this->format))
		{
			$data = $this->externalize($data);
		}

		if(in_array(self::TO_CAMEL, $this->format))
		{
			$data = $this->convertKeysToCamelCase([$id=>$data]);
		}

		if(in_array(self::SORTING_KEYS, $this->format))
		{
			$data = $this->multiSortKeysArray($data);
		}

		return $r->setData(['data'=>$data]);
	}

	public function toArray()
	{
		return $this->process()->getData()['data'];
	}

	protected function multiSortKeysArray(array $data)
	{
		ksort($data);

		foreach ($data as $k=>&$item)
		{
			if(is_array($item))
				$item = $this->multiSortKeysArray($item);
		}

		return $data;
	}

	/**
	 * @param $data
	 * @return array
	 * экстерналайзер работает только с НЕ именнованым списком. id списка не передается
	 */
	private function externalize($fields)
	{
		$name = $this->getName();
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		if($name == 'getfields'){}
		elseif($name == 'delete'){}
		elseif($name == 'get'
			|| $name == 'add'
			|| $name == 'update'
			|| $name == 'tryadd'
			|| $name == 'tryupdate')
		{
			$fields = $entity->externalizeFields($fields);
		}
		elseif($name == 'list')
		{
			$fields = $entity->externalizeListFields($fields);
		}
		elseif($name == 'modify')
		{
			$fields = $entity->externalizeFieldsModify($fields);
		}
		elseif($name == 'trymodify')
		{
			$fields = $entity->externalizeFieldsTryModify($fields);
		}
		else
		{
			$fields = $entity->externalizeResult($name, $fields);
		}

		return $fields;
	}

	/**
	 * @param $data
	 * @return int|null|string
	 * обязательное именование (ключ в массиве) списка или результата в ответе
	 */
	private function getIdList($data)
	{
		return key($data);
	}

	protected function convertKeysToCamelCase($fields)
	{
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		return $entity->convertKeysToCamelCase($fields);
	}

	public function getPage(Page $page)
	{
		$id = $this->convertKeysToCamelCase($page->getId());
		return new Page($id, $this->toArray()[$id], $page->getTotalCount());
	}
}