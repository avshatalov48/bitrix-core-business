<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Node;

use Bitrix\Landing\Copilot\Data\Type\NodeType;

class Text extends Node
{
	protected const TYPE = NodeType::Text;

	private ?array $values;
	private ?array $genderData = null;

	public function __construct(string $code, array $data)
	{
		parent::__construct($code, $data);
		$this->values = $data['values'] ?? [];
	}

	public function toArray(): array
	{
		$data = parent::toArray();
		$data['genderData'] = $this->getGenderData();

		return $data;
	}

	public function setData(array $data): void
	{
		$this->setValues($data);

		if (isset($data['genderData']))
		{
			$this->genderData = $data['genderData'];
		}
	}

	public function getValues(?int $position = null): array
	{
		return $this->values;
	}

	protected function setValues(array $values): self
	{
		$this->values = $values;

		return $this;
	}

	public function setGenderData(array $genderData): self
	{
		// todo: need node type checking!
		$this->genderData = $genderData;

		return $this;
	}

	// todo: use trait for repeated methods
	public function getGenderData(): ?array
	{
		return $this->genderData;
	}
}