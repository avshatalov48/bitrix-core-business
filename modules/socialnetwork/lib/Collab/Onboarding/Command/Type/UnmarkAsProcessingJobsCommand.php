<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Type;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\AbstractCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler\UnmarkAsProcessingJobHandler;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Rule\ArrayOfPositiveNumbers;

class UnmarkAsProcessingJobsCommand extends AbstractCommand
{
	#[ArrayOfPositiveNumbers]
	public readonly array $jobIds;

	public function __construct(int ...$jobIds)
	{
		$this->jobIds = $jobIds;

		parent::__construct();
	}

	protected function execute(): Result
	{
		$handler = new UnmarkAsProcessingJobHandler();

		return $handler($this);
	}
}