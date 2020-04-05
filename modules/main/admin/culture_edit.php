<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/culture_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

use Bitrix\Main;
use Bitrix\Main\Localization\CultureTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_PARAM"), "ICON" => "lang_edit", "TITLE" => Loc::getMessage("MAIN_PARAM_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

$errors = array();
$ID = intval($request["ID"]);
$COPY_ID = intval($request["COPY_ID"]);

if($request->isPost() && ($request["save"] <> '' || $request["apply"] <> '') && $isAdmin && check_bitrix_sessid())
{
	$arFields = array(
		"NAME" => $request['NAME'],
		"FORMAT_DATE" => $request['FORMAT_DATE'],
		"FORMAT_DATETIME" => $request['FORMAT_DATETIME'],
		"WEEK_START" => intval($request["WEEK_START"]),
		"FORMAT_NAME" => $request["FORMAT_NAME"],
		"CHARSET" => $request['CHARSET'],
		"DIRECTION" => $request['DIRECTION'],
		"CODE" => $request['CODE'],
	);

	if($ID > 0)
	{
		$result = CultureTable::update($ID, $arFields);
	}
	else
	{
		$result = CultureTable::add($arFields);
		$ID = $result->getId();
	}

	if($result->isSuccess())
	{
		if($request["save"] <> '')
			LocalRedirect(BX_ROOT."/admin/culture_admin.php?lang=".LANGUAGE_ID);
		else
			LocalRedirect(BX_ROOT."/admin/culture_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$errors = $result->getErrorMessages();
	}

}

if(empty($errors))
{
	$culture = false;
	if($ID > 0 || $COPY_ID > 0)
	{
		$cultureId = ($COPY_ID > 0? $COPY_ID : $ID);
		$culture = CultureTable::getById($cultureId)->fetch();
	}

	if($culture == false)
	{
		$weekStart = Loc::getMessage('LANG_EDIT_WEEK_START_DEFAULT');
		if($weekStart == '')
			$weekStart = 1;
		$culture = array(
			"WEEK_START" => $weekStart,
			"FORMAT_NAME" => CSite::GetDefaultNameFormat(),
		);
	}
}
else
{
	$culture = $request->getPostList()->toArray();
}

$APPLICATION->SetTitle(($ID > 0? Loc::getMessage("EDIT_LANG_TITLE") : Loc::getMessage("NEW_LANG_TITLE")));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("RECORD_LIST"),
		"LINK"	=> "/bitrix/admin/culture_admin.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("RECORD_LIST_TITLE"),
		"ICON"	=> "btn_list"
	)
);

if($ID > 0 && $isAdmin)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("MAIN_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/culture_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("MAIN_NEW_RECORD_TITLE"),
		"ICON"	=> "btn_new"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("MAIN_COPY_RECORD"),
		"LINK"	=> "/bitrix/admin/culture_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=".$ID,
		"TITLE"	=> Loc::getMessage("MAIN_COPY_RECORD_TITLE"),
		"ICON"	=> "btn_copy"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("MAIN_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage("MAIN_DELETE_RECORD_CONF"))."')) window.location='/bitrix/admin/culture_admin.php?ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action_button=delete';",
		"TITLE"	=> Loc::getMessage("MAIN_DELETE_RECORD_TITLE"),
		"ICON"	=> "btn_delete"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}

$cultureField = array();
foreach($culture as $key => $val)
	$cultureField[$key] = htmlspecialcharsbx($val);
?>
<form method="POST" action="<?= htmlspecialcharsbx($request->getRequestedPage())?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="ID" value="<?= $ID?>">
<?if($COPY_ID > 0):?><input type="hidden" name="COPY_ID" value="<?= $COPY_ID?>"><?endif?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
<?if($ID > 0):?>
	<tr>
		<td><?= Loc::getMessage('culture_id')?></td>
		<td><?= $ID?></td>
	</tr>
<?endif?>
	<tr class="adm-detail-required-field">
		<td><?= Loc::getMessage('NAME')?></td>
		<td><input type="text" name="NAME" size="30" maxlength="255" value="<?= $cultureField["NAME"]?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= Loc::getMessage('FORMAT_DATE')?></td>
		<td><input type="text" name="FORMAT_DATE" size="30" maxlength="255" value="<?= $cultureField["FORMAT_DATE"]?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= Loc::getMessage('FORMAT_DATETIME')?></td>
		<td><input type="text" name="FORMAT_DATETIME" size="30" maxlength="255" value="<?= $cultureField["FORMAT_DATETIME"]?>"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('LANG_EDIT_WEEK_START')?></td>
		<td><select name="WEEK_START">
<?
for ($i = 0; $i < 7; $i++)
{
	echo '<option value="'.$i.'"'.($i == $culture["WEEK_START"] ? ' selected="selected"' : '').'>'.Loc::getMessage('DAY_OF_WEEK_' .$i).'</option>';
}
?>
		</select></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= Loc::getMessage('FORMAT_NAME')?></td>
		<td>
			<select name="" onchange="if(this.value != ''){this.form.FORMAT_NAME.value = this.value;}">
				<option value=""><?echo Loc::getMessage("culture_edit_other")?></option>
			<?
			foreach (CSite::GetNameTemplates() as $template => $value)
			{
				echo '<option value="'.$template.'"'.($template == $culture["FORMAT_NAME"]? ' selected' : '').'>'.htmlspecialcharsex($value).'</option>'."\n";
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td></td>
		<td>
			<input type="text" name="FORMAT_NAME" size="30" maxlength="255" value="<?= $cultureField["FORMAT_NAME"]?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= Loc::getMessage('CHARSET')?></td>
		<td><input type="text" name="CHARSET" size="30" maxlength="255" value="<?= $cultureField["CHARSET"]?>">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage('DIRECTION')?></td>
		<td><select name="DIRECTION">
				<option value="Y"><?=Loc::getMessage('DIRECTION_LTR')?></option>
				<option value="N"<?if($culture["DIRECTION"] == "N") echo " selected"?>><?=Loc::getMessage('DIRECTION_RTL')?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('culture_code')?></td>
		<td><input type="text" name="CODE" size="30" maxlength="255" value="<?= $cultureField["CODE"]?>"></td>
	</tr>
<?
$tabControl->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"culture_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
