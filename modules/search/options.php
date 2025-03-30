<?php
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
IncludeModuleLangFile(__FILE__);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/prolog.php';
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/backup.php");

$openSearchAnalyzers = CSearchOpenSearch::getLanguageAnalyzers();

$bVarsFromForm = false;

$aTabs = [
	0 => [
		'DIV' => 'index',
		'TAB' => GetMessage('SEARCH_OPTIONS_TAB_INDEX'),
		'ICON' => 'search_settings',
		'TITLE' => GetMessage('SEARCH_OPTIONS_TAB_TITLE_INDEX_2'),
		'OPTIONS' => [
			'max_file_size' => [GetMessage('SEARCH_OPTIONS_REINDEX_MAX_SIZE'), ['text', 6]],
			'include_mask' => [GetMessage('SEARCH_OPTIONS_MASK_INC'), ['text', 60]],
			'exclude_mask' => [GetMessage('SEARCH_OPTIONS_MASK_EXC'), ['textarea', 5]],
			'page_tag_property' => [GetMessage('SEARCH_OPTIONS_PAGE_PROPERTY'), ['text', 'tags']],
		]
	],
	1 => [
		'DIV' => 'stemming',
		'TAB' => GetMessage('SEARCH_OPTIONS_TAB_STEMMING'),
		'ICON' => 'search_settings',
		'TITLE' => GetMessage('SEARCH_OPTIONS_TAB_TITLE_STEMMING'),
		'OPTIONS' => [
			'use_stemming' => [GetMessage('SEARCH_OPTIONS_USE_STEMMING'), ['checkbox', 'N']],
			'agent_stemming' => [GetMessage('SEARCH_OPTIONS_AGENT_STEMMING'), ['checkbox', 'N']],
			'agent_duration' => [GetMessage('SEARCH_OPTIONS_AGENT_DURATION'), ['text', 6]],
			'full_text_engine' => [GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE'), ['select', [
				'bitrix' => GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE_BITRIX'),
				'sphinx' => GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE_SPHINX'),
				'opensearch' => GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE_OPENSEARCH'),
			]]],
			'letters' => [GetMessage('SEARCH_OPTIONS_LETTERS'), ['text', 45], 'bitrix'],
			'sphinx_connection' => [GetMessage('SEARCH_OPTIONS_SPHINX_CONNECTION'), ['text', 45], 'sphinx'],
			'sphinx_index_name' => [GetMessage('SEARCH_OPTIONS_SPHINX_INDEX_NAME'), ['text', 45], 'sphinx'],
			'sphinx_note' => ['', ['note', '
<pre>
#sphinx.conf
index bitrix
{
	#main settings
		type = rt
		path = /var/lib/sphinxsearch/data/bitrix
		docinfo = inline
	#choose appropriate type of morphology to use
		#morphology = lemmatize_ru_all, lemmatize_en_all, lemmatize_de_all, stem_enru
		morphology = stem_enru, soundex
	#these settings are used by bitrix:search.title component
		dict = keywords
		prefix_fields = title
		infix_fields=
		min_prefix_len = 2
		enable_star = 1
	#all fields must be defined exactly as followed
		rt_field = title
		rt_field = body
		rt_attr_uint = module_id
		rt_attr_string = module
		rt_attr_uint = item_id
		rt_attr_string = item
		rt_attr_uint = param1_id
		rt_attr_string = param1
		rt_attr_uint = param2_id
		rt_attr_string = param2
		rt_attr_timestamp = date_change
		rt_attr_timestamp = date_to
		rt_attr_timestamp = date_from
		rt_attr_uint = custom_rank
		rt_attr_multi = tags
		rt_attr_multi = right
		rt_attr_multi = site
		rt_attr_multi = param
	#depends on settings of your site
		# uncomment for single byte character set
		charset_type = sbcs
		# uncomment for UTF character set
		#charset_type = utf-8
}
</pre>
			'
	], 'sphinx'],
			'mysql_note' => ['', ['note', GetMessage('SEARCH_OPTIONS_MYSQL_NOTE')], 'mysql'],
			'opensearch_connection' => [GetMessage('SEARCH_OPTIONS_OPENSEARCH_CONNECTION'), ['text', 45], 'opensearch'],
			'opensearch_user' => [GetMessage('SEARCH_OPTIONS_OPENSEARCH_USER'), ['text', 45], 'opensearch'],
			'opensearch_password' => [GetMessage('SEARCH_OPTIONS_OPENSEARCH_PASSWORD'), ['password', 45], 'opensearch'],
			'opensearch_index' => [GetMessage('SEARCH_OPTIONS_OPENSEARCH_INDEX'), ['text', 45], 'opensearch'],
			'opensearch_analyzer' => ['', ['note', GetMessage('SEARCH_OPTIONS_OPENSEARCH_ANALYZER_NOTE')], 'opensearch'],
		]
	],
	2 => [
		'DIV' => 'search',
		'TAB' => GetMessage('SEARCH_OPTIONS_TAB_SEARCH'),
		'ICON' => 'search_settings',
		'TITLE' => GetMessage('SEARCH_OPTIONS_TAB_TITLE_SEARCH'),
		'OPTIONS' => [
			'max_result_size' => [GetMessage('SEARCH_OPTIONS_MAX_RESULT_SIZE'), ['text', 6]],
			'max_body_size' => [GetMessage('SEARCH_OPTIONS_MAX_BODY_SIZE'), ['text', 6]],
			'use_tf_cache' => [GetMessage('SEARCH_OPTIONS_USE_TF_CACHE'), ['checkbox', 'N']],
			'use_word_distance' => [
				GetMessage('SEARCH_OPTIONS_USE_WORD_DISTANCE'),
				['checkbox', 'N'],
			],
			'use_social_rating' => [
				GetMessage('SEARCH_OPTIONS_USE_SOCIAL_RATING'),
				['checkbox', 'N'],
			],
			'suggest_save_days' => [GetMessage('SEARCH_OPTIONS_SUGGEST_SAVE_DAYS'), ['text', 6]],
		]
	],
	3 => [
		'DIV' => 'statistic',
		'TAB' => GetMessage('SEARCH_OPTIONS_TAB_STATISTIC'),
		'ICON' => 'search_settings',
		'TITLE' => GetMessage('SEARCH_OPTIONS_TAB_TITLE_STATISTIC'),
		'OPTIONS' => [
			'stat_phrase' => [GetMessage('SEARCH_OPTIONS_STAT_PHRASE'), ['checkbox', 'Y']],
			'stat_phrase_save_days' => [GetMessage('SEARCH_OPTIONS_STAT_PHRASE_SAVE_DAYS'), ['text', 6]],
		]
	],
];

$DBsearch = CDatabase::GetModuleConnection('search');
if ($DBsearch->type === 'MYSQL')
{
	$aTabs[1]['OPTIONS']['full_text_engine'][1][1]['mysql'] = GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE_MYSQL');
}
elseif ($DBsearch->type === 'PGSQL')
{
	$aTabs[1]['OPTIONS']['full_text_engine'][1][1]['pgsql'] = GetMessage('SEARCH_OPTIONS_FULL_TEXT_ENGINE_PGSQL');
}

$siteLangMap = [];
$langs = CLang::GetList();
while ($site = $langs->Fetch())
{
	$siteLangMap[$site['ID']] = $site['LANGUAGE_ID'];
	$aTabs[1]['OPTIONS']['opensearch_analyzer_' . $site['ID']] = [
		GetMessage('SEARCH_OPTIONS_OPENSEARCH_ANALYZER_FOR_SITE', ['#SITE_ID#' => $site['ID']]),
		['select', array_combine(array_keys($openSearchAnalyzers), array_keys($openSearchAnalyzers))],
		'opensearch',
	];
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $Update . $Apply . $RestoreDefaults <> '' && check_bitrix_sessid())
{
	if ($RestoreDefaults <> '')
	{
		COption::RemoveOption('search');
	}
	else
	{
		if ($_POST['full_text_engine'] === 'sphinx')
		{
			$search = new CSearchSphinx();
			if (!$search->connect($_POST['sphinx_connection'], $_POST['sphinx_index_name'], true))
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$message = new CAdminMessage(GetMessage('SEARCH_OPTIONS_SPHINX_ERROR'), $e);
				}
				$bVarsFromForm = true;
			}
		}
		elseif ($_POST['full_text_engine'] === 'opensearch')
		{
			$siteAnalyzerMap = [];
			foreach ($_POST as $key => $value)
			{
				if (str_starts_with($key, 'opensearch_analyzer_'))
				{
					$siteAnalyzerMap[explode('_', $key, 3)[2]] = $value;
				}
			}
			$search = new CSearchOpenSearch();
			$opensearch_password = $_POST['opensearch_password'] ?: CPasswordStorage::Get('search@opensearch_password');
			if (!$search->connect($_POST['opensearch_connection'], $_POST['opensearch_user'], $opensearch_password, $_POST['opensearch_index'], true, $siteAnalyzerMap))
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$message = new CAdminMessage(GetMessage('SEARCH_OPTIONS_OPENSEARCH_ERROR'), $e);
				}
				$bVarsFromForm = true;
			}
		}
		elseif ($_POST['full_text_engine'] === 'mysql')
		{
			$search = new CSearchMysql();
			if (!$search->connect())
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$message = new CAdminMessage(GetMessage('SEARCH_OPTIONS_MYSQL_ERROR'), $e);
				}
				$bVarsFromForm = true;
			}
		}
		elseif ($_POST['full_text_engine'] === 'pgsql')
		{
			$search = new CSearchPgsql();
			if (!$search->connect())
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					$message = new CAdminMessage(GetMessage('SEARCH_OPTIONS_PGSQL_ERROR'), $e);
				}
				$bVarsFromForm = true;
			}
		}

		if (!$bVarsFromForm)
		{
			$old_use_tf_cache = COption::GetOptionString('search', 'use_tf_cache');
			$old_max_result_size = COption::GetOptionInt('search', 'max_result_size');
			$old_full_text_engine = COption::GetOptionString('search', 'full_text_engine');

			foreach ($aTabs as $i => $aTab)
			{
				foreach ($aTab['OPTIONS'] as $name => $arOption)
				{
					$val = $_POST[$name];
					if ($arOption[1][0] == 'checkbox' && $val != 'Y')
					{
						$val = 'N';
					}

					if ($arOption[1][0] == 'password')
					{
						if ($val)
						{
							CPasswordStorage::Set('search@' . $name, $val);
						}
					}
					else
					{
						COption::SetOptionString('search', $name, $val, $arOption[0]);
					}
				}
			}

			if (
				$old_use_tf_cache != COption::GetOptionString('search', 'use_tf_cache')
				|| $old_max_result_size != COption::GetOptionInt('search', 'max_result_size')
			)
			{
				$DBsearch->Query('TRUNCATE TABLE b_search_content_freq');
			}

			if ($old_full_text_engine != COption::GetOptionString('search', 'full_text_engine'))
			{
				$error = [
					'MESSAGE' => GetMessage("SEARCH_OPTIONS_FULL_REINDEX", ['#LINK#' => '/bitrix/admin/search_reindex.php?lang=' . LANGUAGE_ID]),
					'TAG' => 'SEARCH_REINDEX',
					'MODULE_ID' => 'SEARCH',
					'NOTIFY_TYPE' => CAdminNotify::TYPE_ERROR,
				];
				CAdminNotify::Add($error);
			}
		}
	}

	CSearchStatistic::SetActive(COption::GetOptionString('search', 'stat_phrase') == 'Y');

	if (!$bVarsFromForm)
	{
		if ($Update <> '' && $_REQUEST['back_url_settings'] <> '')
		{
			LocalRedirect($_REQUEST['back_url_settings']);
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($mid) . '&lang=' . urlencode(LANGUAGE_ID) . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings']) . '&' . $tabControl->ActiveTabParam());
		}
	}
}

if (is_object($message))
{
	echo $message->Show();
}

$aMenu = [
	[
		'TEXT' => GetMessage('SEARCH_OPTIONS_REINDEX'),
		'LINK' => 'search_reindex.php?lang=' . LANGUAGE_ID,
		'TITLE' => GetMessage('SEARCH_OPTIONS_REINDEX_TITLE'),
	],
	[
		'TEXT' => GetMessage('SEARCH_OPTIONS_SITEMAP'),
		'LINK' => 'search_sitemap.php?lang=' . LANGUAGE_ID,
		'TITLE' => GetMessage('SEARCH_OPTIONS_SITEMAP_TITLE'),
	]
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin();
?>
<form method="post" action="<?php echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>" id="options">
<?php
foreach ($aTabs as $aTab):
	$tabControl->BeginNextTab();
	foreach ($aTab['OPTIONS'] as $name => $arOption):
		if ($bVarsFromForm)
		{
			if ($arOption[1][0] == 'password')
			{
				$val = '';
			}
			else
			{
				$val = $_POST[$name];
			}
		}
		else
		{
			$val = COption::GetOptionString('search', $name);
			if (!$val && str_starts_with($name, 'opensearch_analyzer_'))
			{
				$siteId = explode('_', $name, 3)[2];
				$val = array_search($siteLangMap[$siteId], $openSearchAnalyzers) ?: 'english';
			}
		}
		$type = $arOption[1];
	?>
		<tr <?php
			if (isset($arOption[2]))
			{
				echo 'style="display:none" class="show-for-' . htmlspecialcharsbx($arOption[2]) . '"';
			}?>>
			<td width="40%" <?php
				if ($type[0] == 'textarea')
				{
					echo 'class="adm-detail-valign-top"';
				}?>>
				<label for="<?php echo htmlspecialcharsbx($name)?>"><?php echo $arOption[0]?></label>
			<td width="60%">
				<?php if ($type[0] == 'checkbox'):?>
					<input type="checkbox" name="<?php echo htmlspecialcharsbx($name)?>" id="<?php echo htmlspecialcharsbx($name)?>" value="Y"<?php	echo ($val == 'Y') ? ' checked' : '';?>>
				<?php elseif ($type[0] == 'text'):?>
					<input type="text" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($name)?>">
				<?php elseif ($type[0] == 'password'):?>
					<input type="password" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($name)?>">
				<?php elseif ($type[0] == 'textarea'):?>
					<textarea rows="<?php echo $type[1]?>" name="<?php echo htmlspecialcharsbx($name)?>" style=
					"width:100%"><?php echo htmlspecialcharsbx($val)?></textarea>
				<?php elseif ($type[0] == 'select'):?>
					<select name="<?php echo htmlspecialcharsbx($name)?>" onchange="doShowAndHide()">
					<?php foreach ($type[1] as $key => $value):?>
						<option value="<?php echo htmlspecialcharsbx($key)?>" <?php echo ($val == $key) ? 'selected="selected"' : '';?>><?php echo htmlspecialcharsEx($value)?></option>
					<?php endforeach?>
					</select>
				<?php elseif ($type[0] == 'note'):?>
					<?php echo BeginNote(), $type[1], EndNote();?>
				<?php endif?>
			</td>
		</tr>
	<?php endforeach;
endforeach;?>

<?php $tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage('MAIN_OPT_APPLY')?>" title="<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>">
	<?php if ($_REQUEST['back_url_settings'] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage('MAIN_OPT_CANCEL')?>" title="<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings']))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>">
	<?php endif?>
	<input type="submit" name="RestoreDefaults" title="<?php echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="return confirm('<?php echo AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS')?>">
	<?=bitrix_sessid_post();?>
<?php $tabControl->End();?>
</form>
<script>
function doShowAndHide()
{
	var form = BX('options');
	var selects = document.getElementsByName('full_text_engine');
	for (var i = 0; i < selects.length; i++)
	{
		var selectedValue = selects[i].value;
		var trs = BX.findChildren(form, {tag: 'tr'}, true);
		for (var j = 0; j < trs.length; j++)
		{
			if (/show-for-/.test(trs[j].className))
			{
				if (trs[j].className.indexOf(selectedValue) >= 0)
					trs[j].style.display = 'table-row';
				else
					trs[j].style.display = 'none';
			}
		}
	}
}
BX.ready(doShowAndHide);
</script>
