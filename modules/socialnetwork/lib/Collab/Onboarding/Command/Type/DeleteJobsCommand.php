<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command\Type;

use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\AtLeastOnePropertyNotEmpty;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\AbstractCommand;
use Bitrix\Socialnetwork\Collab\Onboarding\Command\Handler\DeleteJobCollectionHandler;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Rule\ArrayOfJobTypes;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Rule\ArrayOfPositiveNumbers;

#[AtLeastOnePropertyNotEmpty(['jobIds', 'userIds', 'collabIds'])]
class DeleteJobsCommand extends AbstractCommand
{
	#[ArrayOfPositiveNumbers]
	public readonly array $jobIds;

	#[ArrayOfJobTypes]
	public readonly array $jobTypes;

	#[ArrayOfPositiveNumbers]
	public readonly array $userIds;

	#[ArrayOfPositiveNumbers]
	public readonly array $collabIds;

	/**
	 * @param array $filter
	 * @example:
	 *  [
	 *     'JOB_IDS' => [1, 2, 3],
	 *     'USER_IDS' => [1, 2, 3],
	 *     'COLLAB_IDS' => [1, 2, 3],
	 *  ]
	 */
	public function __construct(array $filter = [])
	{
		$this->jobIds = $filter['JOB_IDS'] ?? [];
		$this->jobTypes = $filter['JOB_TYPES'] ?? [];
		$this->userIds = $filter['USER_IDS'] ?? [];
		$this->collabIds = $filter['COLLAB_IDS'] ?? [];

		parent::__construct();
	}

	protected function execute(): Result
	{
		$handler = new DeleteJobCollectionHandler();

		return $handler($this);
	}
}