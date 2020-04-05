<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
if (strlen($arCurrentValues["mail_charset"]) <= 0)
	$arCurrentValues["mail_charset"] = SITE_CHARSET;
if (strlen($arCurrentValues["mail_message_type"]) <= 0)
	$arCurrentValues["mail_message_type"] = "plain";

if ($arCurrentValues["mail_message_encoded"])
{
	$arCurrentValues["mail_text"] = htmlspecialcharsback($arCurrentValues["mail_text"]);
}
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$fileType = $map['FileType'];
$file = $map['File'];
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_FROM") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_user_from', $arCurrentValues['mail_user_from'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_TO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_user_to', $arCurrentValues['mail_user_to'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_SUBJECT") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_subject', $arCurrentValues['mail_subject'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPMA_PD_BODY") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'mail_text', $arCurrentValues['mail_text'], Array('rows'=> 7))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_MESS_TYPE") ?>:</td>
	<td width="60%">
		<select name="mail_message_type">
			<option value="plain"<?= $arCurrentValues["mail_message_type"] == "plain" ? " selected" : "" ?>><?= GetMessage("BPMA_PD_TEXT") ?></option>
			<option value="html"<?= $arCurrentValues["mail_message_type"] == "html" ? " selected" : "" ?>>HTML</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_CP") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_charset', $arCurrentValues['mail_charset'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_DIRRECT_MAIL") ?>:</td>
	<td width="60%">
		<input type="radio" name="dirrect_mail" value="Y" id="dirrect_mail_Y"<?= ($arCurrentValues["dirrect_mail"] != "N") ? " checked": "" ?>><label for="dirrect_mail_Y"><?= GetMessage("BPMA_PD_DIRRECT_MAIL_Y") ?></label><br />
		<input type="radio" name="dirrect_mail" value="N" id="dirrect_mail_N"<?= ($arCurrentValues["dirrect_mail"] == "N") ? " checked": "" ?>><label for="dirrect_mail_N"><?= GetMessage("BPMA_PD_DIRRECT_MAIL_N") ?></label>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPMA_PD_MAIL_SITE") ?>:</td>
	<td width="60%">
		<select name="mail_site">
			<option value="">(<?= GetMessage("BPMA_PD_MAIL_SITE_OTHER") ?>)</option>
			<?
			$bFound = false;
			$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				$bFound = ($site["LID"] == $arCurrentValues["mail_site"]);
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["mail_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<?=CBPDocument::ShowParameterField("string", 'mail_site_x', $arCurrentValues['mail_site'], Array('size'=> 20))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?=htmlspecialcharsbx($fileType['Name'])?>:</td>
	<td width="60%">
		<select name="<?=htmlspecialcharsbx($fileType['FieldName'])?>" onchange="BPMA_changeFileType(this.value)">
			<?
			$currentType = $dialog->getCurrentValue($fileType['FieldName']);
			foreach ($fileType['Options'] as $key => $value):?>
				<option value="<?=htmlspecialcharsbx($key)?>"<?= $currentType == $key ? " selected" : "" ?>>
					<?=htmlspecialcharsbx($value)?>
				</option>
			<?endforeach;?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_FILE") ?>:</td>
	<td width="60%">
		<?
		$attachmentValues = array_values(array_filter((array)$dialog->getCurrentValue($file['FieldName'])));
		$fileValues = $diskValues = array();

		if ($currentType == 'disk' && !CModule::IncludeModule('disk'))
		{
			$currentType = 'file';
		}

		if ($currentType != 'disk')
		{
			$currentType = 'file';
			$fileValues = $attachmentValues;
		}
		else
		{
			$diskValues = $attachmentValues;
		}
		?>
		<div id="BPMA-disk-control" style="<?=($currentType != 'disk')?'display:none':''?>">
			<div id="BPMA-disk-control-items"><?
				foreach ($diskValues as $fileId)
				{
					$object = \Bitrix\Disk\File::loadById($fileId);
					if ($object)
					{
						$objectId = $object->getId();
						$objectName = $object->getName();
						?>
						<div>
							<input type="hidden" name="<?=htmlspecialcharsbx($file['FieldName'])?>[]" value="<?=(int)$objectId?>"/>
							<span style="color: grey">
				<?=htmlspecialcharsbx($objectName)?>
			</span>
							<a onclick="BX.cleanNode(this.parentNode, true); return false" style="color: red; text-decoration: none; border-bottom: 1px dotted">x</a>
						</div>
						<?
					}
				}
				?>
			</div>
			<a href="#" onclick="return BPDCM_showDiskFileDialog()" style="color: black; text-decoration: none; border-bottom: 1px dotted"><?=GetMessage('BPMA_PD_FILE_SELECT')?></a>
		</div>
		<div id="BPMA-file-control" style="<?=($currentType != 'file')?'display:none':''?>">
			<?
			$file['Type'] = 'string';
			$filedType = $dialog->getFieldTypeObject($file);
			echo $filedType->renderControl(array(
				'Form' => $dialog->getFormName(),
				'Field' => $file['FieldName']
			), $fileValues, true, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER);
			?>
		</div>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_MAIL_SEPARATOR") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_separator" size="4" value="<?= htmlspecialcharsbx($arCurrentValues["mail_separator"]) ?>" />
	</td>
</tr>
<script>
	var BPMA_changeFileType = function(type)
	{
		BX.style(BX('BPMA-disk-control'), 'display', type==='disk' ? '' : 'none');
		BX.style(BX('BPMA-file-control'), 'display', type==='file' ? '' : 'none');

		var i, oldType = type==='disk' ? 'file' : 'disk';
		var disableInputs = BX('BPMA-'+oldType+'-control').querySelectorAll('input');
		for (i = 0; i < disableInputs.length; ++i)
			disableInputs[i].setAttribute('disabled', 'disabled');

		var enableInputs = BX('BPMA-'+type+'-control').querySelectorAll('input');
		for (i = 0; i < enableInputs.length; ++i)
			enableInputs[i].removeAttribute('disabled');
	};

	var BPDCM_showDiskFileDialog = function()
	{
		var urlSelect = '/bitrix/tools/disk/uf.php?action=selectFile&dialog2=Y&SITE_ID=' + BX.message('SITE_ID');
		var dialogName = 'BPMA';

		BX.ajax.get(urlSelect, 'multiselect=Y&dialogName='+dialogName,
			BX.delegate(function() {
				setTimeout(BX.delegate(function() {
					BX.DiskFileDialog.obCallback[dialogName] = {'saveButton' :function(tab, path, selected)
						{
							var i;
							for (i in selected)
							{
								if (selected.hasOwnProperty(i))
								{
									if (selected[i].type == 'file')
									{
										var div = BX.create('div',{
											html: '<input type="hidden" name="<?=htmlspecialcharsbx(CUtil::JSEscape($file['FieldName']))?>[]" value="'
											+(selected[i].id).toString().substr(1)+'"/>'
											+ '<span style="color: grey">'+BX.util.htmlspecialchars(selected[i].name)+'</span>'
											+ '<a onclick="BX.cleanNode(this.parentNode, true); return false" style="color: red; text-decoration: none; border-bottom: 1px dotted">x</a>'
										});

										BX('BPMA-disk-control-items').appendChild(div);
									}
								}
							}
						}};
					BX.DiskFileDialog.openDialog(dialogName);
				}, this), 10);
			}, this)
		);
		return false;
	};
</script>