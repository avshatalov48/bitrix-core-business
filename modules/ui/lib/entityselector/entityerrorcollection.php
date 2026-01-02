<?

namespace Bitrix\UI\EntitySelector;

use Traversable;

class EntityErrorCollection implements \IteratorAggregate, \JsonSerializable
{
	/** @var EntityError[] */
	protected array $errors = [];

	public function add(EntityError $error): void
	{
		$this->errors[] = $error;
	}

	public function count(): int
	{
		return count($this->errors);
	}

	public function getErrors(): array
	{
		return $this->errors;
	}

	public function getEntityErrors(string $entityId): array
	{
		$entityErrors = [];
		foreach ($this->errors as $error)
		{
			if ($error->getEntityId() === $entityId)
			{
				$entityErrors[] = $error;
			}
		}

		return $entityErrors;
	}

	public function getErrorByCode($code): ?EntityError
	{
		foreach ($this->errors as $error)
		{
			if ($error->getCode() === $code)
			{
				return $error;
			}
		}

		return null;
	}

	public function jsonSerialize(): array
	{
		return $this->getErrors();
	}

	public function getIterator(): Traversable
	{
		return new \ArrayIterator($this->errors);
	}
}
