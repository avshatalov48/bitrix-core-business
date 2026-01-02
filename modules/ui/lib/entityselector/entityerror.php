<?

namespace Bitrix\UI\EntitySelector;

class EntityError extends \Bitrix\Main\Error
{
	protected string $entityId;

	public function __construct(string $entityId, ...$args)
	{
		$this->entityId = $entityId;

		parent::__construct(...$args);
	}

	public function getEntityId(): string
	{
		return $this->entityId;
	}

	public function jsonSerialize(): array
	{
		return [
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
			'entityId' => $this->getEntityId(),
			'customData' => $this->getCustomData(),
		];
	}
}
