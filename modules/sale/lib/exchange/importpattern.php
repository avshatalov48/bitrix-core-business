<?php
namespace Bitrix\Sale\Exchange;

use Bitrix\Sale\Result;

abstract class ImportPattern
{
    /**
     * @param array $rawData
     * @return mixed
     */
    abstract public function parse(array $rawData);

    /**
     * @param array $items
     * @return mixed
     */
    abstract protected function convert(array $items);

    /**
     * @param ImportBase[] $items
     * @return mixed
     */
    abstract protected function import(array $items);

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
    abstract protected function logger(array $items);

    /**
     * @param array $rawData
     * @return Result
     */
    public function process(array $rawData)
    {
        /** @var Result $r */
        $r = $this->parse($rawData);
        if(!$r->isSuccess())
            return $r;

        $documents = $r->getData();
        $r = $this->convert($documents);
        if(!$r->isSuccess())
            return $r;

        $entityItems = $r->getData();
        $r = $this->import($entityItems);

        $this->logger($entityItems);

        return $r;
    }
}