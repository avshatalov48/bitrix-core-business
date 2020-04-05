;(function()
{
	/**
	 * Implements Call interface
	 * Public methods:
	 * - inviteUsers
	 * - cancel
	 * - answer
	 * - decline
	 * - hangup
	 *
	 * Events:
	 * - onCallStateChanged //not sure about this.
	 * - onUserStateChanged
	 * - onStreamReceived
	 * - onStreamRemoved
	 * - onDestroy
	 */

	debug = false;

	BX.namespace('BX.Call');

	var ajaxActions = {
		invite: 'im.call.invite',
		cancel: 'im.call.cancel',
		answer: 'im.call.answer',
		decline: 'im.call.decline',
		hangup: 'im.call.hangup',
		ping: 'im.call.ping'
	};

	var clientEvents = {
		voiceStarted: 'Call::voiceStarted',
		voiceStopped: 'Call::voiceStopped'
	};

	var pingPeriod = 25000;

	// screensharing workaround
	navigator['getDisplayMedia'] = function()
	{
		var mediaParams = {
			audio: false,
			video: {
				mandatory: {
					chromeMediaSource: 'desktop',
					maxWidth: screen.width > 1920 ? screen.width : 1920,
					maxHeight: screen.height > 1080 ? screen.height : 1080,
				},
				optional: [{googTemporalLayeredScreencast: true}],
			},
		};
		return navigator.mediaDevices.getUserMedia(mediaParams);
	};

	BX.Call.VoximplantCall = function(config)
	{
		BX.Call.PlainCall.superclass.constructor.apply(this, arguments);

		if(!window.VoxImplant)
		{
			throw new Error("Voximplant SDK is not found");
		}

		this.voximplant = null;
		this.voximplantCall = null;
		this.signaling = new BX.Call.VoximplantCall.Signaling({
			call: this
		});


		this.peers = {};
		this.voiceDetection = null;

		this.screenShared = false;

		this.deviceList = [];

		// event handlers
		this.__onMicAccessResultHandler = this.__onMicAccessResult.bind(this);
		this.__onLocalDevicesUpdatedHandler = this.__onLocalDevicesUpdated.bind(this);
		this.__onLocalMediaRendererAddedHandler = this.__onLocalMediaRendererAdded.bind(this);
		this.__onBeforeLocalMediaRendererRemovedHandler = this.__onBeforeLocalMediaRendererRemoved.bind(this);

		this.init();

		this.ping();
		this.pingInterval = setInterval(this.ping.bind(this), pingPeriod);
	};

	BX.extend(BX.Call.VoximplantCall, BX.Call.AbstractCall);

	BX.Call.VoximplantCall.prototype.init = function ()
	{
		this.users.forEach(function(userId)
		{
			this.peers[userId] = this.createPeer(userId);
		}, this);
	};

	BX.Call.VoximplantCall.prototype.ping = function()
	{
		this.signaling.sendPing({userId: this.users});
	};

	BX.Call.VoximplantCall.prototype.createPeer = function (userId)
	{
		return new BX.Call.VoximplantCall.Peer({
			call: this,
			userId: userId,
			ready: userId == this.initiatorId,

			onStreamReceived: function(e)
			{
				this.runCallback(BX.Call.Event.onStreamReceived, e);
			}.bind(this),
			onStreamRemoved: function(e)
			{
				this.runCallback(BX.Call.Event.onStreamRemoved, e);
			}.bind(this),
			onStateChanged: this.__onPeerStateChanged.bind(this)
		})
	};

	BX.Call.VoximplantCall.prototype.getUsers = function ()
	{
		var result = {};
		for (var userId in this.peers)
		{
			result[userId] = this.peers[userId].calculatedState;
		}
		return result;
	};

	BX.Call.VoximplantCall.prototype.getClient = function()
	{
		var self = this;

		return new Promise(function(resolve, reject)
		{
			if(self.voximplant)
			{
				return resolve();
			}

			BX.Voximplant.getClient().then(function(client)
			{
				self.voximplant = client;

				self.voximplant.enableSilentLogging();
				self.voximplant.setLoggerCallback(function(e)
				{
					if(self.debug)
					{
						self.log(e.label + ": " + e.message);
					}
				});

				self.bindClientEvents();

				resolve();
			}).catch(function (err)
			{
				reject(err);
			});
		});
	};

	BX.Call.VoximplantCall.prototype.bindClientEvents = function()
	{
		this.voximplant.addEventListener(VoxImplant.Events.MicAccessResult, this.__onMicAccessResultHandler);

		var streamManager = VoxImplant.Hardware.StreamManager.get();
		streamManager.on(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.__onLocalDevicesUpdatedHandler);
		streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.__onLocalMediaRendererAddedHandler);
		streamManager.on(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.__onBeforeLocalMediaRendererRemovedHandler);
	};

	BX.Call.VoximplantCall.prototype.removeClientEvents = function()
	{
		if(this.voximplant)
		{
			this.voximplant.removeEventListener(VoxImplant.Events.MicAccessResult, this.__onMicAccessResultHandler);
		}

		var streamManager = VoxImplant.Hardware.StreamManager.get();
		streamManager.off(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.__onLocalDevicesUpdatedHandler);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.__onLocalMediaRendererAddedHandler);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.__onBeforeLocalMediaRendererRemovedHandler);
	};

	BX.Call.VoximplantCall.prototype.setMuted = function(muted)
	{
		if(this.muted == muted)
		{
			return;
		}

		this.muted = muted;

		if(this.voximplantCall)
		{
			if(this.muted)
			{
				this.voximplantCall.muteMicrophone();
			}
			else
			{
				this.voximplantCall.unmuteMicrophone();
			}
		}
	};

	BX.Call.VoximplantCall.prototype.setVideoEnabled = function(videoEnabled)
	{
		videoEnabled = (videoEnabled === true);
		if(this.videoEnabled == videoEnabled)
		{
			return;
		}

		this.videoEnabled = videoEnabled;
		if(this.voximplantCall)
		{
			this.voximplantCall.sendVideo(this.videoEnabled);

			// kinda workaround. if we repeat sendVideo(false), voximplant's sdk will release camera.
			if(!this.videoEnabled)
			{
				this.voximplantCall.sendVideo(this.videoEnabled);
			}
		}
	};

	BX.Call.VoximplantCall.prototype.setCameraId = function(cameraId)
	{
		if(this.cameraId == cameraId)
		{
			return;
		}

		this.cameraId = cameraId;
		if(this.voximplantCall)
		{
			VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, {
				cameraId: this.cameraId
			});
		}
	};

	BX.Call.VoximplantCall.prototype.setMicrophoneId = function(microphoneId)
	{
		if(this.microphoneId == microphoneId)
		{
			return;
		}

		this.microphoneId = microphoneId;
		if(this.voximplantCall)
		{
			VoxImplant.Hardware.AudioDeviceManager.get().setCallAudioSettings(this.voximplantCall, {
				inputId: this.microphoneId
			});
		}
	};

	BX.Call.VoximplantCall.prototype.startScreenSharing = function()
	{
		if(!this.voximplantCall)
		{
			return;
		}

		this.voximplantCall.shareScreen(true).then(function()
		{
			this.screenShared = true;
		}.bind(this));
	};

	BX.Call.VoximplantCall.prototype.stopScreenSharing = function()
	{
		if(!this.voximplantCall)
		{
			return;
		}

		this.voximplantCall.stopSharingScreen();
		this.screenShared = false;

		// temporary workaround for the absence of VoxImplant.Hardware.HardwareEvents.MediaRendererRemoved event
		// remove when voximplant fix it
		this.runCallback(BX.Call.Event.onLocalMediaStopped, {
			tag: "screen"
		});
	};

	BX.Call.VoximplantCall.prototype.isScreenSharingStarted = function()
	{
		return this.screenShared;
	};

	/**
	 * Invites users to participate in the call.
	 *
	 * @param {Object} config
	 * @param {int[]} [config.users] Array of ids of the users to be invited.
	 */
	BX.Call.VoximplantCall.prototype.inviteUsers = function(config)
	{
		var self = this;
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		var users = BX.type.isArray(config.users) ? config.users : this.users;

		this.attachToConference().then(function()
		{
			return self.signaling.inviteUsers({
				userIds: users,
				video: self.videoEnabled ? 'Y' : 'N'
			})
		}).then(function(response)
		{
			for (var i = 0; i < users.length; i++)
			{
				if(!self.peers[users[i]])
				{
					self.peers[users[i]] = self.createPeer(users[i]);

					self.runCallback(BX.Call.Event.onUserInvited, {
						userId: users[i]
					});
				}
				self.peers[users[i]].onInvited();
			}
		}).catch(function(error)
		{
			self.runCallback(BX.Call.Event.onCallFailure, {
				error: error
			});
		});
	};

	/**
	 * @param {Object} config
	 * @param {bool} [config.useVideo]
	 */
	BX.Call.VoximplantCall.prototype.answer = function(config)
	{
		var self = this;
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		this.videoEnabled = (config.useVideo == true);

		this.signaling.sendAnswer();
		this.attachToConference().catch(function(error)
		{
			self.runCallback(BX.Call.Event.onCallFailure, {
				error: error
			});
		});
	};

	BX.Call.VoximplantCall.prototype.decline = function()
	{
		this.ready = false;

		BX.ajax.runAction(ajaxActions.decline, {
			data: {
				callId: this.id,
				callInstanceId: this.instanceId,
			}
		}).then(function()
		{
			this.destroy();
		}.bind(this));
	};


	BX.Call.VoximplantCall.prototype.hangup = function(code, reason)
	{
		var self = this;

		return new Promise(function(resolve, reject)
		{
			var data = {};
			if(typeof(code) != 'undefined')
			{
				data.code = code;
			}
			if(typeof(reason) != 'undefined')
			{
				data.reason = reason;
			}
			self.signaling.sendHangup(data);
			if(self.voximplantCall)
			{
				if(self.voximplantCall.state() != 'ENDED')
				{
					self.voximplantCall.hangup();
				}
			}
			return resolve();
		});
	};

	BX.Call.VoximplantCall.prototype.attachToConference = function()
	{
		var self = this;

		return new Promise(function(resolve, reject)
		{
			if(self.voximplantCall)
			{
				return resolve();
			}

			self.getClient().then(function()
			{
				if(!self.voximplant._config.experiments)
				{
					self.voximplant._config.experiments = {};
				}

				self.voximplantCall = self.voximplant.callConference({
					number: "bx_conf_" + self.id,
					video: {sendVideo: self.videoEnabled, receiveVideo: true},
					customData: null
				});

				self.bindCallEvents();

				var onCallConnected = function()
				{
					self.log("Call connected");
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Failed, onCallFailed);

					if(self.deviceList.length === 0)
					{
						navigator.mediaDevices.enumerateDevices().then(function(deviceList)
						{
							self.deviceList = deviceList;
							self.runCallback(BX.Call.Event.onDeviceListUpdated, {
								deviceList: self.deviceList
							})
						});
					}

					resolve();
				};

				var onCallFailed = function(e)
				{
					self.log("Could not attach to conference", e);
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Failed, onCallFailed);

					reject(e);
				};

				self.voximplantCall.addEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
				self.voximplantCall.addEventListener(VoxImplant.CallEvents.Failed, onCallFailed);
			}).catch(function(err)
			{
				var error;
				if(typeof(err) === "string")
				{
					// backward compatibility
					self.runCallback(BX.Call.Event.onCallFailure, {error: err})
				}
				else if(BX.type.isPlainObject(err))
				{
					if(err.hasOwnProperty('status') && err.status == 401)
					{
						error = "AUTHORIZE_ERROR";
					}
					else if(err.name === "AuthResult")
					{
						error = "AUTHORIZE_ERROR";
					}
					else
					{
						error = "UNKNOWN_ERROR";
					}

					self.runCallback(BX.Call.Event.onCallFailure, {error: error})
				}
			});
		});
	};

	BX.Call.VoximplantCall.prototype.bindCallEvents = function()
	{
		this.voximplantCall.addEventListener(VoxImplant.CallEvents.Disconnected, this.__onCallDisconnected.bind(this));
		this.voximplantCall.addEventListener(VoxImplant.CallEvents.MessageReceived, this.__onCallMessageReceived.bind(this));

		this.voximplantCall.addEventListener(VoxImplant.CallEvents.EndpointAdded, this.__onCallEndpointAdded.bind(this));
	};

	BX.Call.VoximplantCall.prototype.isAnyoneParticipating = function()
	{
		for (var userId in this.peers)
		{
			if(this.peers[userId].isParticipating())
			{
				return true;
			}
		}

		return false;
	};


	BX.Call.VoximplantCall.prototype.attachVoiceDetection = function(stream)
	{
		if(this.voiceDetection)
		{
			this.voiceDetection.destroy();
		}

		this.voiceDetection = new BX.SimpleVAD({
			mediaStream: stream,
			onVoiceStarted: this.onLocalVoiceStarted.bind(this),
			onVoiceStopped: this.onLocalVoiceStopped.bind(this)
		})
	};

	BX.Call.VoximplantCall.prototype.onLocalVoiceStarted = function(e)
	{
		this.signaling.sendVoiceStarted();
	};

	BX.Call.VoximplantCall.prototype.onLocalVoiceStopped = function(e)
	{
		this.signaling.sendVoiceStopped();
	};

	BX.Call.VoximplantCall.prototype.__onPeerStateChanged = function(e)
	{
		this.runCallback(BX.Call.Event.onUserStateChanged, e);

		if(e.state == BX.Call.UserState.Failed)
		{
			if(!this.isAnyoneParticipating())
			{
				this.hangup();
			}
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEvent = function(command, params, extra)
	{
		var handlers = {
			'Call::answer': this.__onPullEventAnswer.bind(this),
			'Call::hangup': this.__onPullEventHangup.bind(this),
			'Call::usersInvited': this.__onPullEventUsersInvited.bind(this),
			'Call::finish': this.__onPullEventFinish.bind(this)
		};

		if(handlers[command])
		{
			handlers[command].call(this, params);
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventAnswer = function(params)
	{
		var senderId = params.senderId;

		if(senderId === this.userId)
		{
			return this.__onPullEventAnswerSelf(params);
		}

		if(!this.peers[senderId])
		{
			return;
		}

		this.peers[senderId].setReady(true);
	};

	BX.Call.VoximplantCall.prototype.__onPullEventAnswerSelf = function(params)
	{
		if(params.callInstanceId === this.instanceId)
		{
			return;
		}

		// call was answered elsewhere
		this.destroy();
	};


	BX.Call.VoximplantCall.prototype.__onPullEventHangup = function(params)
	{
		var senderId = params.senderId;

		if(this.userId == senderId && this.instanceId != params.callInstanceId)
		{
			// Call declined by the same user elsewhere
			this.destroy();
			return;
		}

		if(!this.peers[senderId])
			return;

		this.peers[senderId].setReady(false);

		if(params.code == 603)
		{
			this.peers[senderId].setDeclined(true);
		}

		if(!this.isAnyoneParticipating())
		{
			this.hangup();
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventUsersInvited = function(params)
	{
		this.log('__onPullEventUsersInvited', params);
		var users = params.users;

		for(var i = 0; i < users.length; i++)
		{
			var userId = users[i];
			if(this.peers[userId])
			{
				if(this.peers[userId].calculatedState === BX.Call.UserState.Failed || this.peers[userId].calculatedState === BX.Call.UserState.Idle)
				{
					this.peers[userId].onInvited();
				}
			}
			else
			{
				this.peers[userId] = this.createPeer(userId);
				this.runCallback(BX.Call.Event.onUserInvited, {
					userId: userId
				});
				this.peers[userId].onInvited();
			}
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventFinish = function(params)
	{
		this.destroy();
	};

	BX.Call.VoximplantCall.prototype.__onMicAccessResult = function(e)
	{
		this.log("__onMicAccessResult", e);
		if(e.result)
		{
			/*this.attachVoiceDetection(e.stream);*/
			this.runCallback(BX.Call.Event.onLocalMediaReceived, {
				tag: 'main',
				stream: e.stream
			});
		}
		else
		{
			this.log("Could not get access to media input devices", e);
		}
	};

	BX.Call.VoximplantCall.prototype.__onLocalDevicesUpdated = function(e)
	{
		this.log("__onLocalDevicesUpdated", e);
	};

	BX.Call.VoximplantCall.prototype.__onLocalMediaRendererAdded = function(e)
	{
		this.log("__onLocalMediaRendererAdded", e);

		var renderer = e.renderer;
		if(renderer.kind === "sharing")
		{
			this.runCallback(BX.Call.Event.onLocalMediaReceived, {
				tag: "screen",
				stream: renderer.stream
			});
		}
	};

	BX.Call.VoximplantCall.prototype.__onBeforeLocalMediaRendererRemoved = function(e)
	{
		this.log("__onBeforeLocalMediaRendererRemoved", e);

		var renderer = e.renderer;
		if(renderer.kind === "sharing")
		{
			this.runCallback(BX.Call.Event.onLocalMediaStopped, {
				tag: "screen"
			});
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallDisconnected = function(e)
	{
		this.log("__onCallDisconnected", e);
		this.destroy();
	};

	BX.Call.VoximplantCall.prototype.__onCallEndpointAdded = function(e)
	{
		this.log("__onCallEndpointAdded", e);
		var self = this;
		var endpoint = e.endpoint;
		var userName = endpoint.userName;

		if(BX.type.isNotEmptyString(userName) && userName.substr(0, 4) == 'user')
		{
			// user connected to conference
			var userId = parseInt(userName.substr(4));
			if(this.peers[userId])
			{
				this.peers[userId].setEndpoint(endpoint);
			}
		}
		else
		{
			endpoint.addEventListener(VoxImplant.EndpointEvents.InfoUpdated, function(e)
			{
				this.log("VoxImplant.EndpointEvents.InfoUpdated", e);
				var endpoint = e.endpoint;
				var userName = endpoint.userName;

				if(BX.type.isNotEmptyString(userName) && userName.substr(0, 4) == 'user')
				{
					// user connected to conference
					var userId = parseInt(userName.substr(4));
					if(this.peers[userId])
					{
						this.peers[userId].setEndpoint(endpoint);
					}
				}

			}.bind(this));

			this.log('Unknown endpoint ' + userName);
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallMessageReceived = function(e)
	{
		var message;

		try
		{
			message = JSON.parse(e.text);
		}
		catch(err)
		{
			this.log("Could not parse scenario message.", err)
		}

		var eventName = message.eventName;
		if(eventName === clientEvents.voiceStarted)
		{
			this.runCallback(BX.Call.Event.onUserVoiceStarted, {
				userId: message.senderId
			});
		}
		else if(eventName === clientEvents.voiceStopped)
		{
			this.runCallback(BX.Call.Event.onUserVoiceStopped, {
				userId: message.senderId
			});
		}
		else
		{
			this.log("Unknown scenario event");
		}
	};

	BX.Call.VoximplantCall.prototype.destroy = function()
	{
		if(this.voximplantCall)
		{
			if(this.voximplantCall.state() != "ENDED")
			{
				this.voximplantCall.hangup();
			}
		}
		this.voximplantCall = null;

		for(var userId in this.peers)
		{
			if(this.peers.hasOwnProperty(userId))
			{
				this.peers[userId].destroy();
			}
		}

		this.removeClientEvents();

		clearInterval(this.pingInterval);
		this.runCallback(BX.Call.Event.onDestroy);
	};


	BX.Call.VoximplantCall.Signaling = function(params)
	{
		this.call = params.call;
	};

	BX.Call.VoximplantCall.Signaling.prototype.inviteUsers = function(data)
	{
		return this.__runAjaxAction(ajaxActions.invite, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendAnswer = function(data)
	{
		return this.__runAjaxAction(ajaxActions.answer, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendCancel = function(data)
	{
		return this.__runAjaxAction(ajaxActions.cancel, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendHangup = function(data)
	{
		return this.__runAjaxAction(ajaxActions.hangup, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendVoiceStarted = function(data)
	{
		return this.__sendMessage(clientEvents.voiceStarted, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendVoiceStopped = function(data)
	{
		return this.__sendMessage(clientEvents.voiceStopped, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendPing = function(data)
	{
		this.__runAjaxAction(ajaxActions.ping, {retransmit: false});
	};

	BX.Call.VoximplantCall.Signaling.prototype.__sendMessage = function(eventName, data)
	{
		if(!this.call.voximplantCall)
		{
			return;
		}

		if(!BX.type.isPlainObject(data))
		{
			data = {};
		}
		data.eventName = eventName;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();

		this.call.voximplantCall.sendMessage(JSON.stringify(data));
	};

	BX.Call.VoximplantCall.Signaling.prototype.__runAjaxAction = function(signalName, data)
	{
		if(!BX.type.isPlainObject(data))
		{
			data = {};
		}

		data.callId = this.call.id;
		data.callInstanceId = this.call.instanceId;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();
		return BX.ajax.runAction(signalName, {data: data});
	};

	BX.Call.VoximplantCall.Peer = function(params)
	{
		this.userId = params.userId;
		this.call = params.call;

		this.ready = !!params.ready;
		this.calling = false;
		this.declined = false;
		this.inviteTimeout = false;
		this.endpoint = null;

		this.stream = null;

		this.tracks = {
			audio: null,
			video: null,
			sharing: null
		};

		this.callingTimeout = 0;

		this.callbacks = {
			onStateChanged: BX.type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onStreamReceived: BX.type.isFunction(params.onStreamReceived) ? params.onStreamReceived : BX.DoNothing,
			onStreamRemoved: BX.type.isFunction(params.onStreamRemoved) ? params.onStreamRemoved : BX.DoNothing
		};

		// event handlers
		this.__onEndpointRemoteMediaAddedHandler = this.__onEndpointRemoteMediaAdded.bind(this);
		this.__onEndpointRemoteMediaRemovedHandler = this.__onEndpointRemoteMediaRemoved.bind(this);
		this.__onEndpointRemovedHandler = this.__onEndpointRemoved.bind(this);


		this.calculatedState = this.calculateState();
	};

	BX.Call.VoximplantCall.Peer.prototype = {

		setReady: function(ready)
		{
			this.ready = ready;
			if(this.calling)
			{
				clearTimeout(this.callingTimeout);
				this.calling = false;
			}
			this.updateCalculatedState();
		},

		setDeclined: function(declined)
		{
			this.declined = declined;
			if(this.calling)
			{
				clearTimeout(this.callingTimeout);
				this.calling = false;
			}
			this.updateCalculatedState();
		},

		setEndpoint: function(endpoint)
		{
			this.log("Adding endpoint with " + endpoint.mediaRenderers.length + " media renderers");

			if(!this.ready)
			{
				this.setReady(true);
			}

			if(this.endpoint)
			{
				throw new Error("Endpoint already exists for user " + this.userId)
			}

			this.endpoint = endpoint;

			for(var i = 0; i < this.endpoint.mediaRenderers.length; i++)
			{
				this.addMediaRenderer(this.endpoint.mediaRenderers[i]);
				if(this.endpoint.mediaRenderers[i].element)
				{
					BX.remove(this.endpoint.mediaRenderers[i].element);
				}
			}

			this.bindEndpointEventHandlers();
		},

		addMediaRenderer: function(mediaRenderer)
		{
			this.log('Adding media renderer');
			if(!this.stream)
			{
				this.stream = new MediaStream();
			}

			mediaRenderer.stream.getTracks().forEach(function(track)
			{
				if (track.kind == "audio")
				{
					this.tracks.audio = track;
				}
				else if (track.kind == "video")
				{
					if(mediaRenderer.kind == "sharing")
					{
						this.tracks.sharing = track;
					}
					else
					{
						this.tracks.video = track;
					}
				}
				else
				{
					this.log("Unknown track kind " + track.kind);
				}

			}, this);

			this.updateMediaStream();
			this.updateCalculatedState();
		},

		updateMediaStream: function()
		{
			if(!this.stream)
			{
				this.stream = new MediaStream();
			}

			this.stream.getTracks().forEach(function(track)
			{
				if(!this.hasTrack(track))
				{
					this.stream.removeTrack(track);
				}
			}, this);

			if(this.tracks.audio && !this.stream.getTrackById(this.tracks.audio.id))
			{
				this.stream.addTrack(this.tracks.audio);
			}

			if(this.tracks.sharing)
			{
				if(this.tracks.video && this.stream.getTrackById(this.tracks.video.id))
				{
					this.stream.removeTrack(this.tracks.video);
				}

				if(!this.stream.getTrackById(this.tracks.sharing.id))
				{
					this.stream.addTrack(this.tracks.sharing);
				}
			}
			else
			{
				if (this.tracks.video && !this.stream.getTrackById(this.tracks.video.id))
				{
					this.stream.addTrack(this.tracks.video);
				}
			}

			this.callbacks.onStreamReceived({
				userId: this.userId,
				stream: this.stream
			});
		},

		hasTrack: function(track)
		{
			for (var kind in this.tracks)
			{
				if (!this.tracks.hasOwnProperty(kind))
				{
					continue;
				}

				if(this.tracks.kind && this.tracks.kind.id == track.id)
				{
					return true;
				}
			}

			return false;
		},

		removeTrack: function(track)
		{
			for (var kind in this.tracks)
			{
				if (!this.tracks.hasOwnProperty(kind))
				{
					continue;
				}

				var localTrackId = this.tracks[kind] ? this.tracks[kind].id : '';
				if(localTrackId == track.id)
				{
					this.tracks[kind] = null;
				}
			}
		},

		bindEndpointEventHandlers: function()
		{
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.__onEndpointRemoteMediaAddedHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.__onEndpointRemoteMediaRemovedHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.Removed, this.__onEndpointRemovedHandler);
		},

		removeEndpointEventHandlers: function()
		{
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.__onEndpointRemoteMediaAddedHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.__onEndpointRemoteMediaRemovedHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.Removed, this.__onEndpointRemovedHandler);
		},

		calculateState: function()
		{
			if(this.stream)
				return BX.Call.UserState.Connected;

			if(this.endpoint)
				return BX.Call.UserState.Connecting;

			if(this.calling)
				return BX.Call.UserState.Calling;

			if(this.inviteTimeout)
				return BX.Call.UserState.Failed;

			if(this.declined)
				return BX.Call.UserState.Declined;

			if(this.ready)
				return BX.Call.UserState.Ready;

			return BX.Call.UserState.Idle;
		},

		updateCalculatedState: function()
		{
			var calculatedState = this.calculateState();

			if(this.calculatedState != calculatedState)
			{
				this.callbacks.onStateChanged({
					userId: this.userId,
					state: calculatedState,
					previousState: this.calculatedState
				});
				this.calculatedState = calculatedState;
			}
		},

		isParticipating: function()
		{
			return ((this.calling || this.ready || this.endpoint) && !this.declined);
		},

		onInvited: function()
		{
			this.ready = false;
			this.inviteTimeout = false;
			this.declined = false;
			this.calling = true;

			if(this.callingTimeout)
			{
				clearTimeout(this.callingTimeout);
			}
			this.callingTimeout = setTimeout(this.onInviteTimeout.bind(this), 30000);
			this.updateCalculatedState();
		},

		onInviteTimeout: function()
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
			this.inviteTimeout = true;
			this.updateCalculatedState();
		},

		__onEndpointRemoteMediaAdded: function(e)
		{
			this.log("VoxImplant.EndpointEvents.RemoteMediaAdded", e);

			this.addMediaRenderer(e.mediaRenderer);
		},

		__onEndpointRemoteMediaRemoved: function(e)
		{
			this.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, track id: " + e.mediaRenderer.stream.getTracks()[0].id, e);


			e.mediaRenderer.stream.getTracks().forEach(function(track)
			{
				this.removeTrack(track);
			}, this);

			if(this.stream)
			{
				this.updateMediaStream();
			}

			this.updateCalculatedState();
		},

		__onEndpointRemoved: function(e)
		{
			this.log("VoxImplant.EndpointEvents.Removed", e);

			if(this.endpoint)
			{
				this.removeEndpointEventHandlers();
				this.endpoint = null;
			}
			if(this.stream)
			{
				this.stream = null;
			}
			for(var kind in this.tracks)
			{
				if(this.tracks.hasOwnProperty(kind))
				{
					this.tracks[kind] = null;
				}
			}

			this.updateCalculatedState();
		},

		log: function()
		{
			this.call.log.apply(this.call, arguments);
		},

		destroy: function()
		{
			if(this.stream)
			{
				this.stream = null;
			}
			if(this.endpoint)
			{
				this.endpoint = null;
			}
			for(var kind in this.tracks)
			{
				if(this.tracks.hasOwnProperty(kind))
				{
					this.tracks[kind] = null;
				}
			}
			clearTimeout(this.callingTimeout);
			this.callingTimeout = null;
		}
	};
})();