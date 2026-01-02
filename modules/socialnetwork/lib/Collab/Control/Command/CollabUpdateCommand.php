<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command;

use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\SocialNetwork\Validation\Rule\NotContainsUrl;

/**
 * @method self setOptions(CollabOptions $options)
 * @method CollabOptions|null getOptions()
 * @method self setInitiatedByType(string|null $type)
 * @method string getInitiatedByType()
 */
#[AccessController(CollabAccessController::class)]
class CollabUpdateCommand extends UpdateCommand
{
	#[NotContainsUrl]
	#[Map('NAME')]
	#[NotEmpty]
	protected ?string $name;

	#[Validatable]
	protected ?CollabOptions $options;

	#[InArray([null, UserToGroupTable::INITIATED_BY_GROUP, UserToGroupTable::INITIATED_BY_USER, UserToGroupTable::INITIATED_BY_STRUCTURE])]
	protected ?string $initiatedByType = null;

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
