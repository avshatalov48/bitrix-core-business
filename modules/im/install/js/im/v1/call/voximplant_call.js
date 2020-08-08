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
		hangup: 'Call::hangup',
		userInviteTimeout: 'Call::userInviteTimeout'
	};

	var clientEvents = {
		voiceStarted: 'Call::voiceStarted',
		voiceStopped: 'Call::voiceStopped',
		microphoneState: 'Call::microphoneState'
	};

	var VoximplantCallEvent = {
		onCallConference: 'VoximplantCall::onCallConference'
	};

	var pingPeriod = 5000;
	var backendPingPeriod = 25000;

	var reinvitePeriod = 5500;

	var connectionRestoreTime = 15000;

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

		if(!window.VoxImplant)
		{
			throw new Error("Voximplant SDK is not found");
		}

		this.voximplantCall = null;

		this.signaling = new BX.Call.VoximplantCall.Signaling({
			call: this
		});

		this.peers = {};
		this.joinedElsewhere = false;

		this.screenShared = false;
		this.localVideoShown = false;

		this.clientEventsBound = false;

		this.deviceList = [];

		// event handlers
		this.__onLocalDevicesUpdatedHandler = this.__onLocalDevicesUpdated.bind(this);
		this.__onLocalMediaRendererAddedHandler = this.__onLocalMediaRendererAdded.bind(this);
		this.__onBeforeLocalMediaRendererRemovedHandler = this.__onBeforeLocalMediaRendererRemoved.bind(this);


		this.__onCallDisconnectedHandler = this.__onCallDisconnected.bind(this);
		this.__onCallMessageReceivedHandler = this.__onCallMessageReceived.bind(this);
		this.__onCallEndpointAddedHandler = this.__onCallEndpointAdded.bind(this);

		this.initPeers();

		this.pingUsersInterval = setInterval(this.pingUsers.bind(this), pingPeriod);
		this.pingBackendInterval = setInterval(this.pingBackend.bind(this), backendPingPeriod);

		this.lastPingReceivedTimeout = null;
		this.lastSelfPingReceivedTimeout = null;

		this.reinviteTimeout = null;
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
			onStateChanged: this.__onPeerStateChanged.bind(this),
			onInviteTimeout: this.__onPeerInviteTimeout.bind(this)
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
		return new Promise(function(resolve, reject)
		{
			BX.Voximplant.getClient().then(function(client)
			{
				client.enableSilentLogging();
				client.setLoggerCallback(function(e)
				{
					this.log(e.label + ": " + e.message);
				}.bind(this));

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
			streamManager.on(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.__onLocalDevicesUpdatedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.__onLocalMediaRendererAddedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererUpdated, this.__onLocalMediaRendererAddedHandler);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.__onBeforeLocalMediaRendererRemovedHandler);
			this.clientEventsBound = true;
		}
	};

	BX.Call.VoximplantCall.prototype.removeClientEvents = function()
	{
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
		}
	};

	BX.Call.VoximplantCall.prototype.setCameraId = function(cameraId)
	{
		this.cameraId = cameraId;
		var cameraParams = {
			cameraId: this.cameraId,
		};

		if(this.videoResolution)
		{
			cameraParams.frameHeight = this.videoResolution.height;
			cameraParams.frameWidth =  this.videoResolution.width;
		}
		VoxImplant.Hardware.CameraManager.get().getInputDevices().then(function()
		{
			if(this.voximplantCall)
			{
				VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, cameraParams);
			}

			VoxImplant.Hardware.CameraManager.get().setDefaultVideoSettings(cameraParams);
		}.bind(this));
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

	BX.Call.VoximplantCall.prototype.useHdVideo = function(flag)
	{
		this.videoHd = (flag === true);

		if(this.voximplantCall)
		{
			var cameraParams = {
				cameraId: this.cameraId,
				videoQuality: this.videoHd ? VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_HD : VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_nHD
			};
			VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, cameraParams);
		}
	};

	BX.Call.VoximplantCall.prototype.setVideoQuality = function(videoQuality)
	{
		if(this.videoQuality == videoQuality)
		{
			return;
		}
		this.videoQuality = videoQuality;
		this._applyCurrentVideoQuality();
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

	BX.Call.VoximplantCall.prototype._applyCurrentVideoQuality = function()
	{
		if(!this.voximplantCall)
		{
			return;
		}

		if('RTCRtpSender' in window && 'setParameters' in window.RTCRtpSender.prototype)
		{
			this._setMaxBitrate(BX.Call.Util.getMaxBitrate(this.videoQuality))
		}
		else
		{
			this._useVideoResolution(BX.Call.Util.getMaxResolution(this.videoQuality, this.useHdVideo()));
		}
	};

	BX.Call.VoximplantCall.prototype._useVideoResolution = function(resolution)
	{
		this.videoResolution = resolution;

		if(this.voximplantCall)
		{
			var cameraParams = {
				cameraId: this.cameraId,
				frameHeight: this.videoResolution.height,
				frameWidth: this.videoResolution.width,
			};
			VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, cameraParams);
		}
	};

	BX.Call.VoximplantCall.prototype._showLocalVideo = function()
	{
		return new Promise(function(resolve, reject)
		{
			VoxImplant.Hardware.StreamManager.get().showLocalVideo().then(
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
		var replaceTrack = this.videoEnabled;

		this.voximplantCall.shareScreen(showLocalView, replaceTrack).then(function()
		{
			this.log("Screen shared");
			this.screenShared = true;
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

			return self.signaling.inviteUsers({
				userIds: users,
				video: self.videoEnabled ? 'Y' : 'N'
			})
		}).then(function(response)
		{
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

					self.runCallback(BX.Call.Event.onUserInvited, {
						userId: userId
					});
				}
				self.peers[userId].onInvited();
				self.scheduleRepeatInvite();
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
			video: this.videoEnabled ? 'Y' : 'N'
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
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		this.videoEnabled = (config.useVideo == true);

		this.signaling.sendAnswer();
		this.attachToConference().then(function ()
		{
			this.log("Attached to conference");
			this.runCallback(BX.Call.Event.onJoin, {
				local: true
			});
		}.bind(this)).catch(this.onFatalError.bind(this));
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
		this.runCallback(BX.Call.Event.onLeave, {local: true});

		data.userId = this.users;
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
				console.error(e);
			}
		}

		this.screenShared = false;
		this._hideLocalVideo();
	};

	BX.Call.VoximplantCall.prototype.attachToConference = function()
	{
		var self = this;

		// workaround to set default video settings before starting call. ugly, but I do not see another way
		var cameraParams = {};
		if (this.cameraId)
		{
			cameraParams.cameraId = this.cameraId;
		}
		cameraParams.videoQuality = this.videoHd ? VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_HD : VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_nHD;
		VoxImplant.Hardware.CameraManager.get().setDefaultVideoSettings(cameraParams);
		if (this.microphoneId)
		{
			VoxImplant.Hardware.AudioDeviceManager.get().setDefaultAudioSettings({
				inputId: this.microphoneId
			});
		}

		return new Promise(function(resolve, reject)
		{
			if(self.voximplantCall && self.voximplantCall.state() === "CONNECTED")
			{
				return resolve();
			}

			self.getClient().then(function(voximplantClient)
			{
				if(self.videoEnabled)
				{
					self._showLocalVideo();
				}

				try
				{
					self.voximplantCall = voximplantClient.callConference({
						number: "bx_conf_" + self.id,
						video: {sendVideo: self.videoEnabled, receiveVideo: true},
						customData: {}
					});
				}
				catch (e)
				{
					console.error(e);
					return reject(e);
				}

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

					setTimeout(function()
					{
						self._applyCurrentVideoQuality();
					}, 1000);

					resolve();
				};

				var onCallFailed = function(e)
				{
					self.log("Could not attach to conference", e);
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

		this.voximplantCall.addEventListener(VoxImplant.CallEvents.EndpointAdded, this.__onCallEndpointAddedHandler);
	};

	BX.Call.VoximplantCall.prototype.removeCallEvents = function()
	{
		if(this.voximplantCall)
		{
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Disconnected, this.__onCallDisconnectedHandler);
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.MessageReceived, this.__onCallMessageReceivedHandler);
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.EndpointAdded, this.__onCallEndpointAddedHandler);
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
			if(!this.users.includes(userId))
			{
				this.users.push(userId);
			}
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

	BX.Call.VoximplantCall.prototype.__onPeerStateChanged = function(e)
	{
		this.runCallback(BX.Call.Event.onUserStateChanged, e);

		if(!this.ready)
		{
			return;
		}
		if(e.state == BX.Call.UserState.Failed || e.state == BX.Call.UserState.Unavailable || e.state == BX.Call.UserState.Declined || e.state == BX.Call.UserState.Idle)
		{
			if(!this.isAnyoneParticipating())
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
			'Call::usersInvited': this.__onPullEventUsersInvited.bind(this),
			'Call::userInviteTimeout': this.__onPullEventUserInviteTimeout.bind(this),
			'Call::ping': this.__onPullEventPing.bind(this),
			'Call::finish': this.__onPullEventFinish.bind(this)
		};

		if(handlers[command])
		{
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

		if(this.ready && !this.isAnyoneParticipating())
		{
			this.hangup();
		}
	};

	BX.Call.VoximplantCall.prototype.__onPullEventUsersInvited = function(params)
	{
		this.log('__onPullEventUsersInvited', params);
		var users = params.users;

		this.addInvitedUsers(users);
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

		if (params.senderId == this.userId)
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
		this.lastPingReceivedTimeout = setTimeout(this.__onNoPingsReceived.bind(this), pingPeriod * 2.1)
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
			if (trackLabel.match(/^screen|window|tab/i))
			{
				var tag = "screen";
			}
			else
			{
				tag = "main";
			}

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
		}
	};

	BX.Call.VoximplantCall.prototype.__onCallDisconnected = function(e)
	{
		this.log("__onCallDisconnected", e);

		this.ready = false;
		this.muted = false;
		this.reinitPeers();

		this._hideLocalVideo();
		this.removeCallEvents();
		this.voximplantCall = null;

		var client = VoxImplant.getInstance();
		client.enableSilentLogging(false);
		client.setLoggerCallback(null);

		this.runCallback(BX.Call.Event.onLeave, {
			local: true
		});
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
				{ /* nothing :) */ }
				this.voximplantCall = null;
			}

			var client = VoxImplant.getInstance();
			client.enableSilentLogging(false);
			client.setLoggerCallback(null);

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
		}

		this.setVideoQuality(BX.CallEngine.getAllowedVideoQuality(this.voximplantCall.getEndpoints().length));
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
			this.log("Could not parse scenario message.", err);
			return;
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
		else if (eventName === clientEvents.microphoneState)
		{
			this.runCallback(BX.Call.Event.onUserMicrophoneState, {
				userId: message.senderId,
				microphoneState: message.microphoneState === "Y"
			});
		}
		else
		{
			this.log("Unknown scenario event " + eventName);
		}
	};

	BX.Call.VoximplantCall.prototype.destroy = function()
	{
		this.ready = false;
		this._hideLocalVideo();
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
		this.runCallback(BX.Call.Event.onDestroy);
	};

	BX.Call.VoximplantCall.Signaling = function(params)
	{
		this.call = params.call;
	};

	BX.Call.VoximplantCall.Signaling.prototype.inviteUsers = function(data)
	{
		return this.__runRestAction(ajaxActions.invite, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendAnswer = function(data)
	{
		return this.__runRestAction(ajaxActions.answer, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendCancel = function(data)
	{
		return this.__runRestAction(ajaxActions.cancel, data);
	};

	BX.Call.VoximplantCall.Signaling.prototype.sendHangup = function(data)
	{
		if(BX.PULL.isPublishingEnabled())
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


	BX.Call.VoximplantCall.Signaling.prototype.sendPingToUsers = function(data)
	{
		if (BX.PULL.isPublishingEnabled())
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
		if (BX.PULL.isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.userInviteTimeout, data, 0);
		}
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
		data.callInstanceId = this.call.instanceId;
		data.senderId = this.call.userId;
		data.callId = this.call.id;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();

		this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
		BX.PULL.sendMessage(data.userId, 'im', eventName, data, expiry);
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

		this.stream = null;

		this.tracks = {
			audio: null,
			video: null,
			sharing: null
		};

		this.callingTimeout = 0;
		this.connectionRestoreTimeout = 0;

		this.callbacks = {
			onStateChanged: BX.type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onInviteTimeout: BX.type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
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
			ready = !!ready;
			if (this.ready == ready)
			{
				return;
			}
			this.ready = ready;
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
					previousState: this.calculatedState
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
			this.log("VoxImplant.EndpointEvents.RemoteMediaAdded", e);

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
			for(var kind in this.tracks)
			{
				if(this.tracks.hasOwnProperty(kind))
				{
					if(this.tracks[kind] && this.tracks[kind].stop)
					{
						this.tracks[kind].stop();
					}
					this.tracks[kind] = null;
				}
			}

			this.callbacks['onStateChanged'] = BX.DoNothing;
			this.callbacks['onStreamReceived'] = BX.DoNothing;
			this.callbacks['onStreamRemoved'] = BX.DoNothing;

			clearTimeout(this.callingTimeout);
			clearTimeout(this.connectionRestoreTimeout);
			this.callingTimeout = null;
			this.connectionRestoreTimeout = null;
		}
	};

	BX.Call.VoximplantCall.Event = VoximplantCallEvent;
})();