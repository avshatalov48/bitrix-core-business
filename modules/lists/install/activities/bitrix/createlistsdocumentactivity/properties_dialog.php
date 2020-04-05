<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var \CBPDocumentService $documentService */
$docType = $dialog->getMap()['DocumentType'];
?>
<tr>
	<td align="right" width="40%" valign="top">
		<span class="adm-required-field"><?=htmlspecialcharsbx($docType['Name'])?>:</span>
	</td>
	<td width="60%" id="doctype_container">
		<?=$dialog->renderFieldControl($docType, null, false, \Bitrix\Bizproc\FieldType::RENDER_MODE_DESIGNER)?>
	</td>
</tr>
<tbody id="lists_document_fields">
<?foreach ($documentFields as $fieldKey => $fieldValue):?>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l">
			<?if ($fieldValue["Required"]):?><span class="adm-required-field"><?endif;?>
			<?=htmlspecialcharsbx($fieldValue["Name"])?>:
			<?if ($fieldValue["Required"]):?></span><?endif;?>
		</td>
		<td width="60%" class="adm-detail-content-cell-r"><?=$documentService->GetFieldInputControl(
			$listsDocumentType,
			$fieldValue,
			array($dialog->getFormName(), $fieldKey),
			$dialog->getCurrentValue($fieldKey),
			true
			)?>
		</td>
	</tr>
<?endforeach;?>
</tbody>

<script>
	BX.ready(function()
	{
		var container = BX('doctype_container');
		var select = container ? container.querySelector('[name="lists_document_type"]') : null;
		if (select)
		{
			BX.bind(select, 'change', function()
				{
					var documentType = this.value;
					var container = BX('lists_document_fields');
					BX.cleanNode(container);

					if (!documentType)
					{
						return;
					}

					BX.ajax.post(
						'/bitrix/tools/bizproc_activity_ajax.php',
						{
							'site_id': BX.message('SITE_ID'),
							'sessid' : BX.bitrix_sessid(),
							'document_type' : <?=Cutil::PhpToJSObject($dialog->getDocumentType())?>,
							'activity': 'CreateListsDocumentActivity',
							'lists_document_type': documentType,
							'form_name': <?=Cutil::PhpToJSObject($dialog->getFormName())?>,
							'content_type': 'html'
						},
						function(response)
						{
							if (response)
							{
								container.innerHTML = response;
							}
						}
					);
				}
			);
		}
	});
</script>