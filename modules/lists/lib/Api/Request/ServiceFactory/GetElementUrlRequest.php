<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

final class GetElementUrlRequest
{
	public function __construct(
		public readonly int $iBlockId,
		public readonly int $elementId,
	)
	{}
}
