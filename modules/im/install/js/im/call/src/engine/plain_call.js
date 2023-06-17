import {Browser, Type, Runtime} from 'main.core';

import {AbstractCall} from './abstract_call';
import {CallEngine, CallState, CallEvent, UserState, Provider} from './engine';
import {View} from '../view/view';
import {SimpleVAD} from './simple_vad'
import Util from '../util'

const ajaxActions = {
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

const pullEvents = {
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

const defaultConnectionOptions = {
	offerToReceiveVideo: true,
	offerToReceiveAudio: true
};

const signalingConnectionRefreshPeriod = 30000;
const signalingWaitReplyPeriod = 10000;
//var signalingWaitReplyPeriod = 5000;
const pingPeriod = 5000;
const backendPingPeriod = 25000;
const reinvitePeriod = 5500;

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

export class PlainCall extends AbstractCall
{
	peers: {[key: number]: Peer}

	constructor(params)
	{
		super(params)

		this.callFromMobile = params.callFromMobile;
		this.state = params.state || '';

		this.peers = this.initPeers(this.users);

		this.signaling = new Signaling({
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

		this.turnServer = (Browser.isFirefox() ? BX.message('turn_server_firefox') : BX.message('turn_server')) || 'turn.calls.bitrix24.com';
		this.turnServerLogin = BX.message('turn_server_login') || 'bitrix';
		this.turnServerPassword = BX.message('turn_server_password') || 'bitrix';

		this.pingUsersInterval = setInterval(this.pingUsers.bind(this), pingPeriod);
		this.pingBackendInterval = setInterval(this.pingBackend.bind(this), backendPingPeriod);

		this.reinviteTimeout = null;

		this._onUnloadHandler = this.#onUnload.bind(this);

		this.enableMicAutoParameters = params.enableMicAutoParameters !== false;
		this.microphoneLevelInterval = null;

		window.addEventListener("unload", this._onUnloadHandler);
	};

	get provider()
	{
		return Provider.Plain;
	}

	initPeers(userIds)
	{
		let peers = {};
		for (let i = 0; i < userIds.length; i++)
		{
			const userId = Number(userIds[i]);
			if (userId == this.userId)
			{
				continue;
			}

			peers[userId] = this.createPeer(userId);
		}
		return peers;
	};

	createPeer(userId)
	{
		return new Peer({
			call: this,
			userId: userId,
			ready: userId == this.initiatorId,
			signalingConnected: userId == this.initiatorId,
			isLegacyMobile: userId == this.initiatorId && this.callFromMobile,

			onMediaReceived: (e) =>
			{
				console.log("onMediaReceived: ", e);
				this.runCallback(CallEvent.onRemoteMediaReceived, e);
			},
			onMediaStopped: (e) =>
			{
				this.runCallback(CallEvent.onRemoteMediaStopped, e);
			},
			onStateChanged: this.#onPeerStateChanged.bind(this),
			onInviteTimeout: this.#onPeerInviteTimeout.bind(this),
			onRTCStatsReceived: this.#onPeerRTCStatsReceived.bind(this),
			onNetworkProblem: (e) =>
			{
				this.runCallback(CallEvent.onNetworkProblem, e)
			}
		});
	};

	/**
	 * Returns call participants and their states
	 * @return {object} userId => user state
	 */
	getUsers()
	{
		let result = {};
		for (let userId in this.peers)
		{
			result[userId] = this.peers[userId].calculatedState;
		}
		return result;
	};

	isReady()
	{
		return this.ready;
	};

	setVideoEnabled(videoEnabled: boolean)
	{
		videoEnabled = (videoEnabled === true);
		if (this.videoEnabled == videoEnabled)
		{
			return;
		}

		this.videoEnabled = videoEnabled;
		const hasVideoTracks = this.localStreams['main'] && this.localStreams['main'].getVideoTracks().length > 0;
		if (this.ready && hasVideoTracks !== this.videoEnabled)
		{
			this.replaceLocalMediaStream().then(() =>
			{
				const hasVideoTracks = this.localStreams['main'] && this.localStreams['main'].getVideoTracks().length > 0;
				if (this.videoEnabled && !hasVideoTracks)
				{
					this.videoEnabled = false;
				}
				this.signaling.sendCameraState(this.users, this.videoEnabled);
			}).catch(() =>
			{
				// TODO!!
			});
		}
	};

	setMuted(muted: boolean)
	{
		muted = !!muted;
		if (this.muted == muted)
		{
			return;
		}

		this.muted = muted;
		if (this.localStreams["main"])
		{
			const audioTracks = this.localStreams["main"].getAudioTracks();
			if (audioTracks[0])
			{
				audioTracks[0].enabled = !this.muted;
			}
		}

		this.signaling.sendMicrophoneState(this.users, !this.muted);
		this.sendTalkingState();
	};

	isMuted()
	{
		return this.muted;
	}

	setCameraId(cameraId)
	{
		if (this.cameraId == cameraId)
		{
			return;
		}

		this.cameraId = cameraId;
		if (this.ready && this.videoEnabled)
		{
			Runtime.debounce(this.replaceLocalMediaStream, 100, this)();
		}
	};

	setMicrophoneId(microphoneId)
	{
		if (this.microphoneId == microphoneId)
		{
			return;
		}

		this.microphoneId = microphoneId;
		if (this.ready)
		{
			Runtime.debounce(this.replaceLocalMediaStream, 100, this)();
		}
	};

	getCurrentMicrophoneId()
	{
		if (!this.localStreams['main'])
		{
			return this.microphoneId;
		}

		const audioTracks = this.localStreams['main'].getAudioTracks();
		if (audioTracks.length > 0)
		{
			const audioTrackSettings = audioTracks[0].getSettings();
			return audioTrackSettings.deviceId;
		}
		else
		{
			return this.microphoneId;
		}
	};

	useHdVideo(flag)
	{
		this.videoHd = (flag === true);
	};

	sendRecordState(recordState)
	{
		recordState.senderId = this.userId;

		if (!this.#changeRecordState(recordState))
		{
			return false;
		}

		const users = [this.userId].concat(this.users);
		this.signaling.sendRecordState(users, this.recordState);
	};

	stopSendingStream(tag)
	{
		//todo: implement
	};

	allowVideoFrom(userList)
	{
		//todo: implement
	};

	/**
	 * Invites users to participate in the call.
	 **/
	inviteUsers(config: {users: number[], localStream: MediaStream} = {})
	{
		const users = Type.isArray(config.users) ? config.users : Object.keys(this.peers);
		this.ready = true;

		if (config.localStream instanceof MediaStream && !this.localStreams["main"])
		{
			this.localStreams["main"] = config.localStream;
		}

		this.getLocalMediaStream("main", true).then(() =>
		{
			return this.signaling.inviteUsers({
				userIds: users,
				video: this.videoEnabled ? 'Y' : 'N'
			});

		}).then(() =>
		{
			this.state = CallState.Connected;
			this.runCallback(CallEvent.onJoin, {
				local: true
			});

			for (let i = 0; i < users.length; i++)
			{
				const userId = Number(users[i]);
				if (!this.peers[userId])
				{
					this.peers[userId] = this.createPeer(userId);

					this.runCallback(CallEvent.onUserInvited, {
						userId: userId
					});
				}
				this.peers[userId].onInvited();

				this.scheduleRepeatInvite();
			}
		}).catch((e) =>
		{
			console.error(e);
			this.runCallback(CallEvent.onCallFailure, e);
		});
	};

	scheduleRepeatInvite()
	{
		clearTimeout(this.reinviteTimeout);
		this.reinviteTimeout = setTimeout(this.repeatInviteUsers.bind(this), reinvitePeriod)
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
		this.signaling
			.inviteUsers({
				userIds: usersToRepeatInvite,
				video: this.videoEnabled ? 'Y' : 'N',
				isRepeated: 'Y',
			})
			.then(() => this.scheduleRepeatInvite());
	};

	getMediaConstraints(options = {})
	{
		const audio = {};
		const video = options.videoEnabled ? {} : false;
		const hdVideo = !!options.hdVideo;
		const supportedConstraints = navigator.mediaDevices.getSupportedConstraints ? navigator.mediaDevices.getSupportedConstraints() : {};

		if (this.microphoneId)
		{
			audio.deviceId = {ideal: this.microphoneId};
		}

		if (!this.enableMicAutoParameters)
		{
			if (supportedConstraints.echoCancellation)
			{
				audio.echoCancellation = false;
			}
			if (supportedConstraints.noiseSuppression)
			{
				audio.noiseSuppression = false;
			}
			if (supportedConstraints.autoGainControl)
			{
				audio.autoGainControl = false;
			}
		}

		if (video)
		{
			//video.aspectRatio = 1.7777777778;
			if (this.cameraId)
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
	getUserMedia(constraintsArray)
	{
		return new Promise(function (resolve, reject)
		{
			const currentConstraints = constraintsArray[0];
			navigator.mediaDevices.getUserMedia(currentConstraints).then(
				function (stream)
				{
					resolve(stream);
				},
				function (error)
				{
					this.log("getUserMedia error: ", error);
					this.log("Current constraints", currentConstraints);
					if (constraintsArray.length > 1)
					{
						this.getUserMedia(constraintsArray.slice(1)).then(
							function (stream)
							{
								resolve(stream);
							},
							function (error)
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

	getLocalMediaStream(tag, fallbackToAudio)
	{
		if (!Type.isStringFilled(tag))
		{
			tag = 'main';
		}
		if (this.localStreams[tag])
		{
			return Promise.resolve(this.localStreams[tag]);
		}

		this.log("Requesting access to media devices");

		return new Promise((resolve, reject) =>
		{
			let constraintsArray = [];
			if (this.videoEnabled)
			{
				if (this.videoHd)
				{
					constraintsArray.push(this.getMediaConstraints({videoEnabled: true, hdVideo: true}));
				}
				constraintsArray.push(this.getMediaConstraints({videoEnabled: true, hdVideo: false}));
				if (fallbackToAudio)
				{
					constraintsArray.push(this.getMediaConstraints({videoEnabled: false}));
				}
			}
			else
			{
				constraintsArray.push(this.getMediaConstraints({videoEnabled: false}));
			}

			this.getUserMedia(constraintsArray).then((stream) =>
			{
				this.log("Local media stream received");
				this.localStreams[tag] = stream;
				this.runCallback(CallEvent.onLocalMediaReceived, {
					tag: tag,
					stream: stream
				});
				if (tag === 'main')
				{
					this.attachVoiceDetection();
					if (this.muted)
					{
						const audioTracks = stream.getAudioTracks();
						if (audioTracks[0])
						{
							audioTracks[0].enabled = false;
						}
					}
				}
				if (this.deviceList.length === 0)
				{
					navigator.mediaDevices.enumerateDevices().then((deviceList) =>
					{
						this.deviceList = deviceList;
						this.runCallback(CallEvent.onDeviceListUpdated, {
							deviceList: this.deviceList
						})
					});
				}

				resolve(this.localStreams[tag]);
			}).catch((e) =>
			{
				this.log("Could not get local media stream.", e);
				this.log("Request constraints: .", constraintsArray);
				this.runCallback("onLocalMediaError", {
					tag: tag,
					error: e
				});
				reject(e);
			});
		})
	};

	startMediaCapture()
	{
		return this.getLocalMediaStream();
	};

	attachVoiceDetection()
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
			this.voiceDetection = new SimpleVAD({
				mediaStream: this.localStreams['main'],
				onVoiceStarted: this.onLocalVoiceStarted.bind(this),
				onVoiceStopped: this.onLocalVoiceStopped.bind(this)
			})

			this.microphoneLevelInterval = setInterval(function ()
			{
				this.microphoneLevel = this.voiceDetection.currentVolume;
			}.bind(this), 200)
		} catch (e)
		{
			this.log('Could not attach voice detection to media stream');
		}
	};

	getDisplayMedia()
	{
		return new Promise(function (resolve, reject)
		{
			if (window["BXDesktopSystem"])
			{
				navigator.mediaDevices.getUserMedia({
					video: {
						mandatory: {
							chromeMediaSource: 'screen',
							maxWidth: 1920,
							maxHeight: 1080,
							maxFrameRate: 5
						}
					}
				}).then(
					function (stream)
					{
						resolve(stream);
					},
					function (error)
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
					function (stream)
					{
						resolve(stream)
					},
					function (error)
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

	startScreenSharing(changeSource)
	{
		changeSource = !!changeSource;
		if (this.localStreams["screen"] && !changeSource)
		{
			return;
		}

		this.getDisplayMedia().then((stream) =>
		{
			this.localStreams["screen"] = stream;

			stream.getVideoTracks().forEach((track) =>
			{
				track.addEventListener("ended", () => this.stopScreenSharing())
			});

			this.runCallback(CallEvent.onUserScreenState, {
				userId: this.userId,
				screenState: true,
			});

			if (this.ready)
			{
				for (let userId in this.peers)
				{
					if (this.peers[userId].calculatedState === UserState.Connected)
					{
						this.peers[userId].sendMedia();
					}
				}
			}

		}).catch((e) =>
		{
			this.log(e);
		});
	};

	stopScreenSharing()
	{
		if (!this.localStreams["screen"])
		{
			return;
		}

		Util.stopMediaStream(this.localStreams["screen"])
		this.localStreams["screen"] = null;
		this.runCallback(CallEvent.onUserScreenState, {
			userId: this.userId,
			screenState: false,
		});

		for (let userId in this.peers)
		{
			if (this.peers[userId].calculatedState === UserState.Connected)
			{
				this.peers[userId].sendMedia();
			}
		}
	};

	isScreenSharingStarted()
	{
		return this.localStreams["screen"] instanceof MediaStream;
	};

	onLocalVoiceStarted()
	{
		this.talking = true;
		this.sendTalkingState();
	};

	onLocalVoiceStopped()
	{
		this.talking = false;
		this.sendTalkingState();
	};

	sendTalkingState()
	{
		if (this.talking && !this.muted)
		{
			this.runCallback(CallEvent.onUserVoiceStarted, {
				userId: this.userId,
				local: true
			});
			this.signaling.sendVoiceStarted({
				userId: this.users
			});
		}
		else
		{
			this.runCallback(CallEvent.onUserVoiceStopped, {
				userId: this.userId,
				local: true
			});
			this.signaling.sendVoiceStopped({
				userId: this.users
			});
		}
	}

	sendCustomMessage(message)
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
	answer(config)
	{
		if (!Type.isPlainObject(config))
		{
			config = {};
		}
		/*if(this.direction !== Direction.Incoming)
		{
			throw new Error('Only incoming call could be answered');
		}*/

		this.ready = true;
		this.videoEnabled = (config.useVideo === true);
		this.enableMicAutoParameters = (config.enableMicAutoParameters !== false);

		if (config.localStream instanceof MediaStream)
		{
			this.localStreams["main"] = config.localStream;
		}

		return new Promise((resolve, reject) =>
		{
			this.getLocalMediaStream("main", true)
				.then(() =>
					{
						this.state = CallState.Connected;

						this.runCallback(CallEvent.onJoin, {
							local: true
						});
						return this.sendAnswer();
					}
				)
				.then(() => resolve())
				.catch((e) =>
				{
					this.runCallback(CallEvent.onCallFailure, e);
					reject(e)
				});
		});
	};

	sendAnswer()
	{
		this.signaling.sendAnswer();
	};

	decline(code, reason)
	{
		this.ready = false;

		let data = {
			callId: this.id,
			callInstanceId: this.instanceId,
		};

		if (typeof (code) != 'undefined')
		{
			data.code = code;
		}
		if (typeof (reason) != 'undefined')
		{
			data.reason = reason;
		}

		CallEngine.getRestClient().callMethod(ajaxActions.decline, data).then(() =>
		{
			this.destroy();
		});
	};

	hangup()
	{
		if (!this.ready)
		{
			const error = new Error("Hangup in wrong state");
			this.log(error);
			return Promise.reject(error);
		}

		const tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Hangup received \n" + tempError.stack);

		this.ready = false;
		this.state = CallState.Proceeding;

		return new Promise((resolve, reject) =>
		{
			for (let userId in this.peers)
			{
				this.peers[userId].disconnect();
			}
			this.runCallback(CallEvent.onLeave, {local: true});

			this.signaling.sendHangup({userId: this.users})
				.then(() => resolve())
				.catch(e => reject(e))
			;
		});
	};

	pingUsers()
	{
		if (this.ready)
		{
			this.signaling.sendPingToUsers({userId: this.users.concat(this.userId)});
		}
	};

	pingBackend()
	{
		if (this.ready)
		{
			this.signaling.sendPingToBackend();
		}
	};

	getState()
	{

	};

	replaceLocalMediaStream(tag: string = "main")
	{
		if (this.localStreams[tag])
		{
			Util.stopMediaStream(this.localStreams[tag]);
			this.localStreams[tag] = null;
		}

		return new Promise((resolve, reject) =>
		{
			this.getLocalMediaStream(tag).then(() =>
			{
				if (this.ready)
				{
					for (let userId in this.peers)
					{
						if (this.peers[userId].isReady())
						{
							this.peers[userId].replaceMediaStream(tag);
						}
					}
				}
				resolve();
			}).catch((e) =>
			{
				console.error('Could not get access to hardware; don\'t really know what to do. error:', e);
				reject(e);
			});
		})
	};

	sendAllStreams(userId)
	{
		if (!this.peers[userId])
		{
			return;
		}

		if (!this.peers[userId].isReady())
		{
			return;
		}

		for (let tag in this.localStreams)
		{
			if (this.localStreams[tag])
			{
				this.peers[userId].sendMedia();
			}
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

	/**
	 * Adds users, invited by you or someone else
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
					this.peers[userId].onInvited();
				}
			}
			else
			{
				this.peers[userId] = this.createPeer(userId);
				this.runCallback(CallEvent.onUserInvited, {
					userId: userId
				});

				this.peers[userId].onInvited();
			}
			if (!this.users.includes(userId))
			{
				this.users.push(userId);
			}
		}
	};

	#changeRecordState(params)
	{
		if (params.action !== View.RecordState.Started && this.recordState.userId != params.senderId)
		{
			return false;
		}

		if (params.action === View.RecordState.Started)
		{
			if (this.recordState.state !== View.RecordState.Stopped)
			{
				return false;
			}

			this.recordState.state = View.RecordState.Started;
			this.recordState.userId = params.senderId;
			this.recordState.date.start = params.date;
			this.recordState.date.pause = [];
		}
		else if (params.action === View.RecordState.Paused)
		{
			if (this.recordState.state !== View.RecordState.Started)
			{
				return false;
			}

			this.recordState.state = View.RecordState.Paused;
			this.recordState.date.pause.push(
				{start: params.date, finish: null}
			);
		}
		else if (params.action === View.RecordState.Resumed)
		{
			if (this.recordState.state !== View.RecordState.Paused)
			{
				return false;
			}

			this.recordState.state = View.RecordState.Started;
			const pauseElement = this.recordState.date.pause.find(function (element)
			{
				return element.finish === null;
			});
			if (pauseElement)
			{
				pauseElement.finish = params.date;
			}
		}
		else if (params.action === View.RecordState.Stopped)
		{
			this.recordState.state = View.RecordState.Stopped;
			this.recordState.userId = 0;
			this.recordState.date.start = null;
			this.recordState.date.pause = [];
		}

		return true;
	}

	__onPullEvent(command, params, extra)
	{
		const handlers = {
			'Call::answer': this.#onPullEventAnswer.bind(this),
			'Call::hangup': this.#onPullEventHangup.bind(this),
			'Call::ping': this.#onPullEventPing.bind(this),
			'Call::negotiationNeeded': this.#onPullEventNegotiationNeeded.bind(this),
			'Call::connectionOffer': this.#onPullEventConnectionOffer.bind(this),
			'Call::connectionAnswer': this.#onPullEventConnectionAnswer.bind(this),
			'Call::iceCandidate': this.#onPullEventIceCandidate.bind(this),
			'Call::voiceStarted': this.#onPullEventVoiceStarted.bind(this),
			'Call::voiceStopped': this.#onPullEventVoiceStopped.bind(this),
			'Call::microphoneState': this.#onPullEventMicrophoneState.bind(this),
			'Call::cameraState': this.#onPullEventCameraState.bind(this),
			'Call::videoPaused': this.#onPullEventVideoPaused.bind(this),
			'Call::recordState': this.#onPullEventRecordState.bind(this),
			'Call::usersJoined': this.#onPullEventUsersJoined.bind(this),
			'Call::usersInvited': this.#onPullEventUsersInvited.bind(this),
			'Call::userInviteTimeout': this.#onPullEventUserInviteTimeout.bind(this),
			'Call::associatedEntityReplaced': this.#onPullEventAssociatedEntityReplaced.bind(this),
			'Call::finish': this.#onPullEventFinish.bind(this),
			'Call::repeatAnswer': this.#onPullEventRepeatAnswer.bind(this),
			'Call::customMessage': this.#onPullEventCallCustomMessage.bind(this),
		};

		if (handlers[command])
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

	#onPullEventUsersJoined(params)
	{
		if (!this.ready)
		{
			return;
		}
		const users = params.users;

		this.addJoinedUsers(users);
	};

	#onPullEventUsersInvited(params)
	{
		if (!this.ready)
		{
			return;
		}
		const users = params.users;

		this.addInvitedUsers(users);
	};

	#onPullEventUserInviteTimeout(params)
	{
		this.log('__onPullEventUserInviteTimeout', params);
		const failedUserId = params.failedUserId;

		if (this.peers[failedUserId])
		{
			this.peers[failedUserId].onInviteTimeout(false);
		}
	};

	#onPullEventAnswer(params)
	{
		const senderId = Number(params.senderId);

		if (senderId == this.userId)
		{
			return this.#onPullEventAnswerSelf(params);
		}

		if (!this.ready)
		{
			return;
		}

		if (!this.peers[senderId])
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
		if (this.ready)
		{
			this.sendAllStreams(senderId);
		}
	};

	#onPullEventAnswerSelf(params)
	{
		if (params.callInstanceId === this.instanceId)
		{
			return;
		}

		if (this.ready)
		{
			this.log("Received remote self-answer in ready state, ignoring");
			return;
		}

		// call was answered elsewhere
		this.log("Call was answered elsewhere");
		this.runCallback(CallEvent.onJoin, {
			local: false
		});
	};

	#onPullEventHangup(params)
	{
		const senderId = params.senderId;

		if (this.userId == senderId)
		{
			if (this.instanceId != params.callInstanceId)
			{
				// self hangup elsewhere
				this.runCallback(CallEvent.onLeave, {local: false});
			}
			return;
		}

		if (!this.peers[senderId])
		{
			return;
		}

		this.peers[senderId].disconnect(params.code);
		this.peers[senderId].setReady(false);

		if (params.code == 603)
		{
			this.peers[senderId].setDeclined(true);
		}

		if (!this.isAnyoneParticipating())
		{
			this.hangup();
		}
	};

	#onPullEventPing(params)
	{
		if (params.callInstanceId == this.instanceId)
		{
			// ignore self ping
			return;
		}

		const peer = this.peers[params.senderId];
		if (!peer)
		{
			return;
		}

		peer.setSignalingConnected(true);
	};

	#onPullEventNegotiationNeeded(params)
	{
		if (!this.ready)
		{
			return;
		}
		let peer: Peer = this.peers[params.senderId];
		if (!peer)
		{
			return;
		}

		peer.setReady(true);
		if (params.restart)
		{
			peer.reconnect()
		}
		else
		{
			peer.onNegotiationNeeded();
		}
	};

	#onPullEventConnectionOffer(params)
	{
		if (!this.ready)
		{
			return;
		}
		const peer = this.peers[params.senderId];
		if (!peer)
		{
			return;
		}

		peer.setReady(true);
		peer.setUserAgent(params.userAgent);
		peer.setConnectionOffer(params.connectionId, params.sdp, params.tracks);
	};

	#onPullEventConnectionAnswer(params)
	{
		if (!this.ready)
		{
			return;
		}
		const peer = this.peers[params.senderId];
		if (!peer)
		{
			return;
		}

		const connectionId = params.connectionId;

		peer.setUserAgent(params.userAgent);
		peer.setConnectionAnswer(connectionId, params.sdp, params.tracks);
	};

	#onPullEventIceCandidate(params)
	{
		if (!this.ready)
		{
			return;
		}
		const peer = this.peers[params.senderId];
		let candidates;
		if (!peer)
		{
			return;
		}

		try
		{
			candidates = params.candidates;
			for (let i = 0; i < candidates.length; i++)
			{
				peer.addIceCandidate(params.connectionId, candidates[i]);
			}
		} catch (e)
		{
			this.log('Error parsing serialized candidate: ', e);
		}
	};

	#onPullEventVoiceStarted(params)
	{
		this.runCallback(CallEvent.onUserVoiceStarted, {
			userId: params.senderId
		})
	};

	#onPullEventVoiceStopped(params)
	{
		this.runCallback(CallEvent.onUserVoiceStopped, {
			userId: params.senderId
		})
	};

	#onPullEventMicrophoneState(params)
	{
		this.runCallback(CallEvent.onUserMicrophoneState, {
			userId: params.senderId,
			microphoneState: params.microphoneState
		})
	};

	#onPullEventCameraState(params)
	{
		this.runCallback(CallEvent.onUserCameraState, {
			userId: params.senderId,
			cameraState: params.cameraState
		})
	};

	#onPullEventVideoPaused(params)
	{
		const peer = this.peers[params.senderId];
		if (!peer)
		{
			return;
		}

		this.runCallback(CallEvent.onUserVideoPaused, {
			userId: params.senderId,
			videoPaused: params.videoPaused
		});

		peer.holdOutgoingVideo(!!params.videoPaused);
	};

	#onPullEventRecordState(params)
	{
		this.runCallback(CallEvent.onUserRecordState, {
			userId: params.senderId,
			recordState: params.recordState
		})
	};

	#onPullEventAssociatedEntityReplaced(params)
	{
		if (params.call && params.call.ASSOCIATED_ENTITY)
		{
			this.associatedEntity = params.call.ASSOCIATED_ENTITY;
		}
	};

	#onPullEventFinish()
	{
		this.destroy();
	};

	#onPullEventRepeatAnswer()
	{
		if (this.ready)
		{
			this.signaling.sendAnswer({userId: this.userId}, true);
		}
	};

	#onPullEventCallCustomMessage(params)
	{
		this.runCallback(CallEvent.onCustomMessage, {message: params.message});
	}

	#onPeerStateChanged(e)
	{
		this.runCallback(CallEvent.onUserStateChanged, e);

		if (e.state == UserState.Failed || e.state == UserState.Unavailable)
		{
			if (!this.isAnyoneParticipating())
			{
				this.hangup().then(this.destroy.bind(this)).catch(() =>
					{
						//this.runCallback(Event.onCallFailure, e);
						this.destroy();
					}
				);
			}
		}
		else if (e.state == UserState.Connected)
		{
			this.signaling.sendMicrophoneState(e.userId, !this.muted);
			this.signaling.sendCameraState(e.userId, this.videoEnabled);
			this.wasConnected = true;
		}
	};

	#onPeerInviteTimeout(e)
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

	#onPeerRTCStatsReceived(e)
	{
		this.runCallback(CallEvent.onRTCStatsReceived, e);
	};

	#onUnload()
	{
		if (!this.ready)
		{
			return;
		}
		CallEngine.getRestClient().callMethod(ajaxActions.hangup, {
			callId: this.id,
			callInstanceId: this.instanceId
		});

		for (let userId in this.peers)
		{
			this.peers[userId].disconnect();
		}
	};

	destroy()
	{
		const tempError = new Error();
		tempError.name = "Call stack:";
		this.log("Call destroy \n" + tempError.stack);

		// stop sending media streams
		for (let userId in this.peers)
		{
			if (this.peers[userId])
			{
				this.peers[userId].destroy();
			}
		}
		// stop media streams
		for (let tag in this.localStreams)
		{
			if (this.localStreams[tag])
			{
				Util.stopMediaStream(this.localStreams[tag]);
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

		return super.destroy();
	}
}

class Signaling
{
	constructor(params)
	{
		this.call = params.call;
	};

	isIceTricklingAllowed()
	{
		return CallEngine.getPullClient().isPublishingSupported();
	};

	inviteUsers(data)
	{
		return this.#runRestAction(ajaxActions.invite, data);
	};

	sendAnswer(data, repeated)
	{
		if (repeated && CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.answer, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.answer, data);
		}
	};

	sendConnectionOffer(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.connectionOffer, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.connectionOffer, data);
		}
	};

	sendConnectionAnswer(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.connectionAnswer, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.connectionAnswer, data);
		}
	};

	sendIceCandidate(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.iceCandidate, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.iceCandidate, data);
		}
	};

	sendNegotiationNeeded(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.negotiationNeeded, data);
		}
		else
		{
			return this.#runRestAction(ajaxActions.negotiationNeeded, data);
		}
	};

	sendVoiceStarted(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.voiceStarted, data);
		}
	};

	sendVoiceStopped(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.voiceStopped, data);
		}
	};

	sendMicrophoneState(users, microphoneState)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.microphoneState, {
				userId: users,
				microphoneState: microphoneState
			}, 0);
		}
	};

	sendCameraState(users, cameraState)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.cameraState, {
				userId: users,
				cameraState: cameraState
			}, 0);
		}
	};

	sendRecordState(users, recordState)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			return this.#sendPullEvent(pullEvents.recordState, {
				userId: users,
				recordState: recordState
			}, 0);
		}
	};

	sendPingToUsers(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.ping, data, 5);
		}
	};

	sendCustomMessage(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.customMessage, data, 5);
		}
	};

	sendPingToBackend()
	{
		const retransmit = !CallEngine.getPullClient().isPublishingEnabled();
		this.#runRestAction(ajaxActions.ping, {retransmit: retransmit});
	};

	sendUserInviteTimeout(data)
	{
		if (CallEngine.getPullClient().isPublishingEnabled())
		{
			this.#sendPullEvent(pullEvents.userInviteTimeout, data, 0);
		}
	};

	sendHangup(data)
	{
		if (CallEngine.getPullClient().isPublishingSupported())
		{
			this.#sendPullEvent(pullEvents.hangup, data, 3600);
			data.retransmit = false;
			return this.#runRestAction(ajaxActions.hangup, data);
		}
		else
		{
			data.retransmit = true;
			return this.#runRestAction(ajaxActions.hangup, data);
		}
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
		data.callInstanceId = this.call.instanceId;
		data.senderId = this.call.userId;
		data.callId = this.call.id;
		data.requestId = Util.getUuidv4();

		if (eventName == 'Call::ping')
		{
			this.call.log('Sending p2p signaling event ' + eventName);
		}
		else
		{
			this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
		}
		CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
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

		if (signalName == 'Call::ping')
		{
			this.call.log('Sending ajax-based signaling event ' + signalName);
		}
		else
		{
			this.call.log('Sending ajax-based signaling event ' + signalName + '; ' + JSON.stringify(data));
		}
		return CallEngine.getRestClient().callMethod(signalName, data).catch(function (e) {console.error(e)});
	};
}

class Peer
{
	call: PlainCall
	peerConnection: ?RTCPeerConnection
	peerConnectionId: ?string
	videoSender: ?RTCRtpSender
	audioSender: ?RTCRtpSender
	screenSender: ?RTCRtpSender
	calculatedState: string

	constructor(params)
	{
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

		this.localStreams = {
			main: null,
			screen: null
		};

		this.pendingIceCandidates = {};
		this.localIceCandidates = [];

		this.trackList = {};

		this.callbacks = {
			onStateChanged: Type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
			onInviteTimeout: Type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
			onMediaReceived: Type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
			onMediaStopped: Type.isFunction(params.onMediaStopped) ? params.onMediaStopped : BX.DoNothing,
			onRTCStatsReceived: Type.isFunction(params.onRTCStatsReceived) ? params.onRTCStatsReceived : BX.DoNothing,
			onNetworkProblem: Type.isFunction(params.onNetworkProblem) ? params.onNetworkProblem : BX.DoNothing,
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
			get: function ()
			{
				return this._outgoingVideoTrack;
			},
			set: function (track)
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
			get: function ()
			{
				return this._outgoingScreenTrack;
			},
			set: function (track)
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
		this._onPeerConnectionIceConnectionStateChangeHandler = this.#onPeerConnectionIceConnectionStateChange.bind(this);
		this._onPeerConnectionIceGatheringStateChangeHandler = this.#onPeerConnectionIceGatheringStateChange.bind(this);
		this._onPeerConnectionSignalingStateChangeHandler = this.#onPeerConnectionSignalingStateChange.bind(this);
		//this._onPeerConnectionNegotiationNeededHandler = this._onPeerConnectionNegotiationNeeded.bind(this);
		this._onPeerConnectionTrackHandler = this.#onPeerConnectionTrack.bind(this);
		this._onPeerConnectionRemoveStreamHandler = this.#onPeerConnectionRemoveStream.bind(this);

		this._updateTracksDebounced = Runtime.debounce(this.#updateTracks.bind(this), 50);

		this._waitTurnCandidatesTimeout = null;
	};

	_mediaGetter(trackVariable)
	{
		return function ()
		{
			return this[trackVariable]
		}.bind(this)
	};

	_mediaSetter(trackVariable, kind)
	{
		return function (track)
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

	sendMedia(skipOffer)
	{
		if (!this.peerConnection)
		{
			if (!this.isInitiator())
			{
				this.log('waiting for the other side to send connection offer');
				this.sendNegotiationNeeded(false);
				return;
			}
		}

		if (!this.peerConnection)
		{
			const connectionId = Util.getUuidv4();
			this.#createPeerConnection(connectionId);
		}
		this.updateOutgoingTracks();
		this.applyResolutionScale();

		if (!skipOffer)
		{
			this.createAndSendOffer();
		}
	};

	updateOutgoingTracks()
	{
		if (!this.peerConnection)
		{
			return;
		}

		let audioTrack;
		let videoTrack;
		let screenTrack;

		if (this.call.localStreams["main"] && this.call.localStreams["main"].getAudioTracks().length > 0)
		{
			audioTrack = this.call.localStreams["main"].getAudioTracks()[0];
		}
		if (this.call.localStreams["screen"] && this.call.localStreams["screen"].getVideoTracks().length > 0)
		{
			screenTrack = this.call.localStreams["screen"].getVideoTracks()[0];
		}
		if (this.call.localStreams["main"] && this.call.localStreams["main"].getVideoTracks().length > 0)
		{
			videoTrack = this.call.localStreams["main"].getVideoTracks()[0];
		}

		this.outgoingVideoTrack = videoTrack ? videoTrack.clone() : null;
		this.outgoingScreenTrack = screenTrack ? screenTrack.clone() : null;

		let tracksToSend = [];
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

	getSenderMid(rtpSender: RTCRtpSender): string
	{
		if (rtpSender === null || !this.peerConnection)
		{
			return null;
		}
		const transceiver = this.peerConnection.getTransceivers().find(transceiver => transceiver.sender == rtpSender);
		return transceiver ? transceiver.mid : null;
	};

	applyResolutionScale(factor)
	{
		if (!this.videoSender)
		{
			return;
		}

		const scaleFactor = factor || (this.screenSender ? 4 : 1);

		const params = this.videoSender.getParameters();
		if (params.encodings && params.encodings.length > 0)
		{
			params.encodings[0].scaleResolutionDownBy = scaleFactor;
			//params.encodings[0].maxBitrate = rate;
			this.videoSender.setParameters(params);
		}
	};

	replaceMediaStream(tag: string)
	{
		if (this.isRenegotiationSupported())
		{
			this.sendMedia();
		}
		else
		{
			this.localStreams[tag] = this.call.getLocalStream(tag);
			this.reconnect();
		}
	};

	holdOutgoingVideo(holdState)
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

	isInitiator()
	{
		return this.call.userId < this.userId;
	};

	isRenegotiationSupported()
	{
		return true;
		// return (Browser.isChrome() && this.isChrome);
	};

	setReady(ready)
	{
		this.ready = ready;
		if (this.ready)
		{
			this.declined = false;
			this.busy = false;
		}
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	isReady()
	{
		return this.ready;
	};

	onInvited()
	{
		this.ready = false;
		this.inviteTimeout = false;
		this.declined = false;
		this.calling = true;

		if (this.callingTimeout)
		{
			clearTimeout(this.callingTimeout);
		}
		this.callingTimeout = setTimeout(function ()
		{
			this.onInviteTimeout(true);
		}.bind(this), 30000);
		this.updateCalculatedState();
	};

	onInviteTimeout(internal)
	{
		clearTimeout(this.callingTimeout);
		this.calling = false;
		this.inviteTimeout = true;
		if (internal)
		{
			this.callbacks.onInviteTimeout({
				userId: this.userId
			});
		}
		this.updateCalculatedState();
	};

	setUserAgent(userAgent)
	{
		this.userAgent = userAgent;
		this.isFirefox = userAgent.toLowerCase().indexOf('firefox') != -1;
		this.isChrome = userAgent.toLowerCase().indexOf('chrome') != -1;
		this.isLegacyMobile = userAgent === 'Bitrix Legacy Mobile';
	};

	getUserAgent()
	{
		return this.userAgent;
	};

	isParticipating()
	{
		if (this.calling)
		{
			return true;
		}

		if (this.declined || this.busy)
		{
			return false;
		}

		if (this.peerConnection)
		{
			// todo: maybe we should check iceConnectionState as well.
			const iceConnectionState = this.peerConnection.iceConnectionState;
			if (iceConnectionState == 'checking' || iceConnectionState == 'connected' || iceConnectionState == 'completed')
			{
				return true;
			}
		}

		return false;
	};

	setSignalingConnected(signalingConnected)
	{
		this.signalingConnected = signalingConnected;
		this.updateCalculatedState();

		if (this.signalingConnected)
		{
			this.refreshSignalingTimeout();
		}
		else
		{
			this.stopSignalingTimeout();
		}
	};

	isSignalingConnected()
	{
		return this.signalingConnected;
	};

	setDeclined(declined)
	{
		this.declined = declined;
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	setBusy(busy)
	{
		this.busy = busy;
		if (this.calling)
		{
			clearTimeout(this.callingTimeout);
			this.calling = false;
		}
		this.updateCalculatedState();
	};

	isDeclined()
	{
		return this.declined;
	};

	updateCalculatedState()
	{
		const calculatedState = this.calculateState();

		if (this.calculatedState != calculatedState)
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

	calculateState()
	{
		if (this.peerConnection)
		{
			if (this.failureReason !== '')
			{
				return UserState.Failed;
			}

			if (this.peerConnection.iceConnectionState === 'connected' || this.peerConnection.iceConnectionState === 'completed')
			{
				return UserState.Connected;
			}

			return UserState.Connecting;
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
	};

	getSignaling()
	{
		return this.call.signaling;
	};

	startStatisticsGathering()
	{
		clearInterval(this.statsInterval);

		this.statsInterval = setInterval(function ()
		{
			if (!this.peerConnection)
			{
				return false;
			}

			this.peerConnection.getStats().then(function (stats)
			{
				this.callbacks.onRTCStatsReceived({
					userId: this.userId,
					stats: stats
				});
			}.bind(this));
		}.bind(this), 1000);
	};

	stopStatisticsGathering()
	{
		clearInterval(this.statsInterval);
		this.statsInterval = null;
	};

	updateCandidatesTimeout()
	{
		if (this.candidatesTimeout)
		{
			clearTimeout(this.candidatesTimeout);
		}

		this.candidatesTimeout = setTimeout(this.sendIceCandidates.bind(this), 500);
	};

	sendIceCandidates()
	{
		this.log("User " + this.userId + ": sending ICE candidates due to the timeout");

		this.candidatesTimeout = null;
		if (this.localIceCandidates.length > 0)
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
			this.log("User " + this.userId + ": ICE candidates pool is empty");
		}
	};

	#createPeerConnection(id)
	{
		this.log("User " + this.userId + ": Creating peer connection");
		const connectionConfig = {
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

	_destroyPeerConnection()
	{
		if (!this.peerConnection)
		{
			return;
		}

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
		if (this.pendingIceCandidates[this.peerConnectionId])
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

	_onPeerConnectionIceCandidate(e)
	{
		const candidate = e.candidate;
		this.log("User " + this.userId + ": ICE candidate discovered. Candidate: " + (candidate ? candidate.candidate : candidate));

		if (candidate)
		{
			if (this.getSignaling().isIceTricklingAllowed())
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

			const match = candidate.candidate.match(/typ\s(\w+)?/);
			if (match)
			{
				const type = match[1];
				if (type == "srflx")
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

	#onPeerConnectionIceConnectionStateChange()
	{
		this.log("User " + this.userId + ": ICE connection state changed. New state: " + this.peerConnection.iceConnectionState);

		if (this.peerConnection.iceConnectionState === "connected" || this.peerConnection.iceConnectionState === "completed")
		{
			this.connectionAttempt = 0;
			clearTimeout(this.reconnectAfterDisconnectTimeout);
			this._updateTracksDebounced();
		}
		else if (this.peerConnection.iceConnectionState === "failed")
		{
			this.log("ICE connection failed. Trying to restore connection immediately");
			this.reconnect();
		}
		else if (this.peerConnection.iceConnectionState === "disconnected")
		{
			this.log("ICE connection lost. Waiting 5 seconds before trying to restore it");
			clearTimeout(this.reconnectAfterDisconnectTimeout);
			this.reconnectAfterDisconnectTimeout = setTimeout(() => this.reconnect(), 5000);
		}

		this.updateCalculatedState();
	};

	#onPeerConnectionIceGatheringStateChange(e)
	{
		const connection = e.target;
		this.log("User " + this.userId + ": ICE gathering state changed to : " + connection.iceGatheringState);

		if (connection.iceGatheringState === 'complete')
		{
			this.log("User " + this.userId + ": ICE gathering complete");
			if (!this.hasStun || !this.hasTurn)
			{
				const s = [];
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

			if (!this.getSignaling().isIceTricklingAllowed())
			{
				if (this.localIceCandidates.length > 0)
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
					this.log("User " + this.userId + ": ICE candidates already sent");
				}
			}
		}
	};

	#onPeerConnectionSignalingStateChange()
	{
		this.log("User " + this.userId + " PC signalingState: " + this.peerConnection.signalingState);
		if (this.peerConnection.signalingState === "stable")
		{
			this._updateTracksDebounced();
		}
	};

	// this event is unusable in the current version of desktop (cef 64) and leads to signaling cycling
	// todo: reconsider using it after new version is released
	#onPeerConnectionNegotiationNeeded(e)
	{
		this.log("User " + this.userId + ": needed negotiation for peer connection");
		this.log("signaling state: ", e.target.signalingState);
		this.log("ice connection state: ", e.target.iceConnectionState);
		this.log("pendingRemoteDescription: ", e.target.pendingRemoteDescription);

		if (e.target.iceConnectionState !== "new" && e.target.iceConnectionState !== "connected" && e.target.iceConnectionState !== "completed")
		{
			this.log("User " + this.userId + ": wrong connection state");
			return;
		}

		if (this.isInitiator())
		{
			this.createAndSendOffer();
		}
		else
		{
			this.sendNegotiationNeeded(this.peerConnection._forceReconnect === true);
		}
	};

	#onPeerConnectionTrack(e)
	{
		this.log("User " + this.userId + ": media track received: ", e.track.id + " (" + e.track.kind + ")");

		if (e.track.kind === "video")
		{
			e.track.addEventListener("mute", this.#onVideoTrackMuted.bind(this));
			e.track.addEventListener("unmute", this.#onVideoTrackUnMuted.bind(this));
			e.track.addEventListener("ended", this.#onVideoTrackEnded.bind(this));
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

	#onPeerConnectionRemoveStream(e)
	{
		this.log("User: " + this.userId + "_onPeerConnectionRemoveStream: ", e);
	};

	#onVideoTrackMuted()
	{
		console.log("Video track muted");
		//this._updateTracksDebounced();
	};

	#onVideoTrackUnMuted()
	{
		console.log("Video track unmuted");
		//this._updateTracksDebounced();
	};

	#onVideoTrackEnded()
	{
		console.log("Video track ended");
	};

	#updateTracks()
	{
		if (!this.peerConnection)
		{
			return null;
		}
		let audioTrack = null;
		let videoTrack = null;
		let screenTrack = null;
		this.peerConnection.getTransceivers().forEach((tr) =>
		{
			this.call.log("[debug] tr direction: " + tr.direction + " currentDirection: " + tr.currentDirection);
			if (tr.currentDirection === "sendrecv" || tr.currentDirection === "recvonly")
			{
				if (tr.receiver && tr.receiver.track)
				{
					const track = tr.receiver.track;
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
		});
		this.incomingAudioTrack = audioTrack;
		this.incomingVideoTrack = videoTrack;
		this.incomingScreenTrack = screenTrack;
	};

	stopSignalingTimeout()
	{
		clearTimeout(this.signalingConnectionTimeout);
	};

	refreshSignalingTimeout()
	{
		clearTimeout(this.signalingConnectionTimeout);
		this.signalingConnectionTimeout = setTimeout(this.#onLostSignalingConnection.bind(this), signalingConnectionRefreshPeriod);
	};

	#onLostSignalingConnection()
	{
		this.setSignalingConnected(false);
	};

	_onConnectionOfferReplyTimeout(connectionId)
	{
		this.log("did not receive connection answer for connection " + connectionId);

		this.reconnect();
	};

	_onNegotiationNeededReplyTimeout()
	{
		this.log("did not receive connection offer in time");

		this.reconnect();
	};

	setConnectionOffer(connectionId, sdp, trackList)
	{
		this.log("User " + this.userId + ": applying connection offer for connection " + connectionId);

		clearTimeout(this.negotiationNeededReplyTimeout);
		this.negotiationNeededReplyTimeout = null;

		if (!this.call.isReady())
		{
			return;
		}

		if (!this.isReady())
		{
			return;
		}

		if (trackList)
		{
			this.trackList = BX.util.array_flip(trackList);
		}

		if (this.peerConnection)
		{
			if (this.peerConnectionId !== connectionId)
			{
				this._destroyPeerConnection();
				this.#createPeerConnection(connectionId);
			}
		}
		else
		{
			this.#createPeerConnection(connectionId);
		}

		this.applyOfferAndSendAnswer(sdp);
	};

	createAndSendOffer(config)
	{
		let connectionConfig = defaultConnectionOptions;
		for (let key in config)
		{
			connectionConfig[key] = config[key];
		}

		this.peerConnection.createOffer(connectionConfig)
			.then((offer) =>
			{
				this.log("User " + this.userId + ": Created connection offer.");
				this.log("Applying local description");
				return this.peerConnection.setLocalDescription(offer);
			})
			.then(() =>
			{
				this.sendOffer();
			})
		;
	};

	sendOffer()
	{
		clearTimeout(this.connectionOfferReplyTimeout);
		this.connectionOfferReplyTimeout = setTimeout(
			() => this._onConnectionOfferReplyTimeout(this.peerConnectionId),
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

	sendNegotiationNeeded(restart: boolean)
	{
		restart = restart === true;
		clearTimeout(this.negotiationNeededReplyTimeout);
		this.negotiationNeededReplyTimeout = setTimeout(
			() => this._onNegotiationNeededReplyTimeout(),
			signalingWaitReplyPeriod
		);

		const params = {
			userId: this.userId
		};
		if (restart)
		{
			params.restart = true;
		}

		this.getSignaling().sendNegotiationNeeded(params);
	};

	applyOfferAndSendAnswer(sdp)
	{
		const sessionDescription = new RTCSessionDescription({
			type: "offer",
			sdp: sdp
		});

		this.log("User: " + this.userId + "; Applying remote offer");
		this.log("User: " + this.userId + "; Peer ice connection state ", this.peerConnection.iceConnectionState);

		this.peerConnection
			.setRemoteDescription(sessionDescription)
			.then(() =>
			{
				if (this.peerConnection.iceConnectionState === 'new')
				{
					this.sendMedia(true);
				}

				return this.peerConnection.createAnswer();
			})
			.then((answer) =>
			{
				this.log("Created connection answer.");
				this.log("Applying local description.");
				return this.peerConnection.setLocalDescription(answer);
			})
			.then(() =>
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
			})
			.catch((e) =>
			{
				this.failureReason = e.toString();
				this.updateCalculatedState();
				this.log("Could not apply remote offer", e);
				console.error("Could not apply remote offer", e);
			})
		;
	};

	setConnectionAnswer(connectionId, sdp, trackList)
	{
		if (!this.peerConnection || this.peerConnectionId != connectionId)
		{
			this.log("Could not apply answer, for unknown connection " + connectionId);
			return;
		}

		if (this.peerConnection.signalingState !== 'have-local-offer')
		{
			this.log("Could not apply answer, wrong peer connection signaling state " + this.peerConnection.signalingState);
			return;
		}

		if (trackList)
		{
			this.trackList = BX.util.array_flip(trackList);
		}

		const sessionDescription = new RTCSessionDescription({
			type: "answer",
			sdp: sdp
		});

		clearTimeout(this.connectionOfferReplyTimeout);

		this.log("User: " + this.userId + "; Applying remote answer");
		this.peerConnection
			.setRemoteDescription(sessionDescription)
			.then(() =>
			{
				this.applyPendingIceCandidates();
			})
			.catch((e) =>
			{
				this.failureReason = e.toString();
				this.updateCalculatedState();
				this.log(e);
			})
		;
	};

	addIceCandidate(connectionId, candidate)
	{
		if (!this.peerConnection)
		{
			return;
		}

		if (this.peerConnectionId != connectionId)
		{
			this.log("Error: Candidate for unknown connection " + connectionId);
			return;
		}

		if (this.peerConnection.remoteDescription && this.peerConnection.remoteDescription.type)
		{
			this.peerConnection
				.addIceCandidate(candidate)
				.then(() =>
				{
					this.log("User: " + this.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
				})
				.catch((e) =>
				{
					this.log(e);
				})
			;
		}
		else
		{
			if (!this.pendingIceCandidates[connectionId])
			{
				this.pendingIceCandidates[connectionId] = [];
			}
			this.pendingIceCandidates[connectionId].push(candidate);
		}
	};

	applyPendingIceCandidates()
	{
		if (!this.peerConnection || !this.peerConnection.remoteDescription.type)
		{
			return;
		}

		if (Type.isArray(this.pendingIceCandidates[this.peerConnectionId]))
		{
			this.pendingIceCandidates[this.peerConnectionId].forEach((candidate) =>
			{
				this.peerConnection.addIceCandidate(candidate).then(() =>
				{
					this.log("User: " + this.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
				});
			});

			this.pendingIceCandidates[this.peerConnectionId] = [];
		}
	};

	onNegotiationNeeded()
	{
		if (this.peerConnection)
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

	reconnect()
	{
		clearTimeout(this.reconnectAfterDisconnectTimeout);

		this.connectionAttempt++;

		if (this.connectionAttempt > 3)
		{
			this.log("Error: Too many reconnection attempts, giving up");
			this.failureReason = "Could not connect to user in time";
			this.updateCalculatedState();
			return;
		}

		this.log("Trying to restore ICE connection. Attempt " + this.connectionAttempt);
		if (this.isInitiator())
		{
			this._destroyPeerConnection();
			this.sendMedia();
		}
		else
		{
			this.sendNegotiationNeeded(true);
		}
	};

	disconnect()
	{
		this._destroyPeerConnection();
	};

	log()
	{
		this.call.log.apply(this.call, arguments);
	};

	destroy()
	{
		this.disconnect();

		if (this.voiceDetection)
		{
			this.voiceDetection.destroy();
			this.voiceDetection = null;
		}

		for (let tag in this.localStreams)
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
}