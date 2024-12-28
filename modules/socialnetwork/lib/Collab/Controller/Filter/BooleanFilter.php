<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Filter;

use Bitrix\Main\Type\IRequestFilter;

class BooleanFilter implements IRequestFilter
{
	public function filter(array $values): ?array
	{
		if (empty($values['post']) || !is_array($values['post']))
		{
			return null;
		}

		return [
			'post' => $this->prepareBooleanValues($values['post']),
		];
	}

	private function prepareBooleanValues($data): mixed
	{
		if ($data === 'true' || $data === 'Y' || $data === '1')
		{
			return true;
		}

		if ($data === 'false' || $data === 'N' || $data === '0')
		{
			return false;
		}

		if ($data === 'null')
		{
			return null;
		}

		if (is_array($data))
		{
			foreach ($data as $k => $v)
			{
				$data[$k] = $this->prepareBooleanValues($v);
			}
		}

		return $data;
	}
}