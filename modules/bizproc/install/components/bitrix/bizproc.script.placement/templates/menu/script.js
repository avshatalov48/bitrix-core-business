;if (!BX.getClass('BX.Bizproc.ScriptPlacementMenu')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc');

	var ScriptPlacementMenu = {
		scriptList: [],
		runScript: function(scriptId, documentId)
		{
			var script = this.getScript(scriptId);

			if (!script)
			{
				alert('error');
			}
			var params = {
				moduleId: script.MODULE_ID,
				entity: script.ENTITY,
				documentType: script.DOCUMENT_TYPE,
				documentId: this.prepareDocumentId(documentId, [script.MODULE_ID, script.ENTITY, script.DOCUMENT_TYPE]),
				templateId: script.ID,
				templateName: script.NAME,
				hasParameters: (Object.keys(script.PARAMETERS).length > 0)
			};
			BX.Bizproc.Starter.singleStart(params);
		},

		onGridPanelButtonClick: function(gridId, scriptId)
		{
			var me = this;
			var gridInfo = BX.Main.gridManager.getById(gridId);
			var grid = gridInfo ? gridInfo['instance'] : null;

			if (!grid)
			{
				return false;
			}

			var selectedIds = grid.getRows().getSelectedIds();

			if (!selectedIds || !selectedIds.length)
			{
				window.alert('No items');
			}

			selectedIds.forEach(function(id)
			{
				me.runScript(scriptId, id);
			});
		},

		getScript: function(id)
		{
			for (var i = 0; i < this.scriptList.length; ++i)
			{
				if (this.scriptList[i]['ID'] == id)
				{
					return this.scriptList[i];
				}
			}
			return null;
		},

		createScript: function(fields, callback)
		{

			BX.ajax.runComponentAction('bitrix:bizproc.script.placement', 'createScript', {
				data: {
					fields: fields
				}
			}).then(function (response) {

				callback();
			});
		},

		prepareDocumentId: function(id, type)
		{
			//fix CRM document id
			if (type[0] === 'crm')
			{
				id = type[2] + '_' + id;
			}
			return id;
		}
	};

	BX.Bizproc.ScriptPlacementMenu = ScriptPlacementMenu;
})(window.BX || window.top.BX);