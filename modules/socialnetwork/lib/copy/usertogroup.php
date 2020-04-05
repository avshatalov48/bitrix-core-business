<?php
namespace Bitrix\Socialnetwork\Copy;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\EntityCopier;

class UserToGroup extends EntityCopier
{
	protected function setCopiedEntityId(Container $container, $copiedEntityId)
	{
		return;
	}
}