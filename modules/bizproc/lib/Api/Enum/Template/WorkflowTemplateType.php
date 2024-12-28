<?php

namespace Bitrix\Bizproc\Api\Enum\Template;

enum WorkflowTemplateType: string //values are limited in DB varchar 15
{
	case Default = 'default';
	case Robots = 'robots';
	case CustomRobots = 'custom_robots';
}
