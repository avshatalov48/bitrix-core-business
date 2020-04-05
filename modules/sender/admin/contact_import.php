<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_imp_import_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_imp_import_tab_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$arError = array();
$bShowRes = false;


if($REQUEST_METHOD=="POST" && !empty($Import) && $POST_RIGHT>="W" && check_bitrix_sessid())
{
	//*************************************
	//Prepare emails
	//*************************************
	//This is from the form
	$sAddr = $ADDR_LIST.",";
	//And this is from the file
	if(!empty($_FILES["ADDR_FILE"]["tmp_name"]))
	{
		if((integer)$_FILES["ADDR_FILE"]["error"] <> 0)
			$arError[] = array("id"=>"ADDR_FILE", "text"=>GetMessage("sender_imp_file_err1")." (".GetMessage("sender_imp_file_err2")." ".$_FILES["ADDR_FILE"]["error"].")");
		else
			$sAddr .= file_get_contents($_FILES["ADDR_FILE"]["tmp_name"]);
	}

	//explode to emails array
	$aEmail = array();
	$aEmailInvalid = array();
	$addr = strtok($sAddr, ",\r\n\t");
	while($addr!==false)
	{
		if(strlen($addr) > 0)
		{
			$addrPrepared = trim(strtolower($addr));
			if(check_email($addrPrepared))
			{
				$aEmail[] = $addrPrepared;
			}
			else
			{
				$aEmailInvalid[] = $addrPrepared;
			}

		}
		$addr = strtok(", \r\n\t");
	}

	$listId = null;
	if($LIST_TYPE == "N")
	{
		if(strlen($LIST_NAME_NEW) <=0 )
		{
			$arError[] = array("id"=>"", "text"=>GetMessage("sender_imp_list_new_error"));
		}
		else
		{
			$listAddDb = \Bitrix\Sender\ListTable::add(array('NAME' => $LIST_NAME_NEW));
			if($listAddDb->isSuccess())
				$listId = $listAddDb->getId();
			else
			{
				foreach($listAddDb->getErrorMessages() as $errorMessage)
					$arError[] = array("id"=>"", "text"=>$errorMessage);
			}
		}

		$LIST_NAME_EXISTS = $listId;
	}
	else
	{
		$listId = $LIST_NAME_EXISTS;
	}


	$nError = 0;
	$nSuccess = 0;
	$nNew = 0;
	$nExists = 0;
	if(count($arError) == 0 && is_numeric($listId))
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$conHelper = $connection->getSqlHelper();
		$curDateFunc = new \Bitrix\Main\Type\DateTime;

		$mailCount = count($aEmail);
		$mailCounter = 0;

		while(true)
		{
			$preparedMail = array();
			// divide all emails into parts
			$maxPart = 200;
			for($i = $mailCounter; $i<$mailCount; $i++)
			{
				$preparedMail[] = $conHelper->forSql(strtolower($aEmail[$i]));
				$maxPart--;
				if($maxPart===0) break;

				$nSuccess++;
			}
			$mailCounter = $i;

			if(!empty($preparedMail))
			{
				$contactIdList = array();
				$findMail = array();

				$preparedMail = array_unique($preparedMail);
				$emailPart = implode("', '", $preparedMail);
				$contactDb = $connection->query("select EMAIL, ID from b_sender_contact where EMAIL in ('" . $emailPart . "')");
				while ($contact = $contactDb->fetch())
				{
					$findMail[] = $conHelper->forSql($contact['EMAIL']);
					$contactIdList[] = $contact['ID'];
					$nExists++;
				}

				$newMail = array_diff($preparedMail, $findMail);
				$nNew += count($newMail);
				foreach ($newMail as $email)
				{
					$insertedId = $connection->add('b_sender_contact', array(
						'EMAIL' => $email,
						'DATE_INSERT' => $curDateFunc,
						'DATE_UPDATE' => $curDateFunc
					));

					if($insertedId>0)
						$contactIdList[] = $insertedId;
				}

				if (!empty($contactIdList))
				{
					$contactPart = implode(",", $contactIdList);
					$contactIdListExisted = array();

					$contactListDb = $connection->query("select CONTACT_ID from b_sender_contact_list where CONTACT_ID in (" . $contactPart . ") and LIST_ID=".intval($listId));
					while ($contactList = $contactListDb->fetch())
					{
						$contactIdListExisted[] = $contactList['CONTACT_ID'];
					}
					$contactIdList = array_diff($contactIdList, $contactIdListExisted);
				}

				if(!empty($contactIdList))
				{
					$contactIdList = array_unique($contactIdList);
					foreach($contactIdList as $contactId)
					{
						$contactDb = $connection->query("insert into b_sender_contact_list(CONTACT_ID, LIST_ID) values(".intval($contactId).",".intval($listId).")");
					}
				}
			}

			if($mailCounter >= $mailCount) break;
		}

		$bShowRes = true;
	}

	if(count($aEmailInvalid)>0)
	{
		foreach($aEmailInvalid as $email) if(!empty($email)) $arError[] = array("id"=>"", "text"=>htmlspecialcharsbx($email));
		$nSuccess += count($aEmailInvalid);
		$nError += count($aEmailInvalid);
	}

}//$REQUEST_METHOD=="POST"
else
{
	$LIST_TYPE = "E";
}

$listDict = array();
$listDb = \Bitrix\Sender\ListTable::getList();
while($arList = $listDb->fetch())
{
	$listDict[] = $arList;
}
if(empty($listDict))
	$LIST_TYPE = "N";

$APPLICATION->SetTitle(GetMessage("sender_imp_title"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_imp_back_to_list"),
		"TITLE"=>GetMessage("sender_imp_back_to_list_title"),
		"LINK"=>"sender_contact_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();


if(count($arError)>0)
{
	$e = new CAdminException($arError);
	$message = new CAdminMessage(GetMessage("sender_imp_error"), $e);
	echo $message->Show();
}

if($bShowRes)
{
	CAdminMessage::ShowMessage(array(
		"MESSAGE"=>GetMessage("sender_imp_results"),
		"DETAILS"=>GetMessage("sender_imp_results_total").' <b>'.$nSuccess.'</b><br>'
			.GetMessage("sender_imp_results_added").' <b>'.$nNew.'</b><br>'
			.GetMessage("sender_imp_results_exist").' <b>'.$nExists.'</b><br>'
			.GetMessage("sender_imp_results_err").' <b>'.$nError.'</b>',
		"HTML"=>true,
		"TYPE"=>"PROGRESS",
	));
}
?>
<form ENCTYPE="multipart/form-data" action="<?echo $APPLICATION->GetCurPage();?>" method="POST" name="impform">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("sender_imp_delim")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("sender_imp_file")?></td>
		<td><input type=file name="ADDR_FILE" size=30></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("sender_imp_list")?></td>
		<td><textarea name="ADDR_LIST" rows=10 cols=45><?=htmlspecialcharsbx($ADDR_LIST)?></textarea></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("sender_imp_add_list")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("sender_imp_add")?></td>
		<td>
			<table>
				<tr>
					<td class="adm-detail-content-cell-r">
						<input id="LIST_TYPE_1" name="LIST_TYPE" type="radio" value="E"<?if($LIST_TYPE == "E") echo " checked"?> <?if($listDb->getSelectedRowsCount()<=0) echo " disabled"?> onClick="DisableControls(true);"><label for="LIST_TYPE_1"><?echo GetMessage("sender_imp_add_list_exist")?> </label>
					</td>
					<td class="adm-detail-content-cell-r">
						<select name="LIST_NAME_EXISTS">
							<?foreach($listDict as $arList):?>
								<option value="<?=$arList['ID']?>" <?=($arList['ID']==$LIST_NAME_EXISTS ? 'selected':'')?>><?=htmlspecialcharsbx($arList['NAME'])?></option>
							<?endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-r">
						<input id="LIST_TYPE_2" name="LIST_TYPE" type="radio" value="N"<?if($LIST_TYPE == "N") echo " checked"?> onClick="DisableControls(false);"><label for="LIST_TYPE_2"><?echo GetMessage("sender_imp_add_list_new")?> </label>
					</td>
					<td class="adm-detail-content-cell-r">
						<input type="text" name="LIST_NAME_NEW">
					</td>
				</tr>
			</table>
			<script language="JavaScript">
				function DisableControls(bDisable)
				{
					document.impform.LIST_NAME_NEW.disabled=bDisable;
					if(document.impform.LIST_NAME_EXISTS)
						document.impform.LIST_NAME_EXISTS.disabled=!bDisable;
				}
				DisableControls(<?=($LIST_TYPE == "E" ? "true" : "false")?>);
			</script>
		</td>
	</tr>
<?
$tabControl->Buttons();
?>
<input<?if($POST_RIGHT<"W") echo " disabled";?> type="submit" name="Import" value="<?echo GetMessage("sender_imp_butt")?>" class="adm-btn-save">
<input type="hidden" name="lang" value="<?echo LANG?>">
<?echo bitrix_sessid_post();?>
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("impform", $message);
?>

<? require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>