<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

\Bitrix\Main\Loader::IncludeModule('conversion');

use Bitrix\Conversion\RateManager;
use Bitrix\Conversion\AttributeManager;
use Bitrix\Conversion\ReportContext;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

// PERIOD

$from = ($d = $_GET['from']) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('first day of last month'));
$to   = ($d = $_GET['to'  ]) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('last day of this month' ));

// RATES

if (! $rateTypes = RateManager::getTypes())
	die ('No rates!');

$rateName = $_GET['rate'];

if (! $rateType = $rateTypes[$rateName])
{
	list ($rateName, $rateType) = each($rateTypes);
}

// ATTRIBUTES

if (! $attributeTypes = AttributeManager::getTypes())
	die ('No attributes!');

$splitByAttribute = (($s = $_GET['split']) && $attributeTypes[$s]) ? $s : null;

// FILTER

$filter = array(
	'lang'  => LANGUAGE_ID,
	'from'  => $from,
	'to'    => $to,
	'rate'  => $rateName,
	'split' => & $splitByAttribute,
);

call_user_func(function () use ($from, $to, & $attributeTypes, & $filter, & $splitByAttribute)
{
	foreach ($attributeTypes as $name => & $type)
	{
		$values =& $type['VALUES'];

		if ($getValues = $type['GET_VALUES'])
		{
			$values = $getValues($from, $to);

			if (($value = $_GET[$name]) && $values[$value])
			{
				$filter[$name] = $value;
			}
		}
		else
		{
			$values = array();
		}
	}

	if (! $splitByAttribute || $filter[$splitByAttribute])
	{
		foreach ($attributeTypes as $name => $value)
		{
			if (! $filter[$name])
			{
				$splitByAttribute = $name;
			}
		}
	}
});

// SPLITS

$splits = array();

foreach ($attributeTypes[$splitByAttribute]['VALUES'] as $name => $type)
{
	$splits[$name] = array(
		'NAME'  => $splitByAttribute,
		'VALUE' => $name,
		'TITLE' => $type['NAME'],
	);
}

// CONTEXT

$context = new ReportContext();

foreach ($attributeTypes as $name => $type)
{
	if ($value = $filter[$name])
	{
		$context->setAttribute($name, $value);
	}
}

$splitRates = $context->getSplitRatesDeprecated($splits, array($rateName => $rateType), array(
	'>=DAY' => $filter['from'],
	'<=DAY' => $filter['to'],
));

unset($splitRates['total'], $splitRates['other']); // TODO loop through getRates

// LIST

$adminList = new CAdminList($sTableID, $oSort);

$adminList->AddHeaders(array(
	array('id' => 'TITLE'       , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_TITLE')),
	array('id' => 'CONVERSION'  , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_CONVERSION')),
	array('id' => 'SUM'         , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_SUM')),
	array('id' => 'ACHIEVEMENTS', 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_ACHIEVEMENTS')),
	array('id' => 'TRAFFIC'     , 'default' => true, 'content' => Loc::getMessage('CONVERSION_DETAILED_HEAD_TRAFFIC')),

));

foreach ($splitRates as $name => $rates)
{
	$split = $splits[$name];
	$rate = current($rates);

	$row =& $adminList->AddRow();
	$row->AddField('TITLE'       , $split['TITLE']);
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

// FILTER

$filterPopup = array(
	Loc::getMessage('CONVERSION_DETAILED_FILTER_PERIOD'),
	Loc::getMessage('CONVERSION_DETAILED_FILTER_RATE'  ),
	Loc::getMessage('CONVERSION_DETAILED_FILTER_SPLIT' ),
);

foreach ($attributeTypes as $name => $type)
{
	$filterPopup []= $type['NAME'];
}

$filterControl = new CAdminFilter('conversionFilterControl', $filterPopup);

?>
	<form name="conversionFilter" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
		<?$filterControl->Begin()?>
		<tr>
			<td><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_PERIOD')?>:</td>
			<td>
				<?Bitrix\Conversion\AdminHelpers\renderPeriod($filter)?>
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_RATE')?>:</td>
			<td>
				<select name="rate">
					<?

					foreach ($rateTypes as $name => $type)
					{
						?><option value="<?=$name?>"<?=$name == $filter['rate'] ? ' selected' : ''?>><?=$type['NAME']?></option><?
					}

					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_SPLIT')?>:</td>
			<td>
				<select name="split">
					<?

					foreach ($attributeTypes as $name => $type)
					{
						?><option value="<?=$name?>"<?=$name == $filter['split'] ? ' selected' : ''?>><?=$type['NAME']?></option><?
					}

					?>
				</select>
			</td>
		</tr>
		<?

		foreach ($attributeTypes as $aname => $atype)
		{
			if ($getAttributeValues = $atype['GET_VALUES'])
			{
				?>
				<tr>
					<td><?=$atype['NAME']?>:</td>
					<td>
						<select name="<?=$aname?>">
							<option><?=Loc::getMessage('CONVERSION_DETAILED_FILTER_ALL')?></option>
							<?

							foreach ($getAttributeValues() as $name => $type)
							{
								?><option value="<?=$name?>"<?=$name == $filter[$aname] ? ' selected' : ''?>><?=$type['NAME']?></option><?
							}

							?>
						</select>
					</td>
				</tr>
				<?
			}
		}

		$filterControl->Buttons(
			array(
				'url' => $APPLICATION->GetCurPage(),
				'form' => 'conversionFilter'
			)
		);

		$filterControl->End();

		?>
	</form>
<?

$adminList->DisplayList();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
