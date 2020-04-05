/**
 * BX.Scale.AdminFrame class - main class for scalability panel page.
 */
;(function(window) {

	if (BX.Scale.AdminFrame) return;

	BX.Scale.AdminFrame = {

		frameObjectName: "",
		srvFrameObjectName: "",
		currentAsyncActionBID: "", //Bitrix ID (BID) for async long actions.
		timeAsyncRefresh: 20000, //ms how often we want to refresh info about "long" actions
		timeIntervalId: "",
		graphPageUrl: "",
		failureAnswersCount: 0,
		failureAnswersCountAllow: 50, // Max number bad request-answers we suppose to receive.  For example server reboots, etc.
		nextActionId: null, // for action chains

		/**
		 * Initializes frame params
		 */
		init: function (params)
		{
			for(var key in params)
				this[key] = params[key];
		},

		/**
		 * Prepares & builds main frame objects
		 */
		build: function()
		{
			var frameObj = BX(this.frameObjectName);

			if(!frameObj)
				return false;

			this.showActions();
			this.showServers();

			return true;
		},

		showServers: function()
		{
			var frameObj = BX(this.srvFrameObjectName),
				servers = BX.Scale.serversCollection.getObjectsList(),
				newSrv = frameObj.children[0];

			if(frameObj)
				for(var key in servers)
					frameObj.insertBefore(servers[key].getDomObj(), newSrv);
		},

		isObjectEmpty: function (obj)
		{
			for (var i in obj)
				return false;

			return true;
		},

		showActions: function()
		{
			var frameObj = BX(this.frameObjectName);

			if(!frameObj)
				return false;

			if(!this.isObjectEmpty(BX.Scale.serversCollection.getObjectsList()))
				frameObj.insertBefore(this.getMenuObj(), frameObj.children[0]);
		},

		/**
		 * Starts the process of monitoring data refreshing
		 * @param {number} timeInterval - ms how often we must refresh data
		 */
		refreshingDataStart: function(timeInterval)
		{
			BX.Scale.AdminFrame.refreshingDataIntervalId = setInterval( function() {
					BX.Scale.AdminFrame.refreshServersRolesLoadbars();
				},
				timeInterval
			);
		},

		/**
		 * Sets the servers roles loadbar values
		 * @param {object} monitoringData
		 */
		setMonitoringValues: function(monitoringData)
		{
			for(var hostname in monitoringData)
			{
				var server = BX.Scale.serversCollection.getObject(hostname);

				if(monitoringData[hostname].ROLES_LOADBARS)
				{
					for(var roleId in  monitoringData[hostname].ROLES_LOADBARS)
					{
						if(server && server.roles && server.roles[roleId])
						{
							server.roles[roleId].setLoadBarValue(monitoringData[hostname].ROLES_LOADBARS[roleId]);
						}
					}
				}

				if(monitoringData[hostname].MONITORING_VALUES)
					server.setMonitoringValues(monitoringData[hostname].MONITORING_VALUES);
			}
		},

		/**
		 * Receives the data from server for server roles loadbars using ajax request
		 */
		refreshServersRolesLoadbars: function()
		{
			if(!BX.Scale.AdminFrame.monitoringParams)
			{
				BX.Scale.AdminFrame.monitoringParams = {};

				var servers = BX.Scale.serversCollection.getObjectsList();

				for(var srvId in servers)
				{
					if(!BX.Scale.isMonitoringDbCreated[srvId])
						continue;

					BX.Scale.AdminFrame.monitoringParams[srvId] = {
						rolesIds:[],
						monitoringParams: servers[srvId].getMonitoringParams()
					};

					for(var roleId in  servers[srvId].roles)
					{
						if(servers[srvId].roles[roleId].loadBar !== null)
						{
							BX.Scale.AdminFrame.monitoringParams[srvId].rolesIds.push(roleId);
						}
					}
				}
			}

			if(BX.Scale.isObjEmpty(BX.Scale.AdminFrame.monitoringParams))
				return;

			var sendPrams = {
				operation: "get_monitoring_values",
				servers: BX.Scale.AdminFrame.monitoringParams
			};

			var callbacks = {
				onsuccess: function(result){
					if(result)
					{
						if(result.MONITORING_DATA)
						{
							BX.Scale.AdminFrame.setMonitoringValues(result.MONITORING_DATA);
						}

						if(result.ERROR && result.ERROR.length > 0)
						{
							BX.debug("Monitoring data error: "+result.ERROR);
						}
					}
					else
					{
						BX.debug("Monitoring receiving data error.");
					}
				},
				onfailure: function(){
					BX.debug("Monitoring receiving data failure.");
				}
			};

			BX.Scale.Communicator.sendRequest(sendPrams, callbacks, this, false);
		},


		getMenuObj: function()
		{
			var domObj = document.createElement("span");
			BX.addClass(domObj, "adm-scale-menu-btn");
			domObj.innerHTML = BX.message("SCALE_PANEL_JS_GLOBAL_ACTIONS");
			BX.bind(domObj, "click", BX.proxy(this.actionsMenuOpen, this));
			return BX.create("div",{children:[domObj], style:{padding:"0 0 40px 0"}});
		},

		actionsMenuOpen: function(event)
		{
			event = event || window.event;
			var menuButton = event.target || event.srcElement,
				menuItems = [],
				settMenu = [],
				actionsIds = {
					MONITORING_ENABLE: true,
					MONITORING_DISABLE: true,
					SITE_CREATE: true,
					SITE_DEL: true,
					SET_EMAIL_SETTINGS: true,
					CRON_SET: true,
					CRON_UNSET: true,
					HTTP_OFF: true,
					HTTP_ON: true,
					CERTIFICATES: true,
					UPDATE_ALL_BVMS: true,
					UPDATE_ALL_SYSTEMS: true 
				},
				s;

			for(var key in actionsIds)
			{
				if(!actionsIds.hasOwnProperty(key))
					continue;

				var action = BX.Scale.actionsCollection.getObject(key);

				if(action)
				{
					if(key == "SET_EMAIL_SETTINGS")
					{
						settMenu = [];

						for(s in BX.Scale.sitesList)
						{
							if(!BX.Scale.sitesList.hasOwnProperty(s))
								continue;

							settMenu.push({
								TEXT: BX.Scale.sitesList[s].NAME,
								ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start('',{SITE_NAME_CONF: '"+BX.Scale.sitesList[s].SiteName+"', SITE_NAME: '"+BX.Scale.sitesList[s].NAME+"',SMTP_HOST: BX.Scale.sitesList['"+s+"'].SMTPHost,SMTP_PORT: BX.Scale.sitesList['"+s+"'].SMTPPort,SMTP_USER: BX.Scale.sitesList['"+s+"'].SMTPUser,EMAIL: BX.Scale.sitesList['"+s+"'].EmailAddress,SMTPTLS: (BX.Scale.sitesList['"+s+"'].SMTPTLS == 'on' ? 'Y' : 'N'), USER_PASSWORD: BX.Scale.sitesList['"+s+"'].SMTPPassword, USE_AUTH: (BX.Scale.sitesList['"+s+"'].SMTP_USE_AUTH == 'Y' ? 'Y' : 'N')});"
							});
						}

						menuItems.push({
							TEXT: action.name,
							MENU: settMenu
						});
					}
					else if(key == "CERTIFICATES")
					{
						var menu1 = [];

						for(s in BX.Scale.sitesList)
						{
							if(!BX.Scale.sitesList.hasOwnProperty(s))
								continue;

							var email = BX.Scale.sitesList[s].EMAIL ? BX.Scale.sitesList[s].EMAIL : '',
								domains = BX.Scale.sitesList[s].DOMAINS ? BX.Scale.sitesList[s].DOMAINS : '';

							menu1.push({
								TEXT: BX.Scale.sitesList[s].NAME,
								ONCLICK: "BX.Scale.actionsCollection.getObject('CERTIFICATE_LETS_ENCRYPT_CONF').start('',{SITE_NAME_CONF: '"+BX.Scale.sitesList[s].SiteName+"', SITE_NAME: '"+BX.Scale.sitesList[s].NAME+"', EMAIL: '"+email+"', DNS: '"+domains+"'});"
							});
						}

						var menu2 = [];

						for(s in BX.Scale.sitesList)
						{
							if(!BX.Scale.sitesList.hasOwnProperty(s))
								continue;

							menu2.push({
								TEXT: BX.Scale.sitesList[s].NAME,
								ONCLICK: "BX.Scale.actionsCollection.getObject('CERTIFICATE_SELF_CONF').start('',{SITE_NAME_CONF: '"+BX.Scale.sitesList[s].SiteName+"', SITE_NAME: '"+BX.Scale.sitesList[s].NAME+"', PRIVATE_KEY_PATH: '"+BX.Scale.sitesList[s].HTTPSPriv+"', CERTIFICATE_PATH: '"+BX.Scale.sitesList[s].HTTPSCert+"', CERTIFICATE_CHAIN_PATH: '"+BX.Scale.sitesList[s].HTTPSCertChain+"'});"
							});
						}

						settMenu = [
							{
								TEXT: BX.Scale.actionsCollection.getObject('CERTIFICATE_LETS_ENCRYPT_CONF').name,
								MENU:menu1
							},
							{
								TEXT: BX.Scale.actionsCollection.getObject('CERTIFICATE_SELF_CONF').name,
								MENU:menu2
							}
						];

						menuItems.push({
							TEXT: action.name,
							MENU: settMenu
						});
					}
					else if(key == "CRON_SET" || key == "CRON_UNSET" )
					{
						settMenu = [];
						for(s in BX.Scale.sitesList)
						{
							if(
								(BX.Scale.sitesList[s].CronTask == "enable" && key == "CRON_UNSET")
									|| (BX.Scale.sitesList[s].CronTask != "enable" && key == "CRON_SET")
								)
							{
								settMenu.push({
									TEXT: BX.Scale.sitesList[s].NAME,
									ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start('',{VM_SITE_ID: '"+s+"'});"
								});
							}
						}

						if(settMenu.length > 0)
						{
							menuItems.push({
								TEXT: action.name,
								MENU: settMenu
							});
						}
					}
					else if(key == "HTTP_OFF" || key == "HTTP_ON" )
					{
						settMenu = [];
						for(s in BX.Scale.sitesList)
						{
							if(
								(BX.Scale.sitesList[s].HTTPS == "enable" && key == "HTTP_ON")
									|| (BX.Scale.sitesList[s].HTTPS != "enable" && key == "HTTP_OFF")
								)
							{
								settMenu.push({
									TEXT: BX.Scale.sitesList[s].NAME,
									ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start('',{VM_SITE_ID: '"+s+"'});"
								});
							}
						}

						if(settMenu.length > 0)
						{
							menuItems.push({
								TEXT: action.name,
								MENU: settMenu
							});
						}
					}
					else if(key == "SITE_CREATE")
					{
						menuItems.push({
							TEXT: action.name,
							MENU:[
								{
									TEXT: BX.Scale.actionsCollection.getObject('SITE_CREATE_LINK').name,
									ONCLICK: "BX.Scale.actionsCollection.getObject('SITE_CREATE_LINK').start();"
								},
								{
									TEXT: BX.Scale.actionsCollection.getObject('SITE_CREATE_KERNEL').name,
									ONCLICK: "BX.Scale.actionsCollection.getObject('SITE_CREATE_KERNEL').start();"
								}
							]
						});
					}
					else if(key == "SITE_DEL")
					{
						settMenu = [];

						for(s in BX.Scale.sitesList)
						{
							settMenu.push({
								TEXT: BX.Scale.sitesList[s].NAME,
								ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start('',{VM_SITE_ID: '"+s+"'})"
							});
						}

						menuItems.push({
							TEXT: action.name,
							MENU: settMenu
						});
					}
					else
					{
						menuItems.push({
							TEXT: action.name,
							ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start();"
						});
					}
				}
			}

			if (!menuButton.OPENER)
				BX.adminShowMenu(menuButton, menuItems, {active_class: "bx-adm-scale-menu-butt-active"});
			else
				menuButton.OPENER.SetMenu(menuItems);

			return BX.PreventDefault(event);
		},

		/**
		* Generates name for new server
		*/
		getNewServerName: function(idx)
		{
			if(!idx)
				idx = 1;

			var hostname = "server"+idx;
			var server = BX.Scale.serversCollection.getObject(hostname);

			if(server !== false)
			{
				idx++;
				hostname = this.getNewServerName(idx);
			}

			return hostname;
		},

		/**
		 * Shows alert dialog
		 * @param text
		 * @param title
		 * @param callback
		 */
		alert: function(text, title, callback)
		{
			var btnClose = {
				title: BX.message("SCALE_PANEL_JS_CLOSE"),
				id: 'btnClose',
				name: 'btnClose',

				action: function () {
					this.parentWindow.Close();

					if(callback && typeof callback === 'function')
						callback.apply();
				}
			};

			this.dialogWindow = new BX.CDialog({
				title: title ? title : '',
				content: text,
				resizable: false,
				height: 200,
				width: 400,
				buttons: [ btnClose ]
			});

			this.dialogWindow.adjustSizeEx();
			this.dialogWindow.Show();
		},

		/**
		 * Shows confirm dialog
		 * @param text
		 * @param title
		 * @param callbackOk
		 * @param callbackCancel
		 */
		confirm: function(text, title, callbackOk, callbackCancel)
		{
			var btnOk = {
				title: "OK",
				id: 'btnOk',
				name: 'btnOk',
				className: 'adm-btn-save',

				action: function () {
					this.parentWindow.Close();

					if(callbackOk && typeof callbackOk === 'function')
						callbackOk.apply();
				}
			};

			var btnCancel = {
				title: BX.message("SCALE_PANEL_JS_CANCEL"),
				id: 'btnCancel',
				name: 'btnCancel',

				action: function () {
					this.parentWindow.Close();

					if(callbackCancel && typeof callbackCancel === 'function')
						callbackCancel.apply();
				}
			};

			this.dialogWindow = new BX.CDialog({
				title: title ? title : '',
				content: '<div style="margin-top: 9px;">'+text+'</div>',
				resizable: false,
				height: 200,
				width: 400,
				buttons: [ btnOk, btnCancel]
			});

			this.dialogWindow.adjustSizeEx();
			this.dialogWindow.Show();
		},

		/**
		 * Shows window with warning about running action
		 * @param bid
		 * @returns {boolean}
		 */
		waitForAction: function(bid)
		{
			if(!bid)
				return false;

			this.dialogWindow = new BX.CDialog({
				title: BX.message("SCALE_PANEL_JS_WFA_TITLE"),
				content: BX.message("SCALE_PANEL_JS_WFA_TEXT").replace('##BID##',bid)+"<div class='bx-adm-scale-wait'></div>",
				resizable: false,
				height: 200,
				width: 400
			});

			this.dialogWindow.adjustSizeEx();
			this.dialogWindow.Show();

			this.failureAnswersCount = 0;

			var sendPrams = {
				operation: "check_state",
				bid: bid
			};


			var callbacks = {
				onsuccess: function(result) {

					this.failureAnswersCount = 0;

					if(result.ACTION_STATE.status != "running")
					{
						window.location.reload(true);
					}
				},
				onfailure: function(type, e) {

					BX.debug({type: type, error: e});

					if(this.failureAnswersCountAllow >= this.failureAnswersCount)
					{
						this.failureAnswersCount++;
					}
					else
					{
						window.location.reload(true);
					}
				}
			};

			var _this = this;
			setInterval(function(){	BX.Scale.Communicator.sendRequest(sendPrams, callbacks, _this, false); }, _this.timeAsyncRefresh);

			return true;
		},

		waitForPageRefreshing: function()
		{
			var dialog = new BX.CDialog({
				title: BX.message("SCALE_PANEL_JS_REFRESH_TITLE"),
				content: '<div style="margin-top: 9px;">'+BX.message("SCALE_PANEL_JS_REFRESH_TEXT")+'</div>',
				resizable: false,
				height: 200,
				width: 400
			});

			dialog.adjustSizeEx();
			dialog.Show();
		}
	}

})(window);