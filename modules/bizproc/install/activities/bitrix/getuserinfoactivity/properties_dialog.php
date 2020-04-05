<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$values = $dialog->getCurrentValues();

$userParam = $map['GetUser'];

?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= $userParam['Name'] ?></span>:</td>
	<td width="60%">
		<?
		if ($user->isAdmin())
		{
			echo $dialog->renderFieldControl($userParam, null, true, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER);
		}
		else
		{
			echo $user->getFullName();
		}
		?>
	</td>
</tr>