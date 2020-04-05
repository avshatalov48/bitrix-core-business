<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$io = CBXVirtualIo::GetInstance();

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = $io->CombinePath("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true);
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);
$bUploaded = false;
$file_name = "";
$strWarning = "";
if($REQUEST_METHOD=="POST" && strlen($saveimg)>0 && check_bitrix_sessid())
{
	if(!$USER->CanDoOperation('fileman_edit_existent_files') ||
	!$USER->CanDoFileOperation('fm_edit_existent_file',$arPath))
	{
		$strWarning = GetMessage('FILEMAN_CAN_NOT_WRITE')."<br>";
	}
	else
	{
		$file_name = CFileman::GetFileName($newfilename);
		if(strlen($file_name)<=0 || $file_name=="none")
			continue;

		if(!$USER->IsAdmin() && (in_array(CFileman::GetFileExtension($file_name), CFileMan::GetScriptFileExt()) || $file_name[0]=="."))
			$strWarning .= GetMessage("FILEMAN_UPLOAD_BAD_TYPE")."\n";
		elseif($io->FileExists($abs_path."/".$file_name))
	    	$strWarning = GetMessage("FILEMAN_FILE_EXIST")."<br>";
		else
		{
			if($io->Copy($_FILES["imagefile"]["tmp_name"], $abs_path."/".$file_name))
			{
				$bUploaded=true;
				$f = $io->GetFile($abs_path."/".$file_name);
				$f->MarkWritable();
			}
		}
	}
}

if($bUploaded):
	?><script>
	window.top.opener.SetUrl('<?=AddSlashes(htmlspecialcharsex($path."/".$file_name))?>') ;
	window.close();
	</script><?
else:
    ShowError($strWarning);
?>
<script>
<!--
window.focus();

function OnNameChange()
{
	if(imageupload.newfilename.value.length>0)
		imageupload.save.disabled=false;
	else
		imageupload.save.disabled=true;
}

function NewFileName()
{
    var str_filename;
    var filename;
    var str_file = document.imageupload.imagefile.value;
    filename = str_file.substr(str_file.lastIndexOf("\\")+1);
    document.imageupload.newfilename.value = filename;
    if(imageupload.preview)
    {
		imageupload.preview.src=document.imageupload.imagefile.value;
		hiddenimg.src=document.imageupload.imagefile.value;
    }
	OnNameChange();
}

function KeyPress()
{
	if(window.event && window.event.keyCode == 27)
		window.close();
}

function filelist_OnLoad(strDir)
{
	document.cookie = "lopendir=" + escape(strDir) + ";";// expires=Fri, 31 Dec 2009 23:59:59 GMT;";
	//window.opener.strPath=strDir;
	imageupload.url.value=strDir+"/";
	imageupload.path.value=strDir;
	imageupload.bSelect.disabled=true;
}

function filelist_OnFileSelect(strPath)
{
	imageupload.url.value=strPath;
	if(imageupload.preview)
		imageupload.preview.src=strPath;
	imageupload.bSelect.disabled=false;
	hiddenimg.src=strPath;
}

<?if ($WF_CONVERT=="Y"):?>
function WF_OnFileSelect(strPath, strTemp)
{
	var src;
	src = "/bitrix/admin/workflow_get_file.php?cash=Y&did=<?=intval($DOCUMENT_ID)?>&wf_path=<?=urlencode($WF_PATH)?>&fname="+strPath;
	imageupload.url.value=strPath;
	if(imageupload.preview)
		imageupload.preview.src=src;
	imageupload.bSelect.disabled=false;
	hiddenimg.src=src;
}
<?endif;?>


function SelectImage(fname)
{
	window.top.opener.SetUrl(fname) ;
	window.close();
}

function ShowSize(obj)
{
	imageupload.imgwidth.value=obj.width;
	imageupload.imgheight.value=obj.height;
	var W=obj.width, H=obj.height;
	if(W>100)
	{
		H=H*((100.0)/W);
		W=100;
	}

	if(H>100)
	{
		W=W*((100.0)/H);
		H=100;
	}


	if(imageupload.preview)
	{
		imageupload.preview.width=W;
		imageupload.preview.height=H;
		imageupload.fs.value=Math.round(obj.fileSize);
	}
}
//-->
</script>
<?echo "<title".">".GetMessage("FILEMAN_IMAGE_LOADING")."</title>";?>
<img id=hiddenimg style="visibility:hidden; position: absolute; left:-1000; top: -1000px;" onerror="badimg = true;" onload="ShowSize(this)">
<form target="_self" action="fileman_fck_browser.php" method="post" enctype="multipart/form-data" name="imageupload">
<input type="hidden" name="logical" value="<?=htmlspecialcharsex($logical)?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="saveimg" value="Y">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td width="0%">
<iframe name="filelist" src="fileman_file_list.php?path=<?echo urlencode(isset($lopendir) ? $lopendir : $path)?>&site=<?=urlencode($site)?>&lang=<?echo LANG?>&type=<?=urlencode($type)?>" width="450" height="250"></iframe>
</td>
<?if($type=="image"):?>
<td width="2%">&nbsp;</td>
<td valign="top" width="98%" align="center">
	<font class="tableheadtext"><?=GetMessage('FILEMAN_PREVIEW')."<br>"?><hr size="1">
	<img src="/bitrix/images/1.gif" width="100" name="preview">
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td align="right"><font class="tablebodytext"><?=GetMessage('FILEMAN_FILE_SIZE')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="5" name="fs" readonly></td>
		</tr>
		<tr>
			<td align="right"><font class="tablebodytext"><?=GetMessage('FILEMAN_WIDTH')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="5" name="imgwidth" readonly></td>
		</tr>
		<tr>
			<td align="right"><font class="tablebodytext"><?=GetMessage('FILEMAN_HEIGHT')?>&nbsp;</font></td>
			<td><input class="typeinput" type="text" size="5"  name="imgheight" readonly></td>
		</tr>
	</table>
	</font>
</td>
<?endif?>
</tr>
</table>
<?
if ($WF_CONVERT=="Y" && intval($DOCUMENT_ID) > 0 && CModule::IncludeModule("workflow")):
	$doc_files = CWorkflow::GetFileList(intval($DOCUMENT_ID));
	$doc_files->NavStart();
	if ($doc_files->SelectedRowsCount()>0):
?>
<br>
<table border="0" cellspacing="0" cellpadding="0" width="450">
	<tr>
		<td colspan="2" align="left"><font class="tableheadtext"><b><?=GetMessage('FILEMAN_UPLOADED_FILES')?></b></font> </td>
	</tr>
	<tr>
		<td align="center" colspan="2" width="0%">
			<table border="0" cellspacing="0" cellpadding="0" class="tableborder" width="100%">
				<tr>
					<td>
						<table border="0" cellspacing="1" cellpadding="3">
							<tr>
								<td class="tablehead" align="center"><font class="tableheadtext">ID</font></td>
								<td class="tablehead" align="center" width="50%"><font class="tableheadtext"><?=GetMessage("FILEMAN_FILENAME")?></font></td>
								<td class="tablehead" align="center"><font class="tableheadtext"><?=GetMessage("FILEMAN_SIZE")?></font></td>
								<td class="tablehead" align="center"><font class="tableheadtext"><?=GetMessage("FILEMAN_FILE_LOADED")?></font></td>
								<td class="tablehead" align="center" width="50%"><font class="tableheadtext"><?=GetMessage("FILEMAN_UPLOADED_BY")?></font></td>
							</tr>
							<?
							while ($zr=$doc_files->GetNext()) :
								$ftype = GetFileType($zr["FILENAME"]);
								if ($ftype=="IMAGE") :
							?>
							<tr>
								<td class="tablebody"><font class="tablebodytext"><?=$zr["ID"]?></font></td>
								<td class="tablebody"><font class="tablebodytext"><a onclick="WF_OnFileSelect('<?= AddSlashes(htmlspecialcharsex($zr["FILENAME"]))?>'); return false;" href="javascript:void(0)" ><?= htmlspecialcharsex($zr["FILENAME"])?></a></font></td>
								<td class="tablebody" align="right"><font class="tablebodytext"><?=$zr["FILESIZE"]?></font></td>
								<td class="tablebody" align="center" nowrap><font class="tablebodytext"><?=$zr["TIMESTAMP_X"]?></font></td>
								<td class="tablebody"><font class="tablebodytext">[<a target="_blank" class="tablebodylink" href="user_edit.php?ID=<?echo $zr["MODIFIED_BY"]?>&lang=<?=LANG?>"><?echo $zr["MODIFIED_BY"]?></a>]&nbsp;<?echo $zr["USER_NAME"]?></font></td>
							</tr>
							<?
								endif;
							endwhile;
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?
	endif;
endif;
?>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2" align="left"><font class="tableheadtext"><b><?=GetMessage('FILEMAN_SELECT_IMAGE')?></b></font></td>
	</tr>
	<tr>
		<td width="0%" align="right"><font class="tablebodytext">&nbsp;URL:&nbsp;</font></td>
		<td width="100%"><input class="typeinput" type="text" name="url" size="40" value=""><img src="/bitrix/images/1.gif" width="2" height="1" border=0 alt=""><input class="button" type="button" name="bSelect" onclick="SelectImage(imageupload.url.value)" value="<?=GetMessage('FILEMAN_SELECT_IMAGE')?>"></font></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="left"><font class="tableheadtext"><b><?=GetMessage('FILEMAN_UPLOAD_IMAGE')?></b></font></td>
	</tr>
	<tr>
		<td nowrap align="right"><font class="tablebodytext">&nbsp;<?=GetMessage('FILEMAN_FILE')?>&nbsp;</font></td>
		<td><input class="typeinput" type="file" name="imagefile" size="20" onChange="NewFileName();"><br></td>
	</tr>
	<tr>
		<td nowrap align="right"><font class="tablebodytext">&nbsp;<?=GetMessage('FILEMAN_NEW_FILENAME')?>&nbsp;</font></td>
		<td>
		<input class="typeinput" type="text" name="newfilename" size="20" onchange="OnNameChange()">
		<input class="button" type="submit" name="save" value="<?=GetMessage('FILEMAN_UPLOAD')?>" DISABLED></font></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><input type="hidden" name="path" value="<?=htmlspecialcharsex($path)?>"></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><br></td>
	</tr>
	<tr>
		<td colspan="2" nowrap align="center"><input class="button" type="button" name="<?=GetMessage('FILEMAN_CANCEL')?>" value="<?=GetMessage('FILEMAN_CLOSE_WINDOW')?>" onClick="window.close();"></td>
	</tr>
</table>
</form>
<?endif;?>
</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>
