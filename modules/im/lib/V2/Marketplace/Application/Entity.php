<?php

namespace Bitrix\Im\V2\Marketplace\Application;

use Bitrix\Im\Color;
use Bitrix\Im\V2\Marketplace\Placement;
use Bitrix\Im\V2\Marketplace\Types\Context;
use Bitrix\Im\V2\Rest\RestEntity;

class Entity implements RestEntity
{
	public ?int $id = null;
	public ?string $placement = null;
	public ?string $title = null;
	public ?int $restApplicationId = null;
	public ?array $options = null;
	public ?int $order = null;


	public function __construct(?array $fields = null)
	{
		if ($fields !== null)
		{
			$this->hydrate($fields);
		}
	}

	public function hydrate(array $fields): void
	{
		$this->id = $fields['id'] ?? null;
		$this->placement = $fields['placement'] ?? null;
		$this->title = $fields['title'] ?? null;
		$this->restApplicationId = $fields['restApplicationId'] ?? null;
		$this->order = $fields['order'] ?? null;
		$this->hydrateOptions(($fields['options'] ?? null));
	}

	private function hydrateOptions(?array $options): void
	{
		if (!$options)
		{
			return;
		}

		if (isset($options['role']))
		{
			$this->options['role'] = mb_strtolower($options['role']);
		}
		if (isset($options['extranet']))
		{
			$this->options['extranet'] = $options['extranet'] === 'Y'? 'Y': 'N';
		}
		if (isset($options['context']))
		{
			$this->options['context'] = $this->getContext($options['context']);
		}
		if (isset($options['width']))
		{
			$this->options['width'] = $options['width'];
		}
		if (isset($options['height']))
		{
			$this->options['height'] = $options['height'];
		}
		if (isset($options['iconName']))
		{
			$this->options['iconName'] = $options['iconName'];
		}
		if ($this->placement === Placement::IM_TEXTAREA	|| $this->placement === Placement::IM_SIDEBAR)
		{
			$this->options['color'] = Color::getColor($options['color']) ?? Color::getColorByNumber($this->id);
		}
	}

	private function getContext(string $contextOption): array
	{
		$userContextList = explode(';', trim($contextOption));
		if (in_array(Context::ALL, $userContextList, true))
		{
			return [mb_strtolower(Context::ALL)];
		}

		return array_map('mb_strtolower', $userContextList);
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public static function getRestEntityName(): string
	{
		return 'placementApplication';
	}

	protected function toRestFormatOptions(): array
	{
		$options = $this->options;
		if (isset($options['extranet']))
		{
			unset($options['extranet']);
		}
		if (isset($options['role']))
		{
			unset($options['role']);
		}

		return $options;
	}

	/**
	 * @inheritDoc
	 */
	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => (int)$this->id,
			'title' => $this->title,
			'options' => $this->toRestFormatOptions(),
			'placement' => $this->placement,
			'order' => $this->order,
			'loadConfiguration' => [
				'ID' => $this->restApplicationId,
				'PLACEMENT' =>  $this->placement,
				'PLACEMENT_ID' => $this->id,
			],
		];
	}
}