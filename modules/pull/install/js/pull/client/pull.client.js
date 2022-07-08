;(function()
{
	/**
	 * Bitrix Push & Pull
	 * Pull client
	 *
	 * @package bitrix
	 * @subpackage pull
	 * @copyright 2001-2019 Bitrix
	 */

	/****************** ATTENTION *******************************
	 * Please do not use Bitrix CoreJS in this class.
	 * This class can be called on a page without Bitrix Framework
	*************************************************************/

	if (!window.BX)
	{
		window.BX = {};
	}
	else if (window.BX.PullClient)
	{
		return;
	}
	else if (!window.BX.RestClient)
	{
		return;
	}

	var BX = window.BX;
	var protobuf = window.protobuf;

	var REVISION = 19; // api revision - check module/pull/include.php
	var LONG_POLLING_TIMEOUT = 60;
	var RESTORE_WEBSOCKET_TIMEOUT = 30 * 60;
	var CONFIG_TTL = 24 * 60 * 60;
	var CONFIG_CHECK_INTERVAL = 60000;
	var MAX_IDS_TO_STORE = 10;

	var LS_SESSION = "bx-pull-session";
	var LS_SESSION_CACHE_TIME = 20;

	var ConnectionType = {
		WebSocket: 'webSocket',
		LongPolling: 'longPolling'
	};

	var PullStatus = {
		Online: 'online',
		Offline: 'offline',
		Connecting: 'connect'
	};

	var SenderType = {
		Unknown: 0,
		Client: 1,
		Backend: 2
	};

	var SubscriptionType = {
		Server: 'server',
		Client: 'client',
		Online: 'online',
		Status: 'status',
		Revision: 'revision'
	};

	var CloseReasons = {
		NORMAL_CLOSURE : 1000,
		SERVER_DIE : 1001,
		CONFIG_REPLACED : 3000,
		CHANNEL_EXPIRED : 3001,
		SERVER_RESTARTED : 3002,
		CONFIG_EXPIRED : 3003,
		MANUAL : 3004,
	};

	var SystemCommands = {
		CHANNEL_EXPIRE: 'CHANNEL_EXPIRE',
		CONFIG_EXPIRE: 'CONFIG_EXPIRE',
		SERVER_RESTART:'SERVER_RESTART'
	};

	var ServerMode = {
		Shared: 'shared',
		Personal: 'personal'
	};

	// Protobuf message models
	var Response = protobuf.roots['push-server']['Response'];
	var ResponseBatch = protobuf.roots['push-server']['ResponseBatch'];
	var Request = protobuf.roots['push-server']['Request'];
	var RequestBatch = protobuf.roots['push-server']['RequestBatch'];
	var IncomingMessagesRequest = protobuf.roots['push-server']['IncomingMessagesRequest'];
	var IncomingMessage = protobuf.roots['push-server']['IncomingMessage'];
	var Receiver = protobuf.roots['push-server']['Receiver'];

	var Pull = function (params)
	{
		params = params || {};

		if (params.restApplication)
		{
			if (typeof params.configGetMethod === 'undefined')
			{
				params.configGetMethod = 'pull.application.config.get';
			}
			if (typeof params.skipCheckRevision === 'undefined')
			{
				params.skipCheckRevision = true;
			}
			if (typeof params.restApplication === 'string')
			{
				params.siteId = params.restApplication;
			}

			params.serverEnabled = true;
		}

		var self = this;

		this.context = 'master';

		this.guestMode = params.guestMode? params.guestMode: (typeof BX.message !== 'undefined' && BX.message.pull_guest_mode? BX.message.pull_guest_mode === 'Y': false);
		this.guestUserId = params.guestUserId? params.guestUserId: (typeof BX.message !== 'undefined' && BX.message.pull_guest_user_id? parseInt(BX.message.pull_guest_user_id, 10): 0);
		if(this.guestMode && this.guestUserId)
		{
			this.userId = this.guestUserId;
		}
		else
		{
			this.userId = params.userId? params.userId: (typeof BX.message !== 'undefined' && BX.message.USER_ID? BX.message.USER_ID: 0);
		}

		this.siteId = params.siteId? params.siteId: (typeof BX.message !== 'undefined' && BX.message.SITE_ID? BX.message.SITE_ID: 'none');
		this.restClient = typeof params.restClient !== "undefined"? params.restClient: new BX.RestClient(this.getRestClientOptions());

		this.enabled = typeof params.serverEnabled !== 'undefined'? (params.serverEnabled === 'Y' || params.serverEnabled === true): (typeof BX.message !== 'undefined' && BX.message.pull_server_enabled === 'Y');
		this.unloading = false;
		this.starting = false;
		this.debug = false;
		this.connectionAttempt = 0;
		this.connectionType = '';
		this.reconnectTimeout = null;
		this.restoreWebSocketTimeout = null;

		this.configGetMethod = typeof params.configGetMethod !== 'string'? 'pull.config.get': params.configGetMethod;
		this.getPublicListMethod = typeof params.getPublicListMethod !== 'string'? 'pull.channel.public.list': params.getPublicListMethod;

		this.skipStorageInit = params.skipStorageInit === true;

		this.skipCheckRevision = params.skipCheckRevision === true;

		this._subscribers = {};

		this.watchTagsQueue = {};
		this.watchUpdateInterval = 1740000;
		this.watchForceUpdateInterval = 5000;

		if (typeof params.configTimestamp !== 'undefined')
		{
			this.configTimestamp = params.configTimestamp;
		}
		else if (typeof BX.message !== 'undefined' && BX.message.pull_config_timestamp)
		{
			this.configTimestamp = BX.message.pull_config_timestamp;
		}
		else
		{
			this.configTimestamp = 0;
		}

		this.session = {
			mid : null,
			tag : null,
			time : null,
			history: {},
			lastMessageIds: [],
			messageCount: 0
		};

		this._connectors = {
			webSocket: null,
			longPolling: null
		};

		Object.defineProperty(this, "connector", {
			get: function()
			{
				return self._connectors[self.connectionType];
			}
		});

		this.isSecure = document.location.href.indexOf('https') === 0;
		this.config = null;

		this.storage = null;

		if(this.userId && !this.skipStorageInit)
		{
			this.storage = new StorageManager({
				userId: this.userId,
				siteId: this.siteId
			});
		}

		this.sharedConfig = new SharedConfig({
			onWebSocketBlockChanged: this.onWebSocketBlockChanged.bind(this),
			storage: this.storage
		});
		this.channelManager = new ChannelManager({
			restClient: this.restClient,
			getPublicListMethod: this.getPublicListMethod
		});

		this.notificationPopup = null;

		// timers
		this.checkInterval = null;
		this.offlineTimeout = null;

		// manual stop workaround
		this.isManualDisconnect = false;

		this.loggingEnabled = this.sharedConfig.isLoggingEnabled();
	};

	/**
	 * Creates a subscription to incoming messages.
	 *
	 * @param {Object} params
	 * @param {string} [params.type] Subscription type (for possible values see SubscriptionType).
	 * @param {string} [params.moduleId] Name of the module.
	 * @param {Function} params.callback Function, that will be called for incoming messages.
	 * @returns {Function} - Unsubscribe callback function
	 */
	Pull.prototype.subscribe = function(params)
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 * mobile/install/js/mobile/pull/client/src/client.js
		 */

		if (!params)
		{
			console.error(Utils.getDateForLog() + ': Pull.subscribe: params for subscribe function is invalid. ');
			return function(){}
		}

		if (!Utils.isPlainObject(params))
		{
			return this.attachCommandHandler(params);
		}

		params = params || {};
		params.type = params.type || SubscriptionType.Server;
		params.command = params.command || null;

		if (params.type == SubscriptionType.Server || params.type == SubscriptionType.Client)
		{
			if (typeof (this._subscribers[params.type]) === 'undefined')
			{
				this._subscribers[params.type] = {};
			}
			if (typeof (this._subscribers[params.type][params.moduleId]) === 'undefined')
			{
				this._subscribers[params.type][params.moduleId] = {
					'callbacks': [],
					'commands': {},
				};
			}

			if (params.command)
			{
				if (typeof (this._subscribers[params.type][params.moduleId]['commands'][params.command]) === 'undefined')
				{
					this._subscribers[params.type][params.moduleId]['commands'][params.command] = [];
				}

				this._subscribers[params.type][params.moduleId]['commands'][params.command].push(params.callback);

				return function () {
					this._subscribers[params.type][params.moduleId]['commands'][params.command] = this._subscribers[params.type][params.moduleId]['commands'][params.command].filter(function(element) {
						return element !== params.callback;
					});
				}.bind(this);
			}
			else
			{
				this._subscribers[params.type][params.moduleId]['callbacks'].push(params.callback);

				return function () {
					this._subscribers[params.type][params.moduleId]['callbacks'] = this._subscribers[params.type][params.moduleId]['callbacks'].filter(function(element) {
						return element !== params.callback;
					});
				}.bind(this);
			}
		}
		else
		{
			if (typeof (this._subscribers[params.type]) === 'undefined')
			{
				this._subscribers[params.type] = [];
			}

			this._subscribers[params.type].push(params.callback);

			return function () {
				this._subscribers[params.type] = this._subscribers[params.type].filter(function(element) {
					return element !== params.callback;
				});
			}.bind(this);
		}
	};

	Pull.prototype.attachCommandHandler = function(handler)
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 */
		if (typeof handler.getModuleId !== 'function' || typeof handler.getModuleId() !== 'string')
		{
			console.error(Utils.getDateForLog() + ': Pull.attachCommandHandler: result of handler.getModuleId() is not a string.');
			return function(){}
		}

		var type = SubscriptionType.Server;
		if (typeof handler.getSubscriptionType === 'function')
		{
			type = handler.getSubscriptionType();
		}

		return this.subscribe({
			type: type,
			moduleId: handler.getModuleId(),
			callback: function(data)
			{
				var method = null;

				if (typeof handler.getMap === 'function')
				{
					var mapping = handler.getMap();
					if (mapping && typeof mapping === 'object')
					{
						if (typeof mapping[data.command] === 'function')
						{
							method = mapping[data.command].bind(handler)
						}
						else if (typeof mapping[data.command] === 'string' && typeof handler[mapping[data.command]] === 'function')
						{
							method = handler[mapping[data.command]].bind(handler);
						}
					}
				}

				if (!method)
				{
					var methodName = 'handle'+data.command.charAt(0).toUpperCase() + data.command.slice(1);
					if (typeof handler[methodName] === 'function')
					{
						method = handler[methodName].bind(handler);
					}
				}

				if (method)
				{
					if (this.debug && this.context !== 'master')
					{
						console.warn(Utils.getDateForLog() + ': Pull.attachCommandHandler: receive command', data);
					}
					method(data.params, data.extra, data.command);
				}
			}.bind(this)
		});
	};

	/**
	 *
	 * @param params {Object}
	 * @returns {boolean}
	 */
	Pull.prototype.emit = function(params)
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 * mobile/install/js/mobile/pull/client/src/client.js
		 */
		params = params || {};

		if (params.type == SubscriptionType.Server || params.type == SubscriptionType.Client)
		{
			if (typeof (this._subscribers[params.type]) === 'undefined')
			{
				this._subscribers[params.type] = {};
			}
			if (typeof (this._subscribers[params.type][params.moduleId]) === 'undefined')
			{
				this._subscribers[params.type][params.moduleId] = {
					'callbacks': [],
					'commands': {},
				};
			}

			if (this._subscribers[params.type][params.moduleId]['callbacks'].length > 0)
			{
				this._subscribers[params.type][params.moduleId]['callbacks'].forEach(function(callback){
					callback(params.data, {type: params.type, moduleId: params.moduleId});
				});
			}

			if (
				this._subscribers[params.type][params.moduleId]['commands'][params.data.command]
				&& this._subscribers[params.type][params.moduleId]['commands'][params.data.command].length > 0)
			{
				this._subscribers[params.type][params.moduleId]['commands'][params.data.command].forEach(function(callback){
					callback(params.data.params, params.data.extra, params.data.command, {type: params.type, moduleId: params.moduleId});
				});
			}

			return true;
		}
		else
		{
			if (typeof (this._subscribers[params.type]) === 'undefined')
			{
				this._subscribers[params.type] = [];
			}

			if (this._subscribers[params.type].length <= 0)
			{
				return true;
			}

			this._subscribers[params.type].forEach(function(callback){
				callback(params.data, {type: params.type});
			});

			return true;
		}
	};

	Pull.prototype.init = function()
	{
		this._connectors.webSocket = new WebSocketConnector({
			parent: this,
			onOpen: this.onWebSocketOpen.bind(this),
			onMessage: this.parseResponse.bind(this),
			onDisconnect: this.onWebSocketDisconnect.bind(this),
			onError: this.onWebSocketError.bind(this)
		});

		this._connectors.longPolling = new LongPollingConnector({
			parent: this,
			onOpen: this.onLongPollingOpen.bind(this),
			onMessage: this.parseResponse.bind(this),
			onDisconnect: this.onLongPollingDisconnect.bind(this),
			onError: this.onLongPollingError.bind(this)
		});

		this.connectionType = this.isWebSocketAllowed() ? ConnectionType.WebSocket : ConnectionType.LongPolling;

		window.addEventListener("beforeunload", this.onBeforeUnload.bind(this));
		window.addEventListener("offline", this.onOffline.bind(this));
		window.addEventListener("online", this.onOnline.bind(this));

		if(BX && BX.addCustomEvent)
		{
			BX.addCustomEvent("BXLinkOpened", this.connect.bind(this));
		}

		if (BX && BX.desktop)
		{
			BX.addCustomEvent("onDesktopReload", function() {
				this.session.mid = null;
				this.session.tag = null;
				this.session.time = null;
			}.bind(this));

			BX.desktop.addCustomEvent("BXLoginSuccess", function() {
				this.restart(1000, "Desktop login");
			}.bind(this));
		}
	};

	Pull.prototype.start = function(config)
	{
		var allowConfigCaching = true;

		if(this.starting || this.isConnected())
		{
			return;
		}

		if(!this.userId && typeof(BX.message) !== 'undefined' && BX.message.USER_ID)
		{
			this.userId = BX.message.USER_ID;
			if(!this.storage)
			{
				this.storage = new StorageManager({
					userId: this.userId,
					siteId: this.siteId
				});
			}
		}
		if(this.siteId === 'none' && typeof(BX.message) !== 'undefined' && BX.message.SITE_ID)
		{
			this.siteId = BX.message.SITE_ID;
		}

		var result = new BX.Promise();

		var skipReconnectToLastSession = false;
		if (Utils.isPlainObject(config))
		{
			if (typeof config.skipReconnectToLastSession !== 'undefined')
			{
				skipReconnectToLastSession = !!config.skipReconnectToLastSession;
				delete config.skipReconnectToLastSession;
			}
			this.config = config;
			allowConfigCaching = false;
		}

		if (!this.enabled)
		{
			result.reject({
				ex: { error: 'PULL_DISABLED', error_description: 'Push & Pull server is disabled'}
			});
			return result;
		}

		var self = this;
		var now = (new Date()).getTime();
		var oldSession;
		if(!skipReconnectToLastSession && this.storage)
		{
			oldSession = this.storage.get(LS_SESSION);
		}
		if(Utils.isPlainObject(oldSession) && oldSession.hasOwnProperty('ttl') && oldSession.ttl >= now)
		{
			this.session.mid = oldSession.mid;
		}

		this.starting = true;
		this.loadConfig().catch(function(error)
		{
			self.starting = false;
			self.sendPullStatus(PullStatus.Offline);
			self.stopCheckConfig();
			console.error(Utils.getDateForLog() + ': Pull: could not read push-server config. ', error);
			result.reject(error);
		}).then(function(config)
		{
			self.setConfig(config, allowConfigCaching);
			self.init();
			self.connect();
			self.updateWatch();
			self.startCheckConfig();
			result.resolve(true);
		});

		return result;
	};

	Pull.prototype.getRestClientOptions = function()
	{
		var result = {};

		if(this.guestMode && this.guestUserId !== 0)
		{
			result.queryParams = {
				pull_guest_id: this.guestUserId
			}
		}
		return result;
	};

	Pull.prototype.setLastMessageId = function(lastMessageId)
	{
		this.session.mid = lastMessageId;
	};

	/**
	 *
	 * @param {object[]} publicIds
	 * @param {integer} publicIds.user_id
	 * @param {string} publicIds.public_id
	 * @param {string} publicIds.signature
	 * @param {Date} publicIds.start
	 * @param {Date} publicIds.end
	 */
	Pull.prototype.setPublicIds = function(publicIds)
	{
		return this.channelManager.setPublicIds(publicIds);
	};

	/**
	 * Send single message to the specified users.
	 *
	 * @param {integer[]} users User ids of the message receivers.
	 * @param {string} moduleId Name of the module to receive message,
	 * @param {string} command Command name.
	 * @param {object} params Command parameters.
	 * @param {integer} [expiry] Message expiry time in seconds.
	 * @return void
	 */
	Pull.prototype.sendMessage = function(users, moduleId, command, params, expiry)
	{
		return this.sendMessageBatch([{
			users: users,
			moduleId: moduleId,
			command: command,
			params: params,
			expiry: expiry
		}]);
	};

	/**
	 * Send single message to the specified public channels.
	 *
	 * @param {string[]} publicChannels Public ids of the channels to receive message.
	 * @param {string} moduleId Name of the module to receive message,
	 * @param {string} command Command name.
	 * @param {object} params Command parameters.
	 * @param {integer} [expiry] Message expiry time in seconds.
	 * @return void
	 */
	Pull.prototype.sendMessageToChannels = function(publicChannels, moduleId, command, params, expiry)
	{
		return this.sendMessageBatch([{
			publicChannels: publicChannels,
			moduleId: moduleId,
			command: command,
			params: params,
			expiry: expiry
		}]);
	}

	/**
	 * Sends batch of messages to the multiple public channels.
	 *
	 * @param {object[]} messageBatch Array of messages to send.
	 * @param  {int[]} messageBatch.users User ids the message receivers.
	 * @param  {string[]|object[]} messageBatch.publicChannels Public ids of the channels to send messages.
	 * @param {string} messageBatch.moduleId Name of the module to receive message,
	 * @param {string} messageBatch.command Command name.
	 * @param {object} messageBatch.params Command parameters.
	 * @param {integer} [messageBatch.expiry] Message expiry time in seconds.
	 * @return void
	 */
	Pull.prototype.sendMessageBatch = function(messageBatch)
	{
		if(!this.isPublishingEnabled())
		{
			console.error('Client publishing is not supported or is disabled');
			return false;
		}

		var userIds = {};
		for(var i = 0; i < messageBatch.length; i++)
		{
			if (messageBatch[i].users)
			{
				for(var j = 0; j < messageBatch[i].users.length; j++)
				{
					userIds[messageBatch[i].users[j]] = true;
				}
			}
		}

		this.channelManager.getPublicIds(Object.keys(userIds)).then(function(publicIds)
		{
			return this.connector.send(this.encodeMessageBatch(messageBatch, publicIds));
		}.bind(this))
	};

	Pull.prototype.encodeMessageBatch = function(messageBatch, publicIds)
	{
		var messages = [];
		messageBatch.forEach(function(messageFields)
		{
			var messageBody = {
				module_id: messageFields.moduleId,
				command: messageFields.command,
				params: messageFields.params
			};

			var receivers;
			if (messageFields.users)
			{
				receivers = this.createMessageReceivers(messageFields.users, publicIds);
			}
			else
			{
				receivers = [];
			}

			if (messageFields.publicChannels)
			{
				if (!BX.type.isArray(messageFields.publicChannels))
				{
					throw new Error('messageFields.publicChannels must be an array');
				}
				messageFields.publicChannels.forEach(function(publicChannel)
				{
					var publicId;
					var signature;
					if (typeof(publicChannel) === 'string' && publicChannel.includes('.'))
					{
						var fields = publicChannel.toString().split('.');
						publicId = fields[0];
						signature = fields[1];
					}
					else if (typeof(publicChannel) === 'object' && ('publicId' in publicChannel) && ('signature' in publicChannel))
					{
						publicId = publicChannel.publicId;
						signature = publicChannel.signature;
					}
					else
					{
						throw new Error('Public channel MUST be either a string, formatted like "{publicId}.{signature}" or an object with fields \'publicId\' and \'signature\'');
					}

					receivers.push(Receiver.create({
						id: this.encodeId(publicId),
						signature: this.encodeId(signature)
					}))
				}.bind(this))
			}

			var message = IncomingMessage.create({
				receivers: receivers,
				body: JSON.stringify(messageBody),
				expiry: messageFields.expiry || 0
			});
			messages.push(message);
		}, this);

		var requestBatch = RequestBatch.create({
			requests: [{
				incomingMessages: {
					messages: messages
				}
			}]
		});

		return RequestBatch.encode(requestBatch).finish();
	};

	Pull.prototype.createMessageReceivers = function(users, publicIds)
	{
		var result = [];
		for(var i = 0; i < users.length; i++)
		{
			var userId = users[i];
			if(!publicIds[userId] || !publicIds[userId].publicId)
			{
				throw new Error('Could not determine public id for user ' + userId);
			}

			result.push(Receiver.create({
				id: this.encodeId(publicIds[userId].publicId),
				signature: this.encodeId(publicIds[userId].signature)
			}))
		}
		return result;
	};

	Pull.prototype.restart = function(disconnectCode, disconnectReason)
	{
		var self = this;
		this.disconnect(disconnectCode, disconnectReason);
		if(this.storage)
		{
			this.storage.remove('bx-pull-config');
		}
		this.config = null;

		this.loadConfig().catch(function(error)
		{
			console.error(Utils.getDateForLog() + ': Pull: could not read push-server config', error);
			self.sendPullStatus(PullStatus.Offline);

			clearTimeout(self.reconnectTimeout);
			if(error.status == 401 || error.status == 403)
			{
				self.stopCheckConfig();

				if(BX && BX.onCustomEvent)
				{
					BX.onCustomEvent(window, 'onPullError', ['AUTHORIZE_ERROR']);
				}
			}
		}).then(function(config)
		{
			self.setConfig(config, true);
			self.connect();
			self.updateWatch();
			self.startCheckConfig();
		});
	};

	Pull.prototype.loadConfig = function ()
	{
		var result = new BX.Promise();
		if (!this.config)
		{
			this.config = {
				api: {},
				channels: {},
				publicChannels: {},
				server: { timeShift: 0 },
				clientId: null
			};

			var config;
			if(this.storage)
			{
				config = this.storage.get('bx-pull-config');
			}
			if(this.isConfigActual(config) && this.checkRevision(config.api.revision_web))
			{
				result.resolve(config);
				return result;
			}
			else if (this.storage)
			{
				this.storage.remove('bx-pull-config')
			}
		}
		else if(this.isConfigActual(this.config) && this.checkRevision(this.config.api.revision_web))
		{
			result.resolve(this.config);
			return result;
		}
		else
		{
			this.config = {
				api: {},
				channels: {},
				publicChannels: {},
				server: { timeShift: 0 },
				clientId: null
			};
		}

		this.restClient.callMethod(this.configGetMethod, {'CACHE': 'N'}).then(function(response) {
			var timeShift = 0;
			var data = response.data();
			timeShift = Math.floor((Utils.getTimestamp() - new Date(data.serverTime).getTime())/1000);
			delete data.serverTime;

			var config = Object.assign({}, data);
			config.server.timeShift = timeShift;

			result.resolve(config)
		}).catch(function(response)
		{
				var error = response.error();
				if(error.getError().error == "AUTHORIZE_ERROR" || error.getError().error == "WRONG_AUTH_TYPE")
				{
					error.status = 403;
				}
				result.reject(error);
		});

		return result;
	};

	Pull.prototype.isConfigActual = function(config)
	{
		if(!Utils.isPlainObject(config))
		{
			return false;
		}

		if(config.server.config_timestamp < this.configTimestamp)
		{
			return false;
		}

		var now = new Date();

		var channelCount = Object.keys(config.channels).length;
		if(channelCount === 0)
		{
			return false;
		}

		for(var channelType in config.channels)
		{
			if (!config.channels.hasOwnProperty(channelType))
			{
				continue;
			}

			var channel = config.channels[channelType];
			var channelEnd = new Date(channel.end);

			if(channelEnd < now)
			{
				return false;
			}
		}

		return true;
	};

	Pull.prototype.startCheckConfig = function()
	{
		if(this.checkInterval)
		{
			clearInterval(this.checkInterval);
		}

		this.checkInterval = setInterval(this.checkConfig.bind(this), CONFIG_CHECK_INTERVAL)
	};

	Pull.prototype.stopCheckConfig = function()
	{
		if(this.checkInterval)
		{
			clearInterval(this.checkInterval);
		}
		this.checkInterval = null;
	};

	Pull.prototype.checkConfig = function()
	{
		if(this.isConfigActual(this.config))
		{
			if(!this.checkRevision(this.config.api.revision_web))
			{
				return false;
			}
		}
		else
		{
			this.logToConsole("Stale config detected. Restarting");
			this.restart(CloseReasons.CONFIG_EXPIRED, "Config update required");
		}
	};

	Pull.prototype.setConfig = function(config, allowCaching)
	{
		for (var key in config)
		{
			if(config.hasOwnProperty(key) && this.config.hasOwnProperty(key))
			{
				this.config[key] = config[key];
			}
		}

		if (config.publicChannels)
		{
			this.setPublicIds(Utils.objectValues(config.publicChannels));
		}

		if(this.storage && allowCaching)
		{
			try
			{
				this.storage.set('bx-pull-config', config);
			}
			catch (e)
			{
				// try to delete the key "history" (landing site change history, see http://jabber.bx/view.php?id=136492)
				if (localStorage && localStorage.removeItem)
				{
					localStorage.removeItem('history');
				}
				console.error(Utils.getDateForLog() + " Pull: Could not cache config in local storage. Error: ", e);
			}
		}
	};

	Pull.prototype.isWebSocketSupported = function()
	{
		return typeof(window.WebSocket) !== "undefined";
	};

	Pull.prototype.isWebSocketAllowed = function()
	{
		if(this.sharedConfig.isWebSocketBlocked())
		{
			return false;
		}

		return this.isWebSocketEnabled();
	};

	Pull.prototype.isWebSocketEnabled = function()
	{
		if(!this.isWebSocketSupported())
		{
			return false;
		}

		return (this.config && this.config.server && this.config.server.websocket_enabled === true);
	};

	Pull.prototype.isPublishingSupported = function ()
	{
		return this.getServerVersion() > 3;
	};

	Pull.prototype.isPublishingEnabled = function ()
	{
		if(!this.isPublishingSupported())
		{
			return false;
		}

		return (this.config && this.config.server && this.config.server.publish_enabled === true);
	};

	Pull.prototype.isProtobufSupported = function()
	{
		return (this.getServerVersion() > 3 && !Utils.browser.IsIe());
	};

	Pull.prototype.isSharedMode = function()
	{
		return (this.getServerMode() == ServerMode.Shared)
	};

	Pull.prototype.disconnect = function(disconnectCode, disconnectReason)
	{
		if(this.connector)
		{
			this.isManualDisconnect = true;
			this.connector.disconnect(disconnectCode, disconnectReason);
		}
	};

	Pull.prototype.stop = function(disconnectCode, disconnectReason)
	{
		this.disconnect(disconnectCode, disconnectReason);
		this.stopCheckConfig();
	};

	Pull.prototype.reconnect = function(disconnectCode, disconnectReason, delay)
	{
		this.disconnect(disconnectCode, disconnectReason);

		delay = delay || 1;
		this.scheduleReconnect(delay);
	};

	Pull.prototype.restoreWebSocketConnection = function()
	{
		if(this.connectionType == ConnectionType.WebSocket)
		{
			return true;
		}

		this._connectors.webSocket.connect();
	};

	Pull.prototype.scheduleReconnect = function(connectionDelay)
	{
		if(!this.enabled)
			return false;

		if(!connectionDelay)
		{
			if(this.connectionAttempt > 3 && this.connectionType === ConnectionType.WebSocket && !this.sharedConfig.isLongPollingBlocked())
			{
				// Websocket seems to be closed by network filter. Trying to fallback to long polling
				this.sharedConfig.setWebSocketBlocked(true);
				this.connectionType = ConnectionType.LongPolling;
				this.connectionAttempt = 1;
				connectionDelay = 1;
			}
			else
			{
				connectionDelay = this.getConnectionAttemptDelay(this.connectionAttempt);
			}
		}
		if(this.reconnectTimeout)
		{
			clearTimeout(this.reconnectTimeout);
		}

		this.logToConsole('Pull: scheduling reconnection in ' + connectionDelay + ' seconds; attempt # ' + this.connectionAttempt);

		this.reconnectTimeout = setTimeout(this.connect.bind(this), connectionDelay * 1000);
	};

	Pull.prototype.scheduleRestoreWebSocketConnection = function()
	{
		this.logToConsole('Pull: scheduling restoration of websocket connection in ' + RESTORE_WEBSOCKET_TIMEOUT + ' seconds');

		var self = this;
		if(this.restoreWebSocketTimeout)
		{
			return;
		}

		this.restoreWebSocketTimeout = setTimeout(function()
		{
			self.restoreWebSocketTimeout = 0;
			self.restoreWebSocketConnection();
		}, RESTORE_WEBSOCKET_TIMEOUT * 1000);
	};

	Pull.prototype.connect = function()
	{
		if(!this.enabled || this.connector.connected)
		{
			return false;
		}

		if(this.reconnectTimeout)
		{
			clearTimeout(this.reconnectTimeout);
		}

		this.sendPullStatus(PullStatus.Connecting);
		this.connectionAttempt++;
		this.connector.connect();
	};

	Pull.prototype.parseResponse = function (response)
	{
		var events = this.extractMessages(response);
		var messages = [];
		if (events.length === 0)
		{
			this.session.mid = null;
			return;
		}

		for (var i = 0; i < events.length; i++)
		{
			var event = events[i];
			if (event.mid && this.session.lastMessageIds.includes(event.mid))
			{
				console.warn("Duplicate message " + event.mid + " skipped");
				continue;
			}

			this.session.mid = event.mid || null;
			this.session.tag = event.tag || null;
			this.session.time = event.time || null;
			if (event.mid)
			{
				this.session.lastMessageIds.push(event.mid);
			}
			messages.push(event.text);

			if (!this.session.history[event.text.module_id])
			{
				this.session.history[event.text.module_id] = {};
			}
			if (!this.session.history[event.text.module_id][event.text.command])
			{
				this.session.history[event.text.module_id][event.text.command] = 0;
			}
			this.session.history[event.text.module_id][event.text.command]++;
			this.session.messageCount++;
		}

		if (this.session.lastMessageIds.length > MAX_IDS_TO_STORE)
		{
			this.session.lastMessageIds = this.session.lastMessageIds.slice( - MAX_IDS_TO_STORE);
		}
		this.broadcastMessages(messages);
	};

	Pull.prototype.extractMessages = function (pullEvent)
	{
		if(pullEvent instanceof ArrayBuffer)
		{
			return this.extractProtobufMessages(pullEvent);
		}
		else if(Utils.isNotEmptyString(pullEvent))
		{
			return this.extractPlainTextMessages(pullEvent)
		}
	};

	Pull.prototype.extractProtobufMessages = function(pullEvent)
	{
		var result = [];
		try
		{
			var responseBatch = ResponseBatch.decode(new Uint8Array(pullEvent));
			for (var i = 0; i < responseBatch.responses.length; i++)
			{
				var response = responseBatch.responses[i];
				if (response.command != "outgoingMessages")
				{
					continue;
				}

				var messages = responseBatch.responses[i].outgoingMessages.messages;
				for (var m = 0; m < messages.length; m++)
				{
					var message = messages[m];
					var messageFields;
					try
					{
						messageFields = JSON.parse(message.body)
					}
					catch (e)
					{
						console.error(Utils.getDateForLog() + ": Pull: Could not parse message body", e);
						continue;
					}

					if(!messageFields.extra)
					{
						messageFields.extra = {}
					}
					messageFields.extra.sender = {
						type: message.sender.type
					};

					if(message.sender.id instanceof Uint8Array)
					{
						messageFields.extra.sender.id = this.decodeId(message.sender.id)
					}

					var compatibleMessage = {
						mid: this.decodeId(message.id),
						text: messageFields
					};

					result.push(compatibleMessage);
				}
			}
		}
		catch(e)
		{
			console.error(Utils.getDateForLog() + ": Pull: Could not parse message", e)
		}
		return result;
	};

	Pull.prototype.extractPlainTextMessages = function(pullEvent)
	{
		var result = [];
		var dataArray = pullEvent.match(/#!NGINXNMS!#(.*?)#!NGINXNME!#/gm);
		if (dataArray === null)
		{
			text = "\n========= PULL ERROR ===========\n"+
				"Error type: parseResponse error parsing message\n"+
				"\n"+
				"Data string: " + pullEvent + "\n"+
				"================================\n\n";
			console.warn(text);
			return result;
		}
		for (var i = 0; i < dataArray.length; i++)
		{
			dataArray[i] = dataArray[i].substring(12, dataArray[i].length - 12);
			if (dataArray[i].length <= 0)
			{
				continue;
			}

			try
			{
				var data = JSON.parse(dataArray[i])
			}
			catch(e)
			{
				continue;
			}

			result.push(data);
		}
		return result;
	};

	/**
	 * Converts message id from byte[] to string
	 * @param {Uint8Array} encodedId
	 * @return {string}
	 */
	Pull.prototype.decodeId = function(encodedId)
	{
		if(!(encodedId instanceof Uint8Array))
		{
			throw new Error("encodedId should be an instance of Uint8Array");
		}

		var result = "";
		for (var i = 0; i < encodedId.length; i++)
		{
			var hexByte = encodedId[i].toString(16);
			if (hexByte.length === 1)
			{
				result += '0';
			}
			result += hexByte;
		}
		return result;
	};

	/**
	 * Converts message id from hex-encoded string to byte[]
	 * @param {string} id Hex-encoded string.
	 * @return {Uint8Array}
	 */
	Pull.prototype.encodeId = function(id)
	{
		if (!id)
		{
			return new Uint8Array();
		}

		var result = [];
		for (var i = 0; i < id.length; i += 2)
		{
			result.push(parseInt(id.substr(i, 2), 16));
		}

		return new Uint8Array(result);
	};

	Pull.prototype.broadcastMessages = function (messages)
	{
		messages.forEach(function (message)
		{
			var moduleId = message.module_id = message.module_id.toLowerCase();
			var command = message.command;

			if(!message.extra)
			{
				message.extra = {};
			}

			if(message.extra.server_time_unix)
			{
				message.extra.server_time_ago = ((Utils.getTimestamp() - (message.extra.server_time_unix * 1000)) / 1000)-(this.config.server.timeShift? this.config.server.timeShift: 0);
				message.extra.server_time_ago = message.extra.server_time_ago > 0 ? message.extra.server_time_ago : 0;
			}

			this.logMessage(message);
			try
			{
				if(message.extra.sender && message.extra.sender.type === SenderType.Client)
				{
					if (typeof BX.onCustomEvent !== 'undefined')
					{
						BX.onCustomEvent(window, 'onPullClientEvent-' + moduleId, [command, message.params, message.extra], true);
						BX.onCustomEvent(window, 'onPullClientEvent', [moduleId, command, message.params, message.extra], true);
					}

					this.emit({
						type: SubscriptionType.Client,
						moduleId: moduleId,
						data: {
							command: command,
							params: Utils.clone(message.params),
							extra: Utils.clone(message.extra)
						}
					});
				}
				else if (moduleId === 'pull')
				{
					this.handleInternalPullEvent(command, message);
				}
				else if (moduleId == 'online')
				{
					if (message.extra.server_time_ago < 240)
					{
						if (typeof BX.onCustomEvent !== 'undefined')
						{
							BX.onCustomEvent(window, 'onPullOnlineEvent', [command, message.params, message.extra], true);
						}

						this.emit({
							type: SubscriptionType.Online,
							data: {
								command: command,
								params: Utils.clone(message.params),
								extra: Utils.clone(message.extra)
							}
						});
					}
				}
				else
				{
					if (typeof BX.onCustomEvent !== 'undefined')
					{
						BX.onCustomEvent(window, 'onPullEvent-' + moduleId, [command, message.params, message.extra], true);
						BX.onCustomEvent(window, 'onPullEvent', [moduleId, command, message.params, message.extra], true);
					}

					this.emit({
						type: SubscriptionType.Server,
						moduleId: moduleId,
						data: {
							command: command,
							params: Utils.clone(message.params),
							extra: Utils.clone(message.extra)
						}
					});
				}
			}
			catch(e)
			{
				if (typeof(console) == 'object')
				{
					console.warn(
						"\n========= PULL ERROR ===========\n"+
						"Error type: broadcastMessages execute error\n"+
						"Error event: ", e, "\n"+
						"Message: ", message, "\n"+
						"================================\n"
					);
					if (typeof BX.debug !== 'undefined')
					{
						BX.debug(e);
					}
				}
			}

			if(message.extra && message.extra.revision_web)
			{
				this.checkRevision(message.extra.revision_web);
			}
		}, this);
	};

	Pull.prototype.logToConsole = function(message)
	{
		if(this.loggingEnabled)
		{
			console.log(Utils.getDateForLog() + ': ' + message);
		}
	};

	Pull.prototype.logMessage = function(message)
	{
		if(!this.debug)
		{
			return;
		}

		if(message.extra.sender && message.extra.sender.type === SenderType.Client)
		{
			console.info('onPullClientEvent-' + message.module_id, message.command, message.params, message.extra);
		}
		else if (message.moduleId == 'online')
		{
			console.info('onPullOnlineEvent', message.command, message.params, message.extra);
		}
		else
		{
			console.info('onPullEvent', message.module_id, message.command, message.params, message.extra);
		}
	};

	Pull.prototype.onLongPollingOpen = function()
	{
		this.unloading = false;
		this.starting = false;
		this.connectionAttempt = 0;
		this.isManualDisconnect = false;
		this.sendPullStatus(PullStatus.Online);

		if(this.offlineTimeout)
		{
			clearTimeout(this.offlineTimeout);
			this.offlineTimeout = null;
		}

		this.logToConsole('Pull: Long polling connection with push-server opened');
		if(this.isWebSocketEnabled())
		{
			this.scheduleRestoreWebSocketConnection();
		}
	};

	Pull.prototype.onWebSocketBlockChanged = function(e)
	{
		var isWebSocketBlocked = e.isWebSocketBlocked;

		if(isWebSocketBlocked && this.connectionType === ConnectionType.WebSocket && !this.isConnected())
		{
			clearTimeout(this.reconnectTimeout);

			this.connectionAttempt = 0;
			this.connectionType = ConnectionType.LongPolling;
			this.scheduleReconnect(1);
		}
		else if(!isWebSocketBlocked && this.connectionType === ConnectionType.LongPolling)
		{
			clearTimeout(this.reconnectTimeout);
			clearTimeout(this.restoreWebSocketTimeout);

			this.connectionAttempt = 0;
			this.connectionType = ConnectionType.WebSocket;
			this.scheduleReconnect(1);
		}
	};

	Pull.prototype.onWebSocketOpen = function()
	{
		this.unloading = false;
		this.starting = false;
		this.connectionAttempt = 0;
		this.isManualDisconnect = false;
		this.sendPullStatus(PullStatus.Online);
		this.sharedConfig.setWebSocketBlocked(false);

		// to prevent fallback to long polling in case of networking problems
		this.sharedConfig.setLongPollingBlocked(true);

		if(this.connectionType == ConnectionType.LongPolling)
		{
			this.connectionType = ConnectionType.WebSocket;
			this._connectors.longPolling.disconnect();
		}

		if(this.offlineTimeout)
		{
			clearTimeout(this.offlineTimeout);
			this.offlineTimeout = null;
		}
		if (this.restoreWebSocketTimeout)
		{
			clearTimeout(this.restoreWebSocketTimeout);
			this.restoreWebSocketTimeout = null;
		}
		this.logToConsole('Pull: Websocket connection with push-server opened');
	};

	Pull.prototype.onWebSocketDisconnect = function(e)
	{
		if(this.connectionType === ConnectionType.WebSocket)
		{
			if(e.code != CloseReasons.CONFIG_EXPIRED && e.code != CloseReasons.CHANNEL_EXPIRED && e.code != CloseReasons.CONFIG_REPLACED)
			{
				this.sendPullStatus(PullStatus.Offline);
			}
			else
			{
				this.offlineTimeout = setTimeout(function()
				{
					this.sendPullStatus(PullStatus.Offline);
				}.bind(this), 5000)
			}
		}

		if(!e)
		{
			e = {};
		}

		this.logToConsole('Pull: Websocket connection with push-server closed. Code: ' + e.code + ', reason: ' + e.reason);
		if(!this.isManualDisconnect)
		{
			this.scheduleReconnect();
		}

		// to prevent fallback to long polling in case of networking problems
		this.sharedConfig.setLongPollingBlocked(true);
		this.isManualDisconnect = false;
	};

	Pull.prototype.onWebSocketError = function(e)
	{
		this.starting = false;
		if(this.connectionType === ConnectionType.WebSocket)
		{
			this.sendPullStatus(PullStatus.Offline);
		}

		console.error(Utils.getDateForLog() + ": Pull: WebSocket connection error", e);
		this.scheduleReconnect();
	};

	Pull.prototype.onLongPollingDisconnect = function(e)
	{
		if(this.connectionType === ConnectionType.LongPolling)
		{
			if(e.code != CloseReasons.CONFIG_EXPIRED && e.code != CloseReasons.CHANNEL_EXPIRED && e.code != CloseReasons.CONFIG_REPLACED)
			{
				this.sendPullStatus(PullStatus.Offline);
			}
			else
			{
				this.offlineTimeout = setTimeout(function()
				{
					this.sendPullStatus(PullStatus.Offline);
				}.bind(this), 5500)
			}
		}

		if(!e)
		{
			e = {};
		}

		this.logToConsole('Pull: Long polling connection with push-server closed. Code: ' + e.code + ', reason: ' + e.reason);
		if(!this.isManualDisconnect)
		{
			this.scheduleReconnect();
		}
		this.isManualDisconnect = false;
	};

	Pull.prototype.onLongPollingError = function(e)
	{
		this.starting = false;
		if(this.connectionType === ConnectionType.LongPolling)
		{
			this.sendPullStatus(PullStatus.Offline);
		}
		console.error(Utils.getDateForLog() + ': Pull: Long polling connection error', e);
		this.scheduleReconnect();
	};

	Pull.prototype.isConnected = function()
	{
		return this.connector ? this.connector.connected : false;
	};

	Pull.prototype.onBeforeUnload = function()
	{
		this.unloading = true;

		var session = Utils.clone(this.session);
		session.ttl = (new Date()).getTime() + LS_SESSION_CACHE_TIME * 1000;
		if(this.storage)
		{
			try
			{
				this.storage.set(LS_SESSION, JSON.stringify(session), LS_SESSION_CACHE_TIME);
			}
			catch (e)
			{
				console.error(Utils.getDateForLog() + " Pull: Could not save session info in local storage. Error: ", e);
			}
		}

		this.scheduleReconnect(15);
	};

	Pull.prototype.onOffline = function()
	{
		this.disconnect("1000", "offline");
	};

	Pull.prototype.onOnline = function()
	{
		this.connect();
	};

	Pull.prototype.handleInternalPullEvent = function(command, message)
	{
		switch (command.toUpperCase())
		{
			case SystemCommands.CHANNEL_EXPIRE:
			{
				if (message.params.action == 'reconnect')
				{
					this.config.channels[message.params.channel.type] = message.params.new_channel;
					this.logToConsole("Pull: new config for " + message.params.channel.type + " channel set:\n", this.config.channels[message.params.channel.type]);

					this.reconnect(CloseReasons.CONFIG_REPLACED, "config was replaced");
				}
				else
				{
					this.restart(CloseReasons.CHANNEL_EXPIRED, "channel expired");
				}
				break;
			}
			case SystemCommands.CONFIG_EXPIRE:
			{
				this.restart(CloseReasons.CONFIG_EXPIRED, "config expired");
				break;
			}
			case SystemCommands.SERVER_RESTART:
			{
				this.reconnect(CloseReasons.SERVER_RESTARTED, "server was restarted", 15);
				break;
			}
			default://
		}
	};

	Pull.prototype.checkRevision = function(serverRevision)
	{
		if (this.skipCheckRevision)
		{
			return true;
		}

		serverRevision = parseInt(serverRevision);
		if (serverRevision > 0 && serverRevision != REVISION)
		{
			this.enabled = false;
			if (typeof BX.message !== 'undefined')
			{
				this.showNotification(BX.message('PULL_OLD_REVISION'));
			}
			this.disconnect(CloseReasons.NORMAL_CLOSURE, 'check_revision');

			if (typeof BX.onCustomEvent !== 'undefined')
			{
				BX.onCustomEvent(window, 'onPullRevisionUp', [serverRevision, REVISION]);
			}

			this.emit({
				type: SubscriptionType.Revision,
				data: {
					server: serverRevision,
					client: REVISION
				}
			});

			this.logToConsole("Pull revision changed from " + REVISION + " to " + serverRevision + ". Reload required");

			return false;
		}
		return true;
	};

	Pull.prototype.showNotification = function(text)
	{
		var self = this;
		if (this.notificationPopup || typeof BX.PopupWindow === 'undefined')
			return;

		this.notificationPopup = new BX.PopupWindow('bx-notifier-popup-confirm', null, {
			zIndex: 200,
			autoHide: false,
			closeByEsc: false,
			overlay: true,
			content : BX.create("div", {
				props: {className: "bx-messenger-confirm"},
				html: text
			}),
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message('JS_CORE_WINDOW_CLOSE'),
					className: "popup-window-button-decline",
					events: {
						click: function(e)
						{
							self.notificationPopup.close();
						}
					}
				})
			],
			events: {
				onPopupClose: function()
				{
					this.destroy()
				},
				onPopupDestroy: function()
				{
					self.notificationPopup = null;
				}
			}
		});
		this.notificationPopup.show();
	};

	Pull.prototype.getRevision = function()
	{
		return (this.config && this.config.api) ? this.config.api.revision_web : null;
	};

	Pull.prototype.getServerVersion = function()
	{
		return (this.config && this.config.server) ? this.config.server.version : 0;
	};

	Pull.prototype.getServerMode = function()
	{
		return (this.config && this.config.server) ? this.config.server.mode : null;
	};

	Pull.prototype.getConfig = function()
	{
		return this.config;
	};

	Pull.prototype.getDebugInfo = function()
	{
		if (!console || !console.info || !JSON || !JSON.stringify)
			return false;

		var configDump;

		if(this.config && this.config.channels && this.config.channels.private)
		{
			configDump = "ChannelID: " + this.config.channels.private.id + "\n" +
				"ChannelDie: " + this.config.channels.private.end + "\n" +
				("shared" in this.config.channels ? "ChannelDieShared: " + this.config.channels.shared.end : "");
		}
		else
		{
			configDump = "Config error: config is not loaded";
		}

		var watchTagsDump = JSON.stringify(this.watchTagsQueue);
		var text = "\n========= PULL DEBUG ===========\n"+
			"UserId: " + this.userId + " " + (this.userId > 0 ?  '': '(guest)') + "\n" +
			(this.guestMode && this.guestUserId !== 0? "Guest userId: " + this.guestUserId + "\n":"") +
			"Browser online: " + (navigator.onLine ? 'Y' : 'N') + "\n" +
			"Connect: " + (this.isConnected() ? 'Y': 'N') + "\n" +
			"Server type: " + (this.isSharedMode() ? 'cloud' : 'local') + "\n" +
			"WebSocket support: " + (this.isWebSocketSupported() ? 'Y': 'N') + "\n" +
			"WebSocket connect: " + (this._connectors.webSocket && this._connectors.webSocket.connected ? 'Y': 'N') + "\n"+
			"WebSocket mode: " + (this._connectors.webSocket && this._connectors.webSocket.socket ? (this._connectors.webSocket.socket.url.search("binaryMode=true") != -1 ? "protobuf" : "text") : '-') + "\n"+

			"Try connect: " + (this.reconnectTimeout? 'Y': 'N') + "\n" +
			"Try number: " + (this.connectionAttempt) + "\n" +
			"\n"+
			"Path: " + (this.connector ? this.connector.path : '-') + "\n" +
			configDump + "\n" +
			"\n"+
			"Last message: " + (this.session.mid > 0? this.session.mid : '-') + "\n" +
			"Session history: " + JSON.stringify(this.session.history) + "\n" +
			"Watch tags: " + (watchTagsDump == '{}'? '-' : watchTagsDump) + "\n"+
			"================================\n";

		return console.info(text);
	};

	Pull.prototype.enableLogging = function(loggingFlag)
	{
		if(loggingFlag === undefined)
		{
			loggingFlag = true;
		}
		loggingFlag = loggingFlag === true;

		this.sharedConfig.setLoggingEnabled(loggingFlag);
		this.loggingEnabled = loggingFlag;
	};

	Pull.prototype.capturePullEvent = function(debugFlag)
	{
		if(debugFlag === undefined)
		{
			debugFlag = true;
		}

		this.debug = debugFlag;
	};

	Pull.prototype.getConnectionPath = function(connectionType)
	{
		var path;

		switch(connectionType)
		{
			case ConnectionType.WebSocket:
				path = this.isSecure? this.config.server.websocket_secure: this.config.server.websocket;
				break;
			case ConnectionType.LongPolling:
				path = this.isSecure? this.config.server.long_pooling_secure: this.config.server.long_polling;
				break;
			default:
				throw new Error("Unknown connection type " + connectionType);
		}

		if(!Utils.isNotEmptyString(path))
		{
			return false;
		}

		var channels = [];
		['private', 'shared'].forEach(function(type)
		{
			if (typeof this.config.channels[type] !== 'undefined')
			{
				channels.push(this.config.channels[type].id);
			}
		}, this);

		if(channels.length === 0)
		{
			 return false;
		}

		var params = {
			CHANNEL_ID: channels.join('/')
		};

		if(this.isProtobufSupported())
		{
			params.binaryMode = 'true';
		}
		if (this.isSharedMode())
		{
			if(!this.config.clientId)
			{
				throw new Error("Push-server is in shared mode, but clientId is not set");
			}
			params.clientId = this.config.clientId;
		}
		if (this.session.mid)
		{
			params.mid = this.session.mid;
		}
		if (this.session.tag)
		{
			params.tag = this.session.tag;
		}
		if (this.session.time)
		{
			params.time = this.session.time;
		}
		params.revision = REVISION;

		return path + '?' + Utils.buildQueryString(params);
	};

	Pull.prototype.getPublicationPath = function()
	{
		var path = this.isSecure? this.config.server.publish_secure: this.config.server.publish;
		if(!path)
		{
			return '';
		}

		var channels = [];
		for (var type in this.config.channels)
		{
			if (!this.config.channels.hasOwnProperty(type))
			{
				continue;
			}
			channels.push(this.config.channels[type].id);
		}

		var params = {
			CHANNEL_ID: channels.join('/')
		};

		return path + '?' + Utils.buildQueryString(params);
	};

	/**
	 * Returns reconnect delay in seconds
	 * @param attemptNumber
	 * @return {number}
	 */
	Pull.prototype.getConnectionAttemptDelay = function(attemptNumber)
	{
		var result;
		if(attemptNumber < 1)
		{
			result = 0.5;
		}
		else if(attemptNumber < 3)
		{
			result = 15;
		}
		else if(attemptNumber < 5)
		{
			result = 45;
		}
		else if (attemptNumber < 10)
		{
			result = 600;
		}
		else
		{
			result = 3600;
		}

		return result + (result * Math.random() * 0.2);
	};

	Pull.prototype.sendPullStatus = function(status)
	{
		if(this.unloading)
		{
			return;
		}

		if (typeof BX.onCustomEvent !== 'undefined')
		{
			BX.onCustomEvent(window, 'onPullStatus', [status]);
		}

		this.emit({
			type: SubscriptionType.Status,
			data: {
				status: status
			}
		});
	};

	Pull.prototype.extendWatch = function (tag, force)
	{
		if (!tag || this.watchTagsQueue[tag])
		{
			return false;
		}

		this.watchTagsQueue[tag] = true;
		if (force)
		{
			this.updateWatch(force);
		}
	};

	Pull.prototype.updateWatch = function (force)
	{
		clearTimeout(this.watchUpdateTimeout);
		this.watchUpdateTimeout = setTimeout(function ()
		{
			var watchTags = Object.keys(this.watchTagsQueue);
			if (watchTags.length > 0)
			{
				this.restClient.callMethod('pull.watch.extend', {tags: watchTags}, function(result)
				{
					if(result.error())
					{
						this.updateWatch();

						return false;
					}

					var updatedTags = result.data();

					for (var tagId in updatedTags)
					{
						if (updatedTags.hasOwnProperty(tagId) && !updatedTags[tagId])
						{
							this.clearWatch(tagId);
						}
					}
					this.updateWatch();

				}.bind(this))
			}
			else
			{
				this.updateWatch();
			}
		}.bind(this), force ? this.watchForceUpdateInterval : this.watchUpdateInterval);
	};

	Pull.prototype.clearWatch = function (tagId)
	{
		delete this.watchTagsQueue[tagId];
	};

	// old functions, not used anymore.
	Pull.prototype.setPrivateVar = function(){};
	Pull.prototype.returnPrivateVar = function(){};
	Pull.prototype.expireConfig = function(){};
	Pull.prototype.updateChannelID = function(){};
	Pull.prototype.tryConnect = function(){};
	Pull.prototype.tryConnectDelay = function(){};
	Pull.prototype.tryConnectSet = function(){};
	Pull.prototype.updateState = function(){};
	Pull.prototype.setUpdateStateStepCount = function(){};
	Pull.prototype.supportWebSocket = function()
	{
		return this.isWebSocketSupported();
	};
	Pull.prototype.isWebSoketConnected = function()
	{
		return this.isConnected() && this.connectionType == ConnectionType.WebSocket;
	};
	Pull.prototype.getPullServerStatus = function(){return this.isConnected()};
	Pull.prototype.closeConfirm = function()
	{
		if (this.notificationPopup)
		{
			this.notificationPopup.destroy();
		}
	};

	var SharedConfig = function(params)
	{
		params = params || {};
		this.storage = params.storage || new StorageManager();

		this.ttl = 24 * 60 * 60;

		this.lsKeys = {
			websocketBlocked: 'bx-pull-websocket-blocked',
			longPollingBlocked: 'bx-pull-longpolling-blocked',
			loggingEnabled: 'bx-pull-logging-enabled'
		};

		this.callbacks = {
			onWebSocketBlockChanged: (Utils.isFunction(params.onWebSocketBlockChanged) ? params.onWebSocketBlockChanged : function(){})
		};

		if (this.storage)
		{
			window.addEventListener('storage', this.onLocalStorageSet.bind(this));
		}
	};

	SharedConfig.prototype.onLocalStorageSet = function(params)
	{
		if(
			this.storage.compareKey(params.key, this.lsKeys.websocketBlocked)
			&& params.newValue != params.oldValue
		)
		{
			this.callbacks.onWebSocketBlockChanged({
				isWebSocketBlocked: this.isWebSocketBlocked()
			})
		}
	};

	SharedConfig.prototype.isWebSocketBlocked = function()
	{
		if (!this.storage)
		{
			return false;
		}

		return this.storage.get(this.lsKeys.websocketBlocked, 0) > Utils.getTimestamp();
	};

	SharedConfig.prototype.setWebSocketBlocked = function(isWebSocketBlocked)
	{
		if (!this.storage)
		{
			return false;
		}

		try
		{
			this.storage.set(this.lsKeys.websocketBlocked, (isWebSocketBlocked ? Utils.getTimestamp()+this.ttl : 0));
		}
		catch (e)
		{
			console.error(Utils.getDateForLog() + " Pull: Could not save WS_blocked flag in local storage. Error: ", e);
		}
	};

	SharedConfig.prototype.isLongPollingBlocked = function()
	{
		if (!this.storage)
		{
			return false;
		}

		return this.storage.get(this.lsKeys.longPollingBlocked, 0) > Utils.getTimestamp();
	};

	SharedConfig.prototype.setLongPollingBlocked = function(isLongPollingBlocked)
	{
		if (!this.storage)
		{
			return false;
		}

		try
		{
			this.storage.set(this.lsKeys.longPollingBlocked, (isLongPollingBlocked ? Utils.getTimestamp()+this.ttl : 0));
		}
		catch (e)
		{
			console.error(Utils.getDateForLog() + " Pull: Could not save LP_blocked flag in local storage. Error: ", e);
		}
	};

	SharedConfig.prototype.isLoggingEnabled = function()
	{
		if (!this.storage)
		{
			return false;
		}

		return this.storage.get(this.lsKeys.loggingEnabled, 0) > Utils.getTimestamp();
	};

	SharedConfig.prototype.setLoggingEnabled = function(isLoggingEnabled)
	{
		if (!this.storage)
		{
			return false;
		}

		try
		{
			this.storage.set(this.lsKeys.loggingEnabled, (isLoggingEnabled ? Utils.getTimestamp()+this.ttl : 0));
		}
		catch (e)
		{
			console.error("LocalStorage error: ", e);
			return false;
		}
	};

	var ObjectExtend = function(child, parent)
	{
		var f = function() {};
		f.prototype = parent.prototype;

		child.prototype = new f();
		child.prototype.constructor = child;

		child.superclass = parent.prototype;
		if(parent.prototype.constructor == Object.prototype.constructor)
		{
			parent.prototype.constructor = parent;
		}
	};

	var AbstractConnector = function(config)
	{
		this.parent = config.parent;
		this.callbacks = {
			onOpen: Utils.isFunction(config.onOpen) ? config.onOpen : function() {},
			onDisconnect: Utils.isFunction(config.onDisconnect) ? config.onDisconnect : function() {},
			onError: Utils.isFunction(config.onError) ? config.onError : function() {},
			onMessage: Utils.isFunction(config.onMessage) ? config.onMessage : function() {}
		};

		this._connected = false;
		this.connectionType = "";

		this.disconnectCode = '';
		this.disconnectReason = '';

		Object.defineProperty(this, "connected", {
			get: function()
			{
				return this._connected
			},
			set: function(connected)
			{
				if(connected == this._connected)
					return;

				this._connected = connected;

				if(this._connected)
				{
					this.callbacks.onOpen();
				}
				else
				{
					this.callbacks.onDisconnect({
						code: this.disconnectCode,
						reason: this.disconnectReason
					});
				}
			}
		});

		Object.defineProperty(this, "path", {
			get: function()
			{
				return this.parent.getConnectionPath(this.connectionType);
			}
		})
	};

	var WebSocketConnector = function(config)
	{
		WebSocketConnector.superclass.constructor.apply(this, arguments);
		this.connectionType = ConnectionType.WebSocket;
		this.socket = null;

		this.onSocketOpenHandler = this.onSocketOpen.bind(this);
		this.onSocketCloseHandler = this.onSocketClose.bind(this);
		this.onSocketErrorHandler = this.onSocketError.bind(this);
		this.onSocketMessageHandler = this.onSocketMessage.bind(this);
	};

	ObjectExtend(WebSocketConnector, AbstractConnector);

	WebSocketConnector.prototype.connect = function()
	{
		if(this.socket)
		{
			if(this.socket.readyState === 1)
			{
				// already connected
				return true;
			}
			else
			{
				this.socket.removeEventListener('open', this.onSocketOpenHandler);
				this.socket.removeEventListener('close', this.onSocketCloseHandler);
				this.socket.removeEventListener('error', this.onSocketErrorHandler);
				this.socket.removeEventListener('message', this.onSocketMessageHandler);

				this.socket.close();
				this.socket = null;
			}
		}

		this.createSocket();
	};

	WebSocketConnector.prototype.disconnect = function(code, message)
	{
		if (this.socket !== null)
		{
			this.socket.removeEventListener('open', this.onSocketOpenHandler);
			this.socket.removeEventListener('close', this.onSocketCloseHandler);
			this.socket.removeEventListener('error', this.onSocketErrorHandler);
			this.socket.removeEventListener('message', this.onSocketMessageHandler);

			this.socket.close(code, message);
		}
		this.socket = null;
		this.disconnectCode = code;
		this.disconnectReason = message;
		this.connected = false;
	};

	WebSocketConnector.prototype.createSocket = function()
	{
		if(this.socket)
		{
			throw new Error("Socket already exists");
		}

		if(!this.path)
		{
			throw new Error("Websocket connection path is not defined");
		}

		this.socket = new WebSocket(this.path);
		this.socket.binaryType = 'arraybuffer';

		this.socket.addEventListener('open', this.onSocketOpenHandler);
		this.socket.addEventListener('close', this.onSocketCloseHandler);
		this.socket.addEventListener('error', this.onSocketErrorHandler);
		this.socket.addEventListener('message', this.onSocketMessageHandler);
	};

	/**
	 * Sends some data to the server via websocket connection.
	 * @param {ArrayBuffer} buffer Data to send.
	 * @return {boolean}
	 */
	WebSocketConnector.prototype.send = function(buffer)
	{
		if(!this.socket || this.socket.readyState !== 1)
		{
			console.error(Utils.getDateForLog() + ": Pull: WebSocket is not connected");
			return false;
		}

		this.socket.send(buffer);
	};

	WebSocketConnector.prototype.onSocketOpen = function()
	{
		this.connected = true;
	};

	WebSocketConnector.prototype.onSocketClose = function(e)
	{
		this.socket = null;
		this.disconnectCode = e.code;
		this.disconnectReason = e.reason;
		this.connected = false;
	};

	WebSocketConnector.prototype.onSocketError = function(e)
	{
		this.callbacks.onError(e);
	};

	WebSocketConnector.prototype.onSocketMessage = function(e)
	{
		this.callbacks.onMessage(e.data);
	};

	WebSocketConnector.prototype.destroy = function()
	{
		if(this.socket)
		{
			this.socket.close();
			this.socket = null;
		}
	};

	var LongPollingConnector = function(config)
	{
		LongPollingConnector.superclass.constructor.apply(this, arguments);

		this.active = false;
		this.connectionType = ConnectionType.LongPolling;
		this.requestTimeout = null;
		this.failureTimeout = null;
		this.xhr = this.createXhr();
		this.requestAborted = false;
	};

	ObjectExtend(LongPollingConnector, AbstractConnector);

	LongPollingConnector.prototype.createXhr = function()
	{
		var result = new XMLHttpRequest();
		if(this.parent.isProtobufSupported())
		{
			result.responseType = "arraybuffer";
		}
		result.addEventListener("readystatechange", this.onXhrReadyStateChange.bind(this));
		return result;
	};

	LongPollingConnector.prototype.connect = function()
	{
		this.active = true;
		this.performRequest();
	};

	LongPollingConnector.prototype.disconnect = function(code, reason)
	{
		this.active = false;

		if(this.failureTimeout)
		{
			clearTimeout(this.failureTimeout);
			this.failureTimeout = null;
		}
		if(this.requestTimeout)
		{
			clearTimeout(this.requestTimeout);
			this.requestTimeout = null;
		}

		if(this.xhr)
		{
			this.requestAborted = true;
			this.xhr.abort();
		}

		this.disconnectCode = code;
		this.disconnectReason = reason;
		this.connected = false;
	};

	LongPollingConnector.prototype.performRequest = function()
	{
		var self = this;
		if(!this.active)
			return;

		if(!this.path)
		{
			throw new Error("Long polling connection path is not defined");
		}
		if(this.xhr.readyState !== 0 && this.xhr.readyState !== 4)
		{
			return;
		}

		clearTimeout(this.failureTimeout);
		clearTimeout(this.requestTimeout);

		this.failureTimeout = setTimeout(function()
		{
			self.connected = true;
		}, 5000);

		this.requestTimeout = setTimeout(this.onRequestTimeout.bind(this), LONG_POLLING_TIMEOUT * 1000);

		this.xhr.open("GET", this.path);
		this.xhr.send();
	};

	LongPollingConnector.prototype.onRequestTimeout = function()
	{
		this.requestAborted = true;
		this.xhr.abort();
		this.performRequest();
	};

	LongPollingConnector.prototype.onXhrReadyStateChange = function (e)
	{
		if (this.xhr.readyState === 4)
		{
			if(!this.requestAborted || this.xhr.status == 200)
			{
				this.onResponse(this.xhr.response);
			}
			this.requestAborted = false;
		}
	};

	/**
	 * Sends some data to the server via http request.
	 * @param {ArrayBuffer} buffer Data to send.
	 * @return {bool}
	 */
	LongPollingConnector.prototype.send = function(buffer)
	{
		var path = this.parent.getPublicationPath();
		if(!path)
		{
			console.error(Utils.getDateForLog() + ": Pull: publication path is empty");
			return false;
		}

		var xhr = new XMLHttpRequest();
		xhr.open("POST", path);
		xhr.send(buffer);
	};

	LongPollingConnector.prototype.onResponse = function(response)
	{
		if(this.failureTimeout)
		{
			clearTimeout(this.failureTimeout);
			this.failureTimeout = 0;
		}
		if(this.requestTimeout)
		{
			clearTimeout(this.requestTimeout);
			this.requestTimeout = 0;
		}

		if(this.xhr.status == 200)
		{
			this.connected = true;
			if(Utils.isNotEmptyString(response) || (response instanceof ArrayBuffer))
			{
				this.callbacks.onMessage(response);
			}
			else
			{
				this.parent.session.mid = null;
			}
			this.performRequest();
		}
		else if(this.xhr.status == 304)
		{
			this.connected = true;
			if (this.xhr.getResponseHeader("Expires") === "Thu, 01 Jan 1973 11:11:01 GMT")
			{
				var lastMessageId = this.xhr.getResponseHeader("Last-Message-Id");
				if (Utils.isNotEmptyString(lastMessageId))
				{
					this.parent.setLastMessageId(lastMessageId);
				}
			}
			this.performRequest();
		}
		else
		{
			this.callbacks.onError('Could not connect to the server');
			this.connected = false;
		}
	};

	var ChannelManager = function (params)
	{
		this.publicIds = {};

		this.restClient = typeof params.restClient !== "undefined"? params.restClient: BX.rest;

		this.getPublicListMethod = params.getPublicListMethod;
	};

	/**
	 *
	 * @param {Array} users Array of user ids.
	 * @return {BX.Promise}
	 */
	ChannelManager.prototype.getPublicIds = function(users)
	{
		var promise = new BX.Promise();
		var result = {};
		var now = new Date();
		var unknownUsers = [];

		for(var i = 0; i < users.length; i++)
		{
			var userId = users[i];
			if(this.publicIds[userId] && this.publicIds[userId]['end'] > now)
			{
				result[userId] = this.publicIds[userId];
			}
			else
			{
				unknownUsers.push(userId);
			}
		}

		if(unknownUsers.length === 0)
		{
			promise.resolve(result);
			return promise;
		}

		this.restClient.callMethod(this.getPublicListMethod, {users: unknownUsers}).then(function(response)
		{
			if(response.error())
			{
				promise.resolve({});
				return promise;
			}

			var data = response.data();

			this.setPublicIds(Utils.objectValues(data));
			unknownUsers.forEach(function(userId) {
				result[userId] = this.publicIds[userId];
			}, this);

			promise.resolve(result);

		}.bind(this));

		return promise;
	};

	/**
	 *
	 * @param {object[]} publicIds
	 * @param {integer} publicIds.user_id
	 * @param {string} publicIds.public_id
	 * @param {string} publicIds.signature
	 * @param {Date} publicIds.start
	 * @param {Date} publicIds.end
	 */
	ChannelManager.prototype.setPublicIds = function(publicIds)
	{
		for(var i = 0; i < publicIds.length; i++)
		{
			var publicIdDescriptor = publicIds[i];
			var userId = publicIdDescriptor.user_id;
			this.publicIds[userId] = {
				userId: userId,
				publicId: publicIdDescriptor.public_id,
				signature: publicIdDescriptor.signature,
				start: new Date(publicIdDescriptor.start),
				end: new Date(publicIdDescriptor.end)
			}
		}
	};


	var StorageManager = function (params)
	{
		params = params || {};

		this.userId = params.userId? params.userId: (typeof BX.message !== 'undefined' && BX.message.USER_ID? BX.message.USER_ID: 0);
		this.siteId = params.siteId? params.siteId: (typeof BX.message !== 'undefined' && BX.message.SITE_ID? BX.message.SITE_ID: 'none');
	};

	StorageManager.prototype.set = function(name, value)
	{
		if (typeof window.localStorage === 'undefined')
		{
			return false;
		}
		if (typeof value != 'string')
		{
			if (value)
			{
				value = JSON.stringify(value);
			}
		}
		return window.localStorage.setItem(this.getKey(name), value)
	};


	StorageManager.prototype.get = function(name, defaultValue)
	{
		if (typeof window.localStorage === 'undefined')
		{
			return defaultValue || null;
		}

		var result = window.localStorage.getItem(this.getKey(name));
		if (result === null)
		{
			return defaultValue || null;
		}

		return JSON.parse(result);
	};

	StorageManager.prototype.remove = function(name)
	{
		if (typeof window.localStorage === 'undefined')
		{
			return false;
		}
		return window.localStorage.removeItem(this.getKey(name));
	};

	StorageManager.prototype.getKey = function (name)
	{
		return 'bx-pull-' + this.userId + '-' + this.siteId + '-' + name;
	};

	StorageManager.prototype.compareKey = function (eventKey, userKey)
	{
		return eventKey === this.getKey(userKey);
	};

	var Utils = {
		browser: {
			IsChrome: function()
			{
				return navigator.userAgent.toLowerCase().indexOf('chrome') != -1;
			},
			IsFirefox: function()
			{
				return navigator.userAgent.toLowerCase().indexOf('firefox') != -1;
			},
			IsIe: function ()
			{
				return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
			}
		},
		getTimestamp: function()
		{
			return (new Date()).getTime();
		},
		/**
		 * Reduces errors array to single string.
		 * @param {array} errors
		 * @return {string}
		 */
		errorsToString: function(errors)
		{
			if(!this.isArray(errors))
			{
				return "";
			}
			else
			{
				return errors.reduce(function(result, currentValue)
				{
					if(result != "")
					{
						result += "; ";
					}
					return result + currentValue.code + ": " + currentValue.message;
				}, "");
			}
		},
		isString: function(item) {
			return item === '' ? true : (item ? (typeof (item) == "string" || item instanceof String) : false);
		},
		isArray: function(item) {
			return item && Object.prototype.toString.call(item) == "[object Array]";
		},
		isFunction: function(item) {
			return item === null ? false : (typeof (item) == "function" || item instanceof Function);
		},
		isDomNode: function(item) {
			return item && typeof (item) == "object" && "nodeType" in item;
		},
		isDate: function(item) {
			return item && Object.prototype.toString.call(item) == "[object Date]";
		},
		isPlainObject: function(item)
		{
			if(!item || typeof(item) !== "object" || item.nodeType)
			{
				return false;
			}

			var hasProp = Object.prototype.hasOwnProperty;
			try
			{
				if (item.constructor && !hasProp.call(item, "constructor") && !hasProp.call(item.constructor.prototype, "isPrototypeOf") )
				{
					return false;
				}
			}
			catch (e)
			{
				return false;
			}

			var key;
			for (key in item)
			{
			}
			return typeof(key) === "undefined" || hasProp.call(item, key);
		},
		isNotEmptyString: function(item) {
			return this.isString(item) ? item.length > 0 : false;
		},
		buildQueryString: function(params)
		{
			var result = '';
			for (var key in params)
			{
				if (!params.hasOwnProperty(key))
				{
					continue;
				}
				var value = params[key];
				if(Utils.isArray(value))
				{
					value.forEach(function(valueElement, index)
					{
						result += encodeURIComponent(key + "[" + index + "]") + "=" + encodeURIComponent(valueElement) + "&";
					});
				}
				else
				{
					result += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
				}
			}

			if(result.length > 0)
			{
				result = result.substr(0, result.length - 1);
			}
			return result;
		},
		objectValues: function values(obj)
		{
			var result = [];
			for (var key in obj)
			{
				if(obj.hasOwnProperty(key) && obj.propertyIsEnumerable(key))
				{
					result.push(obj[key]);
				}
			}
			return result;
		},
		clone: function(obj, bCopyObj)
		{
			var _obj, i, l;
			if (bCopyObj !== false)
				bCopyObj = true;

			if (obj === null)
				return null;

			if (this.isDomNode(obj))
			{
				_obj = obj.cloneNode(bCopyObj);
			}
			else if (typeof obj == 'object')
			{
				if (this.isArray(obj))
				{
					_obj = [];
					for (i=0,l=obj.length;i<l;i++)
					{
						if (typeof obj[i] == "object" && bCopyObj)
							_obj[i] = this.clone(obj[i], bCopyObj);
						else
							_obj[i] = obj[i];
					}
				}
				else
				{
					_obj =  {};
					if (obj.constructor)
					{
						if (this.isDate(obj))
							_obj = new Date(obj);
						else
							_obj = new obj.constructor();
					}

					for (i in obj)
					{
						if (!obj.hasOwnProperty(i))
						{
							continue;
						}
						if (typeof obj[i] == "object" && bCopyObj)
							_obj[i] = this.clone(obj[i], bCopyObj);
						else
							_obj[i] = obj[i];
					}
				}

			}
			else
			{
				_obj = obj;
			}

			return _obj;
		},

		getDateForLog: function()
		{
			var d = new Date();

			return d.getFullYear() + "-" + Utils.lpad(d.getMonth(), 2, '0') + "-" + Utils.lpad(d.getDate(), 2, '0') + " " + Utils.lpad(d.getHours(), 2, '0') + ":" + Utils.lpad(d.getMinutes(), 2, '0');
		},

		lpad: function(str, length, chr)
		{
			str = str.toString();
			chr = chr || ' ';

			if(str.length > length)
			{
				return str;
			}

			var result = '';
			for(var i = 0; i < length - str.length; i++)
			{
				result += chr;
			}

			return result + str;
		}
	};

	if (
		typeof BX.namespace !== 'undefined'
		&& typeof BX.PULL === 'undefined'
	)
	{
		BX.PULL = new Pull();
	}

	BX.PullClient = Pull;
	BX.PullClient.PullStatus = PullStatus;
	BX.PullClient.SubscriptionType = SubscriptionType;
	BX.PullClient.CloseReasons = CloseReasons;
	BX.PullClient.StorageManager = StorageManager;
})();