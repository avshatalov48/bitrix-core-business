<?php
/** @var CUpdater $updater */
/** @var CDatabase $DB */

if ($updater->TableExists("`b_im_link_calendar`") && $updater->CanUpdateDatabase())
{
	if (!$DB->IndexExists("b_im_link_calendar", array("MESSAGE_ID", ), true))
	{
		$DB->Query("CREATE INDEX `IX_B_IM_LINK_CALENDAR_5` ON `b_im_link_calendar`(`MESSAGE_ID`)");
	}
}
if ($updater->CanUpdateDatabase() && $updater->TableExists("`b_im_link_task`"))
{
	if (!$DB->IndexExists("b_im_link_task", array("MESSAGE_ID", ), true))
	{
		$DB->Query("CREATE INDEX `IX_B_IM_LINK_TASK_2` ON `b_im_link_task`(`MESSAGE_ID`)");
	}
}

if (IsModuleInstalled('im') && $updater->CanUpdateKernel())
{
	//Following copy was parsed out from module installer
	$updater->CopyFiles("install/js", "js");
	$updater->CopyFiles("install/components", "components");
}
if ($updater->canUpdateKernel())
{
	$filesToDelete = [
		'modules/im/install/js/im/css/phone_call_view.css',
		'js/im/css/phone_call_view.css',
		'modules/im/install/js/im/images/im-sprite.svg',
		'js/im/images/im-sprite.svg',
		'modules/im/install/js/im/phone_call_view.js',
		'js/im/phone_call_view.js',
		'modules/im/lang/de/js_phone_call_view.php',
		'modules/im/lang/en/js_phone_call_view.php',
		'modules/im/lang/ru/js_phone_call_view.php',
	];
	foreach ($filesToDelete as $fileName)
	{
		CUpdateSystem::deleteDirFilesEx($_SERVER['DOCUMENT_ROOT'] . $updater->kernelPath . '/' . $fileName);
	}
}
