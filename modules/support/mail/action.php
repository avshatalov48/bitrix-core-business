<?
IncludeModuleLangFile(__FILE__);
if(CModule::IncludeModule("support")):
?>
<tr valign="top">
	<td><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED")?></td>
	<td valign="top">
		<div class="adm-list adm-list-radio">
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="radio" name="W_SUPPORT_USER_FIND" value="Y" <?if($W_SUPPORT_USER_FIND!="N") echo "checked"?> id="W_SUPPORT_USER_FIND_1"></div>
				<div class="adm-list-label"><label for="W_SUPPORT_USER_FIND_1"><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED_Y")?></label></div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="radio" name="W_SUPPORT_USER_FIND" value="N" <?if($W_SUPPORT_USER_FIND=="N") echo "checked"?> id="W_SUPPORT_USER_FIND_2"></div>
				<div class="adm-list-label"><label for="W_SUPPORT_USER_FIND_2"><?echo GetMessage("SUPPORT_MAIL_DEF_REGISTERED_N")?></label></div>
			</div>
		</div>
	</td>
</tr>

<tr valign="top">
	<td valign="top"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_TICKET")?></td>
	<td valign="top" nowrap>

		<div class="adm-list adm-list-radio">
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="radio" name="W_SUPPORT_SEC" value="email" <?if($W_SUPPORT_SEC!="all" && $W_SUPPORT_SEC!="domain") echo "checked"?> id="w_support_sec_1"></div>
				<div class="adm-list-label"><label for="w_support_sec_1"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_EMAIL")?></label></div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="radio" name="W_SUPPORT_SEC" value="domain" <?if($W_SUPPORT_SEC=="domain") echo "checked"?> id="w_support_sec_2"></div>
				<div class="adm-list-label"><label for="w_support_sec_2"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_DOMAIN")?></label></div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="radio" name="W_SUPPORT_SEC" value="all"<?if($W_SUPPORT_SEC=="all")echo " checked"?> id="w_support_sec_3"></div>
				<div class="adm-list-label"><label for="w_support_sec_3"><?echo GetMessage("SUPPORT_MAIL_ADD_TO_OPENED_T_ANY")?></label></div>
			</div>
		</div>
		<br>
		<div class="adm-list">
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="W_SUPPORT_ADD_MESSAGE_AS_HIDDEN" value="Y" <?if($W_SUPPORT_ADD_MESSAGE_AS_HIDDEN=="Y") echo "checked"?> id="w_support_add_message_as_hidden"></div>
				<div class="adm-list-label"><label for="w_support_add_message_as_hidden"><?echo GetMessage("SUPPORT_MAIL_HIDDEN")?></label></div>
			</div>
		</div>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?echo GetMessage("SUPPORT_MAIL_SUBJECT_TEMPLATE")?><br>
	<?echo GetMessage("SUPPORT_MAIL_SUBJECT_TEMPLATE_NOTES")?></td>
</tr>

<tr valign="top">
	<td colspan="2" align="center">
	<?
	if(!isset($W_SUPPORT_SUBJECT))
	{
		$w_subject = "";
		$arrTemplate = array();
		$db_res = CEventMessage::GetList('', '', Array("ACTIVE" => "Y", "EVENT_NAME"=>"TICKET_NEW_FOR_AUTHOR || TICKET_NEW_FOR_TECHSUPPORT || TICKET_CHANGE_FOR_TECHSUPPORT || TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR || TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR", "LID"=>$MAILBOX_LID));
		while($ar_res = $db_res->Fetch()) $arrTemplate[] = $ar_res["SUBJECT"];
		$arrTemplate = array_unique($arrTemplate);
		if (is_array($arrTemplate) && count($arrTemplate)>0)
		{
			foreach ($arrTemplate as $subject)
			{
				$subject = preg_quote($subject, "/");
				$subject = str_replace("#ID#", "([0-9]+)", $subject);
				$subject = preg_replace("/#[-A-Z_0-9]+#/i".BX_UTF_PCRE_MODIFIER, ".*?", $subject);
				$w_subject .= $subject."\r\n";
			}
			$W_SUPPORT_SUBJECT = $w_subject;
		}
	}
	?>
	<textarea name="W_SUPPORT_SUBJECT" style="width:80%;height:200px;" wrap="off"><?=htmlspecialcharsbx($W_SUPPORT_SUBJECT)?></textarea></td>
</tr>
<?
$arrSiteRef = array();
$arrSiteID = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch()) 
{
	$arrSiteRef[] = "[".$ar["ID"]."] ".$ar["NAME"];
	$arrSiteID[] = $ar["ID"];
}
?>
<tr>
	<td><?echo GetMessage("SUPPORT_MAIL_CONNECT_TICKET_WITH_SITE")?></td>
	<td><?
	echo SelectBoxFromArray("W_SUPPORT_SITE_ID", array("reference" => $arrSiteRef, "reference_id" => $arrSiteID), htmlspecialcharsbx($W_SUPPORT_SITE_ID), GetMessage("SUPPORT_MAIL_MAILBOX"), "onChange=\"DictionaryList(this[this.selectedIndex].value)\" ");?></td>
</tr>

<tr>
	<td><?echo GetMessage("SUPPORT_MAIL_ADD_TO_CATEGORY")?></td>
	<td valign="top">
	<?=SelectBox("W_SUPPORT_CATEGORY", CTicket::GetRefBookValues("C", $W_SUPPORT_SITE_ID), " ", $W_SUPPORT_CATEGORY);?>
	</td>
</tr>

<tr>
	<td><?echo GetMessage("SUPPORT_MAIL_ADD_WITH_CRITICALITY")?></td>
	<td valign="top">
	<?=SelectBox("W_SUPPORT_CRITICALITY", CTicket::GetRefBookValues("K", $W_SUPPORT_SITE_ID), " ", $W_SUPPORT_CRITICALITY);?>
	</td>
</tr>
<script type="text/javascript">
<!--
var arCriticality = Array();
var arCategory = Array();
	<?
	if (is_array($arrSiteID)):
		reset($arrSiteID);
		foreach($arrSiteID as $sid):
		?>
			arCriticality["<?=$sid?>"]=Array(<?
				$rs = CTicket::GetRefBookValues("K", $sid);
				echo "Array('NOT_REF', ' ')";
				while($ar=$rs->Fetch()) echo ", Array('".AddSlashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".AddSlashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
				?>);
			arCategory["<?=$sid?>"]=Array(<?
				$rs = CTicket::GetRefBookValues("C", $sid);
				echo "Array('NOT_REF', ' ')";
				while($ar=$rs->Fetch()) echo ", Array('".AddSlashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".AddSlashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
				?>);
		<?
		endforeach;
	endif;
	?>
	function DictionaryList(site_id)
	{		
		var select_index;
		var arrList = Array();
		var arrValues = Array();
		var arrInit = Array();

		arrList[arrList.length] = document.form1.W_SUPPORT_CRITICALITY;
		arrValues[arrValues.length] = arCriticality;
		arrInit[arrInit.length] = parseInt('<?=$W_SUPPORT_CRITICALITY?>');

		arrList[arrList.length] = document.form1.W_SUPPORT_CATEGORY;
		arrValues[arrValues.length] = arCategory;
		arrInit[arrInit.length] = parseInt('<?=$W_SUPPORT_CATEGORY?>');

		for(i=0; i<arrList.length; i++)
		{
			arList = arrList[i];
			arValues = arrValues[i][site_id];
			select_index = 0;
			while(arList.length>0) arList.options[0]=null;
			for(j=0; j<arValues.length; j++)
			{
				newoption = new Option(arValues[j][1], arValues[j][0], false, false);
				arList.options[j] = newoption;
				if (newoption.value==arrInit[i]) select_index = j;
			}
			if (parseInt(select_index)>0) arList.selectedIndex = parseInt(select_index);
		}
	}
//-->
</script>

<?endif?>