<?
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
if(!check_bitrix_sessid()) return;

global $errors;

if (empty($errors))
{
	CAdminMessage::ShowNote(GetMessage('MOD_UNINST_OK'));
}
else
{
	CAdminMessage::ShowMessage(
		array(
			'TYPE' => 'ERROR',
			'MESSAGE' => GetMessage('MOD_UNINST_ERR'),
			'DETAILS' => implode('<br>', $errors),
			'HTML' => true
		)
	);
}
if ($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage(
		array(
			'TYPE' => 'ERROR',
			'MESSAGE' => GetMessage('MOD_UNINST_ERR'),
			'HTML' => true,
			'DETAILS' => $ex->GetString()
		)
	);
}
?><form action="<? echo $APPLICATION->GetCurPage(); ?>">
<p>
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
	<input type="submit" name="" value="<? echo GetMessage('MOD_BACK'); ?>">
</p>
<form>