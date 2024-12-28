<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Decorator;

use Bitrix\Socialnetwork\Collab\Requirement;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Decorator\AbstractGroupServiceDecorator;
use Bitrix\Socialnetwork\Control\GroupResult;

class RequirementDecorator extends AbstractGroupServiceDecorator
{
	public function add(AddCommand $command): GroupResult
	{
		$result = new GroupResult();

		$requirementResult = Requirement::check();

		if (!$requirementResult->isSuccess())
		{
			$result->addErrors($requirementResult->getErrors());

			return $this->finalizeAddResult($result);
		}

		return $this->source->add($command);
	}
}