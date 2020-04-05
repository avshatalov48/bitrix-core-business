<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

if(!$USER->CanDoOperation('install_updates'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


IncludeModuleLangFile(__FILE__);

$errorMessage = "";

$id = trim($_REQUEST["id"]);

$arModules = CUpdateClientPartner::SearchModulesEx(
	array("ID" => "ASC"),
	array("ID" => $id),
	1,
	LANG,
	$errorMessage
);

$arModule = null;
if (is_array($arModules["MODULE"]))
{
	foreach ($arModules["MODULE"] as $module)
		$arModule = $module["@"];
}

if ($arModule == null)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$APPLICATION->SetTitle(GetMessage("USMP_NO_MODULE"));
	CAdminMessage::ShowMessage(GetMessage("USMP_NO_MODULE").". ");
}
else
{
	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "load" && check_bitrix_sessid())
	{
		if (CUpdateClientPartner::LoadModuleNoDemand($arModule["ID"], $errorMessage, "Y", false))
			LocalRedirect("/bitrix/admin/module_admin.php?lang=".LANG."&id=".$arModule["ID"]."&".bitrix_sessid_get()."&install=Y");
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aMenu = array(
		array(
			"TEXT" => GetMessage("USMP_BACK"),
			"LINK" => "update_system_market.php?lang=".LANG."&".GetFilterParams("filter_", false),
			"ICON" => "btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	$APPLICATION->SetTitle(str_replace("#ID#", $arModule["ID"], GetMessage("USMP_TITLE")));

	$arCurrentModules = CUpdateClientPartner::GetCurrentModules($errorMessage);

	if (strlen($errorMessage) > 0)
		CAdminMessage::ShowMessage($errorMessage);
	?>
	<form method="post" name="task_form1" action="update_system_market_detail.php">
		<input type="hidden" name="action" value="load">
		<input type="hidden" name="id" value="<?= $arModule["ID"] ?>">
		<?= bitrix_sessid_post() ?>
		<?

		$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("USMP_TAB_1"), "ICON" => "", "TITLE" => str_replace("#NAME#", $arModule["NAME"], GetMessage("USMP_TAB_2")))
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_ID") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["ID"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_LOAD") ?>:</td>
				<td width="60%" valign="top"><?= array_key_exists($arModule["ID"], $arCurrentModules) ? GetMessage("USMP_YES") : GetMessage("USMP_NO") ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_NAME") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["NAME"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_DESCR") ?>:</td>
				<td width="60%" valign="top"><?= nl2br($arModule["DESCRIPTION"]) ?></td>
			</tr>
			<?if (strlen($arModule["IMAGE"]) > 0):?>
				<tr>
					<td align="right" valign="top" width="40%"><?= GetMessage("USMP_IMAGE") ?>:</td>
					<td width="60%" valign="top">
						<img src="<?= $arModule["IMAGE"] ?>" width="<?= $arModule["IMAGE_WIDTH"] ?>" height="<?= $arModule["IMAGE_HEIGHT"] ?>">
					</td>
				</tr>
			<?endif;?>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_PARTNER") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["PARTNER"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_DATE_UPDATE") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["DATE_UPDATE"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_DATE_CREATE") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["DATE_CREATE"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_CATEGORY") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["CATEGORY"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("USMP_TYPE") ?>:</td>
				<td width="60%" valign="top"><?= $arModule["TYPE"] ?></td>
			</tr>
		<?
		$tabControl->Buttons();
		?>
			<input type="submit" name="laction" value="<?= GetMessage("USMP_DO_LOAD") ?>"<?= array_key_exists($arModule["ID"], $arCurrentModules) ? " disabled" : "" ?>/>
			<input type="button" name="caction" value="<?= GetMessage("USMP_DO_CANCEL") ?>" onclick="window.location='update_system_market.php?lang=<?= LANG ?>&<?= GetFilterParams("filter_", false) ?>'"/>
		<?
		$tabControl->End();
		?>
	</form>
	<?
}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
