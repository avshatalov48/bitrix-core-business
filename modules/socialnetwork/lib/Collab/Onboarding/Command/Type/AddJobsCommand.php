<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Type;

use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\AbstractCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler\AddJobsHandler;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

class AddJobsCommand extends AbstractCommand
{
	#[Validatable]
	public readonly JobCollection $jobs;

	public function __construct(JobCollection $jobs)
	{
		$this->jobs = $jobs;

		parent::__construct();
	}

	protected function execute(): Result
	{
		$handler = new AddJobsHandler();

		return $handler($this);
	}
}