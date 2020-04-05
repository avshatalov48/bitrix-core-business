/**
 * Class BX.Scale.ActionProcessDialog
 */
;(function(window) {

	if (BX.Scale.ActionProcessDialog) return;

	/**
	 * Class BX.Scale.ActionProcessDialog
	 * @constructor
	 */
	BX.Scale.ActionProcessDialog = {
		dialogWindow: null,
		contentTable: null,
		processingAction: false,
		pageRefresh: false,

		getContentObj: function()
		{
			if(this.contentTable === null)
			{
				var result = BX.create('table',{props:{className:"bx-adm-scale-action-result-table"}}),
					tr = BX.create('tr');

				tr.appendChild(BX.create('th',{html: BX.message("SCALE_PANEL_JS_ARD_NAME")}));
				tr.appendChild(BX.create('th',{html: BX.message("SCALE_PANEL_JS_ARD_RESULT")}));
				tr.appendChild(BX.create('th',{html: BX.message("SCALE_PANEL_JS_ARD_MESSAGE")}));
				result.appendChild(tr);
				this.contentTable = result;
			}

			return this.contentTable;
		},

		addActionProcess: function(actionName)
		{
			if(this.processingAction)
				return false;

			this.processingAction = true;

			var content = this.getContentObj(),
				tr = BX.create('tr');

			tr.appendChild(BX.create('td',{html: actionName}));

			var td = BX.create('td'),
				div = document.body.appendChild(document.createElement("DIV"));

			div.className = "bx-adm-scale-wait";
			div.style.marginLeft = '24px';

			td.appendChild(div);
			tr.appendChild(td);
			tr.appendChild(BX.create('td'));

			content.appendChild(tr);

			if(this.dialogWindow)
				this.dialogWindow.adjustSizeEx();

			return true;
		},

		setActionResult: function(result, message)
		{
			if(!this.processingAction)
				return false;

			if(message)
				this.addActionMessage(message, true);

			this.processingAction = false;
			var tr =  this.contentTable.lastChild;

			if(result)
				tr.children[1].innerHTML = "<td style='text-align: center;'><span style='color: green;'>OK</span></td>";
			else
				tr.children[1].innerHTML = "<td style='text-align: center;'><span style='color: red;'>"+BX.message("SCALE_PANEL_JS_ERROR")+"</span></td>";

			BX("close").disabled = false;

			return true;
		},

		addActionMessage: function(message, rewrite)
		{
			if(!this.processingAction)
				return false;

			var tr =  this.contentTable.lastChild;

			if(rewrite)
			{
				tr.children[2].innerHTML = message;
			}
			else
			{
				if(tr.children[2].innerHTML.length > 0)
					tr.children[2].innerHTML += "<br>";

				tr.children[2].innerHTML += message;
			}

			return true;
		},

		show: function()
		{
			var content = this.getContentObj();
			BX.Scale.currentActionProcessDialogContext = this;

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
				title: BX.message("SCALE_PANEL_JS_APD_TITLE"),
				content: content,
				resizable: false,
				height: 500,
				width: 500,
				buttons: [ btnClose ]
			});

			BX("close").disabled = true;
			this.dialogWindow.unclosable = true;
			this.dialogWindow.adjustSizeEx();
			this.dialogWindow.Show();
		}
	};

})(window);
