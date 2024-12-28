<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Control\Command\ValueObject\CreateObjectInterface;

abstract class AbstractOption implements CreateObjectInterface
{
	protected CollabProvider $provider;

	protected string $name;
	protected string $value;

	/**
	 * @throws ObjectNotFoundException
	 */
	public static function create(mixed $data): static
	{
		$name = array_key_first($data);
		$value = $data[$name];

		return OptionFactory::createOption($name, $value);
	}

	public function __construct(string $name, string $value)
	{
		$this->name = $name;
		$this->value = $value;

		$this->init();
	}

	abstract protected function applyImplementation(Collab $collab): Result;

	public function apply(int $collabId): Result
	{
		$result = new Result();

		$collab = $this->provider->disableCache()->getCollab($collabId);
		if ($collab === null)
		{
			$result->addError(new Error('Collab not found'));

			return $result;
		}

		return $this->applyImplementation($collab);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	protected function init(): void
	{
		$this->provider = CollabProvider::getInstance();
	}
}
