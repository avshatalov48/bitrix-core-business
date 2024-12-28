<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command;

use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

/**
 * @method self setOptions(CollabOptions $options)
 * @method CollabOptions|null getOptions()
 */
#[AccessController(CollabAccessController::class)]
class CollabUpdateCommand extends UpdateCommand
{
	#[Validatable]
	protected ?CollabOptions $options;

	public function addOption(AbstractOption $option): static
	{
		if (!isset($this->options))
		{
			$this->options = new CollabOptions();
		}

		$this->options->addOption($option);

		return $this;
	}
}
