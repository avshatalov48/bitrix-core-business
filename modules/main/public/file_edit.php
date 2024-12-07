<?
define('BX_PUBLIC_MODE', 0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

$addUrl = 'lang='.LANGUAGE_ID.(isset($logical) && $logical == "Y"?'&logical=Y':'');
$useEditor3 = COption::GetOptionString('fileman', "use_editor_3", "N") == "Y";
$bFromComponent =
	isset($_REQUEST['from']) &&
	(
		$_REQUEST['from'] == 'main.include'
		|| $_REQUEST['from'] == 'includefile'
		|| $_REQUEST['from'] == 'includecomponent'
	)
;
$bDisableEditor = !CModule::IncludeModule('fileman') || (isset($_REQUEST['noeditor']) && $_REQUEST['noeditor'] == 'Y');

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/admin/fileman_html_edit.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/public/file_edit.php");

$obJSPopup = new CJSPopup("lang=".urlencode($_GET["lang"] ?? '')."&site=".urlencode($_GET["site"] ?? '')."&back_url=".urlencode($_GET["back_url"] ?? '')."&path=".urlencode($_GET["path"] ?? '')."&name=".urlencode($_GET["name"] ?? ''), array("SUFFIX"=>(isset($_REQUEST['subdialog']) && $_REQUEST['subdialog'] == 'Y'? 'editor':'')));

$strWarning = "";
$site_template = false;
$rsSiteTemplates = CSite::GetTemplateList($site ?? '');
while($arSiteTemplate = $rsSiteTemplates->Fetch())
{
	if($arSiteTemplate["CONDITION"] == '')
	{
		$site_template = $arSiteTemplate["TEMPLATE"];
		break;
	}
}

$io = CBXVirtualIo::GetInstance();

$bVarsFromForm = false;	// if 'true' - we will get content  and variables from form, if 'false' - from saved file
$bSessIDRefresh = false;	// флаг, указывающий, нужно ли обновлять ид сессии на клиенте
$editor_name = $_REQUEST['editor_name'] ?? 'filesrc_pub';
$filename = $_REQUEST['filename'] ?? '';
$new = $_REQUEST['new'] ?? '';
$template = $_REQUEST['template'] ?? '';

if ($filename != '' && ($mess = CFileMan::CheckFileName($filename)) !== true)
{
	$filename2 = $filename;
	$filename = '';
	$strWarning = $mess;
	$bVarsFromForm = true;
}

$path = urldecode($path ?? '');
$path = $io->CombinePath("/", $path);

$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $DOC_ROOT.$path;

if(GetFileType($abs_path) == "IMAGE")
	$strWarning = GetMessage("PUBLIC_EDIT_FILE_IMAGE_ERROR");

$arPath = Array($site, $path);

if(!$io->FileExists($abs_path) && !$io->DirectoryExists($abs_path))
{
	$p = mb_strrpos($path, "/");
	if($p!==false)
	{
		$new = "Y";
		$filename = mb_substr($path, $p + 1);
		$path = mb_substr($path, 0, $p);
	}
}
$relPath = $io->ExtractPathFromPath($path);

$NEW_ROW_CNT = 1;

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", false);
$isScriptExt = in_array(CFileman::GetFileExtension($path), CFileMan::GetScriptFileExt());

if (CAutoSave::Allowed())
	$AUTOSAVE = new CAutoSave();

$imgName = $filename;
if ($filename == '')
{
	$imgName = $io->ExtractNameFromPath($path);
}
$imgName = GetFileNameWithoutExtension($imgName).'-img';

//Check access to file
if(
	(
		$new <> '' &&
		!(
			$USER->CanDoOperation('fileman_admin_files') &&
			$USER->CanDoFileOperation('fm_create_new_file', $arPath)
		)
	)
	||
	(
		$new == '' &&
		!(
			$USER->CanDoOperation('fileman_edit_existent_files') &&
			$USER->CanDoFileOperation('fm_edit_existent_file',$arPath)
		)
	)
)
{
	$strWarning = GetMessage("ACCESS_DENIED");
}
elseif($strWarning == '')
{
	if(!$USER->IsAdmin() && str_starts_with(CFileman::GetFileName($abs_path), "."))
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_BAD_FNAME")." ";
		$bEdit = false;
		$bVarsFromForm = true;
		$path = $io->CombinePath("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $DOC_ROOT.$path;
	}
	elseif($new == 'Y')
	{
		$bEdit = false;
	}
	else
	{
		if(!$io->FileExists($abs_path))
			$strWarning = GetMessage("FILEMAN_FILEEDIT_FOLDER_EXISTS")." ";
		else
			$bEdit = true;
	}

	$limit_php_access = ($USER->CanDoFileOperation('fm_lpa', $arPath) && !$USER->CanDoOperation('edit_php'));
	if ($limit_php_access)
	{
		//OFP - 'original full path' used for restorin' php code fragments in limit_php_access mode
		if (!isset(\Bitrix\Main\Application::getInstance()->getSession()['arOFP']))
			\Bitrix\Main\Application::getInstance()->getSession()['arOFP'] = Array();

		if(isset($_POST['ofp_id']))
		{
			$ofp_id = $_POST['ofp_id'];
		}
		else
		{
			$ofp_id = mb_substr(md5($site.'|'.$path), 0, 8);
			if(!isset(\Bitrix\Main\Application::getInstance()->getSession()['arOFP'][$ofp_id]))
				\Bitrix\Main\Application::getInstance()->getSession()['arOFP'][$ofp_id] = $path;
		}
	}
}

if($strWarning == '')
{
	if($bEdit)
	{
		$oFile = $io->GetFile($abs_path);
		$filesrc_tmp = $oFile->GetContents();
	}
	else
	{
		$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
		if(!empty($template))
		{
			foreach ($arTemplates as $arTemplate)
			{
				if($arTemplate["file"] == $template)
				{
					$filesrc_tmp = CFileman::GetTemplateContent($arTemplate["file"],LANGUAGE_ID, array($site_template));
					break;
				}
			}
		}
		else
		{
			$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"], LANGUAGE_ID, array($site_template));
		}
	}

	if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST['save']) && $_REQUEST['save'] == 'Y')
	{
		$filesrc = $filesrc_pub;
		if(!check_bitrix_sessid())
		{
			$strWarning = GetMessage("FILEMAN_SESSION_EXPIRED");
			$bVarsFromForm = true;
			$bSessIDRefresh = true;
		}
		elseif((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access)) //check rights
		{
			$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE");
			$bVarsFromForm = true;
		}
		else
		{
			if($limit_php_access)
			{
				// ofp - original full path :)
				$ofp = \Bitrix\Main\Application::getInstance()->getSession()['arOFP'][$ofp_id];
				$ofp = $io->CombinePath("/", $ofp);
				$abs_ofp = $DOC_ROOT.$ofp;

				$f = $io->GetFile($abs_ofp);
				$old_filesrc_tmp = $f->GetContents();
				$old_res = CFileman::ParseFileContent($old_filesrc_tmp, true);
				$old_filesrc = $old_res["CONTENT"];
				$filesrc = LPA::Process($filesrc, $old_filesrc);
			}

			$res = CFileman::ParseFileContent($filesrc_tmp, true);
			$prolog = CFileman::SetTitle($res["PROLOG"], $title);
			for ($i = 0; $i<=$maxind; $i++)
			{
				if(trim($_POST["CODE_".$i]) <> '')
				{
					if($_POST["CODE_".$i] != $_POST["H_CODE_".$i])
					{
						$prolog = CFileman::SetProperty($prolog, trim($_POST["H_CODE_".$i]), "");
						$prolog = CFileman::SetProperty($prolog, trim($_POST["CODE_".$i]), trim($_POST["VALUE_".$i]));
					}
					else
						$prolog = CFileman::SetProperty($prolog, trim($_POST["CODE_".$i]), trim($_POST["VALUE_".$i]));
				}
				else
					$prolog = CFileman::SetProperty($prolog, trim($_POST["H_CODE_".$i]), "");
			}
			$epilog = $res["EPILOG"];
			$filesrc_for_save = $prolog.$filesrc.$epilog;
		}

		if($strWarning == '')
		{
			if (!CFileMan::CheckOnAllowedComponents($filesrc_for_save))
			{
				$str_err = $APPLICATION->GetException();
				if($str_err && ($err = $str_err ->GetString()))
					$strWarning .= $err;
				$bVarsFromForm = true;
			}
		}

		if($strWarning == '')
		{
			// File was created just a second ago
			if (isset($_REQUEST["edit_new_file_undo"]) && $_REQUEST["edit_new_file_undo"] <> '')
			{
				CUndo::ShowUndoMessage($_REQUEST["edit_new_file_undo"]);
			}
			else
			{
				$f = $io->GetFile($abs_path);
				$arUndoParams = array(
					'module' => 'fileman',
					'undoType' => 'edit_file',
					'undoHandler' => 'CFileman::UndoEditFile',
					'arContent' => array(
						'absPath' => $abs_path,
						'content' => $f->GetContents()
					)
				);
			}

			if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				if($str_err = $APPLICATION->GetException())
				{
					if ($err = $str_err ->GetString())
						$strWarning = $err;

					$bVarsFromForm = true;
					$path = $io->CombinePath("/", $arParsedPath["PREV"]);
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$abs_path = $DOC_ROOT.$path;
				}

				if (empty($strWarning))
					$strWarning = GetMessage("FILEMAN_FILE_SAVE_ERROR")." ";
			}
			else
			{
				if(COption::GetOptionString("fileman", "log_page", "Y")=="Y")
				{
					$res_log['path'] = mb_substr($path, 1);
					CEventLog::Log(
						"content",
						"PAGE_EDIT",
						"main",
						"",
						serialize($res_log),
						$_REQUEST["site"] ?? ''
					);
				}

				if (CAutoSave::Allowed())
					$AUTOSAVE->Reset();
			}
		}

		if($strWarning == '')
		{
			if ($arUndoParams)
				CUndo::ShowUndoMessage(CUndo::Add($arUndoParams));

?>
<script>
<?
if(!isset($_REQUEST['subdialog']) || $_REQUEST['subdialog'] != 'Y'):
	$url = $_REQUEST["back_url"] ?? '';
	if(!str_starts_with($url, "/") || mb_substr($url, 1, 1) == "/")
	{
		//only local /url is allowed
		$url = '';
	}
?>
	top.BX.reload('<?=CUtil::JSEscape($url)?>', true);
<?else:?>
	if (null != top.structReload)
		top.structReload('<?=urlencode($_REQUEST["path"] ?? '')?>');
<?endif;?>
	top.<?=$obJSPopup->jsPopup?>.Close();
</script>
<?
		}
		else
		{
?>
<script>
top.CloseWaitWindow();
top.<?=$obJSPopup->jsPopup?>.ShowError('<?=CUtil::JSEscape($strWarning)?>');
var pMainObj = top.GLOBAL_pMainObj['<?=CUtil::JSEscape($editor_name)?>'];
pMainObj.Show(true);
<?if ($bSessIDRefresh):?>
top.BXSetSessionID('<?=CUtil::JSEscape(bitrix_sessid())?>');
<?endif;?>
</script>
<?
		}
		die();
	}
}
else
{
?>
<script>
top.CloseWaitWindow();
top.<?=$obJSPopup->jsPopup?>.ShowError('<?=CUtil::JSEscape($strWarning)?>');
var pMainObj = top.GLOBAL_pMainObj['<?=CUtil::JSEscape($editor_name)?>'];
pMainObj.Show(true);
</script>
<?
	die();
}

if(!$bVarsFromForm)
{
	$res = CFileman::ParseFileContent($filesrc_tmp, true);
	$filesrc = $res["CONTENT"];

	// ###########  L  P  A  ############
	if ($limit_php_access)
	{
		$arPHP = PHPParser::ParseFile($filesrc);
		$l = count($arPHP);
		if ($l > 0)
		{
			$new_filesrc = '';
			$end = 0;
			$php_count = 0;
			for ($n = 0; $n<$l; $n++)
			{
				$start = $arPHP[$n][0];
				$new_filesrc .= mb_substr($filesrc, $end, $start - $end);
				$end = $arPHP[$n][1];

				//Trim php tags
				$src = $arPHP[$n][2];
				if (str_starts_with($src, "<?php"))
					$src = substr($src, 5);
				else
					$src = substr($src, 2);
				$src = substr($src, 0, -2);

				//If it's Component 2, keep the php code. If it's component 1 or ordinary PHP - than replace code by #PHPXXXX# (XXXX - count of PHP scripts)
				$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
				if (mb_strtoupper(mb_substr($src, 0, mb_strlen($comp2_begin))) == $comp2_begin)
					$new_filesrc .= $arPHP[$n][2];
				else
					$new_filesrc .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
			}
			$new_filesrc .= mb_substr($filesrc, $end);
			$filesrc = $new_filesrc;
		}
	}

	$bEditProps = (mb_strpos($res["PROLOG"], "prolog_before") > 0 || mb_strpos($res["PROLOG"], "header.php") > 0);
	$title = $res["TITLE"];

	if((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access))
		$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE_ACCESS");
}

$obJSPopup->ShowTitlebar(GetMessage('PUBLIC_EDIT_TITLE'.($bFromComponent ? '_COMP' : '')).': '.htmlspecialcharsex($_GET['path']));


$obJSPopup->StartContent(
	array(
		'style' => "0px; height: 500px; overflow: hidden;",
		'class' => "bx-content-editor"
	)
);
?>
</form>
<iframe src="javascript:void(0)" name="file_edit_form_target" height="0" width="0" style="display: none;"></iframe>
<form action="/bitrix/admin/public_file_edit.php" name="editor_form" method="post" enctype="multipart/form-data" target="file_edit_form_target" style="margin: 0px; padding: 0px; ">
<?
if (CAutoSave::Allowed())
{
	echo CJSCore::Init(array('autosave'), true);
	$AUTOSAVE->Init();
?><script>BX.WindowManager.Get().setAutosave();</script><?
}
?>
<?=bitrix_sessid_post()?>
<input type="submit" name="submitbtn" style="display: none;" />
<input type="hidden" name="mode" id="mode" value="public" />
<input type="hidden" name="save" id="save" value="Y" />
<input type="hidden" name="site" id="site" value="<?=htmlspecialcharsbx($site)?>" />
<input type="hidden" name="template" id="template" value="<?echo htmlspecialcharsbx($template)?>" />
<input type="hidden" name="templateID" id="templateID" value="<?echo htmlspecialcharsbx($_REQUEST['templateID'] ?? '')?>" />
<input type="hidden" name="subdialog" value="<?echo htmlspecialcharsbx($_REQUEST['subdialog'] ?? '')?>" />
<?if (is_set($_REQUEST, 'back_url')):?>
	<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($_REQUEST['back_url'] ?? '')?>" />
<?endif;?>
<?if (is_set($_REQUEST, 'edit_new_file_undo')):?>
	<input type="hidden" name="edit_new_file_undo" value="<?=htmlspecialcharsbx($_REQUEST['edit_new_file_undo'] ?? '')?>" />
<?endif;?>
<?if(!$bEdit):?>
	<input type="hidden" name="new" id="new" value="Y" />
	<input type="hidden" name="filename" id="filename" value="<?echo htmlspecialcharsbx($filename)?>" />
	<input type="hidden" name="path" id="path" value="<?=htmlspecialcharsbx($path.'/'.$filename)?>" />
<?else:?>
	<input type="hidden" name="title" value="<?=htmlspecialcharsbx($title)?>" />
	<input type="hidden" name="path" id="path" value="<?=htmlspecialcharsbx($path)?>" />
<?endif;?>

<script>
<?=$obJSPopup->jsPopup?>.PARTS.CONTENT.getElementsByTagName('FORM')[0].style.display = 'none'; // hack

function BXFormSubmit()
{
	ShowWaitWindow();
	var obForm = document.forms.editor_form;
	obForm.elements.submitbtn.click();
}

function BXSetSessionID(new_sessid)
{
	document.forms.editor_form.sessid.value = new_sessid;
}
</script>

<?
if (!$bDisableEditor)
{
	/* ************* HTML EDITOR 3.0 ************* */
	if ($useEditor3)
	{
		$Editor = new CHTMLEditor;
		$Editor->Show(array(
			'name' => $editor_name,
			'id' => $editor_name,
			'width' => '100%',
			'height' => '490',
			'content' => $filesrc,
			'bAllowPhp' => $USER->CanDoOperation('edit_php'),
			"limitPhpAccess" => $limit_php_access,
			"site" => $site,
			"relPath" => $relPath,
			"templateId" => $_REQUEST['templateID'] ?? '',
		));

		?>
		<script>
			(function() {
				var
					editorDialog = BX.WindowManager.Get(),
					editor = top.BXHtmlEditor.Get('<?=CUtil::JSEscape($editor_name)?>');

				if (editor.IsInited())
				{
					onEditorInited();
				}
				else
				{
					BX.addCustomEvent(editor, "OnEditorInitedAfter", onEditorInited);
				}

				BX.addCustomEvent(editorDialog, 'onWindowResizeExt', onEditorDialogResize);
				BX.addCustomEvent(editorDialog, 'onBeforeWindowClose', onBeforeDialogClose);
				BX.addCustomEvent(editorDialog, 'onWindowUnRegister', onEditorUnregister);

				function onEditorDialogResize(params)
				{
					if (this.offsetTop === undefined)
						this.offsetTop = editor.CheckBrowserCompatibility() ? 0 : 40;

					var
						width = params.width,
						height = params.height - this.offsetTop;

					editor.SetConfigHeight(height);
					editor.ResizeSceleton(width, height);
				}

				function onEditorInited()
				{
					onEditorDialogResize(editorDialog.GetInnerPos());
					function ConfirmExitDialog(editor)
					{
						var params = {
							id: 'bx_confirm_exit',
							width: 500,
							resizable: false,
							className: 'bxhtmled-confirm-exit-dialog'
						};

						var _this = this;
						this.id = 'confirm_exit';
						// Call parrent constructor
						ConfirmExitDialog.superclass.constructor.apply(this, [editor, params]);

						this.oDialog.ClearButtons();
						this.oDialog.SetButtons([
							new BX.CWindowButton(
								{
									title: '<?= GetMessageJS('PUBLIC_EDIT_SAVE_BUT')?>',
									className: 'adm-btn-save',
									action: function()
									{
										if (typeof window.BXFormSubmit == 'function')
										{
											BXFormSubmit();
										}
										_this.oDialog.Close(true);
									}
								}),
							new BX.CWindowButton(
								{
									title: '<?= GetMessageJS('PUBLIC_EDIT_EXIT_BUT')?>',
									action: function()
									{
										editorDialog.Close(true);
										_this.oDialog.Close(true);
									}
								}),
							this.oDialog.btnCancel
						]);
						this.SetContent("<?= GetMessageJS('PUBLIC_EDIT_DIALOG_EXIT_ACHTUNG')?>");
						this.SetTitle("<?= GetMessageJS('PUBLIC_EDIT_EDITOR')?>");
					}
					BX.extend(ConfirmExitDialog, window.BXHtmlEditor.Dialog);
					editor.RegisterDialog('ConfirmExit', ConfirmExitDialog);

					BX.addCustomEvent(editor, 'OnIframeKeyDown', function(e, keyCode, target)
					{
						if (keyCode == 27 && !editor.IsExpanded() && !editor.IsPopupsOpened())
						{
							editorDialog.Close();
						}
					});

					BX.addCustomEvent(editor, 'OnGetDefaultUploadImageName', function(nameObj)
					{
						nameObj.value = '<?=CUtil::JSEscape($imgName)?>';
					});
				}

				function onBeforeDialogClose()
				{
					if (editor.IsExpanded() || editor.IsPopupsOpened())
					{
						editorDialog.DenyClose();
					}
					else if (editor.IsContentChanged() && !editor.IsSubmited())
					{
						editorDialog.DenyClose();
						editor.GetDialog('ConfirmExit').Show();
					}
				}

				function onEditorUnregister()
				{
					editor.Destroy();
				}
			})();
		</script>
		<?
		/* ************* END |HTML EDITOR 3.0| END ************* */
	}
	else
	{
		/* ************* OLD HTML EDITOR ************* */
		CFileman::ShowHTMLEditControl($editor_name, $filesrc, Array(
			"site" => $site,
			"templateID" => $_REQUEST['templateID'] ?? '',
			"bUseOnlyDefinedStyles" => COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
			"bWithoutPHP" => (!$USER->CanDoOperation('edit_php')),
			"toolbarConfig" => CFileman::GetEditorToolbarConfig($editor_name),
			"arTaskbars" => Array("BXComponentsTaskbar", "BXComponents2Taskbar", "BXPropertiesTaskbar", "BXSnippetsTaskbar"),
			"sBackUrl" => $back_url,
			"path" => $path,
			"limit_php_access" => $limit_php_access,
			'height' => '490',
			'width' => '100%',
			'light_mode' => true,
		));

	?>
	<script>
		var _bEdit = true;
		arEditorFastDialogs['asksave'] = function(pObj)
		{
			return {
				title: BX_MESS.EDITOR,
				innerHTML : "<div style='margin-bottom: 20px; padding: 5px;'>" + BX_MESS.DIALOG_EXIT_ACHTUNG + "</div>",
				width: 700,
				height: 130,
				OnLoad: function()
				{
					window.oBXEditorDialog.SetButtons([
						new BX.CWindowButton(
							{
								title: BX_MESS.DIALOG_SAVE_BUT,
								action: function()
								{
									pObj.pMainObj.isSubmited = true;
									if(pObj.params.savetype == 'save')
										BXFormSubmit();
									window.oBXEditorDialog.Close(true);
								},
								className: 'adm-btn-save'
							}),
						new BX.CWindowButton(
							{
								title: BX_MESS.DIALOG_EXIT_BUT,
								action: function()
								{
									pObj.pMainObj.isSubmited = true;
									<?=$obJSPopup->jsPopup?>.CloseDialog();
									pObj.pMainObj.oPublicDialog.Close(true);
								}
							}),
						window.oBXEditorDialog.btnCancel
					]);

					BX.addClass(window.oBXEditorDialog.PARTS.CONTENT_DATA, "bxed-dialog");
				}
			};
		};

		function _BXOnBeforeCloseDialog()
		{
			var pMainObj = GLOBAL_pMainObj['<?=CUtil::JSEscape($editor_name)?>'];

			// We need to ask user
			if (pMainObj.IsChanged() && !pMainObj.isSubmited)
			{
				pMainObj.oPublicDialog.DenyClose();
				pMainObj.OpenEditorDialog("asksave", false, 600, {window: window, savetype: _bEdit ? 'save' : 'saveas', popupMode: true}, true);
			}
		}

		function CheckEditorFinish()
		{
			var pMainObj = GLOBAL_pMainObj['<?=CUtil::JSEscape($editor_name)?>'];
			if (!pMainObj.bLoadFinish)
				return setTimeout('CheckEditorFinish()', 100);

			<?=$obJSPopup->jsPopup?>.AllowClose();

			pMainObj.oPublicDialog = BX.WindowManager.Get();
			BX.addClass(pMainObj.oPublicDialog.PARTS.CONTENT, "bx-editor-dialog-cont");
			pMainObj.oPublicDialog.AllowClose();

			// Hack for prevent editor visual bugs from reappending styles from core_window.css
			BX.removeClass(BX.findParent(pMainObj.pWnd, {tagName: "DIV", className: "bx-core-dialog-content"}), "bx-core-dialog-content");

			if (BX.browser.IsIE())
			{
				pMainObj.pWnd.firstChild.rows[0].style.height = '1px';
				var sftbl;
				if (sftbl = BX.findChild(pMainObj.oPublicDialog.PARTS.CONTENT, {tagName: "TABLE"}))
				{
					sftbl.cellSpacing = 0;
					sftbl.cellPadding = 0;
				}
			}

			var onWinResizeExt = function(Params)
			{
				var
					topTlbrH = BX('filesrc_pub_toolBarSet0').offsetHeight || 51,
					h = parseInt(Params.height) - 2,
					w = parseInt(Params.width) - 3;

				pMainObj.pWnd.style.height = h + "px";
				pMainObj.pWnd.style.width = w + "px";
				BX.findParent(pMainObj.cEditor, {tagName: "TABLE"}).style.height = (h - (topTlbrH + 35)) + "px";
				pMainObj.arTaskbarSet[2]._SetTmpClass(true);
				pMainObj.arTaskbarSet[2].Resize(false, false, false);
				pMainObj.arTaskbarSet[3].Resize(false, false, false);

				if (window._SetTmpClassInterval)
					clearInterval(window._SetTmpClassInterval);
				window._SetTmpClassInterval = setTimeout(function()
				{
					pMainObj.arTaskbarSet[2]._SetTmpClass(false);
					pMainObj.SetCursorFF();
				}, 300);
			}
			onWinResizeExt(pMainObj.oPublicDialog.GetInnerPos());
			BX.addCustomEvent(pMainObj.oPublicDialog, 'onWindowResizeExt', onWinResizeExt);
			BX.addCustomEvent(pMainObj.oPublicDialog, 'onBeforeWindowClose', _BXOnBeforeCloseDialog);
		}

		CheckEditorFinish();

		<? if (COption::GetOptionString("fileman", "htmleditor_fullscreen", "N") == "Y"):?>
		BX.WindowManager.Get().__expand();
		<?endif;?>
		</script>
		<?
		/* ************* END |OLD HTML EDITOR| END ************* */
	}
	?>


<?
}
else //if ($bDisableEditor)
{
	?>
<textarea name="<?=htmlspecialcharsbx($editor_name)?>" id="<?=htmlspecialcharsbx($editor_name)?>" style="height: 99%; width: 100%;"><?=htmlspecialcharsex($filesrc)?></textarea>
<script>
var
	border,
	wnd = BX.WindowManager.Get();

function TAResize(data)
{
	var ta = BX('<?=CUtil::JSEscape($editor_name)?>');
	if (null == border)
		border = parseInt(BX.style(ta, 'border-left-width')) + parseInt(BX.style(ta, 'border-right-width'));

	if (isNaN(border))
		border = 0;

	if (data.height)
		ta.style.height = (data.height - border - 10) + 'px';
	if (data.width)
		ta.style.width = (data.width - border - 10) + 'px';
}

BX.addCustomEvent(wnd, 'onWindowResizeExt', TAResize);
TAResize(wnd.GetInnerPos());
</script>
<?
} //if (!$bDisableEditor)
$obJSPopup->StartButtons();
?>
	<input type="button" class="adm-btn-save" id="btn_popup_save" name="btn_popup_save" value="<?=GetMessage("JSPOPUP_SAVE_CAPTION")?>" onclick="BXFormSubmit();" title="<?=GetMessage("JSPOPUP_SAVE_CAPTION")?>" />
<?
$obJSPopup->ShowStandardButtons(array('cancel'));
$obJSPopup->EndButtons();

if (CAutoSave::Allowed())
{
	$AUTOSAVE->checkRestore();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>