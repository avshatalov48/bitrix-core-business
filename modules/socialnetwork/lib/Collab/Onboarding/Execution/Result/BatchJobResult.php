<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

final class BatchJobResult extends Result
{
	private JobCollection $completedJobs;
	private JobCollection $notCompletedJobs;

	public function __construct()
	{
		$this->completedJobs = new JobCollection();
		$this->notCompletedJobs = new JobCollection();

		parent::__construct();
	}

	public function addCompletedJob(AbstractJob $job): void
	{
		$this->completedJobs->add($job);
	}

	public function getCompletedJobs(): JobCollection
	{
		return $this->completedJobs;
	}

	public function addNotCompletedJob(AbstractJob $job): void
	{
		$this->notCompletedJobs->add($job);
	}

	public function getNotCompletedJobs(): JobCollection
	{
		return $this->notCompletedJobs;
	}
}
