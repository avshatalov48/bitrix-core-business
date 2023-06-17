import {Type} from 'main.core';

import {AbstractCall} from './abstract_call';
import {
	CallEngine,
	EndpointDirection,
	UserState,
	Quality,
	UserMnemonic,
	CallEvent,
	CallState,
	CallType,
	Provider
} from './engine';
import {SimpleVAD} from './simple_vad'
import Util from '../util'

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

const ajaxActions = {
	invite: 'im.call.invite',
	cancel: 'im.call.cancel',
	answer: 'im.call.answer',
	decline: 'im.call.decline',
	hangup: 'im.call.hangup',
	ping: 'im.call.ping'
};

const pullEvents = {
	ping: 'Call::ping',
	answer: 'Call::answer',
	hangup: 'Call::hangup',
	userInviteTimeout: 'Call::userInviteTimeout',
	repeatAnswer: 'Call::repeatAnswer',
};

const clientEvents = {
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

const scenarioEvents = {
	viewerJoined: 'Call::viewerJoined',
	viewerLeft: 'Call::viewerLeft',

	joinRoomOffer: 'Call::joinRoomOffer',
	transferRoomHost: 'Call::transferRoomHost',
	listRoomsResponse: 'Call::listRoomsResponse',
	roomUpdated: 'Call::roomUpdated',
};

const VoximplantCallEvent = {
	onCallConference: 'VoximplantCall::onCallConference'
};

const pingPeriod = 5000;
const backendPingPeriod = 25000;
const reinvitePeriod = 5500;
const connectionRestoreTime = 15000;

// const MAX_USERS_WITHOUT_SIMULCAST = 6;

export class VoximplantCall extends AbstractCall
{
	static Event = VoximplantCallEvent
	peers: { [key: number]: Peer }
	localVAD: ?SimpleVAD

	constructor(config)
	{
		super(config);

		this.videoQuality = Quality.VeryHigh; // initial video quality. will drop on new peers connecting

		this.voximplantCall = null;

		this.signaling = new Signaling({
			call: this
		});

		this.peers = {};
		this.joinedElsewhere = false;
		this.joinedAsViewer = false;
		this.localVideoShown = false;
		this._localUserState = UserState.Idle;
		this.clientEventsBound = false;
		this._screenShared = false;
		this.videoAllowedFrom = UserMnemonic.all;
		this.direction = EndpointDirection.SendRecv;

		this.microphoneLevelInterval = null;

		this.rooms = {};

		window.addEventListener("unload", this.#onWindowUnload);

		this.initPeers();

		this.pingUsersInterval = setInterval(this.pingUsers.bind(this), pingPeriod);
		this.pingBackendInterval = setInterval(this.pingBackend.bind(this), backendPingPeriod);

		this.lastPingReceivedTimeout = null;
		this.lastSelfPingReceivedTimeout = null;

		this.reinviteTimeout = null;

		// There are two kinds of reconnection events: from call (for media connection) and from client (for signaling).
		// So we have to use counter to convert these two events to one
		this._reconnectionEventCount = 0;

		this.pullEventHandlers = {
			'Call::answer': this.#onPullEventAnswer,
			'Call::hangup': this.#onPullEventHangup,
			'Call::usersJoined': this.#onPullEventUsersJoined,
			'Call::usersInvited': this.#onPullEventUsersInvited,
			'Call::userInviteTimeout': this.#onPullEventUserInviteTimeout,
			'Call::ping': this.#onPullEventPing,
			'Call::finish': this.#onPullEventFinish,
			'Call::repeatAnswer': this.#onPullEventRepeatAnswer,
		}

	};

	get provider()
	{
		return Provider.Voximplant;
	}

	get screenShared()
	{
		return this._screenShared;
	}

	set screenShared(screenShared)
	{
		if (screenShared != this._screenShared)
		{
			this._screenShared = screenShared;
			this.signaling.sendScreenState(this._screenShared);
		}
	}

	get localUserState()
	{
		return this._localUserState
	}

	set localUserState(state)
	{
		if (state == this._localUserState)
		{
			return;
		}
		this.runCallback(CallEvent.onUserStateChanged, {
			userId: this.userId,
			state: state,
			previousState: this._localUserState,
			direction: this.direction,
		});
		this._localUserState = state;
	}

	get reconnectionEventCount()
	{
		return this._reconnectionEventCount;
	}

	set reconnectionEventCount(newValue)
	{
		if (this._reconnectionEventCount === 0 && newValue > 0)
		{
			this.runCallback(CallEvent.onReconnecting);
		}
		if (newValue === 0)
		{
			this.runCallback(CallEvent.onReconnected);
		}
		this._reconnectionEventCount = newValue;
	}

	initPeers()
	{
		this.users.forEach((userId) =>
		{
			userId = Number(userId);
			this.peers[userId] = this.createPeer(userId);
		});
	};

	reinitPeers()
	{
		for (let userId in this.peers)
		{
			if (this.peers.hasOwnProperty(userId) && this.peers[userId])
			{
				this.peers[userId].destroy();
				this.peers[userId] = null;
			}
		}

		this.initPeers();
	};

	pingUsers()
	{
		if (this.ready)
		{
			const users = this.users.concat(this.userId);
			this.signaling.sendPingToUsers({userId: users}, true);
		}
	};

	pingBackend()
	{
		if (this.ready)
		{
			this.signaling.sendPingToBackend();
		}
	};

	createPeer(userId)
	{
		let incomingVideoAllowed;
		if (this.videoAllowedFrom === UserMnemonic.all)
		{
			incomingVideoAllowed = true;
		}
		else if (this.videoAllowedFrom === UserMnemonic.none)
		{
			incomingVideoAllowed = false;
		}
		else if (Type.isArray(this.videoAllowedFrom))
		{
			incomingVideoAllowed = this.videoAllowedFrom.some(allowedUserId => allowedUserId == userId);
		}
		else
		{
			incomingVideoAllowed = true;
		}

		return new Peer({
			call: this,
			userId: userId,
			ready: userId == this.initiatorId,
			isIncomingVideoAllowed: incomingVideoAllowed,

			onMediaReceived: (e) =>
			{
				this.runCallback(CallEvent.onRemoteMediaReceived, e);
				if (e.kind === 'video')
				{
					this.runCallback(CallEvent.onUserVideoPaused, {
						userId: userId,
						videoPaused: false
					});
				}
			},
			onMediaRemoved: (e) =>
			{
				this.runCallback(CallEvent.onRemoteMediaStopped, e);
			},
			onVoiceStarted: () =>
			{
				// todo: uncomment to switch to SDK VAD events
				/*this.runCallback(Event.onUserVoiceStarted, {
					userId: userId
				});*/
			},
			onVoiceEnded: () =>
			{
				// todo: uncomment to switch to SDK VAD events
				/*this.runCallback(Event.onUserVoiceStopped, {
					userId: userId
				});*/
			},
			onStateChanged: this.#onPeerStateChanged,
			onInviteTimeout: this.#onPeerInviteTimeout,

		})
	};

	getUsers()
	{
		let result = {};
		for (let userId in this.peers)
		{
			result[userId] = this.peers[userId].calculatedState;
		}
		return result;
	};

	getUserCount()
	{
		return Object.keys(this.peers).length;
	};

	getClient()
	{
		return new Promise((resolve, reject) =>
		{
			BX.Voximplant.getClient({restClient: CallEngine.getRestClient()}).then((client) =>
			{
				client.enableSilentLogging();
				client.setLoggerCallback((e) => this.log(e.label + ": " + e.message));
				this.log("User agent: " + navigator.userAgent);
				this.log("Voximplant SDK version: " + VoxImplant.version);

				this.bindClientEvents();

				resolve(client);
			}).catch(reject);
		});
	};

	bindClientEvents()
	{
		const streamManager = VoxImplant.Hardware.StreamManager.get();

		if (!this.clientEventsBound)
		{
			VoxImplant.getInstance().on(VoxImplant.Events.MicAccessResult, this.#onMicAccessResult);
			if (VoxImplant.Events.Reconnecting)
			{
				VoxImplant.getInstance().on(VoxImplant.Events.Reconnecting, this.#onClientReconnecting);
				VoxImplant.getInstance().on(VoxImplant.Events.Reconnected, this.#onClientReconnected);
			}

			// streamManager.on(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.#onLocalDevicesUpdated);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.#onLocalMediaRendererAdded);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererUpdated, this.#onLocalMediaRendererAdded);
			streamManager.on(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.#onBeforeLocalMediaRendererRemoved);
			this.clientEventsBound = true;
		}
	};

	removeClientEvents()
	{
		if (!('VoxImplant' in window))
		{
			return;
		}

		VoxImplant.getInstance().off(VoxImplant.Events.MicAccessResult, this.#onMicAccessResult);
		if (VoxImplant.Events.Reconnecting)
		{
			VoxImplant.getInstance().off(VoxImplant.Events.Reconnecting, this.#onClientReconnecting);
			VoxImplant.getInstance().off(VoxImplant.Events.Reconnected, this.#onClientReconnected);
		}

		const streamManager = VoxImplant.Hardware.StreamManager.get();
		// streamManager.off(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.#onLocalDevicesUpdated);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, this.#onLocalMediaRendererAdded);
		streamManager.off(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, this.#onBeforeLocalMediaRendererRemoved);
		this.clientEventsBound = false;
	};

	setMuted(muted)
	{
		if (this.muted == muted)
		{
			return;
		}

		this.muted = muted;

		if (this.voximplantCall)
		{
			if (this.muted)
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

	isMuted()
	{
		return this.muted;
	}

	setVideoEnabled(videoEnabled)
	{
		videoEnabled = (videoEnabled === true);
		if (this.videoEnabled == videoEnabled)
		{
			return;
		}

		this.videoEnabled = videoEnabled;
		if (this.voximplantCall)
		{
			if (videoEnabled)
			{
				this.#showLocalVideo();
			}
			else
			{
				if (this.localVideoShown)
				{
					VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(() =>
					{
						this.localVideoShown = false;
						this.runCallback(CallEvent.onLocalMediaReceived, {
							tag: "main",
							stream: new MediaStream(),
						});
					});
				}
			}

			this.voximplantCall.sendVideo(this.videoEnabled);
			this.signaling.sendCameraState(this.videoEnabled);
		}
	};

	setCameraId(cameraId)
	{
		if (this.cameraId == cameraId)
		{
			return;
		}
		this.cameraId = cameraId;

		if (this.voximplantCall)
		{
			VoxImplant.Hardware.CameraManager.get().getInputDevices().then(() =>
			{
				VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(this.voximplantCall, this.constructCameraParams());
			});
		}
	};

	setMicrophoneId(microphoneId)
	{
		if (this.microphoneId == microphoneId)
		{
			return;
		}

		this.microphoneId = microphoneId;
		if (this.voximplantCall)
		{
			VoxImplant.Hardware.AudioDeviceManager.get().getInputDevices().then(() =>
			{
				VoxImplant.Hardware.AudioDeviceManager.get().setCallAudioSettings(this.voximplantCall, {
					inputId: this.microphoneId
				});
			});
		}
	};

	getCurrentMicrophoneId()
	{
		if (this.voximplantCall.peerConnection.impl.getTransceivers)
		{
			const transceivers = this.voximplantCall.peerConnection.impl.getTransceivers();
			if (transceivers.length > 0)
			{
				const audioTrack = transceivers[0].sender.track;
				const audioTrackSettings = audioTrack.getSettings();
				return audioTrackSettings.deviceId;
			}
		}
		return this.microphoneId;
	};

	constructCameraParams()
	{
		let result = {};

		if (this.cameraId)
		{
			result.cameraId = this.cameraId;
		}

		result.videoQuality = this.videoHd ? VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_HD : VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_nHD;
		result.facingMode = true;
		return result;
	};

	useHdVideo(flag)
	{
		this.videoHd = (flag === true);
	};

	requestFloor(requestActive)
	{
		this.signaling.sendFloorRequest(requestActive);
	};

	sendRecordState(recordState)
	{
		this.signaling.sendRecordState(recordState);
	};

	sendEmotion(toUserId, emotion)
	{
		this.signaling.sendEmotion(toUserId, emotion);
	};

	sendCustomMessage(message, repeatOnConnect)
	{
		this.signaling.sendCustomMessage(message, repeatOnConnect);
	};

	/**
	 * Updates list of users,
	 * @param {UserMnemonic | int[]} userList
	 */
	allowVideoFrom(userList)
	{
		if (this.videoAllowedFrom == userList)
		{
			return;
		}
		this.videoAllowedFrom = userList;

		if (userList === UserMnemonic.all)
		{
			this.signaling.sendShowAll();
			userList = Object.keys(this.peers);
		}
		else if (userList === UserMnemonic.none)
		{
			this.signaling.sendHideAll();
			userList = [];
		}
		else if (Type.isArray(userList))
		{
			this.signaling.sendShowUsers(userList)
		}
		else
		{
			throw new Error("userList is in wrong format");
		}

		let users = {};
		userList.forEach(userId => users[userId] = true);

		for (let userId in this.peers)
		{
			if (!this.peers.hasOwnProperty(userId))
			{
				continue;
			}
			if (users[userId])
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
	 * @deprecated
	 */

	/*#setMaxBitrate(bitrate)
	{
		if (this.voximplantCall)
		{
			const transceivers = this.voximplantCall.peerConnection.getTransceivers();
			if (!transceivers)
			{
				return;
			}
			transceivers.forEach((tr) =>
			{
				if (tr.sender && tr.sender.track && tr.sender.track.kind === 'video' && !tr.stoped && tr.currentDirection.indexOf('send') !== -1)
				{
					const sender = tr.sender;
					const parameters = sender.getParameters();
					if (!parameters.encodings)
					{
						parameters.encodings = [{}];
					}
					if (bitrate === 0)
					{
						delete parameters.encodings[0].maxBitrate;
					}
					else
					{
						parameters.encodings[0].maxBitrate = bitrate * 1000;
					}
					sender.setParameters(parameters);
				}
			});
		}
	};*/

	#showLocalVideo()
	{
		return new Promise((resolve) =>
		{
			VoxImplant.Hardware.StreamManager.get().showLocalVideo(false).then(
				() =>
				{
					this.localVideoShown = true;
					resolve();
				},
				() =>
				{
					this.localVideoShown = true;
					resolve();
				}
			)
		})
	};

	#hideLocalVideo()
	{
		return new Promise((resolve) =>
		{
			if (!('VoxImplant' in window))
			{
				resolve();
				return;
			}

			VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(
				() =>
				{
					this.localVideoShown = false;
					resolve();
				},
				() =>
				{
					this.localVideoShown = false;
					resolve();
				}
			);
		})
	};

	startScreenSharing()
	{
		if (!this.voximplantCall)
		{
			return;
		}

		const showLocalView = !this.videoEnabled;
		const replaceTrack = this.videoEnabled || this.screenShared;

		this.voximplantCall.shareScreen(showLocalView, replaceTrack)
			.then(() =>
			{
				this.log("Screen shared");
				this.screenShared = true;
			})
			.catch((error) =>
			{
				console.error(error);
				this.log("Screen sharing error:", error)
			})
		;
	};

	stopScreenSharing()
	{
		if (!this.voximplantCall)
		{
			return;
		}

		this.voximplantCall.stopSharingScreen()
			.then(() =>
			{
				this.log("Screen is no longer shared");
				this.screenShared = false;
			})
		;
	};

	isScreenSharingStarted()
	{
		return this.screenShared;
	};

	/**
	 * Invites users to participate in the call.
	 */
	inviteUsers(config: { users: number[] } = {})
	{
		this.ready = true;
		let users = Type.isArray(config.users) ? config.users : this.users;

		this.attachToConference()
			.then(() =>
			{
				this.signaling.sendPingToUsers({userId: users});

				if (users.length > 0)
				{
					return this.signaling.inviteUsers({
						userIds: users,
						video: this.videoEnabled ? 'Y' : 'N'
					})
				}
			})
			.then(() =>
			{
				this.state = CallState.Connected;
				this.runCallback(CallEvent.onJoin, {
					local: true
				});
				for (let i = 0; i < users.length; i++)
				{
					const userId = parseInt(users[i]);
					if (!this.users.includes(userId))
					{
						this.users.push(userId);
					}
					if (!this.peers[userId])
					{
						this.peers[userId] = this.createPeer(userId);

						if (this.type === CallType.Instant)
						{
							this.runCallback(CallEvent.onUserInvited, {
								userId: userId
							});
						}
					}
					if (this.type === CallType.Instant)
					{
						this.peers[userId].onInvited();
						this.scheduleRepeatInvite();
					}
				}
			})
			.catch((e) =>
			{
				this.#onFatalError(e)
			})
		;
	};

	scheduleRepeatInvite()
	{
		clearTimeout(this.reinviteTimeout);
		this.reinviteTimeout = setTimeout(() => this.repeatInviteUsers(), reinvitePeriod)
	};

	repeatInviteUsers()
	{
		clearTimeout(this.reinviteTimeout);
		if (!this.ready)
		{
			return;
		}
		let usersToRepeatInvite = [];
		for (let userId in this.peers)
		{
			if (this.peers.hasOwnProperty(userId) && this.peers[userId].calculatedState === UserState.Calling)
			{
				usersToRepeatInvite.push(userId);
			}
		}

		if (usersToRepeatInvite.length === 0)
		{
			return;
		}
		const inviteParams = {
			userIds: usersToRepeatInvite,
			video: this.videoEnabled ? 'Y' : 'N',
			isRepeated: 'Y',
		}
		this.signaling.inviteUsers(inviteParams).then(() => this.scheduleRepeatInvite());
	};

	/**
	 * @param {Object} config
	 * @param {bool?} [config.useVideo]
	 * @param {bool?} [config.joinAsViewer]
	 */
	answer(config = {})
	{
		this.ready = true;
		const joinAsViewer = config.joinAsViewer === true;
		this.videoEnabled = (config.useVideo === true);

		if (!joinAsViewer)
		{
			this.signaling.sendAnswer();
		}
		this.attachToConference({joinAsViewer: joinAsViewer})
			.then(() =>
			{
				this.log("Attached to conference");
				this.state = CallState.Connected;
				this.runCallback(CallEvent.onJoin, {
					local: true
				});
			})
			.catch((err) =>
			{
				this.#onFatalError(err);
			})
		;
	};

	decline(code)
	{
		this.ready = false;
		const data = {
			callId: this.id,
			callInstanceId: this.instanceId,
		};
		if (code)
		{
			data.code = code
		}

		CallEngine.getRestClient().callMethod(ajaxActions.decline, data);
	};

	hangup(code, reason)
	{
		if (!this.ready)
		{
			const error = new Error("Hangup in wrong state");
			this.log(error);
			return;
		}

		const tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Hangup received \n" + tempError.stack);

		if (this.localVAD)
		{
			this.localVAD.destroy();
			this.localVAD = null;
		}
		clearInterval(this.microphoneLevelInterval);

		let data = {};
		this.ready = false;
		if (typeof (code) != 'undefined')
		{
			data.code = code;
		}
		if (typeof (reason) != 'undefined')
		{
			data.reason = reason;
		}
		this.state = CallState.Proceeding;
		this.runCallback(CallEvent.onLeave, {local: true});

		//clone users and append current user id to send event to all participants of the call
		data.userId = this.users.slice(0).concat(this.userId);
		this.signaling.sendHangup(data);
		this.muted = false;

		// for future reconnections
		this.reinitPeers();

		if (this.voximplantCall)
		{
			this.voximplantCall._replaceVideoSharing = false;
			try
			{
				this.voximplantCall.hangup();
			} catch (e)
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
		this.#hideLocalVideo();
	};

	attachToConference(options: { joinAsViewer: ?boolean } = {})
	{
		const joinAsViewer = options.joinAsViewer === true;
		if (this.voximplantCall && this.voximplantCall.state() === "CONNECTED")
		{
			if (this.joinedAsViewer === joinAsViewer)
			{
				return Promise.resolve();
			}
			else
			{
				return Promise.reject("Already joined call in another mode");
			}
		}

		return new Promise((resolve, reject) =>
		{
			this.direction = joinAsViewer ? EndpointDirection.RecvOnly : EndpointDirection.SendRecv;
			this.sendTelemetryEvent("call");

			this.getClient().then((voximplantClient) =>
			{
				this.localUserState = UserState.Connecting;

				// workaround to set default video settings before starting call. ugly, but I do not see another way
				VoxImplant.Hardware.CameraManager.get().setDefaultVideoSettings(this.constructCameraParams());
				if (this.microphoneId)
				{
					VoxImplant.Hardware.AudioDeviceManager.get().setDefaultAudioSettings({
						inputId: this.microphoneId
					});
				}

				if (this.videoEnabled)
				{
					this.#showLocalVideo();
				}

				try
				{
					if (joinAsViewer)
					{
						this.voximplantCall = voximplantClient.joinAsViewer("bx_conf_" + this.id, {
							'X-Direction': EndpointDirection.RecvOnly
						});
					}
					else
					{
						this.voximplantCall = voximplantClient.callConference({
							number: "bx_conf_" + this.id,
							video: {sendVideo: this.videoEnabled, receiveVideo: true},
							// simulcast: (this.getUserCount() > MAX_USERS_WITHOUT_SIMULCAST),
							// simulcastProfileName: 'b24',
							customData: JSON.stringify({
								cameraState: this.videoEnabled,
							})
						});
					}
				} catch (e)
				{
					console.error(e);
					return reject(e);
				}
				this.joinedAsViewer = joinAsViewer;

				if (!this.voximplantCall)
				{
					this.log("Error: could not create voximplant call");
					return reject({code: "VOX_NO_CALL"});
				}

				this.runCallback(VoximplantCallEvent.onCallConference, {
					call: this
				});

				this.bindCallEvents();

				this.voximplantCall.on(
					VoxImplant.CallEvents.Connected,
					() =>
					{
						this.#onCallConnected();
						resolve();
					},
					{once: true});
				this.voximplantCall.on(
					VoxImplant.CallEvents.Failed,
					(e) =>
					{
						this.#onCallFailed(e);
						reject(e);
					},
					{once: true}
				);
			}).catch(this.#onFatalError.bind(this));
		});
	};

	#onCallConnected()
	{
		this.log("Call connected");
		this.sendTelemetryEvent("connect");
		this.localUserState = UserState.Connected;

		this.voximplantCall.on(VoxImplant.CallEvents.Failed, this.#onCallDisconnected);

		if (this.muted)
		{
			this.voximplantCall.muteMicrophone();
		}
		this.signaling.sendMicrophoneState(!this.muted);
		this.signaling.sendCameraState(this.videoEnabled);

		if (this.videoAllowedFrom == UserMnemonic.none)
		{
			this.signaling.sendHideAll();
		}
		else if (Type.isArray(this.videoAllowedFrom))
		{
			this.signaling.sendShowUsers(this.videoAllowedFrom);
		}
	};

	#onCallFailed(e)
	{
		this.log("Could not attach to conference", e);
		this.sendTelemetryEvent("connect_failure");
		this.localUserState = UserState.Failed;

		const client = VoxImplant.getInstance();
		client.enableSilentLogging(false);
		client.setLoggerCallback(null);
	};

	bindCallEvents()
	{
		this.voximplantCall.on(VoxImplant.CallEvents.Disconnected, this.#onCallDisconnected);
		this.voximplantCall.on(VoxImplant.CallEvents.MessageReceived, this.#onCallMessageReceived);
		if (Util.shouldCollectStats())
		{
			this.voximplantCall.on(VoxImplant.CallEvents.CallStatsReceived, this.#onCallStatsReceived);
		}

		this.voximplantCall.on(VoxImplant.CallEvents.EndpointAdded, this.#onCallEndpointAdded);
		if (VoxImplant.CallEvents.Reconnecting)
		{
			this.voximplantCall.on(VoxImplant.CallEvents.Reconnecting, this.#onCallReconnecting);
			this.voximplantCall.on(VoxImplant.CallEvents.Reconnected, this.#onCallReconnected);
		}
	};

	removeCallEvents()
	{
		if (this.voximplantCall)
		{
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Disconnected, this.#onCallDisconnected);
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.MessageReceived, this.#onCallMessageReceived);
			if (Util.shouldCollectStats())
			{
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.CallStatsReceived, this.#onCallStatsReceived);
			}
			this.voximplantCall.removeEventListener(VoxImplant.CallEvents.EndpointAdded, this.#onCallEndpointAdded);
			if (VoxImplant.CallEvents.Reconnecting)
			{
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnecting, this.#onCallReconnecting);
				this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnected, this.#onCallReconnected);
			}
		}
	};

	/**
	 * Adds new users to call
	 * @param {Number[]} users
	 */
	addJoinedUsers(users)
	{
		for (let i = 0; i < users.length; i++)
		{
			const userId = Number(users[i]);
			if (userId == this.userId || this.peers[userId])
			{
				continue;
			}
			this.peers[userId] = this.createPeer(userId);
			if (!this.users.includes(userId))
			{
				this.users.push(userId);
			}
			this.runCallback(CallEvent.onUserInvited, {
				userId: userId
			});
		}
	};

	/**
	 * Adds users, invited by you or someone else
	 * @param {Number[]} users
	 */
	addInvitedUsers(users)
	{
		for (let i = 0; i < users.length; i++)
		{
			const userId = Number(users[i]);
			if (userId == this.userId)
			{
				continue;
			}

			if (this.peers[userId])
			{
				if (this.peers[userId].calculatedState === UserState.Failed || this.peers[userId].calculatedState === UserState.Idle)
				{
					if (this.type === CallType.Instant)
					{
						this.peers[userId].onInvited();
					}
				}
			}
			else
			{
				this.peers[userId] = this.createPeer(userId);
				if (this.type === CallType.Instant)
				{
					this.peers[userId].onInvited();
				}
			}
			if (!this.users.includes(userId))
			{
				this.users.push(userId);
			}
			this.runCallback(CallEvent.onUserInvited, {
				userId: userId
			});
		}
	};

	isAnyoneParticipating()
	{
		for (let userId in this.peers)
		{
			if (this.peers[userId].isParticipating())
			{
				return true;
			}
		}

		return false;
	};

	getParticipatingUsers()
	{
		let result = [];
		for (let userId in this.peers)
		{
			if (this.peers[userId].isParticipating())
			{
				result.push(userId);
			}
		}
		return result;
	};

	updateRoom(roomData)
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

	currentRoom()
	{
		return this._currentRoomId ? this.rooms[this._currentRoomId] : null;
	}

	isRoomSpeaker()
	{
		return this.currentRoom() ? this.currentRoom().speaker == this.userId : false;
	}

	joinRoom(roomId)
	{
		this.signaling.sendJoinRoom(roomId);
	}

	requestRoomSpeaker()
	{
		this.signaling.sendRequestRoomSpeaker(this._currentRoomId);
	}

	leaveCurrentRoom()
	{
		this.signaling.sendLeaveRoom(this._currentRoomId);
	}

	listRooms()
	{
		return new Promise((resolve) =>
		{
			this.signaling.sendListRooms();
			this.__resolveListRooms = resolve;
		});
	}

	#onPeerStateChanged = (e) =>
	{
		this.runCallback(CallEvent.onUserStateChanged, e);

		if (!this.ready)
		{
			return;
		}
		if (e.state == UserState.Failed || e.state == UserState.Unavailable || e.state == UserState.Declined || e.state == UserState.Idle)
		{
			if (this.type == CallType.Instant && !this.isAnyoneParticipating())
			{
				this.hangup();
			}
		}
	};

	#onPeerInviteTimeout = (e) =>
	{
		if (!this.ready)
		{
			return;
		}
		this.signaling.sendUserInviteTimeout({
			userId: this.users,
			failedUserId: e.userId
		})
	};

	__onPullEvent(command, params, extra)
	{
		if (this.pullEventHandlers[command])
		{
			if (command != 'Call::ping')
			{
				this.log("Signaling: " + command + "; Parameters: " + JSON.stringify(params));
			}
			this.pullEventHandlers[command].call(this, params);
		}
	};

	#onPullEventAnswer = (params) =>
	{
		const senderId = Number(params.senderId);

		if (senderId == this.userId)
		{
			return this.__onPullEventAnswerSelf(params);
		}

		if (!this.peers[senderId])
		{
			this.peers[senderId] = this.createPeer(senderId);
			this.runCallback(CallEvent.onUserInvited, {
				userId: senderId
			});
		}

		if (!this.users.includes(senderId))
		{
			this.users.push(senderId);
		}

		this.peers[senderId].setReady(true);
	};

	__onPullEventAnswerSelf(params)
	{
		if (params.callInstanceId === this.instanceId)
		{
			return;
		}

		// call was answered elsewhere
		this.joinedElsewhere = true;
		this.runCallback(CallEvent.onJoin, {
			local: false
		});
	};

	#onPullEventHangup = (params) =>
	{
		const senderId = params.senderId;
		if (this.userId == senderId && this.instanceId != params.callInstanceId)
		{
			// Call declined by the same user elsewhere
			this.runCallback(CallEvent.onLeave, {local: false});
			return;
		}

		if (!this.peers[senderId])
		{
			return;
		}

		this.peers[senderId].setReady(false);

		if (params.code == 603)
		{
			this.peers[senderId].setDeclined(true);
		}
		else if (params.code == 486)
		{
			this.peers[senderId].setBusy(true);
			console.error("user " + senderId + " is busy");
		}

		if (this.ready && this.type == CallType.Instant && !this.isAnyoneParticipating())
		{
			this.hangup();
		}
	};

	#onPullEventUsersJoined = (params) =>
	{
		this.log('__onPullEventUsersJoined', params);
		const users = params.users;

		this.addJoinedUsers(users);
	};

	#onPullEventUsersInvited = (params) =>
	{
		this.log('__onPullEventUsersInvited', params);
		const users = params.users;

		if (this.type === CallType.Instant)
		{
			this.addInvitedUsers(users);
		}
	};

	#onPullEventUserInviteTimeout = (params) =>
	{
		this.log('__onPullEventUserInviteTimeout', params);
		const failedUserId = params.failedUserId;

		if (this.peers[failedUserId])
		{
			this.peers[failedUserId].onInviteTimeout(false);
		}
	};

	#onPullEventPing = (params) =>
	{
		if (params.callInstanceId == this.instanceId)
		{
			// ignore self ping
			return;
		}

		const senderId = Number(params.senderId);

		if (senderId == this.userId)
		{
			if (!this.joinedElsewhere)
			{
				this.runCallback(CallEvent.onJoin, {
					local: false
				});
				this.joinedElsewhere = true;
			}
			clearTimeout(this.lastSelfPingReceivedTimeout);
			this.lastSelfPingReceivedTimeout = setTimeout(this.#onNoSelfPingsReceived, pingPeriod * 2.1);
		}
		clearTimeout(this.lastPingReceivedTimeout);
		this.lastPingReceivedTimeout = setTimeout(this.#onNoPingsReceived, pingPeriod * 2.1);
		if (this.peers[senderId])
		{
			this.peers[senderId].setReady(true);
		}
	};

	#onNoPingsReceived = () =>
	{
		if (!this.ready)
		{
			this.destroy();
		}
	};

	#onNoSelfPingsReceived = () =>
	{
		this.runCallback(CallEvent.onLeave, {
			local: false
		});
		this.joinedElsewhere = false;
	};

	#onPullEventFinish = () =>
	{
		this.destroy();
	};

	#onPullEventRepeatAnswer = () =>
	{
		if (this.ready)
		{
			this.signaling.sendAnswer({userId: this.userId}, true);
		}
	}

	#onLocalMediaRendererAdded = (e) =>
	{
		const renderer = e.renderer;
		const trackLabel = renderer.stream.getVideoTracks().length > 0 ? renderer.stream.getVideoTracks()[0].label : "";
		this.log("__onLocalMediaRendererAdded", renderer.kind, trackLabel);

		if (renderer.kind === "video")
		{
			let tag;
			if (trackLabel.match(/^screen|window|tab|web-contents-media-stream/i))
			{
				tag = "screen";
			}
			else
			{
				tag = "main";
			}

			this.screenShared = tag === "screen";

			this.runCallback(CallEvent.onLocalMediaReceived, {
				tag: tag,
				stream: renderer.stream,
			});
		}
		else if (renderer.kind === "sharing")
		{
			this.runCallback(CallEvent.onLocalMediaReceived, {
				tag: "screen",
				stream: renderer.stream,
			});
			this.screenShared = true;
		}
	};

	#onBeforeLocalMediaRendererRemoved = (e) =>
	{
		const renderer = e.renderer;
		this.log("__onBeforeLocalMediaRendererRemoved", renderer.kind);

		if (renderer.kind === "sharing" && !this.videoEnabled)
		{
			this.runCallback(CallEvent.onLocalMediaReceived, {
				tag: "main",
				stream: new MediaStream(),
			});
			this.screenShared = false;
		}
	};

	#onMicAccessResult = (e) =>
	{
		if (e.result)
		{
			if (e.stream.getAudioTracks().length > 0)
			{
				if (this.localVAD)
				{
					this.localVAD.destroy();
				}
				this.localVAD = new SimpleVAD({
					mediaStream: e.stream,
					onVoiceStarted: () =>
					{
						this.runCallback(CallEvent.onUserVoiceStarted, {
							userId: this.userId,
							local: true
						});
					},
					onVoiceStopped: () =>
					{
						this.runCallback(CallEvent.onUserVoiceStopped, {
							userId: this.userId,
							local: true
						});
					},
				});

				clearInterval(this.microphoneLevelInterval);
				this.microphoneLevelInterval = setInterval(
					() =>this.microphoneLevel = this.localVAD.currentVolume,
					200
				);
			}
		}
	};

	#onCallReconnecting = () =>
	{
		this.reconnectionEventCount++;
	}

	#onCallReconnected = () =>
	{
		this.reconnectionEventCount--;
	}

	#onClientReconnecting = () =>
	{
		this.reconnectionEventCount++;
	}

	#onClientReconnected = () =>
	{
		this.reconnectionEventCount--;
	};

	#onCallDisconnected = (e) =>
	{
		this.log("__onCallDisconnected", (e && e.headers ? {headers: e.headers} : null));
		this.sendTelemetryEvent("disconnect");
		this.localUserState = UserState.Idle;

		this.ready = false;
		this.muted = false;
		this.joinedAsViewer = false;
		this.reinitPeers();

		this.#hideLocalVideo();
		this.removeCallEvents();
		this.voximplantCall = null;

		const client = VoxImplant.getInstance();
		client.enableSilentLogging(false);
		client.setLoggerCallback(null);

		this.state = CallState.Proceeding;
		this.runCallback(CallEvent.onLeave, {
			local: true
		});
	};

	#onWindowUnload = () =>
	{
		if (this.ready && this.voximplantCall)
		{
			this.signaling.sendHangup({
				userId: this.users
			});
		}
	};

	#onFatalError = (error) =>
	{
		if (error && error.call)
		{
			delete error.call;
		}
		this.log("onFatalError", error);

		this.ready = false;
		this.muted = false;
		this.localUserState = UserState.Failed;
		this.reinitPeers();

		this.#hideLocalVideo().then(() =>
		{
			if (this.voximplantCall)
			{
				this.removeCallEvents();
				try
				{
					this.voximplantCall.hangup({
						'X-Reason': 'Fatal error',
						'X-Error': typeof (error) === 'string' ? error : error.code || error.name
					})
				} catch (e)
				{
					this.log("Voximplant hangup error: ", e);
					console.error("Voximplant hangup error: ", e);
				}
				this.voximplantCall = null;
			}

			if (typeof (VoxImplant) !== 'undefined')
			{
				const client = VoxImplant.getInstance();

				client.enableSilentLogging(false);
				client.setLoggerCallback(null);
			}

			if (typeof (error) === "string")
			{
				this.runCallback(CallEvent.onCallFailure, {
					name: error
				});
			}
			else if (error.name)
			{
				this.runCallback(CallEvent.onCallFailure, error);
			}
		})
	};

	#setEndpointForUser(userName: string, endpoint)
	{
		// user connected to conference (userName is in format `user${id}`
		const userId = parseInt(userName.substring(4));
		if (this.peers[userId])
		{
			this.peers[userId].setEndpoint(endpoint);
		}
		this.wasConnected = true;
	}

	#onCallEndpointAdded = (e) =>
	{
		const endpoint = e.endpoint;
		const userName = endpoint.userName;
		this.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);
		console.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);

		if (Type.isStringFilled(userName) && userName.startsWith('user'))
		{
			this.#setEndpointForUser(userName, endpoint)
		}
		else
		{
			endpoint.addEventListener(VoxImplant.EndpointEvents.InfoUpdated, (e) =>
			{
				const endpoint = e.endpoint;
				const userName = endpoint.userName;
				this.log("VoxImplant.EndpointEvents.InfoUpdated (" + userName + ")", e.endpoint);

				if (Type.isStringFilled(userName) && userName.startsWith('user'))
				{
					this.#setEndpointForUser(userName, endpoint)
				}
			});

			this.log('Unknown endpoint ' + userName);
			console.warn('Unknown endpoint ' + userName);
		}
	};

	#onCallStatsReceived = (e) =>
	{
		if (this.logger)
		{
			this.logger.sendStat(transformVoxStats(e.stats, this.voximplantCall));
		}
	}

	#onJoinRoomOffer = (e) =>
	{
		console.warn("__onJoinRoomOffer", e)
		this.updateRoom({
			id: e.roomId,
			users: e.users,
			speaker: e.speaker,
		});
		this.runCallback(CallEvent.onJoinRoomOffer, {
			roomId: e.roomId,
			users: e.users,
			initiator: e.initiator,
			speaker: e.speaker,
		})
	}

	#onRoomUpdated = (e) =>
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
			this.runCallback(CallEvent.onLeaveRoom, {
				roomId: e.roomId
			})
		}
		else if (e.roomId !== this._currentRoomId && e.users.indexOf(this.userId) !== -1)
		{
			this._currentRoomId = e.roomId;
			this.runCallback(CallEvent.onJoinRoom, {
				roomId: e.roomId,
				speaker: this.currentRoom().speaker,
				users: this.currentRoom().users,
			})
		}
		else if (speakerChanged)
		{
			this.runCallback(CallEvent.onTransferRoomSpeaker, {
				roomId: e.roomId,
				speaker: e.speaker,
				previousSpeaker: previousSpeaker,
				initiator: e.initiator,
			})
		}
	};

	#onCallMessageReceived = (e) =>
	{
		let message;
		let peer;

		try
		{
			message = JSON.parse(e.text);
		} catch (err)
		{
			this.log("Could not parse scenario message.", err);
			return;
		}

		const eventName = message.eventName;
		if (eventName === clientEvents.voiceStarted)
		{
			// todo: remove after switching to SDK VAD events
			this.runCallback(CallEvent.onUserVoiceStarted, {
				userId: message.senderId
			});
		}
		else if (eventName === clientEvents.voiceStopped)
		{
			// todo: remove after switching to SDK VAD events
			this.runCallback(CallEvent.onUserVoiceStopped, {
				userId: message.senderId
			});
		}
		else if (eventName === clientEvents.microphoneState)
		{
			this.runCallback(CallEvent.onUserMicrophoneState, {
				userId: message.senderId,
				microphoneState: message.microphoneState === "Y"
			});
		}
		else if (eventName === clientEvents.cameraState)
		{
			this.runCallback(CallEvent.onUserCameraState, {
				userId: message.senderId,
				cameraState: message.cameraState === "Y"
			});
		}
		else if (eventName === clientEvents.videoPaused)
		{
			this.runCallback(CallEvent.onUserVideoPaused, {
				userId: message.senderId,
				videoPaused: message.videoPaused === "Y"
			});
		}
		else if (eventName === clientEvents.screenState)
		{
			this.runCallback(CallEvent.onUserScreenState, {
				userId: message.senderId,
				screenState: message.screenState === "Y"
			});
		}
		else if (eventName === clientEvents.recordState)
		{
			this.runCallback(CallEvent.onUserRecordState, {
				userId: message.senderId,
				recordState: message.recordState
			});
		}
		else if (eventName === clientEvents.floorRequest)
		{
			this.runCallback(CallEvent.onUserFloorRequest, {
				userId: message.senderId,
				requestActive: message.requestActive === "Y"
			})
		}
		else if (eventName === clientEvents.emotion)
		{
			this.runCallback(CallEvent.onUserEmotion, {
				userId: message.senderId,
				toUserId: message.toUserId,
				emotion: message.emotion
			})
		}
		else if (eventName === clientEvents.customMessage)
		{
			this.runCallback(CallEvent.onCustomMessage, {
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
			this.#onJoinRoomOffer(message);
		}
		else if (eventName === scenarioEvents.roomUpdated)
		{
			// console.log(message)
			this.#onRoomUpdated(message);
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
				peer.setDirection(EndpointDirection.RecvOnly);
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

	sendTelemetryEvent(eventName)
	{
		Util.sendTelemetryEvent({
			call_id: this.id,
			user_id: this.userId,
			kind: "voximplant",
			event: eventName,
		})
	};

	destroy()
	{
		this.ready = false;
		this.joinedAsViewer = false;
		this.#hideLocalVideo();
		if (this.localVAD)
		{
			this.localVAD.destroy();
			this.localVAD = null;
		}
		clearInterval(this.microphoneLevelInterval);
		if (this.voximplantCall)
		{
			this.removeCallEvents();
			if (this.voximplantCall.state() != "ENDED")
			{
				this.voximplantCall.hangup();
			}
			this.voximplantCall = null;
		}

		for (let userId in this.peers)
		{
			if (this.peers.hasOwnProperty(userId) && this.peers[userId])
			{
				this.peers[userId].destroy();
			}
		}

		this.removeClientEvents();

		clearTimeout(this.lastPingReceivedTimeout);
		clearTimeout(this.lastSelfPingReceivedTimeout);
		clearInterval(this.pingUsersInterval);
		clearInterval(this.pingBackendInterval);

		window.removeEventListener("unload", this.#onWindowUnload);
		return super.destroy();
	};
}

class Signaling
{
	constructor(params)
	{
		this.call = params.call;
	};

	inviteUsers(data)
	{
		return this.#runRestAction(ajaxActions.invite, data);
	};

	sendAnswer(data, repeated)
	{
		if (repeated && CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.answer, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.answer, data);
		}
	};

	sendCancel(data)
	{
		return this.#runRestAction(ajaxActions.cancel, data);
	};

	sendHangup(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.hangup, data);
			data.retransmit = false;
			this.#runRestAction(ajaxActions.hangup, data);
		}
		else
		{
			data.retransmit = true;
			this.#runRestAction(ajaxActions.hangup, data);
		}
	};

	sendVoiceStarted(data)
	{
		return this.#sendMessage(clientEvents.voiceStarted, data);
	};

	sendVoiceStopped(data)
	{
		return this.#sendMessage(clientEvents.voiceStopped, data);
	};

	sendMicrophoneState(microphoneState)
	{
		return this.#sendMessage(clientEvents.microphoneState, {
			microphoneState: microphoneState ? "Y" : "N"
		});
	};

	sendCameraState(cameraState)
	{
		return this.#sendMessage(clientEvents.cameraState, {
			cameraState: cameraState ? "Y" : "N"
		});
	};

	sendScreenState(screenState)
	{
		return this.#sendMessage(clientEvents.screenState, {
			screenState: screenState ? "Y" : "N"
		});
	};

	sendRecordState(recordState)
	{
		return this.#sendMessage(clientEvents.recordState, recordState);
	};

	sendFloorRequest(requestActive)
	{
		return this.#sendMessage(clientEvents.floorRequest, {
			requestActive: requestActive ? "Y" : "N"
		});
	};

	sendEmotion(toUserId, emotion)
	{
		return this.#sendMessage(clientEvents.emotion, {
			toUserId: toUserId,
			emotion: emotion
		});
	};

	sendCustomMessage(message, repeatOnConnect)
	{
		return this.#sendMessage(clientEvents.customMessage, {
			message: message,
			repeatOnConnect: !!repeatOnConnect
		});
	};

	sendShowUsers(users)
	{
		return this.#sendMessage(clientEvents.showUsers, {
			users: users
		});
	};

	sendShowAll()
	{
		return this.#sendMessage(clientEvents.showAll, {});
	};

	sendHideAll()
	{
		return this.#sendMessage(clientEvents.hideAll, {});
	};

	sendPingToUsers(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.ping, data, 0);
		}
	};

	sendPingToBackend()
	{
		this.#runRestAction(ajaxActions.ping, {retransmit: false});
	};

	sendUserInviteTimeout(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.userInviteTimeout, data, 0);
		}
	};

	sendJoinRoom(roomId)
	{
		return this.#sendMessage(clientEvents.joinRoom, {
			roomId: roomId
		});
	};

	sendLeaveRoom(roomId)
	{
		return this.#sendMessage(clientEvents.leaveRoom, {
			roomId: roomId
		});
	};

	sendListRooms()
	{
		return this.#sendMessage(clientEvents.listRooms);
	};

	sendRequestRoomSpeaker(roomId)
	{
		return this.#sendMessage(clientEvents.requestRoomSpeaker, {
			roomId: roomId
		});
	};

	#sendPullEvent(eventName, data, expiry)
	{
		expiry = expiry || 5;
		if (!data.userId)
		{
			throw new Error('userId is not found in data');
		}

		if (!Type.isArray(data.userId))
		{
			data.userId = [data.userId];
		}
		if (data.userId.length === 0)
		{
			// nobody to send, exit
			return;
		}

		data.callInstanceId = this.call.instanceId;
		data.senderId = this.call.userId;
		data.callId = this.call.id;
		data.requestId = Util.getUuidv4();

		this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
		CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
	};

	#sendMessage(eventName, data)
	{
		if (!this.call.voximplantCall)
		{
			return;
		}

		if (!Type.isPlainObject(data))
		{
			data = {};
		}
		data.eventName = eventName;
		data.requestId = Util.getUuidv4();

		this.call.voximplantCall.sendMessage(JSON.stringify(data));
	};

	#runRestAction(signalName, data)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		data.callId = this.call.id;
		data.callInstanceId = this.call.instanceId;
		data.requestId = Util.getUuidv4();
		return CallEngine.getRestClient().callMethod(signalName, data);
	};
}

class Peer
{
	calculatedState: string

	constructor(params)
	{
		this.userId = params.userId;
		this.call = params.call;

		this.ready = !!params.ready;
		this.calling = false;
		this.declined = false;
		this.busy = false;
		this.inviteTimeout = false;
		this.endpoint = null;
		this.direction = params.direction || EndpointDirection.SendRecv;

		this.stream = null;
		this.mediaRenderers = [];

		this.isIncomingVideoAllowed = params.isIncomingVideoAllowed !== false;

		this.callingTimeout = 0;
		this.connectionRestoreTimeout = 0;

		this.callbacks = {
			onStateChanged: Type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onInviteTimeout: Type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
			onMediaReceived: Type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
			onMediaRemoved: Type.isFunction(params.onMediaRemoved) ? params.onMediaRemoved : BX.DoNothing,
			onVoiceStarted: Type.isFunction(params.onVoiceStarted) ? params.onVoiceStarted : BX.DoNothing,
			onVoiceEnded: Type.isFunction(params.onVoiceEnded) ? params.onVoiceEnded : BX.DoNothing,
		};

		this.calculatedState = this.calculateState();
	};

	setReady(ready)
	{
		ready = !!ready;
		if (this.ready == ready)
		{
			return;
		}
		this.ready = ready;
		this.readyStack = (new Error()).stack;
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
			this.inviteTimeout = false;
		}
		if (this.ready)
		{
			this.declined = false;
			this.busy = false;
		}
		else
		{
			clearTimeout(this.connectionRestoreTimeout);
		}

		this.updateCalculatedState();
	}

	setDirection(direction)
	{
		if (this.direction == direction)
		{
			return;
		}
		this.direction = direction;
	}

	setDeclined(declined)
	{
		this.declined = declined;
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		if (this.declined)
		{
			this.ready = false;
			this.busy = false;
		}
		clearTimeout(this.connectionRestoreTimeout);
		this.updateCalculatedState();
	}

	setBusy(busy)
	{
		this.busy = busy;
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		if (this.busy)
		{
			this.ready = false;
			this.declined = false;
		}
		clearTimeout(this.connectionRestoreTimeout);
		this.updateCalculatedState();
	}

	setEndpoint(endpoint)
	{
		this.log("Adding endpoint with " + endpoint.mediaRenderers.length + " media renderers");

		this.setReady(true);
		this.inviteTimeout = false;
		this.declined = false;
		clearTimeout(this.connectionRestoreTimeout);

		if (this.endpoint)
		{
			this.removeEndpointEventHandlers();
			this.endpoint = null;
		}

		this.endpoint = endpoint;

		for (let i = 0; i < this.endpoint.mediaRenderers.length; i++)
		{
			this.addMediaRenderer(this.endpoint.mediaRenderers[i]);
			if (this.endpoint.mediaRenderers[i].element)
			{
				//BX.remove(this.endpoint.mediaRenderers[i].element);
			}
		}

		this.bindEndpointEventHandlers();
	}

	allowIncomingVideo(isIncomingVideoAllowed)
	{
		if (this.isIncomingVideoAllowed == isIncomingVideoAllowed)
		{
			return;
		}

		this.isIncomingVideoAllowed = !!isIncomingVideoAllowed;
	}

	addMediaRenderer(mediaRenderer)
	{
		this.log('Adding media renderer for user' + this.userId, mediaRenderer);

		this.mediaRenderers.push(mediaRenderer);
		this.callbacks.onMediaReceived({
			userId: this.userId,
			kind: mediaRenderer.kind,
			mediaRenderer: mediaRenderer
		});
		this.updateCalculatedState();
	}

	removeMediaRenderer(mediaRenderer)
	{
		this.log('Removing media renderer for user' + this.userId, mediaRenderer);

		let i = this.mediaRenderers.indexOf(mediaRenderer);
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
	}

	bindEndpointEventHandlers()
	{
		this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.#onEndpointRemoteMediaAdded);
		this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.#onEndpointRemoteMediaRemoved);
		this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceStart, this.#onEndpointVoiceStart);
		this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceEnd, this.#onEndpointVoiceEnd);
		this.endpoint.addEventListener(VoxImplant.EndpointEvents.Removed, this.#onEndpointRemoved);
	}

	removeEndpointEventHandlers()
	{
		this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, this.#onEndpointRemoteMediaAdded);
		this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, this.#onEndpointRemoteMediaRemoved);
		this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceStart, this.#onEndpointVoiceStart);
		this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceEnd, this.#onEndpointVoiceEnd);
		this.endpoint.removeEventListener(VoxImplant.EndpointEvents.Removed, this.#onEndpointRemoved);
	}

	calculateState()
	{
		if (this.endpoint)
		{
			return UserState.Connected;
		}

		if (this.calling)
		{
			return UserState.Calling;
		}

		if (this.inviteTimeout)
		{
			return UserState.Unavailable;
		}

		if (this.declined)
		{
			return UserState.Declined;
		}

		if (this.busy)
		{
			return UserState.Busy;
		}

		if (this.ready)
		{
			return UserState.Ready;
		}

		return UserState.Idle;
	}

	updateCalculatedState()
	{
		const calculatedState = this.calculateState();

		if (this.calculatedState != calculatedState)
		{
			this.callbacks.onStateChanged({
				userId: this.userId,
				state: calculatedState,
				previousState: this.calculatedState,
				direction: this.direction,
			});
			this.calculatedState = calculatedState;
		}
	}

	isParticipating()
	{
		return ((this.calling || this.ready || this.endpoint) && !this.declined);
	}

	waitForConnectionRestore()
	{
		clearTimeout(this.connectionRestoreTimeout);
		this.connectionRestoreTimeout = setTimeout(
			this.onConnectionRestoreTimeout.bind(this),
			connectionRestoreTime
		);
	}

	onInvited()
	{
		this.ready = false;
		this.inviteTimeout = false;
		this.declined = false;
		this.calling = true;

		clearTimeout(this.connectionRestoreTimeout);
		if (this.callingTimeout)
		{
			clearTimeout(this.callingTimeout);
		}
		this.callingTimeout = setTimeout(() => this.onInviteTimeout(true), 30000);
		this.updateCalculatedState();
	}

	onInviteTimeout(internal)
	{
		clearTimeout(this.callingTimeout);
		if (!this.calling)
		{
			return;
		}
		this.calling = false;
		this.inviteTimeout = true;
		if (internal)
		{
			this.callbacks.onInviteTimeout({
				userId: this.userId
			});
		}
		this.updateCalculatedState();
	}

	onConnectionRestoreTimeout()
	{
		if (this.endpoint || !this.ready)
		{
			return;
		}

		this.log("Done waiting for connection restoration");
		this.setReady(false);
	}

	#onEndpointRemoteMediaAdded = (e) =>
	{
		this.log("VoxImplant.EndpointEvents.RemoteMediaAdded", e.mediaRenderer);

		// voximplant audio auto-play bug workaround:
		if (e.mediaRenderer.element)
		{
			e.mediaRenderer.element.volume = 0;
			e.mediaRenderer.element.srcObject = null;
		}
		this.addMediaRenderer(e.mediaRenderer);
	}

	#onEndpointRemoteMediaRemoved = (e) =>
	{
		console.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e.mediaRenderer)
		//this.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e);
		this.removeMediaRenderer(e.mediaRenderer);
	}

	#onEndpointVoiceStart = () =>
	{
		this.callbacks.onVoiceStarted();
	}

	#onEndpointVoiceEnd = () =>
	{
		this.callbacks.onVoiceEnded();
	}

	#onEndpointRemoved = (e) =>
	{
		this.log("VoxImplant.EndpointEvents.Removed", e);

		if (this.endpoint)
		{
			this.removeEndpointEventHandlers();
			this.endpoint = null;
		}
		if (this.stream)
		{
			this.stream = null;
		}

		if (this.ready)
		{
			this.waitForConnectionRestore();
		}

		this.updateCalculatedState();
	}

	log()
	{
		this.call.log.apply(this.call, arguments);
	}

	destroy()
	{
		if (this.stream)
		{
			Util.stopMediaStream(this.stream);
			this.stream = null;
		}
		if (this.endpoint)
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
}

const transformVoxStats = function (s, voximplantCall)
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
		const statGroup = s.outbound[trackId];
		for (let i = 0; i < statGroup.length; i++)
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