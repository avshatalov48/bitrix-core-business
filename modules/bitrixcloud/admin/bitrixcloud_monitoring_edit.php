<?
define("ADMIN_MODULE_NAME", "bitrixcloud");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
/* @global CMain $APPLICATION */
/* @global CUser $USER */
if (!$USER->CanDoOperation("bitrixcloud_monitoring") || !CModule::IncludeModule("bitrixcloud"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strError = "";
$bVarsFromForm = false;
$APPLICATION->SetTitle(GetMessage("BCL_MONITORING_TITLE"));
$monitoring = CBitrixCloudMonitoring::getInstance();

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("BCL_MONITORING_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("BCL_MONITORING_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

try
{
	if (
		$_SERVER["REQUEST_METHOD"] === "POST"
		&& check_bitrix_sessid()
	)
	{
		$result = $monitoring->startMonitoring(
			$_REQUEST["domain"],
			$_REQUEST["IS_HTTPS"]==="Y",
			LANGUAGE_ID,//$_REQUEST["LANG"],
			$_REQUEST["EMAILS"],
			$_REQUEST["TESTS"]
		);

		if ($result != "")
		{
			$bVarsFromForm = true;
			throw new CBitrixCloudException($result);
		}

		if ($apply != "")
			LocalRedirect("/bitrix/admin/bitrixcloud_monitoring_edit.php?lang=".LANGUAGE_ID."&domain=".urlencode($_REQUEST["domain"]));
		else
			LocalRedirect("/bitrix/admin/bitrixcloud_monitoring_admin.php?lang=".LANGUAGE_ID);
	}

	$arResult = $monitoring->getList();
	if (is_string($arResult))
	{
		throw new CBitrixCloudException($arResult);
		$arResult = array();
	}
}
catch (Exception $e)
{
	$strError = $e->getMessage();
	$arResult = array();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($strError)
	CAdminMessage::ShowMessage($strError);

if ($bVarsFromForm)
{
	$aDomain = array(
		"DOMAIN" => $_REQUEST["domain"],
		"IS_HTTPS" => $_REQUEST["IS_HTTPS"] === "Y"? "Y": "N",
		"LANG" => $_REQUEST["LANG"],
		"EMAILS" => is_array($_REQUEST["EMAIL"])? $_REQUEST["EMAIL"]: array(),
		"TESTS" => is_array($_REQUEST["TESTS"])? $_REQUEST["TESTS"]: array(),
	);
}
else
{
	$aDomain = array(
		"DOMAIN" => $_REQUEST["domain"],
		"IS_HTTPS" => "N",
		"LANG" => LANGUAGE_ID,
		"EMAILS" => array(
			COption::GetOptionString("main", "email_from", ""),
		),
		"TESTS" => array(
			"test_lic",
			"test_domain_registration",
			"test_http_response_time",
		),
	);
	foreach ($arResult as $arRes)
	{
		if ($arRes["DOMAIN"] === $aDomain["DOMAIN"])
		{
			$aDomain = $arRes;
			break;
		}
	}
}
?>

<form method="POST" action="<?echo htmlspecialcharsbx($APPLICATION->GetCurPageParam())?>"  enctype="multipart/form-data" name="editform" id="editform">
<?
$converter = CBXPunycode::GetConverter();
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("BCL_MONITORING_DOMAIN")?>:</td>
		<td><?echo htmlspecialcharsEx($converter->Decode($aDomain["DOMAIN"]))?></td>
	</tr>
	<tr>
		<td><label for="TEST_HTTP_RESPONSE_TIME"><?echo GetMessage("BCL_MONITORING_TEST_HTTP_RESPONSE_TIME")?>:</label></td>
		<td><input type="checkbox" id="TEST_HTTP_RESPONSE_TIME" name="TESTS[]" value="test_http_response_time" <?if (in_array("test_http_response_time", $aDomain["TESTS"])) echo 'checked="checked"';?>></td>
	</tr>
	<tr>
		<td><label for="TEST_DOMAIN_REGISTRATION"><?echo GetMessage("BCL_MONITORING_TEST_DOMAIN_REGISTRATION")?>:</label></td>
		<td><input type="checkbox" id="TEST_DOMAIN_REGISTRATION" name="TESTS[]" value="test_domain_registration" <?if (in_array("test_domain_registration", $aDomain["TESTS"])) echo 'checked="checked"';?>></td>
	</tr>
	<tr>
		<td><label for="TEST_LICENSE"><?echo GetMessage("BCL_MONITORING_TEST_LICENSE")?>:</label></td>
		<td><input type="checkbox" id="TEST_LICENSE" name="TESTS[]" value="test_lic" <?if (in_array("test_lic", $aDomain["TESTS"])) echo 'checked="checked"';?>></td>
	</tr>
	<tr>
		<td><label for="IS_HTTPS"><?echo GetMessage("BCL_MONITORING_IS_HTTPS")?>:</label></td>
		<td>
			<input type="checkbox" id="IS_HTTPS" name="IS_HTTPS" value="Y"
				<?
				if ($aDomain["IS_HTTPS"] === "Y")
					echo 'checked="checked"';
				?>
				onclick="ssl_changed(this)"
				/>
		</td>
	</tr>
	<tr id="ssl" <?if ($aDomain["IS_HTTPS"] !== "Y") echo 'style="display:none"';?>>
		<td><label for="TEST_SSL_CERT_VALIDITY"><?echo GetMessage("BCL_MONITORING_TEST_SSL_CERT_VALIDITY")?>:</label></td>
		<td><input type="checkbox" id="TEST_SSL_CERT_VALIDITY" name="TESTS[]" value="test_ssl_cert_validity" <?if (in_array("test_ssl_cert_validity", $aDomain["TESTS"])) echo 'checked="checked"';?>></td>
	</tr>
	<!--<tr>
		<td width="40%"><?echo GetMessage("BCL_MONITORING_LANGUAGE")?>:</td>
		<td width="60%"><?echo CLanguage::SelectBox("LANG", $aDomain["LANG"]);?></td>
	</tr>-->
	<tr class="adm-detail-required-field adm-detail-valign-top">
		<td width="40%"><?echo GetMessage("BCL_MONITORING_EMAIL")?>:</td>
		<td width="60%" id="EMAILS"><?
			foreach ($aDomain["EMAILS"] as $email)
			{
			?><input type="text" name="EMAILS[]" size="45" value="<?echo htmlspecialcharsbx($email)?>"><br><?
			}
		?>
		</td>
	</tr>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%">
			<input type="button" value="<?echo GetMessage("BCL_MONITORING_ADD_BTN")?>" onclick="add_email()">
		</td>
	</tr>
<?$tabControl->Buttons(array(
	"disabled" => false,
	"back_url" => "bitrixcloud_monitoring_admin.php?lang=".LANGUAGE_ID,
));?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<?
$tabControl->End();
?>
</form>
<script>
	function add_email()
	{
		//TODO: 5 max
		BX('EMAILS').appendChild(
			BX.create('input', {
				props: {
					name: 'EMAILS[]'
				},
				attrs: {
					type: 'text',
					size: '45'
				}
			})
		);
		BX('EMAILS').appendChild(
			BX.create('br')
		);
	}
	function ssl_changed(ckbox)
	{
		if (ckbox.checked)
			BX('ssl').style.display = 'table-row';
		else
			BX('ssl').style.display = 'none';
	}
</script>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>