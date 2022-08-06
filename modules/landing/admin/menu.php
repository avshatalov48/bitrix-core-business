<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('landing'))
{
	return;
}

if (Manager::isB24())
{
	return;
}

$application = Manager::getApplication();
if ($application->getGroupRight('landing') < 'W')
{
	return;
}

$menu = array(
	'parent_menu' => 'global_menu_landing',
	'section' => 'landing',
	'sort' => 50,
	'text' => Loc::getMessage('LANDING_MENU_SITES'),
	'icon' => 'landing_menu_icon',
	'items_id' => 'menu_landing',
	'url' => '',
	'more_url' => array(),
	'items' => array()
);

$sites = array();
$res = \Bitrix\Main\SiteTable::getList(array(
	'select' => array(
		'LID', 'NAME'
	),
	'filter' => array(
		'=ACTIVE' => 'Y'
	),
	'order' => array(
		'SORT' => 'ASC'
	),
	'cache' => ['ttl' => 86400],
));
while ($row = $res->fetch())
{
	$sites[$row['LID']] = $row;
}

if (!empty($sites))
{
	$res = \Bitrix\Landing\Site::getList(array(
		'select' => array(
			'ID', 'TITLE', 'SMN_SITE_ID', 'TYPE'
		),
		'filter' => array(
			'=SMN_SITE_ID' => array_keys($sites),
			'CHECK_PERMISSIONS' => 'N'
		)
	));
	while ($row = $res->fetch())
	{
		$sites[$row['SMN_SITE_ID']]['NAME'] = $row['TITLE'];
	}
}

foreach ($sites as $row)
{
	$menu['items'][] = array(
		'text' => $row['NAME'],
		'url' => 'landing_site.php?lang=' . LANGUAGE_ID . '&site=' . $row['LID']
	);
}

$menu['items'][] = array(
	'text' => Loc::getMessage('LANDING_MENU_SITE_ADD'),
	'url' => 'site_edit.php?lang=' . LANGUAGE_ID . '&landing=Y'
);


return $menu;