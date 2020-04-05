<?php
namespace Bitrix\Vote\Copy;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Vote\Copy\Implement\Answer as AnswerImplementer;
use Bitrix\Vote\Copy\Implement\Question as QuestionImplementer;
use Bitrix\Vote\Copy\Implement\Vote as VoteImplementer;

class Manager
{
	private $voteIdsToCopy;

	private $resetVotingResult = true;

	private $markerQuestion = true;
	private $markerAnswer = true;

	public function __construct(array $voteIdsToCopy)
	{
		$this->voteIdsToCopy = $voteIdsToCopy;
	}

	public function markQuestion($marker)
	{
		$this->markerQuestion = (bool) $marker;
	}

	public function markAnswer($marker)
	{
		$this->markerAnswer = (bool) $marker;
	}

	public function setResetVotingResult(bool $bool): void
	{
		$this->resetVotingResult = $bool;
	}

	public function startCopy()
	{
		$containerCollection = $this->getContainerCollection();

		$voteCopier = $this->getVoteCopier();

		return $voteCopier->copy($containerCollection);
	}

	private function getContainerCollection()
	{
		$containerCollection = new ContainerCollection();

		foreach ($this->voteIdsToCopy as $id)
		{
			$containerCollection[] = new Container($id);
		}

		return $containerCollection;
	}

	private function getVoteCopier()
	{
		$voteImplementer = new VoteImplementer();
		$voteImplementer->setResetVotingResult($this->resetVotingResult);
		if ($this->markerQuestion)
		{
			$voteImplementer->setQuestionCopier($this->getQuestionCopier());
		}
		return new EntityCopier($voteImplementer);
	}

	private function getQuestionCopier()
	{
		$questionImplementer = new QuestionImplementer();
		$questionImplementer->setResetVotingResult($this->resetVotingResult);
		if ($this->markerAnswer)
		{
			$questionImplementer->setAnswerCopier($this->getAnswerCopier());
		}

		return new EntityCopier($questionImplementer);
	}

	private function getAnswerCopier()
	{
		$answerImplementer = new AnswerImplementer();
		$answerImplementer->setResetVotingResult($this->resetVotingResult);
		return new EntityCopier($answerImplementer);
	}
}