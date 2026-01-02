<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text\BbCode;

use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Message\Text\ClosedBbCode;

final class User extends ClosedBbCode
{
	private int $userId;

	private function __construct(int $userId, string $innerText)
	{
		parent::__construct($innerText);
		$this->userId = $userId;
	}

	public static function build(int $userId, string $innerText = ''): self
	{
		return new self($userId, $innerText);
	}

	protected function getValue(): ?string
	{
		return (string)$this->userId;
	}

	public function toPlain(): string
	{
		if ($this->innerText !== '')
		{
			return parent::toPlain();
		}

		$user = Entity\User\User::getInstance($this->userId);
		if ($user instanceof Entity\User\NullUser)
		{
			return '';
		}

		return $user->getName() ?? '';
	}

	protected function getAdditionalParams(): array
	{
		return [];
	}

	public static function getName(): string
	{
		return 'USER';
	}
}
