<?php
namespace Bitrix\Vote\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Result;
use Bitrix\Vote\AnswerTable;

class Answer extends CopyImplementer
{
	private $resetVotingResult = true;

	public function setResetVotingResult(bool $bool): void
	{
		$this->resetVotingResult = $bool;
	}

	/**
	 * Adds answer.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return answer id or false.
	 * @throws \Exception
	 */
	public function add(Container $container, array $fields)
	{
		$result = AnswerTable::add($fields);
		if ($result->isSuccess())
		{
			return $result->getId();
		}
		else
		{
			$this->result->addErrors($result->getErrors());
			return false;
		}
	}

	/**
	 * Returns entity fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = AnswerTable::getById($entityId);
		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new answer.
	 *
	 * @param Container $container
	 * @param array $fields List answer fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		unset($fields["ID"]);

		if ($container->getParentId())
		{
			$fields["QUESTION_ID"] = $container->getParentId();
		}

		if ($this->resetVotingResult)
		{
			unset($fields["COUNTER"]);
		}

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId Entity id.
	 * @param int $copiedEntityId Copied entity id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		return $this->getResult();
	}
}