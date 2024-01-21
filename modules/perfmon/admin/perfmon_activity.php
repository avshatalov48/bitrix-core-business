<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

IncludeModuleLangFile(__FILE__);

$RIGHT = CMain::GetGroupRight('perfmon');
if (!$USER->IsAdmin() || ($RIGHT < 'W'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (
	$request->isPost()
	&& (string)$request->get('activity') !== ''
	&& check_bitrix_sessid()
)
{
	if ($request->getPost('ACTIVE') !== null)
	{
		$ACTIVE = intval($request->getPost('ACTIVE'));
		CPerfomanceKeeper::SetActive($ACTIVE > 0, time() + $ACTIVE);
	}
	LocalRedirect('/bitrix/admin/perfmon_activity.php?lang=' . LANGUAGE_ID);
}

$APPLICATION->SetTitle(GetMessage('PERFMON_ACTIVITY_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$interval = COption::GetOptionInt('perfmon', 'end_time') - time();
if ($interval <= 0)
{
	CPerfomanceKeeper::SetActive(false);
	$ACTIVE = false;
}
else
{
	$ACTIVE = CPerfomanceKeeper::IsActive();
}
?>

<form name="clear_form" method="post" action="<?php echo $APPLICATION->GetCurPage();?>">
	<?php echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
	<?php if ($ACTIVE):?>
		<h3><?php echo GetMessage('PERFMON_ACTIVE_Y')?></h3>
		<p><?php echo GetMessage('PERFMON_ACT_ACTIVE_TO')?>:
			<?php
			$hours = intval($interval / 3600);
			$interval -= $hours * 3600;
			$minutes = intval($interval / 60);
			$interval -= $minutes * 60;
			$seconds = intval($interval);
			echo GetMessage('PERFMON_ACT_MINUTES', ['#HOURS#' => $hours, '#MINUTES#' => $minutes, '#SECONDS#' => $seconds]);
			?></p>
		<p>
			<label for="ACTIVE"><?php echo GetMessage('PERFMON_ACT_SET_IN_ACTIVE')?></label>:
			<input type="checkbox" name="ACTIVE" value="0" id="ACTIVE">
		</p>
		<input type="submit" name="activity" value="<?php echo GetMessage('PERFMON_ACTION_BUTTON_OFF');?>">
	<?php else:?>
		<h3><?php echo GetMessage('PERFMON_ACTIVE_N')?></h3>
		<p><label for="ACTIVE"><?php echo GetMessage('PERFMON_ACT_SET_ACTIVE')?></label>:
		<select name="ACTIVE" id="ACTIVE">
			<option value="0"><?php echo GetMessage('PERFMON_ACT_INTERVAL_NO')?></option>
			<option value="60"><?php echo GetMessage('PERFMON_ACT_INTERVAL_60_SEC')?></option>
			<option value="300"><?php echo GetMessage('PERFMON_ACT_INTERVAL_300_SEC')?></option>
			<option value="600"><?php echo GetMessage('PERFMON_ACT_INTERVAL_600_SEC')?></option>
			<option value="1800"><?php echo GetMessage('PERFMON_ACT_INTERVAL_1800_SEC')?></option>
			<option value="3600"><?php echo GetMessage('PERFMON_ACT_INTERVAL_3600_SEC')?></option>
		</select></p>
		<input type="submit" name="activity" value="<?php echo GetMessage('PERFMON_ACTION_BUTTON_ON');?>">
	<?php endif;?>
</form>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
