;(function () {
	'use strict';

	BX.namespace('BX.rest.integration');

	BX.rest.integration = {
		open: function (integrationCode, url)
		{
			if (!!url)
			{
				BX.SidePanel.Instance.open(url, {cacheable: false});
			}
			else
			{
				if (integrationCode)
				{
					if (!!this.loadProcess)
					{
						BX.UI.Notification.Center.notify(
							{
								content: BX.message('REST_INTEGRATION_LIST_OPEN_PROCESS')
							}
						);
						return false;
					}
					else
					{
						this.loadProcess = true;
					}

					BX.ajax.runComponentAction(
						'bitrix:rest.integration.edit',
						'getNewIntegrationUrl',
						{
							mode: 'class',
							data: {
								code: integrationCode
							}
						}
					).then(
						function (response)
						{
							if (!!response.data && !!response.data.url)
							{
								BX.SidePanel.Instance.open(
									response.data.url,
									{
										cacheable: false,
										requestMethod: 'post',
										requestParams: {
											'NEW_OPEN': 'Y',
										},
										events:
										{
											onLoad: function (event)
											{
												this.loadProcess = false;
											}.bind(this)
										}
									}
								);
							}
							else
							{
								if (!!response.data.helperCode && response.data.helperCode !== '')
								{
									top.BX.UI.InfoHelper.show(response.data.helperCode);
								}
								else
								{
									var errorMessage = BX.message('REST_INTEGRATION_LIST_ERROR_OPEN_URL');
									if (!!response.data.error && response.data.error.length > 0)
									{
										errorMessage = response.data.error;
									}
									BX.UI.Notification.Center.notify(
										{
											content: errorMessage
										}
									);
								}
								this.loadProcess = false;
							}
						}.bind(this)
					).catch(
						function (response)
						{
							BX.UI.Notification.Center.notify(
								{
									content: BX.message('REST_INTEGRATION_LIST_ERROR_OPEN_URL')
								}
							);
							this.loadProcess = false;
						}.bind(this)
					);
				}
			}
		}
	};
})();