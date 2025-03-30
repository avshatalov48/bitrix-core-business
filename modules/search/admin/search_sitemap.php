<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/prolog.php';
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
$searchDB = CDatabase::GetModuleConnection('search');

$POST_RIGHT = $APPLICATION->GetGroupRight('search');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$arSiteMap = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['Generate'] == 'Y')
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	if (check_bitrix_sessid())
	{
		if (array_key_exists('NS', $_POST) && is_array($_POST['NS']))
		{
			$NS = $_POST['NS'];
		}
		else
		{
			$NS = [];

			$sm_max_execution_time = intval($_REQUEST['sm_max_execution_time']);
			if ($sm_max_execution_time < 1)
			{
				$sm_max_execution_time = 30;
			}
			COption::SetOptionString('search', 'sm_max_execution_time', $sm_max_execution_time);

			$sm_record_limit = intval($_REQUEST['sm_record_limit']);
			if ($sm_record_limit < 1)
			{
				$sm_record_limit = 5000;
			}
			COption::SetOptionString('search', 'sm_record_limit', $sm_record_limit);

			COption::SetOptionString('search', 'sm_forum_topics_only', ($_REQUEST['sm_forum_topics_only'] == 'Y' ? 'Y' : 'N'));
			COption::SetOptionString('search', 'sm_blog_no_comments', ($_REQUEST['sm_blog_no_comments'] == 'Y' ? 'Y' : 'N'));
			COption::SetOptionString('search', 'sm_use_https', ($_REQUEST['sm_use_https'] == 'Y' ? 'Y' : 'N'));
		}


		$cSiteMap = new CSiteMap;
		$arOptions = [
			'FORUM_TOPICS_ONLY' => COption::GetOptionString('search', 'sm_forum_topics_only'),
			'BLOG_NO_COMMENTS' => COption::GetOptionString('search', 'sm_blog_no_comments'),
			'USE_HTTPS' => COption::GetOptionString('search', 'sm_use_https'),
		];

		$sm_max_execution_time = COption::GetOptionString('search', 'sm_max_execution_time');
		$sm_record_limit = COption::GetOptionString('search', 'sm_record_limit');

		$arSiteMap = $cSiteMap->Create($SM_SITE_ID, [$sm_max_execution_time, $sm_record_limit], $NS, $arOptions);
		if (is_array($arSiteMap)):
			CAdminMessage::ShowMessage([
				'TYPE' => 'PROGRESS',
				'HTML' => true,
				'DETAILS' => GetMessage('SEARCH_SITEMAP_DOC_COUNT') . ': <b>' . intval($arSiteMap['CNT']) . '</b>.<br>'
					. GetMessage('SEARCH_SITEMAP_ERR_COUNT') . ': <b>' . intval($arSiteMap['ERROR_CNT']) . '</b>.<br>',
			]);
			?>
			<script>
				CloseWaitWindow();
				DoNext(<?php echo CUtil::PhpToJSObject(['NS' => $arSiteMap])?>);
			</script>
		<?php elseif ($arSiteMap === true):?>
			<?php echo BeginNote()?>
			<p><?php echo GetMessage('SEARCH_SITEMAP_CREATED')?>: <b><a href="<?=htmlspecialcharsbx($cSiteMap->m_href)?>"><?=$cSiteMap->m_href?></a></b></p>
			<p><?php echo GetMessage('SEARCH_SITEMAP_INSTR1')?>:</p>
			<ol>
				<li>
					<?php echo GetMessage('SEARCH_SITEMAP_INSTR2')?> <a href="https://www.google.com/webmasters/tools/home">Google Sitemaps</a>
					<?php echo GetMessage('SEARCH_SITEMAP_INSTR3')?> <a href="https://www.google.com/accounts/ManageAccount"><?php echo GetMessage('SEARCH_SITEMAP_INSTR7')?></a>
				</li>
				<li>
					<?php echo GetMessage('SEARCH_SITEMAP_INSTR4')?> <strong>"<?php echo GetMessage('SEARCH_SITEMAP_INSTR8')?>"</strong>.
				</li>
				<li>
					<?php echo GetMessage('SEARCH_SITEMAP_INSTR5')?>.
				</li>
			</ol>
			<p><?php echo GetMessage('SEARCH_SITEMAP_INSTR6')?></p>
			<?php if ($cSiteMap->m_errors_count > 0):?>
				<p>
				<?php echo GetMessage('SEARCH_SITEMAP_WARN')?> <a href="<?=htmlspecialcharsbx($cSiteMap->m_errors_href)?>"><?=$cSiteMap->m_errors_href?></a>.
				<br><?php echo GetMessage('SEARCH_SITEMAP_WARN1')?>.
				</p>
			<?php endif;?>
			<?php echo EndNote()?>
			<script>
				CloseWaitWindow();
				EndReindex();
			</script>
		<?php elseif ($arSiteMap === false):?>
			<?php
			CAdminMessage::ShowMessage([
				'MESSAGE' => GetMessage('SEARCH_SITEMAP_ERR'),
				'DETAILS' => $cSiteMap->m_error,
				'TYPE' => 'ERROR',
				'HTML' => 'Y',
			]);
			?>
			<script>
				CloseWaitWindow();
				EndReindex();
			</script>
		<?php endif;
	}
	else
	{
		CAdminMessage::ShowMessage(GetMessage('SEARCH_SITEMAP_SESSION_ERR'));
		?>
		<script>
			CloseWaitWindow();
			EndReindex();
		</script>
		<?php
	}
	require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin_js.php';
}
else
{
$APPLICATION->SetTitle(GetMessage('SEARCH_SITEMAP_TITLE'));

$aTabs = [
	['DIV' => 'edit1', 'TAB' => 'Google Sitemap', 'ICON' => 'main_user_edit', 'TITLE' => GetMessage('SEARCH_SITEMAP_TAB_TITLE')],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs, true, true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if (is_object($message))
{
	echo $message->Show();
}
?>
<script>
var savedNS;
var stop;
var interval = 0;
function StartReindex()
{
	stop=false;
	document.getElementById('reindex_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	DoNext();
}
function DoNext(NS)
{
	var queryString = 'Generate=Y'
		+ '&lang=<?php echo urlencode(LANGUAGE_ID)?>'
		+ '&<?php echo bitrix_sessid_get()?>';

	var ck = document.getElementById('sm_forum_topics_only');
	if(ck && ck.checked)
		queryString += '&sm_forum_topics_only=Y';

	ck = document.getElementById('sm_blog_no_comments');
	if(ck && ck.checked)
		queryString += '&sm_blog_no_comments=Y';

	ck = document.getElementById('sm_use_https');
	if(ck && ck.checked)
		queryString += '&sm_use_https=Y';

	queryString+='&SM_SITE_ID='+document.fs2.SM_SITE_ID.value;

	if(!NS)
	{
		interval = parseInt(document.getElementById('sm_max_execution_time').value);
		queryString += '&sm_max_execution_time='+interval;
		queryString += '&sm_record_limit='+document.getElementById('sm_record_limit').value;
	}

	savedNS = NS;

	if(!stop)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'search_sitemap.php?'+queryString,
			NS,
			function(result){
				document.getElementById('reindex_result_div').innerHTML = result;
			}
		);
	}
}
function StopReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=false;
}
function ContinueReindex()
{
	stop=false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	DoNext(savedNS);
}
function EndReindex()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=true;
}
</script>

<div id="reindex_result_div">
</div>

<form method="POST" action="<?php echo $APPLICATION->GetCurPage()?>?lang=<?php echo htmlspecialcharsbx(LANG)?>" name="fs2">
<?php

$sm_max_execution_time = intval(COption::GetOptionInt('search', 'sm_max_execution_time'));
if ($sm_max_execution_time < 1)
{
	$sm_max_execution_time = 30;
}

$sm_record_limit = intval(COption::GetOptionInt('search', 'sm_record_limit'));
if ($sm_record_limit < 1)
{
	$sm_record_limit = 5000;
}

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?php echo GetMessage('SEARCH_SITEMAP_SITE')?></td>
		<td width="60%"><?php echo CLang::SelectBox('SM_SITE_ID', SITE_ID);?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('SEARCH_SITEMAP_STEP')?></td>
		<td><input type="text" name="sm_max_execution_time" id="sm_max_execution_time" size="5" value="<?php echo $sm_max_execution_time?>"> <?php echo GetMessage('SEARCH_SITEMAP_STEP_sec')?></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('SEARCH_SITEMAP_RECORD_LIMIT')?></td>
		<td><input type="text" name="sm_record_limit" id="sm_record_limit" size="5" value="<?php echo $sm_record_limit?>"></td>
	</tr>
	<?php if (IsModuleInstalled('forum')):?>
	<tr>
		<td><label for="sm_forum_topics_only"><?php echo GetMessage('SEARCH_SITEMAP_FORUM_TOPICS_ONLY')?>:</label></td>
		<td><input type="checkbox" id="sm_forum_topics_only" name="sm_forum_topics_only" value="Y"<?php
			if (COption::GetOptionString('search', 'sm_forum_topics_only') == 'Y')
			{
				echo ' checked="checked"';
			}?>></td>
	</tr>
	<?php endif?>
	<?php if (IsModuleInstalled('blog')):?>
	<tr>
		<td><label for="sm_blog_no_comments"><?php echo GetMessage('SEARCH_SITEMAP_BLOG_NO_COMMENTS')?>:</label></td>
		<td><input type="checkbox" id="sm_blog_no_comments" name="sm_blog_no_comments" value="Y"<?php
			if (COption::GetOptionString('search', 'sm_blog_no_comments') == 'Y')
			{
				echo ' checked="checked"';
			}?>></td>
	</tr>
	<?php endif?>
	<tr>
		<td><label for="sm_use_https"><?php echo GetMessage('SEARCH_SITEMAP_USE_HTTPS')?>:</label></td>
		<td><input type="checkbox" id="sm_use_https" name="sm_use_https" value="Y"<?php
			if (COption::GetOptionString('search', 'sm_use_https') == 'Y')
			{
				echo ' checked="checked"';
			}?>></td>
	</tr>
<?php
$tabControl->Buttons();
?>
	<input type="button" id="start_button" value="<?=GetMessage('SEARCH_SITEMAP_CREATE')?>" OnClick="StartReindex();" class="adm-btn-save">
	<input type="button" id="stop_button" value="<?=GetMessage('SEARCH_SITEMAP_STOP')?>" OnClick="StopReindex();" disabled>
	<input type="button" id="continue_button" value="<?=GetMessage('SEARCH_SITEMAP_CONTINUE')?>" OnClick="ContinueReindex();" disabled>
<?php
$tabControl->End();
?>
</form>

<?php echo BeginNote();?>
<?php echo GetMessage('SEARCH_SITEMAP_NOTE')?>
<?php echo EndNote();?>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
}
