<?php

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Tasks\Internals\Project\UserOption\UserOptionTypeDictionary;
use Bitrix\Tasks\Internals\Task\ProjectUserOptionTable;

Loc::loadMessages(__FILE__);

class WorkgroupTasksFilterMigrationProject extends WorkgroupTasksFilterMigration
{
	protected static $sourceFilterOptionName = 'TASKS_GRID_PROJECTS';
	protected static $targetFilterOptionName = 'SONET_GROUP_LIST_PROJECT';
	protected static $stepperNeedOptionName = 'needWorkgroupTaskFilterMigrationProject';
	protected static $stepperDataOptionName = 'workgrouptaskspinmigrationproject';
}
