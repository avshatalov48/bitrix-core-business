;(function()
{
	BX.namespace('BX.Call');

	BX.Call.State = {
		Incoming: 'Incoming'
	};

	BX.Call.UserState = {
		Idle: 'Idle',
		Calling: 'Calling',
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

	BX.Call.Event = {
		onUserInvited: 'onUserInvited',
		onUserStateChanged: 'onUserStateChanged',
		onUserVoiceStarted: 'onUserVoiceStarted',
		onUserVoiceStopped: 'onUserVoiceStopped',
		onLocalMediaReceived: 'onLocalMediaReceived',
		onLocalMediaStopped: 'onLocalMediaStopped',
		onDeviceListUpdated: 'onDeviceListUpdated',
		onRTCStatsReceived: 'onRTCStatsReceived',
		onCallFailure: 'onCallFailure',
		onStreamReceived: 'onStreamReceived',
		onStreamRemoved: 'onStreamRemoved',
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
		if(!__allowConstructing)
		{
			throw new Error('Do not use this constructor directly, use BX.Call.Engine.getInstance instead');
		}
		this.calls = {};
		this.userId = BX.message('USER_ID');

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

	/**
	 * @param {Object} config
	 * @param {int} config.type
	 * @param {string} config.provider
	 * @return Promise<BX.Call.AbstractCall>
	 */
	BX.Call.Engine.prototype.createCall = function(config)
	{
		var self = this;

		return new Promise(function(resolve, reject)
		{
			var callType = config.type || BX.Call.Type.Instant;
			var callProvider = config.provider || self.getDefaultProvider();

			var callParameters = {
				type: callType,
				provider: callProvider,
				entityType: config.entityType,
				entityId: config.entityId,
				userIds: BX.type.isArray(config.userIds) ? config.userIds : []
			};

			var batchParameters = {
				callParams: [ajaxActions.createCall, callParameters],
				publicChannels: [ajaxActions.getPublicChannels, {USERS: "$result[callParams][users]"}]
			};

			BX.rest.callBatch(batchParameters, function(response)
			{
				if(response.callParams.error())
				{
					var error = response.callParams.error().getError()
					return reject({
						code: error.error,
						message: error.error_description
					});
				}

				var createCallResponse = response.callParams.data();

				var callFields = createCallResponse.call;
				var callFabric = self.__getCallFabric(callFields['PROVIDER']);
				var call = callFabric.createCall({
					id: callFields['ID'],
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

				var publicChannels = response.publicChannels.data();
				BX.PULL.setPublicIds(Object.values(publicChannels));

				self.calls[callFields['ID']] = call;

				if(createCallResponse.userData)
				{
					BX.MessengerCommon.updateUserData(createCallResponse.userData);
				}

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			});
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

			var batchParameters = {
				callParams: [ajaxActions.createChildCall, callParameters],
				publicChannels: [ajaxActions.getPublicChannels, {USERS: "$result[callParams][users]"}]
			};

			BX.rest.callBatch(batchParameters, function(response)
			{
				var createCallResponse = response.callParams.data();
				var callFields = createCallResponse.call;
				var callFabric = self.__getCallFabric(callFields['PROVIDER']);

				var call = callFabric.createCall({
					id: callFields['ID'],
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

				var publicChannels = response.publicChannels.data();
				BX.PULL.setPublicIds(Object.values(publicChannels));

				self.calls[callFields['ID']] = call;

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			});
		});
	};
	
	BX.Call.Engine.prototype.getCall = function(callFields, users)
	{
		var callFabric = this.__getCallFabric(callFields['PROVIDER']);
		var call = callFabric.createCall({
			id: callFields['ID'],
			instanceId: this.getUuidv4(),
			direction: BX.Call.Direction.Outgoing,
			users: users,
			events: {
				onDestroy: this.__onCallDestroy.bind(this)
			}
		});

		this.calls[callFields['ID']] = call;
		return call;
	};

	BX.Call.Engine.prototype.getCallWithId = function(id)
	{
		var self = this;
		return new Promise(function(resolve, reject)
		{
			if(self.calls[id])
			{
				return resolve(self.calls[id]);
			}

			BX.ajax.runAction(ajaxActions.getCall, {
				data: {
					callId: id
				}
			}).then(function(answer)
			{
				if(answer.state === 'success')
				{
					var callFields = answer.data.call;
					resolve({
						call: self.getCall(callFields),
						isNew: false
					})
				}
				else if(answer.state === 'error')
				{
					reject(answer.errors[0]);
				}
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
			'Call::incoming': this.__onPullIncomingCall.bind(this)
		};

		if(handlers[command])
		{
			handlers[command].call(this, params, extra);
		}
		else if(command.substr(0, 6) === 'Call::' && params['call'])
		{
			var callFields = params['call'];
			var callId = callFields['ID'];
			if(this.calls[callId])
			{
				this.calls[callId].__onPullEvent(command, params, extra);
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
		var callId = callFields.ID;
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
				direction: BX.Call.Direction.Incoming,
				users: params.users,
				initiatorId: params.senderId,
				associatedEntity: callFields.ASSOCIATED_ENTITY,
				events: {
					onDestroy: this.__onCallDestroy.bind(this)
				}
			});

			this.calls[callId] = call;
		}

		if(call)
		{
			BX.onCustomEvent(window, "CallEvents::incomingCall", [{
				call: call,
				video: params.video === true,
				isMobile: params.isMobile === true
			}]);
		}
	};

	BX.Call.Engine.prototype.__onCallDestroy = function(e)
	{
		if(this.calls[e.call.id])
		{
			delete this.calls[e.call.id];
		}
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


	__allowConstructing = true;
	BX.CallEngine = new BX.Call.Engine();
	__allowConstructing = false;

})();