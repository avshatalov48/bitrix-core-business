<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

final class GetIBlockInfoRequest
{
	public function __construct(
		public /*readonly*/ int $iBlockId,
		public /*readonly*/ bool $needCheckPermissions = true,
	)
	{}
}
