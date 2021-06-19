<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
global $APPLICATION;
if ($APPLICATION->GetGroupRight('forum') == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/forum/prolog.php');

use \Bitrix\Forum;
use \Bitrix\Main;

Main\Loader::includeModule('forum');

$dbRes = Forum\ForumTable::getList([
	'select' => ['*'],
	'order' => ['SORT' => 'ASC', 'NAME' => 'ASC'],
	'cache' => [
		'ttl' => 3600
	]
]);
$forums = [];
while ($forum = $dbRes->fetch())
{
	$forums[$forum['ID']] = htmlspecialcharsbx($forum['NAME']);
}

$sTableID = 'tbl_topic';
$oSort = new CAdminUiSorting($sTableID, 'ID', 'desc');
$lAdmin = new CAdminUiList($sTableID, $oSort);

//region Filter
$filter = [];
$filterFields = [
	[
		'id' => 'FORUM_ID',
		'name' => GetMessage('FM_TITLE_FORUM'),
		'type' => 'list',
		'items' => $forums,
	],
	[
		'id' => 'START_DATE',
		'name' => GetMessage('FM_TITLE_DATE_CREATE'),
		'type' => 'date',
	],
	[
		'id' => 'LAST_POST_DATE',
		'name' => GetMessage('FM_TITLE_DATE_LAST_POST'),
		'type' => 'date',
	]
];
$lAdmin->AddFilter($filterFields, $filter);
//endregion
//region Page & PageNavigation
$nav = $lAdmin->getPageNavigation('pages-topic-admin');
global $by, $order;
$query = new Main\ORM\Query\Query(Forum\TopicTable::getEntity());
$query->setSelect(['*'])
	->setFilter($filter)
	->setOrder([$by => $order])
	->setOffset($nav->getOffset())
	->setLimit($nav->getLimit() + 1);
if ($lAdmin->isTotalCountRequest())
{
	$query->countTotal(true);
	$lAdmin->sendTotalCountResponse($query->exec()->getCount());
}

$result = $query->exec();
$n = 0;
$pageSize = $lAdmin->getNavSize();
while ($data = $result->fetch())
{
	$n++;
	if ($n > $pageSize)
	{
		break;
	}
	$row =& $lAdmin->addRow($data['ID'], $data);
	$row->bReadOnly = true;
	$row->AddViewField('ACTION',
		' <input type= "button" onclick="setValue('.intval($data['ID']).', \''.htmlspecialcharsbx($data['TITLE']).'\')" value="'.GetMessage('MAIN_SELECT').'">');
}
$nav->setRecordCount($nav->getOffset() + $n);
$lAdmin->setNavigation($nav, GetMessage('FM_TOPICS'), false);
$lAdmin->AddHeaders(array(
	array('id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true),
	array('id' => 'TITLE', 'content' => GetMessage('FM_TITLE_NAME'), 'sort' => 'TITLE', 'default' => true),
	array('id' => 'START_DATE', 'content' => GetMessage('FM_TITLE_DATE_CREATE'), 'sort' => 'START_DATE', 'default' => true),
	array('id' => 'USER_START_NAME', 'content' => GetMessage('FM_TITLE_AUTHOR'), 'sort' => 'USER_START_NAME', 'default' => true),
	array('id' => 'POSTS', 'content' => GetMessage('FM_TITLE_MESSAGES'), 'sort' => 'POSTS', 'default' => false),
	array('id' => 'VIEWS', 'content' => GetMessage('FM_TITLE_VIEWS'), 'sort' => 'VIEWS', 'default' => false),
	array('id' => 'FORUM_ID', 'content' => GetMessage('FM_TITLE_FORUM'), 'sort' => 'FORUM_ID'),
	array('id' => 'LAST_POST_DATE', 'content' => GetMessage('FM_TITLE_LAST_MESSAGE'), 'sort' => 'LAST_POST_DATE'),
	array('id' => 'ACTION', 'content' => GetMessage('MAIN_ACTION'), 'default' => true),
));
//endregion
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('FORUM_TOPICS'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_popup_admin.php');

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList(['SHOW_COUNT_HTML' => true]);
$nodeId = Main\Context::getCurrent()->getRequest()->get('nodeId');
?>
<script>
function setValue(id, title)
{
	var element = window.opener.document.getElementById('<?=htmlspecialcharsbx($nodeId)?>');
	element.value = id;
	if ('createEvent' in document)
	{
		var evt = document.createEvent('HTMLEvents');
		evt.initEvent('change', false, true);
		element.dispatchEvent(evt);
	}
	else
	{
		element.fireEvent('onchange');
	}
	window.close();
}
//-->
</script>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_popup_admin.php');
