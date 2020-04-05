<?php
namespace Bitrix\Vote\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Result;
use Bitrix\Vote\Event;
use Bitrix\Vote\EventTable;
use Bitrix\Vote\QuestionTable;
use Bitrix\Vote\Vote as VoteBase;
use Bitrix\Vote\VoteTable;

class Vote extends CopyImplementer
{
	private $resetVotingResult = true;

	public function setResetVotingResult(bool $bool): void
	{
		$this->resetVotingResult = $bool;
	}

	/**
	 * @var EntityCopier|null
	 */
	private $questionCopier = null;

	/**
	 * To copy questions needs question copier.
	 * @param EntityCopier $questionCopier
	 */
	public function setQuestionCopier(EntityCopier $questionCopier): void
	{
		$this->questionCopier = $questionCopier;
	}

	/**
	 * Adds vote.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return bool|int
	 * @throws \Exception
	 */
	public function add(Container $container, array $fields)
	{
		$result = VoteTable::add($fields);
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
	 * Returns vote fields.
	 * @param Container $container
	 * @param int $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = VoteTable::getById($entityId);
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
	 * @param int $entityId Vote id.
	 * @param int $copiedEntityId Copied vote id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		$results = [];

		$results[] = $this->copyQuestion($entityId, $copiedEntityId);

		$result = $this->getResult($results);

		if (!$this->resetVotingResult)
		{
			$this->copyEvents($entityId, $copiedEntityId, $result);
		}

		return $result;
	}

	private function copyQuestion(int $voteId, int $copiedVoteId)
	{
		if (!$this->questionCopier)
		{
			return new Result();
		}

		$containerCollection = new ContainerCollection();

		$queryObject = QuestionTable::getList(["filter" => ["VOTE_ID" => $voteId]]);
		while ($question = $queryObject->fetch())
		{
			$container = new Container($question["ID"]);
			$container->setParentId($copiedVoteId);
			$containerCollection[] = $container;
		}

		if (!$containerCollection->isEmpty())
		{
			return $this->questionCopier->copy($containerCollection);
		}

		return new Result();
	}

	private function copyEvents($voteId, $copiedVoteId, Result $result)
	{
		try
		{
			$copiedIdsRelation = $this->getCopiedIdsRelation($result);
			$ballots = $this->getEventBallots($voteId, $copiedIdsRelation);

			$voteBaseCopiedVote = new VoteBase($copiedVoteId);
			$eventObject = new Event($voteBaseCopiedVote);

			$queryObject = EventTable::getList(["filter" => ["VOTE_ID" => $voteId]]);
			while ($event = $queryObject->fetch())
			{
				$ballot = $ballots[$event["ID"]];

				unset($event["ID"]);
				$event["VOTE_ID"] = $copiedVoteId;

				$eventObject->add($event, $ballot, false);
			}
		}
		catch (\Exception $exception) {}
	}

	private function getCopiedIdsRelation(Result $result)
	{
		$copiedIdsRelation = [];

		$resultData = $result->getData();
		foreach ($resultData as $data)
		{
			array_walk($data, function($item, $key) use (&$copiedIdsRelation) {
				if (is_array($item))
				{
					$copiedIdsRelation["answer"] = $item;
				}
				else
				{
					$copiedIdsRelation[$key] = $item;
				}
			});
		}

		return $copiedIdsRelation;
	}

	/**
	 * @param $voteId
	 * @param $copiedIdsRelation
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private function getEventBallots($voteId, $copiedIdsRelation)
	{
		$ballots = [];

		$questionIds = [];
		$answerIds = [];
		foreach ($copiedIdsRelation as $key => $value)
		{
			if (is_int($key))
			{
				$questionIds[$key] = $value;
			}
			else
			{
				$answerIds = $answerIds + $value;
			}
		}

		$voteBase = new VoteBase($voteId);
		foreach ($voteBase->getStatistic() as $data)
		{
			$ballot = [];
			foreach ($data["BALLOT"] as $questionId => $answer)
			{
				foreach ($answer as $answerId => $answerMessage)
				{
					$ballot[$questionIds[$questionId]] = [
						$answerIds[$answerId] => $answerMessage
					];
				}
			}
			$ballots[$data["ID"]] = ["BALLOT" => $ballot];
		}

		return $ballots;
	}
}