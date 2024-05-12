<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

final class UpdateElementRequest
{
	public function __construct(
		public /* readonly */ int $elementId,
		public /* readonly */ int $iBlockId,
		public /* readonly */ int $sectionId,
		public /* readonly */ array $values,
		public /* readonly */ int $modifiedByUserId,
		public /* readonly */ bool $needCheckPermission,
		public /* readonly */ bool $needStartWorkflows,
		public /* readonly */ array $wfParameterValues = [],
		public /* readonly */ ?int $timeToStart = null,
	)
	{}
}
