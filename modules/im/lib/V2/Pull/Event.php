<?php

namespace Bitrix\Im\V2\Pull;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Pull\Dto\Group;
use Bitrix\Im\V2\Result;

interface Event
{
	public function send(): Result;
	public function getBase(): array;

	/**
	 * @return Group[]
	 */
	public function getPullByUsers(): array;

	/**
	 * @return Group[]
	 */
	public function getMobilePushByUsers(): array;
	public function getTarget(): ?Chat;
	public function isGlobal(): bool;
	public function shouldSendToOnlySpecificRecipients(): bool;
	public function shouldSendMobilePush(): bool;
	public function shouldSendImmediately(): bool;
}
