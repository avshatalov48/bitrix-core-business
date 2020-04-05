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

// init data
$hls = array();
$hlsVisual = array();
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
	$row['NAME_LANG'] = $row['NAME_LANG'] != '' ? $row['NAME_LANG'] : $row['NAME'];
	$hlsVisual[$row['ID']] = $row;
	unset($row['NAME_LANG']);
	$hls[$row['ID']] = $row;
}

// functions
function __hlExportPrepareField($value, $userField, array $params=array())
{
	if (is_array($value))
	{
		foreach ($value as &$v)
		{
			$v = __hlExportPrepareField($v, $userField, $params);
		}
		unset($v);
	}
	elseif (trim($value) != '')
	{
		// file save to local folder
		if ($userField['BASE_TYPE'] == 'file')
		{
			if ($file = \CFile::getFileArray($value))
			{
				$tmpFile = \CFile::makeFileArray($value);
				if (isset($tmpFile['tmp_name']) && $tmpFile['tmp_name'] != '')
				{
					$fileName = $file['SUBDIR'].'/'.$file['FILE_NAME'];
					$strNewFile = str_replace('//', '/', $params['path'].'/'.$fileName);
					CheckDirPath($strNewFile);
					if (@copy($tmpFile['tmp_name'], $strNewFile))
					{
						return $file['SUBDIR'].'/'.$file['FILE_NAME'];
					}
					else
					{
						return '';
					}
				}
			}
		}
		// for enums get the vals
		elseif ($userField['BASE_TYPE'] == 'enum')
		{
			if ($value == 0)
			{
				$value = '';
			}
			elseif (isset($userField['enum_values'][$value]))
			{
				$value = $userField['enum_values'][$value];
			}
		}
	}

	return $value;
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

// process
if ($request->get('start') == 'Y' && $server->getRequestMethod() == 'POST')
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');

	$error = '';
	$userFelds = array();

	// data for next hit
	$NS = array(
		'url_data_file' => $request->get('url_data_file'),
		'object' => $request->get('object'),
		'export_hl' => $request->get('export_hl'),
		'export_data' => $request->get('export_data'),
		'step' => (int)$request->get('step'),
		'last_id' => (int)$request->get('last_id'),
		'count' => (int)$request->get('count'),
		'has_files' => (int)$request->get('has_files'),
		'left_margin' => 0,
		'right_margin' => 0,
		'all' => 0,
		'percent' => 0,
		'time_limit' => 30,
		'finish' => false
	);

	// check filename
	if (substr($NS['url_data_file'], -4) != '.xml')
	{
		$error = Loc::getMessage('XML_FILENAME_IS_NOT_XML');
	}
	if (!preg_match('/^[a-zA-Z0-9_\/.]+$/', $NS['url_data_file'], $n))
	{
		$error = Loc::getMessage('XML_FILENAME_IS_NOT_CORRECT');
	}

	// first level errors
	if ($error != '')
	{
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_ERROR_EXPORT'),
			'DETAILS' => $error,
			'HTML' => true,
			'TYPE' => 'ERROR',
		));
		echo '<script>CloseWaitWindow();</script>';
		echo '<script>EndExport();</script>';
		require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin_js.php');
	}

	// create / open file
	$export = new \Bitrix\Main\XmlWriter(array(
		'file' => $NS['url_data_file'],
		'create_file' => $NS['step'] == 0,
		'charset' => SITE_CHARSET,
		'lowercase' => true,
		'tab' => $NS['step'] == 0 ? 0 : 2
	));
	$export->openFile();

	if (!$export->getErrors())
	{
		// first step - meta-data and open items-tag
		if ($NS['step'] == 0)
		{
			$export->writeBeginTag('hiblock');
			// write hlblock
			if ($NS['export_hl'] && $NS['object'])
			{
				$export->writeItem(array(
					'hiblock' => $hls[$NS['object']]
				));
				// write langs
				$export->writeBeginTag('langs');
				$res = HL\HighloadBlockLangTable::getList(array(
					'filter' => array(
						'ID' => $NS['object']
					)
				));
				while ($row = $res->fetch())
				{
					$export->writeItem(array(
						'lang' => $row
					));
				}
				$export->writeEndTag('langs');
			}
			// write fields
			if ($NS['export_hl'] && $NS['object'])
			{
				$export->writeBeginTag('fields');
				$res = \CUserTypeEntity::GetList(
					array(),
					array(
						'ENTITY_ID' => 'HLBLOCK_'.$NS['object']
					)
				);
				while ($row = $res->fetch())
				{
					$row = \CUserTypeEntity::getById($row['ID']);//for get langs
					$row['BASE_TYPE'] = '';
					if (isset($USER_FIELD_MANAGER))
					{
						$type = $USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);
						if (is_array($type) && isset($type['BASE_TYPE']))
						{
							$row['BASE_TYPE'] = $type['BASE_TYPE'];
							// get enums
							if ($type['BASE_TYPE'] == 'enum')
							{
								$i = 0;
								$row['enums'] = array();
								$enumValues = array();
								$resE = \CUserFieldEnum::GetList(
									array(),
									array(
										'USER_FIELD_ID' => $row['ID']
									)
								);
								while ($rowE = $resE->fetch())
								{
									$row['enums']['enum'.$i++] = $rowE;
									$enumValues[$rowE['ID']] = $rowE['VALUE'];
								}
							}
						}
					}
					// check some settings
					if (isset($row['SETTINGS']) && is_array($row['SETTINGS']))
					{
						if (isset($row['SETTINGS']['HLBLOCK_ID']))
						{
							$hid = $row['SETTINGS']['HLBLOCK_ID'];
							$row['SETTINGS']['HLBLOCK_TABLE'] = $hls[$hid]['TABLE_NAME'];
						}
						if (isset($row['SETTINGS']['EXTENSIONS']) && is_array($row['SETTINGS']['EXTENSIONS']) && $row['USER_TYPE_ID'] == 'file')
						{
							$row['SETTINGS']['EXTENSIONS'] = implode(', ', array_keys($row['SETTINGS']['EXTENSIONS']));
						}
					}
					$export->writeItem(array(
						'field' => $row
					));
					$row['enum_values'] = $enumValues;
					$userFelds[$row['FIELD_NAME']] = $row;
				}
				$export->writeEndTag('fields');
			}
			// begin write items
			if ($NS['export_data'])
			{
				$export->writeBeginTag('items');
			}
		}

		// if not select user fields
		if (empty($userFelds) && $NS['object'])
		{
			$res = \CUserTypeEntity::GetList(
				array(),
				array(
					'ENTITY_ID' => 'HLBLOCK_'.$NS['object']
				)
			);
			while ($row = $res->fetch())
			{
				$row['BASE_TYPE'] = '';
				if (isset($USER_FIELD_MANAGER))
				{
					$type = $USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);
					if (is_array($type) && isset($type['BASE_TYPE']))
					{
						$row['BASE_TYPE'] = $type['BASE_TYPE'];
					}
				}
				$userFelds[$row['FIELD_NAME']] = $row;
			}
		}

		// write data
		if ($NS['export_data'] && $NS['object'])
		{
			$dataExist = false;
			if ($hlblock = HL\HighloadBlockTable::getById($NS['object'])->fetch())
			{
				$startTime = time();
				$filesPath = $server->getDocumentRoot() . substr($NS['url_data_file'], 0, -4) . '_files';
				$entity = HL\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
				$res = $entity::getList(array(
					'filter' => array(
						'>ID' => $NS['last_id']
					),
					'order' => array(
						'ID' => 'ASC'
					)
				));
				while ($row = $res->fetch())
				{
					foreach ($row as $k => $v)
					{
						if ($userFelds[$k]['BASE_TYPE'] == 'file')
						{
							$NS['has_files'] = 1;
						}
						$v = __hlExportPrepareField(
								$v,
								$userFelds[$k],
								array(
									'path' => $filesPath,
								)
							);
						if (is_array($v))
						{
							$v = 'serialize#' . serialize($v);
						}
						$row[$k] = $v;
					}
					$export->writeItem(array(
						'item' => $row
					));
					$dataExist = true;
					$NS['count']++;
					$NS['last_id'] = $row['ID'];
					if (time() - $NS['time_limit'] > $startTime)
					{
						break;
					}
				}

				if (!$dataExist)
				{
					$NS['finish'] = true;
				}

				// calculate margins
				$res = $entity::getList(array(
					'select' => array(
						new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'),
					)
				));
				if ($row = $res->fetch())
				{
					$NS['all'] = $row['CNT'];
					$NS['right_margin'] = $row['CNT'] - $NS['count'];
				}
				$NS['left_margin'] = $NS['count'];
				if ($NS['all'] != 0)
				{
					$NS['percent'] = round($NS['count'] / $NS['all'] * 100, 2);
				}
				else
				{
					$NS['percent'] = 100;
				}
			}
		}
		else
		{
			$NS['percent'] = 100;
			$NS['finish'] = true;
		}

		$NS['step']++;
	}

	// show message (error or processing)
	if ($export->getErrors())
	{
		$errors = array();
		foreach ($export->getErrors() as $error)
		{
			$errors[] = Loc::getMessage($error->getCode());
		}
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_ERROR_EXPORT'),
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
			$pathInfo = pathinfo($NS['url_data_file']);
			$pathInfo['files_dir'] = $pathInfo['filename'].'_files';
			$details .= '<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FINAL', array(
				'#xml_link#' => '<a href="/bitrix/admin/fileman_admin.php?lang='.LANG.'&amp;path='.htmlspecialcharsbx(urlencode($pathInfo['dirname'])).'&amp;set_filter=Y&amp;find_name='.htmlspecialcharsbx(urlencode($pathInfo['basename'])).'" target="_blank">'.
									htmlspecialcharsbx($pathInfo['basename']).
								'</a>'
			));
			if ($NS['has_files'])
			{
				$details .= '<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FILES_FINAL', array(
					'#files_link#' => '<a href="/bitrix/admin/fileman_admin.php?lang='.LANG.'&amp;path='.htmlspecialcharsbx(urlencode($pathInfo['dirname'])).'&amp;set_filter=Y&amp;find_name='.htmlspecialcharsbx(urlencode($pathInfo['files_dir'])).'" target="_blank">'.
										htmlspecialcharsbx($pathInfo['files_dir']).
									'</a>'
				));
			}
		}
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_PROCESS_EXPORT'),
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
	if ($export->getErrors() || $NS['finish'])
	{
		// end write items
		if ($NS['export_data'])
		{
			$export->writeEndTag('items');
		}
		$export->writeEndTag('hiblock');
		echo '<script>EndExport();</script>';
	}
	else
	{
		echo '<script>DoNext('.\CUtil::PhpToJSObject($NS).');</script>';
	}
	$export->closeFile();

	require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin_js.php');
}

// form
$aTabs = array(
	array(
		'DIV' => 'export',
		'TAB' => Loc::getMessage('ADMIN_TOOLS_TAB_EXPORT'),
		'TITLE' => Loc::getMessage('ADMIN_TOOLS_TAB_EXPORT_TITLE')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

$APPLICATION->SetTitle(Loc::getMessage('ADMIN_TOOLS_TITLE_EXPORT'));

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
			queryString += '&export_hl=' + (BX('export_hl').checked ? 1 : 0);
			queryString += '&export_data=' + (BX('export_data').checked ? 1 : 0);
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

	function StartExport()
	{
		if (parseInt(BX('object').value) > 0)
		{
			running = BX('start_button').disabled = true;
			DoNext();
		}
		else
		{
			alert('<?= \CUtil::JSEscape(Loc::getMessage('ADMIN_TOOLS_SELECT_HL'))?>');
		}
	}

	function EndExport()
	{
		running = BX('start_button').disabled = false;
	}
</script>

<form name="form_tools" method="get" action="<?=$APPLICATION->GetCurPage()?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><?= Loc::getMessage('ADMIN_TOOLS_FIELD_EXPORT_FILE')?>:</td>
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
					'operation' => 'S',// O - open, S - save
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
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_EXPORT_HL')?>:</td>
		<td>
			<select id="object">
				<option value="0"></option>
				<?foreach ($hlsVisual as $row):?>
				<option value="<?= intval($row['ID'])?>"><?= htmlspecialcharsbx($row['NAME_LANG'])?> [<?= $row['ID']?>]</option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_EXPORT_HLS')?>:</td>
		<td>
			<input type="checkbox" id="export_hl" value="Y" checked="checked" />
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('ADMIN_TOOLS_FIELD_EXPORT_DATA')?>:</td>
		<td>
			<input type="checkbox" id="export_data" value="Y" checked="checked" />
		</td>
	</tr>
	<?$tabControl->Buttons();?>
	<input type="button" id="start_button" value="<?= Loc::getMessage('ADMIN_TOOLS_START_EXPORT')?>" OnClick="StartExport();" class="adm-btn-save" />
	<input type="button" id="stop_button" value="<?= Loc::getMessage('ADMIN_TOOLS_STOP_EXPORT')?>" OnClick="EndExport();" />
	<?$tabControl->End();?>
</form>
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');