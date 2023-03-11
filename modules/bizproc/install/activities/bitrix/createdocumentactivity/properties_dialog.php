<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<?
$runtime = CBPRuntime::GetRuntime();
$documentService = $runtime->GetService("DocumentService");
foreach ($arDocumentFields as $fieldKey => $fieldValue)
{
	?>
	<tr>
		<td align="right" width="40%" valign="top"><?= (!empty($fieldValue["Required"])) ? "<span class=\"adm-required-field\">".htmlspecialcharsbx($fieldValue["Name"]).":</span>" : htmlspecialcharsbx($fieldValue["Name"]).":" ?></td>
		<td width="60%" id="td_<?= htmlspecialcharsbx($fieldKey) ?>">
			<?
			echo $documentService->GetFieldInputControl(
				$documentType,
				$fieldValue,
				array($formName, $fieldKey),
				$arCurrentValues[$fieldKey] ?? null,
				true,
				false
			);
			?>
		</td>
	</tr>
	<?
}
?>