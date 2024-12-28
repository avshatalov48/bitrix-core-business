<?php

namespace Bitrix\Bizproc\Api\Enum\WorkflowFacesService;

enum WorkflowFacesStepStatus: string
{
	case Success = 'success';
	case NotSuccess = 'not-success';
	case Wait = 'wait';
}
