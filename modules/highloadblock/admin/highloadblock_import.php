<?php
define('ADMIN_MODULE_NAME', 'highloadblock');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

// libs
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

// check rights
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage('ADMIN_TOOLS_ACCESS_DENIED'));
}
if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(Loc::getMessage('ADMIN_TOOLS_ACCESS_DENIED'));
}

// functions
function __getEnumUserFields($ufId=false, $clear=false)
{
	static $userFieldsEnums = array();

	if ($clear && $ufId !== false &&array_key_exists($ufId, $userFieldsEnums))
	{
		unset($userFieldsEnums[$ufId]);
	}

	if ($ufId===false || !array_key_exists($ufId, $userFieldsEnums))
	{
		if ($ufId !== false)
		{
			$userFieldsEnums[$ufId] = array();
		}
		$res = \CUserFieldEnum::GetList(
			array(),
			array('USER_FIELD_ID' => $fId)
		);
		while ($row = $res->fetch())
		{
			if (!isset($userFieldsEnums[$row['USER_FIELD_ID']]))
			{
				$userFieldsEnums[$row['USER_FIELD_ID']] = array();
			}
			$userFieldsEnums[$row['USER_FIELD_ID']][$row['ID']] = $row['VALUE'];
		}
	}

	return $ufId === false ? $userFieldsEnums : $userFieldsEnums[$ufId];
}
function __hlImportPrepareField($value, &$userField, array $params=array())
{
	if (is_array($value))
	{
		foreach ($value as &$v)
		{
			$v = __hlImportPrepareField($v, $userField, $params);
		}
		unset($v);
	}
	elseif (trim($value) != '')
	{
		// file get from local folder
		if ($userField['BASE_TYPE'] == 'file')
		{
			if (file_exists($value))
			{
				$value = \CFile::MakeFileArray($value);
			}
			else
			{
				$value = \CFile::MakeFileArray($params['path'] . '/' . $value);
			}
		}
		// for enums get the vals
		elseif ($userField['BASE_TYPE'] == 'enum' && is_array($userField['ENUMS']))
		{
			$enums = array_flip($userField['ENUMS']);
			if (isset($enums[$value]))
			{
				$value = $enums[$value];
			}
			// add new enum
			else
			{
				$userFieldEnums = new \CUserFieldEnum;
				$userFieldEnums->setEnumValues($userField['ID'], array(
					'n0' => array(
						'VALUE' => $value
					)
				));
				$userField['ENUMS'] = __getEnumUserFields($userField['ID'], true);
				$enums = array_flip($userField['ENUMS']);
				if (isset($enums[$value]))
				{
					$value = $enums[$value];
				}
			}
		}
	}

	return $value;
}

// init data
$hls = array();
$hlsOriginal = array();
$hlTables = array();
$xmlFields = array();
$res = HL\HighloadBlockTable::getList(array(
		'select' => array(
			'*', 'NAME_LANG' => 'LANG.NAME'
		),
		'order' => array(
			'NAME_LANG' => 'ASC', 'NAME' => 'ASC'
		)
));
while ($row = $res->fetch())
{
	$hlsOriginal[$row['ID']] = $row;

	$row['NAME'] = $row['NAME_LANG'] != '' ? $row['NAME_LANG'] : $row['NAME'];

	// get fields for HL
	$row['FIELDS'] = array(
		'ID' => 'ID'
	);
	$resF = \CUserTypeEntity::GetList(
		array(),
		array(
			'ENTITY_ID' => 'HLBLOCK_'.$row['ID'],
			'LANG' => LANG
		)
	);
	while ($rowF = $resF->fetch())
	{
		if (isset($USER_FIELD_MANAGER))
		{
			$type = $USER_FIELD_MANAGER->GetUserType($rowF['USER_TYPE_ID']);
			if (is_array($type) && isset($type['BASE_TYPE']))
			{
				if (in_array($type['BASE_TYPE'], array('string', 'int')))
				{
					$row['FIELDS'][$rowF['FIELD_NAME']] = $rowF['EDIT_FORM_LABEL'] != ''
															? $rowF['EDIT_FORM_LABEL']
															: $rowF['FIELD_NAME'];
				}
			}
		}
	}

	$xmlFields[$row['ID']] = $row['FIELDS'];
	$hls[$row['ID']] = $row;
	$hlTables[$row['HLBLOCK_TABLE']] = $row['ID'];
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

function __prepareArrayFromXml(array $item, $code = false)
{
	$fields = array();
	if ($code !== false)
	{
		if (isset($item[$code]) && is_array($item[$code]))
		{
			$item = $item[$code];
		}
		else
		{
			$item = array();
		}
	}
	if (isset($item['#']) && is_array($item['#']))
	{
		foreach ($item['#'] as $key => $value)
		{
			if (is_array($value))
			{
				$value = array_shift($value);
			}
			if (is_array($value['#']))
			{
				$fields[mb_strtoupper($key)] = __prepareArrayFromXml($value);
			}
			else
			{
				$fields[mb_strtoupper($key)] = $value['#'];
			}
		}
	}

	return $fields;
}

// process
if (
	$request->get('start') == 'Y' &&
	$server->getRequestMethod() == 'POST' &&
	check_bitrix_sessid()
)
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');

	$userFelds = array();

	// data for next hit
	$NS = array(
		'url_data_file' => $request->get('url_data_file'),
		'object' => $request->get('object'),
		'xml_id' => $request->get('xml_id'),
		'import_hl' => !$request->get('object') || $request->get('import_hl'),
		'import_data' => $request->get('import_data'),
		'save_reference' => $request->get('save_reference'),
		'step' => (int)$request->get('step'),
		'last_id' => (int)$request->get('last_id'),
		'count' => (int)$request->get('count'),
		'has_files' => (int)$request->get('has_files'),
		'xml_pos' => explode('|', $request->get('xml_pos')),
		'left_margin' => 0,
		'right_margin' => 0,
		'all' => 0,
		'percent' => 0,
		'time_limit' => 30,
		'finish' => false,
	);

	// init
	$errors = array();
	$langs = array();
	$userFelds = array();
	$userFieldsEnums = __getEnumUserFields();
	$dataExist = false;
	$startTime = time();
	$import = new CXMLFileStream;
	$filesPath = $server->getDocumentRoot().mb_substr($NS['url_data_file'], 0, -4) . '_files';

	// get langs
	$langs = array();
	$res = \CLanguage::GetList();
	while($row = $res->getNext())
	{
		$langs[$row['LID']] = $row;
	}

	// get user fields
	if ($NS['object'] > 0)
	{
		$res = \CUserTypeEntity::GetList(
			array(),
			array(
				'ENTITY_ID' => 'HLBLOCK_'.$NS['object']
			)
		);
		while ($row = $res->fetch())
		{
			$userFelds[$row['FIELD_NAME']] = $row;
		}
	}

	// import hiblock
	$import->registerNodeHandler(
		'/hiblock/hiblock',
		function (CDataXML $xmlObject) use (&$NS, &$hls, &$errors)
		{
			if ($NS['import_hl'] && !$NS['object'] && empty($errors))
			{
				$hiblock = __prepareArrayFromXml($xmlObject->GetArray(), 'hiblock');
				if (!empty($hiblock))
				{
					if (isset($hiblock['ID']))
					{
						unset($hiblock['ID']);
					}
					$result = HL\HighloadBlockTable::add($hiblock);
					if ($result->isSuccess())
					{
						$NS['object'] = $result->getId();
						$hls[$NS['object']] = $hiblock;
					}
					else
					{
						$errors = array_merge($errors, $result->getErrorMessages());
					}
				}
				else
				{
					$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_CREATE');
				}
			}
			elseif (!$NS['object'])
			{
				$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
			}
			elseif ($NS['object'])
			{
				if (!HL\HighloadBlockTable::getById($NS['object'])->fetch())
				{
					$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
				}
			}
		}
	);

	// import langs
	$import->registerNodeHandler(
		'/hiblock/langs/lang',
		function (CDataXML $xmlObject) use (&$NS, &$errors)
		{
			if ($NS['import_hl'] && $NS['object'] && empty($errors))
			{
				$lang = __prepareArrayFromXml($xmlObject->GetArray(), 'lang');
				if (!empty($lang))
				{
					$lang['ID'] = $NS['object'];
					// delete if exist
					$res = HL\HighloadBlockLangTable::getList(array(
						'filter' => array(
							'ID' => $lang['ID'],
							'LID' => $lang['LID']
						)
					));
					if ($row = $res->fetch())
					{
						HL\HighloadBlockLangTable::delete([
							'ID' => $row['ID'],
							'LID' => $row['LID'],
						]);
					}
					// add new
					HL\HighloadBlockLangTable::add($lang);
				}
			}
		}
	);

	// import uf
	$import->registerNodeHandler(
		'/hiblock/fields/field',
		function (CDataXML $xmlObject) use (&$NS, $hlTables, &$userFelds, &$userFieldsEnums, $langs, &$errors, $APPLICATION)
		{
			if ($NS['import_hl'] && $NS['object'] && empty($errors))
			{
				$field = __prepareArrayFromXml($xmlObject->GetArray(), 'field');
				if (!empty($field))
				{
					// add new field, if no exist
					if (!isset($userFelds[$field['FIELD_NAME']]))
					{
						if (isset($field['ID']))
						{
							unset($field['ID']);
						}
						// re-set some settings
						if (isset($field['SETTINGS']) && is_array($field['SETTINGS']))
						{
							if (isset($field['SETTINGS']['HLBLOCK_TABLE']) && $field['SETTINGS']['HLBLOCK_TABLE'] !='')
							{
								$field['SETTINGS']['HLBLOCK_ID'] = $hlTables[$field['HLBLOCK_TABLE']['HLBLOCK_TABLE']];
							}
						}
						// set language keys to lowercase
						$codes = array('EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL',
										'ERROR_MESSAGE', 'HELP_MESSAGE');
						foreach ($codes as $code)
						{
							if (isset($field[$code]) && is_array($field[$code]))
							{
								foreach ($langs as $lng => $lang)
								{
									if ($lng !== mb_strtoupper($lng) && isset($field[$code][mb_strtoupper($lng)]))
									{
										$field[$code][$lng] = $field[$code][mb_strtoupper($lng)];
										unset($field[$code][mb_strtoupper($lng)]);
									}
								}
							}
						}
						// add field
						$field['ENTITY_ID'] = 'HLBLOCK_'.$NS['object'];
						$userField  = new \CUserTypeEntity;
						$fId = $userField->add($field);
						if ($fId > 0)
						{
							$userFelds[$field['FIELD_NAME']] = $field;
							// set enumeration list
							if (
								$fId && $field['BASE_TYPE'] == 'enum' &&
								isset($field['ENUMS']) && !empty($field['ENUMS'])
							)
							{
								$enums = array();
								foreach (array_values($field['ENUMS']) as $k => $enum)
								{
									$enums['n'.$k] = array(
										'VALUE' => $enum['VALUE'],
										'DEF' => $enum['DEF'],
										'SORT' => $enum['SORT'],
										'XML_ID' => $enum['XML_ID']
									);
								}
								$userFieldEnums = new \CUserFieldEnum;
								$userFieldEnums->setEnumValues($fId, $enums);
								// add new values
								$userFieldsEnums[$fId] = __getEnumUserFields($fId, true);
							}
						}
						else
						{
							if ($e = $APPLICATION->getException())
							{
								$errors[] = $e->getString();
							}
						}
					}
				}
			}
		}
	);

	// import data
	$import->registerNodeHandler(
		'/hiblock/items/item',
		function (CDataXML $xmlObject) use (&$NS, $hls, $filesPath, $userFelds, &$errors, $USER_FIELD_MANAGER, $hlsOriginal)
		{
			static $class = null;
			static $hlLocal = null;
			static $userFeldsLocal = null;
			static $userFeldsEnumLocal = null;
			if ($NS['object'] && empty($errors))
			{
				// first refill some arrays if need
				if (!isset($hls[$NS['object']]))
				{
					if ($hlLocal === null)
					{
						$hlLocal = HL\HighloadBlockTable::getById($NS['object'])->fetch();
					}
					$hls[$NS['object']] = $hlLocal;
				}
				if (!$hls[$NS['object']])
				{
					$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
					return;
				}
				if (empty($userFelds))
				{
					if ($userFeldsLocal === null)
					{
						$userFeldsLocal = array();
						$res = \CUserTypeEntity::GetList(
							array(),
							array(
								'ENTITY_ID' => 'HLBLOCK_'.$NS['object']
							)
						);
						while ($row = $res->fetch())
						{
							$userFeldsLocal[$row['FIELD_NAME']] = $row;
						}
					}
					$userFelds = $userFeldsLocal;
				}
				if ($userFeldsEnumLocal === null)
				{
					$userFeldsEnumLocal = __getEnumUserFields(false, true);
				}
				$userFieldsEnums = $userFeldsEnumLocal;
				// then add
				$item = __prepareArrayFromXml($xmlObject->GetArray(), 'item');
				if (!empty($item))
				{
					$NS['count']++;
					if (!isset($item['ID']))
					{
						$item['ID'] = 'unknown';
					}
					if ($class === null)
					{
						if (
							$entity = HL\HighloadBlockTable::compileEntity(
								isset($hlsOriginal[$NS['object']])
									? $hlsOriginal[$NS['object']]
									: $hls[$NS['object']]
							)
						)
						{
							$class = $entity->getDataClass();
						}
					}
					if ($class)
					{
						// send event
						$event = new \Bitrix\Main\Event(ADMIN_MODULE_NAME, 'onBeforeItemImportAdd', array(
							'ITEM' => $item,
							'USER_FIELDS' => $userFelds,
							'NS' => $NS,
						));
						$event->send();
						foreach ($event->getResults() as $result)
						{
							if ($result->getResultType() != \Bitrix\Main\EventResult::ERROR)
							{
								if (($modified = $result->getModified()))
								{
									if (isset($modified['ITEM']))
									{
										$item = $modified['ITEM'];
									}
								}
								// here not used: $result->getUnset()
							}
							elseif ($result->getResultType() == \Bitrix\Main\EventResult::ERROR)
							{
								if (($eventErrors = $result->getErrors()))
								{
									foreach ($eventErrors as $error)
									{
										$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#' => $item['ID'])) . ' ' . $error->getMessage();
									}
									return;
								}
							}
						}
						// prepare array before add
						$filesExist = false;
						foreach ($item as $key => &$value)
						{
							if ($key != 'ID' && !isset($userFelds[$key]))
							{
								$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#' => $item['ID'])) . ' ' .
											Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM_UNKNOWN', array('#CODE#' => $key));
								return;
							}
							if (mb_substr($value, 0, 10) == 'serialize#')
							{
								$value = unserialize(mb_substr($value, 10), ['allowed_classes' => false]);
							}
							// get base type
							$userFelds[$key]['BASE_TYPE'] = '';
							if (isset($USER_FIELD_MANAGER))
							{
								$type = $USER_FIELD_MANAGER->GetUserType($userFelds[$key]['USER_TYPE_ID']);
								if (is_array($type) && isset($type['BASE_TYPE']))
								{
									$userFelds[$key]['BASE_TYPE'] = $type['BASE_TYPE'];
								}
							}
							// get enums
							if ($userFelds[$key]['BASE_TYPE'] == 'enum')
							{
								$userFelds[$key]['ENUMS'] = $userFieldsEnums[$userFelds[$key]['ID']];
							}
							if ($userFelds[$key]['BASE_TYPE'] == 'file')
							{
								$filesExist = true;
							}
							// prepare value
							$value = __hlImportPrepareField(
									$value,
									$userFelds[$key],
									array(
										'path' => $filesPath,
									));
							// clear refernces
							if (!$NS['save_reference'])
							{
								$codeReferences = array('employee', 'hlblock', 'crm',
														'iblock_section', 'iblock_element');
								if (in_array($userFelds[$key]['USER_TYPE_ID'], $codeReferences))
								{
									$value = '';
								}
							}
						}
						unset($value);
						// add / update item
						$exist = false;
						if ($NS['xml_id'] && isset($item[$NS['xml_id']]) && trim($item[$NS['xml_id']]) != '')
						{
							$exist = $class::getList($a=array(
								'filter' => array(
									'='.$NS['xml_id'] => trim($item[$NS['xml_id']])
								)
							))->fetch();
							if ($exist)
							{
								if (isset($item['ID']))
								{
									unset($item['ID']);
								}
								$result = $class::update($exist['ID'], $item);
							}
						}
						if (!$exist)
						{
							if (isset($item['ID']))
							{
								unset($item['ID']);
							}
							$result = $class::add($item);
						}
						if ($result->isSuccess())
						{
							// remove old files
							if ($exist && $filesExist)
							{
								foreach ($exist as $key => $value)
								{
									if ($userFelds[$key]['BASE_TYPE'] == 'file')
									{
										if (!is_array($value))
										{
											$value = array($value);
										}
										foreach ($value as $fid)
										{
											\CFile::delete($fid);
										}
									}
								}
							}
						}
						else
						{
							foreach ($result->getErrorMessages() as $message)
							{
								$errors[] = Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#' => $item['ID'])) . ' ' . $message;
							}
						}
					}
				}
			}
		}
	);

	// work
	$import->setPosition($NS['xml_pos']);
	if ($import->openFile($server->getDocumentRoot() . $NS['url_data_file']))
	{
		while ($import->findNext())
		{
			if (time() - $NS['time_limit'] > $startTime)
			{
				break;
			}
		}
		// finish or not
		if ($import->endOfFile())
		{
			$NS['percent'] = 100;
			$NS['finish'] = true;
		}
		else
		{
			// calc percent
			$NS['xml_pos'] = $import->getPosition();
			if (is_array($NS['xml_pos']) && isset($NS['xml_pos'][1]))
			{
				$curSize = $NS['xml_pos'][1];
				$allSize = filesize($server->getDocumentRoot() . $NS['url_data_file']);
				$NS['percent'] = round($curSize / $allSize * 100, 2);
			}
			$NS['xml_pos'] = implode('|', $NS['xml_pos']);
		}
	}
	else
	{
		$errors[] = Loc::getMessage('XML_FILE_NOT_ACCESSIBLE');
	}
	$NS['step']++;

	// show message (error or processing)
	if (!empty($errors))
	{
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT'),
			'DETAILS' => implode('<br/>', $errors),
			'HTML' => true,
			'TYPE' => 'ERROR',
		));
	}
	else
	{
		$details = Loc::getMessage('ADMIN_TOOLS_PROCESS_PERCENT',
										array(
											'#percent#' => $NS['percent'],
											'#count#' => $NS['count'],
											'#all#' => $NS['all'],
										));
		if ($NS['finish'])
		{
			$details .= '<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FINAL');
		}
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_PROCESS_IMPORT'),
			'DETAILS' => $details,
			'HTML' => true,
			'TYPE' => 'PROGRESS',
		));
		if ($NS['finish'])
		{
			\CAdminMessage::ShowMessage(array(
				'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_PROCESS_FINISH_DELETE'),
				'DETAILS' => '',
				'HTML' => true,
				'TYPE' => 'ERROR',
			));
		}
	}
	echo '<script>CloseWaitWindow();</script>';

	// final - errors or finished
	if (!empty($errors) || $NS['finish'])
	{
		echo '<script>EndImport();</script>';
	}
	else
	{
		echo '<script>DoNext('.\CUtil::PhpToJSObject($NS).');</script>';
	}

	require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin_js.php');
}

// form
$aTabs = array(
	array(
		'DIV' => 'import',
		'TAB' => Loc::getMessage('ADMIN_TOOLS_TAB_IMPORT'),
		'TITLE' => Loc::getMessage('ADMIN_TOOLS_TAB_IMPORT_TITLE')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

$APPLICATION->SetTitle(Loc::getMessage('ADMIN_TOOLS_TITLE_IMPORT'));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
?>
<div id="tools_result_div"></div>
<script type="text/javascript">
	var running = false;

	function DoNext(NS)
	{
		var queryString =
						'start=Y'
						+ '&lang=<?=LANGUAGE_ID?>'
						+ '&<?= bitrix_sessid_get()?>'
						;

		if (!NS)
		{
			queryString += '&url_data_file=' + jsUtils.urlencode(BX('url_data_file').value);
			queryString += '&object=' + jsUtils.urlencode(BX('object').value);
			queryString += '&xml_id=' + jsUtils.urlencode(BX('xml_id').value);
			queryString += '&import_hl=' + (BX('import_hl').checked ? 1 : 0);
			queryString += '&import_data=' + (BX('import_data').checked ? 1 : 0);
			queryString += '&save_reference=' + (BX('save_reference').checked ? 1 : 0);
		}

		if (running)
		{
			ShowWaitWindow();
			BX.ajax.post(
				'<?= \CUtil::JSEscape($APPLICATION->getCurPage())?>?'+queryString,
				NS,
				function(result) {
					BX('tools_result_div').innerHTML = result;
				}
			);
		}
	}

	function StartImport()
	{
		if (parseInt(BX('object').value) >= 0)
		{
			running = BX('start_button').disabled = true;
			DoNext();
		}
		else
		{
			alert('<?= \CUtil::JSEscape(Loc::getMessage('ADMIN_TOOLS_SELECT_HL'))?>');
		}
	}

	function EndImport()
	{
		running = BX('start_button').disabled = false;
	}

	function selectHL(hlSelect)
	{
		var fields = <?= \CUtil::PhpToJSObject($xmlFields)?>;
		var fieldsSelect = BX('xml_id');

		// disable some checkboxes
		if (parseInt(hlSelect.value) === 0)
		{
			BX('import_hl').disabled = true;
		}
		else
		{
			BX('import_hl').disabled = false;
		}

		// remove fields
		for (var i=fieldsSelect.length-1; i >= 0; i--)
		{
			fieldsSelect.remove(i);
		}
		// add new fields
		fieldsSelect.options.add(
				new Option('', '', false, false)
			);
		if (fields[hlSelect.value])
		{
			for (var j in fields[hlSelect.value])
			{
				fieldsSelect.options.add(
						new Option(fields[hlSelect.value][j], j, false, false)
					);
			}
		}
	}
</script>

<form name="form_tools" method="get" action="<?=$APPLICATION->GetCurPage()?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_FILE')?>:</td>
		<td>
			<input type="text" id="url_data_file" size="30" value="" />
			<input type="button" value="..." OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					'event' => 'BtnClick',
					'arResultDest' => array('FORM_NAME' => 'form_tools', 'FORM_ELEMENT_NAME' => 'url_data_file'),
					'arPath' => array('SITE' => SITE_ID, 'PATH' =>'/upload'),
					'select' => 'F',// F - file only, D - folder only
					'operation' => 'O',// O - open, S - save
					'showUploadTab' => true,
					'showAddToMenuTab' => false,
					'fileFilter' => 'xml',
					'allowAllFiles' => true,
					'SaveConfig' => true,
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_HL')?>:</td>
		<td>
			<select id="object" onchange="selectHL(this);">
				<option value="-1"></option>
				<option value="0"><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_HL_NEW')?></option>
				<?foreach ($hls as $row):?>
					<option value="<?= $row['ID']?>"><?= htmlspecialcharsbx($row['NAME'])?> [<?= $row['ID']?>]</option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_XML_ID')?>:<sup><span class="required">1</span></sup></td>
		<td>
			<select id="xml_id"></select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_HLS')?>:</td>
		<td>
			<input type="checkbox" id="import_hl" value="Y" checked="checked" />
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_IMPORT_DATA')?>:</td>
		<td>
			<input type="checkbox" id="import_data" value="Y" checked="checked" />
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_SAVE_REFERENCE')?><sup><span class="required">2</span></sup>:</td>
		<td>
			<input type="checkbox" id="save_reference" value="Y" checked="checked" />
		</td>
	</tr>
	<?$tabControl->Buttons();?>
	<input type="button" id="start_button" value="<?= Loc::getMessage('ADMIN_TOOLS_START_IMPORT')?>" OnClick="StartImport();" class="adm-btn-save" />
	<input type="button" id="stop_button" value="<?= Loc::getMessage('ADMIN_TOOLS_STOP_IMPORT')?>" OnClick="EndImport();" />
	<?$tabControl->End();?>
</form>
<?= BeginNote();?>
	<p><span class="required">1</span> <?= Loc::getMessage('ADMIN_TOOLS_NOTE_IMPORT_XML_ID')?></p>
	<p><span class="required">2</span> <?= Loc::getMessage('ADMIN_TOOLS_NOTE_SAVE_REFERENCE')?></p>
<?= EndNote();?>
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');