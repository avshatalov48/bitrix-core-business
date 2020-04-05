<?php
namespace Bitrix\Bizproc\Copy\Implement;

use Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Trigger
{
	const TRIGGER_COPY_ERROR = "TRIGGER_COPY_ERROR";

	private $targetDocumentType = [];
	private $mapStatusIdsCopiedDocument = [];

	private $result;

	public function __construct($targetDocumentType = [], $mapStatusIdsCopiedDocument = [])
	{
		$this->targetDocumentType = $targetDocumentType;
		$this->mapStatusIdsCopiedDocument = $mapStatusIdsCopiedDocument;

		$this->result = new Result();
	}

	/**
	 * @param $triggerId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getFields($triggerId)
	{
		$queryResult = TriggerTable::getList(["filter" => ["=ID" => $triggerId]]);
		return (($fields = $queryResult->fetch()) ? $fields : []);
	}

	public function prepareFieldsToCopy(array $fields)
	{
		if (isset($fields["ID"]))
		{
			unset($fields["ID"]);
		}

		if ($this->targetDocumentType)
		{
			$fields["MODULE_ID"] = $this->targetDocumentType[0];
			$fields["ENTITY"] = $this->targetDocumentType[1];
			$fields["DOCUMENT_TYPE"] = $this->targetDocumentType[2];
		}

		if (array_key_exists($fields["DOCUMENT_STATUS"], $this->mapStatusIdsCopiedDocument))
		{
			$fields["DOCUMENT_STATUS"] = $this->mapStatusIdsCopiedDocument[$fields["DOCUMENT_STATUS"]];
		}

		return $fields;
	}

	/**
	 * @param array $fields
	 * @return array|bool|int
	 * @throws \Exception
	 */
	public function add(array $fields)
	{
		$result = false;

		if ($fields)
		{
			$addResult = TriggerTable::add($fields);
			$result = ($addResult->isSuccess() ? $addResult->getId() : false);
		}

		if (!$result)
		{
			$this->result->addError(new Error("Failed to copy trigger", self::TRIGGER_COPY_ERROR));
		}

		return $result;
	}
}