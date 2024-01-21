<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

class GetIBlockFieldsRequest
{
	public function __construct(
		public /*readonly*/ int $iBlockId,
		public /*readonly*/ bool $loadEnumValues = false,
		public /*readonly*/ bool $loadDefaultFields = false,
		public /*readonly*/ bool $needCheckPermissions = true,
	)
	{}
}
