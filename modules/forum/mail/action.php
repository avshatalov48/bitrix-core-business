<?
IncludeModuleLangFile(__FILE__);
if(CModule::IncludeModule("socialnetwork")):
?>
<tr valign="top">
	<td colspan="2" align="center">
	<table class="internal">
		<tr>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_WG")?></td>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_EMAIL")?></td>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_FILTER")?></td>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_SHOW_AUTHOR")?></td>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_USE_TOPIC")?></td>
			<td><?=GetMessage("FORUM_MAIL_COLUMN_SUF")?></td>
		</tr>
		<?
		$dblist = CForumEMail::GetMailFilters($ID);
		while($arFF=$dblist->GetNext()):?>
		<tr>
			<td><?=$arFF['SOCNET_NAME']?> <?=$arFF['SOCNET_GROUP_ID']?></td>
			<td><?=$arFF['EMAIL']?></td>
			<td><?=$arFF['EMAIL_GROUP']?></td>
			<td><?=$arFF['USE_EMAIL']?></td>
			<td><?=$arFF['USE_SUBJECT']?></td>
			<td><?=$arFF['SUBJECT_SUF']?></td>
		</tr>
		<?endwhile;?>
	</table>
		</td>
</tr>
<?endif?>