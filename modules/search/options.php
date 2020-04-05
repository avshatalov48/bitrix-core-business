<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/prolog.php");
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */

$bVarsFromForm = false;
$aTabs = array(
	0 => array(
		"DIV" => "index",
		"TAB" => GetMessage("SEARCH_OPTIONS_TAB_INDEX"),
		"ICON" => "search_settings",
		"TITLE" => GetMessage("SEARCH_OPTIONS_TAB_TITLE_INDEX_2"),
		"OPTIONS" => Array(
			"max_file_size" => Array(GetMessage("SEARCH_OPTIONS_REINDEX_MAX_SIZE"), Array("text", 6)),
			"include_mask" => Array(GetMessage("SEARCH_OPTIONS_MASK_INC"), Array("text", 60)),
			"exclude_mask" => Array(GetMessage("SEARCH_OPTIONS_MASK_EXC"), Array("textarea", 5)),
			"page_tag_property" => Array(GetMessage("SEARCH_OPTIONS_PAGE_PROPERTY"), Array("text", "tags")),
		)
	),
	1 => array(
		"DIV" => "stemming",
		"TAB" => GetMessage("SEARCH_OPTIONS_TAB_STEMMING"),
		"ICON" => "search_settings",
		"TITLE" => GetMessage("SEARCH_OPTIONS_TAB_TITLE_STEMMING"),
		"OPTIONS" => Array(
			"use_stemming" => Array(GetMessage("SEARCH_OPTIONS_USE_STEMMING"), Array("checkbox", "N")),
			"agent_stemming" => Array(GetMessage("SEARCH_OPTIONS_AGENT_STEMMING"), Array("checkbox", "N")),
			"agent_duration" => Array(GetMessage("SEARCH_OPTIONS_AGENT_DURATION"), Array("text", 6)),
			"full_text_engine" => Array(GetMessage("SEARCH_OPTIONS_FULL_TEXT_ENGINE"), Array("select", array(
				"bitrix" => GetMessage("SEARCH_OPTIONS_FULL_TEXT_ENGINE_BITRIX"),
				"sphinx" => GetMessage("SEARCH_OPTIONS_FULL_TEXT_ENGINE_SPHINX"),
			))),
			"letters" => Array(GetMessage("SEARCH_OPTIONS_LETTERS"), Array("text", 45), "bitrix"),
			"sphinx_connection" => Array(GetMessage("SEARCH_OPTIONS_SPHINX_CONNECTION"), Array("text", 45), "sphinx"),
			"sphinx_index_name" => Array(GetMessage("SEARCH_OPTIONS_SPHINX_INDEX_NAME"), Array("text", 45), "sphinx"),
			"sphinx_note" => Array("", Array("note", "
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
			"), "sphinx"),
			"mysql_note" => Array("", Array("note", GetMessage("SEARCH_OPTIONS_MYSQL_NOTE")), "mysql"),
		)
	),
	2 => array(
		"DIV" => "search",
		"TAB" => GetMessage("SEARCH_OPTIONS_TAB_SEARCH"),
		"ICON" => "search_settings",
		"TITLE" => GetMessage("SEARCH_OPTIONS_TAB_TITLE_SEARCH"),
		"OPTIONS" => Array(
			"max_result_size" => Array(GetMessage("SEARCH_OPTIONS_MAX_RESULT_SIZE"), Array("text", 6)),
			"max_body_size" => Array(GetMessage("SEARCH_OPTIONS_MAX_BODY_SIZE"), Array("text", 6)),
			"use_tf_cache" => Array(GetMessage("SEARCH_OPTIONS_USE_TF_CACHE"), Array("checkbox", "N")),
			"use_word_distance" => Array(
				GetMessage("SEARCH_OPTIONS_USE_WORD_DISTANCE"),
				Array("checkbox", "N"),
				"disabled" => BX_SEARCH_VERSION > 1? "": GetMessage("SEARCH_OPTIONS_REINSTALL_MODULE"),
			),
			"use_social_rating" => Array(
				GetMessage("SEARCH_OPTIONS_USE_SOCIAL_RATING"),
				Array("checkbox", "N"),
				"disabled" => BX_SEARCH_VERSION > 1? "": GetMessage("SEARCH_OPTIONS_REINSTALL_MODULE"),
			),
			"suggest_save_days" => Array(GetMessage("SEARCH_OPTIONS_SUGGEST_SAVE_DAYS"), Array("text", 6)),
		)
	),
	3 => array(
		"DIV" => "statistic",
		"TAB" => GetMessage("SEARCH_OPTIONS_TAB_STATISTIC"),
		"ICON" => "search_settings",
		"TITLE" => GetMessage("SEARCH_OPTIONS_TAB_TITLE_STATISTIC"),
		"OPTIONS" => Array(
			"stat_phrase" => Array(GetMessage("SEARCH_OPTIONS_STAT_PHRASE"), Array("checkbox", "Y")),
			"stat_phrase_save_days" => Array(GetMessage("SEARCH_OPTIONS_STAT_PHRASE_SAVE_DAYS"), Array("text", 6)),
		)
	),
);

$DBsearch = CDatabase::GetModuleConnection('search');
if ($DBsearch->type === 'MYSQL')
{
	$aTabs[1]['OPTIONS']['full_text_engine'][1][1]['mysql'] = GetMessage("SEARCH_OPTIONS_FULL_TEXT_ENGINE_MYSQL");
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("search");
	}
	else
	{
		if ($_POST["full_text_engine"] === "sphinx")
		{
			$search = new CSearchSphinx();
			if (!$search->connect($_POST["sphinx_connection"], $_POST["sphinx_index_name"], true))
			{
				$e = $APPLICATION->GetException();
				if(is_object($e))
					$message = new CAdminMessage(GetMessage("SEARCH_OPTIONS_SPHINX_ERROR"), $e);
				$bVarsFromForm = true;
			}
		}
		elseif ($_POST["full_text_engine"] === "mysql")
		{
			$search = new CSearchMysql();
			if (!$search->connect())
			{
				$e = $APPLICATION->GetException();
				if(is_object($e))
					$message = new CAdminMessage(GetMessage("SEARCH_OPTIONS_MYSQL_ERROR"), $e);
				$bVarsFromForm = true;
			}
		}

		if (!$bVarsFromForm)
		{
			$old_use_tf_cache = COption::GetOptionString("search", "use_tf_cache");
			$old_max_result_size = COption::GetOptionInt("search", "max_result_size");
			$old_full_text_engine = COption::GetOptionString("search", "full_text_engine");

			foreach($aTabs as $i => $aTab)
			{
				foreach($aTab["OPTIONS"] as $name => $arOption)
				{
					$disabled = array_key_exists("disabled", $arOption)? $arOption["disabled"]: "";
					if($disabled)
						continue;

					$val = $_POST[$name];
					if($arOption[1][0]=="checkbox" && $val!="Y")
						$val="N";

					COption::SetOptionString("search", $name, $val, $arOption[0]);
				}
			}

			if (
				$old_use_tf_cache != COption::GetOptionString("search", "use_tf_cache")
				|| $old_max_result_size != COption::GetOptionInt("search", "max_result_size")
			)
			{
				$DBsearch->Query("TRUNCATE TABLE b_search_content_freq");
			}

			if ($old_full_text_engine != COption::GetOptionString("search", "full_text_engine"))
			{
				COption::SetOptionString("search", "full_reindex_required", "Y");
			}
		}
	}

	CSearchStatistic::SetActive(COption::GetOptionString("search", "stat_phrase")=="Y");

	if (!$bVarsFromForm)
	{
		if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
			LocalRedirect($_REQUEST["back_url_settings"]);
		else
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
	}
}

if(is_object($message))
	echo $message->Show();

$aMenu = array(
	array(
		"TEXT"=>GetMessage("SEARCH_OPTIONS_REINDEX"),
		"LINK"=>"search_reindex.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("SEARCH_OPTIONS_REINDEX_TITLE"),
	),
	array(
		"TEXT"=>GetMessage("SEARCH_OPTIONS_SITEMAP"),
		"LINK"=>"search_sitemap.php?lang=".LANGUAGE_ID,
		"TITLE"=>GetMessage("SEARCH_OPTIONS_SITEMAP_TITLE"),
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>" id="options">
<?
foreach($aTabs as $aTab):
	$tabControl->BeginNextTab();
	foreach($aTab["OPTIONS"] as $name => $arOption):
		if ($bVarsFromForm)
			$val = $_POST[$name];
		else
			$val = COption::GetOptionString("search", $name);
		$type = $arOption[1];
		$disabled = array_key_exists("disabled", $arOption)? $arOption["disabled"]: "";
		//if (isset($_REQUEST["
	?>
		<tr <?if(isset($arOption[2])) echo 'style="display:none" class="show-for-'.htmlspecialcharsbx($arOption[2]).'"'?>>
			<td width="40%" <?if($type[0]=="textarea") echo 'class="adm-detail-valign-top"'?>>
				<label for="<?echo htmlspecialcharsbx($name)?>"><?echo $arOption[0]?></label>
			<td width="60%">
				<?if($type[0]=="checkbox"):?>
					<input type="checkbox" name="<?echo htmlspecialcharsbx($name)?>" id="<?echo htmlspecialcharsbx($name)?>" value="Y"<?if($val=="Y")echo" checked";?><?if($disabled)echo' disabled="disabled"';?>><?if($disabled) echo '<br>'.$disabled;?>
				<?elseif($type[0]=="text"):?>
					<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($name)?>">
				<?elseif($type[0]=="textarea"):?>
					<textarea rows="<?echo $type[1]?>" name="<?echo htmlspecialcharsbx($name)?>" style=
					"width:100%"><?echo htmlspecialcharsbx($val)?></textarea>
				<?elseif($type[0]=="select"):?>
					<select name="<?echo htmlspecialcharsbx($name)?>" onchange="doShowAndHide()">
					<?foreach($type[1] as $key => $value):?>
						<option value="<?echo htmlspecialcharsbx($key)?>" <?if ($val == $key) echo 'selected="selected"'?>><?echo htmlspecialcharsEx($value)?></option>
					<?endforeach?>
					</select>
				<?elseif($type[0]=="note"):?>
					<?echo BeginNote(), $type[1], EndNote();?>
				<?endif?>
			</td>
		</tr>
	<?endforeach;
endforeach;?>

<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<script>
function doShowAndHide()
{
	var form = BX('options');
	var selects = BX.findChildren(form, {tag: 'select'}, true);
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