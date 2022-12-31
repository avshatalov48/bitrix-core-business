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

class WorkgroupTasksFilterMigrationScrum extends WorkgroupTasksFilterMigration
{
	protected static $sourceFilterOptionName = 'TASKS_GRID_SCRUM';
	protected static $targetFilterOptionName = 'SONET_GROUP_LIST_SCRUM';
	protected static $stepperNeedOptionName = 'needWorkgroupTaskFilterMigrationScrum';
	protected static $stepperDataOptionName = 'workgrouptaskspinmigrationscrum';
}
