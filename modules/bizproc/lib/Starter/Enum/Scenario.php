<?php

namespace Bitrix\Bizproc\Starter\Enum;

enum Scenario: string
{
	case onDocumentInnerAdd = 'onInnerAdd';
	case onDocumentAdd = 'onAdd';
	case onDocumentInnerUpdate = 'onInnerUpdate';
	case onDocumentUpdate = 'onUpdate';
	case onEvent = 'onEvent';
	case onManual = 'onManual';
	case onScript = 'onScript';
}
