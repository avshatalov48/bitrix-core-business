/**
 * Class BX.Scale.ActionResultDialog
 */
;(function(window) {

	if (BX.Scale.ActionResultDialog) return;

	/**
	 * Class BX.Scale.ActionResultDialog
	 * @constructor
	 */
	BX.Scale.ActionResultDialog = function (params)
	{
		this.dialogWindow = null;
		this.result = params.result;
		this.actionName = params.actionName;
		this.pageRefresh = params.pageRefresh;
	};

	BX.Scale.ActionResultDialog.prototype.buildContent = function()
	{
		var result = "";

		if(this.result.ACTION_RESULT)
		{
			result = "<table class='bx-adm-scale-action-result-table'>";

			result += "<tr><th>"+BX.message("SCALE_PANEL_JS_ARD_NAME")+
				"</th><th>"+BX.message("SCALE_PANEL_JS_ARD_RESULT")+
				"</th><th>"+BX.message("SCALE_PANEL_JS_ARD_MESSAGE")+
				"</th></tr>";

			for(var actId in this.result.ACTION_RESULT)
			{
				var message = "",
					actionResult = this.result.ACTION_RESULT[actId];

				if(actionResult.ERROR)
					message += actionResult.ERROR;
				else if(actionResult.OUTPUT && actionResult.OUTPUT.DATA && actionResult.OUTPUT.DATA.message)
					message = actionResult.OUTPUT.DATA.message;

				result += "<tr>";
				result += "<td>"+this.result.ACTION_RESULT[actId].NAME+"</td>";

				if(actionResult.RESULT == "OK")
					result += "<td style='text-align: center;'><span style='color: green;'>OK</span></td>";
				else
					result += "<td style='text-align: center;'><span style='color: red;'>"+BX.message("SCALE_PANEL_JS_ERROR")+"</span></td>";

				result += "<td><pre>"+BX.util.htmlspecialchars(message)+"</pre></td>";

				result += "</tr>";
			}
			result += "</table>";
		}

		return result;
	};

	BX.Scale.ActionResultDialog.prototype.show = function()
	{
		var content = this.buildContent();
		BX.Scale.currentActionResultDialogContext = this;

		var btnClose = BX.CAdminDialog.btnClose;

		if(this.pageRefresh)
		{
			btnClose.action = function ()
			{
				this.parentWindow.Close();
				BX.Scale.AdminFrame.waitForPageRefreshing();
				window.location.reload(true);
			};
		}

		this.dialogWindow = new BX.CDialog({
			title: BX.message("SCALE_PANEL_JS_ARD_RES"),
			content: content,
			resizable: true,
			height: 500,
			width: 600,
			buttons: [ btnClose ]
		});

		this.dialogWindow.Show();
		this.dialogWindow.adjustSizeEx();
	};

})(window);
