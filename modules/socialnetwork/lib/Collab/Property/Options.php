<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Property;

use Bitrix\Main\Type\Contract\Arrayable;

class Options implements Arrayable
{
	/**
	 * @var Option[] $options
	 */
	public function __construct(public readonly array $options)
	{
	}

	public function toArray(): array
	{
		$data = [];
		foreach ($this->options as $option)
		{
			$data = array_merge($data, $option->toArray());
		}

		return $data;
	}
}