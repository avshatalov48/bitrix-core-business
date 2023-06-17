<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_OWNER_ID") ?>:</span></td>
	<td width="60%">
		<?
		if ($user->isAdmin())
		{
			echo CBPDocument::ShowParameterField("user", 'owner_id', $arCurrentValues['owner_id'], Array('rows'=> 1));
		}
		else
		{
			echo $user->getFullName();
		}
		?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_USERS_TO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'users_to', $arCurrentValues['users_to'], Array('rows'=> 2))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class=""><?= GetMessage("SNBPA_PD_POST_TITLE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'post_title', $arCurrentValues['post_title'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_POST_MESSAGE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'post_message', $arCurrentValues['post_message'], ['rows'=> 7, 'cols' => 40])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("SNBPA_PD_POST_SITE") ?>:</td>
	<td width="60%">
		<select name="post_site">
			<option value="">(<?= GetMessage("SNBPA_PD_POST_SITE_OTHER") ?>)</option>
			<?
			$expression = CBPDocument::IsExpression($arCurrentValues["post_site"]) ? htmlspecialcharsbx($arCurrentValues["post_site"]) : '';
			$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["post_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<?=CBPDocument::ShowParameterField("string", 'post_site_x', $expression, Array('size'=> 30))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?=htmlspecialcharsbx($map['AttachmentType']['Name'])?>:</td>
	<td width="60%">
		<select name="<?=htmlspecialcharsbx($map['AttachmentType']['FieldName'])?>" onchange="BPMA_changeFileType(this.value)">
			<?php
			$currentType = $dialog->getCurrentValue($map['AttachmentType']['FieldName']);
			foreach ($map['AttachmentType']['Options'] as $key => $value):?>
				<option value="<?=htmlspecialcharsbx($key)?>"<?= $currentType == $key ? " selected" : "" ?>>
					<?=htmlspecialcharsbx($value)?>
				</option>
			<?endforeach;?>
		</select>
	</td>
</tr>
<?php if (isset($map['Attachment'], $map['AttachmentType'])): ?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= htmlspecialcharsbx($map['Attachment']['Name']) ?>:</span></td>
	<td width="60%">
		<?php
		$file = $map['Attachment'];

		$attachmentValues = array_values(array_filter((array)$dialog->getCurrentValue($file['FieldName'])));
		$fileValues = [];
		$diskValues = [];

		if ($currentType === 'disk' && !CModule::IncludeModule('disk'))
		{
			$currentType = 'file';
		}

		if ($currentType !== 'disk')
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
							<input type="hidden" name="<?=htmlspecialcharsbx($map['Attachment']['FieldName'])?>[]" value="<?=(int)$objectId?>"/>
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
			<a href="#" onclick="return BPDCM_showDiskFileDialog()" style="color: black; text-decoration: none; border-bottom: 1px dotted"><?= htmlspecialcharsbx(GetMessage('SNBPA_PD_CHOOSE_ATTACHMENT')) ?></a>
		</div>
		<div id="BPMA-file-control" style="<?=($currentType != 'file')?'display:none':''?>">
			<?php
			$file = $map['Attachment'];
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
<?php endif; ?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= htmlspecialcharsbx($map['Tags']['Name']) ?>:</span></td>
	<td width="60%">
		<?= $dialog->renderFieldControl($map['Tags'], null, true, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER) ?>
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