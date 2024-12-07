<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CCacheManager $CACHE_MANAGER */
/** @var CStackCacheManager $stackCacheManager */
IncludeModuleLangFile(__FILE__);

if (!Loader::includeModule('perfmon'))
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage(GetMessage('PERFMON_ROW_EDIT_MODULE_ERROR'));
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$hasTokenizer = function_exists('token_get_all');
$isAdmin = $USER->CanDoOperation('edit_php');
$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT <= 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$connectionName = $request['connection'] ?: 'default';
$connection = \Bitrix\Main\Application::getConnection($connectionName);
$sqlHelper = $connection->getSqlHelper();

function var_import_r($tokens, &$pos, &$result)
{
	static $single_quote = [
		'\\' => '\\',
		'\'' => "'",
	];
	static $double_quote = [
		'\\' => '\\',
		'b' => "\b",
		'f' => "\f",
		'n' => "\n",
		'r' => "\r",
		't' => "\t",
		'\'' => "'",
	];
	while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
	{
		$pos++;
	}

	while (isset($tokens[$pos]))
	{
		if ($tokens[$pos][0] === T_STRING)
		{
			$uc = mb_strtoupper($tokens[$pos][1]);
		}
		else
		{
			$uc = '';
		}

		if ($uc === 'NULL')
		{
			$result = null;
			$pos++;
		}
		elseif ($uc === 'TRUE')
		{
			$result = true;
			$pos++;
		}
		elseif ($uc === 'FALSE')
		{
			$result = false;
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_LNUMBER)
		{
			$result = (int)$tokens[$pos][1];
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_DNUMBER)
		{
			$result = (double)$tokens[$pos][1];
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_CONSTANT_ENCAPSED_STRING)
		{
			if ($tokens[$pos][1][0] === "'")
			{
				$s = '';
				$l = strlen($tokens[$pos][1]);
				for ($i = 1; $i < $l - 1; $i++)
				{
					if ($tokens[$pos][1][$i] === '\\' && isset($single_quote[$tokens[$pos][1][$i + 1]]))
					{
						$s .= $single_quote[$tokens[$pos][1][$i + 1]];
						$i++;
					}
					else
					{
						$s .= $tokens[$pos][1][$i];
					}
				}
			}
			else
			{
				$s = '';
				$l = strlen($tokens[$pos][1]);
				for ($i = 1; $i < $l - 1; $i++)
				{
					if ($tokens[$pos][1][$i] === '\\' && isset($double_quote[$tokens[$pos][1][$i + 1]]))
					{
						$s .= $double_quote[$tokens[$pos][1][$i + 1]];
						$i++;
					}
					else
					{
						$s .= $tokens[$pos][1][$i];
					}
				}
			}
			$result = $s;
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_ARRAY)
		{
			$pos++;
			while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
			{
				$pos++;
			}

			if ($tokens[$pos][0] !== '(')
			{
				return;
			}
			else
			{
				$pos++;
			}

			$result = [];
			while (true)
			{
				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ')')
				{
					break;
				}

				$key = null;
				var_import_r($tokens, $pos, $key);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ',')
				{
					$result[] = $key;
					$pos++;
					continue;
				}

				if ($tokens[$pos][0] === ')')
				{
					$result[] = $key;
					$pos++;
					break;
				}

				if ($tokens[$pos][0] !== T_DOUBLE_ARROW)
				{
					return;
				}
				else
				{
					$pos++;
				}

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				$value = null;
				var_import_r($tokens, $pos, $value);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ',' || $tokens[$pos][0] === ')')
				{
					$result[$key] = $value;
				}

				if ($tokens[$pos][0] === ',')
				{
					$pos++;
				}
			}
			$pos++;
		}
		elseif ($tokens[$pos][0] === '[')
		{
			$pos++;

			$result = [];
			while (true)
			{
				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ']')
				{
					break;
				}

				$key = null;
				var_import_r($tokens, $pos, $key);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ',')
				{
					$result[] = $key;
					$pos++;
					continue;
				}

				if ($tokens[$pos][0] === ']')
				{
					$result[] = $key;
					$pos++;
					break;
				}

				if ($tokens[$pos][0] !== T_DOUBLE_ARROW)
				{
					return;
				}
				else
				{
					$pos++;
				}

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				$value = null;
				var_import_r($tokens, $pos, $value);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				{
					$pos++;
				}

				if ($tokens[$pos][0] === ',' || $tokens[$pos][0] === ']')
				{
					$result[$key] = $value;
				}

				if ($tokens[$pos][0] === ',')
				{
					$pos++;
				}
			}
			$pos++;
		}
		else
		{
			return;
		}
	}
}

function var_import($str)
{
	$tokens = token_get_all('<?php ' . trim($str));
	$pos = 1;
	$result = null;
	var_import_r($tokens, $pos, $result);
	return $result;
}

if (
	$request->isPost()
	&& $request->get('action') !== null
	&& check_bitrix_sessid()
	&& $isAdmin
)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	switch ($request->get('action'))
	{
		case 'unserialize':
			echo var_export(unserialize($_POST['data'], ['allowed_classes' => false]), true);
			break;
		case 'serialize':
			echo serialize(var_import($_POST['data']));
			break;
		case 'base64decode':
			echo base64_decode($_POST['data']);
			break;
		case 'base64encode':
			echo base64_encode($_POST['data']);
			break;
		case 'jsondecode':
			try
			{
				echo var_export(Bitrix\Main\Web\Json::decode($_POST['data']), true);
			}
			catch (\Exception $exception)
			{
			}
			break;
		case 'jsonencode':
			try
			{
				echo Bitrix\Main\Web\Json::encode((var_import($_POST['data'])));
			}
			catch (\Exception $exception)
			{
			}
			break;
	}

	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php';
}

$table_name = $request['table_name'];
$obTable = new CPerfomanceTable;
$obTable->Init($table_name, $connection);
if (!$obTable->IsExists())
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage(GetMessage('PERFMON_ROW_EDIT_TABLE_ERROR', [
		'#TABLE_NAME#' => htmlspecialcharsbx($table_name),
	]));
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

$arUniqueIndexes = $obTable->GetUniqueIndexes();
$arFields = $obTable->GetTableFields(false, true);
$arFilter = [];
$strWhere = '';
$bNewRow = false;

$autoIncrement = false;
foreach ($arFields as $Field => $arField)
{
	if ($arField['increment'])
	{
		$autoIncrement = $Field;
	}
}

$arPrimary = [];
$arRowPK = is_array($request['pk']) ? $request['pk'] : [];
if ($arRowPK)
{
	foreach ($arUniqueIndexes as $arIndexColumns)
	{
		$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
		if (!$arMissed)
		{
			$arPrimary = $arIndexColumns;
			$strWhere = 'WHERE 1 = 1';
			foreach ($arRowPK as $column => $value)
			{
				$arFilter['=' . $column] = $value;
				if ($value != '')
				{
					$strWhere .= ' AND ' . $sqlHelper->quote($column) . " = '" . $sqlHelper->forSql($value) . "'";
				}
				else
				{
					$strWhere .= ' AND (' . $sqlHelper->quote($column) . " = '' OR " . $sqlHelper->quote($column) . ' IS NULL)';
				}
			}
			break;
		}
	}
}

if (!isset($request['pk']) && !empty($arUniqueIndexes))
{
	$bNewRow = true;
	if ($autoIncrement)
	{
		foreach ($arUniqueIndexes as $arIndexColumns)
		{
			$arMissed = array_diff($arIndexColumns, [$autoIncrement]);
			if (!$arMissed)
			{
				$arPrimary = $arIndexColumns;
				break;
			}
		}
	}
	else
	{
		$arPrimary = current($arUniqueIndexes);
	}
}

if (empty($arFilter) && !$bNewRow)
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage(GetMessage('PERFMON_ROW_EDIT_PK_ERROR'));
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

if ($bNewRow)
{
	$arRecord = [];
	foreach ($arFields as $Field => $arField)
	{
		$arRecord[$Field] = $arField['default'];
	}
}
else
{
	CTimeZone::Disable();
	$rsRecord = $obTable->GetList(array_keys($arFields), $arFilter, []);
	CTimeZone::Enable();
	$arRecord = $rsRecord->fetch();
}

if (!$arRecord)
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	$message = new CAdminMessage(GetMessage('PERFMON_ROW_EDIT_NOT_FOUND'));
	echo $message->Show();
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

$obSchema = new CPerfomanceSchema;
$arChildren = $obSchema->GetChildren($table_name);
$arParents = $obSchema->GetParents($table_name);
$additionalMeta = $obSchema->GetAttributes($table_name);

$aTabs = [
	[
		'DIV' => 'edit',
		'TAB' => GetMessage('PERFMON_ROW_EDIT_TAB'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('PERFMON_ROW_EDIT_TAB_TITLE', ['#TABLE_NAME#' => $table_name]),
	],
	[
		'DIV' => 'cache',
		'TAB' => GetMessage('PERFMON_ROW_CACHE_TAB'),
		'ICON' => 'main_user_edit',
		'TITLE' => GetMessage('PERFMON_ROW_CACHE_TAB_TITLE'),
	],
];
$tabControl = new CAdminTabControl('tabControl_' . mb_strtolower($table_name), $aTabs);
$bVarsFromForm = false;
$strError = '';

if (
	$request->isPost()
	&& $isAdmin
	&& check_bitrix_sessid()
)
{
	CTimeZone::Disable();
	$pk = false;

	if ($request['delete'] !== '')
	{
		if (!$bNewRow)
		{
			try
			{
				$connection->query('DELETE FROM ' . $sqlHelper->quote($table_name) . $strWhere);
				LocalRedirect('perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request['connection']) ? '&connection=' . urlencode($connectionName) : ''));
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$strError = $e->getDatabaseMessage();
			}
		}
		$bVarsFromForm = true;
	}
	elseif ($bNewRow)
	{
		$arToInsert = [];
		foreach ($arFields as $Field => $arField)
		{
			if ($Field === $autoIncrement)
			{
				continue;
			}

			if (isset($_POST[$Field . '_IS_NULL']) && $_POST[$Field . '_IS_NULL'] === 'Y')
			{
				$arToInsert[$Field] = null;
			}
			elseif (isset($_POST['mark_' . $Field . '_']) && $_POST['mark_' . $Field . '_'] === 'Y')
			{
				$arToInsert[$Field] = var_import($_POST[$Field]);
			}
			elseif (isset($_POST['mark_' . $Field . '_']) && $_POST['mark_' . $Field . '_'] === 'J')
			{
				$arToInsert[$Field] = Bitrix\Main\Web\Json::encode(var_import($_POST[$Field]));
			}
			elseif (isset($_POST[$Field]))
			{
				$arToInsert[$Field] = $_POST[$Field];
			}
			else
			{
				$arToInsert[$Field] = null;
			}

			if ($arToInsert[$Field] !== null && $arFields[$Field]['orm_type'] === 'datetime')
			{
				$arToInsert[$Field] = new \Bitrix\Main\Type\DateTime($arToInsert[$Field]);
			}

			if ($arToInsert[$Field] !== null && $arFields[$Field]['orm_type'] === 'date')
			{
				$arToInsert[$Field] = new \Bitrix\Main\Type\Date($arToInsert[$Field]);
			}
		}

		if ($autoIncrement)
		{
			try
			{
				$pk = [
					$autoIncrement => $connection->add($table_name, $arToInsert, $autoIncrement)
				];
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$strError = $e->getDatabaseMessage();
			}
		}
		else
		{
			$arInsert = $sqlHelper->prepareInsert($table_name, $arToInsert);
			try
			{
				$connection->query('INSERT INTO ' . $sqlHelper->quote($table_name) . '(' . $arInsert[0] . ') VALUES (' . $arInsert[1] . ')');
				foreach ($arPrimary as $Field)
				{
					$pk[$Field] = $arToInsert[$Field];
				}
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$strError = $e->getDatabaseMessage();
			}
		}

		if (!$pk)
		{
			$bVarsFromForm = true;
		}
	}
	else
	{
		$arToUpdate = [];
		foreach ($arFields as $Field => $arField)
		{
			if (in_array($Field, $arPrimary, true))
			{
				continue;
			}

			if (isset($_POST[$Field . '_IS_NULL']) && $_POST[$Field . '_IS_NULL'] === 'Y')
			{
				$arToUpdate[$Field] = null;
			}
			elseif (isset($_POST['mark_' . $Field . '_']) && $_POST['mark_' . $Field . '_'] === 'Y')
			{
				$arToUpdate[$Field] = serialize(var_import($_POST[$Field]));
			}
			elseif (isset($_POST['mark_' . $Field . '_']) && $_POST['mark_' . $Field . '_'] === 'J')
			{
				$arToUpdate[$Field] = Bitrix\Main\Web\Json::encode(var_import($_POST[$Field]));
			}
			elseif (isset($_POST[$Field]))
			{
				$arToUpdate[$Field] = $_POST[$Field];
			}
			else
			{
				$arToUpdate[$Field] = null;
			}

			if ($arToUpdate[$Field] !== null && $arFields[$Field]['orm_type'] === 'datetime')
			{
				$arToUpdate[$Field] = new \Bitrix\Main\Type\DateTime($arToUpdate[$Field]);
			}

			if ($arToUpdate[$Field] !== null && $arFields[$Field]['orm_type'] === 'date')
			{
				$arToUpdate[$Field] = new \Bitrix\Main\Type\Date($arToUpdate[$Field]);
			}
		}

		$arUpdate = $sqlHelper->prepareUpdate($table_name, $arToUpdate);

		if ($arUpdate[0])
		{
			try
			{
				$connection->query('UPDATE ' . $sqlHelper->quote($table_name) . ' SET ' . $arUpdate[0] . ' ' . $strWhere);
				$pk = $arRowPK;
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$bVarsFromForm = true;
				$strError = $e->getDatabaseMessage();
			}
		}
		else
		{
			$pk = $arRowPK;
		}
	}

	CTimeZone::Enable();

	if ($pk)
	{
		if ($_POST['clear_managed_cache'] === 'Y')
		{
			$CACHE_MANAGER->CleanAll();
			$stackCacheManager->CleanAll();
		}

		// clean orm cache
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cache->cleanDir('orm_' . $table_name);

		if ($_POST['apply'] != '')
		{
			$s = $tabControl->ActiveTabParam();
			if ($bNewRow)
			{
				foreach ($pk as $Field => $value)
				{
					$s .= '&' . urlencode('pk[' . $Field . ']') . '=' . urlencode($value);
				}
			}
			LocalRedirect($APPLICATION->GetCurPageParam() . '&' . $s);
		}
		else
		{
			LocalRedirect('perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : ''));
		}
	}
}

$APPLICATION->SetTitle(GetMessage('PERFMON_ROW_EDIT_TITLE', ['#TABLE_NAME#' => $table_name]));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'TEXT' => $table_name,
		'TITLE' => GetMessage('PERFMON_ROW_EDIT_MENU_LIST_TITLE'),
		'LINK' => 'perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : ''),
		'ICON' => 'btn_list',
	]
];
if (!$bNewRow)
{
	$aMenu[] = [
		'TEXT' => GetMessage('PERFMON_ROW_EDIT_MENU_DELETE'),
		'TITLE' => GetMessage('PERFMON_ROW_EDIT_MENU_DELETE_TITLE'),
		'LINK' => "javascript:jsDelete('editform', '" . GetMessage('PERFMON_ROW_EDIT_MENU_DELETE_CONF') . "')",
		'ICON' => 'btn_delete',
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($strError)
{
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('admin_lib_error'),
		'DETAILS' => $strError,
		'TYPE' => 'ERROR',
	]);
	echo $message->Show();
}

?>
	<script>
		function jsDelete(form_id, message)
		{
			var _form = document.getElementById(form_id);
			var _flag = document.getElementById('delete');
			if(_form && _flag)
			{
				if(confirm(message))
				{
					_flag.value = 'y';
					_form.submit();
				}
			}
		}
		function AdjustHeight()
		{
			var TEXTS = BX.findChildren(BX('editform'), {tag: /^(textarea)$/i}, true);
			if (TEXTS)
			{
				for (var i = 0; i < TEXTS.length; i++)
				{
					var TEXT = TEXTS[i];
					if (TEXT.scrollHeight > TEXT.clientHeight)
					{
						var dy = TEXT.offsetHeight - TEXT.clientHeight;
						var newHeight = TEXT.scrollHeight + dy;
						TEXT.style.height = newHeight + 'px';
					}
				}
			}
		}
		function editAsSerialize(button, Field, mark)
		{
			var textArea = BX(Field);
			var markHidden = BX(mark);
			if (textArea && markHidden)
			{
				var action = (button.value == 'unserialize' ? 'unserialize' : 'serialize');
				var url = 'perfmon_row_edit.php?lang=<?php echo LANGUAGE_ID?>&<?php echo bitrix_sessid_get()?>&action=' + action;
				BX.showWait();
				BX.ajax.post(
					url,
					{data: textArea.value},
					function (result)
					{
						BX.closeWait();
						if (result.length > 0)
						{
							textArea.value = result;
							if (action == 'unserialize')
							{
								markHidden.value = 'Y';
								button.value = 'serialize';
							}
							else
							{
								markHidden.value = '';
								button.value = 'unserialize';
							}
							AdjustHeight();
						}
					}
				);
			}
		}
		function editAsBase64(button, Field, mark)
		{
			var textArea = BX(Field);
			var markHidden = BX(mark);
			if (textArea && markHidden)
			{
				var action = (button.value == 'base64decode' ? 'base64decode' : 'base64encode');
				var url = 'perfmon_row_edit.php?lang=<?php echo LANGUAGE_ID?>&<?php echo bitrix_sessid_get()?>&action=' + action;
				BX.showWait();
				BX.ajax.post(
					url,
					{data: textArea.value},
					function (result)
					{
						BX.closeWait();
						if (result.length > 0)
						{
							textArea.value = result;
							if (action == 'base64decode')
							{
								markHidden.value = 'Y';
								button.value = 'base64encode';
							}
							else
							{
								markHidden.value = '';
								button.value = 'base64decode';
							}
							AdjustHeight();
						}
					}
				);
			}
		}
		function editAsJson(button, Field, mark)
		{
			var textArea = BX(Field);
			var markHidden = BX(mark);
			if (textArea && markHidden)
			{
				var action = (button.value == 'jsondecode' ? 'jsondecode' : 'jsonencode');
				var url = 'perfmon_row_edit.php?lang=<?php echo LANGUAGE_ID?>&<?php echo bitrix_sessid_get()?>&action=' + action;
				BX.showWait();
				BX.ajax.post(
					url,
					{data: textArea.value},
					function (result)
					{
						BX.closeWait();
						if (result.length > 0)
						{
							textArea.value = result;
							if (action == 'jsondecode')
							{
								markHidden.value = 'J';
								button.value = 'jsonencode';
							}
							else
							{
								markHidden.value = '';
								button.value = 'jsondecode';
							}
							AdjustHeight();
						}
					}
				);
			}
		}
		BX.ready(function ()
		{
			AdjustHeight();
		});
	</script>
	<form method="POST" action="<?php echo $APPLICATION->GetCurPageParam() ?>" enctype="multipart/form-data" name="editform" id="editform">
	<?php
	$tabControl->Begin();

	$tabControl->BeginNextTab();

	foreach ($arFields as $Field => $arField)
	{
		$selectValues = null;
		$trClass = $arField['nullable'] ? '' : 'adm-detail-required-field';
		?><tr class="<?php echo $trClass?>"><?php

		$textSize = '';
		if (isset($additionalMeta[$Field]['edit_mode']))
		{
			$editMode = $additionalMeta[$Field]['edit_mode'];
			if (isset($additionalMeta[$Field]['select_values']))
			{
				$selectValues = [
					'REFERENCE_ID' => array_keys($additionalMeta[$Field]['select_values']),
					'REFERENCE' => array_values($additionalMeta[$Field]['select_values']),
				];
			}
			if (isset($additionalMeta[$Field]['text_size']))
			{
				$textSize = $additionalMeta[$Field]['text_size'];
			}
		}
		elseif (
			in_array($Field, $arPrimary)
			&& !(
				$bNewRow
				&& !$autoIncrement
			)
		)
		{
			$editMode = 'read_only';
		}
		elseif ($arField['type'] === 'datetime')
		{
			$editMode = 'datetime';
		}
		elseif ($arField['type'] === 'date')
		{
			$editMode = 'date';
		}
		elseif (
			$arField['type'] === 'string'
			&& $arField['length'] == 1
			&& ($arField['default'] === 'Y' || $arField['default'] === 'N')
			&& ($arRecord[$Field] === 'Y' || $arRecord[$Field] === 'N')
			&& !$arField['nullable']
		)
		{
			$editMode = 'checkbox';
		}
		elseif (
			$arField['type'] === 'string'
			&& $arField['length'] > 0
			&& $arField['length'] <= 100
		)
		{
			$editMode = 'text';
			$textSize = $arField['length'];
		}
		elseif (
			$arField['type'] === 'string'
		)
		{
			$editMode = 'textarea';
		}
		elseif (
			$arField['type'] === 'int'
			|| $arField['type'] === 'double'
		)
		{
			$editMode = 'text';
			$textSize = '15';
		}
		else
		{
			$editMode = 'default';
		}

		if (
			(
				$arField['type'] === 'string'
				|| $arField['type'] === 'int'
			)
			&& array_key_exists($Field, $arParents)
			&& $connection->isTableExists($arParents[$Field]['PARENT_TABLE'])
			&& !$selectValues
		)
		{
			$rs = $connection->query(
			$sqlHelper->getTopSql('
					select distinct ' . $arParents[$Field]['PARENT_COLUMN'] . '
					from ' . $arParents[$Field]['PARENT_TABLE'] . '
					order by 1
				', 21)
			);
			$selectValues = [
				'REFERENCE' => [],
				'REFERENCE_ID' => [],
			];
			while ($ar = $rs->fetch())
			{
				$selectValues['REFERENCE'][] = $ar[$arParents[$Field]['PARENT_COLUMN']];
				$selectValues['REFERENCE_ID'][] = $ar[$arParents[$Field]['PARENT_COLUMN']];
			}
			if (count($selectValues['REFERENCE']) > 20)
			{
				$selectValues = null;
			}
			///TODO lookup window
		}

		if ($selectValues)
		{
			$editMode = 'select';
		}

		switch ($editMode)
		{
		case 'read_only':
			$value = $bVarsFromForm ? ($request[$Field] ?? $arRowPK[$Field]) : $arRecord[$Field];
			?>
			<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><?php echo htmlspecialcharsEx($value); ?></td>
		<?php
			break;
		case 'datetime':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord['FULL_' . $Field];
			?>
			<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><div class="adm-input-wrap adm-input-wrap-calendar">
				<input
					class="adm-input adm-input-calendar"
					type="text"
					id="<?php echo htmlspecialcharsbx($Field) ?>"
					name="<?php echo htmlspecialcharsbx($Field) ?>"
					size="23"
					value="<?php echo htmlspecialcharsbx($value) ?>"
					<?php echo $value === null && !$bNewRow ? 'disabled' : '' ?>
				>
				<span class="adm-calendar-icon" title="<?php echo GetMessage("admin_lib_calend_title") ?>" onclick="BX.calendar({node:this, field:'<?php echo htmlspecialcharsbx($Field) ?>', form: '', bTime: true, bHideTime: false});"></span>
				</div><?php if ($arField['nullable']): ?>
						<label><input
							type="checkbox"
							value="Y"
							name="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							<?php echo $value === null ? 'checked' : '' ?>
							id="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							onclick="document.getElementById('<?php echo htmlspecialcharsbx($Field) ?>').disabled=this.checked"
						> NULL</label>
					<?php endif ?></td>
		<?php
			break;
		case 'date':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord['SHORT_' . $Field];
			?>
			<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><div class="adm-input-wrap adm-input-wrap-calendar">
				<input
					class="adm-input adm-input-calendar"
					type="text"
					id="<?php echo htmlspecialcharsbx($Field) ?>"
					name="<?php echo htmlspecialcharsbx($Field) ?>"
					size="13"
					value="<?php echo htmlspecialcharsbx($value) ?>"
					<?php echo $value === null && !$bNewRow ? 'disabled' : '' ?>
				>
				<span class="adm-calendar-icon" title="<?php echo GetMessage("admin_lib_calend_title") ?>" onclick="BX.calendar({node:this, field:'<?php echo htmlspecialcharsbx($Field) ?>', form: '', bTime: false, bHideTime: false});"></span>
				</div><?php if ($arField['nullable']): ?>
						<label><input
							type="checkbox"
							value="Y"
							name="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							<?php echo $value === null ? 'checked' : '' ?>
							id="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							onclick="document.getElementById('<?php echo htmlspecialcharsbx($Field) ?>').disabled=this.checked"
						> NULL</label>
					<?php endif ?></td>
		<?php
			break;
		case 'select':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord[$Field];
			?>
				<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%"><?php
					echo SelectBoxFromArray(
						$Field,
						$selectValues,
						$value,
						$arField['nullable'] ? '[NULL]' : '',
						"class='typeselect'" . ($arField['nullable'] ? ' oninput="document.getElementById(\'' . htmlspecialcharsbx($Field . '_IS_NULL') . '\').checked = false;"' : '') . ($value === null && !$bNewRow? ' disabled' : '')
					);
					?><?php if ($arField['nullable']): ?>
						<label><input
							type="checkbox"
							value="Y"
							name="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							<?php echo $value === null ? 'checked' : '' ?>
							id="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
							onclick="document.getElementById('<?php echo htmlspecialcharsbx($Field) ?>').disabled=!this.checked"
						> NULL</label>
					<?php endif ?></td>
		<?php
			break;
		case 'checkbox':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord[$Field];
			?>
				<td width="40%"><label
						for="<?php echo htmlspecialcharsbx($Field) ?>"
						><?php echo htmlspecialcharsbx($Field) ?></label>:
				</td>
				<td width="60%"><input
						type="hidden"
						name="<?php echo htmlspecialcharsbx($Field) ?>"
						value="N"
						><input
						type="checkbox"
						name="<?php echo htmlspecialcharsbx($Field) ?>"
						id="<?php echo htmlspecialcharsbx($Field) ?>"
						value="Y"
						<?php echo $value === 'Y' ? 'checked="checked"' : '';?>
						<?php echo $value === null && !$bNewRow ? 'disabled' : '' ?>
						></td>
		<?php
			break;
		case 'text':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord[$Field];
			?>
				<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%"><input
						type="text"
						maxsize="<?php echo $textSize ?>"
						size="<?php echo min($textSize, 35) ?>"
						name="<?php echo htmlspecialcharsbx($Field) ?>"
						id="<?php echo htmlspecialcharsbx($Field) ?>"
						value="<?php echo htmlspecialcharsbx($value) ?>"
						<?php echo $value === null && !$bNewRow ? 'disabled' : '' ?>
						><?php if ($arField['nullable']): ?>
							<label><input
								type="checkbox"
								value="Y"
								name="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
								<?php echo $value === null ? 'checked' : '' ?>
								id="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
								onclick="document.getElementById('<?php echo htmlspecialcharsbx($Field) ?>').disabled=this.checked"
							> NULL</label>
						<?php endif ?></td>
		<?php
			break;
		case 'textarea':
			$value = $bVarsFromForm ? $request[$Field] : $arRecord[$Field];
			?>
				<td width="40%" class="adm-detail-valign-top"
					style="padding-top:14px"><?php echo htmlspecialcharsbx($Field) ?>:
				</td>
				<td width="60%"><textarea
						style="width:90%"
						rows="1"
						name="<?php echo htmlspecialcharsbx($Field) ?>"
						id="<?php echo htmlspecialcharsbx($Field) ?>"
						<?php echo $value === null && !$bNewRow ? 'disabled' : '' ?>
						><?php echo htmlspecialcharsEx($value) ?></textarea><?php if ($arField['nullable']): ?>
							<label><input
								type="checkbox"
								value="Y"
								name="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
								<?php echo $value === null ? 'checked' : '' ?>
								id="<?php echo htmlspecialcharsbx($Field . '_IS_NULL') ?>"
								onclick="document.getElementById('<?php echo htmlspecialcharsbx($Field) ?>').disabled=this.checked"
							> NULL</label>
						<?php endif ?><br>
					<input
						type="hidden"
						value=""
						name="<?php echo htmlspecialcharsbx('mark_' . $Field . '_') ?>"
						id="<?php echo htmlspecialcharsbx('mark_' . $Field . '_') ?>"
						>
					<?php if ($hasTokenizer):?>
					<input
						type="button"
						value="unserialize"
						onclick="<?php echo htmlspecialcharsbx("editAsSerialize(this, '" . CUtil::JSEscape($Field) . "', 'mark_" . CUtil::JSEscape($Field) . "_');") ?>"/>
					<input
						type="button"
						value="base64decode"
						onclick="<?php echo htmlspecialcharsbx("editAsBase64(this, '" . CUtil::JSEscape($Field) . "', 'mark_" . CUtil::JSEscape($Field) . "_');") ?>"/>
					<input
						type="button"
						value="jsondecode"
						onclick="<?php echo htmlspecialcharsbx("editAsJson(this, '" . CUtil::JSEscape($Field) . "', 'mark_" . CUtil::JSEscape($Field) . "_');") ?>"/>
					<?php endif;?>
				</td>
		<?php
			break;
		default:
			?>
				<td width="40%"><?php echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%">UNSUPPORTED DATA TYPE</td>
			<?php
			break;
		}
		?></tr><?php
	}
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><label
				for="clear_managed_cache"
				><?php echo GetMessage('PERFMON_ROW_CACHE_CLEAR') ?></label>:
		</td>
		<td width="60%"><input
				type="checkbox"
				name="clear_managed_cache"
				id="clear_managed_cache"
				value="Y"
				></td>
	</tr>
	<?php
	?>
	<?php echo bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID ?>">
	<input type="hidden" name="delete" id="delete" value="">
	<?php
	$tabControl->Buttons(
		[
			'disabled' => !$isAdmin,
			'back_url' => 'perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : ''),
		]
	);
	$tabControl->End();
	?>
	</form>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
