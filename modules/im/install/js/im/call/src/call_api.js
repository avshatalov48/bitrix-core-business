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

const VIDEO_QUEUE = {
	INITIAL: '',
	ENABLE: 'enable',
	DISABLE: 'disable',
};

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
		realTracksIds: {}, // todo: check why track ids are different in a stream and in the track itself
		url: null,
		roomId: null,
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
		pendingPublications: {},
		publicationTimeout: 10000,
		cameraStream: null,
		microphoneStream: null,
		screenStream: null,
		localTracks: {},
		rtt: 0,
		pingIntervalDuration: 0,
		pingTimeoutDuration: 0,
		remoteTracks: {},
		remoteParticipants: {},
		mainStream: {},
		pingPongTimeout: null,
		pingPongInterval: null,
		userData: '',
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
		reconnectionAttempt: 0,
		reconnectionTimeout: null,
		reconnectionDelay: 1000,
		callStatsInterval: null,
		callState: '',
		packetLostThreshold: 7,
		statsTimeout: 3000,
		videoQueue: VIDEO_QUEUE.INITIAL,
	}

	constructor() {
		this.sendLeaveBound = this.#sendLeave.bind(this);
	}

	async connect(options) {
		this.setLog(`Connecting to a call (desktop: ${window['BXDesktopSystem'] ? 'true' : 'false'})`);
		this.#privateProperties.callState = CALL_STATE.PROGRESSING

		for (let key in options) {
			this.#privateProperties[`${key}`] = options[key]
		}

		if (!this.#privateProperties.endpoint) {
			this.setLog(`Missing required param 'endpoint' from the backend, disconnecting`);
			this.triggerEvents('Failed', [{name: 'AUTHORIZE_ERROR', message: `Missing required param 'endpoint'`}]);
			return;
		}
		if (!this.#privateProperties.jwt) {
			this.setLog(`Missing required param 'jwt' from the backend, disconnecting`);
			this.triggerEvents('Failed', [{name: 'AUTHORIZE_ERROR', message: `Missing required param 'jwt'`}]);
			return;
		}

		this.#privateProperties.endpoint = this.#privateProperties.endpoint.replace(/\/+$/, '');

		try
		{
			const mediaServerInfo = await this.getMediaServerInfo();
			this.#privateProperties.url = mediaServerInfo.url;
			this.#privateProperties.token = mediaServerInfo.token;
			this.#privateProperties.data = mediaServerInfo.data;
		}
		catch (error)
		{
			if (!this.#privateProperties.isReconnecting)
			{
				try
				{
					fetch(`${this.#privateProperties.endpoint}/send-to-log`, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json;charset=utf-8'
						},
						body: JSON.stringify({
							roomId: this.#privateProperties.roomId,
							token: this.#privateProperties.jwt,
							data: `Can't connect to a mediaserver: ${error}`,
						})
					});
				}
				finally
				{
					this.triggerEvents('Failed', [error]);
				}
			}
			else
			{
				this.#reconnect();
			}
			return;
		}

		this.#privateProperties.socketConnect = new WebSocket(`${this.#privateProperties.url}?access_token=${this.#privateProperties.token}&auto_subscribe=1&sdk=js&version=1.6.7&protocol=8&roomData=${this.#privateProperties.data}`);
		this.#privateProperties.socketConnect.onmessage = (e) => this.socketOnMessageHandler(e);
		this.#privateProperties.socketConnect.onopen = () => this.socketOnOpenHandler();
		this.#privateProperties.socketConnect.onerror = () => this.socketOnErrorHandler();
		this.#privateProperties.socketConnect.onclose = (e) => this.socketOnCloseHandler(e);
	};

	#reconnect() {
		this.#privateProperties.isReconnecting = true;
		this.#privateProperties.videoQueue = VIDEO_QUEUE.INITIAL;

		const reconnect = () => {
			this.setLog(`Reconnecting attempt : ${++this.#privateProperties.reconnectionAttempt}`);
			this.#privateProperties.reconnectionTimeout = setTimeout(this.connect.bind(this), this.#privateProperties.reconnectionDelay);
		};

		reconnect();
		this.triggerEvents('Reconnecting');
	};

	#beforeDisconnect() {
		window.removeEventListener('unload', this.sendLeaveBound);
		this.#clearPingInterval();
		this.#clearPingTimeout();
		clearInterval(this.#privateProperties.callStatsInterval);

		this.#privateProperties.localTracks = {};
		this.#privateProperties.isWaitAnswer = false;

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

	getMediaServerInfo() {
		return new Promise(async (resolve, reject) => {
			const url = `${this.#privateProperties.endpoint}/join?token=${this.#privateProperties.jwt}`;
			let response;
			let data;

			try
			{
				response = await fetch(url, {
					method: 'GET',
				});
				if (!response.ok)
				{
					throw new Error(`Got response code ${response.status}`);
				}
			}
			catch (error)
			{
				reject({name: 'MEDIASERVER_UNREACHABLE', message: error.message});
				return;
			}

			try
			{
				data = await response.json();
			}
			catch (error)
			{
				reject({name: 'MEDIASERVER_UNEXPECTED_ANSWER', message: error.message});
				return;
			}

			if (data.result?.mediaServerUrl && data.result?.tokenToAccessMediaServer && data.result?.roomData)
			{
				resolve({
					url: data.result.mediaServerUrl,
					token: data.result.tokenToAccessMediaServer,
					data: data.result.roomData,
				});
			}
			else
			{
				reject({name: 'MEDIASERVER_MISSING_PARAMS', message: `Incorrect signaling response`});
			}
		});
	}
	async sendOffer() {
		if (this.#privateProperties.offersStack > 0 && !this.#privateProperties.isWaitAnswer) {
			this.setLog(`Start sending an offer`);
			this.#privateProperties.isWaitAnswer = true;
			this.#privateProperties.offersStack--;

			try {
				const offer = await this.sender.createOffer()
				await this.sender.setLocalDescription(offer);
				this.#sendSignal({ offer });
				this.setLog(`Sending an offer succeed`);
			}
			catch (e)
			{
				this.setLog(`Sending an offer failed: ${e}`);
				this.#privateProperties.isWaitAnswer = false;
				await this.sendOffer();
			}
		}
	}

	async startStream() {
		const videoTrack = await this.getLocalVideo();
		if (videoTrack) {
			await this.publishTrack(MediaStreamsKinds.Camera, videoTrack);
		} else {
			this.#releaseStream(MediaStreamsKinds.Camera);
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Camera])
		}

		const audioTrack = await this.getLocalAudio();
		if (audioTrack) {
			await this.publishTrack(MediaStreamsKinds.Microphone, audioTrack);
		} else {
			this.#releaseStream(MediaStreamsKinds.Microphone);
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Microphone])
		}
	}

	async socketOnMessageHandler(event) {
		if (typeof event.data !== 'string') return;

		let data

		try
		{
			data = JSON.parse(event.data);
		}
		catch (err)
		{
			this.setLog(`Could not parse a socket message: ${event.data}`);
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
				this.#privateProperties.isReconnecting = false;
				this.setLog(`Reconnected to a mediaserver after ${this.#privateProperties.reconnectionAttempt} attempts`);
				this.triggerEvents('Reconnected');
				this.#privateProperties.reconnectionAttempt = 0;
			}
			else
			{
				this.setLog(`Connected to room ${this.#privateProperties.roomId} on a mediaserver`);
				this.triggerEvents('Connected')
			}

			const partcipantsToDelete = {...this.#privateProperties.remoteParticipants};
			Object.values(data.joinResponse.otherParticipants).forEach( p => {
				if (partcipantsToDelete[p.userId])
				{
					delete partcipantsToDelete[p.userId];
				}
				this.setLog(`Adding an early connected participant with id ${p.userId} (sid: ${p.sid})`);
				this.#setRemoteParticipant(p);
			})

			for (let userId in partcipantsToDelete)
			{
				const participant = this.#privateProperties.remoteParticipants[userId];
				this.setLog(`Deleting a missing participant with id ${participant.userId} (sid: ${participant.sid})`);
				this.triggerEvents('ParticipantLeaved', [participant]);
				delete this.#privateProperties.remoteTracks[userId];
				delete this.#privateProperties.remoteParticipants[userId];
			}

			this.#privateProperties.pingIntervalDuration = data.joinResponse.pingInterval * 1000
			this.#privateProperties.pingTimeoutDuration = this.#privateProperties.pingIntervalDuration * 2
			this.#startPingInterval()
		} else if (data?.participantJoined) {
			this.setLog(`Adding a new participant with id ${data.participantJoined.participant.userId} (sid: ${data.participantJoined.participant.sid})`);
			this.#setRemoteParticipant(data.participantJoined.participant);
		} else if (data?.participantLeft) {
			const participantId = data?.participantLeft.userId;
			const participant = this.#privateProperties.remoteParticipants[participantId];
			if (participant)
			{
				this.setLog(`Deleting a participant with id ${participant.userId} (sid: ${participant.sid})`);
				this.triggerEvents('ParticipantLeaved', [participant]);
				delete this.#privateProperties.remoteTracks[participantId];
				delete this.#privateProperties.remoteParticipants[participantId];
			}
			else
			{
				this.setLog(`Got participantLeft signal for non-existent participant with id ${participantId} (sid: ${data.participantLeft.sid})`);
			}
		} else if (data?.trackCreated) {
			const participantId = data.trackCreated.userId;
			const cid = data.trackCreated.cid;
			const track = data.trackCreated.track;
			const trackId = track.sid;
			track.userId = participantId
			if (participantId === this.#privateProperties.myUserId) {
				const timeout = this.#privateProperties.pendingPublications[cid];
				if (timeout)
				{
					clearTimeout(this.#privateProperties.pendingPublications[cid]);
					delete this.#privateProperties.pendingPublications[cid];

					this.setLog(`Publishing a local track with kind ${track.source} succeed (sid: ${data.trackCreated.track.sid})`);
					this.#privateProperties.localTracks[track.source] = track
					this.triggerEvents('PublishSucceed', [track.source]);
					if (track.source === MediaStreamsKinds.Camera && this.#privateProperties.videoQueue)
					{
						this.#processVideoQueue();
					}
					return;
				}

				this.setLog(`Got a trackCreated signal for unknown publication ${cid} for track with kind ${track.source}`);
			} else {
				this.#privateProperties.tracksDataFromSocket[trackId] = track;
				const participant = this.#privateProperties.remoteParticipants[participantId];
				if (participant)
				{
					this.setLog(`Got a track info with kind ${track.source} (sid: ${data.trackCreated.track.sid}) for a participant with id ${participantId} (sid: ${participant.sid}), waiting for it`);
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
				else
				{
					this.setLog(`Got a track info with kind ${track.source} (sid: ${data.trackCreated.track.sid}) without a participant`);
				}
			}
		} else if (data?.trackDeleted) {
			try
			{
				const participantId = data?.trackDeleted.publisher
				if (participantId === this.#privateProperties.myUserId) return;
				this.setLog(`Start deleting a track with id ${data.trackDeleted.shortId} from ${participantId})`);
				const participant = this.#privateProperties.remoteParticipants[participantId];
				if (!participant)
				{
					this.setLog(`Deleting a track with id ${data.trackDeleted.shortId} failed: can't find a participant`);
					return
				}
				const trackId = data?.trackDeleted.shortId
				const track = Object.values(participant.tracks)?.find(track => track?.id === trackId);
				delete this.#privateProperties.tracksDataFromSocket[trackId];

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
					this.setLog(`Deleting a track with id ${data.trackDeleted.shortId} succeed`);
					this.triggerEvents('RemoteMediaRemoved', [participant, track]);
				}
				else
				{
					this.setLog(`Deleting a track with id ${data.trackDeleted.shortId} failed: can't find a track`);
				}
			}
			catch (e)
			{
				this.setLog(`Deleting a track with id ${data.trackDeleted.shortId} failed: ${e}`);
			}
		} else if (data?.trackMuted) {
			const participant = this.#privateProperties.remoteParticipants[data.trackMuted.track.publisher];
			if (data.trackMuted.track.publisher === this.#privateProperties.myUserId)
			{
				const track = Object.values(this.#privateProperties.localTracks)?.find(track => track?.sid === data.trackMuted.track.shortId);
				if (track)
				{
					if (track.source === MediaStreamsKinds.Camera)
					{
						if (data.trackMuted.muted && !track.muted)
						{
							this.triggerEvents('PublishEnded', [track.source]);
						}
						else if (!data.trackMuted.muted && track.muted)
						{
							this.triggerEvents('PublishSucceed', [track.source]);
						}
						else
						{
							this.triggerEvents('PublishPaused', [track.source]);
						}

						if (this.#privateProperties.videoQueue)
						{
							this.#processVideoQueue();
						}
					}
					else if(track.source === MediaStreamsKinds.Microphone)
					{
						this.triggerEvents('PublishPaused', [track.source, data.trackMuted.muted]);
					}
				}
				return;
			}

			if (!participant) {
				if (data.trackMuted.track.publisher != this.#privateProperties.myUserId)
				{
					this.setLog(`Got mute signal (${data.trackMuted.muted}) for a non-existent participant ${data.trackMuted.track.publisher}`);
				}
				return;
			}

			const track = Object.values(participant.tracks)?.find(track => track?.id === data.trackMuted.track.shortId);
			if (!track) {
				this.setLog(`Got mute signal (${data.trackMuted.muted}) for a non-existent track ${data.trackMuted.track.shortId}`);
				return;
			}

			if (track.source === MediaStreamsKinds.Microphone)
			{
				participant.isMutedAudio = data.trackMuted.muted;
				const eventName = data.trackMuted.muted
					? 'RemoteMediaMuted'
					: 'RemoteMediaUnmuted';
				this.setLog(`Got mute signal (${data.trackMuted.muted}) for ${participant.userId} (sid: ${participant.sid})`);
				this.triggerEvents(eventName, [participant, track]);
			}
			else if (track.source === MediaStreamsKinds.Camera)
			{
				participant.isMutedVideo = data.trackMuted.muted;
				const eventName = data.trackMuted.muted
					? 'RemoteMediaRemoved'
					: 'RemoteMediaAdded';
				this.setLog(`Got mute signal (${data.trackMuted.muted}) for ${participant.userId} (sid: ${participant.sid})`);
				this.triggerEvents(eventName, [participant, track]);
			}
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
		} else if (data.leave) {
			this.setLog(`got leave signal with ${data.leave.reason} reason`);
			if (data.leave.reason === 'CHANGING_MEDIA_SERVER')
			{
				this.#beforeDisconnect();
				this.#reconnect();
			}
		}
	};
	socketOnOpenHandler() {
		window.addEventListener('unload', this.sendLeaveBound)
	};

	socketOnCloseHandler(e) {
		this.#beforeDisconnect();

		if (e?.code && e?.code !== 1005)
		{
			this.setLog(`Socket closed with a code ${e.code}, reconnecting`);
			this.#reconnect();
		}
		else
		{
			this.setLog(`Socket closed with a code ${e.code}`);
		}
	};
	socketOnErrorHandler() {
		this.setLog(`Got a socket error`);
		if (!this.isConnected() && !this.#privateProperties.isReconnecting)
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

		if (state === 'failed' && !this.#privateProperties.isReconnecting)
		{
			this.setLog(`Peer connection state changed to 'failed', reconnecting`);
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
			this.setLog(`Ping signal was not received, reconnecting`);
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

	setBitrate(bitrate, MediaStreamKind) {
		this.setLog('Start setting bitrate');
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
			if(!params || !params.encodings || params.encodings.length === 0)
			{
				this.setLog('Setting bitrate failed: has no encodings in the sender parameters');
			}
			else
			{
				params.encodings.forEach(encoding => {
					if (isSimulcast) {
						encoding.maxBitrate = bitrate < this.#privateProperties.defaultSimulcastBitrate[encoding.rid] ? bitrate : this.#privateProperties.defaultSimulcastBitrate[encoding.rid]
					} else {
						encoding.maxBitrate = bitrate
					}
				})
				sender.setParameters(params);
				this.setLog('Setting bitrate succeed');
			}
		});
	}

	#addPendingPublication(trackId, source)
	{
		this.#privateProperties.pendingPublications[trackId] = setTimeout(() =>
		{
			delete this.#privateProperties.pendingPublications[trackId];
			this.triggerEvents('PublishFailed', [source]);
		}, this.#privateProperties.publicationTimeout);
	}

	async publishTrack(MediaStreamKind, MediaStreamTrack, StreamQualityOptions = {}) {
		if (!this.sender) {
			this.setLog(`Publishing a track before a peer connection was created, ignoring`);
			return;
		}

		this.setLog(`Start publishing a track with kind ${MediaStreamKind}`);

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

					const sender = this.#getSender(MediaStreamsKinds.Camera);
					if (sender)
					{
						await sender.replaceTrack(MediaStreamTrack);
						this.triggerEvents('PublishSucceed', [MediaStreamsKinds.Camera]);
						return;
					}
					else
					{
						this.sender.addTransceiver(MediaStreamTrack, {
							direction: 'sendonly',
							streams: [this.#privateProperties.cameraStream],
							sendEncodings: MediaStreamTrack.sendEncodings || [
								{ rid: 'q', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['q'], scaleResolutionDownBy: 4 },
								{ rid: 'h', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['h'], scaleResolutionDownBy: 2 },
								{ rid: 'f', active: true, maxBitrate: this.#privateProperties.defaultSimulcastBitrate['f'] },
							]
						});

						this.#addPendingPublication(MediaStreamTrack.id, source);

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
					}
				} else {
					const sender = this.#getSender(MediaStreamsKinds.Camera);
					if (sender)
					{
						await sender.replaceTrack(MediaStreamTrack);
						this.triggerEvents('PublishSucceed', [MediaStreamsKinds.Camera]);
						return;
					}
					else
					{
						this.sender.addTransceiver(MediaStreamTrack, {
							direction: 'sendonly'
						});
						this.setBitrate(this.#privateProperties.videoBitrate, MediaStreamsKinds.Camera)

						const width = MediaStreamTrack.getSettings().width
						const height = MediaStreamTrack.getSettings().height

						this.#addPendingPublication(MediaStreamTrack.id, source);

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
				}
			} else if (source === MediaStreamsKinds.Microphone) {
				const sender = this.#getSender(MediaStreamsKinds.Microphone);
				if (sender)
				{
					await sender.replaceTrack(MediaStreamTrack);
					this.triggerEvents('PublishSucceed', [MediaStreamsKinds.Microphone]);
					return;
				}
				else
				{
					this.sender.addTransceiver(MediaStreamTrack, {
						direction: 'sendonly'
					});

					this.#addPendingPublication(MediaStreamTrack.id, source);

					this.#sendSignal({
						"addTrack":  {
							"cid" : MediaStreamTrack.id,
							"source":  source
						}
					});
				}
			} else if (source === MediaStreamsKinds.Screen) {
				this.sender.addTransceiver(MediaStreamTrack, {
					direction: 'sendonly'
				});
				const width = MediaStreamTrack.getSettings().width
				const height = MediaStreamTrack.getSettings().height

				this.#addPendingPublication(MediaStreamTrack.id, source);

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
		}
		catch (e)
		{
			this.setLog(`Publishing a track with kind ${MediaStreamKind} failed: ${e}`);
			this.#releaseStream(MediaStreamKind);
			this.triggerEvents('PublishFailed', [MediaStreamKind])
		}
	}

	async changeStreamQuality(StreamQualityOptions) {
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
		this.setLog(`Start republishing a track with kind ${MediaStreamKind}`);
		await this.unpublishTrack(MediaStreamKind);
		const track = await this.getTrack(MediaStreamKind);
		if (track) {
			await this.publishTrack(MediaStreamKind, track);
		} else {
			this.setLog(`Republishing a track with kind ${MediaStreamKind} failed: ${error}`);
			this.#releaseStream(MediaStreamKind);
			this.triggerEvents('PublishFailed', [MediaStreamKind])
		}
	}

	async unpublishTrack(MediaStreamKind) {
		this.setLog(`Start unpublishing a track with kind ${MediaStreamKind}`);
		const sender = this.#getSender(MediaStreamKind);

		if (sender) {
			this.sender.removeTrack(sender);
			this.#privateProperties.offersStack++;
			await this.sendOffer();
			this.setLog(`Unpublishing a track with kind ${MediaStreamKind} succeed`);
		}
		else
		{
			this.setLog(`Unpublishing a track with kind ${MediaStreamKind} failed: has no sender for a track`);
		}
	}

	#changeRoomStreamsQuality(userId, kind) {
		this.setLog(`Start changing a streams quality`);
		Object.values(this.getParticipants()).forEach(p =>
		{
			let quality = this.#privateProperties.defaultRemoteStreamsQuality;
			if (userId)
			{
				const exactUser = userId == p.userId;
				quality = exactUser && kind === MediaStreamsKinds.Camera ? STREAM_QUALITY.HIGH : STREAM_QUALITY.MEDIUM;

				if (exactUser)
				{
					this.#privateProperties.mainStream = { userId, kind };
				}
			}
			else
			{
				this.#privateProperties.mainStream = {};
			}

			p.setStreamQuality(quality);
			this.setLog(`Quality of video for a participant with id ${p.userId} (sid: ${p.sid}) was changed to ${quality}`);
		})
	}

	hangup() {
		this.setLog(`Disconnecting from a call`);
		this.#sendLeave();
		this.#beforeDisconnect();
		this.#destroyPeerConnection();

		clearTimeout(this.#privateProperties.reconnectionTimeout);

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
		this.#privateProperties.reconnectionAttempt = 0;
		this.#privateProperties.mainStream = {};

		if (this.isConnected()) {
			this.#privateProperties.callState = CALL_STATE.TERMINATED
			this.triggerEvents('Disconnected')
		}
	}

	isConnected() {
		return this.#privateProperties.callState === CALL_STATE.CONNECTED
	}

	setMainStream(userId, kind) {
		this.setLog(`Setting main stream for a participant width id ${userId} (sid: ${this.#privateProperties.remoteParticipants[userId].sid})`);
		this.#changeRoomStreamsQuality(userId, kind);
	}

	resetMainStream() {
		this.setLog(`Resetting main stream`);
		this.#changeRoomStreamsQuality()
	}

	removeTrack(mediaStreamKind) {
		const trackSid = this.#privateProperties.localTracks[mediaStreamKind]?.sid
		if (trackSid)
		{
			delete this.#privateProperties.localTracks[mediaStreamKind];
			this.#sendSignal({
				removeTrack: {
					sid: trackSid
				}
			});
		}
	}

	pauseTrack(mediaStreamKind, keepTrack) {
		const trackSid = this.#privateProperties.localTracks[mediaStreamKind]?.sid;
		if (trackSid)
		{
			this.setLog(`Got pause signal (keep: ${keepTrack}) for a track with kind ${mediaStreamKind} (id: ${trackSid})`);
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
		else
		{
			this.setLog(`Got pause signal for a non-existent track with kind ${mediaStreamKind}`);
		}
	}

	unpauseTrack(mediaStreamKind) {
		const trackSid = this.#privateProperties.localTracks[mediaStreamKind]?.sid;
		if (trackSid)
		{
			this.setLog(`Got unpause signal for a track with kind ${mediaStreamKind} (id: ${trackSid})`);
			this.#sendSignal({
				mute: {
					sid: trackSid,
					muted: false
				}
			});
		}
		else
		{
			this.setLog(`Got unpause signal for a non-existent track with kind ${mediaStreamKind}`);
		}
	}

	disableAudio() {
		this.setLog(`Start disabling audio`);
		const track = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
		if (track)
		{
			track.enabled = false;
			this.pauseTrack(MediaStreamsKinds.Microphone, true);
		}
		else
		{
			this.setLog(`Disabling audio failed: has no track`);
		}
	}

	async enableAudio() {
		this.setLog(`Start enabling audio`);
		let track = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
		if (track && this.#privateProperties.localTracks[MediaStreamsKinds.Microphone])
		{
			this.setLog(`Enabling audio via unpause signal`);
			track.enabled = true;
			this.unpauseTrack(MediaStreamsKinds.Microphone);
		}
		else
		{
			track = await this.getLocalAudio();
			if (track)
			{
				this.setLog(`Enabling audio via publish`);
				track.enabled = true;
				await this.publishTrack(MediaStreamsKinds.Microphone, track);
			}
			else
			{
				this.setLog(`Enabling audio failed: has no track`);
				this.#releaseStream(MediaStreamsKinds.Microphone);
				this.triggerEvents('PublishFailed', [MediaStreamsKinds.Microphone]);
			}
		}
	}

	async disableVideo() {
		if (this.#privateProperties.isReconnecting)
		{
			return;
		}
		const hasQueue = this.#privateProperties.videoQueue !== VIDEO_QUEUE.INITIAL;
		this.#privateProperties.videoQueue = VIDEO_QUEUE.DISABLE;
		if (hasQueue)
		{
			return;
		}
		this.setLog(`Start disabling video`);
		const track = this.#privateProperties.cameraStream?.getVideoTracks()[0];
		if (track)
		{
			track.stop();
			this.#privateProperties.localTracks[MediaStreamsKinds.Camera].muted = true;
			this.pauseTrack(MediaStreamsKinds.Camera, true);
		}
		else
		{
			this.setLog(`Disabling video failed: has no track`);
		}
	}

	async enableVideo() {
		if (this.#privateProperties.isReconnecting)
		{
			return;
		}
		const hasQueue = this.#privateProperties.videoQueue !== VIDEO_QUEUE.INITIAL;
		this.#privateProperties.videoQueue = VIDEO_QUEUE.ENABLE;
		if (hasQueue)
		{
			return;
		}
		this.setLog(`Start enabling video`);
		let track = this.#privateProperties.cameraStream?.getVideoTracks()[0];
		if (track && this.#privateProperties.localTracks[MediaStreamsKinds.Camera])
		{
			track = await this.getLocalVideo();
			this.setLog(`Enabling video via unpause signal`);
			this.#privateProperties.localTracks[MediaStreamsKinds.Camera].muted = false;
			await this.publishTrack(MediaStreamsKinds.Camera, track);
			this.unpauseTrack(MediaStreamsKinds.Camera);
		}
		else
		{
			track = await this.getLocalVideo();
			if (track)
			{
				this.setLog(`Enabling video via publish`);
				await this.publishTrack(MediaStreamsKinds.Camera, track);
			}
			else
			{
				this.setLog(`Enabling video failed: has no track`);
				this.#releaseStream(MediaStreamsKinds.Camera);
				this.triggerEvents('PublishFailed', [MediaStreamsKinds.Camera]);
			}
		}
	}

	#processVideoQueue()
	{
		const videoQueue = this.#privateProperties.videoQueue;
		this.#privateProperties.videoQueue = VIDEO_QUEUE.INITIAL;
		if (videoQueue === VIDEO_QUEUE.ENABLE && this.#privateProperties.cameraStream?.getVideoTracks()[0].readyState !== 'live')
		{
			this.enableVideo();
		}
		else if (videoQueue === VIDEO_QUEUE.DISABLE && this.#privateProperties.cameraStream?.getVideoTracks()[0].readyState === 'live')
		{
			this.disableVideo();
		}
	}

	async startScreenShare() {
		this.setLog(`Start enabling screen sharing`);
		const track = await this.getLocalScreen()
		if (track) {
			await this.publishTrack(MediaStreamsKinds.Screen, track)
		} else {
			this.setLog(`Enabling screen sharing failed: has no track`);
			this.#releaseStream(MediaStreamsKinds.Screen);
			this.triggerEvents('PublishFailed', [MediaStreamsKinds.Screen])
		}
	}

	async stopScreenShare() {
		this.setLog(`Start disabling screen sharing`);
		this.#releaseStream(MediaStreamsKinds.Screen)
		this.removeTrack(MediaStreamsKinds.Screen)
		await this.unpublishTrack(MediaStreamsKinds.Screen)
	}

	sendMessage(message) {
		this.#sendSignal({ sendMessage: { message } });
	}

	raiseHand(raised) {
		this.#sendSignal({ raiseHand: { raised } });
	}

	async getLocalVideo() {
		if (this.#privateProperties.cameraStream?.getVideoTracks()[0].readyState !== 'live')
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
		this.setLog(`Start getting user media with options: ${JSON.stringify(options)}`);
		const constraints = {
			audio: false,
			video: false,
		}

		let stream = null

		try {
			if (options.video) {
				constraints.video = {
					width: { ideal: this.#privateProperties.defaultVideoResolution.width },
					height: { ideal: this.#privateProperties.defaultVideoResolution.height },
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
			this.setLog(`Getting user media with constraints: ${JSON.stringify(constraints)} succeed`);
		} catch (e) {
			this.setLog(`Getting user media  with constraints: ${JSON.stringify(constraints)} failed: ${e}`);
		} finally {
			this.triggerEvents('GetUserMediaEnded');
			return stream
		}
	}

	async #getDisplayMedia() {
		this.setLog(`Start getting display media`);
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
			this.setLog(`Getting display media succeed`);
		} catch (e) {
			this.setLog(`Getting display media failed: ${e}`);
		} finally {
			return stream;
		}
	}

	async getTrack(MediaStreamKind) {
		if (MediaStreamKind === MediaStreamsKinds.Camera && this.#privateProperties.cameraStream?.getVideoTracks().readyState !== 'live')
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
			const interrupted = MediaStreamKind === MediaStreamsKinds.Microphone;

			track.onended = () => {
				if (this.#privateProperties.localTracks[MediaStreamKind])
				{
					this.#privateProperties.localTracks[MediaStreamKind].muted = true;
				}
				this.triggerEvents('PublishEnded', [MediaStreamKind, interrupted]);
			};
		}

		return track;
	}

	async switchActiveAudioDevice(deviceId) {
		this.setLog(`Start switching an audio device`);
		return new Promise(async (resolve, reject) =>
		{
			this.#privateProperties.audioDeviceId = deviceId;
			try
			{
				const prevTrack = this.#privateProperties.microphoneStream?.getAudioTracks()[0];
				this.#privateProperties.microphoneStream = null;
				let prevTrackEnabledState = true;
				let prevTrackId = '';
				if (prevTrack)
				{
					prevTrackEnabledState = prevTrack.enabled;
					prevTrackId = prevTrack.id;
					prevTrack.stop();
				}
				const audioTrack = await this.getLocalAudio();
				audioTrack.source = MediaStreamsKinds.Microphone;
				audioTrack.enabled = prevTrackEnabledState;
				const sender = this.#getSender(MediaStreamsKinds.Microphone);
				if (
					sender
					&& (this.isAudioPublished() || sender.track.id !== audioTrack.id || audioTrack.id !== prevTrackId)
				)
				{
					this.setLog(`Have sender for audio, start replacing track`);
					await sender.replaceTrack(audioTrack);
					resolve();
				}
				this.setLog(`Switching an audio device succeed`);
			} catch (e)
			{
				this.setLog(`Switching an audio device failed: ${e}`);
				reject(e);
			}
		});

	}

	async switchActiveVideoDevice(deviceId) {
		this.setLog(`Start switching a video device`);
		return new Promise(async (resolve, reject) =>
		{
			this.#privateProperties.videoDeviceId = deviceId;
			try
			{
				const sender = this.#getSender(MediaStreamsKinds.Camera);
				if (sender && this.isVideoPublished())
				{
					this.setLog(`Have sender for video, start replacing track`);
					this.#privateProperties.cameraStream?.getVideoTracks()[0].stop();
					this.#privateProperties.cameraStream = null;
					const videoTrack = await this.getLocalVideo();
					videoTrack.source = MediaStreamsKinds.Camera;
					await sender.replaceTrack(videoTrack);
					resolve();
				}
				this.setLog(`Switching a video device succeed`);
			} catch (e)
			{
				this.setLog(`Switching a video device failed: ${e}`);
				reject(e);
			}
		});
	}

	isAudioPublished()
	{
		return this.#privateProperties.localTracks[MediaStreamsKinds.Microphone] && this.#privateProperties.localTracks[MediaStreamsKinds.Microphone]?.muted !== true;
	}

	isVideoPublished()
	{
		return this.#privateProperties.localTracks[MediaStreamsKinds.Camera] && this.#privateProperties.localTracks[MediaStreamsKinds.Camera]?.muted !== true;
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
			const _log = {
				timestamp: Math.floor(Date.now() / 1000),
				event: log,
			};
			const logLength = Object.values(this.#privateProperties.logs).length;
			this.#privateProperties.logs[logLength] = _log;
			let lastSentLog = 0;

			for (let index in this.#privateProperties.logs)
			{
				if (this.#sendLog(this.#privateProperties.logs[index]))
				{
					lastSentLog = index;
				}
				else
				{
					break;
				}
			}

			if (lastSentLog)
			{
				this.#privateProperties.logs = Object.values(this.#privateProperties.logs).slice(lastSentLog + 1);
			}

			if (this.#privateProperties.loggerCallback) {
				this.#privateProperties.loggerCallback();
			}
		}
	}
	#sendLog(log) {
		const signal = {
			sendLog: {
				userName: this.#privateProperties.userData.name,
				data: JSON.stringify(log)
			}
		};

		return this.isConnected() ? this.#sendSignal(signal) : false;
	}

	setLoggerCallback(callback) {
		this.#privateProperties.loggerCallback = callback;
	}

	enableSilentLogging(enable) {
		this.#privateProperties.isloggingEnable = enable;
	}

	async #answerHandler(data) {
		this.setLog(`Start handling a remote answer`);
		let hasError = false;
		try
		{
			await this.sender.setRemoteDescription(data.answer);
			this.#privateProperties.pendingCandidates.sender.forEach((candidate) =>
			{
				this.sender.addIceCandidate(candidate);
				this.setLog(`Added a deferred ICE candidate`);
			});
			this.#privateProperties.pendingCandidates.sender = [];
		}
		catch (e)
		{
			this.setLog(`Handling a remote answer failed: ${e}`);
			hasError = true;
		}
		finally
		{
			if (!hasError)
			{
				this.setLog(`Handling a remote answer succeed`);
			}
			this.#privateProperties.isWaitAnswer = false;
			await this.sendOffer();
		}
	}

	async #offerHandler(data) {
		this.setLog(`Handling a remote offer`);
		try
		{
			await this.recipient.setRemoteDescription(data.offer);
			this.#privateProperties.pendingCandidates.recipient.forEach((candidate) =>
			{
				this.recipient.addIceCandidate(candidate);
				this.setLog(`Added a deferred ICE candidate`);
			});
			this.#privateProperties.pendingCandidates.recipient = [];
			const answer = await this.recipient.createAnswer();
			await this.recipient.setLocalDescription(answer);
			this.#sendSignal({ answer });
			this.setLog(`Handling a remote offer succeed`);
		}
		catch (e)
		{
			this.setLog(`Handling a remote offer failed: ${e}`);
		}
	}

	#addIceCandidate(trickle) {
		this.setLog(`Start adding an ICE candidate`);
		try
		{
			const candidate = JSON.parse(trickle.candidateInit);

			if (trickle.target)
			{
				if (this.recipient.remoteDescription)
				{
					this.recipient.addIceCandidate(candidate);
					this.setLog(`Adding an ICE candidate succeed`);
					return;
				}

				this.#privateProperties.pendingCandidates.recipient.push(candidate);
				this.setLog(`Adding an ICE candidate deferred: has no remote description`);

			}
			else
			{
				if (this.sender.remoteDescription)
				{
					this.sender.addIceCandidate(candidate);
					this.setLog(`Adding an ICE candidate succeed`);
					return;
				}

				this.#privateProperties.pendingCandidates.sender.push(candidate);
				this.setLog(`Adding an ICE candidate deferred: has no remote description`);
			}
		}
		catch (e)
		{
			this.setLog(`Adding an ICE candidate failed: ${e}`);
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
				if (track.muted && track.source === MediaStreamsKinds.Camera)
				{
					remoteParticipant.isMutedVideo = true;
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
			const trackId = ids[1];
			const userId = this.#privateProperties.tracksDataFromSocket[trackId]?.userId
			event.track.source = this.#privateProperties.tracksDataFromSocket[trackId]?.source
			event.track.layers = this.#privateProperties.tracksDataFromSocket[trackId]?.layers || null;
			this.#privateProperties.realTracksIds[event.track.id] = trackId;
			const participant = this.#privateProperties.remoteParticipants[userId];
			// A track without participant, we should skip it
			if (!participant) {
				this.setLog(`Got a track with kind ${event.track.source} (sid: ${trackId}) without a participant`);
				return;
			}
			if (!this.#privateProperties.remoteTracks?.[userId]) {
				this.#privateProperties.remoteTracks[userId] = {}
			}
			const track = new Track(trackId, event.track);
			this.#privateProperties.remoteTracks[userId][trackId] = track;

			if (track) {
				this.setLog(`Got an expected track with kind ${event.track.source} (sid: ${trackId}) for a participant with id ${participant.userId} (sid: ${participant.sid})`);
				participant.addTrack(event.track.source, track);
				if (event.track.source !== MediaStreamsKinds.Camera || !participant.isMutedVideo)
				{
					this.triggerEvents('RemoteMediaAdded', [participant, track]);
				}
			}

			if (event.track.source === MediaStreamsKinds.Camera) {
				const exactUser = this.#privateProperties.mainStream.userId == userId;
				const exactTrack = this.#privateProperties.mainStream.kind === event.track.source;

				let quality = STREAM_QUALITY.LOW;
				if (exactUser && (exactTrack || !participant.screenSharingEnabled))
				{
					quality = STREAM_QUALITY.HIGH;
				}
				else if (!this.#privateProperties.mainStream.userId)
				{
					quality = this.#privateProperties.defaultRemoteStreamsQuality;
				}
				this.setLog(`Quality of video for a participant with id ${participant.userId} (sid: ${participant.sid}) was changed to ${quality} after receiving`);
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
					const codecs = {};
					const reportsWithoutCodecs = {};

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

						if (report.type === 'codec') {
							codecs[report.id] = report.mimeType;
							if (reportsWithoutCodecs[report.id])
							{
								reportsWithoutCodecs[report.id].forEach(r =>
								{
									r.codecName = report.mimeType;
								});
								delete reportsWithoutCodecs[report.id];
							}
						}

						if (report.type === 'inbound-rtp' && report.kind === 'video')
						{
							const realTrackId = this.#privateProperties.realTracksIds[report.trackIdentifier];
							const track = this.#privateProperties.tracksDataFromSocket[realTrackId];
							if (track)
							{
								const prevReport = track.report || {};
								track.report = report;

								const bytes = report.bytesReceived - (prevReport.bytesReceived || 0);
								const time = report.timestamp - (prevReport.timestamp || 0);
								const bitrate = 8 * bytes / (time / 1000);
								report.bitrate = bitrate < 0 ? 0 : Math.trunc(bitrate);
								report.userId = track.userId;
								report.source = track.source;
								if (codecs[report.codecId])
								{
									report.codecName = codecs[report.codecId];
								}
								else
								{
									if (reportsWithoutCodecs[report.codecId])
									{
										reportsWithoutCodecs[report.codecId].push(report);
									}
									else
									{
										reportsWithoutCodecs[report.codecId] = [report];
									}
								}
							}
						}
					});

					statsAll.recipient = statsOutput;
					if (participantsWithLargeDataLoss.size || this.#privateProperties.prevParticipantsWithLargeDataLoss.size)
					{
						this.setLog(`Have high packetsLost on users: ${[...participantsWithLargeDataLoss]}`);
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
			return true;
		}

		return false;
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
	cameraStreamQuality: STREAM_QUALITY.MEDIUM;

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
		if (this.cameraStreamQuality === quality)
		{
			return;
		}

		if (this.tracks?.[MediaStreamsKinds.Camera]) {
			this.cameraStreamQuality = quality;
			const trackId = this.tracks[MediaStreamsKinds.Camera].id;
			this.tracks[MediaStreamsKinds.Camera].track.currentVideoQuality =  quality;
			const signal = {
				trackSetting: {
					trackSids: [trackId],
					quality: quality,
				}
			};

			this.#socketConnect.send(JSON.stringify(signal));
		}
	}
}