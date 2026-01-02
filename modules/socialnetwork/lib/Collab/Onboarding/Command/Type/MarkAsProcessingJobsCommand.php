<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Type;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\AbstractCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler\MarkAsProcessingJobHandler;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Rule\ArrayOfPositiveNumbers;

class MarkAsProcessingJobsCommand extends AbstractCommand
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
		$handler = new MarkAsProcessingJobHandler();

		return $handler($this);
	}
}