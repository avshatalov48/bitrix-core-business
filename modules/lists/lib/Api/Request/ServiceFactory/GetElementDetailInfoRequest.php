<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

class GetElementDetailInfoRequest
{
	public function __construct(
		public /*readonly*/ int $iBlockId,
		public /*readonly*/ int $elementId,
		public /*readonly*/ int $sectionId = 0,
		public /*readonly*/ array $additionalSelectFields = [],
		public /*readonly*/ bool $needCheckPermission = true,
	)
	{}
}
