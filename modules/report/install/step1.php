<?php

if(!check_bitrix_sessid())
{
	return;
}

/** @global CMain $APPLICATION */
global $APPLICATION, $errors;

if(!is_array($errors) && $errors == '' || is_array($errors) && count($errors) <= 0)
{
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
}
else
{
	$allErrors = '';
	foreach ($errors as $errorMessage)
	{
		$allErrors .= $errorMessage . "<br>";
	}

	CAdminMessage::ShowMessage(
		[
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("MOD_INST_ERR"),
			"DETAILS" => $allErrors,
			"HTML" => true,
		]
	);
}
if ($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage(
		[
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("MOD_INST_ERR"),
			"HTML" => true,
			"DETAILS" => $ex->GetString(),
		]
	);
}
?>

<form action="<?php echo $APPLICATION->GetCurPage(); ?>">
	<input type="hidden" name="lang" value="<?php
	echo LANG?>">
	<input type="submit" name="" value="<?php echo GetMessage("MOD_BACK"); ?>">
</form>