;(function()
{
	BX.namespace('BX.Call');

	BX.Call.State = {
		Incoming: 'Incoming'
	};

	BX.Call.UserState = {
		Idle: 'Idle',
		Busy: 'Busy',
		Calling: 'Calling',
		Unavailable: 'Unavailable',
		Declined: 'Declined',
		Ready: 'Ready',
		Connecting: 'Connecting',
		Connected: 'Connected',
		Failed: 'Failed'
	};

	BX.Call.Type = {
		Instant: 1,
		Permanent: 2
	};

	BX.Call.Provider = {
		Plain: 'Plain',
		Voximplant: 'Voximplant',
		Janus: 'Janus'
	};

	BX.Call.StreamTag = {
		Main: 'main',
		Screen: 'screen'
	};

	BX.Call.Direction = {
		Incoming: 'Incoming',
		Outgoing: 'Outgoing'
	};

	BX.Call.Quality = {
		VeryHigh: "very_high",
		High: "high",
		Medium: "medium",
		Low: "low",
		VeryLow: "very_low"
	};

	BX.Call.Event = {
		onUserInvited: 'onUserInvited',
		onUserStateChanged: 'onUserStateChanged',
		onUserMicrophoneState: 'onUserMicrophoneState',
		onUserVoiceStarted: 'onUserVoiceStarted',
		onUserVoiceStopped: 'onUserVoiceStopped',
		onLocalMediaReceived: 'onLocalMediaReceived',
		onLocalMediaStopped: 'onLocalMediaStopped',
		onDeviceListUpdated: 'onDeviceListUpdated',
		onRTCStatsReceived: 'onRTCStatsReceived',
		onCallFailure: 'onCallFailure',
		onStreamReceived: 'onStreamReceived',
		onStreamRemoved: 'onStreamRemoved',
		onJoin: 'onJoin',
		onLeave: 'onLeave',
		onDestroy: 'onDestroy',
	};

	var ajaxActions = {
		createCall: 'im.call.create',
		createChildCall: 'im.call.createChildCall',
		getPublicChannels: 'pull.channel.public.list',
		getCall: 'im.call.get'
	};

	var __allowConstructing = false;

	BX.Call.Engine = function()
	{
		this.debugFlag = false;
		if(!__allowConstructing)
		{
			throw new Error('Do not use this constructor directly, use BX.Call.Engine.getInstance instead');
		}
		this.calls = {};
		this.userId = Number(BX.message('USER_ID'));

		this.unknownCalls = {};

		this.restClient = null;

		this.init();
	};

	/**
	 * @return {BX.Call.Engine}
	 */
	BX.Call.Engine.getInstance = function()
	{
		return BX.CallEngine;
	};

	BX.Call.Engine.prototype.init = function()
	{
		BX.addCustomEvent("onPullEvent-im", this.__onPullEvent.bind(this));
		BX.addCustomEvent("onPullClientEvent-im", this.__onPullClientEvent.bind(this));
	};

	BX.Call.Engine.prototype.getCurrentUserId = function()
	{
		return this.userId;
	};

	BX.Call.Engine.prototype.setRestClient = function(restClient)
	{
		this.restClient = restClient;
	};

	BX.Call.Engine.prototype.getRestClient = function()
	{
		return this.restClient || BX.rest;
	};

	/**
	 * @param {Object} config
	 * @param {int} config.type
	 * @param {string} config.provider
	 * @param {string} config.entityType
	 * @param {string} config.entityId
	 * @param {string} config.provider
	 * @param {boolean} config.joinExisting
	 * @return Promise<BX.Call.AbstractCall>
	 */
	BX.Call.Engine.prototype.createCall = function(config)
	{
		var self = this;

		return new Promise(function(resolve, reject)
		{
			var callType = config.type || BX.Call.Type.Instant;
			var callProvider = config.provider || self.getDefaultProvider();

			if (config.joinExisting)
			{
				for(var callId in self.calls)
				{
					if(self.calls.hasOwnProperty(callId))
					{
						var call = self.calls[callId];
						if(call.provider == config.provider && call.associatedEntity.type == config.entityType && call.associatedEntity.id == config.entityId)
						{
							self.log(callId, "Found existing call, attaching to it");
							return resolve({
								call: call,
								isNew: false
							});
						}
					}
				}
			}

			var callParameters = {
				type: callType,
				provider: callProvider,
				entityType: config.entityType,
				entityId: config.entityId,
				joinExisting: !!config.joinExisting,
				userIds: BX.type.isArray(config.userIds) ? config.userIds : []
			};

			self.getRestClient().callMethod(ajaxActions.createCall, callParameters).then(function(response)
			{
				if(response.error())
				{
					var error = response.error().getError();
					return reject({
						code: error.error,
						message: error.error_description
					});
				}

				var createCallResponse = response.data();
				if(createCallResponse.userData)
				{
					BX.MessengerCommon.updateUserData(createCallResponse.userData);
				}
				if(createCallResponse.publicChannels)
				{
					BX.PULL.setPublicIds(Object.values(createCallResponse.publicChannels))
				}
				var callFields = createCallResponse.call;
				if (self.calls[callFields['ID']])
				{
					if(self.calls[callFields['ID']] instanceof CallStub)
					{
						self.calls[callFields['ID']].destroy();
					}
					else
					{
						console.error("Call " + callFields['ID'] + " already exists");
						return resolve ({
							call: self.calls[callFields['ID']],
							isNew: false
						});
					}
				}

				var callFabric = self.__getCallFabric(callFields['PROVIDER']);
				var call = callFabric.createCall({
					id: parseInt(callFields['ID'], 10),
					instanceId: self.getUuidv4(),
					direction: BX.Call.Direction.Outgoing,
					users: createCallResponse.users,
					videoEnabled: (config.videoEnabled == true),
					enableMicAutoParameters: (config.enableMicAutoParameters !== false),
					associatedEntity: callFields.ASSOCIATED_ENTITY,
					events: {
						onDestroy: self.__onCallDestroy.bind(self)
					},
					debug: config.debug === true
				});


				self.calls[callFields['ID']] = call;

				if(createCallResponse.isNew)
				{
					self.log(call.id, "Creating new call");
				}
				else
				{
					self.log(call.id, "Server returned existing call, attaching to it");
				}

				BX.onCustomEvent(window, "CallEvents::callCreated", [{
					call: call
				}]);

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			}).catch(function(error)
			{
				if (BX.type.isFunction(error.error))
				{
					error = error.error().getError();
				}
				reject({
					code: error.error,
					message: error.error_description
				})
			})
		});

	};

	BX.Call.Engine.prototype.createChildCall = function(parentId, newProvider, newUsers)
	{
		var self = this;
		return new Promise(function(resolve, reject)
		{
			if(!self.calls[parentId])
			{
				return reject('Parent call is not found');
			}

			var parentCall = self.calls[parentId];

			var callParameters = {
				parentId: parentId,
				newProvider: newProvider,
				newUsers: newUsers
			};

			self.getRestClient().callMethod(ajaxActions.createChildCall, callParameters, function(response)
			{
				var createCallResponse = response.data();
				var callFields = createCallResponse.call;
				var callFabric = self.__getCallFabric(callFields['PROVIDER']);

				var call = callFabric.createCall({
					id: parseInt(callFields['ID'], 10),
					instanceId: self.getUuidv4(),
					parentId: callFields['PARENT_ID'],
					direction: BX.Call.Direction.Outgoing,
					users: createCallResponse.users,
					videoEnabled: parentCall.isVideoEnabled(),
					enableMicAutoParameters: parentCall.enableMicAutoParameters !== false,
					associatedEntity: callFields.ASSOCIATED_ENTITY,
					events: {
						onDestroy: self.__onCallDestroy.bind(self)
					}
				});

				self.calls[callFields['ID']] = call;
				BX.onCustomEvent(window, "CallEvents::callCreated", [{
					call: call
				}]);

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			});
		});
	};
	
	BX.Call.Engine.prototype._instantiateCall = function(callFields, users)
	{
		if(this.calls[callFields['ID']])
		{
			console.error("Call " + callFields['ID'] + " already exists");
			return this.calls[callFields['ID']];
		}

		var callFabric = this.__getCallFabric(callFields['PROVIDER']);
		var call = callFabric.createCall({
			id: parseInt(callFields['ID'], 10),
			instanceId: this.getUuidv4(),
			initiatorId: parseInt(callFields['INITIATOR_ID'], 10),
			parentId: callFields['PARENT_ID'],
			direction: callFields['INITIATOR_ID'] == this.userId ? BX.Call.Direction.Outgoing : BX.Call.Direction.Incoming,
			users: users,
			associatedEntity: callFields.ASSOCIATED_ENTITY,

			events: {
				onDestroy: this.__onCallDestroy.bind(this)
			}
		});

		this.calls[callFields['ID']] = call;

		BX.onCustomEvent(window, "CallEvents::callCreated", [{
			call: call
		}]);

		return call;
	};

	BX.Call.Engine.prototype.getCallWithId = function(id)
	{
		var self = this;
		return new Promise(function(resolve, reject)
		{
			if(self.calls[id])
			{
				return resolve({
					call: self.calls[id],
					isNew: false
				});
			}

			self.getRestClient().callMethod(ajaxActions.getCall, {callId: id}).then(function(answer)
			{
				var data = answer.data();
				resolve({
					call: self._instantiateCall(data.call, data.users),
					isNew: false
				})
			}).catch(function (error)
			{
				if (BX.type.isFunction(error.error))
				{
					error = error.error().getError();
				}
				reject({
					code: error.error,
					message: error.error_description
				})
			})
		})
	};

	BX.Call.Engine.prototype.getUuidv4 = function()
	{
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	};

	BX.Call.Engine.prototype.__onPullEvent = function(command, params, extra)
	{
		var handlers = {
			'Call::incoming': this.__onPullIncomingCall.bind(this),
		};

		if(command.substr(0, 6) === 'Call::' && params.publicIds)
		{
			BX.PULL.setPublicIds(Object.values(params.publicIds));
		}

		if(handlers[command])
		{
			handlers[command].call(this, params, extra);
		}
		else if(command.substr(0, 6) === 'Call::' && (params['call'] || params['callId']))
		{
			var callId = params['call'] ? params['call']['ID'] : params['callId'];
			if(this.calls[callId])
			{
				this.calls[callId].__onPullEvent(command, params, extra);
			}
			else if(command === 'Call::ping')
			{
				this.__onUnknownCallPing(params, extra).then(function(result)
				{
					if(result && this.calls[callId])
					{
						this.calls[callId].__onPullEvent(command, params, extra);
					}
				}.bind(this));
			}
		}
	};

	BX.Call.Engine.prototype.__onPullClientEvent = function(command, params, extra)
	{
		if(command.substr(0, 6) === 'Call::' && params['callId'])
		{
			var callId = params['callId'];
			if(this.calls[callId])
			{
				this.calls[callId].__onPullEvent(command, params, extra);
			}
			else if (command === 'Call::ping')
			{
				this.__onUnknownCallPing(params, extra).then(function(result)
				{
					if(result && this.calls[callId])
					{
						this.calls[callId].__onPullEvent(command, params, extra);
					}
				}.bind(this));
			}
		}
	};

	BX.Call.Engine.prototype.__onPullIncomingCall = function(params, extra)
	{
		if(extra.server_time_ago > 30)
		{
			console.error("Call was started too long time ago");
			return;
		}

		var callFields = params.call;
		var callId = parseInt(callFields.ID, 10);
		var call;

		if(params.publicIds)
		{
			BX.PULL.setPublicIds(Object.values(params.publicIds));
		}

		if(params.userData)
		{
			BX.MessengerCommon.updateUserData(params.userData);
		}

		if(this.calls[callId])
		{
			call = this.calls[callId];
		}
		else
		{
			var callFabric = this.__getCallFabric(callFields.PROVIDER);
			call = callFabric.createCall({
				id: callId,
				instanceId: this.getUuidv4(),
				parentId: callFields.PARENT_ID || null,
				callFromMobile: params.isMobile === true,
				direction: BX.Call.Direction.Incoming,
				users: params.users,
				initiatorId: params.senderId,
				associatedEntity: callFields.ASSOCIATED_ENTITY,
				events: {
					onDestroy: this.__onCallDestroy.bind(this)
				}
			});

			this.calls[callId] = call;

			BX.onCustomEvent(window, "CallEvents::callCreated", [{
				call: call
			}]);
		}

		call.addInvitedUsers(params.invitedUsers);
		if(call)
		{
			BX.onCustomEvent(window, "CallEvents::incomingCall", [{
				call: call,
				video: params.video === true,
				isMobile: params.isMobile === true
			}]);
		}
		this.log(call.id, "Incoming call " + call.id);
	};

	BX.Call.Engine.prototype.__onUnknownCallPing = function(params, extra)
	{
		return new Promise(function(resolve, reject)
		{
			var callId = Number(params.callId);
			if(extra.server_time_ago > 10)
			{
				this.log(callId, "Error: Ping was sent too long time ago");
				return resolve(false);
			}
			if(!window.BXIM || !window.BXIM.init)
			{
				return resolve(false);
			}

			if(this.unknownCalls[callId])
			{
				return resolve(false);
			}

			this.unknownCalls[callId] = true;

			if(params.userData)
			{
				BX.MessengerCommon.updateUserData(params.userData);
			}

			this.getCallWithId(callId).then(function(result)
			{
				this.unknownCalls[callId] = false;
				resolve(true);
			}.bind(this)).catch(function(error)
			{
				this.unknownCalls[callId] = false;
				this.log(callId, "Error: Could not instantiate call", error);
				resolve(false);
			}.bind(this));
		}.bind(this));
	};

	BX.Call.Engine.prototype.__onCallDestroy = function(e)
	{
		var callId = e.call.id;
		this.calls[callId] = new CallStub({
			callId: callId,
			onDelete: function()
			{
				if(this.calls[callId])
				{
					delete this.calls[callId];
				}
			}.bind(this)
		});

		BX.onCustomEvent(window, "CallEvents::callDestroyed", [{
			callId: e.call.id
		}]);
	};

	BX.Call.Engine.prototype.getDefaultProvider = function()
	{
		return BX.Call.Provider.Plain;
	};

	BX.Call.Engine.prototype.__getCallFabric = function(providerType)
	{
		if(providerType == BX.Call.Provider.Plain)
		{
			return BX.Call.PlainCallFabric;
		}
		else if(providerType == BX.Call.Provider.Voximplant)
		{
			return BX.Call.VoximplantCallFabric;
		}
		else if(providerType == BX.Call.Provider.Janus)
		{
			return BX.Call.JanusCallFabric;
		}

		throw new Error("Unknown call provider type " + providerType);
	};

	BX.Call.Engine.prototype.debug = function(debugFlag)
	{
		this.debugFlag = typeof(debugFlag) === 'undefined' ? true:  !!debugFlag;

		return this.debugFlag;
	};

	BX.Call.Engine.prototype.log = function()
	{
		var text = BX.Call.Util.getDateForLog();

		var callId = typeof(arguments[0]) === "number" ? arguments[0] : 0;

		for (var i = callId > 0 ? 1 : 0; i < arguments.length; i++)
		{
			if(arguments[i] instanceof Error)
			{
				text = arguments[i].message + "\n" + arguments[i].stack
			}
			else
			{
				try
				{
					text = text+' | '+(typeof(arguments[i]) == 'object'? JSON.stringify(arguments[i]): arguments[i]);
				}
				catch (e)
				{
					text = text+' | (circular structure)';
				}
			}
		}
		if (BX.desktop && BX.desktop.ready())
		{
			BX.desktop.log(BX.message('USER_ID')+'.video.log', text.substr(3));
		}
		if (this.debugFlag)
		{
			if (console)
			{
				var a = ['Call log [' + BX.Call.Util.getTimeForLog() + ']: '];
				console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
			}
		}

		if(BX.MessengerDebug && callId)
		{
			BX.MessengerDebug.addLog(callId, text);
		}
	};

	BX.Call.Engine.prototype.getAllowedVideoQuality = function(participantsCount)
	{
		if(participantsCount < 5)
		{
			return BX.Call.Quality.VeryHigh
		}
		else if(participantsCount < 8)
		{
			return BX.Call.Quality.High
		}
		else if(participantsCount < 16)
		{
			return BX.Call.Quality.Medium
		}
		else if(participantsCount < 24)
		{
			return BX.Call.Quality.Low
		}
		else
		{
			return BX.Call.Quality.VeryLow
		}
	};

	BX.Call.PlainCallFabric =
	{
		createCall: function(config)
		{
			return new BX.Call.PlainCall(config);
		}
	};

	BX.Call.VoximplantCallFabric =
	{
		createCall: function(config)
		{
			return new BX.Call.VoximplantCall(config);
		}
	};

	BX.Call.JanusCallFabric =
	{
		createCall: function(config)
		{
			return new BX.Call.JanusCall(config);
		}
	};

	var CallStub = function(config)
	{
		this.callId = config.callId;
		this.lifetime = config.lifetime || 120;
		this.callbacks = {
			onDelete: BX.type.isFunction(config.onDelete) ? config.onDelete : BX.DoNothing
		};

		this.deleteTimeout = setTimeout(function()
		{
			this.callbacks.onDelete({
				callId: this.callId
			})
		}.bind(this), this.lifetime * 1000);
	};

	CallStub.prototype.__onPullEvent = function(command, params, extra)
	{
		// do nothing
	};

	CallStub.prototype.isAnyoneParticipating = function()
	{
		return false;
	};

	CallStub.prototype.addEventListener = function()
	{
		return false;
	};

	CallStub.prototype.removeEventListener = function()
	{
		return false;
	};

	CallStub.prototype.destroy = function()
	{
		clearTimeout(this.deleteTimeout);
		this.callbacks.onDelete = BX.DoNothing;
	};

	__allowConstructing = true;
	BX.CallEngine = new BX.Call.Engine();
	__allowConstructing = false;

})();