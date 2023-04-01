<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<?= $javascriptFunctions ?>
<script language="JavaScript">
var BPRIAParams = <?=(is_array($requestedInformation)?CUtil::PhpToJSObject($requestedInformation):'{}')?>;

function BPRIAEditForm(b)
{
	var f = document.getElementById('ria_pd_edit_form');
	var l = document.getElementById('ria_pd_list_form');

	<?=$popupWindow->jsPopup?>.btnSave.btn.disabled = b ? true : false;
	<?=$popupWindow->jsPopup?>.btnCancel.btn.disabled = b ? true : false;

	if (b)
	{
		l.style.display = 'none';
		try{
			f.style.display = 'table-row';
		}catch(e){
			f.style.display = 'inline';
		}
	}
	else
	{
		f.style.display = 'none';
		try{
			l.style.display = 'table-row';
		}catch(e){
			l.style.display = 'inline';
		}
	}
}

var currentType = null;
var lastEd = false;

function BPRIANewParam()
{
	lastEd = false;
	BPRIAEditForm(true);

	for (var i = 1; i < 10000; i++)
	{
		if (!BPRIAParams[i])
			break;
	}

	document.getElementById("id_fri_title").value = "";
	document.getElementById("id_fri_name").value = "";
	document.getElementById("id_fri_description").value = "";
	document.getElementById("id_fri_required").checked = false;
	document.getElementById("id_fri_multiple").checked = false;
	document.getElementById("id_fri_id").value = i;

	for (var t in objFields.arFieldTypes)
		break;

	window.currentType = {'Type' : t, 'Options' : null, 'Required' : 'N', 'Multiple' : 'N'};

	BPRIAChangeFieldType(window.currentType);

	document.getElementById("id_fri_type").selectedIndex = 0;
	document.getElementById("id_fri_title").focus();
}

function BPRIAToHiddens(ob, name)
{
	if (typeof ob == 'object')
	{
		var s = '';
		for (var k in ob)
		{
			s += BPRIAToHiddens(ob[k], name + '[' + encodeURIComponent(k) + ']');
		}

		return s;
	}

	return '<input type="hidden" name="' + objFields.HtmlSpecialChars(name) + '" value="' + objFields.HtmlSpecialChars(ob) + '">';
}

function BPRIAParamFillParam(id, p)
{
	var i, t = document.getElementById('ria_pd_list_table');
	for (i = 1; i < t.rows.length; i++)
	{
		if (t.rows[i].paramId == id)
		{
			var r = t.rows[i].cells;

			r[0].innerHTML = '<a href="javascript:void(0);" onclick="BPRIAParamEditParam(this);">'+HTMLEncode(p['Name'])+"</a>"
				+ BPRIAToHiddens(p, 'requested_information[' + id + ']');

			r[1].innerHTML = HTMLEncode(p['Title']);
			r[2].innerHTML = (objFields.arFieldTypes[p['Type']] ? objFields.arFieldTypes[p['Type']]['Name'] : p['Type'] );
			r[3].innerHTML = (p['Required']=="Y" ? '<?=GetMessage("BPSFA_PD_YES")?>' : '<?=GetMessage("BPSFA_PD_NO")?>');
			r[4].innerHTML = (p['Multiple']=="Y" ? '<?=GetMessage("BPSFA_PD_YES")?>' : '<?=GetMessage("BPSFA_PD_NO")?>');

			return true;
		}
	}
}

function BPRIAParamAddParam(id, p)
{
	var t = document.getElementById('ria_pd_list_table');
	var r = t.insertRow(-1);
	r.paramId = id;
	var c = r.insertCell(-1);
	c = r.insertCell(-1);
	c = r.insertCell(-1);
	c.align="center";
	c = r.insertCell(-1);
	c.align="center";
	c = r.insertCell(-1);
	c = r.insertCell(-1);
	c.innerHTML = '<a href="javascript:void(0);" onclick="moveRowUp(this); return false;"><?= GetMessage("BP_WF_UP") ?></a> | <a href="javascript:void(0);" onclick="moveRowDown(this); return false;"><?= GetMessage("BP_WF_DOWN") ?></a> | <a href="javascript:void(0);" onclick="BPRIAParamEditParam(this); return false;"><?=GetMessage("BPSFA_PD_CHANGE")?></a> | <a href="javascript:void(0);" onclick="BPRIADeleteRow(this); return false;"><?=GetMessage("BPSFA_PD_DELETE")?></a>';
	BPRIAParamFillParam(id, p);
}

function BPRIADeleteRow(ob)
{
	var id = ob.parentNode.parentNode.paramId;
	delete BPRIAParams[id];

	var i, t = document.getElementById('ria_pd_list_table');
	for (i = 1; i < t.rows.length; i++)
	{
		if (t.rows[i].paramId == id)
		{
			t.deleteRow(i);
			return;
		}
	}
}

function BPRIAParamEditParam(ob)
{
	BPRIAEditForm(true);

	window.lastEd = ob.parentNode.parentNode.paramId;

	var s = BPRIAParams[window.lastEd];

	window.currentType = {'Type' : s['Type'], 'Options' : s['Options'], 'Required' : s['Required'], 'Multiple' : s['Multiple']};

	document.getElementById("id_fri_title").value = s["Title"];
	document.getElementById("id_fri_name").value = s["Name"];
	document.getElementById("id_fri_description").value = s["Description"] || '';
	document.getElementById("id_fri_required").checked = (s["Required"] == "Y");
	document.getElementById("id_fri_multiple").checked = (s["Multiple"] == "Y");
	document.getElementById("id_fri_id").value = window.lastEd;
	document.getElementById('id_td_document_value').innerHTML = "";

	BPRIAChangeFieldType(
		window.currentType,
		s['Default']
	);

	document.getElementById("id_fri_title").focus();
}


function BPRIAChangeFieldType(type, value)
{
	BX.showWait();

	var f1 = document.getElementById("id_fri_type");
	if (f1)
	{
		for (var i = 0; i < f1.options.length; i++)
		{
			if (f1.options[i].value == type['Type'])
			{
				f1.selectedIndex = i;
				break;
			}
		}
	}

	if (typeof value == "undefined")
		value = "";

	if (objFields.arFieldTypes[type['Type']]['Complex'] == "Y")
	{
		objFields.GetFieldInputControl4Type(
			type,
			value,
			{'Field':'fri_default', 'Form':'<?= $formName ?>'},
			"BPRIASwitchSubTypeControl",
			function(v, newPromt)
			{
				if (v == undefined)
				{
					document.getElementById('id_td_document_value').innerHTML = "";
					document.getElementById('id_tr_pbria_options').style.display = 'none';
				}
				else
				{
					document.getElementById('id_tr_pbria_options').style.display = '';
					document.getElementById('id_td_fri_options').innerHTML = v;
				}

				if (newPromt.length <= 0)
					newPromt = '<?= GetMessage("BPSFA_PD_F_VLIST") ?>';
				document.getElementById('id_td_fri_options_promt').innerHTML = newPromt + ":";

				objFields.GetFieldInputControl4Subtype(
					type,
					value,
					{'Field':'fri_default', 'Form':'<?= $formName ?>'},
					function(v1)
					{
						if (v1 == undefined)
							document.getElementById('id_td_document_value').innerHTML = "";
						else
							document.getElementById('id_td_document_value').innerHTML = v1;

						BX.closeWait();
					}
				);

			}
		);
	}
	else
	{
		document.getElementById('id_td_document_value').innerHTML = "";
		document.getElementById('id_tr_pbria_options').style.display = 'none';

		objFields.GetFieldInputControl4Subtype(
			type,
			value,
			{'Field':'fri_default', 'Form':'<?= $formName ?>'},
			function(v)
			{
				if (v == undefined)
					document.getElementById('id_td_document_value').innerHTML = "";
				else
					document.getElementById('id_td_document_value').innerHTML = v;

				BX.closeWait();
			}
		);
	}
}

function BPRIASwitchTypeControl(newType)
{
	BX.showWait();

	objFields.GetFieldInputValue(
		window.currentType,
		{'Field':'fri_default', 'Form':'<?= $formName ?>'},
		function(v)
		{
			window.currentType['Type'] = newType;

			if (typeof v == "object")
				v = v[0];

			BX.closeWait();

			BPRIAChangeFieldType(window.currentType, v);
		}
	);
}

function BPRIASwitchSubTypeControl(newSubtype)
{
	BX.showWait();
	document.getElementById('dpsavebuttonform').disabled = true;
	document.getElementById('dpcancelbuttonform').disabled = true;

	objFields.GetFieldInputValue(
		window.currentType,
		{'Field':'fri_default', 'Form':'<?= $formName ?>'},
		function(v)
		{
			window.currentType['Options'] = newSubtype;

			if (typeof v == "object")
				v = v[0];

			BX.closeWait();
			document.getElementById('dpsavebuttonform').disabled = false;
			document.getElementById('dpcancelbuttonform').disabled = false;

			BPRIAChangeFieldSubtype(window.currentType, v);
		}
	);
}

function BPHide()
{

}

function BPRIAChangeFieldSubtype(type, value)
{
	BX.showWait();

	if (typeof value == "undefined")
		value = "";

	objFields.GetFieldInputControl4Subtype(
		type,
		value,
		{'Field':'fri_default', 'Form':'<?= $formName ?>'},
		function(v)
		{
			if (v == undefined)
				document.getElementById('id_td_document_value').innerHTML = "";
			else
				document.getElementById('id_td_document_value').innerHTML = v;

			BX.closeWait();
		}
	);
}

function BPRIAParamSaveForm()
{
	if (document.getElementById("id_fri_title").value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessageJS("BPSFA_PD_EMPTY_TITLE") ?>');
		document.getElementById("id_fri_title").focus();
		return;
	}
	if (document.getElementById("id_fri_name").value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessageJS("BPSFA_PD_EMPTY_NAME") ?>');
		document.getElementById("id_fri_name").focus();
		return;
	}
	if (!document.getElementById("id_fri_name").value.match(/^[A-Za-z_][A-Za-z0-9_]*$/g))
	{
		alert('<?= GetMessageJS("BPSFA_PD_WRONG_NAME") ?>');
		document.getElementById("id_fri_name").focus();
		return;
	}

	BX.showWait();

	var N = lastEd;
	if (!lastEd)
	{
		lastEd = document.getElementById("id_fri_id").value.replace(/^\s+|\s+$/g, '');
		BPRIAParams[lastEd] = {};
	}

	BPRIAParams[lastEd]['Title'] = document.getElementById("id_fri_title").value.replace(/^\s+|\s+$/g, '');
	BPRIAParams[lastEd]['Name'] = document.getElementById("id_fri_name").value.replace(/^\s+|\s+$/g, '');
	BPRIAParams[lastEd]['Description'] = document.getElementById("id_fri_description").value;
	BPRIAParams[lastEd]['Type'] = document.getElementById("id_fri_type").options[document.getElementById("id_fri_type").selectedIndex].value;
	BPRIAParams[lastEd]['Required'] = document.getElementById("id_fri_required").checked ? "Y" : "N";
	BPRIAParams[lastEd]['Multiple'] = document.getElementById("id_fri_multiple").checked ? "Y" : "N";

	BPRIAParams[lastEd]['Options'] = null;
	if (objFields.arFieldTypes[BPRIAParams[lastEd]['Type']]['Complex'] == "Y")
		BPRIAParams[lastEd]['Options'] = window.currentType['Options'];

	objFields.GetFieldInputValue(
		BPRIAParams[lastEd],
		{'Field':'fri_default', 'Form':'<?= $formName ?>'},
		function(v){
			//alert("GetFieldInputValue0a=" + v);
			if (typeof v == "object")
			{
				v = v[0];
			}

			BPRIAParams[lastEd]['Default'] = v;
			if (N === false)
				BPRIAParamAddParam(lastEd, BPRIAParams[lastEd]);
			else
				BPRIAParamFillParam(lastEd, BPRIAParams[lastEd]);

			BPRIAEditForm(false);

			BX.closeWait();
		}
	);
}

function moveRowUp(a)
{
	var row = a.parentNode.parentNode;
	if (row.previousSibling.previousSibling)
		row.parentNode.insertBefore(row, row.previousSibling);
}

function moveRowDown(a)
{
	var row = a.parentNode.parentNode;
	if (row.nextSibling)
	{
		if (row.nextSibling.nextSibling)
			row.parentNode.insertBefore(row, row.nextSibling.nextSibling);
		else
			row.parentNode.appendChild(row);
	}
}

function BPRIAStart()
{
	var id;

	for (id in BPRIAParams)
		BPRIAParamAddParam(id, BPRIAParams[id]);
}

setTimeout(BPRIAStart, 0);
</script>

<?php
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$renderName = function ($field)
{
	return isset($field['Required']) && $field['Required']
		? sprintf('<span class="adm-required-field">%s:</span>', htmlspecialcharsbx($field['Name']))
		: htmlspecialcharsbx($field['Name']) . ':';
};

$renderField = function(array $field, bool $allowSelection) use($dialog)
{
	$fieldType = $dialog->getFieldTypeObject($field);
	return $fieldType->renderControl([
		'Form' => $dialog->getFormName(),
		'Field' => $field['FieldName']
	], $dialog->getCurrentValue($field['FieldName']), $allowSelection, 0);
};
?>

<tr id="ria_pd_list_form">
	<td colspan="2">
		<table width="100%" class="adm-detail-content-table edit-table">
			<?php foreach ($dialog->getMap() as $fieldId => $field):?>
				<?php if (
					$fieldId !== 'TimeoutDurationType'
					&& (
						!isset($field['Settings'])
						|| !is_array($field['Settings'])
						|| !($field['Settings']['Hidden'] ?? false)
					)):
					?>
					<tr>
						<td align="right" width="40%" class="adm-detail-content-cell-l"><?= $renderName($field) ?></td>
						<td width="60%" class="adm-detail-content-cell-r">
							<?php
							echo $renderField($field, true);

							if($fieldId === 'TimeoutDuration')
							{
								echo $renderField($dialog->getMap()['TimeoutDurationType'], false);
								$delayMinLimit = CBPSchedulerService::getDelayMinLimit();
								if ($delayMinLimit)
								{
									printf('<p style="color: red;">* %s: %s</p>',
										GetMessage("BPSFA_PD_TIMEOUT_LIMIT"), CBPHelper::FormatTimePeriod($delayMinLimit)
									);
								}
							}
							?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			<tr>
				<td colspan="2"><br><b><?= GetMessage("BPSFA_PD_FIELDS") ?></b><br><br></td>
			</tr>
		</table>

		<table width="100%" id="ria_pd_list_table" class="internal">
			<tr class="heading">
				<td><?= GetMessage("BPSFA_PD_F_NAME") ?></td>
				<td><?= GetMessage("BPSFA_PD_F_TITLE") ?></td>
				<td><?= GetMessage("BPSFA_PD_F_TYPE") ?></td>
				<td><?= GetMessage("BPSFA_PD_F_REQ") ?></td>
				<td><?= GetMessage("BPSFA_PD_F_MULT") ?></td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<br>
		<span style="padding: 10px;" ><a href="javascript:void(0);" onclick="BPRIANewParam()"><?= GetMessage("BPSFA_PD_F_ADD") ?></a></span>
	</td>
</tr>


<tr id="ria_pd_edit_form">
	<td colspan="2">

		<table width="100%" class="adm-detail-content-table edit-table">
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<br><br><b><?= GetMessage("BPSFA_PD_FIELD") ?></b>
			</td>

		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"><span class="adm-required-field"><?= GetMessage("BPSFA_PD_F_TITLE") ?>:</span></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="text" size="50" name="fri_title" id="id_fri_title" value="">
			</td>
		</tr>
		<tr>
			<td align="right" class="adm-detail-content-cell-l" width="40%"><span class="adm-required-field"><?= GetMessage("BPSFA_PD_F_NAME") ?>:</span></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="text" size="20" name="fri_name" id="id_fri_name" value="">
			</td>
		</tr>
		<tr>
			<td align="right" class="adm-detail-content-cell-l" width="40%"><span><?= GetMessage("BPSFA_PD_F_DESCR") ?>:</span></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<textarea cols="50" rows="2" name="fri_description" id="id_fri_description"></textarea>
			</td>
		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"><span class="adm-required-field"><?= GetMessage("BPSFA_PD_F_TYPE") ?>:</span></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<select name="fri_type" id="id_fri_type" onchange="BPRIASwitchTypeControl(this.options[this.selectedIndex].value)">
					<?php
					foreach ($arFieldTypes as $k => $v)
					{
						?><option value="<?= $k ?>"><?= $v["Name"] ?></option><?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="id_tr_pbria_options" style="display:none">
			<td align="right" class="adm-detail-content-cell-l" width="40%" valign="top" id="id_td_fri_options_promt"><?= GetMessage("BPSFA_PD_F_VLIST") ?>:</td>
			<td width="60%" id="id_td_fri_options" class="adm-detail-content-cell-r">

			</td>
		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPSFA_PD_F_DEF") ?>:</td>
			<td width="60%" id="id_td_document_value" class="adm-detail-content-cell-r">

			</td>
		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPSFA_PD_F_REQ") ?>:</td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="checkbox" name="fri_required" id="id_fri_required" value="Y">
			</td>
		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"><?= GetMessage("BPSFA_PD_F_MULT") ?>:</td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="checkbox" name="fri_multiple" id="id_fri_multiple" value="Y">
			</td>
		</tr>
		<tr>
			<td align="right" width="40%" class="adm-detail-content-cell-l"></td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="hidden" name="fri_id" id="id_fri_id">
				<input type="button" value="<?= GetMessage("BPSFA_PD_SAVE") ?>" onclick="BPRIAParamSaveForm()" id="dpsavebuttonform" title="<?= GetMessage("BPSFA_PD_SAVE_HINT") ?>" />
				<input type="button" value="<?= GetMessage("BPSFA_PD_CANCEL") ?>" onclick="BPRIAEditForm(false);" id="dpcancelbuttonform" title="<?= GetMessage("BPSFA_PD_CANCEL_HINT") ?>" />
			</td>
		</tr>
	</table>

	</td>
</tr>
<script>
document.getElementById('ria_pd_edit_form').style.display = 'none';
try{
	document.getElementById('ria_pd_list_form').style.display = 'table-row';
}catch(e){
	document.getElementById('ria_pd_list_form').style.display = 'inline';
}
</script>