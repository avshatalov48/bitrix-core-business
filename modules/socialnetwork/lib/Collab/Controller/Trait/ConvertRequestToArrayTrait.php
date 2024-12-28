<?php

namespace Bitrix\Socialnetwork\Collab\Controller\Trait;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\Dictionary;

trait ConvertRequestToArrayTrait
{
	public static function convertRequest(mixed $request): array
	{
		if (is_array($request))
		{
			return $request;
		}

		if ($request instanceof HttpRequest)
		{
			return $request->getPostList()->toArray();
		}

		if ($request instanceof Arrayable)
		{
			return $request->toArray();
		}

		if ($request instanceof Dictionary)
		{
			return $request->toArray();
		}

		return [];
	}
}