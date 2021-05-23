BX.ready(
	function () {
		BX.namespace('BX.rest.integration');
		if (BX.rest.integration.grid)
		{
			return;
		}

		var Grid = function () {};

		Grid.prototype =
		{
			init: function (options) {
				this.gridId = options.gridId;
				// todo delete this hack
				// it is here to prevent grid's title changing after filter apply
				if(window !== window.top && BX.type.isFunction(top.BX.ajax.UpdatePageData))
				{
					top.BX.ajax.UpdatePageData = (function() {});
				}
			},
			reloadData: function () {
				if (restIntegrationGridComponent.gridId.length > 0)
				{
					var reloadParams = {apply_filter: 'Y'};
					var gridObject = BX.Main.gridManager.getById(restIntegrationGridComponent.gridId);
					if (gridObject.hasOwnProperty('instance'))
					{
						gridObject.instance.reloadTable('POST', reloadParams);
					}
				}
			},
			delete: function (id, code) {
				BX.ajax.runComponentAction(
					'bitrix:rest.integration.grid',
					'delete',
					{
						mode: 'class',
						signedParameters: restIntegrationGridComponent.signetParameters,
						data:
							{
								id: id
							},
						analyticsLabel:
							{
								type: 'integrationDelete',
								integrationCode: code
							}
					}
				).then(
					function (response)
					{
						if (!!response.data && !!response.data.result)
						{
							if (response.data.result === 'success')
							{
								BX.rest.integration.grid.reloadData();
							}
							else if (!!response.data.errors)
							{
								var key;
								for(key in response.data.errors)
								{
									BX.UI.Notification.Center.notify(
										{
											content: response.data.errors[key]
										}
									);
								}
							}
						}
					}
				);
			}
		};
		BX.rest.integration.grid = new Grid();
	}
);
