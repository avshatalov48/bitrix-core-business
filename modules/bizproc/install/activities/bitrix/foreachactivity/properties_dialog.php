<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var array $arCurrentValues */

\Bitrix\Main\UI\Extension::load(['bizproc.mixed-selector']);
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

?>
<tbody>
	<tr>
		<td align="right" width="40%" class="adm-detail-content-cell-l">
			<?= \Bitrix\Main\Localization\Loc::getMessage('BPFEA_PD_SOURCE') ?>:
		</td>
		<td width="60%" class="adm-detail-content-cell-r" id="bp_fea_mixed_selector"></td>
	</tr>
</tbody>

<script>
	BX.ready(function () {
		var currentValues = <?= CUtil::PhpToJSObject($arCurrentValues) ?>;

		var objectTabs = {
			Parameter: window.arWorkflowParameters ?? [],
			Variable: window.arWorkflowVariables ?? [],
			Constant: window.arWorkflowConstants ?? [],
			GlobalConst: window.arWorkflowGlobalConstants ?? [],
			GlobalVar: window.arWorkflowGlobalVariables ?? [],
			Document: window.arDocumentFields ?? [],
			Activity: arAllActivities ?? []
		};

		var selector = new BX.Bizproc.BpMixedSelector({
			targetNode: document.getElementById('bp_fea_mixed_selector'),
			template: [rootActivity.Serialize()],
			checkActivityChildren: false,
			activityName: '<?= CUtil::JSEscape($dialog->getActivityName())?>',
			objectTabs: objectTabs,
			inputNames: {
				object: 'object',
				field: 'variable'
			}
		});

		selector.renderMixedSelector();

		function bpForEachActivityFindActivityTitle(items, object, field)
		{
			for (var i in items)
			{
				var activityInfo = items[i];
				if (activityInfo.object === object)
				{
					var activityItems = activityInfo.items;
					for (var j in activityItems)
					{
						var itemInfo = activityItems[j];
						if (itemInfo.field === field)
						{
							return itemInfo.text;
						}
					}
				}
			}

			return null;
		}

		if (BX.Type.isStringFilled(currentValues['variable']))
		{
			var field = currentValues['variable'];
			var object = BX.Type.isStringFilled(currentValues['object']) ? currentValues['object'] : 'Variable';
			if (objectTabs[object] && objectTabs[object][field])
			{
				selector.setSelectedObjectAndField(object, field, objectTabs[object][field]['Name']);
			}
			else
			{
				var activityTabItems = selector.getMenuItemsByTabName('Activity');
				var sourceName = bpForEachActivityFindActivityTitle(activityTabItems, object, field);
				if (sourceName)
				{
					selector.setSelectedObjectAndField(object, field, sourceName);
				}
			}
		}
	});
</script>