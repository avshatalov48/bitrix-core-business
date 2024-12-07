<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Input;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsRelationTable;

$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
if ($saleModulePermissions < 'W')
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
Loader::includeModule('sale');

ClearVars();
ClearVars('f_');
ClearVars('l_');

Loc::loadMessages(__FILE__);

$request = Main\Context::getCurrent()->getRequest();

$propertyId = (int)$request->get('ID');
$personTypeId = (int)$request->get('PERSON_TYPE_ID');

// load person types
$personTypes = array();

$result = CSalePersonType::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array());
while ($row = $result->Fetch())
	$personTypes[$row['ID']] = array(
		'ID'   => $row['ID'],
		'NAME' => htmlspecialcharsex($row['NAME']),
		'LID'  => htmlspecialcharsex(implode(", ", $row['LIDS'])),
	);

$errors = array();
$reload = 'reloadForm()';
$variants = array();

// PREPARE PROPERTY, RELATIONS /////////////////////////////////////////////////////////////////////////////////////////

// 1. load property from database if exists

$existentProperty = $propertyId ? OrderPropsTable::getById($propertyId)->fetch() : null;

// 1. get property from post
if ($_SERVER['REQUEST_METHOD'] == 'POST') // get property from post
{
	$_POST = Input\File::getPostWithFiles($_POST, $_FILES);

//	// MULTIPLE_DEBUG
//	if ($_POST['TYPE'] != 'ENUM' && $_POST['TYPE'] != 'FILE')
//		$_POST['MULTIPLE'] = 'N';

	if ($_POST['TYPE'] == $_POST['PREVIOUS-TYPE'])
	{
		if ($_POST['TYPE'] == 'ENUM')
			foreach ($_POST['VARIANTS'] as $row)
			{
				$row = array_filter($row, 'strlen');
				if (count($row) > 2)
				{
					$variants[] = $row;
				}
			}
	}
	else
	{
		$resetInputSettings = true;
	}

	$property = $_POST;
	$relations = (array)($_POST['RELATIONS'] ?? []);
}
else
{
	$relations = array();

	// 2. load property from database
	if ($property = $existentProperty)
	{
		$personTypeId = $property['PERSON_TYPE_ID'];

		$property += $property['SETTINGS'];

		// load relations
		$result = CSaleOrderProps::GetOrderPropsRelations(array('PROPERTY_ID' => $propertyId));
		while ($row = $result->Fetch())
			$relations[$row['ENTITY_TYPE']][] = $row['ENTITY_ID'];
	}
	// 3. make new property
	else
	{
		$propertyId = null;
		$property = array(
			'TYPE' => 'STRING',
			'PERSON_TYPE_ID' => $personTypeId,
		);
	}
}

// 4. check requested person type
if (! $personType = $personTypes[$personTypeId])
	LocalRedirect('sale_order_props.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_', false));

// SETTINGS ////////////////////////////////////////////////////////////////////////////////////////////////////////////

// input settings

$inputSettings = Input\Manager::getSettings($property, $reload);

if (isset($resetInputSettings))
{
	unset($property['DEFAULT_VALUE'], $property['SETTINGS']);
	$property = array_diff_key($property, $inputSettings);

	if ($propertyId && $existentProperty && $property['TYPE'] == $existentProperty['TYPE'])
	{
		$property['MULTIPLE'     ] = $existentProperty['MULTIPLE'     ];
		$property['DEFAULT_VALUE'] = $existentProperty['DEFAULT_VALUE'];
		$property += $existentProperty['SETTINGS'];
	}
}

// load property metadata
switch ($property['TYPE'])
{
	case 'ENUM':

		if (! $variants)
		{
			$result = \Bitrix\Sale\Internals\OrderPropsVariantTable::getList([
				'filter' => ['ORDER_PROPS_ID' => $propertyId],
				'order' => ['SORT' => 'ASC']
			]);
			while ($row = $result->fetch())
			{
				$variants []= $row;
			}
		}

		break;

	case 'FILE':

		$property['DEFAULT_VALUE'] = Input\File::loadInfo($property['DEFAULT_VALUE']);

		break;
}

// variant settings

$variantSettings = array(
	'VALUE'       => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_CODE' ), 'SIZE' =>  '5', 'MAXLENGTH' => 255, 'REQUIRED' => 'Y'),
	'NAME'        => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_NAME' ), 'SIZE' => '20', 'MAXLENGTH' => 255, 'REQUIRED' => 'Y'),
	'SORT'        => array('TYPE' => 'NUMBER', 'LABEL' => Loc::getMessage('SALE_VARIANTS_SORT' ), 'MIN' => 0, 'STEP' => 1, 'VALUE' => 100),
	'DESCRIPTION' => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_DESCR'), 'SIZE' => '30', 'MAXLENGTH' => 255),
	'ID'          => array('TYPE' => 'NUMBER', 'MIN' => 0, 'STEP' => 1, 'HIDDEN' => 'Y'),
	'XML_ID' => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_XML_ID')),
);

// common settings

$groupOptions = array();
$result = \CSaleOrderPropsGroup::GetList(($b="NAME"), ($o="ASC"), Array('PERSON_TYPE_ID' => $personTypeId));
while ($row = $result->Fetch())
	$groupOptions[$row['ID']] = $row['NAME'];

$commonSettings = array(
	'PERSON_TYPE_ID' => array('TYPE' => 'NUMBER', 'LABEL' => Loc::getMessage('SALE_PERS_TYPE'  ), 'MIN' => 0, 'STEP' => 1, 'HIDDEN' => 'Y', 'REQUIRED' => 'Y', 'RLABEL' => "[$personTypeId] {$personType['NAME']} ({$personType['LID']})"),
	'PROPS_GROUP_ID' => array('TYPE' => 'ENUM'  , 'LABEL' => Loc::getMessage('F_PROPS_GROUP_ID'), 'OPTIONS' => $groupOptions, 'RLABEL' => '&nbsp;&nbsp;<a href="sale_order_props_group.php?lang=' . LANGUAGE_ID . '" target="_blank">'.Loc::getMessage('SALE_PROPS_GROUP').'</a>'),
	'NAME'           => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('F_NAME'          ), 'MAXLENGTH' => 255, 'REQUIRED' => 'Y'),
	'CODE'           => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('F_CODE'          ), 'MAXLENGTH' => 50),
	'ACTIVE'         => array('TYPE' => 'Y/N'   , 'LABEL' => Loc::getMessage('F_ACTIVE'        ), 'VALUE' => 'Y'),
	'UTIL'           => array('TYPE' => 'Y/N'   , 'LABEL' => Loc::getMessage('F_UTIL'          )),
	'USER_PROPS'     => array('TYPE' => 'Y/N'   , 'LABEL' => Loc::getMessage('F_USER_PROPS'    )),
	'IS_FILTERED'    => array('TYPE' => 'Y/N'   , 'LABEL' => Loc::getMessage('F_IS_FILTERED'   ), 'DESCRIPTION' => Loc::getMessage('MULTIPLE_DESCRIPTION')),
	'SORT'           => array('TYPE' => 'NUMBER', 'LABEL' => Loc::getMessage('F_SORT'          ), 'MIN' => 0, 'STEP' => 1, 'VALUE' => 100),
	'DESCRIPTION'    => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('F_DESCRIPTION'   ), 'MULTILINE' => 'Y', 'ROWS' => 3, 'COLS' => 40),
	'XML_ID' => array('TYPE' => 'STRING', 'LABEL' => Loc::getMessage('F_XML_ID'), 'VALUE' => OrderPropsTable::generateXmlId()),
);
if ($propertyId > 0)
{
	$commonSettings = array_merge(
		array(
			'ID' => array(
				'TYPE' => 'NUMBER',
				'LABEL' => 'ID',
				'MIN' => 0,
				'STEP' => 1,
				'HIDDEN' => 'Y',
				'RLABEL' => &$propertyId
			)
		),
		$commonSettings
	);
}
$commonSettings += Input\Manager::getCommonSettings($property, $reload);
$commonSettings['MULTIPLE']['DESCRIPTION'] = Loc::getMessage('MULTIPLE_DESCRIPTION');
unset($commonSettings['VALUE']);

if (isset($commonSettings['TYPE']['OPTIONS']['ADDRESS'])
	&& (
		!$existentProperty
		|| $existentProperty['TYPE'] !== 'ADDRESS'
	)
)
{
	unset($commonSettings['TYPE']['OPTIONS']['ADDRESS']);
}

$commonSettings['DEFAULT_VALUE'] = array(
		'REQUIRED' => 'N',
		'DESCRIPTION' => null,
		'VALUE' => $property['DEFAULT_VALUE'],
		'LABEL' => Loc::getMessage('F_DEFAULT_VALUE'),
	) + $property;

if ($property['TYPE'] == 'ENUM')
{
	$defaultOptions = $property['MULTIPLE'] == 'Y'
		? array()
		: array('' => Loc::getMessage('NO_DEFAULT_VALUE'));

	foreach ($variants as $row)
		$defaultOptions[$row['VALUE']] = $row['NAME'];

	$commonSettings['DEFAULT_VALUE']['OPTIONS'] = &$defaultOptions;
}
elseif ($property['TYPE'] == 'LOCATION')
{
	if ($property['IS_LOCATION'] == "Y" || $property['IS_LOCATION4TAX'] == "Y")
	{
		unset($commonSettings['MULTIPLE']);
	}
} elseif ($property['TYPE'] === 'ADDRESS')
{
	unset($commonSettings['DEFAULT_VALUE']);
}
// string settings

$stringSettings = array(
	'IS_PROFILE_NAME' => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_PROFILE_NAME'), 'DESCRIPTION' => Loc::getMessage('F_IS_PROFILE_NAME_DESCR')),
	'IS_PAYER'        => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_PAYER'       ), 'DESCRIPTION' => Loc::getMessage('F_IS_PAYER_DESCR'       )),
	'IS_EMAIL'        => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_EMAIL'       ), 'DESCRIPTION' => Loc::getMessage('F_IS_EMAIL_DESCR'       )),
	'IS_PHONE'        => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_PHONE'       )),
	'IS_ZIP'          => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_ZIP'         ), 'DESCRIPTION' => Loc::getMessage('F_IS_ZIP_DESCR'         )),
	'IS_ADDRESS'      => array('TYPE' => 'Y/N', 'LABEL' => Loc::getMessage('F_IS_ADDRESS'     )),
);

// location settings

$locationOptions = array('' => Loc::getMessage('NULL_ANOTHER_LOCATION'));
$result = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $personTypeId, 'TYPE' => 'STRING', 'ACTIVE' => 'Y'), false, false, array('ID', 'NAME'));
while ($row = $result->Fetch())
	$locationOptions[$row['ID']] = $row['NAME'];

$locationSettings = array(
	'IS_LOCATION'          => array('TYPE' => 'Y/N' , 'LABEL' => Loc::getMessage('F_IS_LOCATION'     ), 'DESCRIPTION' => Loc::getMessage('F_IS_LOCATION_DESCR'    ), 'ONCLICK' => $reload),
	'INPUT_FIELD_LOCATION' => array('TYPE' => 'ENUM', 'LABEL' => Loc::getMessage('F_ANOTHER_LOCATION'), 'DESCRIPTION' => Loc::getMessage('F_INPUT_FIELD_DESCR'    ), 'OPTIONS' => $locationOptions, 'VALUE' => 0),
	'IS_LOCATION4TAX'      => array('TYPE' => 'Y/N' , 'LABEL' => Loc::getMessage('F_IS_LOCATION4TAX' ), 'DESCRIPTION' => Loc::getMessage('F_IS_LOCATION4TAX_DESCR'), 'ONCLICK' => $reload),
);

// prepare property settings for view

$propertySettings = $commonSettings + $inputSettings;

//// MULTIPLE_DEBUG
//if ($property['TYPE'] != 'ENUM' && $property['TYPE'] != 'FILE')
//	unset($propertySettings['MULTIPLE']);
//elseif ($property['MULTIPLE'] == 'Y')
//	unset($propertySettings['IS_FILTERED']);

/*
 * We store the property of type DATE as a string, so we can't filter properly by it.
 */
if ($property['MULTIPLE'] === 'Y' || $property['TYPE'] === 'DATE')
{
	$propertySettings['IS_FILTERED']['DISABLED'] = 'Y';
	unset($property['IS_FILTERED']);
}

if ($property['TYPE'] == 'STRING')
{
	$propertySettings += $stringSettings;
}
elseif ($property['TYPE'] == 'LOCATION')
{
	$propertySettings += $locationSettings;
	if ($property['IS_LOCATION'] != 'Y' || $property['MULTIPLE'] == 'Y') // TODO
		unset($propertySettings['INPUT_FIELD_LOCATION']);
}

// RELATION SETTINGS ///////////////////////////////////////////////////////////////////////////////////////////////////

// payment system options

$paymentOptions = array();
$result = CSalePaySystem::GetList(
	array("SORT"=>"ASC", "NAME"=>"ASC"),
	array("ACTIVE" => "Y"),
	false,
	false,
	array("ID", "NAME", "ACTIVE", "SORT", "LID")
);
while ($row = $result->Fetch())
	$paymentOptions[$row['ID']] = $row['NAME'] . "[{$row['ID']}]";

// delivery system options
$deliveryOptions = array();

foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList(true) as $deliveryId => $deliveryFields)
{
	$name = $deliveryFields["NAME"]." [".$deliveryId."]";
	$sites = \Bitrix\Sale\Delivery\Restrictions\Manager::getSitesByServiceId($deliveryId);

	if(!empty($sites))
		$name .= " (".implode(", ", $sites).")";

	$deliveryOptions[$deliveryId] = $name;
}

$relationsSettings = [
	OrderPropsRelationTable::ENTITY_TYPE_PAY_SYSTEM => ['TYPE' => 'ENUM', 'LABEL' => Loc::getMessage('SALE_PROPERTY_PAYSYSTEM'), 'OPTIONS' => $paymentOptions , 'MULTIPLE' => 'Y', 'SIZE' => '5'],
	OrderPropsRelationTable::ENTITY_TYPE_DELIVERY => ['TYPE' => 'ENUM', 'LABEL' => Loc::getMessage('SALE_PROPERTY_DELIVERY' ), 'OPTIONS' => $deliveryOptions, 'MULTIPLE' => 'Y', 'SIZE' => '5'],
];

$landingOptions = [];
$dbRes = Bitrix\Sale\TradingPlatform\Manager::getList(
	[
		'select' => ['ID', 'NAME'],
		'filter' => [
			'=ACTIVE' => 'Y',
			'%CODE' => Bitrix\Sale\TradingPlatform\Landing\Landing::TRADING_PLATFORM_CODE,
		]
	]
);
foreach ($dbRes as $item)
{
	$landingOptions[$item['ID']] = "{$item['NAME']} [{$item['ID']}]";
}

if ($landingOptions)
{
	$relationsSettings[OrderPropsRelationTable::ENTITY_TYPE_LANDING] = [
		'TYPE' => 'ENUM',
		'LABEL' => Loc::getMessage('SALE_PROPERTY_TP_LANDING'),
		'OPTIONS' => $landingOptions,
		'MULTIPLE' => 'Y',
		'SIZE' => '5'
	];
}

$tradingPlatformOptions = [];
$dbRes = Bitrix\Sale\TradingPlatform\Manager::getList(
	[
		'select' => ['ID', 'NAME'],
		'filter' => [
			'=ACTIVE' => 'Y',
			'!%CODE' => Bitrix\Sale\TradingPlatform\Landing\Landing::TRADING_PLATFORM_CODE,
		]
	]
);
foreach ($dbRes as $item)
{
	$tradingPlatformOptions[$item['ID']] = "{$item['NAME']} [{$item['ID']}]";
}

if ($tradingPlatformOptions)
{
	$relationsSettings[OrderPropsRelationTable::ENTITY_TYPE_TRADING_PLATFORM] = [
		'TYPE' => 'ENUM',
		'LABEL' => Loc::getMessage('SALE_PROPERTY_TP'),
		'OPTIONS' => $tradingPlatformOptions,
		'MULTIPLE' => 'Y',
		'SIZE' => '5'
	];
}

// VALIDATE AND SAVE POST //////////////////////////////////////////////////////////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST["apply"]) || isset($_POST["save"])) && bitrix_sessid())
{
	// validate property
	foreach ($propertySettings as $name => $input)
	{
		if ($error = Input\Manager::getError($input, $property[$name]))
		{
			if ($input['MULTIPLE'] && $input['MULTIPLE'] == 'Y') // for DEFAULT_VALUE
			{
				$errorString = '';
				foreach ($error as $k => $v)
					$errorString .= ' '.(++$k).': '.implode(', ', $v).';';

				$errors []= $input['LABEL'].$errorString;
			}
			else
			{
				$errors []= $input['LABEL'].': '.implode(', ', $error);
			}
		}
	}

	// validate variants
	if ($property['TYPE'] == 'ENUM')
	{
		$index = 0;
		$variantCodes = [];
		foreach ($variants as $row)
		{
			++ $index;
			if ($row['DELETE'])
			{
				unset($defaultOptions[$row['VALUE']]);
			}
			else
			{
				$hasError = false;
				if (in_array($row['VALUE'], $variantCodes, true))
				{
					$errors[] = Loc::getMessage('INPUT_ENUM_NOT_UNIQUE_CODES');
					$hasError = true;
				}
				else
				{
					$variantCodes[] = $row['VALUE'];
				}
				foreach ($variantSettings as $name => $input)
					if ($error = Input\Manager::getError($input, $row[$name]))
					{
						$errors []= Loc::getMessage('INPUT_ENUM')." $index: ".$input['LABEL'].': '.implode(', ', $error);
						$hasError = true;
					}
				if ($hasError)
					unset($defaultOptions[$row['VALUE']]);
			}
		}
	}

	// validate relations

	foreach ($relationsSettings as $name => $input)
	{
		if (!isset($relations[$name]))
		{
			$relations[$name] = array();
		}
		if (($value = $relations[$name]) && $value != array(''))
		{
			if ($error = Input\Manager::getError($input, $value))
				$errors [] = $input['LABEL'].': '.implode(', ', $error);
		}
		else
		{
			$relations[$name] = array();
		}
	}

	// insert/update database
	if (
		!$errors
		&& ($request->getPost('save') !== null || $request->getPost('apply') !== null)
		&& $saleModulePermissions == 'W'
		&& check_bitrix_sessid()
	)
	{
		// save uploaded files
		if ($property['TYPE'] == 'FILE')
		{
			$savedFiles = array();
			$files = Input\File::asMultiple($property['DEFAULT_VALUE']);

			foreach ($files as $i => $file)
			{
				if (Input\File::isDeletedSingle($file))
				{
					unset($files[$i]);
				}
				else
				{
					if (Input\File::isUploadedSingle($file)
						&& ($fileId = \CFile::SaveFile(array('MODULE_ID' => 'sale') + $file, 'sale/order/properties/default'))
						&& is_numeric($fileId))
					{
						$file = $fileId;
						$savedFiles []= $fileId;
					}

					$files[$i] = Input\File::loadInfoSingle($file);
				}
			}

			$property['DEFAULT_VALUE'] = $files;
		}

		// prepare property for database & set defaults

		$propertyForDB = array();
		foreach ($commonSettings + $inputSettings + $stringSettings + $locationSettings as $name => $input)
		{
			if (isset($property[$name]))
			{
				if (is_string($property[$name]))
				{
					$property[$name] = trim($property[$name]);
				}

				$propertyForDB[$name] = Input\Manager::getValue($input, $property[$name]);
			}
		}

		$propertyForDB['SETTINGS'] = array_intersect_key($propertyForDB, $inputSettings);
		$propertyForDB = array_diff_key($propertyForDB, $propertyForDB['SETTINGS']);

		// 1. update property
		if ($propertyId)
		{
			$update = OrderPropsTable::update($propertyId, array_diff_key($propertyForDB, array('ID'=>1)));

			if ($update->isSuccess())
			{
				$propertyCode = ($v = $property['CODE']) ? $v : false;

				$result = CSaleOrderPropsValue::GetList( // TODO modernize
					($b = 'ID'),
					($o = 'ASC'),
					array(
						'ORDER_PROPS_ID' => $propertyId,
						'!CODE' => $propertyCode,
					)
				);

				while ($row = $result->Fetch())
				{
					CSaleOrderPropsValue::Update($row['ID'], array('CODE' => $propertyCode));
				}
			}
			else
			{
				$errors []= loc::getMessage('ERROR_EDIT_PROP').': '.implode(', ', $update->getErrorMessages());
			}
		}
		// 2. insert property
		else
		{
			$propertyForDB['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;
			$insert = OrderPropsTable::add($propertyForDB);
			if ($insert->isSuccess())
				$propertyId = $property['ID'] = $insert->getId();
			else
				$errors []= loc::getMessage('ERROR_ADD_PROP').': '.implode(', ', $insert->getErrorMessages());
		}

		// cleanup files
		if ($errors)
		{
			if (isset($savedFiles))
				$filesToDelete = $savedFiles;
		}
		else
		{
			if ($existentProperty && $existentProperty['TYPE'] == 'FILE')
			{
				$filesToDelete = Input\File::asMultiple(Input\File::getValue($existentProperty, $existentProperty['DEFAULT_VALUE']));

				if (isset($files))
					$filesToDelete = array_diff($filesToDelete, Input\File::asMultiple(Input\File::getValue($property, $files)));
			}
		}
		if (isset($filesToDelete))
		{
			foreach ($filesToDelete as $fileId)
				if (is_numeric($fileId))
					\CFile::Delete($fileId);
		}



//		$filesToDelete = array();
//
//		if ($v = $property['DEFAULT_VALUE'])
//			$filesToDelete = is_array($v) ? $v : array($v);
//
//		if (! $errors)
//		{
//			$filesToDelete = ($existentProperty && $existentProperty['TYPE'] == 'FILE' && ($v = $existentProperty['DEFAULT_VALUE']))
//				? array_diff((is_array($v) ? $v : array($v)), $filesToDelete)
//				: array();
//		}



		// save associated data
		if (! $errors)
		{
			// save property variants
			if ($property['TYPE'] == 'ENUM')
			{
				$index = 0;
				foreach ($variants as $key => $row)
				{
					if ($row['DELETE'])
					{
						if ($row['ID'])
							CSaleOrderPropsVariant::Delete($row['ID']); // TODO modernize
						unset($variants[$key]);
					}
					else
					{
						++ $index;
						$variantId = $row['ID'];
						$row = array_intersect_key($row, $variantSettings);

						if ($variantId)
						{
							unset($row['ID']);
							if (! CSaleOrderPropsVariant::Update($variantId, $row))
								$errors []= Loc::getMessage('ERROR_EDIT_VARIANT')." $index";
						}
						else
						{
							$row['ORDER_PROPS_ID'] = $propertyId;
							if ($variantId = CSaleOrderPropsVariant::Add($row))
								$variants[$key]['ID'] = $variantId;
							else
								$errors []= Loc::getMessage('ERROR_ADD_VARIANT')." $index";
						}
					}
				}
			}
			// cleanup variants
			elseif ($existentProperty && $existentProperty['TYPE'] == 'ENUM')
			{
				CSaleOrderPropsVariant::DeleteAll($propertyId);
			}

			// save property relations
			foreach ($relationsSettings as $name => $input)
				CSaleOrderProps::UpdateOrderPropsRelations($propertyId, $relations[$name], $name);
		}

		if ($request->getPost('save') !== null && !$errors)
			LocalRedirect("sale_order_props.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false));

		if ($request->getPost('apply') !== null && ! $errors)
			LocalRedirect("sale_order_props_edit.php?lang=" . LANGUAGE_ID . "&ID=".$propertyId.GetFilterParams("filter_", false));
	}
}
// RENDER VIEW /////////////////////////////////////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle($propertyId
	? Loc::getMessage('SALE_EDIT_RECORD', array('#ID#' => $propertyId))
	: Loc::getMessage('SALE_NEW_RECORD'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage('SOPEN_2FLIST'),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/sale_order_props.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_")
	)
);

if ($propertyId && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$arDDMenu = array();

	$arDDMenu[] = array(
		"HTML" => "<b>".Loc::getMessage('SOPEN_4NEW_PROMT')."</b>",
		"ACTION" => false
	);

	foreach($personTypes as $row)
		$arDDMenu[] = array(
			'TEXT' => "[{$row['ID']}] {$row['NAME']} ({$row['LID']})",
			'ACTION' => "window.location = 'sale_order_props_edit.php?lang=" . LANGUAGE_ID . "&PERSON_TYPE_ID={$row['ID']}';"
		);

	$aMenu[] = array(
		"TEXT" => Loc::getMessage('SOPEN_NEW_PROPS'),
		"ICON" => "btn_new",
		"MENU" => $arDDMenu
	);

	$aMenu[] = array(
		"TEXT" => Loc::getMessage('SOPEN_DELETE_PROPS'),
		"LINK" => "javascript:if(confirm('".Loc::getMessage('SOPEN_DELETE_PROPS_CONFIRM')."')) window.location='/bitrix/admin/sale_order_props.php?action=delete&ID[]=".$propertyId."&lang=" . LANGUAGE_ID . "&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errors)
{
	$message = '';
	foreach ($errors as $v)
		$message .= $v.'<br>';
	$m = new CAdminMessage($message);
	echo $m->Show();
}

?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANG;?>&PERSON_TYPE_ID=<?=$personTypeId;?><?=GetFilterParams("filter_", false);?>" name="form1" id="form1" enctype="multipart/form-data">
	<script>function reloadForm(){document.getElementById('form1').submit();}</script>
	<?=GetFilterHiddens("filter_")?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="ID" value="<?echo $propertyId ?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="PREVIOUS-TYPE" value="<?=htmlspecialcharsbx($property['TYPE'])?>">
	<?=bitrix_sessid_post()?>

	<?
	$tabs = [
		[
			'DIV' => 'edit1',
			'TAB' => Loc::getMessage('SOPEN_TAB_PROPS'),
			'ICON' => 'sale',
			'TITLE' => str_replace(
				'#PTYPE#',
				"{$personType['NAME']} ({$personType['LID']})",
				Loc::getMessage('SOPEN_TAB_PROPS_DESCR')
			)
		],
		[
			'DIV' => 'edit2',
			'TAB' => Loc::getMessage('SALE_PROPERTY_LINKING'),
			'ICON' => 'sale',
			'TITLE' => Loc::getMessage('SALE_PROPERTY_LINKING_DESC')
		]
	];

	$tabControl = new CAdminTabControl('tabControl', $tabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>

	<tr class="heading"><td colspan="2"><?=Loc::getMessage('PROPERTY_TITLE')?></td></tr>

	<?

	foreach ($propertySettings as $name => $input):
		$input['HIDDEN'] ??= 'N';
		$input['REQUIRED'] ??= 'N';
		$input['MULTIPLE'] ??= 'N';
		$input['DESCRIPTION'] ??= '';
		$input['RLABEL'] ??= '';
		$tr = '';
		$td = '';

		if ($input['HIDDEN'] !== 'Y')
		{
			$tr = $input['REQUIRED'] === 'Y' ? ' class="adm-detail-required-field"' : '';
			$td = $input['MULTIPLE'] === 'Y' ? ' valign="top"' : '';
			switch ($input['TYPE'])
			{
				case 'FILE': $input['CLASS'] = 'adm-designed-file'; break;
				//case 'Y/N' : $input['CLASS'] = 'adm-designed-checkbox-label'; break; // TODO admin hack
			}
		}
		?>
		<?if ($name == 'TYPE'):?><tr class="heading"><td colspan="2"><?=Loc::getMessage('TYPE_TITLE')?></td></tr><?endif?>
		<tr<?=$tr?>>
			<td width="40%"<?=$td?>><?=$input['LABEL']?>:</td>
			<td width="60%"<?=$td?>><?php
				echo Input\Manager::getEditHtml($name, $input, $property[$name] ?? '').$input['RLABEL'];
				if ($input['DESCRIPTION']):?>
					<small><?=$input['DESCRIPTION']?></small>
				<?endif?>
			</td>
		</tr>
	<?endforeach?>

	<?if ($property['TYPE'] == 'ENUM'):?>
		<tr>
			<td colspan="2" align="center">
				<table cellspacing="0" class="internal">
					<tr class="heading">
						<td align="center"></td>
						<?foreach ($variantSettings as $input):?>
							<td align="center"><?=$input['LABEL']?></td>
						<?endforeach?>
						<td align="center"><?=Loc::getMessage('SALE_VARIANTS_DEL')?></td>
					</tr>
					<?
					for ($index = 1; $index <= 5; ++ $index)
						$variants []= array();
					$index = 0;
					foreach ($variants as $variant):?>
						<tr>
							<td><?=++$index?></td>
							<?foreach ($variantSettings as $name => $input): $input['REQUIRED'] = 'N'?>
								<?
									if ($name === 'XML_ID')
									{
										$input['VALUE'] = \Bitrix\Sale\Internals\OrderPropsVariantTable::generateXmlId();
									}
								?>
								<td><?=Input\Manager::getEditHtml("VARIANTS[$index][$name]", $input, $variant[$name])?></td>
							<?endforeach?>
							<td><input type="checkbox" name="VARIANTS[<?=$index?>][DELETE]"></td>
						</tr>
					<?endforeach?>
				</table>
			</td>
		</tr>
	<?endif?>

	<?$tabControl->BeginNextTab()?>

	<?foreach ($relationsSettings as $name => $input):
		if (empty($relations[$name]))
		{
			$value = array('-1');
		}
		else
		{
			$value = $relations[$name];
		}
		?>
		<tr>
			<?
			if ($property['TYPE'] == 'LOCATION' && $property['IS_LOCATION'] == 'Y')
				$input['DISABLED'] = true;
			?>
			<td width="40%"><?=$input['LABEL']?>:</td>
			<td width="60%"><?=Input\Manager::getEditHtml("RELATIONS[$name]", $input, $value)?></td>
		</tr>
	<?endforeach?>
	<?
	$tabControl->EndTab();
	$tabControl->Buttons(array(
		'disabled' => ($saleModulePermissions < 'W'),
		'back_url' => '/bitrix/admin/sale_order_props.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
	));
	$tabControl->End();
	?>
</form>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
