<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
/***************************************************************************
Компонент редактирования профайла пользователя
****************************************************************************/

IncludeTemplateLangFile(__FILE__);
extract($_POST, EXTR_SKIP);
global $REQUEST_METHOD;

$ID=intval($USER->GetID());
if($ID<=0) $APPLICATION->AuthForm();

$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");
if($MAIN_RIGHT!="P" && $MAIN_RIGHT!="T" && $MAIN_RIGHT!="W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//echo "<pre>"; print_r($_POST); echo "</pre>";

/***************************************************************************
Обработка GET | POST
****************************************************************************/

$strError="";
$ID=IntVal($ID);

if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0) && check_bitrix_sessid())
{
	$obUser = new CUser;

	if($ID=="1")
	{
		$ACTIVE = "Y";
		$GROUP_ID[]=1;
	}

	$arPERSONAL_PHOTO = $_FILES["PERSONAL_PHOTO"];
	$arPERSONAL_PHOTO["del"] = ${"PERSONAL_PHOTO_del"};

	$arWORK_LOGO = $_FILES["WORK_LOGO"];
	$arWORK_LOGO["del"] = ${"WORK_LOGO_del"};

	$rsUser = CUser::GetByID($ID);
	if($arUser = $rsUser->Fetch())
	{
		$arPERSONAL_PHOTO["old_file"] = $arUser["PERSONAL_PHOTO"];
		$arWORK_LOGO["old_file"] = $arUser["WORK_LOGO"];
	}

	$arFields = Array(
		"NAME"					=> $NAME,
		"LAST_NAME"				=> $LAST_NAME,
		"SECOND_NAME"			=> $SECOND_NAME,
		"EMAIL"					=> $EMAIL,
		"LOGIN"					=> $LOGIN,
		"PERSONAL_PROFESSION"	=> $PERSONAL_PROFESSION,
		"PERSONAL_WWW"			=> $PERSONAL_WWW,
		"PERSONAL_ICQ"			=> $PERSONAL_ICQ,
		"PERSONAL_GENDER"		=> $PERSONAL_GENDER,
		"PERSONAL_BIRTHDAY"		=> $PERSONAL_BIRTHDAY,
		"PERSONAL_PHOTO"		=> $arPERSONAL_PHOTO,
		"PERSONAL_PHONE"		=> $PERSONAL_PHONE,
		"PERSONAL_FAX"			=> $PERSONAL_FAX,
		"PERSONAL_MOBILE"		=> $PERSONAL_MOBILE,
		"PERSONAL_PAGER"		=> $PERSONAL_PAGER,
		"PERSONAL_STREET"		=> $PERSONAL_STREET,
		"PERSONAL_MAILBOX"		=> $PERSONAL_MAILBOX,
		"PERSONAL_CITY"			=> $PERSONAL_CITY,
		"PERSONAL_STATE"		=> $PERSONAL_STATE,
		"PERSONAL_ZIP"			=> $PERSONAL_ZIP,
		"PERSONAL_COUNTRY"		=> $PERSONAL_COUNTRY,
		"PERSONAL_NOTES"		=> $PERSONAL_NOTES,
		"WORK_COMPANY"			=> $WORK_COMPANY,
		"WORK_DEPARTMENT"		=> $WORK_DEPARTMENT,
		"WORK_POSITION"			=> $WORK_POSITION,
		"WORK_WWW"				=> $WORK_WWW,
		"WORK_PHONE"			=> $WORK_PHONE,
		"WORK_FAX"				=> $WORK_FAX,
		"WORK_PAGER"			=> $WORK_PAGER,
		"WORK_STREET"			=> $WORK_STREET,
		"WORK_MAILBOX"			=> $WORK_MAILBOX,
		"WORK_CITY"				=> $WORK_CITY,
		"WORK_STATE"			=> $WORK_STATE,
		"WORK_ZIP"				=> $WORK_ZIP,
		"WORK_COUNTRY"			=> $WORK_COUNTRY,
		"WORK_PROFILE"			=> $WORK_PROFILE,
		"WORK_LOGO"				=> $arWORK_LOGO,
		"WORK_NOTES"			=> $WORK_NOTES
		);

//echo "<pre>"; print_r($arFields); echo "</pre>";
	if($MAIN_RIGHT=="W" && strlen($LID)>0) $arFields["LID"] = $LID;
	if($MAIN_RIGHT=="W" && is_set($_POST, 'EXTERNAL_AUTH_ID')) $arFields['EXTERNAL_AUTH_ID'] = $EXTERNAL_AUTH_ID;
	if($USER->IsAdmin())
	{
		$arFields["ACTIVE"]=$ACTIVE;
		$arFields["GROUP_ID"]=$GROUP_ID;
		$arFields["ADMIN_NOTES"]=$ADMIN_NOTES;
	}
	if(strlen($NEW_PASSWORD)>0)
	{
		$arFields["PASSWORD"]=$NEW_PASSWORD;
		$arFields["CONFIRM_PASSWORD"]=$NEW_PASSWORD_CONFIRM;
	}

	//echo "arFields:<pre>"; print_r($arFields); echo "</pre>";
	
	if($ID>0) $res = $obUser->Update($ID, $arFields, true);
	else
	{
		$ID = $obUser->Add($arFields);
		$res = ($ID>0);
		$new="Y";
	}

	$strError .= $obUser->LAST_ERROR;

	if (strlen($strError)<=0)
	{
		if (CModule::IncludeModule("forum"))
		{
			$arforumFields = Array(
				"SHOW_NAME"		=> ($forum_SHOW_NAME=="Y") ? "Y" : "N",
				"DESCRIPTION"	=> $forum_DESCRIPTION,
				"INTERESTS"		=> $forum_INTERESTS,
				"SIGNATURE"		=> $forum_SIGNATURE,
				"AVATAR"		=> $_FILES["forum_AVATAR"]
			);
			$arforumFields["AVATAR"]["del"] = $forum_AVATAR_del;

			if ($USER->IsAdmin()) $arforumFields["ALLOW_POST"] = (($forum_ALLOW_POST=="Y") ? "Y" : "N");

			$ar_res = CForumUser::GetByUSER_ID($ID);
			if ($ar_res)
			{
				$arforumFields["AVATAR"]["old_file"] = $ar_res["AVATAR"];
				$FORUM_USER_ID = IntVal($ar_res["ID"]);
				$FORUM_USER_ID1 = CForumUser::Update($FORUM_USER_ID, $arforumFields);
				$forum_res = (IntVal($FORUM_USER_ID1)>0);
			}
			else
			{
				$arforumFields["USER_ID"] = $ID;
				$FORUM_USER_ID = CForumUser::Add($arforumFields);
				$forum_res = (IntVal($FORUM_USER_ID)>0);
			}
		}
	}
	
	if (strlen($strError)<=0)
	{
		if (CModule::IncludeModule("blog"))
		{
			$arblogFields = Array(
				"ALIAS" => $blog_ALIAS,
				"DESCRIPTION" => $blog_DESCRIPTION,
				"INTERESTS" => $blog_INTERESTS,
				"AVATAR" => $_FILES["blog_AVATAR"]
			);
			$arblogFields["AVATAR"]["del"] = $blog_AVATAR_del;

			if ($USER->IsAdmin())
				$arblogFields["ALLOW_POST"] = (($blog_ALLOW_POST=="Y") ? "Y" : "N");

			$ar_res = CBlogUser::GetByID($ID, BLOG_BY_USER_ID);
			if ($ar_res)
			{
				$arblogFields["AVATAR"]["old_file"] = $ar_res["AVATAR"];
				$BLOG_USER_ID = IntVal($ar_res["ID"]);

				$BLOG_USER_ID1 = CBlogUser::Update($BLOG_USER_ID, $arblogFields);
				$blog_res = (IntVal($BLOG_USER_ID1)>0);
			}
			else
			{
				$arblogFields["USER_ID"] = $ID;
				$arblogFields["~DATE_REG"] = CDatabase::CurrentTimeFunction();

				$BLOG_USER_ID = CBlogUser::Add($arblogFields);
				$blog_res = (IntVal($BLOG_USER_ID)>0);
			}
		}
	}
	
	if(CModule::IncludeModule("learning") && strlen($strError)<=0)
	{
		$arStudentFields = Array(
			"RESUME" => $student_RESUME,
			"PUBLIC_PROFILE" => ($student_PUBLIC_PROFILE=="Y" ? "Y" : "N")
		);

		$ar_res = CStudent::GetList(Array(), Array("USER_ID" => $ID));
		if ($arStudent = $ar_res->Fetch())
		{
			$learning_res = CStudent::Update($ID, $arStudentFields);
		}
		else
		{
			$arStudentFields["USER_ID"] = $ID;
			$STUDENT_USER_ID = CStudent::Add($arStudentFields);
			$learning_res = (intval($STUDENT_USER_ID)>0);
		}
	}
}


$rsUser = CUser::GetByID($ID);
if(!$arUser = $rsUser->GetNext(false))
{
	$ID=0;
	$arUser["ACTIVE"]="Y";
}
else
{
	$arUser["GROUP_ID"] = CUser::GetUserGroup($ID);
}

//echo "arUser:<pre>"; print_r($arUser); echo "</pre>";
if (CModule::IncludeModule("blog"))
{

	$arBlogUser = CBlogUser::GetByID($ID, BLOG_BY_USER_ID);
	if (!isset($arBlogUser["ALLOW_POST"]) || ($arBlogUser["ALLOW_POST"]!="Y" && $arBlogUser["ALLOW_POST"]!="N"))
		$arBlogUser["ALLOW_POST"] = "Y";
}

if (CModule::IncludeModule("forum"))
{
	$ID = IntVal($ID);
	$rsForumUser = CForumUser::GetList(array(), array("USER_ID" => $ID));
	$arForumUser = $rsForumUser->GetNext(false);
	if (!isset($arForumUser["ALLOW_POST"]) || ($arForumUser["ALLOW_POST"]!="Y" && $arForumUser["ALLOW_POST"]!="N"))
		$arForumUser["ALLOW_POST"] = "Y";
}

if (CModule::IncludeModule("learning"))
{
	$dbStudent = CStudent::GetList(array(), array("USER_ID" => $ID));
	$arStudent = $dbStudent->GetNext();
	if (!isset($arStudent["PUBLIC_PROFILE"]) || ($arStudent["PUBLIC_PROFILE"]!="Y" && $arStudent["PUBLIC_PROFILE"]!="N"))
		$arStudent["PUBLIC_PROFILE"] = "N";
}
	
if(strlen($strError)>0)
{
	foreach($_POST as $k=>$val)
	{
		if(!is_array($val))
		{
			$arUser[$k] = htmlspecialcharsex($val);
			$arForumUser[$k] = htmlspecialcharsex($val);
		}
		else
		{
			$arUser[$k] = $val;
			$arForumUser[$k] = $val;
		}
	}
	$arUser["GROUP_ID"] = $GROUP_ID;
}

if(!is_array($arUser["GROUP_ID"])) $arUser["GROUP_ID"]=Array();

/***************************************************************************
HTML форма
****************************************************************************/

?>
<a name="tb"></a>
<?echo ShowError($strError);?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function SectionClick(id)
{
	var div = document.getElementById('user_div_'+id);
	document.cookie = "user_div_"+id+"="+(div.style.display != 'none'? 'N':'Y')+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";
	div.style.display = (div.style.display != 'none'? 'none':'block');
}
//-->
</SCRIPT>

<form method="POST" name="form1" action="<?echo $APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value=<?echo $ID?>>
<table border="0" cellpadding="3" width="100%" cellspacing="1">
	<?if($ID>0):?>
	<?if (strlen($arUser["TIMESTAMP_X"])>0):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_UPDATE')?></font></td>
		<td><font class="tablebodytext"><?echo $arUser["TIMESTAMP_X"]?></font></td>
	</tr>
	<?endif;?>
	<?if (strlen($arUser["LAST_LOGIN"])>0):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_LOGIN')?></font></td>
		<td><font class="tablebodytext"><?echo $arUser["LAST_LOGIN"]?></font></td>
	</tr>
	<?endif;?>
	<?endif;?>
	<?if($ID!='1' && ($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W")):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('ACTIVE')?></font></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($arUser["ACTIVE"]=="Y")echo " checked"?>></td>
	</tr>
	<?endif;?>
	<tr valign="top">
		<td width="40%" align="right"><font class="tablefieldtext"><?echo GetMessage('NAME')?></font></td>
		<td width="60%"><input type="text" class="inputtext" name="NAME" size="30" maxlength="50" value="<? echo $arUser["NAME"]?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('LAST_NAME')?></font></td>
		<td><input type="text" class="inputtext" name="LAST_NAME" size="30" maxlength="50" value="<? echo $arUser["LAST_NAME"]?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('SECOND_NAME')?></font></td>
		<td><input type="text" class="inputtext" name="SECOND_NAME" size="30" maxlength="50" value="<? echo $arUser["SECOND_NAME"]?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><font class="starrequired">*</font><? echo GetMessage('EMAIL')?></font></td>
		<td><input type="text" class="inputtext" name="EMAIL" size="30" maxlength="50" value="<? echo $arUser["EMAIL"]?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><font class="starrequired">*</font><?echo GetMessage('LOGIN')?></font></td>
		<td><input type="text" class="inputtext" name="LOGIN" size="30" maxlength="50" value="<? echo $arUser["LOGIN"]?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('NEW_PASSWORD')?></font></td>
		<td><input class="inputtext" type="password" name="NEW_PASSWORD" size="30" maxlength="50" value=""></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('NEW_PASSWORD_CONFIRM')?></font></td>
		<td><input class="inputtext" type="password" name="NEW_PASSWORD_CONFIRM" size="30" maxlength="50" value=""></td>
	</tr>
	<?if($MAIN_RIGHT=="R" || $MAIN_RIGHT=="W"):?>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?echo GetMessage('GROUPS');?></font></td>
		<td><font class="tablebodytext"><?
			$rsGroups = CGroup::GetList(($by="c_sort"), ($order="asc"), Array("ANONYMOUS"=>"N"));
			while($arGroup = $rsGroups->Fetch())
			{
				if ($arGroup["ID"]!=2) :
					?><input type="checkbox" name="GROUP_ID[]" value="<?echo $arGroup["ID"]?>"<?if(in_array($arGroup["ID"], $arUser["GROUP_ID"]))echo " checked"?>><?
					echo htmlspecialcharsbx($arGroup["NAME"]);
					echo "<br>";
				endif;
			}
			?></font></td>
	</tr>
	<?endif;?>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a class="tablebodylink" title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript:SectionClick('personal')"><?=GetMessage("USER_PERSONAL_INFO")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="user_div_personal" style="display:<?echo ($_COOKIE["user_div_personal"]=="Y"? "block":"none")?>">
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
				<tr valign="top">
					<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage('USER_PROFESSION')?></font></td>
					<td width="60%"><input type="text" class="inputtext" name="PERSONAL_PROFESSION" size="30" maxlength="255" value="<?=$arUser["PERSONAL_PROFESSION"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_WWW')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_WWW" size="30" maxlength="255" value="<?=$arUser["PERSONAL_WWW"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ICQ')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_ICQ" size="30" maxlength="255" value="<?=$arUser["PERSONAL_ICQ"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_GENDER')?></font></td>
					<td><?
						$arr = array(
							"reference"=>array(GetMessage("USER_MALE"),GetMessage("USER_FEMALE")), "reference_id"=>array("M","F"));
						echo SelectBoxFromArray("PERSONAL_GENDER", $arr, $arUser["PERSONAL_GENDER"], GetMessage("USER_DONT_KNOW"), "class=\"inputselect\"");
						?></td>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?echo GetMessage("USER_BIRTHDAY_DT")." (".CLang::GetDateFormat("SHORT")."):"?></font></td>
					<td><font class="tablebodytext"><?echo CalendarDate("PERSONAL_BIRTHDAY", $arUser["PERSONAL_BIRTHDAY"], "form1", "15")?></font></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_PHOTO")?></font></td>
					<td><font class="tablebodytext">
					<input type="hidden" name="PERSONAL_PHOTO_ID" value="<?=$arUser["PERSONAL_PHOTO"]?>">
					<?
					echo CFile::InputFile("PERSONAL_PHOTO", 20, $arUser["PERSONAL_PHOTO"], false, 0, "IMAGE", "class=\"inputfile\"");
					if (strlen($arUser["PERSONAL_PHOTO"])>0):
						?><br><?
						echo CFile::ShowImage($arUser["PERSONAL_PHOTO"], 150, 150, "border=0", "", true);
					endif;
					?></font></td>
				<tr valign="top">
					<td class="tablebody" colspan="2" align="center"><font class="tablebodytext"><?=GetMessage("USER_PHONES")?></font></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PHONE')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_PHONE" size="30" maxlength="255" value="<?=$arUser["PERSONAL_PHONE"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_FAX')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_FAX" size="30" maxlength="255" value="<?=$arUser["PERSONAL_FAX"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MOBILE')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_MOBILE" size="30" maxlength="255" value="<?=$arUser["PERSONAL_MOBILE"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PAGER')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_PAGER" size="30" maxlength="255" value="<?=$arUser["PERSONAL_PAGER"]?>"></td>
				</tr>
				<tr valign="top">
					<td class="tablebody" colspan="2" align="center"><font class="tablebodytext"><?=GetMessage("USER_POST_ADDRESS")?></font></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_COUNTRY')?></font></td>
					<td><?echo SelectBoxFromArray("PERSONAL_COUNTRY", GetCountryArray(), $arUser["PERSONAL_COUNTRY"], GetMessage("USER_DONT_KNOW"), "class=\"inputselect\"");?></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_STATE')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_STATE" size="30" maxlength="255" value="<?=$arUser["PERSONAL_STATE"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_CITY')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_CITY" size="30" maxlength="255" value="<?=$arUser["PERSONAL_CITY"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ZIP')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_ZIP" size="30" maxlength="255" value="<?=$arUser["PERSONAL_ZIP"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_STREET")?></font></td>
					<td><textarea name="PERSONAL_STREET" class="inputtextarea" cols="40" rows="3"><?echo $arUser["PERSONAL_STREET"]?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MAILBOX')?></font></td>
					<td><input type="text" class="inputtext" name="PERSONAL_MAILBOX" size="30" maxlength="255" value="<?=$arUser["PERSONAL_MAILBOX"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_NOTES")?></font></td>
					<td><textarea name="PERSONAL_NOTES" class="inputtextarea" cols="40" rows="5"><?echo $arUser["PERSONAL_NOTES"]?></textarea></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" class="tablebodylink" OnClick="javascript: SectionClick('work')"><?=GetMessage("USER_WORK_INFO")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="user_div_work" style="display:<?echo ($_COOKIE["user_div_work"]=="Y"? "block":"none")?>">
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
				<tr valign="top">
					<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage('USER_COMPANY')?></font></td>
					<td width="60%"><input type="text" class="inputtext" name="WORK_COMPANY" size="30" maxlength="255" value="<?=$arUser["WORK_COMPANY"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_WWW')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_WWW" size="30" maxlength="255" value="<?=$arUser["WORK_WWW"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_DEPARTMENT')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_DEPARTMENT" size="30" maxlength="255" value="<?=$arUser["WORK_DEPARTMENT"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_POSITION')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_POSITION" size="30" maxlength="255" value="<?=$arUser["WORK_POSITION"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_WORK_PROFILE")?></font></td>
					<td><textarea name="WORK_PROFILE" class="inputtextarea" cols="40" rows="5"><?echo $arUser["WORK_PROFILE"]?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_LOGO")?></font></td>
					<td><font class="tablebodytext">
					<input type="hidden" name="WORK_LOGO_ID" value="<?=$arUser["WORK_LOGO"]?>">
					<?
						echo CFile::InputFile("WORK_LOGO", 20, $arUser["WORK_LOGO"], false, 0, "IMAGE", "class=\"inputfile\"");
						if (strlen($arUser["WORK_LOGO"])>0):
							?><br><?
							echo CFile::ShowImage($arUser["WORK_LOGO"], 150, 150, "border=0", "", true);
						endif;
						?></font></td>
				</tr>
				<tr valign="top">
					<td class="tablebody" colspan="2" align="center"><font class="tablebodytext"><?=GetMessage("USER_PHONES")?></font></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PHONE')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_PHONE" size="30" maxlength="255" value="<?=$arUser["WORK_PHONE"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_FAX')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_FAX" size="30" maxlength="255" value="<?=$arUser["WORK_FAX"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_PAGER')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_PAGER" size="30" maxlength="255" value="<?=$arUser["WORK_PAGER"]?>"></td>
				</tr>
				<tr valign="top">
					<td class="tablebody" colspan="2" align="center"><font class="tablebodytext"><?=GetMessage("USER_POST_ADDRESS")?></font></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_COUNTRY')?></font></td>
					<td><?echo SelectBoxFromArray("WORK_COUNTRY", GetCountryArray(), $arUser["WORK_COUNTRY"], GetMessage("USER_DONT_KNOW"), "class=\"inputselect\"");?></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_STATE')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_STATE" size="30" maxlength="255" value="<?=$arUser["WORK_STATE"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_CITY')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_CITY" size="30" maxlength="255" value="<?=$arUser["WORK_CITY"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_ZIP')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_ZIP" size="30" maxlength="255" value="<?=$arUser["WORK_ZIP"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_STREET")?></font></td>
					<td><textarea name="WORK_STREET" class="inputtextarea" cols="40" rows="3"><?echo $arUser["WORK_STREET"]?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('USER_MAILBOX')?></font></td>
					<td><input type="text" class="inputtext" name="WORK_MAILBOX" size="30" maxlength="255" value="<?=$arUser["WORK_MAILBOX"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("USER_NOTES")?></font></td>
					<td><textarea name="WORK_NOTES" class="inputtextarea" cols="40" rows="5"><?echo $arUser["WORK_NOTES"]?></textarea></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>

	<?if (CModule::IncludeModule("forum")):?>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a class="tablebodylink" title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('forum')"><?=GetMessage("forum_INFO")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="user_div_forum" style="display:<?echo ($_COOKIE["user_div_forum"]=="Y"? "block":"none")?>">
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
				<?if ($USER->IsAdmin()):?>
					<tr valign="top">
						<td align="right"><font class="tablefieldtext"><?=GetMessage("forum_ALLOW_POST")?></font></td>
						<td><input class="inputtext" type="checkbox" name="forum_ALLOW_POST" value="Y" <?if ($arForumUser["ALLOW_POST"]=="Y") echo "checked";?>></td>
					</tr>
				<?endif;?>
				<tr valign="top">
					<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("forum_SHOW_NAME")?></font></td>
					<td width="60%"><input class="inputtext" type="checkbox" name="forum_SHOW_NAME" value="Y" <?if ($arForumUser["SHOW_NAME"]=="Y") echo "checked";?>></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('forum_DESCRIPTION')?></font></td>
					<td><input class="inputtext" type="text" name="forum_DESCRIPTION" size="30" maxlength="255" value="<?=$arForumUser["DESCRIPTION"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('forum_INTERESTS')?></font></td>
					<td><textarea class="inputtextarea" name="forum_INTERESTS" rows="3" cols="35"><?echo $arForumUser["INTERESTS"]; ?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("forum_SIGNATURE")?></font></td>
					<td><textarea class="inputtextarea" name="forum_SIGNATURE" rows="3" cols="35"><?echo $arForumUser["SIGNATURE"]; ?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("forum_AVATAR")?></font></td>
					<td><font class="tablebodytext"><?
						echo CFile::InputFile("forum_AVATAR", 20, $arForumUser["AVATAR"], false, 0, "IMAGE", "class=\"inputfile\"");
						if (strlen($arForumUser["AVATAR"])>0):
							?><br><?
							echo CFile::ShowImage($arForumUser["AVATAR"], 150, 150, "border=0", "", true);
						endif;
						?></font></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<?endif;?>
	
	<?if (CModule::IncludeModule("blog")):?>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a class="tablebodylink" title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('blog')"><?=GetMessage("blog_INFO")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="user_div_blog" style="display:<?echo ($_COOKIE["user_div_blog"]=="Y"? "block":"none")?>">
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
				<input type="hidden" name="profile_module_id[]" value="blog">
				<?if ($USER->IsAdmin()):?>
					<tr valign="top">
						<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("blog_ALLOW_POST")?></font></td>
						<td width="60%"><input type="checkbox" name="blog_ALLOW_POST" value="Y" <?if ($arBlogUser["ALLOW_POST"]=="Y") echo "checked";?>></td>
					</tr>
				<?endif;?>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('blog_ALIAS')?></font></td>
					<td><input class="typeinput" type="text" name="blog_ALIAS" size="30" maxlength="255" value="<?=$arBlogUser["ALIAS"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('blog_DESCRIPTION')?></font></td>
					<td><input class="typeinput" type="text" name="blog_DESCRIPTION" size="30" maxlength="255" value="<?=$arBlogUser["DESCRIPTION"]?>"></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage('blog_INTERESTS')?></font></td>
					<td><textarea class="typearea" name="blog_INTERESTS" rows="3" cols="35"><?echo $arBlogUser["INTERESTS"]; ?></textarea></td>
				</tr>
				<tr valign="top">
					<td align="right"><font class="tablefieldtext"><?=GetMessage("blog_AVATAR")?></font></td>
					<td><font class="tablebodytext"><?
						echo CFile::InputFile("blog_AVATAR", 20, $arBlogUser["AVATAR"]);
						if (strlen($arBlogUser["AVATAR"])>0):
							?><br><?
							echo CFile::ShowImage($arBlogUser["AVATAR"], 150, 150, "border=0", "", true);
						endif;
						?></font></td>
				</tr>
			</table>
		</div>
		</td>
	</tr>
	<?endif;?>

	<?if (CModule::IncludeModule("learning")):?>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a class="tablebodylink" title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('learning')"><?=GetMessage("learning_INFO")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="user_div_learning" style="display:<?echo ($_COOKIE["user_div_learning"]=="Y"? "block":"none")?>">
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
			<input type="hidden" name="profile_module_id[]" value="learning">
			<tr valign="top">
					<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("learning_PUBLIC_PROFILE");?>:</font></td>
					<td width="60%"><input type="checkbox" name="student_PUBLIC_PROFILE" value="Y" <?if ($arStudent["PUBLIC_PROFILE"]=="Y") echo "checked";?>></td>
			</tr>
			<tr valign="top">
				<td align="right"><font class="tablefieldtext"><?=GetMessage("learning_RESUME");?>:</font></td>
				<td><textarea class="typearea" name="student_RESUME" style="width:50%; height:200px;"><?echo $arStudent["RESUME"]; ?></textarea></td>
			</tr>

			<tr valign="top">
				<td align="right"><font class="tablefieldtext"><?=GetMessage("learning_TRANSCRIPT");?>:</font></td>
				<td><font class="tablefieldtext"><?echo $arStudent["TRANSCRIPT"]; ?></font></td>
			</tr>
			</table>
		</div>
		</td>
	</tr>
	<?endif;?>

	
	<?if($USER->IsAdmin()):?>
	<tr valign="top">
		<td class="tablebody" colspan="2"><font class="tablebodytext"><a class="tablebodylink" title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" OnClick="javascript: SectionClick('admin')"><?=GetMessage("USER_ADMIN_NOTES")?></a></font></td>
	</tr>
	<tr>
		<td colspan="2"><div id="user_div_admin" style="display:<?echo ($_COOKIE["user_div_admin"]=="Y"? "block":"none")?>">
		<table width="100%" border="0" cellspacing="1" cellpadding="3">
			<tr valign="top">
				<td align="center" colspan="2"><textarea name="ADMIN_NOTES" class="inputtextarea" cols="60" rows="10"><?echo $arUser["ADMIN_NOTES"]?></textarea></td>
			</tr>
		</table></div></td>
	</tr>
	<?endif;?>

</table>
<p align="left"><input class="inputbutton" type="submit" name="save" value="<?echo (($ID>0) ? GetMessage("MAIN_SAVE") : GetMessage("MAIN_ADD"))?>">&nbsp;&nbsp;<input class="inputbutton" type="reset" value="<?echo GetMessage('MAIN_RESET');?>"></p>
</form>