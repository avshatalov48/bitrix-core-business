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
	 * - setMuted
	 * - setVideoEnabled
	 * - setCameraId
	 * - setMicrophoneId
	 *
	 * Events:
	 * - onCallStateChanged //not sure about this.
	 * - onUserStateChanged
	 * - onUserVoiceStarted
	 * - onUserVoiceStopped
	 * - onLocalMediaReceived
	 * - onLocalMediaStopped
	 * - onLocalMediaError
	 * - onDeviceListUpdated
	 * - onDestroy
	 */

	BX.namespace('BX.Call');

	var ajaxActions = {
		invite: 'im.call.invite',
		cancel: 'im.call.cancel',
		answer: 'im.call.answer',
		decline: 'im.call.decline',
		hangup: 'im.call.hangup',
		ping: 'im.call.ping',
		negotiationNeeded: 'im.call.negotiationNeeded',
		connectionOffer: 'im.call.connectionOffer',
		connectionAnswer: 'im.call.connectionAnswer',
		iceCandidate: 'im.call.iceCandidate'
	};

	var pullEvents = {
		ping: 'Call::ping',
		answer: 'Call::answer',
		negotiationNeeded: 'Call::negotiationNeeded',
		connectionOffer: 'Call::connectionOffer',
		connectionAnswer: 'Call::connectionAnswer',
		iceCandidate: 'Call::iceCandidate',
		voiceStarted: 'Call::voiceStarted',
		voiceStopped: 'Call::voiceStopped',
		recordState: 'Call::recordState',
		microphoneState: 'Call::microphoneState',
		cameraState: 'Call::cameraState',
		videoPaused: 'Call::videoPaused',
		customMessage: 'Call::customMessage',
		hangup: 'Call::hangup',
		userInviteTimeout: 'Call::userInviteTimeout'
	};

	var defaultConnectionOptions = {
		offerToReceiveVideo: true,
		offerToReceiveAudio: true
	};

	var signalingConnectionRefreshPeriod = 30000;
	var signalingWaitReplyPeriod = 10000;
	//var signalingWaitReplyPeriod = 5000;
	var pingPeriod = 5000;
	var backendPingPeriod = 25000;

	var reinvitePeriod = 5500;

	BX.Call.PlainCall = function(params)
	{
		this.superclass.constructor.apply(this, arguments);

		this.callFromMobile = params.callFromMobile;
		this.state = params.state || '';

		this.peers = this.initPeers(this.users);

		this.signaling = new BX.Call.PlainCall.Signaling({
			call: this
		});

		this.recordState = {
			state: 'stopped',
			userId: 0,
			date: {
				start: null,
				pause: []
			},
		}

		this.deviceList = [];

		this.turnServer = (BX.browser.IsFirefox() ? BX.message('turn_server_firefox') : BX.message('turn_server')) || 'turn.calls.bitrix24.com';
		this.turnServerLogin = BX.message('turn_server_login') || 'bitrix';
		this.turnServerPassword = BX.message('turn_server_password') || 'bitrix';

		this.pingUsersInterval = setInterval(this.pingUsers.bind(this), pingPeriod);
		this.pingBackendInterval = setInterval(this.pingBackend.bind(this), backendPingPeriod);

		this.reinviteTimeout = null;

		this._onUnloadHandler = this._onUnload.bind(this);

		this.enableMicAutoParameters = params.enableMicAutoParameters !== false;
		this.microphoneLevelInterval = null;

		window.addEventListener("unload", this._onUnloadHandler);
	};

	BX.extend(BX.Call.PlainCall, BX.Call.AbstractCall);

	BX.Call.PlainCall.prototype.initPeers = function(userIds)
	{
		var peers = {};
		for(var i = 0; i < userIds.length; i++)
		{
			var userId = Number(userIds[i]);
			if(userId == this.userId)
				continue;

			peers[userId] = this.createPeer(userId);
		}
		return peers;
	};

	BX.Call.PlainCall.prototype.createPeer = function(userId)
	{
		var self = this;
		return new BX.Call.PlainCall.Peer({
			call: this,
			userId: userId,
			ready: userId == this.initiatorId,
			signalingConnected: userId == this.initiatorId,
			isLegacyMobile: userId == this.initiatorId && this.callFromMobile,

			onMediaReceived: function(e)
			{
				console.log("onMediaReceived: ", e);
				self.runCallback(BX.Call.Event.onRemoteMediaReceived, e);
			},
			onMediaStopped: function(e)
			{
				self.runCallback(BX.Call.Event.onRemoteMediaStopped, e);
			},
			onStateChanged: this.__onPeerStateChanged.bind(this),
			onInviteTimeout: this.__onPeerInviteTimeout.bind(this),
			onRTCStatsReceived: this.__onPeerRTCStatsReceived.bind(this),
			onNetworkProblem: function(e)
			{
				self.runCallback(BX.Call.Event.onNetworkProblem, e)
			}
		});
	};

	/**
	 * Returns call participants and their states
	 * @return {object} userId => user state
	 */
	BX.Call.PlainCall.prototype.getUsers = function()
	{
		var result = {};
		for (var userId in this.peers)
		{
			result[userId] = this.peers[userId].calculatedState;
		}
		return result;
	};

	BX.Call.PlainCall.prototype.isReady = function()
	{
		return this.ready;
	};

	BX.Call.PlainCall.prototype.setVideoEnabled = function(videoEnabled)
	{
		videoEnabled = (videoEnabled === true);
		if(this.videoEnabled == videoEnabled)
		{
			return;
		}

		this.videoEnabled = videoEnabled;
		var hasVideoTracks = this.localStreams['main'] && this.localStreams['main'].getVideoTracks().length > 0;
		if(this.ready && hasVideoTracks !== this.videoEnabled)
		{
			this.replaceLocalMediaStream().then(function()
			{
				var hasVideoTracks = this.localStreams['main'] && this.localStreams['main'].getVideoTracks().length > 0;
				if (this.videoEnabled && !hasVideoTracks)
				{
					this.videoEnabled = false;
				}
				this.signaling.sendCameraState(this.users, this.videoEnabled);
			}.bind(this));
		}
	};

	BX.Call.PlainCall.prototype.setMuted = function(muted)
	{
		muted = !!muted;
		if(this.muted == muted)
		{
			return;
		}

		this.muted = muted;
		if(this.localStreams["main"])
		{
			var audioTracks = this.localStreams["main"].getAudioTracks();
			if(audioTracks[0])
			{
				audioTracks[0].enabled = !this.muted;
			}
		}

		this.signaling.sendMicrophoneState(this.users, !this.muted);
		this.sendTalkingState();
	};

	BX.Call.PlainCall.prototype.isMuted = function()
	{
		return this.muted;
	}

	BX.Call.PlainCall.prototype.setCameraId = function(cameraId)
	{
		if(this.cameraId == cameraId)
		{
			return;
		}

		this.cameraId = cameraId;
		if(this.ready && this.videoEnabled)
		{
			BX.debounce(this.replaceLocalMediaStream, 100, this)();
		}
	};

	BX.Call.PlainCall.prototype.setMicrophoneId = function(microphoneId)
	{
		if(this.microphoneId == microphoneId)
		{
			return;
		}

		this.microphoneId = microphoneId;
		if(this.ready)
		{
			BX.debounce(this.replaceLocalMediaStream, 100, this)();
		}
	};

	BX.Call.PlainCall.prototype.getCurrentMicrophoneId = function()
	{
		if(!this.localStreams['main'])
		{
			return this.microphoneId;
		}

		var audioTracks = this.localStreams['main'].getAudioTracks();
		if (audioTracks.length > 0)
		{
			var audioTrackSettings = audioTracks[0].getSettings();
			return audioTrackSettings.deviceId;
		}
		else
		{
			return this.microphoneId;
		}
	};

	BX.Call.PlainCall.prototype.useHdVideo = function(flag)
	{
		this.videoHd = (flag === true);
	};

	BX.Call.PlainCall.prototype.sendRecordState = function(recordState)
	{
		recordState.senderId = this.userId;

		if (!this.__changeRecordState(recordState))
		{
			return false;
		}

		var users = [this.userId].concat(this.users);

		this.signaling.sendRecordState(users, this.recordState);
	};

	BX.Call.PlainCall.prototype.stopSendingStream = function(tag)
	{
		//todo: implement
	};

	BX.Call.PlainCall.prototype.allowVideoFrom = function(userList)
	{
		//todo: implement
	};

	/**
	 * Invites users to participate in the call.
	 *
	 * @param {Object} config
	 * @param {int[]} [config.users] Array of ids of the users to be invited.
	 * @param {MediaStream} [config.localStream] Prefetched local media stream (if has one).
	**/
	BX.Call.PlainCall.prototype.inviteUsers = function(config)
	{
		var self = this;
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		var users = BX.type.isArray(config.users) ? config.users : Object.keys(this.peers);
		this.ready = true;

		if(config.localStream instanceof MediaStream && !this.localStreams["main"])
		{
			this.localStreams["main"] = config.localStream;
		}

		this.getLocalMediaStream("main", true).then(function()
		{
			return self.signaling.inviteUsers({
				userIds: users,
				video: self.videoEnabled ? 'Y' : 'N'
			});

		}).then(function(response)
		{
			self.state = BX.Call.State.Connected;

			self.runCallback(BX.Call.Event.onJoin, {
				local: true
			});

			for (var i = 0; i < users.length; i++)
			{
				var userId = Number(users[i]);
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
		}).catch(function(e)
		{
			console.error(e);
			self.runCallback(BX.Call.Event.onCallFailure, e);
		});
	};

	BX.Call.PlainCall.prototype.scheduleRepeatInvite = function()
	{
		clearTimeout(this.reinviteTimeout);
		this.reinviteTimeout = setTimeout(this.repeatInviteUsers.bind(this), reinvitePeriod)
	};

	BX.Call.PlainCall.prototype.repeatInviteUsers = function()
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

	BX.Call.PlainCall.prototype.getMediaConstraints = function(options)
	{
		var audio = {};
		var video = options.videoEnabled ? {} : false;

		hdVideo = !!options.hdVideo;

		var supportedConstraints = navigator.mediaDevices.getSupportedConstraints ? navigator.mediaDevices.getSupportedConstraints() : {};

		if(this.microphoneId)
		{
			audio.deviceId = {ideal: this.microphoneId};
		}

		if(!this.enableMicAutoParameters)
		{
			if(supportedConstraints.echoCancellation)
			{
				audio.echoCancellation = false;
			}
			if(supportedConstraints.noiseSuppression)
			{
				audio.noiseSuppression = false;
			}
			if(supportedConstraints.autoGainControl)
			{
				audio.autoGainControl = false;
			}
		}

		if(video)
		{
			//video.aspectRatio = 1.7777777778;
			if(this.cameraId)
			{
				video.deviceId = {exact: this.cameraId};
			}

			if (hdVideo)
			{
				video.width = {max: 1920, min: 1280};
				video.height = {max: 1080, min: 720};
			}
			else
			{
				video.width = {ideal: 640};
				video.height = {ideal: 360};
			}
		}

		return {audio: audio, video: video};
	};

	/**
	 * Recursively tries to get user media stream with array of constraints
	 *
	 * @param constraintsArray array of constraints objects
	 * @returns {Promise}
	 */
	BX.Call.PlainCall.prototype.getUserMedia = function(constraintsArray)
	{
		return new Promise(function(resolve, reject)
		{
			var currentConstraints = constraintsArray[0];
			navigator.mediaDevices.getUserMedia(currentConstraints).then(
				function(stream)
				{
					resolve(stream);
				},
				function(error)
				{
					this.log("getUserMedia error: ", error);
					this.log("Current constraints", currentConstraints);
					if (constraintsArray.length > 1)
					{
						this.getUserMedia(constraintsArray.slice(1)).then(
							function(stream)
							{
								resolve(stream);
							},
							function(error)
							{
								reject(error);
							}
						)
					}
					else
					{
						this.log("Last fallback constraints used, failing");
						reject(error);
					}
				}.bind(this)
			)
		}.bind(this))
	};

	BX.Call.PlainCall.prototype.getLocalMediaStream = function(tag, fallbackToAudio)
	{
		var self = this;
		if(!BX.type.isNotEmptyString(tag))
		{
			tag = 'main';
		}
		this.log("Requesting access to media devices");

		return new Promise(function(resolve, reject)
		{
			if(self.localStreams[tag])
			{
				return resolve(self.localStreams[tag]);
			}

			var constraintsArray = [];
			if(self.videoEnabled)
			{
				if(self.videoHd)
				{
					constraintsArray.push(self.getMediaConstraints({videoEnabled: true, hdVideo: true}));
				}
				constraintsArray.push(self.getMediaConstraints({videoEnabled: true, hdVideo: false}));
				if(fallbackToAudio)
				{
					constraintsArray.push(self.getMediaConstraints({videoEnabled: false}));
				}
			}
			else
			{
				constraintsArray.push(self.getMediaConstraints({videoEnabled: false}));
			}

			self.getUserMedia(constraintsArray).then(function(stream)
			{
				self.log("Local media stream received");
				self.localStreams[tag] = stream;
				self.runCallback(BX.Call.Event.onLocalMediaReceived, {
					tag: tag,
					stream: stream
				});
				if(tag === 'main')
				{
					self.attachVoiceDetection();
					if(self.muted)
					{
						var audioTracks = stream.getAudioTracks();
						if(audioTracks[0])
						{
							audioTracks[0].enabled = false;
						}
					}
				}
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

				resolve(self.localStreams[tag]);
			}).catch(function(e)
			{
				self.log("Could not get local media stream.", e);
				self.log("Request constraints: .", constraintsArray);
				self.runCallback("onLocalMediaError", {
					tag: tag,
					error: e
				});
				reject(e);
			});
		})
	};

	BX.Call.PlainCall.prototype.startMediaCapture = function ()
	{
		return this.getLocalMediaStream();
	};

	BX.Call.PlainCall.prototype.attachVoiceDetection = function()
	{
		if (this.voiceDetection)
		{
			this.voiceDetection.destroy();
		}
		if (this.microphoneLevelInterval)
		{
			clearInterval(this.microphoneLevelInterval);
		}

		try
		{
			this.voiceDetection = new BX.SimpleVAD({
				mediaStream: this.localStreams['main'],
				onVoiceStarted: this.onLocalVoiceStarted.bind(this),
				onVoiceStopped: this.onLocalVoiceStopped.bind(this)
			})

			this.microphoneLevelInterval = setInterval(function()
			{
				this.microphoneLevel = this.voiceDetection.currentVolume;
			}.bind(this), 200)
		}
		catch (e)
		{
			this.log('Could not attach voice detection to media stream');
		}
	};

	BX.Call.PlainCall.prototype.getDisplayMedia = function()
	{
		return new Promise(function(resolve, reject)
		{
			if(window["BXDesktopSystem"])
			{
				navigator.mediaDevices.getUserMedia({
					video: {
						mandatory: {
							chromeMediaSource : 'screen',
							maxWidth : 1920,
							maxHeight : 1080,
							maxFrameRate : 5
						}
					}
				}).then(
					function(stream)
					{
						resolve(stream);
					},
					function(error)
					{
						reject(error);
					}
				)
			}
			else if (navigator.mediaDevices.getDisplayMedia)
			{
				navigator.mediaDevices.getDisplayMedia({
					video: {
						width: {max: 1920},
						height: {max: 1080},
						frameRate: {max: 5},
					}
				}).then(
					function(stream)
					{
						resolve(stream)
					},
					function(error)
					{
						reject(error)
					}
				)
			}
			else
			{
				console.error("Screen sharing is not supported");
				reject("Screen sharing is not supported");
			}
		})
	};

	BX.Call.PlainCall.prototype.startScreenSharing = function(changeSource)
	{
		var self = this;
		changeSource = !!changeSource;
		if(this.localStreams["screen"] && !changeSource)
		{
			return;
		}

		this.getDisplayMedia().then(function(stream)
		{
			self.localStreams["screen"] = stream;

			stream.getVideoTracks().forEach(function(track)
			{
				track.addEventListener("ended", function()
				{
					self.stopScreenSharing();
				})
			});

			self.runCallback(BX.Call.Event.onUserScreenState, {
				userId: self.userId,
				screenState: true,
			});

			if(self.ready)
			{
				for(var userId in self.peers)
				{
					if(self.peers[userId].calculatedState === BX.Call.UserState.Connected)
					{
						self.peers[userId].sendMedia();
					}
				}
			}

		}).catch(function(e)
		{
			this.log(e);
		}.bind(this));
	};

	BX.Call.PlainCall.prototype.stopScreenSharing = function()
	{
		if(!this.localStreams["screen"])
		{
			return;
		}

		this.localStreams["screen"].getTracks().forEach(function(track)
		{
			track.stop();
		});
		this.localStreams["screen"] = null;
		this.runCallback(BX.Call.Event.onUserScreenState, {
			userId: this.userId,
			screenState: false,
		});

		for(var userId in this.peers)
		{
			if(this.peers[userId].calculatedState === BX.Call.UserState.Connected)
			{
				this.peers[userId].sendMedia();
			}
		}
	};

	BX.Call.PlainCall.prototype.isScreenSharingStarted = function()
	{
		return this.localStreams["screen"] instanceof MediaStream;
	};

	BX.Call.PlainCall.prototype.onLocalVoiceStarted = function()
	{
		this.talking = true;
		this.sendTalkingState();
	};

	BX.Call.PlainCall.prototype.onLocalVoiceStopped = function()
	{
		this.talking = false;
		this.sendTalkingState();
	};

	BX.Call.PlainCall.prototype.sendTalkingState = function()
	{
		if (this.talking && !this.muted)
		{
			this.runCallback(BX.Call.Event.onUserVoiceStarted, {
				userId: this.userId,
				local: true
			});
			this.signaling.sendVoiceStarted({
				userId: this.users
			});
		}
		else
		{
			this.runCallback(BX.Call.Event.onUserVoiceStopped, {
				userId: this.userId,
				local: true
			});
			this.signaling.sendVoiceStopped({
				userId: this.users
			});
		}
	}

	BX.Call.PlainCall.prototype.sendCustomMessage = function(message)
	{
		this.signaling.sendCustomMessage({
			userId: this.users,
			message: message
		});
	}

	/**
	 * @param {Object} config
	 * @param {bool} [config.useVideo]
	 * @param {bool} [config.enableMicAutoParameters]
	 * @param {MediaStream} [config.localStream]
	 */
	BX.Call.PlainCall.prototype.answer = function(config)
	{
		var self = this;
		if(!BX.type.isPlainObject(config))
		{
			config = {};
		}
		/*if(this.direction !== BX.Call.Direction.Incoming)
		{
			throw new Error('Only incoming call could be answered');
		}*/

		this.ready = true;
		this.videoEnabled = (config.useVideo == true);
		this.enableMicAutoParameters = (config.enableMicAutoParameters !== false);

		if(config.localStream instanceof MediaStream)
		{
			this.localStreams["main"] = config.localStream;
		}

		return new Promise(function(resolve, reject)
		{
			self.getLocalMediaStream("main", true).then(
				function()
				{
					self.state = BX.Call.State.Connected;

					self.runCallback(BX.Call.Event.onJoin, {
						local: true
					});
					return self.sendAnswer();
				},
				function(e)
				{
					self.runCallback(BX.Call.Event.onCallFailure, e);
				}
			).then(function()
			{
				resolve();
			});
		});
	};

	BX.Call.PlainCall.prototype.sendAnswer = function()
	{
		this.signaling.sendAnswer();
	};

	BX.Call.PlainCall.prototype.decline = function(code, reason)
	{
		this.ready = false;

		var data = {
			callId: this.id,
			callInstanceId: this.instanceId,
		};

		if(typeof(code) != 'undefined')
		{
			data.code = code;
		}
		if(typeof(reason) != 'undefined')
		{
			data.reason = reason;
		}

		BX.CallEngine.getRestClient().callMethod(ajaxActions.decline, data).then(function()
		{
			this.destroy();
		}.bind(this));
	};

	BX.Call.PlainCall.prototype.hangup = function()
	{
		if(!this.ready)
		{
			var error = new Error("Hangup in wrong state");
			this.log(error);
			return Promise.reject(error);
		}

		var tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Hangup received \n" + tempError.stack);

		this.ready = false;
		this.state = BX.Call.State.Proceeding;

		return new Promise(function (resolve, reject)
		{
			for (var userId in this.peers)
			{
				this.peers[userId].disconnect();
			}
			this.runCallback(BX.Call.Event.onLeave, {local: true});

			this.signaling.sendHangup({userId: this.users});
		}.bind(this));
	};

	BX.Call.PlainCall.prototype.pingUsers = function()
	{
		if (this.ready)
		{
			this.signaling.sendPingToUsers({userId: this.users.concat(this.userId)});
		}
	};

	BX.Call.PlainCall.prototype.pingBackend = function()
	{
		if (this.ready)
		{
			this.signaling.sendPingToBackend();
		}
	};

	BX.Call.PlainCall.prototype.getState = function()
	{

	};

	BX.Call.PlainCall.prototype.replaceLocalMediaStream = function(tag)
	{
		tag = tag || "main";
		if(this.localStreams[tag])
		{
			BX.webrtc.stopMediaStream(this.localStreams[tag]);
			this.localStreams[tag] = null;
		}

		return new Promise(function(resolve, reject)
		{
			this.getLocalMediaStream(tag).then(function()
			{
				if(this.ready)
				{
					for(var userId in this.peers)
					{
						if(this.peers[userId].isReady())
						{
							this.peers[userId].replaceMediaStream(tag);
						}
					}
				}
				resolve();
			}.bind(this)).catch(function(e)
			{
				console.error('Could not get access to hardware; don\'t really know what to do. error:', e);
				reject(e);
			}.bind(this));
		}.bind(this))
	};

	BX.Call.PlainCall.prototype.sendAllStreams = function(userId)
	{
		if(!this.peers[userId])
			return;

		if(!this.peers[userId].isReady())
			return;

		for (var tag in this.localStreams)
		{
			if(this.localStreams[tag])
			{
				this.peers[userId].sendMedia();
			}
		}
	};

	BX.Call.PlainCall.prototype.isAnyoneParticipating = function()
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

	BX.Call.PlainCall.prototype.getParticipatingUsers = function()
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

	/**
	 * Adds users, invited by you or someone else
	 * @param {Number[]} users
	 */
	BX.Call.PlainCall.prototype.addJoinedUsers = function(users)
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
		}
	};

	/**
	 * Adds users, invited by you or someone else
	 * @param {Number[]} users
	 */
	BX.Call.PlainCall.prototype.addInvitedUsers = function(users)
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

	BX.Call.PlainCall.prototype.__changeRecordState = function(params)
	{
		if (params.action !== BX.Call.View.RecordState.Started && this.recordState.userId != params.senderId)
		{
			return false;
		}

		if (params.action === BX.Call.View.RecordState.Started)
		{
			if (this.recordState.state !== BX.Call.View.RecordState.Stopped)
			{
				return false;
			}

			this.recordState.state = BX.Call.View.RecordState.Started;
			this.recordState.userId = params.senderId;
			this.recordState.date.start = params.date;
			this.recordState.date.pause = [];
		}
		else if (params.action === BX.Call.View.RecordState.Paused)
		{
			if (this.recordState.state !== BX.Call.View.RecordState.Started)
			{
				return false;
			}

			this.recordState.state = BX.Call.View.RecordState.Paused;
			this.recordState.date.pause.push(
				{start: params.date, finish: null}
			);
		}
		else if (params.action === BX.Call.View.RecordState.Resumed)
		{
			if (this.recordState.state !== BX.Call.View.RecordState.Paused)
			{
				return false;
			}

			this.recordState.state = BX.Call.View.RecordState.Started;
			var pauseElement = this.recordState.date.pause.find(function(element) {
				return element.finish === null;
			});
			if (pauseElement)
			{
				pauseElement.finish = params.date;
			}
		}
		else if (params.action === BX.Call.View.RecordState.Stopped)
		{
			this.recordState.state = BX.Call.View.RecordState.Stopped;
			this.recordState.userId = 0;
			this.recordState.date.start = null;
			this.recordState.date.pause = [];
		}

		return true;
	}

	BX.Call.PlainCall.prototype.__onPullEvent = function(command, params, extra)
	{
		var handlers = {
			'Call::answer': this.__onPullEventAnswer.bind(this),
			'Call::hangup': this.__onPullEventHangup.bind(this),
			'Call::ping': this.__onPullEventPing.bind(this),
			'Call::negotiationNeeded': this.__onPullEventNegotiationNeeded.bind(this),
			'Call::connectionOffer': this.__onPullEventConnectionOffer.bind(this),
			'Call::connectionAnswer': this.__onPullEventConnectionAnswer.bind(this),
			'Call::iceCandidate': this.__onPullEventIceCandidate.bind(this),
			'Call::voiceStarted': this.__onPullEventVoiceStarted.bind(this),
			'Call::voiceStopped': this.__onPullEventVoiceStopped.bind(this),
			'Call::microphoneState': this.__onPullEventMicrophoneState.bind(this),
			'Call::cameraState': this.__onPullEventCameraState.bind(this),
			'Call::videoPaused': this.__onPullEventVideoPaused.bind(this),
			'Call::recordState': this.__onPullEventRecordState.bind(this),
			'Call::usersJoined': this.__onPullEventUsersJoined.bind(this),
			'Call::usersInvited': this.__onPullEventUsersInvited.bind(this),
			'Call::userInviteTimeout': this.__onPullEventUserInviteTimeout.bind(this),
			'Call::associatedEntityReplaced': this.__onPullEventAssociatedEntityReplaced.bind(this),
			'Call::finish': this.__onPullEventFinish.bind(this),
			'Call::repeatAnswer': this.__onPullEventRepeatAnswer.bind(this),
			'Call::customMessage': this.__onPullEventCallCustomMessage.bind(this),
		};

		if(handlers[command])
		{
			if (command === 'Call::ping')
			{
				if (params.senderId != this.userId || params.instanceId != this.instanceId)
				{
					this.log("Signaling: ping from user " + params.senderId);
				}
			}
			else
			{
				this.log("Signaling: " + command + "; Parameters: " + JSON.stringify(params));
			}
			handlers[command].call(this, params);
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventUsersJoined = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		var users = params.users;

		this.addJoinedUsers(users);
	};

	BX.Call.PlainCall.prototype.__onPullEventUsersInvited = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		var users = params.users;

		this.addInvitedUsers(users);
	};

	BX.Call.PlainCall.prototype.__onPullEventUserInviteTimeout = function(params)
	{
		this.log('__onPullEventUserInviteTimeout', params);
		var failedUserId = params.failedUserId;

		if(this.peers[failedUserId])
		{
			this.peers[failedUserId].onInviteTimeout(false);
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventAnswer = function(params)
	{
		var senderId = Number(params.senderId);

		if(senderId == this.userId)
		{
			return this.__onPullEventAnswerSelf(params);
		}

		if(!this.ready)
		{
			return;
		}

		if(!this.peers[senderId])
		{
			return;
		}

		if (this.peers[senderId].isReady())
		{
			this.log("Received answer for user " + senderId + " in ready state, ignoring");
			return;
		}

		this.peers[senderId].setSignalingConnected(true);
		this.peers[senderId].setReady(true);
		this.peers[senderId].isLegacyMobile = params.isLegacyMobile === true;
		if(this.ready)
		{
			this.sendAllStreams(senderId);
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventAnswerSelf = function(params)
	{
		if(params.callInstanceId === this.instanceId)
			return;

		if (this.ready)
		{
			this.log("Received remote self-answer in ready state, ignoring");
			return;
		}

		// call was answered elsewhere
		this.log("Call was answered elsewhere");
		this.runCallback(BX.Call.Event.onJoin, {
			local: false
		});
	};

	BX.Call.PlainCall.prototype.__onPullEventHangup = function(params)
	{
		var senderId = params.senderId;

		if(this.userId == senderId)
		{
			if(this.instanceId != params.callInstanceId)
			{
				// self hangup elsewhere
				this.runCallback(BX.Call.Event.onLeave, {local: false});
			}
			return;
		}

		if(!this.peers[senderId])
			return;

		this.peers[senderId].disconnect(params.code);
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

	BX.Call.PlainCall.prototype.__onPullEventPing = function(params)
	{
		if (params.callInstanceId == this.instanceId)
		{
			// ignore self ping
			return;
		}

		var peer = this.peers[params.senderId];
		if(!peer)
			return;

		peer.setSignalingConnected(true);
	};

	BX.Call.PlainCall.prototype.__onPullEventNegotiationNeeded = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		/** @var {BX.Call.PlainCall.Peer} peer*/
		var peer = this.peers[params.senderId];
		if(!peer)
		{
			return;
		}

		peer.setReady(true);
		if(params.restart)
		{
			peer.reconnect()
		}
		else
		{
			peer.onNegotiationNeeded();
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventConnectionOffer = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		var peer = this.peers[params.senderId];
		if(!peer)
		{
			return;
		}

		peer.setReady(true);
		peer.setUserAgent(params.userAgent);
		peer.setConnectionOffer(params.connectionId, params.sdp, params.tracks);
	};

	BX.Call.PlainCall.prototype.__onPullEventConnectionAnswer = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		var peer = this.peers[params.senderId];
		if(!peer)
			return;

		var connectionId = params.connectionId;

		peer.setUserAgent(params.userAgent);
		peer.setConnectionAnswer(connectionId, params.sdp, params.tracks);
	};

	BX.Call.PlainCall.prototype.__onPullEventIceCandidate = function(params)
	{
		if(!this.ready)
		{
			return;
		}
		var peer = this.peers[params.senderId];
		var candidates;
		if(!peer)
			return;

		try
		{
			candidates = params.candidates;
			for(var i = 0; i < candidates.length; i++)
			{
				peer.addIceCandidate(params.connectionId, candidates[i]);
			}
		}
		catch (e)
		{
			this.log('Error parsing serialized candidate: ', e);
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventVoiceStarted = function(params)
	{
		this.runCallback(BX.Call.Event.onUserVoiceStarted, {
			userId: params.senderId
		})
	};

	BX.Call.PlainCall.prototype.__onPullEventVoiceStopped = function(params)
	{
		this.runCallback(BX.Call.Event.onUserVoiceStopped, {
			userId: params.senderId
		})
	};

	BX.Call.PlainCall.prototype.__onPullEventMicrophoneState = function(params)
	{
		this.runCallback(BX.Call.Event.onUserMicrophoneState, {
			userId: params.senderId,
			microphoneState: params.microphoneState
		})
	};

	BX.Call.PlainCall.prototype.__onPullEventCameraState = function(params)
	{
		this.runCallback(BX.Call.Event.onUserCameraState, {
			userId: params.senderId,
			cameraState: params.cameraState
		})
	};

	BX.Call.PlainCall.prototype.__onPullEventVideoPaused = function(params)
	{
		var peer = this.peers[params.senderId];
		if(!peer)
		{
			return;
		}

		this.runCallback(BX.Call.Event.onUserVideoPaused, {
			userId: params.senderId,
			videoPaused: params.videoPaused
		});

		peer.holdOutgoingVideo(!!params.videoPaused);
	};

	BX.Call.PlainCall.prototype.__onPullEventRecordState = function(params)
	{
		this.runCallback(BX.Call.Event.onUserRecordState, {
			userId: params.senderId,
			recordState: params.recordState
		})
	};

	BX.Call.PlainCall.prototype.__onPullEventAssociatedEntityReplaced = function(params)
	{
		if (params.call && params.call.ASSOCIATED_ENTITY)
		{
			this.associatedEntity = params.call.ASSOCIATED_ENTITY;
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventFinish = function(params)
	{
		this.destroy();
	};

	BX.Call.PlainCall.prototype.__onPullEventRepeatAnswer = function()
	{
		if (this.ready)
		{
			this.signaling.sendAnswer({userId: this.userId}, true);
		}
	};

	BX.Call.PlainCall.prototype.__onPullEventCallCustomMessage = function(params)
	{
		this.runCallback(BX.Call.Event.onCustomMessage, {message: params.message});
	}

	BX.Call.PlainCall.prototype.__onPeerStateChanged = function(e)
	{
		this.runCallback(BX.Call.Event.onUserStateChanged, e);

		if(e.state == BX.Call.UserState.Failed || e.state == BX.Call.UserState.Unavailable)
		{
			if(!this.isAnyoneParticipating())
			{
				this.hangup().then(this.destroy.bind(this)).catch(function(e)
					{
						//this.runCallback(BX.Call.Event.onCallFailure, e);
						this.destroy();
					}.bind(this)
				);
			}
		}
		else if(e.state == BX.Call.UserState.Connected)
		{
			this.signaling.sendMicrophoneState(e.userId, !this.muted);
			this.signaling.sendCameraState(e.userId, this.videoEnabled);
			this.wasConnected = true;
		}
	};

	BX.Call.PlainCall.prototype.__onPeerInviteTimeout = function(e)
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

	BX.Call.PlainCall.prototype.__onPeerRTCStatsReceived = function(e)
	{
		this.runCallback(BX.Call.Event.onRTCStatsReceived, e);
	};

	BX.Call.PlainCall.prototype._onUnload = function(e)
	{
		if(!this.ready)
		{
			return;
		}
		BX.CallEngine.getRestClient().callMethod(ajaxActions.hangup, {
			callId: this.id,
			callInstanceId: this.instanceId
		});

		for (var userId in this.peers)
		{
			this.peers[userId].disconnect();
		}
	};

	BX.Call.PlainCall.prototype.destroy = function ()
	{
		var tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Call destroy \n" + tempError.stack);

		// stop sending media streams
		for(var userId in this.peers)
		{
			if(this.peers[userId])
			{
				this.peers[userId].destroy();
			}
		}
		// stop media streams
		for(var tag in this.localStreams)
		{
			if(this.localStreams[tag])
			{
				BX.webrtc.stopMediaStream(this.localStreams[tag]);
				this.localStreams[tag] = null;
			}
		}

		if (this.voiceDetection)
		{
			this.voiceDetection.destroy();
			this.voiceDetection = null;
		}

		// remove all event listeners
		window.removeEventListener("unload", this._onUnloadHandler);

		clearInterval(this.pingUsersInterval);
		clearInterval(this.pingBackendInterval);
		clearInterval(this.microphoneLevelInterval);
		clearTimeout(this.reinviteTimeout);

		this.superclass.destroy.apply(this, arguments);
	};

	BX.Call.PlainCall.Signaling = function(params)
	{
		this.call = params.call;
	};

	BX.Call.PlainCall.Signaling.prototype.isIceTricklingAllowed = function()
	{
		return BX.CallEngine.getPullClient().isPublishingSupported();
	};

	BX.Call.PlainCall.Signaling.prototype.inviteUsers = function(data)
	{
		return this.__runRestAction(ajaxActions.invite, data);
	};

	BX.Call.PlainCall.Signaling.prototype.sendAnswer = function(data, repeated)
	{
		if (repeated && BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.answer, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.answer, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendConnectionOffer = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.connectionOffer, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.connectionOffer, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendConnectionAnswer = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.connectionAnswer, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.connectionAnswer, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendIceCandidate = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.iceCandidate, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.iceCandidate, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendNegotiationNeeded = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.negotiationNeeded, data);
		}
		else
		{
			return this.__runRestAction(ajaxActions.negotiationNeeded, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendVoiceStarted = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.voiceStarted, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendVoiceStopped = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.voiceStopped, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendMicrophoneState = function(users, microphoneState)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.microphoneState, {
				userId: users,
				microphoneState: microphoneState
			}, 0);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendCameraState = function(users, cameraState)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.cameraState, {
				userId: users,
				cameraState: cameraState
			}, 0);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendRecordState = function(users, recordState)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			return this.__sendPullEvent(pullEvents.recordState, {
				userId: users,
				recordState: recordState
			}, 0);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendPingToUsers = function(data)
	{
		if (BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.ping, data, 5);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendCustomMessage = function(data)
	{
		if (BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.customMessage, data, 5);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendPingToBackend = function()
	{
		var retransmit = !BX.CallEngine.getPullClient().isPublishingEnabled();
		this.__runRestAction(ajaxActions.ping, {retransmit: retransmit});
	};

	BX.Call.PlainCall.Signaling.prototype.sendUserInviteTimeout = function(data)
	{
		if (BX.CallEngine.getPullClient().isPublishingEnabled())
		{
			this.__sendPullEvent(pullEvents.userInviteTimeout, data, 0);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.sendHangup = function(data)
	{
		if(BX.CallEngine.getPullClient().isPublishingSupported())
		{
			this.__sendPullEvent(pullEvents.hangup, data, 3600);
			data.retransmit = false;
			return this.__runRestAction(ajaxActions.hangup, data);
		}
		else
		{
			data.retransmit = true;
			return this.__runRestAction(ajaxActions.hangup, data);
		}
	};

	BX.Call.PlainCall.Signaling.prototype.__sendPullEvent = function(eventName, data, expiry)
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

		if (eventName == 'Call::ping')
		{
			this.call.log('Sending p2p signaling event ' + eventName);
		}
		else
		{
			this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
		}
		BX.CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
	};

	BX.Call.PlainCall.Signaling.prototype.__runRestAction = function(signalName, data)
	{
		if(!BX.type.isPlainObject(data))
		{
			data = {};
		}

		data.callId = this.call.id;
		data.callInstanceId = this.call.instanceId;
		data.requestId = BX.Call.Engine.getInstance().getUuidv4();

		if (signalName == 'Call::ping')
		{
			this.call.log('Sending ajax-based signaling event ' + signalName);
		}
		else
		{
			this.call.log('Sending ajax-based signaling event ' + signalName + '; ' + JSON.stringify(data));
		}
		return BX.CallEngine.getRestClient().callMethod(signalName, data).catch(function(e) {console.error(e)});
	};

	BX.Call.PlainCall.Peer = function(params)
	{
		/** @var {BX.Call.PlainCall} */
		this.call = params.call;
		this.userId = params.userId;

		this.ready = params.ready === true;
		this.calling = false;
		this.inviteTimeout = false;
		this.declined = false;
		this.busy = false;
		this.signalingConnected = params.signalingConnected === true;
		this.failureReason = '';

		this.userAgent = '';
		this.isFirefox = false;
		this.isChrome = false;
		this.isLegacyMobile = params.isLegacyMobile === true;

		/*sums up from signaling, ready and connection states*/
		this.calculatedState = this.calculateState();

		this.localStreams =	{
			main: null,
			screen: null
		};

		this.videoSender = null;
		this.audioSender = null;
		this.screenSender = null;
		this.peerConnection = null;
		this.peerConnectionId = null;
		this.pendingIceCandidates = {};
		this.localIceCandidates = [];

		this.trackList = {};

		this.callbacks = {
			onStateChanged: BX.type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onInviteTimeout: BX.type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
			onMediaReceived: BX.type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
			onMediaStopped: BX.type.isFunction(params.onMediaStopped) ? params.onMediaStopped : BX.DoNothing,
			onRTCStatsReceived: BX.type.isFunction(params.onRTCStatsReceived) ? params.onRTCStatsReceived : BX.DoNothing,
			onNetworkProblem: BX.type.isFunction(params.onNetworkProblem) ? params.onNetworkProblem : BX.DoNothing,
		};

		// intervals and timeouts
		this.answerTimeout = null;
		this.callingTimeout = null;
		this.connectionTimeout = null;
		this.signalingConnectionTimeout = null;
		this.candidatesTimeout = null;

		this.statsInterval = null;

		this.connectionOfferReplyTimeout = null;
		this.negotiationNeededReplyTimeout = null;
		this.reconnectAfterDisconnectTimeout = null;

		this.connectionAttempt = 0;
		this.hasStun = false;
		this.hasTurn = false;

		this._outgoingVideoTrack = null;
		Object.defineProperty(this, 'outgoingVideoTrack', {
			get: function()
			{
				return this._outgoingVideoTrack;
			},
			set: function(track)
			{
				if (this._outgoingVideoTrack)
				{
					this._outgoingVideoTrack.stop();
				}
				this._outgoingVideoTrack = track;
				if (this._outgoingVideoTrack)
				{
					this._outgoingVideoTrack.enabled = !this.outgoingVideoHoldState;
				}
			}
		});
		this._outgoingScreenTrack = null;
		Object.defineProperty(this, 'outgoingScreenTrack', {
			get: function()
			{
				return this._outgoingScreenTrack;
			},
			set: function(track)
			{
				if (this._outgoingScreenTrack)
				{
					this._outgoingScreenTrack.stop();
				}
				this._outgoingScreenTrack = track;
				if (this._outgoingScreenTrack)
				{
					this._outgoingScreenTrack.enabled = !this.outgoingVideoHoldState;
				}
			}
		});

		this._incomingAudioTrack = null;
		this._incomingVideoTrack = null;
		this._incomingScreenTrack = null;
		Object.defineProperty(this, 'incomingAudioTrack', {
			get: this._mediaGetter('_incomingAudioTrack'),
			set: this._mediaSetter('_incomingAudioTrack', 'audio')
		});
		Object.defineProperty(this, 'incomingVideoTrack', {
			get: this._mediaGetter('_incomingVideoTrack'),
			set: this._mediaSetter('_incomingVideoTrack', 'video')
		});
		Object.defineProperty(this, 'incomingScreenTrack', {
			get: this._mediaGetter('_incomingScreenTrack'),
			set: this._mediaSetter('_incomingScreenTrack', 'screen')
		});

		this.outgoingVideoHoldState = false;

		// event handlers
		this._onPeerConnectionIceCandidateHandler = this._onPeerConnectionIceCandidate.bind(this);
		this._onPeerConnectionIceConnectionStateChangeHandler = this._onPeerConnectionIceConnectionStateChange.bind(this);
		this._onPeerConnectionIceGatheringStateChangeHandler = this._onPeerConnectionIceGatheringStateChange.bind(this);
		this._onPeerConnectionSignalingStateChangeHandler = this._onPeerConnectionSignalingStateChange.bind(this);
		//this._onPeerConnectionNegotiationNeededHandler = this._onPeerConnectionNegotiationNeeded.bind(this);
		this._onPeerConnectionTrackHandler = this._onPeerConnectionTrack.bind(this);
		this._onPeerConnectionRemoveStreamHandler = this._onPeerConnectionRemoveStream.bind(this);

		this._updateTracksDebounced = BX.debounce(this._updateTracks.bind(this), 50);

		this._waitTurnCandidatesTimeout = null;
	};

	BX.Call.PlainCall.Peer.prototype._mediaGetter = function(trackVariable)
	{
		return function()
		{
			return this[trackVariable]
		}.bind(this)
	};

	BX.Call.PlainCall.Peer.prototype._mediaSetter = function(trackVariable, kind)
	{
		return function(track)
		{
			if (this[trackVariable] != track)
			{
				this[trackVariable] = track;
				if (track)
				{
					this.callbacks.onMediaReceived({
						userId: this.userId,
						kind: kind,
						track: track
					})
				}
				else
				{
					this.callbacks.onMediaStopped({
						userId: this.userId,
						kind: kind
					})
				}
			}
		}.bind(this)
	};

	BX.Call.PlainCall.Peer.prototype.sendMedia = function(skipOffer)
	{
		if(!this.peerConnection)
		{
			if(!this.isInitiator())
			{
				this.log('waiting for the other side to send connection offer');
				this.sendNegotiationNeeded(false);
				return;
			}
		}

		if(!this.peerConnection)
		{
			var connectionId = BX.Call.Engine.getInstance().getUuidv4();
			this._createPeerConnection(connectionId);
		}
		this.updateOutgoingTracks();
		this.applyResolutionScale();

		if(!skipOffer)
		{
			this.createAndSendOffer();
		}
	};

	BX.Call.PlainCall.Peer.prototype.updateOutgoingTracks = function()
	{
		if (!this.peerConnection)
		{
			return;
		}

		var audioTrack;
		var videoTrack;
		var screenTrack;

		if(this.call.localStreams["main"] && this.call.localStreams["main"].getAudioTracks().length > 0)
		{
			audioTrack = this.call.localStreams["main"].getAudioTracks()[0];
		}
		if(this.call.localStreams["screen"] && this.call.localStreams["screen"].getVideoTracks().length > 0)
		{
			screenTrack = this.call.localStreams["screen"].getVideoTracks()[0];
		}
		if(this.call.localStreams["main"] && this.call.localStreams["main"].getVideoTracks().length > 0)
		{
			videoTrack = this.call.localStreams["main"].getVideoTracks()[0];
		}

		this.outgoingVideoTrack = videoTrack ? videoTrack.clone() : null;
		this.outgoingScreenTrack = screenTrack ? screenTrack.clone() : null;

		var tracksToSend = [];
		if (audioTrack)
		{
			tracksToSend.push(audioTrack.id + ' (audio)')
		}
		if (videoTrack)
		{
			tracksToSend.push(videoTrack.id + ' (' + videoTrack.kind + ')');
		}
		if (screenTrack)
		{
			tracksToSend.push(screenTrack.id + ' (' + screenTrack.kind + ')');
		}

		console.log("User: " + this.userId + '; Sending media streams. Tracks: ' + tracksToSend.join('; '));

		// if video sender found - replace track
		// if not found - add track
		if (this.videoSender && this.outgoingVideoTrack)
		{
			this.videoSender.replaceTrack(this.outgoingVideoTrack);
		}
		if (!this.videoSender && this.outgoingVideoTrack)
		{
			this.videoSender = this.peerConnection.addTrack(this.outgoingVideoTrack);
		}
		if (this.videoSender && !this.outgoingVideoTrack)
		{
			this.peerConnection.removeTrack(this.videoSender);
			this.videoSender = null;
		}

		// if screen sender found - replace track
		// if not found - add track
		if (this.screenSender && this.outgoingScreenTrack)
		{
			this.screenSender.replaceTrack(this.outgoingScreenTrack);
		}
		if (!this.screenSender && this.outgoingScreenTrack)
		{
			this.screenSender = this.peerConnection.addTrack(this.outgoingScreenTrack);
		}
		if (this.screenSender && !this.outgoingScreenTrack)
		{
			this.peerConnection.removeTrack(this.screenSender);
			this.screenSender = null;
		}

		// if audio sender found - replace track
		// if not found - add track
		if (this.audioSender && audioTrack)
		{
			this.audioSender.replaceTrack(audioTrack);
		}
		if (!this.audioSender && audioTrack)
		{
			this.audioSender = this.peerConnection.addTrack(audioTrack);
		}
		if (this.audioSender && !audioTrack)
		{
			this.peerConnection.removeTrack(this.audioSender);
			this.audioSender = null;
		}
	};

	BX.Call.PlainCall.Peer.prototype.getSenderMid = function(rtpSender)
	{
		if (rtpSender === null || !this.peerConnection)
		{
			return null;
		}
		var transceiver = this.peerConnection.getTransceivers().find(function(transceiver)
		{
			return transceiver.sender == rtpSender;
		});
		return transceiver ? transceiver.mid : null;
	};

	BX.Call.PlainCall.Peer.prototype.applyResolutionScale = function(factor)
	{
		if (!this.videoSender)
		{
			return;
		}

		var scaleFactor = factor || (this.screenSender ? 4 : 1);

		var params = this.videoSender.getParameters();
		if (params.encodings && params.encodings.length > 0)
		{
			params.encodings[0].scaleResolutionDownBy = scaleFactor;
			//params.encodings[0].maxBitrate = rate;
			this.videoSender.setParameters(params);
		}
	};

	BX.Call.PlainCall.Peer.prototype.replaceMediaStream = function(tag)
	{
		if(this.isRenegotiationSupported())
		{
			this.sendMedia();
		}
		else
		{
			this.localStreams[tag] = this.call.getLocalStream(tag);
			this.reconnect();
		}
	};

	BX.Call.PlainCall.Peer.prototype.holdOutgoingVideo = function(holdState)
	{
		if (this.outgoingVideoHoldState == holdState)
		{
			return;
		}

		this.outgoingVideoHoldState = holdState;
		if (this._outgoingVideoTrack)
		{
			this._outgoingVideoTrack.enabled = !this.outgoingVideoHoldState;
		}
	};

	BX.Call.PlainCall.Peer.prototype.isInitiator = function()
	{
		return this.call.userId < this.userId;
	};

	BX.Call.PlainCall.Peer.prototype.isRenegotiationSupported = function()
	{
		return  true;
		return (BX.browser.IsChrome() && this.isChrome);
	};

	BX.Call.PlainCall.Peer.prototype.setReady = function(ready)
	{
		this.ready = ready;
		if(this.ready)
		{
			this.declined = false;
			this.busy = false;
		}
		if(this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype.isReady = function()
	{
		return this.ready;
	};

	BX.Call.PlainCall.Peer.prototype.onInvited = function()
	{
		this.ready = false;
		this.inviteTimeout = false;
		this.declined = false;
		this.calling = true;

		if(this.callingTimeout)
		{
			clearTimeout(this.callingTimeout);
		}
		this.callingTimeout = setTimeout(function ()
		{
			this.onInviteTimeout(true);
		}.bind(this), 30000);
		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype.onInviteTimeout = function(internal)
	{
		clearTimeout(this.callingTimeout);
		this.calling = false;
		this.inviteTimeout = true;
		if(internal)
		{
			this.callbacks.onInviteTimeout({
				userId: this.userId
			});
		}
		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype.setUserAgent = function(userAgent)
	{
		this.userAgent = userAgent;
		this.isFirefox = userAgent.toLowerCase().indexOf('firefox') != -1;
		this.isChrome = userAgent.toLowerCase().indexOf('chrome') != -1;
		this.isLegacyMobile = userAgent === 'Bitrix Legacy Mobile';
	};

	BX.Call.PlainCall.Peer.prototype.getUserAgent = function()
	{
		return this.userAgent;
	};

	BX.Call.PlainCall.Peer.prototype.isParticipating = function()
	{
		if(this.calling)
			return true;

		if(this.declined || this.busy)
			return false;

		if(this.peerConnection)
		{
			// todo: maybe we should check iceConnectionState as well.
			var iceConnectionState = this.peerConnection.iceConnectionState;
			if(iceConnectionState == 'checking' || iceConnectionState == 'connected' || iceConnectionState == 'completed')
			{
				return true;
			}
		}

		return false;
	};

	BX.Call.PlainCall.Peer.prototype.setSignalingConnected = function(signalingConnected)
	{
		this.signalingConnected = signalingConnected;
		this.updateCalculatedState();

		if(this.signalingConnected)
			this.refreshSignalingTimeout();
		else
			this.stopSignalingTimeout();
	};

	BX.Call.PlainCall.Peer.prototype.isSignalingConnected = function()
	{
		return this.signalingConnected;
	};

	BX.Call.PlainCall.Peer.prototype.setDeclined = function(declined)
	{
		this.declined = declined;
		if(this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype.setBusy = function(busy)
	{
		this.busy = busy;
		if(this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype.isDeclined = function()
	{
		return this.declined;
	};

	BX.Call.PlainCall.Peer.prototype.updateCalculatedState = function()
	{
		var calculatedState = this.calculateState();

		if(this.calculatedState != calculatedState)
		{
			this.callbacks.onStateChanged({
				userId: this.userId,
				state: calculatedState,
				previousState: this.calculatedState,
				isLegacyMobile: this.isLegacyMobile,
				networkProblem: !this.hasStun || !this.hasTurn
			});
			this.calculatedState = calculatedState;
		}
	};

	BX.Call.PlainCall.Peer.prototype.calculateState = function()
	{
		if(this.peerConnection)
		{
			if (this.failureReason !== '')
			{
				return BX.Call.UserState.Failed;
			}

			if(this.peerConnection.iceConnectionState === 'connected' || this.peerConnection.iceConnectionState === 'completed')
			{
				return BX.Call.UserState.Connected;
			}

			return BX.Call.UserState.Connecting;
		}

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
	};

	BX.Call.PlainCall.Peer.prototype.getSignaling = function()
	{
		return this.call.signaling;
	};

	BX.Call.PlainCall.Peer.prototype.startStatisticsGathering = function()
	{
		clearInterval(this.statsInterval);

		this.statsInterval = setInterval(function()
		{
			if(!this.peerConnection)
			{
				return false;
			}

			this.peerConnection.getStats().then(function(stats)
			{
				this.callbacks.onRTCStatsReceived({
					userId: this.userId,
					stats: stats
				});
			}.bind(this));
		}.bind(this), 1000);
	};

	BX.Call.PlainCall.Peer.prototype.stopStatisticsGathering = function()
	{
		clearInterval(this.statsInterval);
		this.statsInterval = null;
	};

	BX.Call.PlainCall.Peer.prototype.updateCandidatesTimeout = function()
	{
		if(this.candidatesTimeout)
		{
			clearTimeout(this.candidatesTimeout);
		}

		this.candidatesTimeout = setTimeout(this.sendIceCandidates.bind(this), 500);
	};

	BX.Call.PlainCall.Peer.prototype.sendIceCandidates = function()
	{
		this.log("User " + this.userId + ": sending ICE candidates due to the timeout");

		this.candidatesTimeout = null;
		if(this.localIceCandidates.length > 0)
		{
			this.getSignaling().sendIceCandidate({
				userId: this.userId,
				connectionId: this.peerConnectionId,
				candidates: this.localIceCandidates
			});
			this.localIceCandidates = [];
		}
		else
		{
			this.log("User " + this.userId +  ": ICE candidates pool is empty");
		}
	};

	BX.Call.PlainCall.Peer.prototype._createPeerConnection = function(id)
	{
		this.log("User " + this.userId + ": Creating peer connection");
		var connectionConfig = {
			"iceServers": [
				{
					urls: "stun:" + this.call.turnServer
				},
				{
					urls: "turn:" + this.call.turnServer,
					username: this.call.turnServerLogin,
					credential: this.call.turnServerPassword
				}
			],
			// iceTransportPolicy: 'relay'
		};

		this.localIceCandidates = [];
		this.peerConnection = new RTCPeerConnection(connectionConfig);
		this.peerConnectionId = id;

		this.peerConnection.addEventListener("icecandidate", this._onPeerConnectionIceCandidateHandler);
		this.peerConnection.addEventListener("iceconnectionstatechange", this._onPeerConnectionIceConnectionStateChangeHandler);
		this.peerConnection.addEventListener("icegatheringstatechange", this._onPeerConnectionIceGatheringStateChangeHandler);
		this.peerConnection.addEventListener("signalingstatechange", this._onPeerConnectionSignalingStateChangeHandler);
		// this.peerConnection.addEventListener("negotiationneeded", this._onPeerConnectionNegotiationNeededHandler);
		this.peerConnection.addEventListener("track", this._onPeerConnectionTrackHandler);
		this.peerConnection.addEventListener("removestream", this._onPeerConnectionRemoveStreamHandler);

		this.failureReason = '';
		this.hasStun = false;
		this.hasTurn = false;
		this.updateCalculatedState();

		this.startStatisticsGathering();
	};

	BX.Call.PlainCall.Peer.prototype._destroyPeerConnection = function()
	{
		if(!this.peerConnection)
			return;

		this.log("User " + this.userId + ": Destroying peer connection " + this.peerConnectionId);
		this.stopStatisticsGathering();

		this.peerConnection.removeEventListener("icecandidate", this._onPeerConnectionIceCandidateHandler);
		this.peerConnection.removeEventListener("iceconnectionstatechange", this._onPeerConnectionIceConnectionStateChangeHandler);
		this.peerConnection.removeEventListener("icegatheringstatechange", this._onPeerConnectionIceGatheringStateChangeHandler);
		this.peerConnection.removeEventListener("signalingstatechange", this._onPeerConnectionSignalingStateChangeHandler);
		// this.peerConnection.removeEventListener("negotiationneeded", this._onPeerConnectionNegotiationNeededHandler);
		this.peerConnection.removeEventListener("track", this._onPeerConnectionTrackHandler);
		this.peerConnection.removeEventListener("removestream", this._onPeerConnectionRemoveStreamHandler);

		this.localIceCandidates = [];
		if(this.pendingIceCandidates[this.peerConnectionId])
		{
			delete this.pendingIceCandidates[this.peerConnectionId];
		}

		this.peerConnection.close();
		this.peerConnection = null;
		this.peerConnectionId = null;
		this.videoSender = null;
		this.audioSender = null;
		this.incomingAudioTrack = null;
		this.incomingVideoTrack = null;
		this.incomingScreenTrack = null;
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionIceCandidate = function(e)
	{
		var candidate = e.candidate;
		this.log("User " + this.userId +  ": ICE candidate discovered. Candidate: " + (candidate ? candidate.candidate : candidate));

		if(candidate)
		{
			if(this.getSignaling().isIceTricklingAllowed())
			{
				this.getSignaling().sendIceCandidate({
					userId: this.userId,
					connectionId: this.peerConnectionId,
					candidates: [candidate.toJSON()]
				});
			}
			else
			{
				this.localIceCandidates.push(candidate.toJSON());
				this.updateCandidatesTimeout();
			}

			var match = candidate.candidate.match(/typ\s(\w+)?/);
			if(match)
			{
				var type = match[1];
				if(type == "srflx")
				{
					this.hasStun = true;
				}
				else if (type == "relay")
				{
					this.hasTurn = true;
				}
			}
		}
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionIceConnectionStateChange = function(e)
	{
		this.log("User " + this.userId +  ": ICE connection state changed. New state: " + this.peerConnection.iceConnectionState);

		if(this.peerConnection.iceConnectionState === "connected" || this.peerConnection.iceConnectionState === "completed")
		{
			this.connectionAttempt = 0;
			clearTimeout(this.reconnectAfterDisconnectTimeout);
			this._updateTracksDebounced();
		}
		else if(this.peerConnection.iceConnectionState === "failed")
		{
			this.log("ICE connection failed. Trying to restore connection immediately");
			this.reconnect();
		}
		else if(this.peerConnection.iceConnectionState === "disconnected")
		{
			this.log("ICE connection lost. Waiting 5 seconds before trying to restore it");
			clearTimeout(this.reconnectAfterDisconnectTimeout);
			this.reconnectAfterDisconnectTimeout = setTimeout(function()
			{
				this.reconnect();
			}.bind(this), 5000);
		}

		this.updateCalculatedState();
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionIceGatheringStateChange = function(e)
	{
		var connection = e.target;
		this.log("User " + this.userId +  ": ICE gathering state changed to : " + connection.iceGatheringState);

		if(connection.iceGatheringState === 'complete')
		{
			this.log("User " + this.userId +  ": ICE gathering complete");
			if (!this.hasStun || !this.hasTurn)
			{
				var s = [];
				if (!this.hasTurn)
				{
					s.push("TURN");
				}
				if (!this.hasStun)
				{
					s.push("STUN");
				}
				this.log("Connectivity problem detected: no ICE candidates from " + s.join(" and ") + " servers");
				console.error("Connectivity problem detected: no ICE candidates from " + s.join(" and ") + " servers");
				this.callbacks.onNetworkProblem();
			}

			if (!this.hasTurn && !this.hasStun)
			{

			}

			if(!this.getSignaling().isIceTricklingAllowed())
			{
				if(this.localIceCandidates.length > 0)
				{
					this.getSignaling().sendIceCandidate({
						userId: this.userId,
						connectionId: this.peerConnectionId,
						candidates: this.localIceCandidates
					});
					this.localIceCandidates = [];
				}
				else
				{
					this.log("User " + this.userId +  ": ICE candidates already sent");
				}
			}
		}
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionSignalingStateChange = function(e)
	{
		this.log("User " + this.userId + " PC signalingState: " + this.peerConnection.signalingState);
		if (this.peerConnection.signalingState === "stable")
		{
			this._updateTracksDebounced();
		}
	};

	// this event is unusable in the current version of desktop (cef 64) and leads to signaling cycling
	// todo: reconsider using it after new version is released
	BX.Call.PlainCall.Peer.prototype._onPeerConnectionNegotiationNeeded = function(e)
	{
		this.log("User " + this.userId +  ": needed negotiation for peer connection");
		this.log("signaling state: ", e.target.signalingState);
		this.log("ice connection state: ", e.target.iceConnectionState);
		this.log("pendingRemoteDescription: ", e.target.pendingRemoteDescription);

		if(e.target.iceConnectionState !== "new" && e.target.iceConnectionState !== "connected" && e.target.iceConnectionState !== "completed")
		{
			this.log("User " + this.userId + ": wrong connection state");
			return;
		}

		if(this.isInitiator())
		{
			this.createAndSendOffer();
		}
		else
		{
			this.sendNegotiationNeeded(this.peerConnection._forceReconnect === true);
		}
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionTrack = function(e)
	{
		this.log("User " + this.userId + ": media track received: ", e.track.id + " (" + e.track.kind + ")");

		if (e.track.kind == "video")
		{
			e.track.addEventListener("mute", this._onVideoTrackMuted.bind(this));
			e.track.addEventListener("unmute", this._onVideoTrackUnMuted.bind(this));
			e.track.addEventListener("ended", this._onVideoTrackEnded.bind(this));
			if (this.trackList[e.track.id] === 'screen')
			{
				this.incomingScreenTrack = e.track;
			}
			else
			{
				this.incomingVideoTrack = e.track
			}
		}
		else if (e.track.kind === 'audio')
		{
			this.incomingAudioTrack = e.track;
		}
	};

	BX.Call.PlainCall.Peer.prototype._onPeerConnectionRemoveStream = function(e)
	{
		this.log("User: " + this.userId + "_onPeerConnectionRemoveStream: ", e);
	};

	BX.Call.PlainCall.Peer.prototype._onVideoTrackMuted = function()
	{
		console.log("Video track muted");
		//this._updateTracksDebounced();
	};

	BX.Call.PlainCall.Peer.prototype._onVideoTrackUnMuted = function()
	{
		console.log("Video track unmuted");
		//this._updateTracksDebounced();
	};

	BX.Call.PlainCall.Peer.prototype._onVideoTrackEnded = function()
	{
		console.log("Video track ended");
	};

	BX.Call.PlainCall.Peer.prototype._updateTracks = function()
	{
		if (!this.peerConnection)
		{
			return null;
		}
		var audioTrack = null;
		var videoTrack = null;
		var screenTrack = null;
		this.peerConnection.getTransceivers().forEach(function(tr)
		{
			this.call.log("[debug] tr direction: " + tr.direction + " currentDirection: " + tr.currentDirection);
			if (tr.currentDirection == "sendrecv" || tr.currentDirection == "recvonly")
			{
				if (tr.receiver && tr.receiver.track)
				{
					var track = tr.receiver.track;
					if (track.kind === 'audio')
					{
						audioTrack = track;
					}
					else if (track.kind === 'video')
					{
						if (this.trackList[tr.mid] === 'screen')
						{
							screenTrack = track;
						}
						else
						{
							videoTrack = track;
						}
					}
				}
			}
		}, this);
		this.incomingAudioTrack = audioTrack;
		this.incomingVideoTrack = videoTrack;
		this.incomingScreenTrack = screenTrack;
	};

	BX.Call.PlainCall.Peer.prototype.stopSignalingTimeout = function()
	{
		clearTimeout(this.signalingConnectionTimeout);
	};

	BX.Call.PlainCall.Peer.prototype.refreshSignalingTimeout = function()
	{
		clearTimeout(this.signalingConnectionTimeout);
		this.signalingConnectionTimeout = setTimeout(this._onLostSignalingConnection.bind(this), signalingConnectionRefreshPeriod);
	};

	BX.Call.PlainCall.Peer.prototype._onLostSignalingConnection = function()
	{
		this.setSignalingConnected(false);
	};

	BX.Call.PlainCall.Peer.prototype._onConnectionOfferReplyTimeout = function(connectionId)
	{
		this.log("did not receive connection answer for connection " + connectionId);

		this.reconnect();
	};

	BX.Call.PlainCall.Peer.prototype._onNegotiationNeededReplyTimeout = function()
	{
		this.log("did not receive connection offer in time");

		this.reconnect();
	};

	BX.Call.PlainCall.Peer.prototype.setConnectionOffer = function(connectionId, sdp, trackList)
	{
		this.log("User " + this.userId + ": applying connection offer for connection " + connectionId);

		clearTimeout(this.negotiationNeededReplyTimeout);
		this.negotiationNeededReplyTimeout = null;

		if(!this.call.isReady())
			return;

		if(!this.isReady())
			return;

		if (trackList)
		{
			this.trackList = BX.util.array_flip(trackList);
		}

		if(this.peerConnection)
		{
			if(this.peerConnectionId !== connectionId)
			{
				this._destroyPeerConnection();
				this._createPeerConnection(connectionId);
			}
		}
		else
		{
			this._createPeerConnection(connectionId);
		}

		this.applyOfferAndSendAnswer(sdp);
	};

	BX.Call.PlainCall.Peer.prototype.createAndSendOffer = function(config)
	{
		var self = this;

		connectionConfig = defaultConnectionOptions;
		for(var key in config)
		{
			connectionConfig[key] = config[key];
		}

		self.peerConnection.createOffer(connectionConfig).then(function(offer)
		{
			self.log("User " + self.userId + ": Created connection offer.");
			self.log("Applying local description");
			return self.peerConnection.setLocalDescription(offer);
		}).then(function()
		{
			self.sendOffer();
		})
	};

	BX.Call.PlainCall.Peer.prototype.sendOffer = function()
	{
		clearTimeout(this.connectionOfferReplyTimeout);
		this.connectionOfferReplyTimeout = setTimeout(
			function()
			{
				this._onConnectionOfferReplyTimeout(this.peerConnectionId);
			}.bind(this),
			signalingWaitReplyPeriod
		);

		this.getSignaling().sendConnectionOffer({
			userId: this.userId,
			connectionId: this.peerConnectionId,
			sdp: this.peerConnection.localDescription.sdp,
			tracks: {
				audio: this.getSenderMid(this.audioSender),
				video: this.getSenderMid(this.videoSender),
				screen: this.getSenderMid(this.screenSender),
			},
			userAgent: navigator.userAgent
		})
	};

	BX.Call.PlainCall.Peer.prototype.sendNegotiationNeeded = function(restart)
	{
		restart = restart === true;
		clearTimeout(this.negotiationNeededReplyTimeout);
		this.negotiationNeededReplyTimeout = setTimeout(
			function()
			{
				this._onNegotiationNeededReplyTimeout();
			}.bind(this),
			signalingWaitReplyPeriod
		);

		var params = {
			userId: this.userId
		};
		if (restart)
		{
			params.restart = true;
		}

		this.getSignaling().sendNegotiationNeeded(params);
	};

	BX.Call.PlainCall.Peer.prototype.applyOfferAndSendAnswer = function(sdp)
	{
		var sessionDescription = new RTCSessionDescription({
			type: "offer",
			sdp: sdp
		});

		this.log("User: " + this.userId + "; Applying remote offer");
		this.log("User: " + this.userId + "; Peer ice connection state ", this.peerConnection.iceConnectionState);

		this.peerConnection.setRemoteDescription(sessionDescription).then(function()
		{
			if(this.peerConnection.iceConnectionState === 'new')
			{
				this.sendMedia(true);
			}

			return this.peerConnection.createAnswer();
		}.bind(this)).then(function(answer)
		{
			this.log("Created connection answer.");
			this.log("Applying local description.");
			return this.peerConnection.setLocalDescription(answer);
		}.bind(this)).then(function()
		{
			this.applyPendingIceCandidates();
			this.getSignaling().sendConnectionAnswer({
				userId: this.userId,
				connectionId: this.peerConnectionId,
				sdp: this.peerConnection.localDescription.sdp,
				tracks: {
					audio: this.getSenderMid(this.audioSender),
					video: this.getSenderMid(this.videoSender),
					screen: this.getSenderMid(this.screenSender),
				},
				userAgent: navigator.userAgent
			});
		}.bind(this)).catch(function(e)
		{
			this.failureReason = e.toString();
			this.updateCalculatedState();
			this.log("Could not apply remote offer", e);
			console.error("Could not apply remote offer", e);
		}.bind(this));
	};

	BX.Call.PlainCall.Peer.prototype.setConnectionAnswer = function(connectionId, sdp, trackList)
	{
		if(!this.peerConnection || this.peerConnectionId != connectionId)
		{
			this.log("Could not apply answer, for unknown connection " + connectionId);
			return;
		}

		if(this.peerConnection.signalingState !== 'have-local-offer')
		{
			this.log("Could not apply answer, wrong peer connection signaling state " + this.peerConnection.signalingState);
			return;
		}

		if (trackList)
		{
			this.trackList = BX.util.array_flip(trackList);
		}

		var sessionDescription = new RTCSessionDescription({
			type: "answer",
			sdp: sdp
		});

		clearTimeout(this.connectionOfferReplyTimeout);

		this.log("User: " + this.userId + "; Applying remote answer");
		this.peerConnection.setRemoteDescription(sessionDescription).then(function()
		{
			this.applyPendingIceCandidates();
		}.bind(this)).catch(function(e)
		{
			this.failureReason = e.toString();
			this.updateCalculatedState();
			this.log(e);
		}.bind(this));
	};

	BX.Call.PlainCall.Peer.prototype.addIceCandidate = function(connectionId, candidate)
	{
		if(!this.peerConnection)
			return;

		if(this.peerConnectionId != connectionId)
		{
			this.log("Error: Candidate for unknown connection " + connectionId);
			return;
		}

		if(this.peerConnection.remoteDescription && this.peerConnection.remoteDescription.type)
		{
			this.peerConnection.addIceCandidate(candidate).then(function()
			{
				this.log("User: " + this.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
			}.bind(this)).catch(function(e)
			{
				this.log(e);
			}.bind(this));
		}
		else
		{
			if(!this.pendingIceCandidates[connectionId])
			{
				this.pendingIceCandidates[connectionId] = [];
			}
			this.pendingIceCandidates[connectionId].push(candidate);
		}
	};

	BX.Call.PlainCall.Peer.prototype.applyPendingIceCandidates = function()
	{
		var self = this;
		if(!this.peerConnection || !this.peerConnection.remoteDescription.type)
			return;

		if(BX.type.isArray(this.pendingIceCandidates[this.peerConnectionId]))
		{
			this.pendingIceCandidates[this.peerConnectionId].forEach(function(candidate)
			{
				self.peerConnection.addIceCandidate(candidate).then(function()
				{
					this.log("User: " + this.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
				}.bind(this));
			}, this);

			self.pendingIceCandidates[this.peerConnectionId] = [];
		}
	};

	BX.Call.PlainCall.Peer.prototype.onNegotiationNeeded = function()
	{
		if(this.peerConnection)
		{
			if (this.peerConnection.signalingState == "have-local-offer")
			{
				this.sendOffer();
			}
			else
			{
				this.createAndSendOffer({iceRestart: true});
			}
		}
		else
		{
			this.sendMedia();
		}
	};

	BX.Call.PlainCall.Peer.prototype.reconnect = function()
	{
		clearTimeout(this.reconnectAfterDisconnectTimeout);

		this.connectionAttempt++;

		if(this.connectionAttempt > 3)
		{
			this.log("Error: Too many reconnection attempts, giving up");
			this.failureReason = "Could not connect to user in time";
			this.updateCalculatedState();
			return;
		}

		this.log("Trying to restore ICE connection. Attempt " + this.connectionAttempt);
		if(this.isInitiator())
		{
			this._destroyPeerConnection();
			this.sendMedia();
		}
		else
		{
			this.sendNegotiationNeeded(true);
		}
	};

	BX.Call.PlainCall.Peer.prototype.disconnect = function()
	{
		this._destroyPeerConnection();
	};

	BX.Call.PlainCall.Peer.prototype.log = function()
	{
		this.call.log.apply(this.call, arguments);
	};

	BX.Call.PlainCall.Peer.prototype.destroy = function()
	{
		this.disconnect();

		if(this.voiceDetection)
		{
			this.voiceDetection.destroy();
			this.voiceDetection = null;
		}

		for (var tag in this.localStreams)
		{
			this.localStreams[tag] = null;
		}
		this.outgoingVideoTrack = null;
		this.outgoingScreenTrack = null;
		this.outgoingVideoHoldState = false;

		this.incomingAudioTrack = null;
		this.incomingVideoTrack = null;
		this.incomingScreenTrack = null;

		clearTimeout(this.answerTimeout);
		this.answerTimeout = null;

		clearTimeout(this.connectionTimeout);
		this.connectionTimeout = null;

		clearTimeout(this.signalingConnectionTimeout);
		this.signalingConnectionTimeout = null;

		this.callbacks.onStateChanged = BX.DoNothing;
		this.callbacks.onMediaReceived = BX.DoNothing;
		this.callbacks.onMediaStopped = BX.DoNothing;
	};

})();