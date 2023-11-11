export const MediaStreamsKinds = {
	Camera: 1,
	Microphone: 2,
	Screen: 3
}

class Track {
	id = null;
	source = '';
	track = {};

	constructor(id, track) {
		this.id = id;
		this.source = track.source;
		this.track = track
	}
}

class Message {
	text
	from
	timestamp

	constructor(message) {
		this.text = message.message
		this.from = message.senderSid
		this.timestamp = Math.floor(Date.now() / 1000)
	}
}

const STREAM_QUALITY = {
	HIGH: 2,
	MEDIUM: 1,
	LOW: 0,
}

export const CALL_STATE = {
	CONNECTED: 'Connected',
	PROGRESSING: 'Progressing',
	TERMINATED: 'Terminated',
}

export class Call {
	sender = null;
	recipient = null;
	#privateProperties = {
		logs: {},
		isloggingEnable: true,
		loggerCallback: null,
		isWaitAnswer: false,
		prevPacketsLost: {},
		prevPacketsReceived: {},
		prevParticipantsWithLargeDataLoss: new Set(),
		tracksDataFromSocket: {},
		url: null,
		token: null,
		endpoint: null,
		jwt: null,
		options: null,
		iceServers: null,
		socketConnect: null,
		peerConnectionFailed: false,
		pendingCandidates: {
			recipient: [],
			sender: [],
		},
		cameraStream: null,
		microphoneStream: null,
		screenStream: null,
		localTracks: {},
		rtt: 0,
		pingIntervalDuration: 0,
		pingTimeoutDuration: 0,
		remoteTracks: {},
		remoteParticipants: {},
		hasMainStream: false,
		pingPongTimeout: null,
		pingPongInterval: null,
		myUserId: '',
		defaultVideoResolution: {
			width: 1280,
			height: 720
		},
		defaultSimulcastBitrate: {
			q: 120000,
			h: 300000,
			f: 1000000
		},
		defaultRemoteStreamsQuality: STREAM_QUALITY.MEDIUM,
		audioBitrate: 70000,
		videoBitrate: 1500000,
		screenBitrate: 1500000,
		videoSimulcast: true,
		screenSimulcast: false,
		events: new Map(),
		offersStack: 0,
		audioDeviceId: '',
		videoDeviceId: '',
		isReconnecting: false,
		initialReconnectionTries: 5,
		reconnectionTries: 5,
		reconnectionTimeout: 1000,
		callStatsInterval: null,
		callState: '',
		packetLostThreshold: 7,
		statsTimeout: 3000,
	}

	constructor() {
		this.sendLeaveBound = this.#sendLeave.bind(this);
	}

	async connect(options) {
		this.#privateProperties.callState = CALL_STATE.PROGRESSING

		for (let key in options) {
			this.#privateProperties[`${key}`] = options[key]
		}

		if (!this.#privateProperties.endpoint) {
			this.triggerEvents('Failed', [{name: 'AUTHORIZE_ERROR', message: `Missing required param 'endpoint'`}]);
			return;
		}
		if (!this.#privateProperties.jwt) {
			this.triggerEvents('Failed', [{name: 'AUTHORIZE_ERROR', message: `Missing required param 'jwt'`}]);
			return;
		}

		if (!this.#privateProperties.url || !this.#privateProperties.token || !this.#privateProperties.data)
		{
			try
			{
				const mediaServerInfo = await this.getMediaServerInfo();
				if (mediaServerInfo)
				{
					this.#privateProperties.url = mediaServerInfo.url;
					this.#privateProperties.token = mediaServerInfo.token;
					this.#privateProperties.data = mediaServerInfo.data;
				}
				else
				{
					throw new Error('No media server info provided');
				}
			}
			catch (error)
			{
				this.setLog(error);
				this.triggerEvents('Failed', [{name: 'ERROR_UNEXPECTED_ANSWER', message: error.message}]);
				return;
			}
		}

		this.#privateProperties.socketConnect = new WebSocket(`${this.#privateProperties.url}?access_token=${this.#privateProperties.token}&auto_subscribe=1&sdk=js&version=1.6.7&protocol=8&roomData=${this.#privateProperties.data}`);
		this.#privateProperties.socketConnect.onmessage = (e) => this.socketOnMessageHandler(e);
		this.#privateProperties.socketConnect.onopen = () => this.socketOnOpenHandler();
		this.#privateProperties.socketConnect.onerror = () => this.socketOnErrorHandler();
		this.#privateProperties.socketConnect.onclose = (e) => this.socketOnCloseHandler(e);
	};

	#reconnect() {
		this.#privateProperties.isReconnecting = true;

		const reconnect = () => {
			if (this.#privateProperties.reconnectionTries)
			{
				--this.#privateProperties.reconnectionTries;
				setTimeout(this.connect.bind(this), this.#privateProperties.reconnectionTimeout);
			}
			else
			{
				this.hangup();
			}
		};

		reconnect();
		this.triggerEvents('Reconnecting');
	};

	#beforeDisconnect() {
		window.removeEventListener('beforeunload', this.sendLeaveBound);
		this.#clearPingInterval();
		this.#clearPingTimeout();
		clearInterval(this.#privateProperties.callStatsInterval);

		this.#destroyPeerConnection();

		this.#privateProperties.localTracks = {};

		if (this.#privateProperties.socketConnect)
		{
			this.#privateProperties.socketConnect.onmessage = null;
			this.#privateProperties.socketConnect.onopen = null;
			this.#privateProperties.socketConnect.onerror = null;
			this.#privateProperties.socketConnect.onclose = null;
			this.#privateProperties.socketConnect.close();
			this.#privateProperties.socketConnect = null;
		}
	}

	async getMediaServerInfo() {
		const url = `${this.#privateProperties.endpoint}/join?token=${this.#privateProperties.jwt}`;

		try {
			const response = await fetch(url, {
				method: 'GET',
			});

			const data = await response.json();
			return {
				url: data.result.mediaServerUrl,
				token: data.result.tokenToAccessMediaServer,
				data: data.result.roomData,
			};
		} catch (error) {
			this.setLog(error)
			console.error('mediaserver error:', error);
			return null;
		}
	}
	async sendOffer() {
		if (this.#privateProperties.offersStack > 0 && !this.#privateProperties.isWaitAnswer) {
			this.#privateProperties.isWaitAnswer = true;
			this.#privateProperties.offersStack--;

			try {
				const offer = await this.sender.createOffer()
				await this.sender.setLocalDescription(offer);
				this.#sendSignal({ offer });
			} catch (e) {
				this.setLog(e)
				console.error(e);
				this.#privateProperties.isWaitAnswer = false;
				await sendOffer();
			}
		}
	}

	async startStream() {
		const videoTrack = await this.getLocalVideo();
		if (videoTrack) {
			await this.publishTrack(MediaStreamsKinds.Camera, videoTrack);
		} else {
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Camera])
		}

		const audioTrack = await this.getLocalAudio();
		if (audioTrack) {
			await this.publishTrack(MediaStreamsKinds.Microphone, audioTrack);
		} else {
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Microphone])
		}
	}

	async socketOnMessageHandler(event) {
		if (typeof event.data !== 'string') return;

		let data

		try
		{
			data = JSON.parse(event.data);
		} catch (err)
		{
			this.setLog(err);
			console.error("Could not parse socket message.", err);
			return;
		}

		if (data?.answer){
			await this.#answerHandler(data);
		} else if (data?.offer) {
			await this.#offerHandler(data);
		} else if (data?.joinResponse) {
			this.#privateProperties.iceServers = data.joinResponse.iceServers;
			this.#privateProperties.myUserId = data.joinResponse.localParticipant.userId
			this.#privateProperties.callState = CALL_STATE.CONNECTED
			this.#createPeerConnection()
			if (this.#privateProperties.isReconnecting)
			{
				this.triggerEvents('Reconnected');
				this.#privateProperties.isReconnecting = false;
				this.#privateProperties.reconnectionTries = this.#privateProperties.initialReconnectionTries;
			}
			else
			{
				this.triggerEvents('Connected')
			}

			const partcipantsToDelete = {...this.#privateProperties.remoteParticipants};
			Object.values(data.joinResponse.otherParticipants).forEach( p => {
				if (partcipantsToDelete[p.userId])
				{
					delete partcipantsToDelete[p.userId];
				}
				this.#setRemoteParticipant(p)
			})

			for (let userId in partcipantsToDelete)
			{
				const participant = this.#privateProperties.remoteParticipants[userId];
				this.triggerEvents('ParticipantLeaved', [participant]);
				delete this.#privateProperties.remoteTracks[userId];
				delete this.#privateProperties.remoteParticipants[userId];
			}

			this.#privateProperties.pingIntervalDuration = data.joinResponse.pingInterval * 1000
			this.#privateProperties.pingTimeoutDuration = this.#privateProperties.pingIntervalDuration * 2
			this.#startPingInterval()
		} else if (data?.participantJoined) {
			this.#setRemoteParticipant(data.participantJoined.participant)
		} else if (data?.participantLeft) {
			setTimeout(() => {
				const participantId = data?.participantLeft.userId
				const participant = this.#privateProperties.remoteParticipants[participantId]
				this.triggerEvents('ParticipantLeaved', [participant])
				delete this.#privateProperties.remoteTracks[participantId]
				delete this.#privateProperties.remoteParticipants[participantId]
			},0)
		} else if (data?.trackCreated) {
			const participantId = data.trackCreated.userId;
			const trackId = data.trackCreated.track.sid
			const track = data.trackCreated.track;
			track.userId = participantId
			if (participantId === this.#privateProperties.myUserId) {
				this.#privateProperties.localTracks[track.source] = track
				this.triggerEvents('PublishSucceed', [track.source]);
			} else {
				this.#privateProperties.tracksDataFromSocket[trackId] = track;
				const participant = this.#privateProperties.remoteParticipants[participantId];
				if (participant)
				{
					switch (track.source)
					{
						case MediaStreamsKinds.Camera:
							participant.videoEnabled = true;
							break;
						case MediaStreamsKinds.Microphone:
							participant.audioEnabled = true;
							break;
						case MediaStreamsKinds.Screen:
							participant.screenSharingEnabled = true;
							break;
					}
				}
			}
		} else if (data?.trackDeleted) {
			try
			{
				const participantId = data?.trackDeleted.publisher
				if (participantId === this.#privateProperties.myUserId) return;
				const participant = this.#privateProperties.remoteParticipants[participantId]
				const trackId = data?.trackDeleted.shortId
				const track = Object.values(participant.tracks)?.find(track => track?.id === trackId);

				if (track)
				{
					if (track.source === MediaStreamsKinds.Microphone)
					{
						participant.audioEnabled = false;
					}
					else if (track.source === MediaStreamsKinds.Camera)
					{
						participant.videoEnabled = false;
					}
					else if (track.source === MediaStreamsKinds.Screen)
					{
						participant.screenSharingEnabled = false;
					}
					participant.removeTrack(track.source);
					this.triggerEvents('RemoteMediaRemoved', [participant, track]);
				}
			} catch (e)
			{
				console.error(e, data.trackDeleted)
			}
		} else if (data?.trackMuted) {
			const participant = this.#privateProperties.remoteParticipants[data.trackMuted.track.publisher];
			if (!participant) return;

			const track = Object.values(participant.tracks)?.find(track => track?.id === data.trackMuted.track.shortId);
			if (!track) return;

			if (track.source === MediaStreamsKinds.Microphone)
			{
				participant.isMutedAudio = data.trackMuted.muted;
			}
			const eventName = data.trackMuted.muted
				? 'RemoteMediaMuted'
				: 'RemoteMediaUnmuted';
			this.triggerEvents(eventName, [participant, track]);
		} else if (data?.trickle) {
			this.#addIceCandidate(data.trickle);
		} else if (data?.newMessage) {
			const message = new Message(data.newMessage)
			this.triggerEvents('MessageReceived', [message]);
		} else if (data?.handRaised) {
			const participant = this.#privateProperties.remoteParticipants[data.handRaised.participantId];
			if (participant)
			{
				participant.isHandRaised = data.handRaised.isHandRaised;
				this.triggerEvents('HandRaised', [participant]);
			}
		} else if (data?.speakersChanged) {
			this.#speakerChangedHandler(data)
		} else if (data?.connectionQuality || data?.subscribedQualityUpdate) {
			console.log(data)
		} else if (data.pong) {
			this.#resetPingTimeout()
		} else if (data.pongResp) {
			this.#privateProperties.rtt = Date.now() - data.pongResp.lastPingTimestamp
			this.#resetPingTimeout()
		}
	};
	socketOnOpenHandler() {
		window.addEventListener('beforeunload', this.sendLeaveBound)
	};

	socketOnCloseHandler(e) {
		console.log(e)
		this.#beforeDisconnect();

		if (e?.code && e?.code !== 1005 && !this.isReconnecting)
		{
			this.#reconnect();
		}
	};
	socketOnErrorHandler() {
		if (!this.isConnected())
		{
			this.triggerEvents('Failed', [{name: 'WEBSOCKET_ERROR'}]);
		}
	};
	async onIceCandidate(target, event) {
		if (!event.candidate) return;
		const trickle = {
			candidateInit: JSON.stringify({
				candidate: event.candidate.candidate,
				sdpMid: event.candidate?.sdpMid,
				sdpMLineIndex: event.candidate?.sdpMLineIndex,
				usernameFragment: event.candidate?.usernameFragment
			})
		};

		if (target) {
			trickle.target = target;
		}

		this.#sendSignal({ trickle });
	};
	onConnectionStateChange(subscriber) {
		const state = subscriber
			? this.recipient.connectionState
			: this.sender.connectionState

		if (state === 'failed' && !this.isReconnecting)
		{
			if (this.#privateProperties.peerConnectionFailed)
			{
				return;
			}

			this.#privateProperties.peerConnectionFailed = true;
			this.#beforeDisconnect();
			this.#reconnect();
		}
	}

	#resetPingTimeout() {
		this.#clearPingTimeout()
		if (!this.#privateProperties.pingTimeoutDuration) {
			return;
		}
		this.#privateProperties.pingTimeout = setTimeout(() => {
			this.#beforeDisconnect();
			this.#reconnect();
		}, this.#privateProperties.pingTimeoutDuration);
	}


	#clearPingTimeout() {
		if (this.#privateProperties.pingTimeout) {
			clearTimeout(this.#privateProperties.pingTimeout);
		}
	}

	#startPingInterval() {
		this.#clearPingInterval()
		this.#resetPingTimeout()
		if (!this.#privateProperties.pingIntervalDuration) {
			return;
		}
		this.#privateProperties.pingPongInterval = setInterval(() => {
			this.#sendPing();
		}, this.#privateProperties.pingIntervalDuration);
	}

	#clearPingInterval() {
		this.#clearPingTimeout();
		if (this.#privateProperties.pingPongInterval) {
			clearInterval(this.#privateProperties.pingPongInterval);
		}
	}

	#sendPing() {
		this.#sendSignal({ ping: Date.now() });
		this.#sendSignal({
			pingReq: {
				timestamp: Date.now(),
				rtt: this.#privateProperties.rtt
			}
		});
	}

	on(eventType, handler) {
		this.#privateProperties.events.set(eventType, handler)
		return this
	}
	off(eventType) {
		if (this.#privateProperties.events.has(eventType)) {
			return this.#privateProperties.events.delete(eventType)
		}
		return this
	}

	triggerEvents(eventType, args) {
		if (this.#privateProperties.events.has(eventType)) {
			const event = this.#privateProperties.events.get(eventType)
			if (args) {
				event(...args)
			} else {
				event()
			}
		}
	}

	isRecordable() {
		console.log('isRecordable')
	}

	async setBitrate(bitrate, MediaStreamKind) {
		let track;
		let isSimulcast;
		switch (MediaStreamKind) {
			case MediaStreamsKinds.Camera:
				track = this.#privateProperties.cameraStream.getVideoTracks[0];
				isSimulcast = this.#privateProperties.videoSimulcast;
				break;
			case MediaStreamsKinds.Microphone:
				track = this.#privateProperties.microphoneStream.getAudioTracks[0];
				break;
			case MediaStreamsKinds.Screen:
				track = this.#privateProperties.screenStream.getVideoTracks[0];
				break;
		}
		const senders = this.sender.getSenders()

		senders.forEach( (sender) => {
			const params = sender.getParameters();
			if(!params || !params.encodings || params.encodings.length === 0) {
				console.warn('No encodings in the sender parameters, ignoring bitrate for track:', track);
			} else {
				params.encodings.forEach(encoding => {
					if (isSimulcast) {
						encoding.maxBitrate = bitrate < this.#privateProperties.defaultSimulcastBitrate[encoding.rid] ? bitrate : this.#privateProperties.defaultSimulcastBitrate[encoding.rid]
					} else {
						encoding.maxBitrate = bitrate
					}
				})
				sender.setParameters(params);
			}
		})

		await Promise.all(senders)
	}

	async publishTrack(MediaStreamKind, MediaStreamTrack, StreamQualityOptions = {}) {
		console.log('publishTrack', MediaStreamKind, MediaStreamTrack)

		if (!this.sender) return;

		try {
			for (let keys in StreamQualityOptions ) {
				this.#privateProperties[`${keys}`] = StreamQualityOptions[keys]
			}

			const source = MediaStreamKind
			MediaStreamTrack.source = source
			if (source === MediaStreamsKinds.Camera) {
				if( this.#privateProperties.videoSimulcast) {
					const width = MediaStreamTrack.getSettings().width;
					const height = MediaStreamTrack.getSettings().height;

					this.sender.addTransceiver(MediaStreamTrack, {
						direction: 'sendonly',
						streams: [this.#privateProperties.cameraStream],
						sendEncodings: MediaStreamTrack.sendEncodings || [
							{ rid: 'q', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['q'], scaleResolutionDownBy: 4 },
							{ rid: 'h', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['h'], scaleResolutionDownBy: 2 },
							{ rid: 'f', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['f'] },
						]
					});

					this.#sendSignal({
						"addTrack":  {
							"cid":  MediaStreamTrack.id,
							"type":  "VIDEO",
							"width":  width,
							"height":  height,
							"source":  source,
							"layers":  [
								{
									"quality":  "LOW",
									"width":  width / 4,
									"height":  height / 4,
									"bitrate":  this.#privateProperties.defaultSimulcastBitrate.q
								},
								{
									"quality":  "MEDIUM",
									"width":  width / 2,
									"height":  height / 2,
									"bitrate":  this.#privateProperties.defaultSimulcastBitrate.h
								},
								{
									"quality":  "HIGH",
									"width":  width,
									"height":  height,
									"bitrate":  this.#privateProperties.defaultSimulcastBitrate.f
								}
							]
						}
					});
				} else {
					this.sender.addTransceiver(MediaStreamTrack, {
						direction: 'sendonly'
					});
					await this.setBitrate(this.#privateProperties.videoBitrate, MediaStreamsKinds.Camera)

					const width = MediaStreamTrack.getSettings().width
					const height = MediaStreamTrack.getSettings().height

					this.#sendSignal({
						"addTrack":  {
							"cid":  MediaStreamTrack.id,
							"type":  "VIDEO",
							"width":  width,
							"height":  height,
							"source":  source,
						}
					});
				}

			} else if (source === MediaStreamsKinds.Microphone) {
				this.sender.addTransceiver(MediaStreamTrack, {
					direction: 'sendonly'
				});
				this.#sendSignal({
					"addTrack":  {
						"cid" : MediaStreamTrack.id,
						"source":  source
					}
				});
			} else if (source === MediaStreamsKinds.Screen) {
				this.sender.addTransceiver(MediaStreamTrack, {
					direction: 'sendonly'
				});
				const width = MediaStreamTrack.getSettings().width
				const height = MediaStreamTrack.getSettings().height

				this.#sendSignal({
					"addTrack":  {
						"cid":  MediaStreamTrack.id,
						"type":  "VIDEO",
						"width":  width,
						"height":  height,
						"source":  source,
					}
				});
			}

			this.#privateProperties.offersStack++;
			await this.sendOffer();
		} catch (e) {
			this.setLog(e)
			console.error(e)
			this.triggerEvents('PublishFailed', [MediaStreamKind])
		}
	}

	async changeStreamQuality(StreamQualityOptions) {
		console.log('changeStreamQuality', StreamQualityOptions)
		for (let key in StreamQualityOptions) {
			if (this.#privateProperties[`${key}`] !== StreamQualityOptions[key]) {
				this.#privateProperties[`${key}`] = StreamQualityOptions[key]

				if (key === 'videoSimulcast' || (key === 'screenSimulcast' && this.#privateProperties.screenStream)) {
					const kind = key === 'videoSimulcast'
						? MediaStreamsKinds.Camera
						: MediaStreamsKinds.Screen

					if (this.#getSender(kind)) {
						await this.republishTrack(kind)
					}
				} else if ( ['videoBitrate', 'audioBitrate', 'screenBitrate'].includes(key)) {
					const kind = key === 'videoBitrate'
						? MediaStreamsKinds.Camera
						: key === 'screenBitrate'
							? MediaStreamsKinds.Screen
							: MediaStreamsKinds.Microphone

					await this.setBitrate(StreamQualityOptions[key], kind)
				}
			}
		}
	}

	async republishTrack(MediaStreamKind) {
		await this.unpublishTrack(MediaStreamKind);
		const track = await this.getTrack(MediaStreamKind);
		if (track) {
			await this.publishTrack(MediaStreamKind, track);
		} else {
			this.triggerEvents('PublishFailed', [MediaStreamKind])
		}
	}

	async unpublishTrack(MediaStreamKind) {
		console.log('unpublishTrack', MediaStreamKind)
		const sender = this.#getSender(MediaStreamKind);

		if (sender) {
			this.sender.removeTrack(sender);
			this.#privateProperties.offersStack++;
			await this.sendOffer();
		}
	}

	#changeRoomStreamsQuality(mainUserId, kind) {
		Object.values(this.getParticipants()).forEach(p =>
		{
			if (mainUserId)
			{
				const exactUser = mainUserId == p.userId;
				const quality = exactUser && kind === MediaStreamsKinds.Camera ? STREAM_QUALITY.HIGH : STREAM_QUALITY.LOW;

				if (exactUser)
				{
					this.#privateProperties.hasMainStream = true;
				}

				p.setStreamQuality(quality);
			}
			else
			{
				const quality = STREAM_QUALITY.MEDIUM;
				this.#privateProperties.hasMainStream = false;
				p.setStreamQuality(quality);
			}
		})
	}

	hangup() {
		this.#sendLeave();
		this.#beforeDisconnect();

		this.#privateProperties.url = null;
		this.#privateProperties.token = null;
		this.#privateProperties.endpoint = null;
		this.#privateProperties.jwt = null;
		this.#privateProperties.options = null;
		this.#privateProperties.iceServers = null;

		this.#releaseStream(MediaStreamsKinds.Camera);
		this.#releaseStream(MediaStreamsKinds.Microphone);
		this.#releaseStream(MediaStreamsKinds.Screen);

		this.#privateProperties.rtt = 0;
		this.#privateProperties.remoteTracks = {};
		this.#privateProperties.isReconnecting = false;
		this.#privateProperties.reconnectionTries = this.#privateProperties.initialReconnectionTries;
		this.#privateProperties.hasMainStream = false;

		if (this.isConnected()) {
			this.#privateProperties.callState = CALL_STATE.TERMINATED
			this.triggerEvents('Disconnected')
		}
	}

	isConnected() {
		console.log('isConnected')
		return this.#privateProperties.callState === CALL_STATE.CONNECTED
	}

	setMainStream(user, kind) {
		this.#changeRoomStreamsQuality(user, kind);
	}

	resetMainStream() {
		this.#changeRoomStreamsQuality()
	}

	pauseTrack(mediaStreamKind, keepTrack) {
		const trackSid = this.#privateProperties.localTracks[mediaStreamKind]?.sid;
		if (trackSid)
		{
			if (!keepTrack)
			{
				delete this.#privateProperties.localTracks[mediaStreamKind];
			}
			this.#sendSignal({
				mute: {
					sid: trackSid,
					muted: true
				}
			});
		}
	}

	unpauseTrack(mediaStreamKind) {
		const trackSid = this.#privateProperties.localTracks[mediaStreamKind]?.sid;
		if (trackSid)
		{
			this.#sendSignal({
				mute: {
					sid: trackSid,
					muted: false
				}
			});
		}
	}

	disableAudio() {
		const track = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
		if (track)
		{
			track.enabled = false;
			this.pauseTrack(MediaStreamsKinds.Microphone, true);
		}
	}

	async enableAudio() {
		let track = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
		if (track && this.#privateProperties.localTracks[MediaStreamsKinds.Microphone])
		{
			track.enabled = true;
			this.unpauseTrack(MediaStreamsKinds.Microphone);
		}
		else
		{
			track = await this.getLocalAudio();
			if (track)
			{
				track.enabled = true;
				await this.publishTrack(MediaStreamsKinds.Microphone, track);
			}
			else
			{
				this.triggerEvents('PublishFailed', [MediaStreamsKinds.Microphone]);
			}
		}
	}

	async disableVideo() {
		this.#releaseStream(MediaStreamsKinds.Camera)
		this.pauseTrack(MediaStreamsKinds.Camera)
		await this.unpublishTrack(MediaStreamsKinds.Camera)
	}

	async enableVideo() {
		const track = await this.getLocalVideo()
		if (track) {
			await this.publishTrack(MediaStreamsKinds.Camera, track)
		} else {
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Camera])
		}
	}

	async startScreenShare() {
		const track = await this.getLocalScreen()
		if (track) {
			await this.publishTrack(MediaStreamsKinds.Screen, track)
		} else {
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Screen])
		}
	}

	async stopScreenShare() {
		this.#releaseStream(MediaStreamsKinds.Screen)
		this.pauseTrack(MediaStreamsKinds.Screen)
		await this.unpublishTrack(MediaStreamsKinds.Screen)
	}

	sendMessage(message) {
		this.#sendSignal({ sendMessage: { message } });
	}

	raiseHand(raised) {
		this.#sendSignal({ raiseHand: { raised } });
	}

	async getLocalVideo() {
		if (!this.#privateProperties.cameraStream)
		{
			await this.getTrack(MediaStreamsKinds.Camera);
		}

		return this.#privateProperties.cameraStream?.getVideoTracks()[0];
	}
	async getLocalAudio() {
		if (!this.#privateProperties.microphoneStream)
		{
			await this.getTrack(MediaStreamsKinds.Microphone);
		}

		return this.#privateProperties.microphoneStream?.getAudioTracks()[0];
	}
	async getLocalScreen() {
		if (!this.#privateProperties.screenStream)
		{
			await this.getTrack(MediaStreamsKinds.Screen);
		}

		return this.#privateProperties.screenStream?.getVideoTracks()[0];
	}

	async #getUserMedia(options) {
		const constraints = {
			audio: false,
			video: false,
		}

		let stream = null

		try {
			if (options.video) {
				constraints.video = {
					width: this.#privateProperties.defaultVideoResolution.width,
					height: this.#privateProperties.defaultVideoResolution.height,
				}

				if (this.#privateProperties.videoDeviceId) {
					constraints.video.deviceId = {exact: this.#privateProperties.videoDeviceId}
				}
			} else if (options.audio) {
				if (this.#privateProperties.audioDeviceId) {
					constraints.audio = {
						deviceId: {exact: this.#privateProperties.audioDeviceId}
					}
				} else {
					constraints.audio = true
				}
			}

			stream = await navigator.mediaDevices.getUserMedia(constraints)
		} catch (e) {
			console.error(e)
		} finally {
			return stream
		}
	}

	async #getDisplayMedia() {
		let stream = null;

		try {
			if (window["BXDesktopSystem"])
			{
				stream = await navigator.mediaDevices.getUserMedia({
					video: {
						mandatory: {
							chromeMediaSource: 'screen',
							maxWidth: 1920,
							maxHeight: 1080,
							maxFrameRate: 5
						}
					}
				});
			}
			else
			{
				stream = await navigator.mediaDevices.getDisplayMedia({
					video: {
						cursor: 'always'
					},
					audio: false
				});
			}
		} finally {
			return stream;
		}
	}

	async getTrack(MediaStreamKind) {
		if (MediaStreamKind === MediaStreamsKinds.Camera && !this.#privateProperties.cameraStream)
		{
			this.#privateProperties.cameraStream = await this.#getUserMedia({video: true});
		}
		else if (MediaStreamKind === MediaStreamsKinds.Microphone && !this.#privateProperties.microphoneStream)
		{
			this.#privateProperties.microphoneStream = await this.#getUserMedia({audio: true});
		}
		else if (MediaStreamKind === MediaStreamsKinds.Screen && !this.#privateProperties.screenStream)
		{
			this.#privateProperties.screenStream = await this.#getDisplayMedia();
		}

		let track;

		if (MediaStreamKind === MediaStreamsKinds.Screen)
		{
			track = this.#privateProperties.screenStream?.getVideoTracks()[0];
			if (track && track.readyState !== 'live')
			{
				this.#privateProperties.screenStream = null;
				track = this.getLocalScreen();
			}
		}
		else if (MediaStreamKind === MediaStreamsKinds.Camera)
		{
			track = this.#privateProperties.cameraStream?.getVideoTracks()[0];
			if (track && track.readyState !== 'live')
			{
				this.#privateProperties.cameraStream = null;
				track = this.getLocalVideo();
			}
		}
		else if (MediaStreamKind === MediaStreamsKinds.Microphone)
		{
			track = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
			if (track && track.readyState !== 'live')
			{
				this.#privateProperties.microphoneStream = null;
				track = this.getLocalAudio();
			}
		}

		if (track && !track.onended)
		{
			track.onended = () => this.triggerEvents('PublishEnded', [MediaStreamKind]);
		}

		return track;
	}

	async switchActiveAudioDevice(deviceId) {
		return new Promise(async (resolve, reject) =>
		{
			this.#privateProperties.audioDeviceId = deviceId;
			try
			{
				const prevTrack = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
				this.#privateProperties.microphoneStream = null;
				let prevTrackEnabledState = true;
				if (prevTrack)
				{
					prevTrackEnabledState = prevTrack.enabled;
					prevTrack.stop();
				}
				const audioTrack = await this.getLocalAudio();
				audioTrack.source = MediaStreamsKinds.Microphone;
				audioTrack.enabled = prevTrackEnabledState;
				const sender = this.#getSender(MediaStreamsKinds.Microphone);
				if (sender)
				{
					await sender.replaceTrack(audioTrack);
					resolve();
				}
			} catch (e)
			{
				reject(e);
			}
		});

	}

	async switchActiveVideoDevice(deviceId) {
		return new Promise(async (resolve, reject) =>
		{
			this.#privateProperties.videoDeviceId = deviceId;
			try
			{
				this.#privateProperties.cameraStream?.getVideoTracks()[0].stop();
				this.#privateProperties.cameraStream = null;
				const videoTrack = await this.getLocalVideo();
				videoTrack.source = MediaStreamsKinds.Camera;
				const sender = this.#getSender(MediaStreamsKinds.Camera);
				if (sender)
				{
					await sender.replaceTrack(videoTrack);
					resolve();
				}
			} catch (e)
			{
				reject(e);
			}
		});
	}

	getLocalUserId() {
		return this.#privateProperties.myUserId
	}

	getParticipants() {
		return this.#privateProperties.remoteParticipants
	}

	getState() {
		return this.#privateProperties.callState
	}

	setLog(log) {
		if (this.#privateProperties.isloggingEnable) {
			const idx = Object.values(this.#privateProperties.logs).length + 1
			const _log = {}
			_log.timestamp = Math.floor(Date.now() / 1000)
			_log.event = log

			this.#privateProperties.logs[idx] = _log

			if (this.#privateProperties.loggerCallback) {
				this.#privateProperties.loggerCallback();
			}
		}
	}

	setLoggerCallback(callback) {
		this.#privateProperties.loggerCallback = callback;
	}

	enableSilentLogging(enable) {
		this.#privateProperties.isloggingEnable = enable;
	}

	async #answerHandler(data) {
		try
		{
			await this.sender.setRemoteDescription(data.answer);
			this.#privateProperties.pendingCandidates.sender.forEach((candidate) =>
			{
				this.sender.addIceCandidate(candidate);
			});
			this.#privateProperties.pendingCandidates.sender = [];
		}
		finally
		{
			this.#privateProperties.isWaitAnswer = false;
			await this.sendOffer();
		}
	}

	async #offerHandler(data) {
		await this.recipient.setRemoteDescription(data.offer);
		this.#privateProperties.pendingCandidates.recipient.forEach((candidate) =>
		{
			this.recipient.addIceCandidate(candidate);
		});
		this.#privateProperties.pendingCandidates.recipient = [];
		const answer = await this.recipient.createAnswer();
		await this.recipient.setLocalDescription(answer);
		this.#sendSignal({ answer });
	}

	#addIceCandidate(trickle) {
		try
		{
			const candidate = JSON.parse(trickle.candidateInit);

			if (trickle.target)
			{
				if (this.recipient.remoteDescription)
				{
					this.recipient.addIceCandidate(candidate);
					return;
				}

				this.#privateProperties.pendingCandidates.recipient.push(candidate);
			}
			else
			{
				if (this.sender.remoteDescription)
				{
					this.sender.addIceCandidate(candidate);
					return;
				}

				this.#privateProperties.pendingCandidates.sender.push(candidate);
			}
		}
		catch (e)
		{
			this.setLog(e);
		}
	}

	#setRemoteParticipant(participant) {
		const userId = participant.userId
		const participantEvent = this.#privateProperties.remoteParticipants[userId]
			? 'ParticipantStateUpdated'
			: 'ParticipantJoined'
		const remoteParticipant = new Participant(participant, this.#privateProperties.socketConnect)
		if (participant.participantTracks) {
			Object.values(participant.participantTracks).forEach( track => {
				track.userId = userId
				this.#privateProperties.tracksDataFromSocket[track.sid] = track
				if (track.muted && track.source === MediaStreamsKinds.Microphone)
				{
					remoteParticipant.isMutedAudio = true;
				}
				switch (track.source)
				{
					case MediaStreamsKinds.Camera:
						remoteParticipant.videoEnabled = true;
						break;
					case MediaStreamsKinds.Microphone:
						remoteParticipant.audioEnabled = true;
						break;
					case MediaStreamsKinds.Screen:
						remoteParticipant.screenSharingEnabled = true;
						break;
				}
			})
		}
		this.#privateProperties.remoteParticipants[userId] = remoteParticipant;
		this.triggerEvents(participantEvent, [remoteParticipant]);
	}

	#speakerChangedHandler(data) {
		data.speakersChanged.speakers.forEach((speaker) => {
			const participant = Object.values(this.#privateProperties.remoteParticipants).find(p => p.sid === speaker.sid)
			if (participant && participant?.userId !== this.#privateProperties.myUserId) {
				participant.isSpeaking = speaker?.active || false
				if (speaker?.active) {
					this.triggerEvents('VoiceStarted', [participant]);
				} else {
					this.triggerEvents('VoiceEnded', [participant]);
				}
			}
		})
	}

	#createPeerConnection() {
		this.#destroyPeerConnection()

		const config = {};
		if (this.#privateProperties.iceServers)
		{
			config.iceServers = this.#privateProperties.iceServers;
		}

		this.sender = new RTCPeerConnection(config);
		this.sender.addEventListener('icecandidate', e => this.onIceCandidate(null, e));
		this.sender.addEventListener('connectionstatechange', e => this.onConnectionStateChange());

		this.recipient = new RTCPeerConnection(config);
		this.recipient.ontrack = (event) => {
			const ids = event.streams[0].id.split('|');
			const trackId = ids[1]
			const userId = this.#privateProperties.tracksDataFromSocket[trackId]?.userId
			event.track.source = this.#privateProperties.tracksDataFromSocket[trackId]?.source
			event.track.layers = this.#privateProperties.tracksDataFromSocket[trackId]?.layers || null;
			const participant = this.#privateProperties.remoteParticipants[userId];
			// A track without participant, we should skip it
			if (!participant) {
				return
			}
			if (!this.#privateProperties.remoteTracks?.[userId]) {
				this.#privateProperties.remoteTracks[userId] = {}
			}
			const track = new Track(trackId, event.track);
			this.#privateProperties.remoteTracks[userId][trackId] = track;

			if (track) {
				participant.addTrack(event.track.source, track);
				this.triggerEvents('RemoteMediaAdded', [participant, track]);
			}

			if (event.track.source === MediaStreamsKinds.Camera) {
				const quality = this.#privateProperties.hasMainStream
					? STREAM_QUALITY.LOW
					: this.#privateProperties.defaultRemoteStreamsQuality;
				participant.setStreamQuality(quality);
			}
		};
		this.recipient.addEventListener('icecandidate', e => this.onIceCandidate('SUBSCRIBER', e));
		this.recipient.addEventListener('connectionstatechange', e => this.onConnectionStateChange(true));

		this.#privateProperties.callStatsInterval = setInterval( async () => {
			try
			{
				const statsAll = {};
				await this.sender.getStats(null).then((stats) =>
				{
					let statsOutput = [];

					stats.forEach((report) =>
					{
						statsOutput.push(report);
					})
					statsAll.sender = statsOutput;
				});

				await this.recipient.getStats(null).then((stats) =>
				{
					let statsOutput = [];
					const participantsWithLargeDataLoss = new Set();

					stats.forEach((report) =>
					{
						statsOutput.push(report);

						const needCheckPacketLosts = (report?.trackIdentifier
							&& report?.kind === 'video'
							&& report.hasOwnProperty('packetsLost')
							&& report.hasOwnProperty('packetsReceived'));

						if ( needCheckPacketLosts )
						{
							const {packetsLost, trackIdentifier, packetsReceived} = report;
							this.#privateProperties.prevPacketsLost[trackIdentifier] = this.#privateProperties.prevPacketsLost?.[trackIdentifier] || 0;
							this.#privateProperties.prevPacketsReceived[trackIdentifier] = this.#privateProperties.prevPacketsReceived?.[trackIdentifier] || 0;
							const percentPacketLost = (packetsReceived - this.#privateProperties.prevPacketsReceived[trackIdentifier]) / 100 * (packetsLost - this.#privateProperties.prevPacketsLost[trackIdentifier]);
							this.#privateProperties.prevPacketsLost[trackIdentifier] = packetsLost;
							this.#privateProperties.prevPacketsReceived[trackIdentifier] = packetsReceived;

							if (percentPacketLost > this.#privateProperties.packetLostThreshold)
							{
								const participant = Object.values(this.#privateProperties.remoteParticipants).find(p => p?.tracks?.[MediaStreamsKinds.Camera]?.track?.id === report?.trackIdentifier);
								if (participant && participant.userId !== this.#privateProperties.myUserId)
								{
									participantsWithLargeDataLoss.add(participant.userId);
									this.#privateProperties.prevParticipantsWithLargeDataLoss.delete(participant.userId);
								}
							}
						}
					});

					statsAll.recipient = statsOutput;
					if (participantsWithLargeDataLoss.size || this.#privateProperties.prevParticipantsWithLargeDataLoss.size)
					{
						this.triggerEvents('UpdatePacketLoss', [[...participantsWithLargeDataLoss]] );
					}
					this.#privateProperties.prevParticipantsWithLargeDataLoss = participantsWithLargeDataLoss;
				});

				this.triggerEvents('CallStatsReceived', [statsAll]);
			} catch (e)
			{
				// if we're here it's almost okay
				// we tried to get stats during reconnection
			}
		}, this.#privateProperties.statsTimeout);
	}

	#destroyPeerConnection() {
		if (this.sender) {
			this.sender.close()
			this.sender = null
		}

		if (this.recipient) {
			this.recipient.close()
			this.recipient = null
		}

		this.#privateProperties.peerConnectionFailed = false;
	}

	#releaseStream(kind) {
		let streamType

		switch (kind) {
			case MediaStreamsKinds.Camera:
				streamType = 'cameraStream'
				break
			case MediaStreamsKinds.Microphone:
				streamType = 'microphoneStream'
				break
			case MediaStreamsKinds.Screen:
				streamType = 'screenStream'
				break
		}

		if (streamType) {
			this.#privateProperties[streamType]?.getTracks?.()?.forEach( track => {
				track.onended = null
				track.stop()
			})
			this.#privateProperties[streamType] = null
		}
	}

	#getSender(kind) {
		const senders = this.sender?.getSenders?.();
		let sender = null;

		if (senders?.length > 0) {
			for (const s of senders) {
				if (s.track?.source === kind) {
					sender = s
					break;
				}
			}
		}

		return sender;
	}

	#sendSignal(signal) {
		if (this.#privateProperties.socketConnect?.readyState === 1)
		{
			this.#privateProperties.socketConnect.send(JSON.stringify(signal));
		}
	}

	#sendLeave() {
		if (this.#privateProperties.socketConnect?.readyState === 1) {
			this.#sendSignal({
				leave:  {
					reason: 'CLIENT_INITIATED'
				}
			});
		}
	}
}

class Participant {
	#socketConnect;

	name = '';
	image = '';
	userId = '';
	videoEnabled = false;
	audioEnabled = false;
	screenSharingEnabled = false;
	isSpeaking = false;
	tracks = {};
	sid = '';
	isMutedVideo = false;
	isMutedAudio = false;
	isHandRaised = false;

	constructor(participant, socket) {
		this.name = participant?.name || '';
		this.image = participant?.image || '';
		this.userId = participant?.userId || '';
		this.sid = participant?.sid || '';
		this.videoEnabled = participant?.videoEnabled || false;
		this.audioEnabled = participant?.audioEnabled || false;
		this.screenSharingEnabled = participant?.screenSharingEnabled || false;
		this.isSpeaking = participant?.isSpeaking || false;
		this.isHandRaised = participant?.isHandRaised || false;
		this.#socketConnect = socket;
	}

	subscribeTrack(MediaStreamKind) {};

	unsubscribeTrack(MediaStreamKind) {};

	attachTrack(MediaStreamKind) {};

	detachTrack(MediaStreamKind) {};

	disableAudio() {
		this.tracks[MediaStreamsKinds.Microphone].track.enabled = false;
		this.isMutedAudio = true;
	};

	enableAudio() {
		this.tracks[MediaStreamsKinds.Microphone].track.enabled = true;
		this.isMutedAudio = false;
	};

	disableVideo() {
		this.tracks[MediaStreamsKinds.Camera].track.enabled = false;
		this.isMutedVideo = true;
	};

	enableVideo() {
		this.tracks[MediaStreamsKinds.Camera].track.enabled = true;
		this.isMutedVideo = false;
	};

	addTrack(MediaStreamKind, Track) {
		this.tracks[MediaStreamKind] = Track;
	};

	removeTrack(MediaStreamKind, Track) {
		delete this.tracks[MediaStreamKind];
	};

	getTrack(MediaStreamKind) {
		return this.tracks?.[MediaStreamKind]
	}

	setStreamQuality(quality) {
		if (this.tracks?.[MediaStreamsKinds.Camera]) {
			const trackId = this.tracks[MediaStreamsKinds.Camera].id;
			this.tracks[MediaStreamsKinds.Camera].track.currentVideoQuality =  quality;
			const signal = {
				trackSetting: {
					trackSids: [trackId],
					quality: quality,
				}
			};

			this.#socketConnect.send(JSON.stringify(signal));

			console.log('setQuality', signal);
		}
	}
}