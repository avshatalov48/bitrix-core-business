<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Result;

abstract class ExportPattern
{
	/**
	 * @param array $fields
	 * @return Result
	 */
	abstract protected function getItems(array $fields);

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	abstract protected function convert(array $items);

	/**
	 * @param DocumentBase[] $items
	 * @return Result
	 */
	abstract protected function export(array $items);

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	abstract protected function logger(array $items);

	/**
	 * @param array $fields
	 * @return Result
	 */
	public function proccess(array $fields)
	{
		$r = $this->getItems($fields);
		if(!$r->isSuccess())
			return $r;

		$entityItems = $r->getData();
		$r = $this->convert($entityItems);
		if(!$r->isSuccess())
			return $r;

		$documents = $r->getData();
		$r = $this->export($documents);

		$this->logger($entityItems);

		return $r;
	}
}