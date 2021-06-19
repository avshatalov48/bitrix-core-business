<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Conversion\RateManager;
use Bitrix\Conversion\AttributeManager;
use Bitrix\Conversion\AttributeGroupManager;
use Bitrix\Conversion\ReportContext;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::IncludeModule('conversion');

if ($APPLICATION->GetGroupRight('conversion') < 'R')
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$userOptions = CUserOptions::GetOption('conversion', 'filter', array());

// PERIOD

$from = ($d = $_GET['from'] ?: $userOptions['from']) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('first day of last month'));
$to   = ($d = $_GET['to'  ] ?: $userOptions['to'  ]) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('last day of this month'));

// RATES

if (! $rateTypes = RateManager::getTypes())
	die ('No rates available!');

$rateName = $_GET['rate'] ?: $userOptions['rate'];

if (! $rateType = $rateTypes[$rateName])
{
	$rateName = key($rateTypes);
	$rateType = current($rateTypes);
}

// SITES

$sites = array();

$result = SiteTable::getList(array(
	'select' => array('LID', 'NAME'),
	'order'  => array('DEF' => 'DESC', 'SORT' => 'ASC'),
));

while ($row = $result->fetch())
{
	$sites[$row['LID']] = $row['NAME'];
}

if (! $sites)
	die ('No sites available!');

$site = $_GET['site'] ?: $userOptions['site'];

if (! $siteName = $sites[$site])
{
	$site = key($sites);
	$siteName = current($sites);
}

// ATTRIBUTES

if (! $attributeTypes = AttributeManager::getTypes())
	die ('No attributes!');

unset($attributeTypes['conversion_site']);

$attributeName = $_GET['split']; // different split in $userOptions from summary page!

if (! $attributeType = $attributeTypes[$attributeName])
{
	$attributeName = key($attributeTypes);
	$attributeType = current($attributeTypes);
}

$attributeGroupTypes = AttributeGroupManager::getTypes();

// FILTER

$filter = array(
	'from'  => $from->toString(),
	'to'    => $to->toString(),
	'site'  => $site,
);

CUserOptions::SetOption('conversion', 'filter', array_merge($userOptions, $filter));

$filter['rate' ] = $rateName;
$filter['split'] = $attributeName;
$filter['lang' ] = LANGUAGE_ID;

// CONTEXT

$context = new ReportContext();

$context->setAttribute('conversion_site', $site);

$filterInfo = array();

foreach ($attributeTypes as $name => $type)
{
	if (($value = $_GET[$name]) !== null && $name != $attributeName)
	{
		$filter[$name] = $value;

		$context->setAttribute($name, $value);

		if ($value)
		{
			$filterInfo[$type['NAME'] ?: $name] = ($gv = $type['GET_VALUES']) && ($vs = $gv(array($value))) && isset($vs[$value]['NAME']) ? $vs[$value]['NAME'] : htmlspecialcharsbx($value);
		}
		elseif ($g = $type['GROUP'])
		{
			$filterInfo[isset($attributeGroupTypes[$g]['NAME']) ? $attributeGroupTypes[$g]['NAME'] : $g] = $type['NAME'] ?: $name;
		}
		else
		{
			$filterInfo[$type['NAME'] ?: $name] = htmlspecialcharsbx($value);
		}
	}
}

//echo '<pre>'.print_r($context->getCounters(array(
//		'filter' => array(
//			'>=DAY' => $filter['from'],
//			'<=DAY' => $filter['to'],
//		),
//		'split' => array(
//			'ATTRIBUTE_NAME' => $attributeName,
//		),
//
//	)), true)
//	.'</pre>';
//die;

//echo '<pre>'.print_r($context->getRates(array($rateName => $rateType), array(
//		'filter' => array(
//			'>=DAY' => $filter['from'],
//			'<=DAY' => $filter['to'],
//		),
//		'split' => array(
//			'ATTRIBUTE_NAME' => $attributeName,
//		),
//
//	)), true)
//	.'</pre>';
//die;



$splitRates = $context->getRates(array($rateName => $rateType), array(
	'filter' => array(
		'>=DAY' => $filter['from'],
		'<=DAY' => $filter['to'],
	),
	'split' => array(
		'ATTRIBUTE_NAME' => $attributeName,
	),
));

$attributeValues = $splitRates ? $attributeType['GET_VALUES'](array_keys($splitRates)) : array();

// LIST

$adminList = new CAdminList('');

$adminList->AddHeaders(array(
	array('id' => 'TITLE'       , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_TITLE')),
	array('id' => 'CONVERSION'  , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_CONVERSION')),
	array('id' => 'SUM'         , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_SUM')),
	array('id' => 'ACHIEVEMENTS', 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_ACHIEVEMENTS')),
	array('id' => 'TRAFFIC'     , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_TRAFFIC')),

));

foreach ($splitRates as $name => $rates)
{
	if (isset($attributeValues[$name]['NAME']))
	{
		$name = $attributeValues[$name]['NAME'];
		if (is_array($name))
			$name = $name[0];
	}

	$rate = current($rates);

	$row =& $adminList->AddRow();
	$row->AddField('TITLE'       , $name);
	$row->AddField('CONVERSION'  , number_format($rate['RATE'] * 100, 2).' %');
	$row->AddField('SUM'         , isset($rate['SUM']) ? (isset($rateType['FORMAT']['SUM']) ? $rateType['FORMAT']['SUM']($rate['SUM']) : $rate['SUM']) : '');
	$row->AddField('ACHIEVEMENTS', number_format($rate['NUMERATOR']));
	$row->AddField('TRAFFIC'     , number_format($rate['DENOMINATOR']));
}
unset ($row);

$adminList->CheckListMode(); // must be called before prolog_admin_after!!!

// VIEW

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/admin/helpers/scale.php');

$APPLICATION->SetTitle(Loc::getMessage('CONVERSION_DETAILED_TITLE'));

Bitrix\Conversion\AdminHelpers\renderFilter($filter);

?>
	<div class="adm-detail-block">
		<div class="adm-detail-content-wrap">

			<div class="adm-detail-content">
				<div class="adm-detail-content-item-block">
					<?

					$menuItems = array();

					foreach ($sites as $id => $name)
					{
						$menuItems[sprintf('%s (%s)', $name, $id)] = array_merge($filter, array('site' => $id));
					}

					Bitrix\Conversion\AdminHelpers\renderSite(sprintf('%s (%s)', $siteName, $site), $menuItems);

					?>
					<div class="adm-profit-title"><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_SPLIT').': '.($attributeType['NAME'] ?: $attributeName)?></div>
					<div class="adm-profit-title"><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_RATE').': '.($rateType['NAME'] ?: $rateName)?></div>
					<?

					foreach ($filterInfo as $name => $value)
					{
						?>
						<div class="adm-profit-title"><?=$name.': '.$value?></div>
						<?
					}

					?><br><?

					$adminList->DisplayList();

					?>
				</div>
			</div>

			<div class="adm-detail-content-btns-wrap">
				<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>
			</div>
		</div>
	</div>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
