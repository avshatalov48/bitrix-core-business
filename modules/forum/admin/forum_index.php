<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule("forum");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
$APPLICATION->SetTitle(GetMessage("FORUMS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D"):
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
endif;

IncludeModuleLangFile(__FILE__);

ob_start();
echo \Bitrix\Forum\Statistic\MembersStepper::getHtml();
echo \Bitrix\Forum\Statistic\TopicMembersStepper::getHtml();
echo \Bitrix\Forum\Statistic\ForumsStepper::getHtml();
echo \Bitrix\Forum\Statistic\Forum::getHtml();
echo \Bitrix\Main\Update\Stepper::getHtml("forum", GetMessage("FORUM_STEPPERS_MAIN"));
$res = ob_get_clean();
?><h3><?=GetMessage("FORUM_STEPPERS_TITLE")?></h3><?
if (empty($res))
{
	?><div><?=GetMessage("FORUM_STEPPERS_ARE_ABSENT")?></div><?
}
else
{
	echo $res;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
