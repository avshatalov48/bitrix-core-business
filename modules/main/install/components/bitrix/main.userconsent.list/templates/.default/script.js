;(function ()
{
	BX.namespace('BX.Main.UserConsent');

	BX.Main.UserConsent.List = function (options)
	{
		this.pathToEdit = options.pathToEdit;
		this.pathToConsentList = options.pathToConsentList;

		this.initSidePanel();
	};

	BX.Main.UserConsent.List.prototype.initSidePanel = function ()
	{
		if (!BX.SidePanel.Instance)
		{
			return;
		}

		this.pathToEdit = this.pathToEdit.split(/#|\?/);
		this.pathToConsentList = this.pathToConsentList.split(/#|\?/);

		BX.SidePanel.Instance.bindAnchors({
			rules: [
				{
					condition: [
						this.pathToEdit[0],
						this.pathToConsentList[0]
					],
					options: {
						width: 1080
					}
				},
			]
		});
	};

	BX.Main.UserConsent.List.prototype.remove = function (agreementId, uiGridId)
	{
		BX.Main.gridManager.getInstanceById(uiGridId).removeRow(agreementId.toString());
	};

})();