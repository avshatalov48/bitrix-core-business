/**
 * Class BX.Scale.Provider
 */
;(function(window) {

	if (BX.Scale.Provider) return;

	BX.Scale.Provider = {

		dialogChooseWindow: null,
		dialogConfigsWindow: null,

		getList: function()
		{

			if(this.dialogChooseWindow !== null)
			{
				if(this.dialogChooseWindow === "manual")
					BX.Scale.Provider.startManual();
				else
					this.dialogChooseWindow.Show();

				return;
			}

			var sendParams = {
				operation: "get_providers_list"
			};

			var callbacks = {
				onsuccess: function(result) {
					if(result && result["RESULT"] == "OK" && result["PROVIDERS_LIST"])
					{
						var providersCount = 0;

						for(var i in result["PROVIDERS_LIST"])
							providersCount++;

						if(providersCount > 0)
							BX.Scale.Provider.showChooseWindow(result["PROVIDERS_LIST"]);
						else
						{
							BX.Scale.Provider.dialogChooseWindow = "manual";
							BX.Scale.Provider.startManual();
						}
					}
					else
					{
						BX.Scale.AdminFrame.alert(BX.message("SCALE_PANEL_JS_PROVIDER_LIST_ERROR"), BX.message("SCALE_PANEL_JS_PROVIDER_ERROR"));
					}
				},
				onfailure: function(type, e) {
					BX.debug({type: type, error: e});
				}
			};

			BX.Scale.Communicator.sendRequest(sendParams, callbacks, this, false);

			return true;
		},

		startManual: function()
		{
			BX.Scale.actionsCollection.getObject('NEW_SERVER_CHAIN').start('',{ HOSTNAME: BX.Scale.AdminFrame.getNewServerName()});
		},

		/**
		 * @param {object} providers
		 * @returns {boolean}
		 */
		showChooseWindow: function(providers)
		{
			var content ='<table><tr><td>'+BX.message("SCALE_PANEL_JS_PROVIDER")+':</td><td>'+
				'<select id="provider_choose_select" name="provider_choose_select">';
			content += '<option value="manual">'+BX.message("SCALE_PANEL_JS_PROVIDER_MANUAL")+'</option>';

			for(var i in providers)
				content += '<option value="'+i+'">'+i+'</option>';

			content += '</select></td></tr></table>';


			this.dialogChooseWindow = new BX.CDialog({
				title: BX.message("SCALE_PANEL_JS_PROVIDER_CHOOSE"),
				content: content,
				resizable: false,
				height: 300,
				width: 500,
				buttons: [{
					title: BX.message("SCALE_PANEL_JS_PROVIDER_BUT_CHOOSE"),
					id: "admin_frame_choose_button",
					name: "admin_frame_choose_button",
					className: 'adm-btn-save',
					action: function(){

						var providerId = BX("provider_choose_select").value;

						this.parentWindow.Close();

						if(providerId == 'manual')
							BX.Scale.Provider.startManual();
						else
							BX.Scale.Provider.getConfigs(providerId);

					}
				}, BX.CAdminDialog.btnCancel]

			});

			this.dialogChooseWindow.adjustSizeEx();
			this.dialogChooseWindow.Show();

			return true;
		},

		getConfigs: function(providerId)
		{
			var sendParams = {
				operation: "get_provider_configs",
				providerId: providerId
			};

			var callbacks = {
				onsuccess: function(result) {
					if(result && result["RESULT"] == "OK" && result["PROVIDER_CONFIGS"])
					{
						BX.Scale.Provider.showConfigsWindow(providerId, result["PROVIDER_CONFIGS"]);
					}
					else
					{
						BX.Scale.AdminFrame.alert(BX.message("SCALE_PANEL_JS_PROVIDER_CONFIGS_ERROR"), BX.message("SCALE_PANEL_JS_PROVIDER_ERROR"));
					}
				},
				onfailure: function(type, e) {
					BX.debug({type: type, error: e});
				}
			};

			BX.Scale.Communicator.sendRequest(sendParams, callbacks, this, false);

			return true;
		},

		/**
		 * @param {string} providerId
		 * @param {object} configs
		 * @returns {boolean}
		 */
		showConfigsWindow: function(providerId, configs)
		{

			var configCount = 0,
				content = "",
				buttons = [];

			content ='<form id="provider_configs_form"><table class="bx-adm-scale-provider-config-table">';

			for(var i in configs)
			{
				var id = providerId+"_config_"+i;
				content += 	'<tr><td><input type="radio" name="provider_configs" id="'+id+'" value="'+configs[i].id+'" onclick="BX(\'provider_config_choose_but\').disabled=false;"></td>'+
					'<td><label for="'+id+'">'+configs[i].descr+'</label></td></tr>';

				configCount++;
			}

			if(configCount > 0)
			{
				content += '</tr></table></form>';
				buttons.push({
					title: BX.message("SCALE_PANEL_JS_PROVIDER_BUT_CHOOSE"),
					id: "provider_config_choose_but",
					name: "provider_config_choose_but",
					className: 'adm-btn-save',
					action: function(){

						var form = BX("provider_configs_form"),
							configId = "";

						for(var i = form.elements.length-1; i >= 0; i-- )
						{
							if(form.elements[i].checked)
							{
								configId = form.elements[i].value;
								break;
							}
						}

						BX.Scale.Provider.sendOrder(providerId, configId);
						this.parentWindow.Close();
					}
				});
			}
			else
			{
				content = BX.message("SCALE_PANEL_JS_PROVIDER_NO_CONFIGS");
			}

			buttons.push(BX.CAdminDialog.btnCancel);

			if(this.dialogConfigsWindow == null)
			{
				this.dialogConfigsWindow = new BX.CDialog({
					title: BX.message("SCALE_PANEL_JS_PROVIDER_CONFIG_CHOOSE"),
					content: content,
					resizable: false,
					height: 400,
					width: 600,
					buttons: buttons
				});
			}
			else
			{
				this.dialogConfigsWindow.SetContent(content);
			}

			BX('provider_config_choose_but').disabled = true;
			this.dialogConfigsWindow.adjustSizeEx();
			this.dialogConfigsWindow.Show();

			return true;
		},

		sendOrder: function(providerId, configId)
		{
			var sendParams = {
				operation: "send_order_to_provider",
				providerId: providerId,
				configId: configId
			};

			var callbacks = {
				onsuccess: function(result) {
					if(result && result["RESULT"] == "OK" && result["TASK_ID"])
					{
						var mess = BX.message("SCALE_PANEL_JS_PROVIDER_ORDER_SUCCESS").replace("##ORDER_ID##", result["TASK_ID"]);
						mess = mess.replace("##PROVIDER_ID##", providerId);
						BX.Scale.AdminFrame.alert(mess, BX.message("SCALE_PANEL_JS_PROVIDER_ORDER_SUCCESS_TITLE"));
					}
					else
					{
						BX.Scale.AdminFrame.alert(BX.message("SCALE_PANEL_JS_PROVIDER_ORDER_ERROR"), BX.message("SCALE_PANEL_JS_PROVIDER_ERROR"));
					}
				},
				onfailure: function(type, e) {
					BX.debug({type: type, error: e});
				}
			};

			BX.Scale.Communicator.sendRequest(sendParams, callbacks, this, false);

			return true;
		}
	};

})(window);
