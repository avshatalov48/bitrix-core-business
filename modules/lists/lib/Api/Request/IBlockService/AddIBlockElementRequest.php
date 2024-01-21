<?php

namespace Bitrix\Lists\Api\Request\IBlockService;

class AddIBlockElementRequest
{
	public function __construct(
		public /* readonly */ int $iBlockId,
		public /* readonly */ int $sectionId,
		public /* readonly */ array $values,
		public /* readonly */ int $createdByUserId,
		public /* readonly */ bool $needStartWorkflows = true,
		public /* readonly */ bool $needCheckPermissions = true,
		public /* readonly */ array $wfParameterValues = [],
		public /* readonly */ ?int $timeToStart = null,
	)
	{}
}
