<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider\File;

use Bitrix\Main\Type\Contract\Arrayable;

class File implements Arrayable
{
	public function __construct(
		public readonly int $id,
		public readonly string $src,
	)
	{

	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'src' => $this->src,
		];
	}
}