<?
/* This code captures parse errors*/
register_shutdown_function('error_alert');

function error_alert()
{
	$arErrorType = array(
		E_ERROR => "Fatal error",
		E_PARSE => "Parse error",
	);
	$e = error_get_last();
	if(is_null($e) === false && isset($arErrorType[$e['type']]))
	{
		ob_end_clean();
		echo "<h2>".GetMessage("php_cmd_error")."&nbsp;</h2><p>";
		echo '<b>'.$arErrorType[$e['type']].'</b>: '.htmlspecialcharsbx($e['message']).' in <b>'.htmlspecialcharsbx($e['file']).'</b> on line <b>'.htmlspecialcharsbx($e['line']).'</b>';
	}
	else
	{
		global $DB;
		if(
			isset($DB)
			&& is_object($DB)
			&& $DB->GetErrorMessage() != ''
		)
		{
			ob_end_clean();
			echo "<h2>".GetMessage("php_cmd_error")."&nbsp;</h2><p>";
			echo '<font color=#ff0000>Query Error: '.htmlspecialcharsbx($DB->GetErrorSQL()).'</font> ['.htmlspecialcharsbx($DB->GetErrorMessage()).']';
		}
	}
}

function fancy_output($content)
{
	if (isTextMode())
	{
		$flags = ENT_COMPAT;
		if (defined('ENT_SUBSTITUTE'))
			$flags |= ENT_SUBSTITUTE;
		else
			$flags |= ENT_IGNORE;

		return sprintf('<pre>%s</pre>', htmlspecialcharsbx($content, $flags));
	}

	return sprintf('<p>%s</e>', $content);
}

function isTextMode()
{
	return (isset($_POST['result_as_text']) && $_POST['result_as_text'] === 'y');
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "utilities/php_command_line.php");

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 **/

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$remove = 0;
if (isset($_REQUEST["remove"]) && preg_match('/^tab(\d+)$/', $_REQUEST["remove"], $match) && check_bitrix_sessid())
{
	$remove = $match[1];
}

if (isset($_REQUEST["query_count"]) && $_REQUEST["query_count"] > 1 && check_bitrix_sessid())
{
	$query_count = intval($_REQUEST["query_count"]);
	CUserOptions::SetOption("php_command_line", "count", $query_count);
}
$query_count = CUserOptions::GetOption("php_command_line", "count", 1);
if ($query_count <= 1)
	$remove = 0;

if (isset($_REQUEST["save"]) && check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	$i = 1;
	while (isset($_POST["query".$i]))
	{
		$saved = CUserOptions::GetOption("php_command_line", "query".$i, '');
		if ($saved !== $_POST["query".$i])
		{
			CUserOptions::SetOption("php_command_line", "query".$i, $_POST["query".$i]);
		}
		$i++;
	}
	while(CUserOptions::GetOption("php_command_line", "query".$i, '') <> '')
	{
		CUserOptions::DeleteOption("php_command_line", "query".$i);
		$i++;
	}
	echo "saved";
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	die();
}
	
	
if(
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST["ajax"] === "y"
	&& !isset($_POST["add"])
	&& !$remove
)
{
	CUtil::JSPostUnescape();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if(
		$_POST['query'] <> ''
		&& $isAdmin
		&& check_bitrix_sessid()
	)
	{
		printf('<h2>%s</h2>', getMessage('php_cmd_result'));

		if (isTextMode())
			ini_set('html_errors', 0);

		ob_start('fancy_output');
		$query = rtrim($_POST['query'], ";\x20\n").";\n";

		$stime = microtime(1);
		eval($query);
		ob_end_flush();
		printf("<hr>".GetMessage("php_cmd_exec_time")." %0.6f", microtime(1) - $stime);
	}

	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	die();
}

$APPLICATION->SetTitle(GetMessage("php_cmd_title"));

CJSCore::Init(array('ls'));

if(
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST["ajax"] === "y"
	&& (isset($_POST["add"]) || $remove)
)
{
	CUtil::JSPostUnescape();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
}
else
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
}


$aTabs = array();
for ($i = 1; $i <= $query_count - ($remove? 1: 0); $i++)
{
	$aTabs[] = array(
		"DIV" => "tab".$i,
		"TAB" => GetMessage("php_cmd_input")." (".$i.")",
		"TITLE" => GetMessage("php_cmd_php"),
	);
}
$aTabs[] = array(
	"DIV" => "tab_plus",
	"TAB" => '',
	"ONSELECT" => "AddNewTab();",
);
$editTab = new CAdminTabControl("editTab", $aTabs);
?>
<script>
var tabActionInProgress = false;
function TabAction(action, param, showWait)
{
	var firstTab = BX('tab_cont_tab1');
	if (!firstTab)
		return;

	tabActionInProgress = true;
	var data = {
		ajax: 'y'
	};
	data[action] = param;

	var lastIndex = 1;
	while (BX('tab_cont_tab' + lastIndex))
	{
		data['query' + lastIndex] = BX('query' + lastIndex).value;
		lastIndex++;
	}
	if (action == 'add')
		data['query_count'] = lastIndex;

	var selectedTab = BX('editTab_active_tab');
	if (action == 'add')
		data[selectedTab.name] = 'tab' + lastIndex;
	else
		data[selectedTab.name] = param;

	if (showWait)
	{
		ShowWaitWindow();
	}

	BX.ajax.post(
		'php_command_line.php?lang=' + phpVars.LANGUAGE_ID + '&sessid=' + phpVars.bitrix_sessid, data,
		function(result){
			if (result && BX.util.trim(result) != 'saved')
			{
				document.getElementById('whole_form').innerHTML = result;
				queries = [];
				CloseWaitWindow();
			}
			tabActionInProgress = false;
		}
	);
}

function AddNewTab()
{
	TabAction('add', 'y');
}

function RemoveTab(event)
{
	if (event)
	{
		var tab = event.target.parentNode;
		var m = tab.id.match(/^tab_cont_(.+)$/);
		if (m)
		{
			TabAction('remove', m[1]);
		}
	}
}

var oldQueries = {};
function saveQueries(firstRun)
{
	var newQueries = {};
	var lastIndex = 1;
	while (BX('query' + lastIndex))
	{
		newQueries['query' + lastIndex] = BX('query' + lastIndex).value;
		lastIndex++;
	}

	if (firstRun)
	{
		oldQueries = newQueries;
		return;
	}

	if (!tabActionInProgress && !compareMaps(oldQueries, newQueries))
	{
		oldQueries = newQueries;
		TabAction('save', 'y', false);
	}
}

var queries = [];
function adjustTabTitles()
{
	var lastIndex = 1;
	while (BX('tab_cont_tab' + lastIndex))
	{
		var query = BX('query' + lastIndex).value;
		if (query != queries[lastIndex])
		{
			var m = query.match(/^\/\/title:\s*(.+)\n/);
			if (m)
				BX('tab_cont_tab' + lastIndex).innerHTML = BX.util.htmlspecialchars(m[1]);
			
			var close = BX.findChildren(BX('tab_cont_tab' + lastIndex), {className: 'adm-detail-tab-close'}, true);
			if (!close || close.length == 0)
			{
				var button = BX.create('SPAN', {props: {className: 'adm-detail-tab-close'}});
				BX('tab_cont_tab' + lastIndex).appendChild(button);
				BX.bind(button, 'click', RemoveTab);
				//BX.bind(BX('query' + lastIndex), "keyup", saveQueries);
			}
		}
		queries[lastIndex] = query;
		lastIndex++;
	}
	var plus = BX.findChildren(BX('tab_cont_tab_plus'), {className: 'adm-detail-tab-plus'}, true);
	if(!plus || plus.length == 0)
	{
		button = BX.create('SPAN', {props: {className: 'adm-detail-tab-plus'}});
		BX('tab_cont_tab_plus').appendChild(button);
	}
}

BX.ready(
	function init()
	{
		var resultAsText = BX.localStorage.get('result_as_text');
		BX('result_as_text').checked = resultAsText != 'n';
		saveQueries(true);
		adjustTabTitles();
		setInterval(function()
		{
			saveQueries();
			adjustTabTitles();
		}, 250);
		BX.addCustomEvent('OnAfterActionSelectionChanged', function()
		{
			saveQueries();
			adjustTabTitles();
		});
	}
);

function __FPHPRenderResult(result)
{
	document.getElementById('result_div').innerHTML = result;
	CloseWaitWindow();
}

function __FPHPSubmit()
{
	if(confirm('<?=GetMessageJS("php_cmd_confirm")?>'))
	{
		var resultAsText = BX('result_as_text').checked? 'y': 'n';
		if (resultAsText != BX.localStorage.get('result_as_text'))
			BX.localStorage.set('result_as_text', resultAsText, 31104000);

		var selectedTab = BX('editTab_active_tab');
		var m = selectedTab.value.match(/^tab(\d+)$/);

		window.scrollTo(0, 500);
		ShowWaitWindow();

		var data = BX.ajax.prepareData({
			query: BX('query' + m[1]).value,
			result_as_text: resultAsText,
			ajax: 'y'
		});

		BX.ajax({
			'method': 'POST',
			'dataType': 'html',
			'url': 'php_command_line.php?lang=' + phpVars.LANGUAGE_ID + '&sessid=' + phpVars.bitrix_sessid,
			'data':  data,
			'onsuccess': function (result) {
				__FPHPRenderResult(result);
			},
			'onfailure': function (type, status, config) {
				__FPHPRenderResult(config.xhr.responseText);
			}
		});
	}
}

function __FPHPClear()
{
	var selectedTab = BX('editTab_active_tab');
	var m = selectedTab.value.match(/^tab(\d+)$/);
	var textarea = BX('query' + m[1]);
	textarea.value = '';
	textarea.focus();
}

function compareMaps(map1, map2)
{
	var testVal;
	if (map1.size !== map2.size)
	{
		return false;
	}
	for (key in map1)
	{
		if (map1.hasOwnProperty(key))
		{
			val = map1[key];
			testVal = map2[key];
			// in cases of an undefined value, make sure the key
			// actually exists on the object so there are no false positives
			if (testVal !== val || (testVal === undefined && !map2.hasOwnProperty(key)))
			{
				return false;
			}
		}
	}
	return true;
}

</script>
<div id="whole_form">
<?
if(
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST["ajax"] === "y"
	&& (isset($_POST["add"]) || $remove)
)
{
	$APPLICATION->RestartBuffer();
	?>
	<script>window.editTab = null;</script>
	<?
}
?>
<form name="form1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="POST">
<?
$editTab->Begin();
for ($i = 1; $i <= $query_count - ($remove? 1: 0); $i++)
{
	$index = $remove? ($i >= $remove? $i + 1: $i): $i;
	$query = $_REQUEST['query'.$index] ?? CUserOptions::GetOption("php_command_line", "query".$index, '');

	$editTab->BeginNextTab();
	?>
	<tr valign="top">
		<td width="100%" colspan="2">
			<textarea cols="60" name="query<?echo $i?>" id="query<?echo $i?>" rows="15" wrap="OFF" style="width:100%;"><?echo htmlspecialcharsbx($query); ?></textarea><br />
			<?
			if(COption::GetOptionString('fileman', "use_code_editor", "Y") == "Y" && CModule::IncludeModule('fileman'))
			{
				CCodeEditor::Show(array(
					'textareaId' => 'query'.$i,
					'height' => 350,
					'forceSyntax' => 'php',
				));
			}
			?>
		</td>
	</tr>
<?
}
?>
<?$editTab->Buttons();
?>
<input<?if(!$isAdmin) echo " disabled"?> type="button" accesskey="x" name="execute" value="<?echo GetMessage("php_cmd_button")?>" onclick="return __FPHPSubmit();" class="adm-btn-save">
<input type="button" value="<?echo GetMessage("php_cmd_button_clear")?>" onclick="this.form.reset(); __FPHPClear();">

<input type="checkbox" value="Y" name="result_as_text" id="result_as_text">
<label for="result_as_text"><?=GetMessage("php_cmd_text_result")?></label>
<?
$editTab->End();
?>
</form>
<?
if(
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST["ajax"] === "y"
	&& (isset($_POST["add"]) || $remove)
)
{
	if ($remove)
	{
		CUserOptions::SetOption("php_command_line", "count", $query_count - 1);
	}
	?><script>adjustTabTitles();</script><?

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
else
{
	?>
	</div>
	<div id="result_div"></div>
	<?echo BeginNote(), GetMessage("php_cmd_note"), EndNote();?>
	<?
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
}