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

	BX.namespace('BX.Call');

	var ajaxActions = {
		invite: 'im.call.invite',
		cancel: 'im.call.cancel',
		answer: 'im.call.answer',
		decline: 'im.call.decline',
		hangup: 'im.call.hangup',
		ping: 'im.call.ping'
	};

	var pullEvents = {
		ping: 'Call::ping',
		answer: 'Call::answer',
		hangup: 'Call::hangup',
		userInviteTimeout: 'Call::userInviteTimeout',
		repeatAnswer: 'Call::repeatAnswer',
	};

	var clientEvents = {
		voiceStarted: 'Call::voiceStarted',
		voiceStopped: 'Call::voiceStopped',
		microphoneState: 'Call::microphoneState',
		cameraState: 'Call::cameraState',
		videoPaused: 'Call::videoPaused',
		screenState: 'Call::screenState',
		recordState: 'Call::recordState',
		floorRequest: 'Call::floorRequest',
		emotion: 'Call::emotion',
		customMessage: 'Call::customMessage',
		showUsers: 'Call::showUsers',
		showAll: 'Call::showAll',
		hideAll: 'Call::hideAll',

		joinRoom: 'Call::joinRoom',
		leaveRoom: 'Call::leaveRoom',
		listRooms: 'Call::listRooms',
		requestRoomSpeaker: 'Call::requestRoomSpeaker',
	};

	var scenarioEvents = {
		viewerJoined: 'Call::viewerJoined',
		viewerLeft: 'Call::viewerLeft',

		joinRoomOffer: 'Call::joinRoomOffer',
		transferRoomHost: 'Call::transferRoomHost',
		listRoomsResponse: 'Call::listRoomsResponse',
		roomUpdated: 'Call::roomUpdated',
	};

	var VoximplantCallEvent = {
		onCallConference: 'VoximplantCall::onCallConference'
	};

	var pingPeriod = 5000;
	var backendPingPeriod = 25000;

	var reinvitePeriod = 5500;

	var connectionRestoreTime = 15000;

	var MAX_USERS_WITHOUT_SIMULCAST = 6;

	// screensharing workaround
	if(window["BXDesktopSystem"])
	{
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
	}

	BX.Call.VoximplantCall = function(config)
	{
		BX.Call.VoximplantCall.superclass.constructor.apply(this, arguments);

		this.videoQuality = BX.Call.Quality.VeryHigh; // initial video quality. will drop on new peers connecting

		this.voximplantCall = null;

		this.signaling = new BX.Call.VoximplantCall.Signaling({
			call: this
		});

		this.peers = {};
		this.joinedElsewhere = false;
		this.joinedAsViewer = false;
		this.localVideoShown = false;
		this._localUserState = BX.Call.UserState.Idle;
		this.clientEventsBound = false;
		this._screenShared = false;
		this.videoAllowedFrom = BX.Call.UserMnemonic.all;
		this.direction = BX.Call.EndpointDirection.SendRecv;

		this.localVAD = null;
		this.microphoneLevelInterval = null;

		this.rooms = {};

		Object.defineProperty(this, "screenShared", {
			get: function() {
				return this._screenShared;
			},
			set: function(screenShared) {
				if (screenShared != this._screenShared)
				{
					this._screenShared = screenShared;
					this.signaling.sendScreenState(this._screenShared);
				}
			}
		});

		Object.defineProperty(this, "localUserState", {
			get: function()
			{
				return this._localUserState
			},
			set: function (state)
			{
				if (state == this._localUserState)
				{
					return;
				}
				this.runCallback(BX.Call.Event.onUserStateChanged, {
					userId: this.userId,
					state: state,
					previousState: this._localUserState,
					direction: this.direction,
				});
				this._localUserState = state;
			}
		});

		this.deviceList = [];

		// event handlers
		this.__onLocalDevicesUpdatedHandler = this.__onLocalDevicesUpdated.bind(this);
		this.__onLocalMediaRendererAddedHandler = this.__onLocalMediaRendererAdded.bind(this);
		this.__onBeforeLocalMediaRendererRemovedHandler = this.__onBeforeLocalMediaRendererRemoved.bind(this);
		this.__onMicAccessResultHandler = this.__onMicAccessResult.bind(this);
		this.__onClientReconnectingHandler = this.__onClientReconnecting.bind(this);
		this.__onClientReconnectedHandler = this.__onClientReconnected.bind(this);

		this.__onCallDisconnectedHandler = this.__onCallDisconnected.bind(this);
		this.__onCallMessageReceivedHandler = this.__onCallMessageReceived.bind(this);
		this.__onCallStatsReceivedHandler = this.__onCallStatsReceived.bind(this);
		this.__onCallEndpointAddedHandler = this.__onCallEndpointAdded.bind(this);
		this.__onCallReconnectingHandler = this.__onCallReconnecting.bind(this);
		this.__onCallReconnectedHandler = this.__onCallReconnected.bind(this);

		this.__onWindowUnloadHandler = this.__onWindowUnload.bind(this);
		window.addEventListener("unload", this.__onWindowUnloadHandler);

		this.initPeers();

		this.pingUsersInterval = setInterval(this.pingUsers.bind(this), pingPeriod);
		this.pingBackendInterval = setInterval(this.pingBackend.bind(this), backendPingPeriod);

		this.lastPingReceivedTimeout = null;
		this.lastSelfPingReceivedTimeout = null;

		this.reinviteTimeout = null;

		// There are two kinds of reconnection events: from call (for media connection) and from client (for signaling).
		// So we have to use counter to convert these two events to one
		this._reconnectionEventCount = 0;
		Object.defineProperty(this, 'reconnectionEventCount', {
			get: function()
			{
				return this._reconnectionEventCount;
			},
			set: function(newValue)
			{
				if (this._reconnectionEventCount === 0 && newValue > 0)
				{
					this.runCallback(BX.Call.Event.onReconnecting);
				}
				if (newValue === 0)
				{
					this.runCallback(BX.Call.Event.onReconnected);
				}
				this._reconnectionEventCount = newValue;
			}
		})
	};

	BX.extend(BX.Call.VoximplantCall, BX.Call.AbstractCall);

	BX.Call.VoximplantCall.prototype.initPeers = function ()
	{
		this.users.forEach(function(userId)
		{
			userId = Number(userId);
			this.peers[userId] = this.createPeer(userId);
		}, this);
	};

	BX.Call.VoximplantCall.prototype.reinitPeers = function ()
	{
		for (var userId in this.peers)
		{
			if(this.peers.hasOwnProperty(userId) && this.peers[userId])
			{
				this.peers[userId].destroy();
				this.peers[userId] = null;
			}
		}

		this.initPeers();
	};

	BX.Call.VoximplantCall.prototype.pingUsers = function()
	{
		if (this.ready)
		{
			var users = this.users.concat(this.userId);
			this.signaling.sendPingToUsers({userId: users}, true);
		}
	};

	BX.Call.VoximplantCall.prototype.pingBackend = function()
	{
		if (this.ready)
		{
			this.signaling.sendPingToBackend();
		}
	};

	BX.Call.VoximplantCall.prototype.createPeer = function (userId)
	{
		var incomingVideoAllowed;
		if (this.videoAllowedFrom === BX.Call.UserMnemonic.all)
		{
			incomingVideoAllowed = true;
		}
		else if (this.videoAllowedFrom === BX.Call.UserMnemonic.none)
		{
			incomingVideoAllowed = false;
		}
		else if (BX.type.isArray(this.videoAllowedFrom))
		{
			incomingVideoAllowed = this.videoAllowedFrom.some(function(allowedUserId) {
				return allowedUserId == userId;
			});
		}
		else
		{
			incomingVideoAllowed = true;
		}

		return new BX.Call.VoximplantCall.Peer({
			call: this,
			userId: userId,
			ready: userId == this.initiatorId,
			isIncomingVideoAllowed: incomingVideoAllowed,

			onMediaReceived: function(e)
			{
				console.log("onMediaReceived: ", e);
				this.runCallback(BX.Call.Event.onRemoteMediaReceived, e);
				if (e.kind === 'video')
				{
					this.runCallback(BX.Call.Event.onUserVideoPaused, {
						userId: userId,
						videoPaused: false
					});
				}
			}.bind(this),
			onMediaRemoved: function(e)
			{
				console.log("onMediaRemoved: ", e);
				this.runCallback(BX.Call.Event.onRemoteMediaStopped, e);
			}.bind(this),
			onVoiceStarted: function(e)
			{
				// todo: uncomment to switch to SDK VAD events
				/*this.runCallback(BX.Call.Event.onUserVoiceStarted, {
					userId: userId
				});*/
			}.bind(this),
			onVoiceEnded: function(e)
			{
				// todo: uncomment to switch to SDK VAD events
				/*this.runCallback(BX.Call.Event.onUserVoiceStopped, {
					userId: userId
				});*/
			}.bind(this),
			onStateChanged: this.__onPeerStateChanged.bind(this),
			onInviteTimeout: this.__onPeerInviteTimeout.bind(this),

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

	BX.Call.VoximplantCall.prototype.getUserCount = function ()
	{
		return Object.keys(this.peers).length;
	};

	BX.Call.VoximplantCall.prototype.getClient = function()
	{
		return new Promise(function(resolve, reject)
		{
			BX.Voximplant.getClient({restClient: BX.CallEngine.getRestClient()}).then(function(client)
			{
				client.enableSilentLogging();
				client.setLoggerCallback(function(e)
				{
					this.log(e.label + ": " + e.message);
				}.bind(this));
				this.log("User agent: " + navigator.userAgent);
				this.log("Voximplant SDK version: " + VoxImplant.version);

				this.bindClientEvents();

				resolve(client);
			}.bind(this)).catch(reject);
		}.bind(this));
	};

	BX.Call.VoximplantCall.prototype.bindClientEvents = function()
	{
		var streamManager = VoxImplant.Hardware.StreamManager.get();

		if(!this.clientEventsBound)
		{
			VoxImplant.getInstance().on(VoxImplant.Events.MicAccessResult, this.__onMicAccessResultHandler);
			if (VoxImplant.Events.Reconnecting)
			{
				VoxImplant.getInstance().on(VoxImplant.Events.Reconnecting, this.__onClientReconnectingHandler);
				VoxImplant.getInstance().on(VoxImplant.Events.Reconnected, this.__onClientReconnectedHandler);
			}

			streamManager.on(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.__onLocalDevicesUpdatedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.__onLocalMediaRendererAddedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererUpdated, this.__onLocalMediaRendererAddedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.__onBeforeLocalMediaRendererRemovedHandler);
			this.clientEventsBound = true;
		}
	};

	BX.Call.VoximplantCall.prototype.removeClientEvents = function()
	{
		if (!('VoxImplant' in window))
		{
			return;
		}

		VoxImplant.getInstance().off(VoxImplant.Events.MicAccessResult, this.__onMicAccessResultHandler);
		if (VoxImplant.Events.Reconnecting)
		{
			VoxImplant.getInstance().off(VoxImplant.Events.Reconnecting, this.__onClientReconnectingHandler);
			VoxImplant.getInstance().off(VoxImplant.Events.Reconnected, this.__onClientReconnectedHandler);
		}

		var streamManager = VoxImplant.Hardware.StreamManager.get();
		streamManager.off(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.__onLocalDevicesUpdatedHandler);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.__onLocalMediaRendererAddedHandler);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.__onBeforeLocalMediaRendererRemovedHandler);
		this.clientEventsBound = false;
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
			this.signaling.sendMicrophoneState(!this.muted);
		}
	};

	BX.Call.VoximplantCall.prototype.isMuted = function()
	{
		return this.muted;
	}

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
			if(videoEnabled)
			{
				this._showLocalVideo();
			}
			else
			{
				if(this.localVideoShown)
				{
					VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(function()
					{
						this.localVideoShown = false;
						this.runCallback(BX.Call.Event.onLocalMediaReceived, {
							tag: "main",
							stream: new MediaStream(),
						});
					}.bind(this));
				}
			}

			this.voximplantCall.sendVideo(this.videoEnabled);
			this.signaling.sendCameraState(this.videoEnabled);
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
			VoxImplant.Hardware.CameraManager.get().getInputDevices().then(function()
			{
				VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, this.constructCameraParams());
			}.bind(this));
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
			VoxImplant.Hardware.AudioDeviceManager.get().getInputDevices().then(function(){
				VoxImplant.Hardware.AudioDeviceManager.get().setCallAudioSettings(this.voximplantCall, {
					inputId: this.microphoneId
				});
			}.bind(this));
		}
	};

	BX.Call.VoximplantCall.prototype.getCurrentMicrophoneId = function()
	{
		if (this.voximplantCall.peerConnection.impl.getTransceivers)
		{
			var transceivers = this.voximplantCall.peerConnection.impl.getTransceivers();
			if(transceivers.length > 0)
			{
				var audioTrack = transceivers[0].sender.track;
				var audioTrackSettings = audioTrack.getSettings();
				return audioTrackSettings.deviceId;
			}
		}
		return this.microphoneId;
	};

	BX.Call.VoximplantCall.prototype.constructCameraParams = function()
	{
		var result = {};

		if(this.cameraId)
		{
			result.cameraId = this.cameraId;
		}

		result.videoQuality = this.videoHd ? VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_HD : VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_nHD;
		result.facingMode = true;
		return result;
	};

	BX.Call.VoximplantCall.prototype.useHdVideo = function(flag)
	{
		this.videoHd = (flag === true);
	};

	BX.Call.VoximplantCall.prototype.requestFloor = function(requestActive)
	{
		this.signaling.sendFloorRequest(requestActive);
	};

	BX.Call.VoximplantCall.prototype.sendRecordState = function(recordState)
	{
		this.signaling.sendRecordState(recordState);
	};

	BX.Call.VoximplantCall.prototype.sendEmotion = function(toUserId, emotion)
	{
		this.signaling.sendEmotion(toUserId, emotion);
	};

	BX.Call.VoximplantCall.prototype.sendCustomMessage = function(message, repeatOnConnect)
	{
		this.signaling.sendCustomMessage(message, repeatOnConnect);
	};

	/**
	 * Updates list of users,
	 * @param {BX.Call.UserMnemonic | int[]} userList
	 */
	BX.Call.VoximplantCall.prototype.allowVideoFrom = function(userList)
	{
		if (this.videoAllowedFrom == userList)
		{
			return;
		}
		this.videoAllowedFrom = userList;

		if (userList === BX.Call.UserMnemonic.all)
		{
			this.signaling.sendShowAll();
			userList = Object.keys(this.peers);
		}
		else if (userList === BX.Call.UserMnemonic.none)
		{
			this.signaling.sendHideAll();
			userList = [];
		}
		else if (BX.type.isArray(userList))
		{
			this.signaling.sendShowUsers(userList)
		}
		else
		{
			throw new Error("userList is in wrong format");
		}

		var users = {};
		userList.forEach(function(userId)
		{
			users[userId] = true;
		});

		for (var userId in this.peers)
		{
			if(!this.peers.hasOwnProperty(userId))
			{
				continue;
			}
			if(users[userId])
			{
				this.peers[userId].allowIncomingVideo(true);
			}
			else
			{
				this.peers[userId].allowIncomingVideo(false);
			}
		}
	};

	/**
	 * Sets bitrate cap for outgoing video
	 * @param bitrate
	 */
	BX.Call.VoximplantCall.prototype._setMaxBitrate = function(bitrate)
	{
		if(this.voximplantCall)
		{
			var transceivers = this.voximplantCall.peerConnection.getTransceivers();
			if(!transceivers)
			{
				return;
			}
			transceivers.forEach(function (tr)
			{
				if(tr.sender && tr.sender.track && tr.sender.track.kind === 'video' && !tr.stoped && tr.currentDirection.indexOf('send') !== -1)
				{
					var sender = tr.sender;
					var parameters = sender.getParameters();
					if (!parameters.encodings)
					{
						parameters.encodings = [{}];
					}
					if(bitrate === 0)
					{
						delete parameters.encodings[0].maxBitrate;
					}
					else
					{
						parameters.encodings[0].maxBitrate = bitrate * 1000;
					}
					sender.setParameters(parameters);
				}
			}, this);
		}
	};

	BX.Call.VoximplantCall.prototype._showLocalVideo = function()
	{
		return new Promise(function(resolve, reject)
		{
			VoxImplant.Hardware.StreamManager.get().showLocalVideo(false).then(
				function()
				{
					this.localVideoShown = true;
					resolve();
				}.bind(this),
				function()
				{
					this.localVideoShown = true;
					resolve();
				}.bind(this)
			)
		}.bind(this))
	};

	BX.Call.VoximplantCall.prototype._hideLocalVideo = function()
	{
		return new Promise(function(resolve, reject)
		{
			if (!('VoxImplant' in window))
			{
				resolve();
				return;
			}

			VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(
				function()
				{
					this.localVideoShown = false;
					resolve();
				}.bind(this),
				function()
				{
					this.localVideoShown = false;
					resolve();
				}.bind(this)
			);
		})
	};

	BX.Call.VoximplantCall.prototype.startScreenSharing = function()
	{
		if(!this.voximplantCall)
		{
			return;
		}

		var showLocalView = !this.videoEnabled;
		var replaceTrack = this.videoEnabled || this.screenShared;

		this.voximplantCall.shareScreen(showLocalView, replaceTrack).then(function()
		{
			this.log("Screen shared");
			this.screenShared = true;
		}.bind(this)).catch(function(error)
		{
			console.error(error);
			this.log("Screen sharing error:", error)
		}.bind(this));
	};

	BX.Call.VoximplantCall.prototype.stopScreenSharing = function()
	{
		if(!this.voximplantCall)
		{
			return;
		}

		this.voximplantCall.stopSharingScreen().then(function()
		{
			this.log("Screen is no longer shared");
			this.screenShared = false;
		}.bind(this));
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
		this.ready = true;
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		var users = BX.type.isArray(config.users) ? config.users : this.users;

		this.attachToConference().then(function()
		{
			self.signaling.sendPingToUsers({userId: users});

			if(users.length > 0)
			{
				return self.signaling.inviteUsers({
					userIds: users,
					video: self.videoEnabled ? 'Y' : 'N'
				})
			}
		}).then(function(response)
		{
			self.state = BX.Call.State.Connected;
			self.runCallback(BX.Call.Event.onJoin, {
				local: true
			});
			for (var i = 0; i < users.length; i++)
			{
				var userId = parseInt(users[i], 10);
				if(!self.users.includes(userId))
				{
					self.users.push(userId);
				}
				if(!self.peers[userId])
				{
					self.peers[userId] = self.createPeer(userId);

					if (self.type === BX.Call.Type.Instant)
					{
						self.runCallback(BX.Call.Event.onUserInvited, {
							userId: userId
						});
					}
				}
				if (self.type === BX.Call.Type.Instant)
				{
					self.peers[userId].onInvited();
					self.scheduleRepeatInvite();
				}
			}
		}).catch(self.onFatalError.bind(self));
	};

	BX.Call.VoximplantCall.prototype.scheduleRepeatInvite = function()
	{
		clearTimeout(this.reinviteTimeout);
		this.reinviteTimeout = setTimeout(this.repeatInviteUsers.bind(this), reinvitePeriod)
	};

	BX.Call.VoximplantCall.prototype.repeatInviteUsers = function()
	{
		clearTimeout(this.reinviteTimeout);
		if(!this.ready)
		{
			return;
		}
		var usersToRepeatInvite = [];

		for (var userId in this.peers)
		{
			if(this.peers.hasOwnProperty(userId) && this.peers[userId].calculatedState === BX.Call.UserState.Calling)
			{
				usersToRepeatInvite.push(userId);
			}
		}

		if(usersToRepeatInvite.length === 0)
		{
			return;
		}
		this.signaling.inviteUsers({
			userIds: usersToRepeatInvite,
			video: this.videoEnabled ? 'Y' : 'N',
			isRepeated: 'Y',
		}).then(function()
		{
			this.scheduleRepeatInvite();
		}.bind(this));
	};

	/**
	 * @param {Object} config
	 * @param {bool} [config.useVideo]
	 */
	BX.Call.VoximplantCall.prototype.answer = function(config)
	{
		this.ready = true;
		var joinAsViewer = BX.prop.getBoolean(config, "joinAsViewer", false);
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		this.videoEnabled = (config.useVideo == true);

		if (!joinAsViewer)
		{
			this.signaling.sendAnswer();
		}
		this.attachToConference({joinAsViewer: joinAsViewer}).then(() =>
		{
			this.log("Attached to conference");
			this.state = BX.Call.State.Connected;
			this.runCallback(BX.Call.Event.onJoin, {
				local: true
			});
		}).catch((err) => {
			this.onFatalError(err);
		});
	};

	BX.Call.VoximplantCall.prototype.decline = function(code)
	{
		this.ready = false;
		var data = {
			callId: this.id,
			callInstanceId: this.instanceId,
		};
		if(code)
		{
			data.code = code
		}

		BX.CallEngine.getRestClient().callMethod(ajaxActions.decline, data);
	};

	BX.Call.VoximplantCall.prototype.hangup = function(code, reason)
	{
		if(!this.ready)
		{
			var error = new Error("Hangup in wrong state");
			this.log(error);
			return;
		}

		var tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Hangup received \n" + tempError.stack);

		if (this.localVAD)
		{
			this.localVAD.destroy();
			this.localVAD = null;
		}
		clearInterval(this.microphoneLevelInterval);

		var data = {};
		this.ready = false;
		if(typeof(code) != 'undefined')
		{
			data.code = code;
		}
		if(typeof(reason) != 'undefined')
		{
			data.reason = reason;
		}
		this.state = BX.Call.State.Proceeding;
		this.runCallback(BX.Call.Event.onLeave, {local: true});

		//clone users and append current user id to send event to all participants of the call
		data.userId = this.users.slice(0).concat(this.userId);
		this.signaling.sendHangup(data);
		this.muted = false;

		// for future reconnections
		this.reinitPeers();

		if(this.voximplantCall)
		{
			this.voximplantCall._replaceVideoSharing = false;
			try
			{
				this.voximplantCall.hangup();
			}
			catch (e)
			{
				this.log("Voximplant hangup error: ", e);
				console.error("Voximplant hangup error: ", e);
			}
		}
		else
		{
			this.log("Tried to hangup, but this.voximplantCall points nowhere");
			console.error("Tried to hangup, but this.voximplantCall points nowhere");
		}

		this.screenShared = false;
		this._hideLocalVideo();
	};

	BX.Call.VoximplantCall.prototype.attachToConference = function(options)
	{
		var self = this;

		var joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);

		return new Promise(function(resolve, reject)
		{
			if(self.voximplantCall && self.voximplantCall.state() === "CONNECTED")
			{
				if (self.joinedAsViewer === joinAsViewer)
				{
					return resolve();
				}
				else
				{
					return reject("Already joined call in another mode");
				}
			}

			self.direction = joinAsViewer ? BX.Call.EndpointDirection.RecvOnly : BX.Call.EndpointDirection.SendRecv;
			self.sendTelemetryEvent("call");

			self.getClient().then(function(voximplantClient)
			{
				self.localUserState = BX.Call.UserState.Connecting;

				// workaround to set default video settings before starting call. ugly, but I do not see another way
				VoxImplant.Hardware.CameraManager.get().setDefaultVideoSettings(self.constructCameraParams());
				if (self.microphoneId)
				{
					VoxImplant.Hardware.AudioDeviceManager.get().setDefaultAudioSettings({
						inputId: self.microphoneId
					});
				}

				if(self.videoEnabled)
				{
					self._showLocalVideo();
				}

				try
				{
					if (joinAsViewer)
					{
						self.voximplantCall = voximplantClient.joinAsViewer("bx_conf_" + self.id, {
							'X-Direction': BX.Call.EndpointDirection.RecvOnly
						});
					}
					else
					{
						self.voximplantCall = voximplantClient.callConference({
							number: "bx_conf_" + self.id,
							video: {sendVideo: self.videoEnabled, receiveVideo: true},
							// simulcast: (self.getUserCount() > MAX_USERS_WITHOUT_SIMULCAST),
							// simulcastProfileName: 'b24',
							customData: JSON.stringify({
								cameraState: self.videoEnabled,
							})
						});
					}
				}
				catch (e)
				{
					console.error(e);
					return reject(e);
				}
				self.joinedAsViewer = joinAsViewer;

				if(!self.voximplantCall)
				{
					self.log("Error: could not create voximplant call");
					return reject({code: "VOX_NO_CALL"});
				}

				self.runCallback(BX.Call.VoximplantCall.Event.onCallConference, {
					call: self
				});

				self.bindCallEvents();

				var onCallConnected = function()
				{
					self.log("Call connected");
					self.sendTelemetryEvent("connect");
					self.localUserState = BX.Call.UserState.Connected;

					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Failed, onCallFailed);

					self.voximplantCall.addEventListener(VoxImplant.CallEvents.Failed, self.__onCallDisconnectedHandler);

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
					else
					{
						self.runCallback(BX.Call.Event.onDeviceListUpdated, {
							deviceList: self.deviceList
						})
					}
					if(self.muted)
					{
						self.voximplantCall.muteMicrophone();
					}
					self.signaling.sendMicrophoneState(!self.muted);
					self.signaling.sendCameraState(self.videoEnabled);

					if (self.videoAllowedFrom == BX.Call.UserMnemonic.none)
					{
						self.signaling.sendHideAll();
					}
					else if (BX.type.isArray(self.videoAllowedFrom))
					{
						self.signaling.sendShowUsers(self.videoAllowedFrom);
					}

					resolve();
				};

				var onCallFailed = function(e)
				{
					self.log("Could not attach to conference", e);
					self.sendTelemetryEvent("connect_failure");
					self.localUserState = BX.Call.UserState.Failed;

					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
					self.voximplantCall.removeEventListener(VoxImplant.CallEvents.Failed, onCallFailed);

					var client = VoxImplant.getInstance();
					client.enableSilentLogging(false);
					client.setLoggerCallback(null);

					reject(e);
				};

				self.voximplantCall.addEventListener(VoxImplant.CallEvents.Connected, onCallConnected);
				self.voximplantCall.addEventListener(VoxImplant.CallEvents.Failed, onCallFailed);
			}).catch(self.onFatalError.bind(self));
		});
	};

	BX.Call.VoximplantCall.prototype.bindCallEvents = function()
	{
		this.voximplantCall.addEventListener(VoxImplant.CallEvents.Disconnected, this.__onCallDisconnectedHandler);
		this.voximplantCall.addEventListener(VoxImplant.CallEvents.MessageReceived, this.__onCallMessageReceivedHandler);
		if (BX.Call.Util.shouldCollectStats())
		{
			this.voximplantCall.addEventListener(VoxImplant.CallEvents.CallStatsReceived, this.__onCallStatsReceivedHandler);
		}

		this.voximplantCall.addEventListener(VoxImplant.CallEvents.EndpointAdded, this.__onCallEndpointAddedHandler);
		if (VoxImplant.CallEvents.Reconnecting)
		{
			this.voximplantCall.addEventListener(VoxImplant.CallEvents.Reconnecting, this.__onCallReconnectingHandler);
			this.voximplantCall.addEventListener(VoxImplant.CallEvents.Reconnected, this.__onCallReconnectedHandler);
		}
	};

	BX.Call.VoximplantCall.prototype.removeCallEvents = function()
	{
		if(this.voximplantCall)
		{
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Disconnected, this.__onCallDisconnectedHandler);
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.MessageReceived, this.__onCallMessageReceivedHandler);
			if (BX.Call.Util.shouldCollectStats())
			{
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.CallStatsReceived, this.__onCallStatsReceivedHandler);
			}
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.EndpointAdded, this.__onCallEndpointAddedHandler);
			if (VoxImplant.CallEvents.Reconnecting)
			{
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnecting, this.__onCallReconnectingHandler);
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnected, this.__onCallReconnectedHandler);
			}
		}
	};

	/**
	 * Adds new users to call
	 * @param {Number[]} users
	 */
	BX.Call.VoximplantCall.prototype.addJoinedUsers = function(users)
	{
		for(var i = 0; i < users.length; i++)
		{
			var userId = Number(users[i]);
			if(userId == this.userId || this.peers[userId])
			{
				continue;
			}
			this.peers[userId] = this.createPeer(userId);
			if(!this.users.includes(userId))
			{
				this.users.push(userId);
			}
			this.runCallback(BX.Call.Event.onUserInvited, {
				userId: userId
			});
		}
	};

	/**
	 * Adds users, invited by you or someone else
	 * @param {Number[]} users
	 */
	BX.Call.VoximplantCall.prototype.addInvitedUsers = function(users)
	{
		for(var i = 0; i < users.length; i++)
		{
			var userId = Number(users[i]);
			if(userId == this.userId)
			{
				continue;
			}

			if(this.peers[userId])
			{
				if(this.peers[userId].calculatedState === BX.Call.UserState.Failed || this.peers[userId].calculatedState === BX.Call.UserState.Idle)
				{
					if (this.type === BX.Call.Type.Instant)
					{
						this.peers[userId].onInvited();
					}
				}
			}
			else
			{
				this.peers[userId] = this.createPeer(userId);
				if (this.type === BX.Call.Type.Instant)
				{
					this.peers[userId].onInvited();
				}
			}
			if(!this.users.includes(userId))
			{
				this.users.push(userId);
			}
			this.runCallback(BX.Call.Event.onUserInvited, {
				userId: userId
			});
		}
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

	BX.Call.VoximplantCall.prototype.getParticipatingUsers = function()
	{
		var result = [];
		for (var userId in this.peers)
		{
			if(this.peers[userId].isParticipating())
			{
				result.push(userId);
			}
		}
		return result;
	};

	BX.Call.VoximplantCall.prototype.updateRoom = function(roomData)
	{
		if (!this.rooms[roomData.id])
		{
			this.rooms[roomData.id] = {
				id: roomData.id,
				users: roomData.users,
				speaker: roomData.speaker
			}
		}
		else
		{
			this.rooms[roomData.id].users = roomData.users;
			this.rooms[roomData.id].speaker = roomData.speaker;
		}
	}

	BX.Call.VoximplantCall.prototype.currentRoom = function()
	{
		return this._currentRoomId ? this.rooms[this._currentRoomId] : null;
	}

	BX.Call.VoximplantCall.prototype.isRoomSpeaker = function()
	{
		return this.currentRoom() ? this.currentRoom().speaker == this.userId : false;
	}

	BX.Call.VoximplantCall.prototype.joinRoom = function(roomId)
	{
		this.signaling.sendJoinRoom(roomId);
	}

	BX.Call.VoximplantCall.prototype.requestRoomSpeaker = function()
	{
		this.signaling.sendRequestRoomSpeaker(this._currentRoomId);
	}

	BX.Call.VoximplantCall.prototype.leaveCurrentRoom = function()
	{
		this.signaling.sendLeaveRoom(this._currentRoomId);
	}

	BX.Call.VoximplantCall.prototype.listRooms = function()
	{
		return new Promise((resolve, reject) =>
		{
			this.signaling.sendListRooms();
			this.__resolveListRooms = resolve;
		});
	}

	BX.Call.VoximplantCall.prototype.__onPeerStateChanged = function(e)
	{
		this.runCallback(BX.Call.Event.onUserStateChanged, e);

		if(!this.ready)
		{
			return;
		}
		if(e.state == BX.Call.UserState.Failed || e.state == BX.Call.UserState.Unavailable || e.state == BX.Call.UserState.Declined || e.state == BX.Call.UserState.Idle)
		{
			if(this.type == BX.Call.Type.Instant && !this.isAnyoneParticipating())
			{
				this.hangup();
			}
		}
	};

	BX.Call.VoximplantCall.prototype.__onPeerInviteTimeout = function(e)
	{
		if(!this.ready)
		{
			return;
		}
		this.signaling.sendUserInviteTimeout({
			userId: this.users,
			failedUserId: e.userId
		})
	};

	BX.Call.VoximplantCall.prototype.__onPullEvent = function(command, params, extra)
	{
		var handlers = {
			'Call::answer': this.__onPullEventAnswer.bind(this),
			'Call::hangup': this.__onPullEventHangup.bind(this),
			'Call::usersJoined': this.__onPullEventUsersJoined.bind(this),
			'Call::usersInvited': this.__onPullEventUsersInvited.bind(this),
			'Call::userInviteTimeout': this.__onPullEventUserInviteTimeout.bind(this),
			'Call::ping': this.__onPullEventPing.bind(this),
			'Call::finish': this.__onPullEventFinish.bind(this),
			'Call::repeatAnswer': this.__onPullEventRepeatAnswer.bind(this),
		};

		if(handlers[command])
		{
			if (command != 'Call::ping')
			{
				this.log("Signaling: " + command + "; Parameters: " + JSON.stringify(params));
			}
			handlers[command].call(this, params);
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventAnswer = function(params)
	{
		var senderId = Number(params.senderId);

		if(senderId == this.userId)
		{
			return this.__onPullEventAnswerSelf(params);
		}

		if(!this.peers[senderId])
		{
			this.peers[senderId] = this.createPeer(senderId);
			this.runCallback(BX.Call.Event.onUserInvited, {
				userId: senderId
			});
		}

		if(!this.users.includes(senderId))
		{
			this.users.push(senderId);
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
		this.joinedElsewhere = true;
		this.runCallback(BX.Call.Event.onJoin, {
			local: false
		});
	};


	BX.Call.VoximplantCall.prototype.__onPullEventHangup = function(params)
	{
		var senderId = params.senderId;

		if(this.userId == senderId && this.instanceId != params.callInstanceId)
		{
			// Call declined by the same user elsewhere
			this.runCallback(BX.Call.Event.onLeave, {local: false});
			return;
		}

		if(!this.peers[senderId])
			return;

		this.peers[senderId].setReady(false);

		if(params.code == 603)
		{
			this.peers[senderId].setDeclined(true);
		}
		else if (params.code == 486)
		{
			this.peers[senderId].setBusy(true);
			console.error("user " + senderId + " is busy");
		}

		if(this.ready && this.type == BX.Call.Type.Instant && !this.isAnyoneParticipating())
		{
			this.hangup();
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventUsersJoined = function(params)
	{
		this.log('__onPullEventUsersJoined', params);
		var users = params.users;

		this.addJoinedUsers(users);
	};

	BX.Call.VoximplantCall.prototype.__onPullEventUsersInvited = function(params)
	{
		this.log('__onPullEventUsersInvited', params);
		var users = params.users;

		if (this.type === BX.Call.Type.Instant)
		{
			this.addInvitedUsers(users);
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventUserInviteTimeout = function(params)
	{
		this.log('__onPullEventUserInviteTimeout', params);
		var failedUserId = params.failedUserId;

		if(this.peers[failedUserId])
		{
			this.peers[failedUserId].onInviteTimeout(false);
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventPing = function(params)
	{
		if(params.callInstanceId == this.instanceId)
		{
			// ignore self ping
			return;
		}

		var senderId = Number(params.senderId);

		if (senderId == this.userId)
		{
			if (!this.joinedElsewhere)
			{
				this.runCallback(BX.Call.Event.onJoin, {
					local: false
				});
				this.joinedElsewhere = true;
			}
			clearTimeout(this.lastSelfPingReceivedTimeout);
			this.lastSelfPingReceivedTimeout = setTimeout(this.__onNoSelfPingsReceived.bind(this), pingPeriod * 2.1);
		}
		clearTimeout(this.lastPingReceivedTimeout);
		this.lastPingReceivedTimeout = setTimeout(this.__onNoPingsReceived.bind(this), pingPeriod * 2.1);
		if(this.peers[senderId])
		{
			this.peers[senderId].setReady(true);
		}
	};

	BX.Call.VoximplantCall.prototype.__onNoPingsReceived = function()
	{
		if(!this.ready)
		{
			this.destroy();
		}
	};

	BX.Call.VoximplantCall.prototype.__onNoSelfPingsReceived = function()
	{
		this.runCallback(BX.Call.Event.onLeave, {
			local: false
		});
		this.joinedElsewhere = false;
	};

	BX.Call.VoximplantCall.prototype.__onPullEventFinish = function(params)
	{
		this.destroy();
	};

	BX.Call.VoximplantCall.prototype.__onPullEventRepeatAnswer = function()
	{
		if (this.ready)
		{
			this.signaling.sendAnswer({userId: this.userId}, true);
		}
	}

	BX.Call.VoximplantCall.prototype.__onLocalDevicesUpdated = function(e)
	{
		this.log("__onLocalDevicesUpdated", e);
	};

	BX.Call.VoximplantCall.prototype.__onLocalMediaRendererAdded = function(e)
	{
		var renderer = e.renderer;
		var trackLabel = renderer.stream.getVideoTracks().length > 0 ? renderer.stream.getVideoTracks()[0].label : "";
		this.log("__onLocalMediaRendererAdded", renderer.kind, trackLabel);

		if(renderer.kind === "video")
		{
			if (trackLabel.match(/^screen|window|tab|web-contents-media-stream/i))
			{
				var tag = "screen";
			}
			else
			{
				tag = "main";
			}

			this.screenShared = tag === "screen";

			this.runCallback(BX.Call.Event.onLocalMediaReceived, {
				tag: tag,
				stream: renderer.stream,
			});
		}
		else if (renderer.kind === "sharing")
		{
			this.runCallback(BX.Call.Event.onLocalMediaReceived, {
				tag: "screen",
				stream: renderer.stream,
			});
			this.screenShared = true;
		}
	};

	BX.Call.VoximplantCall.prototype.__onBeforeLocalMediaRendererRemoved = function(e)
	{
		var renderer = e.renderer;
		this.log("__onBeforeLocalMediaRendererRemoved", renderer.kind);

		if(renderer.kind === "sharing" && !this.videoEnabled)
		{
			this.runCallback(BX.Call.Event.onLocalMediaReceived, {
				tag: "main",
				stream: new MediaStream(),
			});
			this.screenShared = false;
		}
	};

	BX.Call.VoximplantCall.prototype.__onMicAccessResult = function(e)
	{
		if (e.result)
		{
			if (e.stream.getAudioTracks().length > 0)
			{
				if (this.localVAD)
				{
					this.localVAD.destroy();
				}
				this.localVAD = new BX.SimpleVAD({
					mediaStream: e.stream,
					onVoiceStarted: function()
					{
						this.runCallback(BX.Call.Event.onUserVoiceStarted, {
							userId: this.userId,
							local: true
						});
					}.bind(this),
					onVoiceStopped: function()
					{
						this.runCallback(BX.Call.Event.onUserVoiceStopped, {
							userId: this.userId,
							local: true
						});
					}.bind(this),
				});

				clearInterval(this.microphoneLevelInterval);
				this.microphoneLevelInterval = setInterval(function()
				{
					this.microphoneLevel = this.localVAD.currentVolume;
				}.bind(this), 200)
			}
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallReconnecting = function(e)
	{
		this.reconnectionEventCount++;
	};

	BX.Call.VoximplantCall.prototype.__onCallReconnected = function(e)
	{
		this.reconnectionEventCount--;
	};

	BX.Call.VoximplantCall.prototype.__onClientReconnecting = function(e)
	{
		this.reconnectionEventCount++;
	};

	BX.Call.VoximplantCall.prototype.__onClientReconnected = function(e)
	{
		this.reconnectionEventCount--;
	};

	BX.Call.VoximplantCall.prototype.__onCallDisconnected = function(e)
	{
		this.log("__onCallDisconnected", (e && e.headers ? {headers: e.headers} : null));
		this.sendTelemetryEvent("disconnect");
		this.localUserState = BX.Call.UserState.Idle;

		this.ready = false;
		this.muted = false;
		this.joinedAsViewer = false;
		this.reinitPeers();

		this._hideLocalVideo();
		this.removeCallEvents();
		this.voximplantCall = null;

		var client = VoxImplant.getInstance();
		client.enableSilentLogging(false);
		client.setLoggerCallback(null);

		this.state = BX.Call.State.Proceeding;
		this.runCallback(BX.Call.Event.onLeave, {
			local: true
		});
	};

	BX.Call.VoximplantCall.prototype.__onWindowUnload = function()
	{
		if(this.ready && this.voximplantCall)
		{
			this.signaling.sendHangup({
				userId: this.users
			});
		}
	};

	BX.Call.VoximplantCall.prototype.onFatalError = function(error)
	{
		if(error && error.call)
		{
			delete error.call;
		}
		this.log("onFatalError", error);

		this.ready = false;
		this.muted = false;
		this.localUserState = BX.Call.UserState.Failed;
		this.reinitPeers();

		this._hideLocalVideo().then(function()
		{
			if(this.voximplantCall)
			{
				this.removeCallEvents();
				try
				{
					this.voximplantCall.hangup({
						'X-Reason': 'Fatal error',
						'X-Error': typeof(error) === 'string' ? error : error.code || error.name
					})
				}
				catch (e)
				{
					this.log("Voximplant hangup error: ", e);
					console.error("Voximplant hangup error: ", e);
				}
				this.voximplantCall = null;
			}

			if (typeof(VoxImplant) !== 'undefined')
			{
				var client = VoxImplant.getInstance();

				client.enableSilentLogging(false);
				client.setLoggerCallback(null);
			}

			if(typeof(error) === "string")
			{
				this.runCallback(BX.Call.Event.onCallFailure, {
					name: error
				});
			}
			else if(error.name)
			{
				this.runCallback(BX.Call.Event.onCallFailure, error);
			}
		}.bind(this))
	};

	BX.Call.VoximplantCall.prototype.__onCallEndpointAdded = function(e)
	{
		var endpoint = e.endpoint;
		var userName = endpoint.userName;
		this.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);
		console.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);

		if(BX.type.isNotEmptyString(userName) && userName.substr(0, 4) == 'user')
		{
			// user connected to conference
			var userId = parseInt(userName.substr(4));
			if(this.peers[userId])
			{
				this.peers[userId].setEndpoint(endpoint);
			}
			this.wasConnected = true;
		}
		else
		{
			endpoint.addEventListener(VoxImplant.EndpointEvents.InfoUpdated, function(e)
			{
				var endpoint = e.endpoint;
				var userName = endpoint.userName;
				this.log("VoxImplant.EndpointEvents.InfoUpdated (" + userName + ")", e.endpoint);

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
			console.warn('Unknown endpoint ' + userName);
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallStatsReceived = function(e)
	{
		if(this.logger)
		{
			this.logger.sendStat(transformVoxStats(e.stats, this.voximplantCall));
		}
	}

	BX.Call.VoximplantCall.prototype.__onJoinRoomOffer = function(e)
	{
		console.warn("__onJoinRoomOffer", e)
		this.updateRoom({
			id: e.roomId,
			users: e.users,
			speaker: e.speaker,
		});
		this.runCallback(BX.Call.Event.onJoinRoomOffer, {
			roomId: e.roomId,
			users: e.users,
			initiator: e.initiator,
			speaker: e.speaker,
		})
	}

	BX.Call.VoximplantCall.prototype.__onRoomUpdated = function(e)
	{
		const speakerChanged = (
			e.roomId === this._currentRoomId
			&& this.rooms[e.roomId]
			&& this.rooms[e.roomId].speaker != e.speaker
		);
		const previousSpeaker = speakerChanged && this.rooms[e.roomId].speaker;

		console.log("__onRoomUpdated; speakerChanged: ", speakerChanged)
		this.updateRoom({
			id: e.roomId,
			users: e.users,
			speaker: e.speaker,
		});


		if (e.roomId === this._currentRoomId && e.users.indexOf(this.userId) === -1)
		{
			this._currentRoomId = null;
			this.runCallback(BX.Call.Event.onLeaveRoom, {
				roomId: e.roomId
			})
		}
		else if (e.roomId !== this._currentRoomId && e.users.indexOf(this.userId) !== -1)
		{
			this._currentRoomId = e.roomId;
			this.runCallback(BX.Call.Event.onJoinRoom, {
				roomId: e.roomId,
				speaker: this.currentRoom().speaker,
				users: this.currentRoom().users,
			})
		}
		else if (speakerChanged)
		{
			this.runCallback(BX.Call.Event.onTransferRoomSpeaker, {
				roomId: e.roomId,
				speaker: e.speaker,
				previousSpeaker: previousSpeaker,
				initiator: e.initiator,
			})
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallMessageReceived = function(e)
	{
		var message;
		var peer;

		try
		{
			message = JSON.parse(e.text);
		}
		catch(err)
		{
			this.log("Could not parse scenario message.", err);
			return;
		}

		var eventName = message.eventName;
		if(eventName === clientEvents.voiceStarted)
		{
			// todo: remove after switching to SDK VAD events
			this.runCallback(BX.Call.Event.onUserVoiceStarted, {
				userId: message.senderId
			});
		}
		else if(eventName === clientEvents.voiceStopped)
		{
			// todo: remove after switching to SDK VAD events
			this.runCallback(BX.Call.Event.onUserVoiceStopped, {
				userId: message.senderId
			});
		}
		else if (eventName === clientEvents.microphoneState)
		{
			this.runCallback(BX.Call.Event.onUserMicrophoneState, {
				userId: message.senderId,
				microphoneState: message.microphoneState === "Y"
			});
		}
		else if (eventName === clientEvents.cameraState)
		{
			this.runCallback(BX.Call.Event.onUserCameraState, {
				userId: message.senderId,
				cameraState: message.cameraState === "Y"
			});
		}
		else if (eventName === clientEvents.videoPaused)
		{
			this.runCallback(BX.Call.Event.onUserVideoPaused, {
				userId: message.senderId,
				videoPaused: message.videoPaused === "Y"
			});
		}
		else if (eventName === clientEvents.screenState)
		{
			this.runCallback(BX.Call.Event.onUserScreenState, {
				userId: message.senderId,
				screenState: message.screenState === "Y"
			});
		}
		else if (eventName === clientEvents.recordState)
		{
			this.runCallback(BX.Call.Event.onUserRecordState, {
				userId: message.senderId,
				recordState: message.recordState
			});
		}
		else if (eventName === clientEvents.floorRequest)
		{
			this.runCallback(BX.Call.Event.onUserFloorRequest, {
				userId: message.senderId,
				requestActive: message.requestActive === "Y"
			})
		}
		else if (eventName === clientEvents.emotion)
		{
			this.runCallback(BX.Call.Event.onUserEmotion, {
				userId: message.senderId,
				toUserId: message.toUserId,
				emotion: message.emotion
			})
		}
		else if (eventName === clientEvents.customMessage)
		{
			this.runCallback(BX.Call.Event.onCustomMessage, {
				message: message.message
			})
		}
		else if (eventName === "scenarioLogUrl")
		{
			console.warn("scenario log url: " + message.logUrl)
		}
		else if (eventName === scenarioEvents.joinRoomOffer)
		{
			console.log(message)
			this.__onJoinRoomOffer(message);
		}
		else if (eventName === scenarioEvents.roomUpdated)
		{
			// console.log(message)
			this.__onRoomUpdated(message);
		}
		else if (eventName === scenarioEvents.listRoomsResponse)
		{
			if (this.__resolveListRooms)
			{
				this.__resolveListRooms(message.rooms)
				delete this.__resolveListRooms;
			}
		}
		else if (eventName === scenarioEvents.viewerJoined)
		{
			console.log("viewer " + message.senderId + " joined");
			peer = this.peers[message.senderId];
			if (peer)
			{
				peer.setDirection(BX.Call.EndpointDirection.RecvOnly);
				peer.setReady(true);
			}
		}
		else if (eventName === scenarioEvents.viewerLeft)
		{
			console.log("viewer " + message.senderId + " left");
			peer = this.peers[message.senderId];
			if (peer)
			{
				peer.setReady(false);
			}
		}
		else
		{
			this.log("Unknown scenario event " + eventName);
		}
	};

	BX.Call.VoximplantCall.prototype.sendTelemetryEvent = function(eventName)
	{
		BX.Call.Util.sendTelemetryEvent({
			call_id: this.id,
			user_id: this.userId,
			kind: "voximplant",
			event: eventName,
		})
	};

	BX.Call.VoximplantCall.prototype.destroy = function()
	{
		this.ready = false;
		this.joinedAsViewer = false;
		this._hideLocalVideo();
		if (this.localVAD)
		{
			this.localVAD.destroy();
			this.localVAD = null;
		}
		clearInterval(this.microphoneLevelInterval);
		if(this.voximplantCall)
		{
			this.removeCallEvents();
			if(this.voximplantCall.state() != "ENDED")
			{
				this.voximplantCall.hangup();
			}
			this.voximplantCall = null;
		}

		for(var userId in this.peers)
		{
			if(this.peers.hasOwnProperty(userId) && this.peers[userId])
			{
				this.peers[userId].destroy();
			}
		}

		this.removeClientEvents();

		clearTimeout(this.lastPingReceivedTimeout);
		clearTimeout(this.lastSelfPingReceivedTimeout);
		clearInterval(this.pingUsersInterval);
		clearInterval(this.pingBackendInterval);

		window.removeEventListener("unload", this.__onWindowUnloadHandler);
		this.superclass.destroy.apply(this, arguments);
	};

	BX.Call.VoximplantCall.Signaling = function(params)
	{
		this.call = params.call;
	};

	BX.Call.VoximplantCall.Signaling.prototype.inviteUsers = function(data)
	{
		return this.__runRestAction(ajaxActions.invite, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendAnswer = function(data, repeated)
	{
		if (repeated && BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.answer, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.answer, data);
		}
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendCancel = function(data)
	{
		return this.__runRestAction(ajaxActions.cancel, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendHangup = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.hangup, data);
			data.retransmit = false;
			this.__runRestAction(ajaxActions.hangup, data);
		}
		else
		{
			data.retransmit = true;
			this.__runRestAction(ajaxActions.hangup, data);
		}
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendVoiceStarted = function(data)
	{
		return this.__sendMessage(clientEvents.voiceStarted, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendVoiceStopped = function(data)
	{
		return this.__sendMessage(clientEvents.voiceStopped, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendMicrophoneState = function(microphoneState)
	{
		return this.__sendMessage(clientEvents.microphoneState, {
			microphoneState: microphoneState ? "Y" : "N"
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendCameraState = function(cameraState)
	{
		return this.__sendMessage(clientEvents.cameraState, {
			cameraState: cameraState ? "Y" : "N"
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendScreenState = function(screenState)
	{
		return this.__sendMessage(clientEvents.screenState, {
			screenState: screenState ? "Y" : "N"
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendRecordState = function(recordState)
	{
		return this.__sendMessage(clientEvents.recordState, recordState);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendFloorRequest = function(requestActive)
	{
		return this.__sendMessage(clientEvents.floorRequest, {
			requestActive: requestActive ? "Y" : "N"
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendEmotion = function(toUserId, emotion)
	{
		return this.__sendMessage(clientEvents.emotion, {
			toUserId: toUserId,
			emotion: emotion
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendCustomMessage = function(message, repeatOnConnect)
	{
		return this.__sendMessage(clientEvents.customMessage, {
			message: message,
			repeatOnConnect: !!repeatOnConnect
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendShowUsers = function(users)
	{
		return this.__sendMessage(clientEvents.showUsers, {
			users: users
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendShowAll = function()
	{
		return this.__sendMessage(clientEvents.showAll, {});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendHideAll = function()
	{
		return this.__sendMessage(clientEvents.hideAll, {});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendPingToUsers = function(data)
	{
		if (BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.ping, data, 0);
		}
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendPingToBackend = function()
	{
		this.__runRestAction(ajaxActions.ping, {retransmit: false});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendUserInviteTimeout = function(data)
	{
		if (BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.userInviteTimeout, data, 0);
		}
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendJoinRoom = function(roomId)
	{
		return this.__sendMessage(clientEvents.joinRoom, {
			roomId: roomId
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendLeaveRoom = function(roomId)
	{
		return this.__sendMessage(clientEvents.leaveRoom, {
			roomId: roomId
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendListRooms = function()
	{
		return this.__sendMessage(clientEvents.listRooms);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendRequestRoomSpeaker = function(roomId)
	{
		return this.__sendMessage(clientEvents.requestRoomSpeaker, {
			roomId: roomId
		});
	};

	BX.Call.VoximplantCall.Signaling.prototype.__sendPullEvent = function(eventName, data, expiry)
	{
		expiry = expiry || 5;
		if(!data.userId)
		{
			throw new Error('userId is not found in data');
		}

		if(!BX.type.isArray(data.userId))
		{
			data.userId = [data.userId];
		}
		if(data.userId.length === 0)
		{
			// nobody to send, exit
			return;
		}

		data.callInstanceId = this.call.instanceId;
		data.senderId = this.call.userId;
		data.callId = this.call.id;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();

		this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
		BX.CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
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

	BX.Call.VoximplantCall.Signaling.prototype.__runRestAction = function(signalName, data)
	{
		if(!BX.type.isPlainObject(data))
		{
			data = {};
		}

		data.callId = this.call.id;
		data.callInstanceId = this.call.instanceId;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();
		return BX.CallEngine.getRestClient().callMethod(signalName, data);
	};

	BX.Call.VoximplantCall.Peer = function(params)
	{
		this.userId = params.userId;
		this.call = params.call;

		this.ready = !!params.ready;
		this.calling = false;
		this.declined = false;
		this.busy = false;
		this.inviteTimeout = false;
		this.endpoint = null;
		this.direction = params.direction || BX.Call.EndpointDirection.SendRecv;

		this.stream = null;
		this.mediaRenderers = [];

		this.isIncomingVideoAllowed = params.isIncomingVideoAllowed !== false;

		this.callingTimeout = 0;
		this.connectionRestoreTimeout = 0;

		this.callbacks = {
			onStateChanged: BX.type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onInviteTimeout: BX.type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
			onMediaReceived: BX.type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
			onMediaRemoved: BX.type.isFunction(params.onMediaRemoved) ? params.onMediaRemoved : BX.DoNothing,
			onVoiceStarted: BX.type.isFunction(params.onVoiceStarted) ? params.onVoiceStarted : BX.DoNothing,
			onVoiceEnded: BX.type.isFunction(params.onVoiceEnded) ? params.onVoiceEnded : BX.DoNothing,
		};

		// event handlers
		this.__onEndpointRemoteMediaAddedHandler = this.__onEndpointRemoteMediaAdded.bind(this);
		this.__onEndpointRemoteMediaRemovedHandler = this.__onEndpointRemoteMediaRemoved.bind(this);
		this.__onEndpointVoiceStartHandler = this.__onEndpointVoiceStart.bind(this);
		this.__onEndpointVoiceEndHandler = this.__onEndpointVoiceEnd.bind(this);
		this.__onEndpointRemovedHandler = this.__onEndpointRemoved.bind(this);

		this.calculatedState = this.calculateState();
	};

	BX.Call.VoximplantCall.Peer.prototype = {

		setReady: function(ready)
		{
			ready = !!ready;
			if (this.ready == ready)
			{
				return;
			}
			this.ready = ready;
			this.readyStack = (new Error()).stack;
			if(this.calling)
			{
				clearTimeout(this.callingTimeout);
				this.calling = false;
				this.inviteTimeout = false;
			}
			if(this.ready)
			{
				this.declined = false;
				this.busy = false;
			}
			else
			{
				clearTimeout(this.connectionRestoreTimeout);
			}

			this.updateCalculatedState();
		},

		setDirection: function(direction)
		{
			if (this.direction == direction)
			{
				return;
			}
			this.direction = direction;
		},

		setDeclined: function(declined)
		{
			this.declined = declined;
			if(this.calling)
			{
				clearTimeout(this.callingTimeout);
				this.calling = false;
			}
			if(this.declined)
			{
				this.ready = false;
				this.busy = false;
			}
			clearTimeout(this.connectionRestoreTimeout);
			this.updateCalculatedState();
		},

		setBusy: function(busy)
		{
			this.busy = busy;
			if(this.calling)
			{
				clearTimeout(this.callingTimeout);
				this.calling = false;
			}
			if(this.busy)
			{
				this.ready = false;
				this.declined = false;
			}
			clearTimeout(this.connectionRestoreTimeout);
			this.updateCalculatedState();
		},

		setEndpoint: function(endpoint)
		{
			this.log("Adding endpoint with " + endpoint.mediaRenderers.length + " media renderers");

			this.setReady(true);
			this.inviteTimeout = false;
			this.declined = false;
			clearTimeout(this.connectionRestoreTimeout);

			if(this.endpoint)
			{
				this.removeEndpointEventHandlers();
				this.endpoint = null;
			}

			this.endpoint = endpoint;

			for(var i = 0; i < this.endpoint.mediaRenderers.length; i++)
			{
				this.addMediaRenderer(this.endpoint.mediaRenderers[i]);
				if(this.endpoint.mediaRenderers[i].element)
				{
					//BX.remove(this.endpoint.mediaRenderers[i].element);
				}
			}

			this.bindEndpointEventHandlers();
		},

		allowIncomingVideo: function(isIncomingVideoAllowed)
		{
			if(this.isIncomingVideoAllowed == isIncomingVideoAllowed)
			{
				return;
			}

			this.isIncomingVideoAllowed = !!isIncomingVideoAllowed;
		},

		addMediaRenderer: function(mediaRenderer)
		{
			this.log('Adding media renderer for user' + this.userId, mediaRenderer);

			this.mediaRenderers.push(mediaRenderer);
			this.callbacks.onMediaReceived({
				userId: this.userId,
				kind: mediaRenderer.kind,
				mediaRenderer: mediaRenderer
			});
			this.updateCalculatedState();
		},

		removeMediaRenderer: function(mediaRenderer)
		{
			console.log('Removing media renderer for user' + this.userId, mediaRenderer);
			this.log('Removing media renderer for user' + this.userId, mediaRenderer);

			var i = this.mediaRenderers.indexOf(mediaRenderer);
			if (i >= 0)
			{
				this.mediaRenderers.splice(i, 1);
			}
			this.callbacks.onMediaRemoved({
				userId: this.userId,
				kind: mediaRenderer.kind,
				mediaRenderer: mediaRenderer
			});
			this.updateCalculatedState();
		},

		bindEndpointEventHandlers: function()
		{
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.__onEndpointRemoteMediaAddedHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.__onEndpointRemoteMediaRemovedHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceStart, this.__onEndpointVoiceStartHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceEnd, this.__onEndpointVoiceEndHandler);
			this.endpoint.addEventListener(VoxImplant.EndpointEvents.Removed, this.__onEndpointRemovedHandler);
		},

		removeEndpointEventHandlers: function()
		{
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.__onEndpointRemoteMediaAddedHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.__onEndpointRemoteMediaRemovedHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceStart, this.__onEndpointVoiceStartHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceEnd, this.__onEndpointVoiceEndHandler);
			this.endpoint.removeEventListener(VoxImplant.EndpointEvents.Removed, this.__onEndpointRemovedHandler);
		},

		calculateState: function()
		{
			if(this.endpoint)
				return BX.Call.UserState.Connected;

			if(this.calling)
				return BX.Call.UserState.Calling;

			if(this.inviteTimeout)
				return BX.Call.UserState.Unavailable;

			if(this.declined)
				return BX.Call.UserState.Declined;

			if(this.busy)
				return BX.Call.UserState.Busy;

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
					previousState: this.calculatedState,
					direction: this.direction,
				});
				this.calculatedState = calculatedState;
			}
		},

		isParticipating: function()
		{
			return ((this.calling || this.ready || this.endpoint) && !this.declined);
		},

		waitForConnectionRestore: function()
		{
			clearTimeout(this.connectionRestoreTimeout);
			this.connectionRestoreTimeout = setTimeout(
				this.onConnectionRestoreTimeout.bind(this),
				connectionRestoreTime
			);
		},

		onInvited: function()
		{
			this.ready = false;
			this.inviteTimeout = false;
			this.declined = false;
			this.calling = true;

			clearTimeout(this.connectionRestoreTimeout);
			if(this.callingTimeout)
			{
				clearTimeout(this.callingTimeout);
			}
			this.callingTimeout = setTimeout(function()
			{
				this.onInviteTimeout(true);
			}.bind(this), 30000);
			this.updateCalculatedState();
		},

		onInviteTimeout: function(internal)
		{
			clearTimeout(this.callingTimeout);
			if(!this.calling)
			{
				return;
			}
			this.calling = false;
			this.inviteTimeout = true;
			if(internal)
			{
				this.callbacks.onInviteTimeout({
					userId: this.userId
				});
			}
			this.updateCalculatedState();
		},

		onConnectionRestoreTimeout: function()
		{
			if(this.endpoint || !this.ready)
			{
				return;
			}

			this.log("Done waiting for connection restoration");
			this.setReady(false);
		},

		__onEndpointRemoteMediaAdded: function(e)
		{
			this.log("VoxImplant.EndpointEvents.RemoteMediaAdded", e.mediaRenderer);

			// voximplant audio auto-play bug workaround:
			if(e.mediaRenderer.element)
			{
				e.mediaRenderer.element.volume = 0;
				e.mediaRenderer.element.srcObject = null;
			}
			this.addMediaRenderer(e.mediaRenderer);
		},

		__onEndpointRemoteMediaRemoved: function(e)
		{
			console.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e.mediaRenderer)
			//this.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e);
			this.removeMediaRenderer(e.mediaRenderer);
		},

		__onEndpointVoiceStart: function(e)
		{
			this.callbacks.onVoiceStarted();
		},

		__onEndpointVoiceEnd: function(e)
		{
			this.callbacks.onVoiceEnded();
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

			if(this.ready)
			{
				this.waitForConnectionRestore();
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
				this.stream.getTracks().forEach(function(track)
				{
					track.stop();
				});
				this.stream = null;
			}
			if(this.endpoint)
			{
				this.removeEndpointEventHandlers();
				this.endpoint = null;
			}

			this.callbacks.onStateChanged = BX.DoNothing;
			this.callbacks.onInviteTimeout = BX.DoNothing;
			this.callbacks.onMediaReceived = BX.DoNothing;
			this.callbacks.onMediaRemoved = BX.DoNothing;

			clearTimeout(this.callingTimeout);
			clearTimeout(this.connectionRestoreTimeout);
			this.callingTimeout = null;
			this.connectionRestoreTimeout = null;
		}
	};

	var transformVoxStats = function(s, voximplantCall)
	{
		let result = {
			connection: s.connection,
			outboundAudio: [],
			outboundVideo: [],
			inboundAudio: [],
			inboundVideo: [],
		}

		let endpoints = {};
		if (voximplantCall.getEndpoints)
		{
			voximplantCall.getEndpoints().forEach(endpoint => endpoints[endpoint.id] = endpoint)
		}

		if (!result.connection.timestamp)
		{
			result.connection.timestamp = Date.now();
		}
		for (let trackId in s.outbound)
		{
			if (!s.outbound.hasOwnProperty(trackId))
			{
				continue;
			}
			var statGroup = s.outbound[trackId];
			for (var i = 0; i < statGroup.length; i ++)
			{
				let stat = statGroup[i];
				stat.trackId = trackId;
				if ('audioLevel' in stat)
				{
					result.outboundAudio.push(stat)
				}
				else
				{
					result.outboundVideo.push(stat)
				}
			}
		}
		for (let trackId in s.inbound)
		{
			if (!s.inbound.hasOwnProperty(trackId))
			{
				continue;
			}
			let stat = s.inbound[trackId];
			if (!('endpoint' in stat))
			{
				continue;
			}
			stat.trackId = trackId;
			if ('audioLevel' in stat)
			{
				result.inboundAudio.push(stat)
			}
			else
			{
				if (endpoints[stat.endpoint])
				{
					let videoRenderer = endpoints[stat.endpoint].mediaRenderers.find(r => r.id == stat.trackId)
					if (videoRenderer && videoRenderer.element)
					{
						stat.actualHeight = videoRenderer.element.videoHeight;
						stat.actualWidth = videoRenderer.element.videoWidth;
					}
				}

				result.inboundVideo.push(stat)
			}
		}
		return result;
	}

	BX.Call.VoximplantCall.Event = VoximplantCallEvent;
})();