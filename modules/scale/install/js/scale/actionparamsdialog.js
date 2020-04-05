/**
 * Class BX.Scale.ActionParamsDialog
 */
;(function(window) {

	if (BX.Scale.ActionParamsDialog) return;

	/**
	 * Class BX.Scale.ActionParamsDialog
	 * @constructor
	 */
	BX.Scale.ActionParamsDialog = function (params)
	{
		this.title = params.title;
		this.userParams = params.userParams;
		this.serverHostname = params.serverHostname;
		//Function wich will be execute after dialog closes
		this.callback = params.callback;
		//outer _this
		this.context = params.context;
		//BX.CDialog
		this.dialogWindow = null;
		this.params = {};
		//Required params wich still empty
		this.requiredParamsEmptyty = {};
		this.startButtonId = 'butt_action_start';
		this.startButtonDisabled = false;
		//Params wich we must confirm (enter twice)
		this.confirmParams = {};
	};

	/**
	 * Shows dialog window
	 */
	BX.Scale.ActionParamsDialog.prototype.show = function()
	{
		var content = this.buildContent();
		BX.Scale.currentActionDialogContext = this;

		this.dialogWindow = new BX.CDialog({
			title: this.title,
			content: content,
			resizable: true,
			buttons: [{
				title: BX.message("SCALE_PANEL_JS_APD_BUT_START"),
				id: this.startButtonId,
				name: this.startButtonId,
				className: 'adm-btn-save',
				action: BX.proxy(this.returnParamsValues, BX.Scale.currentActionDialogContext)
			}, BX.CAdminDialog.btnCancel]
		});

		BX.addCustomEvent(this.dialogWindow, 'onWindowClose', function(obWnd) {
			obWnd.DIV.parentNode.removeChild(obWnd.DIV);
		});


		if(!BX.Scale.isObjEmpty(this.requiredParamsEmptyty))
		{
			this.disableStartButton();
		}

		this.dialogWindow.adjustSizeEx();
		this.dialogWindow.Show();
	};

	/**
	 * Creates HTML inputs from user params description
	 * @returns {string}
	 */
	BX.Scale.ActionParamsDialog.prototype.buildContent = function()
	{
		var content = BX.create('DIV'),
			contentForm = BX.create('form',{
				props:{
					id: 'action_params_dialog_form'
				}
			}),
			contentTable = BX.create('table');

		for(var paramId in this.userParams)
		{
			if(!this.userParams.hasOwnProperty(paramId))
				continue;

			switch(this.userParams[paramId].TYPE)
			{
				case "STRING":
				case "PASSWORD":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.String(paramId, this.userParams[paramId]);

					if(this.userParams[paramId].VERIFY_TWICE == "Y")
					{
						var confirmParams = {};

						for(var key in  this.userParams[paramId])
							confirmParams[key] =  this.userParams[paramId][key];

						confirmParams.NAME += " ("+BX.message("SCALE_PANEL_JS_APD_2_CONFIRM")+")";
						this.confirmParams[paramId] = new BX.Scale.ActionsParamsTypes.String(paramId+"_confirm", confirmParams);
					}

					break;
				case "CHECKBOX":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.Checkbox(paramId, this.userParams[paramId]);
					break;
				case "DROPDOWN":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.Dropdown(paramId, this.userParams[paramId]);
					break;
				case "TEXT":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.Text(paramId, this.userParams[paramId]);
					break;
				case "FILE":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.File(paramId, this.userParams[paramId]);
					break;
				case "REMOTE_AND_LOCAL_PATH":
					this.params[paramId] = new BX.Scale.ActionsParamsTypes.RemoteAndLocalPath(paramId, this.userParams[paramId]);
					break;
			}

			if(this.params[paramId])
				contentTable.appendChild(this.createParamNodeRaw(this.params[paramId]));

			if(this.confirmParams[paramId])
				contentTable.appendChild(this.createParamNodeRaw(this.confirmParams[paramId]));
		}

		contentForm.appendChild(contentTable);
		content.appendChild(contentForm);

		BX.addCustomEvent("BXScaleActionParamKeyUp", BX.proxy(this.onParamFieldKeyUp, this));

		return content;
	};

	BX.Scale.ActionParamsDialog.prototype.createParamNodeRaw = function(paramNode)
	{
		if(!BX.type.isElementNode(paramNode.domNode))
			return false;

		var tr = BX.create('tr'),
			name = BX.create('span', {props: { innerHTML: paramNode.name+': '}});

		if(paramNode.required !== undefined	&& paramNode.required == "Y")
		{
			BX.addClass(name,'adm-required-field');

			if(!paramNode.defaultValue || paramNode.defaultValue.length <= 0)
				this.requiredParamsEmptyty[paramNode.id] = true;
			else
				this.requiredParamsEmptyty[paramNode.id] = false;
		}

		var control = paramNode.domNode;

		if(paramNode.domNode.type == 'file')
			control = BX.adminFormTools.modifyFile(control);

		var td = BX.create('td', {style: {'textAlign': 'right', 'width': '40%'}});
		td.appendChild(name);
		tr.appendChild(td);
		td = BX.create('td', {style: {'textAlign': 'left', 'width': '60%'}});
		td.appendChild(control);
		tr.appendChild(td);

		return tr;
	};

	BX.Scale.ActionParamsDialog.prototype.isAllRequiredParamsFilled = function()
	{
		var result = true;

		for(var paramId in this.requiredParamsEmptyty)
		{
			if(this.requiredParamsEmptyty[paramId])
			{
				result = false;
				break;
			}
		}

		return result;
	};

	BX.Scale.ActionParamsDialog.prototype.enableStartButton = function()
	{
		this.disableStartButton(true);
	};

	BX.Scale.ActionParamsDialog.prototype.disableStartButton = function(enable)
	{
		var but = BX(this.startButtonId);

		if(but && but.disabled !== undefined)
		{
			var disable = !(enable);
			but.disabled = disable;
			this.startButtonDisabled = disable;
		}
	};

	BX.Scale.ActionParamsDialog.prototype.onParamFieldKeyUp = function(params)
	{
		if(this.requiredParamsEmptyty[params.paramId] !== undefined)
		{
			this.requiredParamsEmptyty[params.paramId] = params.empty;

			if(this.isAllRequiredParamsFilled() == this.startButtonDisabled)  //if state was changed
				this.disableStartButton(this.startButtonDisabled);
		}

		this.addSitePatch(params);
	};

	/**
	 * Extracts params values entered by user from html inputs,
	 * and calls callback.
	 * @returns {object}
	 */
	BX.Scale.ActionParamsDialog.prototype.returnParamsValues = function()
	{
		var paramsValues = {},
			paramValue,
			confirmParamValue;

		for(var paramId in this.params)
		{
			if(!this.params.hasOwnProperty(paramId))
				continue;

			paramValue = this.params[paramId].getValue();

			if(this.confirmParams[paramId])
			{
				confirmParamValue = this.confirmParams[paramId].getValue();

				if(paramValue != confirmParamValue)
				{
					var message = BX.message("SCALE_PANEL_JS_APD_2_NOT_CONCIDE");
					message = message.replace("##FIELD1##", this.params[paramId].name);
					message = message.replace("##FIELD2##", this.confirmParams[paramId].name);
					BX.Scale.AdminFrame.alert(
						message,
						BX.message("SCALE_PANEL_JS_WARNING")
					);

					return false;
				}
			}

			paramsValues[paramId] = paramValue;
		}

		this.dialogWindow.Close();

		if(typeof this.callback == "function")
			this.callback.call(this.context, {
				userParams: paramsValues,
				serverHostname: this.serverHostname
			});

		return paramsValues;
	};

	BX.Scale.ActionParamsDialog.prototype.addSitePatch = function(params)
	{
		if(params.paramId !== "SITE_NAME")
			return;

		var siteName = BX('action_user_param_SITE_NAME').value;

		/*
		BX('action_user_param_DB_NAME').value = siteName+'db';
		BX('action_user_param_DB_USERNAME').value = siteName+'user';
		*/
		BX('action_user_param_SITE_PATH').value = siteName;
	};

	})(window);
