<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Node;

use Bitrix\Landing\Copilot\Data\Type\NodeType;

class Link extends Node
{
	protected const TYPE = NodeType::Link;

	private ?array $texts;
	private ?array $href;
	private ?array $target;
	private ?array $genderData = null;

	public function __construct(string $code, array $data)
	{
		parent::__construct($code, $data);
		$this->texts = $data['values'] ?? [];
		$this->href = $data['href'] ?? ['#'];
		$this->target = $data['target'] ?? ['_blank'];
	}

	public function toArray(): array
	{
		$data = parent::toArray();
		$data['genderData'] = $this->getGenderData();

		return $data;
	}

	// todo: incorrect format. It is not data, it is setText
	// todo: need check all nodes setData functions. I think in must be operations from constructor
	public function setData(array $data): void
	{
		$this->setTexts($data);

		if (isset($data['genderData']))
		{
			$this->genderData = $data['genderData'];
		}
	}

	public function getValues(?int $position = null): array
	{
		$nodeDataArray = [];
		foreach ($this->texts as $text)
		{
			$nodeDataArray[] = [
				'text' => $text,
				'href' => $this->href[0],
				'target' => $this->target[0],
			];
		}

		return $nodeDataArray;
	}

	protected function setTexts(array $texts): void
	{
		$this->texts = $texts;
	}

	public function setGenderData(array $genderData): self
	{
		$this->genderData = $genderData;

		return $this;
	}

	// todo: use trait for repeated methods
	public function getGenderData(): ?array
	{
		return $this->genderData;
	}
}