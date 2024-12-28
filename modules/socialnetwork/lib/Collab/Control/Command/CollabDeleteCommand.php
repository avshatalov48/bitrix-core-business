<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command;

use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;

#[AccessController(CollabAccessController::class)]
class CollabDeleteCommand extends DeleteCommand
{

}