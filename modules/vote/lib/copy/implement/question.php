<?php
namespace Bitrix\Vote\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Result;
use Bitrix\Vote\AnswerTable;
use Bitrix\Vote\QuestionTable;

class Question extends CopyImplementer
{
	private $resetVotingResult = true;

	public function setResetVotingResult(bool $bool): void
	{
		$this->resetVotingResult = $bool;
	}

	/**
	 * @var EntityCopier|null
	 */
	private $answerCopier = null;

	/**
	 * To copy questions needs question copier.
	 * @param EntityCopier $answerCopier
	 */
	public function setAnswerCopier(EntityCopier $answerCopier): void
	{
		$this->answerCopier = $answerCopier;
	}

	/**
	 * Adds question.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return entity id or false.
	 * @throws \Exception
	 */
	public function add(Container $container, array $fields)
	{
		$result = QuestionTable::add($fields);
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
	 * Returns question fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = QuestionTable::getById($entityId);
		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		unset($fields["ID"]);

		$fields["FIELD_TYPE"] = (int) $fields["FIELD_TYPE"];

		if ($container->getParentId())
		{
			$fields["VOTE_ID"] = $container->getParentId();
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
		$results = [];

		$results[] = $this->copyAnswer($entityId, $copiedEntityId);

		return $this->getResult($results);
	}

	private function copyAnswer(int $questionId, int $copiedQuestionId)
	{
		if (!$this->answerCopier)
		{
			return new Result();
		}

		$containerCollection = new ContainerCollection();
		$queryObject = AnswerTable::getList(["order" => [], "filter" => ["QUESTION_ID" => $questionId]]);
		while ($question = $queryObject->fetch())
		{
			$container = new Container($question["ID"]);
			$container->setParentId($copiedQuestionId);
			$containerCollection[] = $container;
		}

		if (!$containerCollection->isEmpty())
		{
			return $this->answerCopier->copy($containerCollection);
		}

		return new Result();
	}
}