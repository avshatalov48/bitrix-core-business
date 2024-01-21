<?php

namespace Bitrix\Lists\Api\Request\IBlockService;

class GetIBlockDefaultFieldsRequest
{
	public function __construct(
		public /*readonly*/ int $iBlockId,
		public /*readonly*/ bool $needCheckPermissions = true,
	)
	{}
}
