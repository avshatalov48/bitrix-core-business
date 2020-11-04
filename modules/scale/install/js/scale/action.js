/**
 * Class BX.Scale.Action
 * Describes action's props, view & behavior
 */
;(function(window) {

	if (BX.Scale.Action) return;

	/**
	 * Class BX.Scale.Action
	 * @constructor
	 */
	BX.Scale.Action = function (id, params)
	{
		this.id = id;
		this.name = params.NAME;
		this.userParams = params.USER_PARAMS;
		this.freeParams = {};

		if(params && params.TYPE !== undefined)
			this.type = params.TYPE;
		else
			this.type = "ACTION";

		if(this.type == "CHAIN" && params.ACTIONS !== undefined)
			this.actions = params.ACTIONS;
		else if(this.type == "MODIFYED")
			this.allParams  = params;

		this.currentOperation = "";
		this.paramsDialog = null;
		this.async = params.ASYNC == "Y";
		this.pageRefresh = params.PAGE_REFRESH == "Y";
		this.backupAlert = params.BACKUP_ALERT == "Y";
		this.timeToComplete = null;
		this.timeToCompleteInterval = null;
		this.extraDbConfirm = params.CHECK_EXTRA_DB_USER_ASK == "Y";
		this.skipConfirmation = false;
	};

	/**
	 * Returns list of  params to ask user
	 * @returns {{}}
	 */
	BX.Scale.Action.prototype.getUserParams = function()
	{
		var result = {};

		if(this.type == "CHAIN")
			result = this.extractUserParamsFromActions();
		else
			result = this.userParams;

		return result;
	};

	/**
	 * In case actions chain returns all user params from all actions
	 * @returns {{}}
	 */
	BX.Scale.Action.prototype.extractUserParamsFromActions = function()
	{
		var result = {};

		for(var actionId in this.actions)
		{
			var actUserParams = BX.Scale.actionsCollection.getObject(this.actions[actionId]).getUserParams();

			for(var paramId in actUserParams)
				result[paramId] = actUserParams[paramId];
		}

		return result;
	};

	/**
	 * Starts execution of the action
	 * @param serverHostname
	 * @param paramsValues
	 * @param skipBackupAlert
	 */
	BX.Scale.Action.prototype.start = function(serverHostname, paramsValues, skipBackupAlert)
	{
		var _this = this;

		if(this.backupAlert && !skipBackupAlert)
		{
			BX.Scale.AdminFrame.confirm(
				BX.message("SCALE_PANEL_JS_ADVICE_TO_BACKUP"),
				BX.message("SCALE_PANEL_JS_ADVICE_TO_BACKUP_TITLE"),
				function(){ window.location.href = "/bitrix/admin/dump.php?lang="+BX.message('LANGUAGE_ID'); },
				function() { _this.start(serverHostname, paramsValues, true); }
			);

			return;
		}

		if(this.extraDbConfirm)
		{
			BX.Scale.AdminFrame.confirm(
				BX.message("SCALE_PANEL_JS_EXTRA_DB_CONFIRM"),
				BX.message("SCALE_PANEL_JS_EXTRA_DB_CONFIRM_TITLE"),
				function() {
					_this.extraDbConfirm = false;
					_this.skipConfirmation = true;
					_this.start(serverHostname, paramsValues, true);
				}
			);

			return;
		}

		var userParams = this.getUserParams();
		var freeParams = {};

		this.currentOperation = "start";

		if(paramsValues !== undefined)
		{
			for(var key in paramsValues)
			{
				if(userParams && userParams[key] !== undefined)
					userParams[key].DEFAULT_VALUE = paramsValues[key];
				else
					this.freeParams[key] = paramsValues[key];
			}
		}

		if(userParams !== undefined)
		{
			this.paramsDialog = new BX.Scale.ActionParamsDialog({
				title: this.name,
				userParams: userParams,
				serverHostname: serverHostname,
				callback: this.sendRequest,
				context: this
			});

			this.paramsDialog.show();
		}
		else if(!this.skipConfirmation)
		{
			BX.Scale.AdminFrame.confirm(
				BX.message("SCALE_PANEL_JS_ACT_CONFIRM")+" "+this.name.toLowerCase()+"?",
				BX.message("SCALE_PANEL_JS_ACT_CONFIRM_TITLE"),
				BX.proxy(function(){ this.sendRequest({ serverHostname: serverHostname, freeParams: freeParams }); }, this)
			);
		}
		else if(this.skipConfirmation)
		{
			this.skipConfirmation = false;
			this.sendRequest({ serverHostname: serverHostname, freeParams: freeParams });
		}
	};

	/**
	 * Shows the action execution results
	 * @param result
	 */
	BX.Scale.Action.prototype.showResultDialog = function(result)
	{
		var resultDialog = new BX.Scale.ActionResultDialog({
			actionName: this.name,
			result: result,
			pageRefresh: this.pageRefresh
		});

		resultDialog.show();
	};

	/**
	 * Shows the dialog of the async action's  execution process
	 * @param {object} result -request result
	 */
	BX.Scale.Action.prototype.showAsyncDialog = function(result)
	{
		BX.Scale.ActionProcessDialog.addActionProcess(this.name);

		BX.Scale.AdminFrame.timeIntervalId = setInterval(BX.proxy(this.checkAsyncState, this), BX.Scale.AdminFrame.timeAsyncRefresh);

		if( result.ACTION_RESULT
			&& result.ACTION_RESULT[this.id]
			&& result.ACTION_RESULT[this.id].OUTPUT
			&& result.ACTION_RESULT[this.id].OUTPUT.DATA
			)
		{
			if(result.ACTION_RESULT[this.id].OUTPUT.DATA.message)
				BX.Scale.ActionProcessDialog.addActionMessage(result.ACTION_RESULT[this.id].OUTPUT.DATA.message);

			if(result.ACTION_RESULT[this.id].OUTPUT.DATA.params)
			{
				if(result.ACTION_RESULT[this.id].OUTPUT.DATA.params.task_name)
				{
					BX.Scale.AdminFrame.currentAsyncActionBID = result.ACTION_RESULT[this.id].OUTPUT.DATA.params.task_name;
				}
				else
				{
					for(var i in result.ACTION_RESULT[this.id].OUTPUT.DATA.params)
					{
						BX.Scale.AdminFrame.currentAsyncActionBID = i;
						break;
					}
				}
			}
		}

		BX.Scale.ActionProcessDialog.pageRefresh = this.pageRefresh;
		BX.Scale.ActionProcessDialog.show();

		if(BX.Scale.AdminFrame.currentAsyncActionBID.length <= 0)
		{
			var message;

			if(result.ACTION_RESULT[this.id].ERROR.length > 0)
				message = result.ACTION_RESULT[this.id].ERROR;
			else if(result.ACTION_RESULT[this.id].OUTPUT && result.ACTION_RESULT[this.id].OUTPUT.TEXT)
				message = result.ACTION_RESULT[this.id].OUTPUT.TEXT;
			else
				message = BX.message("SCALE_PANEL_JS_BID_ERROR");

			BX.Scale.ActionProcessDialog.setActionResult(false, message);
		}
	};

	/**
	 * Forms request params to execute action
	 */
	BX.Scale.Action.prototype.checkAsyncState = function()
	{
		if(BX.Scale.AdminFrame.currentAsyncActionBID.length <= 0 )
			return false;

		var sendPrams = {
			operation: "check_state",
			bid: BX.Scale.AdminFrame.currentAsyncActionBID
		};

		var callbacks = {
			onsuccess: function(result){

				if(this.timeToCompleteInterval !== null)
				{
					BX.Scale.AdminFrame.failureAnswersCount = 0;
					clearInterval(this.timeToCompleteInterval);
					this.timeToComplete = null;
					BX.Scale.ActionProcessDialog.addActionMessage("", true);
				}

				if(result)
				{
					BX.Scale.AdminFrame.failureAnswersCount = 0;

					if(result.ERROR.length <= 0 && result.ACTION_STATE && result.ACTION_STATE.status)
					{
						if(result.ACTION_STATE.status == "finished")
						{
							clearInterval(BX.Scale.AdminFrame.timeIntervalId );
							BX.Scale.ActionProcessDialog.setActionResult(true, BX.message("SCALE_PANEL_JS_ACT_EXEC_SUCCESS"));
							BX.Scale.AdminFrame.currentAsyncActionBID = "";
						}
						else if(result.ACTION_STATE.status == "error")
						{
							clearInterval(BX.Scale.AdminFrame.timeIntervalId );

							var mess = "";

							if(result.ACTION_STATE.error_messages)
							{
								for(var i in result.ACTION_STATE.error_messages)
								{
									mess += result.ACTION_STATE.error_messages[i]+"<br>";
								}
							}

							BX.Scale.ActionProcessDialog.setActionResult(false, mess);
							BX.Scale.AdminFrame.currentAsyncActionBID = "";
						}
						else if(result.ACTION_STATE.status == "interrupt")
						{
							clearInterval(BX.Scale.AdminFrame.timeIntervalId );
							BX.Scale.ActionProcessDialog.setActionResult(false, BX.message("SCALE_PANEL_JS_ACT_EXEC_INTERRUPTED"));
							BX.Scale.AdminFrame.currentAsyncActionBID = "";
						}
						else
						{
							if(result.ACTION_STATE.status == "running" && result.ACTION_STATE.last_action && result.ACTION_STATE.last_action.length > 0)
							{
								BX.Scale.ActionProcessDialog.addActionMessage("last operation:<br>"+result.ACTION_STATE.last_action, true);
							}
						}
					}
					else if(!result.ACTION_STATE || result.ACTION_STATE.status)
					{
						clearInterval(BX.Scale.AdminFrame.timeIntervalId );
						BX.Scale.ActionProcessDialog.setActionResult(false);
					}
					else
					{
						clearInterval(BX.Scale.AdminFrame.timeIntervalId );
						BX.Scale.ActionProcessDialog.setActionResult(false, BX.message("SCALE_PANEL_JS_ERROR")+" "+result.ERROR);
					}
				}
				else
				{
					if(BX.Scale.AdminFrame.failureAnswersCountAllow >= BX.Scale.AdminFrame.failureAnswersCount)
					{
						BX.Scale.AdminFrame.failureAnswersCount++;
						return;
					}

					clearInterval(BX.Scale.AdminFrame.timeIntervalId );
					BX.Scale.ActionProcessDialog.setActionResult(false, BX.message("SCALE_PANEL_JS_ACT_EXEC_ERROR"));
				}
			},
			onfailure: function(type, e){

				var now = new Date();

				if(type == "processing" &&
					e.data &&
					e.data.search('SCALE_SERVER_NOT_AVAILABLE') != -1 &&
						(
							this.timeToComplete === null ||
							now.getTime() > this.timeToComplete
						)
					)
				{
					var _this = this;

					var timeToComplete = this.extractTimeToComplete(e.data);

					if(this.timeToComplete < timeToComplete)
						this.timeToComplete = timeToComplete;

					this.timeToCompleteInterval = window.setInterval(function(){
						var timePeriod = _this.makeTimeToCompleteString(_this.timeToComplete);
						if(timePeriod)
							BX.Scale.ActionProcessDialog.addActionMessage(timePeriod, true);
					},1000);

					return;
				}

				if(BX.Scale.AdminFrame.failureAnswersCountAllow >= BX.Scale.AdminFrame.failureAnswersCount)
				{
					BX.Scale.AdminFrame.failureAnswersCount++;
					return;
				}

				clearInterval(BX.Scale.AdminFrame.timeIntervalId );
				BX.Scale.ActionProcessDialog.setActionResult(false, BX.message("SCALE_PANEL_JS_ACT_RES_ERROR"));
			}
		};

		BX.Scale.Communicator.sendRequest(sendPrams, callbacks, this, false);

		return true;
	};

	/**
	 * Form request params to execute action
	 * @param {object} params - action params
	 */
	BX.Scale.Action.prototype.sendRequest = function(params)
	{
		var sendPrams = {
				actionId: this.id,
				serverHostname: params.serverHostname,
				operation: this.currentOperation
			},
			_this = this;

		if(params.userParams !== undefined)
			sendPrams.userParams = params.userParams;

		if(this.freeParams)
			sendPrams.freeParams = this.freeParams;

		if(this.type == "MODIFYED")
			sendPrams.actionParams = this.allParams;

		var callbacks = {
			onsuccess: function(result){

				if(result)
				{
					if(result.NEED_MORE_USER_INFO)
					{
						this.startModifyed(result.NEED_MORE_USER_INFO);
						return;
					}

					if(result.ERROR.length <= 0)
					{
						if(this.async)
						{
							_this.showAsyncDialog(result);
						}
						else
						{
							if(result.ACTION_RESULT
								&& result.ACTION_RESULT.COPY_KEY_TO_SERVER
								&& result.ACTION_RESULT.COPY_KEY_TO_SERVER.RESULT == "ERROR"
								&& result.ACTION_RESULT.COPY_KEY_TO_SERVER.OUTPUT.DATA.message.search(/^User must change password/) != -1
								)
							{
								BX.Scale.AdminFrame.alert(
									BX.message("SCALE_PANEL_JS_PASS_MUST_BE_CHANGED"),
									BX.message("SCALE_PANEL_JS_WARNING"),
									function(){
										BX.Scale.actionsCollection.getObject("CHANGE_PASSWD_FIRST_ALL").start(sendPrams.serverHostname, sendPrams.userParams);
										BX.Scale.AdminFrame.nextActionId = "NEW_SERVER_CHAIN";
									}
								);
							}
							else
							{
								if(BX.Scale.AdminFrame.nextActionId != this.id
									&& BX.Scale.AdminFrame.nextActionId !== null
									&& BX.Scale.actionsCollection.getObject(BX.Scale.AdminFrame.nextActionId)
									)
								{
									BX.Scale.actionsCollection.getObject(BX.Scale.AdminFrame.nextActionId).start(sendPrams.serverHostname, sendPrams.userParams);
									BX.Scale.AdminFrame.nextActionId = null;
								}

								_this.showResultDialog(result);
							}
						}
					}
					else
					{
						BX.Scale.AdminFrame.alert(
							result.ERROR,
							BX.message("SCALE_PANEL_JS_ERROR")
						);

					}
				}
				else
				{
					BX.Scale.AdminFrame.alert(
						BX.message("SCALE_PANEL_JS_ACT_EXEC_ERROR"),
						BX.message("SCALE_PANEL_JS_ERROR")
					);
				}
			},
			onfailure: function(){
				BX.Scale.AdminFrame.alert(
					BX.message("SCALE_PANEL_JS_ACT_RES_ERROR"),
					BX.message("SCALE_PANEL_JS_ERROR")
				);
			}
		};

		BX.Scale.Communicator.sendRequest(sendPrams, callbacks, this, true);
	};

	/**
	 * Parses time to end server unavailability from received special page
	 * @param html
	 * @returns {int} timestamp
	 */
	BX.Scale.Action.prototype.extractTimeToComplete = function(html)
	{
		if(!html || typeof html != 'string')
			return false;

		var	now = new Date(),
			availableDateTime = html.match(/availableDateTime\s=\s(\d+)/im)[1],
			serverNow = html.match(/serverNow\s=\s(\d+)/im)[1],
			timePeriod = availableDateTime - serverNow,
			timeToComplete = now.getTime() + timePeriod;

			if(now > timeToComplete)
				timeToComplete.setTime(now.getTime()+timePeriod);

		return timeToComplete;
	};

	/**
	 * @param timeToComplete - timestamp
	 * @returns {String}
	 */
	BX.Scale.Action.prototype.makeTimeToCompleteString = function(timeToComplete)
	{
		var now = new Date();

		if(now > timeToComplete)
			return false;

		var deltaTime = timeToComplete-now,
			hours = Math.floor(deltaTime/3600000),
			minutes = Math.floor(deltaTime/60000)-hours*60,
			seconds = Math.floor(deltaTime/1000)-hours*3600-minutes*60;

		hours = (hours < 10) ? "0"+hours : hours;
		minutes = (minutes < 10) ? "0"+minutes : minutes;
		seconds = (seconds < 10) ? "0"+seconds : seconds;

		return BX.message("SCALE_PANEL_JS_ACT_SERVER_WILL_AVAILABLE")+"<br>"+
				hours+" "+BX.message("SCALE_PANEL_JS_ACT_HOUR")+". "+
				minutes+" "+BX.message("SCALE_PANEL_JS_ACT_MIN")+". "+
				seconds+" "+BX.message("SCALE_PANEL_JS_ACT_SEC")+".";
	};

	/**
	 * If we need more info from user - let's ask him by starting new action
	 * @param newActionParams
	 */
	BX.Scale.Action.prototype.startModifyed = function(newActionParams)
	{
		delete(newActionParams.ACTION_PARAMS.MODIFYERS);
		newActionParams.ACTION_PARAMS.TYPE = "MODIFYED";

		var action = BX.Scale.actionsCollection.addObject(newActionParams.ACTION_ID+"_MODIF", newActionParams.ACTION_PARAMS);
		var hostname = newActionParams.HOSTNAME || false;

		action.start(hostname, {}, true);
	};

})(window);
