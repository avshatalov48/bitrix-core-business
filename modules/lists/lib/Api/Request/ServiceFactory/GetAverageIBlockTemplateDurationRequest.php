<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

class GetAverageIBlockTemplateDurationRequest
{
	public function __construct(
		public /*readonly*/ int $iBlockId,
		public /*readonly*/ int $autoExecuteType,
		public /*readonly*/ bool $isNeedCheckPermissions = true,
	)
	{}
}
