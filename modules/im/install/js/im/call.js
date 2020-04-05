(function()
{
	var janus = null;
	var roomId = null;

	var Publisher = function(config)
	{
		this.mediaState = {
			video: null,
			audio: null
		};

		this.webrtcState = null;

		this.callbacks = {
			onAttached: (BX.type.isFunction(config.onAttached) ? config.onAttached : null),
			onDetached: (BX.type.isFunction(config.onDetached) ? config.onDetached : null),
			onRoomJoined: (BX.type.isFunction(config.onRoomJoined) ? config.onRoomJoined : null),
			onRoomLeft: (BX.type.isFunction(config.onRoomLeft) ? config.onRoomLeft : null),
			onError: (BX.type.isFunction(config.onError) ? config.onError : null),
			onRemoteFeed: (BX.type.isFunction(config.onRemoteFeed) ? config.onRemoteFeed : null),
			onDestroyed: (BX.type.isFunction(config.onDestroyed) ? config.onDestroyed : null),
			onPublisherLeft: (BX.type.isFunction(config.onPublisherLeft) ? config.onPublisherLeft : null),
			onWebrtcState: (BX.type.isFunction(config.onWebrtcState) ? config.onWebrtcState : null),
		};

		this.userId = config.userId;

		this.pluginHandle = null;
		this.localStream = config.stream;

		this.init();
	};

	Publisher.prototype.init = function()
	{
		this.attach();
	};

	Publisher.prototype.attach = function()
	{
		var self = this;
		janus.attach({
			plugin: 'janus.plugin.videoroom',
			success: this.onAttached.bind(this),
			error: this.onError.bind(this),
			consentDialog: function() {console.log('consentDialog??')},
			webrtcState: function(state) {
				self.webrtcState = state;
			},
			mediaState: function (media, state) {
				if(self.mediaState.hasOwnProperty(media))
				{
					self.mediaState[media] = state;
				}
			},
			onmessage: this.onMessage.bind(this),
			onlocalstream: function() {console.log('onlocalstream')},
			onremotestream: function() {console.log('onremotestream')},
			oncleanup: function() {console.log('oncleanup')},
			ondetached: function() {console.log('Publisher detached')}
		})
	};

	Publisher.prototype.onAttached = function(pluginHandle)
	{
		this.pluginHandle = pluginHandle;
		console.log("Plugin attached! (" + pluginHandle.getPlugin() + ", id=" + pluginHandle.getId() + ")");
		console.log("  -- This is a publisher/manager");

		this.joinRoom();
	};

	Publisher.prototype.onError = function(error)
	{
		if(BX.type.isFunction(this.callbacks.onError))
		{
			this.callbacks.onError({
				target: this,
				errorCode: 0,
				error: error
			});
		}
	};

	Publisher.prototype.onMessage = function(msg, jsep)
	{
		console.log('Received message:', msg);
		var event = msg["videoroom"];
		if (event != undefined && event != null)
		{
			if (event === "joined")
			{
				// Publisher/manager created, negotiate WebRTC and attach to existing feeds, if any
				myid = msg["id"];
				console.log("Successfully joined room " + msg["room"] + " with ID " + myid);
				if(this.localStream)
				{
					this.publishStream();
				}

				if (msg["publishers"] !== undefined && msg["publishers"] !== null)
				{
					if(BX.type.isFunction(this.callbacks.onRemoteFeed))
						this.callbacks.onRemoteFeed(msg["publishers"]);
				}
			}
			else if (event === "destroyed")
			{
				console.log("The room has been destroyed!");
				if(BX.type.isFunction(this.callbacks.onDestroyed))
				{
					this.callbacks.onDestroyed();
				}
			}
			else if (event === "event")
			{
				// Any new feed to attach to?
				if (msg["publishers"] !== undefined && msg["publishers"] !== null)
				{
					if(BX.type.isFunction(this.callbacks.onRemoteFeed))
					{
						this.callbacks.onRemoteFeed(msg["publishers"]);
					}
				}
				else if (msg["leaving"] !== undefined && msg["leaving"] !== null)
				{
					// One of the publishers has gone away?
					if(BX.type.isFunction(this.callbacks.onPublisherLeft))
					{
						this.callbacks.onPublisherLeft(msg["leaving"]);
					}
				}
				else if (msg["unpublished"] !== undefined && msg["unpublished"] !== null)
				{
					// One of the publishers has unpublished?
					var unpublished = msg["unpublished"];
					Janus.log("Publisher left: " + unpublished);
					if (unpublished === 'ok')
					{
						// That's us
						this.pluginHandle.hangup();

						return;
					}
					else
					{
						if(BX.type.isFunction(this.callbacks.onPublisherLeft))
						{
							this.callbacks.onPublisherLeft(msg["unpublished"]);
						}
					}
				} else if (msg["error"] !== undefined && msg["error"] !== null)
				{
					if(BX.type.isFunction(this.callbacks.onError))
					{
						this.callbacks.onError({
							target: this,
							errorCode: msg["error_code"],
							error: msg["error"]
						});
					}
				}
			}
		}
		if (jsep !== undefined && jsep !== null)
		{
			Janus.debug("Handling SDP as well...");
			Janus.debug(jsep);
			this.pluginHandle.handleRemoteJsep({jsep: jsep});
		}
	};

	Publisher.prototype.joinRoom = function()
	{
		var self = this;
		return new Promise(function(resolve, reject)
		{
			var request = { "request": "join", "room": roomId, "ptype": "publisher", "display": 'user' + self.userId };
			self.pluginHandle.send({
				message: request,
				success: function(data) {resolve(data)},
				error: function(error) {reject(error)}
			});
		});
	};

	Publisher.prototype.publishStream = function()
	{
		var self = this;
		this.pluginHandle.createOffer({
			media: { audioRecv: false, videoRecv: false, audioSend: true, videoSend: true},	// Publishers are sendonly
			stream: self.localStream,
			success: function(jsep)
			{
				Janus.debug("Got publisher SDP!");
				Janus.debug(jsep);
				var publish = { "request": "configure", "audio": true, "video": true };
				self.pluginHandle.send({"message": publish, "jsep": jsep});
			},
			error: function(error)
			{
				Janus.error("WebRTC error:", error);
			}
		});
	};

	Publisher.prototype.unpublishStream = function()
	{
		var message = { "request": "unpublish"};
		this.pluginHandle.send({message: message});
	};
	
	Publisher.prototype.changeStream = function(stream)
	{
		var self = this;
		return new Promise(function(resolve, reject)
		{
			// straughtforward hangup works slightly faster :)
			//self.unpublishStream();
			self.pluginHandle.hangup();
			setTimeout(function()
			{
				self.localStream = stream;
				self.publishStream();
				return resolve();
			}, 1000);
		})
	};

	Publisher.prototype.dispose = function()
	{
		if(this.pluginHandle)
		{
			this.pluginHandle.hangup();
			this.pluginHandle.detach();
		}

		this.pluginHandle = null;
		this.localStream = null;
	}

	Receiver = function(config)
	{
		this.feedId = config.feedId;
		this.userId = config.userId;

		this.webrtcState = null;

		this.pluginHandle = null;
		this.stream = null;

		this.callbacks = {
			onRemoteStream: (BX.type.isFunction(config.onRemoteStream) ? config.onRemoteStream : null)
		};

		this.init();
	};

	Receiver.prototype.init = function()
	{
		this.attach();
	};

	Receiver.prototype.attach = function()
	{
		var self = this;
		janus.attach({
			plugin: "janus.plugin.videoroom",
			success: this.onAttached.bind(this),
			error: this.onError.bind(this),
			webrtcState: function(state) {
				self.webrtcState = state;
			},

			onmessage: this.onMessage.bind(this),
			onlocalstream: function(stream) { /*The subscriber stream is recvonly, we don't expect anything here*/ },
			onremotestream: this.onRemoteStream.bind(this),
			oncleanup: function() {console.log('oncleanup')}
		});
	};

	Receiver.prototype.onAttached = function(pluginHandle)
	{
		this.pluginHandle = pluginHandle;

		this.joinRoom();
	};

	Receiver.prototype.onMessage = function(msg, jsep)
	{
		var self = this;
		Janus.debug(" ::: Got a message (listener) :::");
		Janus.debug(JSON.stringify(msg));
		var event = msg["videoroom"];
		Janus.debug("Event: " + event);
		if (event != undefined && event != null)
		{
			if (event === "attached")
			{
				console.log("Successfully attached to feed ", msg);
			}
			else if (msg["error"] !== undefined && msg["error"] !== null)
			{
				console.log('Receiver error: ', msg["error"]);
			}
			else
			{
				console.log('Empty message from media gateway');
			}
		}
		if (jsep !== undefined && jsep !== null)
		{
			Janus.debug("Handling SDP as well...");
			Janus.debug(jsep);
			// Answer and attach
			this.pluginHandle.createAnswer({
				jsep: jsep,
				media: {audioRecv: true, videoRecv: true, audioSend: false, videoSend: false},	// We want recvonly audio/video
				success: function (jsep)
				{
					Janus.debug("Got SDP!");
					Janus.debug(jsep);
					var body = {"request": "start", "room": roomId};
					self.pluginHandle.send({"message": body, "jsep": jsep});
				},
				error: function (error)
				{
					console.log("WebRTC error:", error);
				}
			});
		}
	};

	Receiver.prototype.onRemoteStream = function(stream)
	{
		this.stream = stream;
		console.log('remote stream received');
		var event = {
			target: this,
			stream: stream
		};

		if(BX.type.isFunction(this.callbacks.onRemoteStream))
			this.callbacks.onRemoteStream(event);
	};

	Receiver.prototype.onError = function(error)
	{
		console.log('Receiver error: ', error);
	};

	Receiver.prototype.joinRoom = function()
	{
		console.log('feed: ', this.feedId);
		var request = {
			request: "join",
			room: roomId,
			ptype: "listener",
			feed: this.feedId
		};

		this.pluginHandle.send({message: request});
	};

	Receiver.prototype.dispose = function()
	{
		this.stream = null;
		if(this.pluginHandle)
		{
			this.pluginHandle.hangup();
			this.pluginHandle.detach();
		}
	};

	var CallView = function(config)
	{
		this.server = config.server;
		this.apiSecret = config.apiSecret;

		roomId = config.roomId;

		this.elements = {
			root: BX.type.isDomNode(config.element) ? config.element : null,
			errors: null,
			main: null,
			mainPlaceholder: null,
			self: {
				main: null,
				video: null
			},
			receivers: {},
			buttons: {
				mic: {
					button: null,
					icon: null
				} ,
				camera: {
					button: null,
					icon: null
				},
				connect: {
					button: null,
					icon: null
				},
				log: {
					button: null,
					icon: null
				},
				signout: {
					button: null,
					icon: null
				}
			},
			hardware: {
				main: null,
				mic: null,
				camera: null,
				resolution: null
			},
			log: null
		};

		this.chatId = config.chatId;
		this.userCount = config.userCount;
		this.userId = config.userId;
		this.userDetails = config.userDetails;

		this.publisher = null;
		this.receivers = [];

		this.localStream = null;
		this.hardware = this.getDefaultHardware();

		this.state = {
			mic: true,
			camera: true,
			connect: true,
			selectMic: true,
			selectCamera: true,
			log: false
		};

		this.resolutions = {
			QVGA: {
				description: 'QVGA (320x240)',
				width: {max: 320, min: 320},
				height: {max: 240, min: 240}
			},
			VGA: {
				description: 'VGA (640x480)',
				width: {max: 640, min: 320, ideal: 640},
				height: {max: 480, min: 240, ideal: 480}
			},
			HD: {
				description: 'HD (1280x720)',
				width: {max: 1280, min: 320, ideal: 1280},
				height: {max: 720, min: 240, ideal: 720}
			}
		};

		this.logText = '';

		this.init();
	};

	CallView.prototype.init = function()
	{
		this.render();
		this.bindEvents();
		this.elements.self.video.volume = 0;
		Janus.init({
			debug: false,
			callback: this.onJanusInited.bind(this)
		});
	};

	CallView.prototype.bindEvents = function()
	{
		this.elements.hardware.mic.addEventListener('change', this.onChangeMicrophone.bind(this));
		this.elements.hardware.camera.addEventListener('change', this.onChangeCamera.bind(this));
		this.elements.hardware.resolution.addEventListener('change', this.onChangeResolution.bind(this));

		this.elements.buttons.mic.button.addEventListener('click', this.onMicClick.bind(this));
		this.elements.buttons.camera.button.addEventListener('click', this.onCameraClick.bind(this));
		this.elements.buttons.log.button.addEventListener('click', this.onLogClick.bind(this));
		this.elements.buttons.signout.button.addEventListener('click', this.onSignOutClick.bind(this));

		//this.elements.buttons.connect.button.addEventListener('click', this.onConnectClick.bind(this));
	};

	CallView.prototype.getDefaultHardware = function()
	{
		return {
			mic: localStorage.getItem('im-call-prototype-hardware-mic'),
			camera: localStorage.getItem('im-call-prototype-hardware-camera'),
			resolution: localStorage.getItem('im-call-prototype-hardware-resolution') || 'HD'
		}
	};

	CallView.prototype.showLocalVideo = function()
	{
		BX.removeClass(this.elements.self.main, 'im-call-hidden');
	};

	CallView.prototype.showHardwareSettings = function()
	{
		BX.removeClass(this.elements.hardware.main, 'im-call-hidden');
	};

	CallView.prototype.onJanusInited = function()
	{
		this.log(BX.message('IM_CALL_CONNECTING'));
		janus = new Janus({
			server: this.server,
			apisecret: this.apiSecret,
			success: this.onJanusConnected.bind(this),
			error: this.onJanusError.bind(this),
			destroyed: function(){ console.log('something went terribly wrong'); }
		});
	};

	CallView.prototype.onJanusConnected = function()
	{
		this.log(BX.message('IM_CALL_CONNECTED'));
		var self = this;
		this.createLocalStream().then(
			function()
			{
				attachMediaStream(self.elements.self.video, self.localStream);
				return self.fillHardware();
			},
			function(error)
			{
				self.showError(BX.message('IM_CALL_ERROR_HARDWARE') + ' ' + error);
				return self.fillHardware();
			}
		).then(function()
		{
			self.showHardwareSettings();

			if(self.localStream)
			{
				self.showLocalVideo();
				self.log(BX.message('IM_CALL_PUBLISHING'));
			}

			self.publisher = new Publisher({
				userId: self.userId,
				stream: self.localStream,
				onRemoteFeed: self.onRemoteFeed.bind(self),
				onAttached: self.onPublisherAttached.bind(self),
				onDetached: function() {self.log(BX.message('IM_CALL_PUBLISHING_STOPPED'));},
				onPublisherLeft: self.onPublisherLeft.bind(self),
				onError: function (e)
				{
					self.showError(BX.message('IM_CALL_PUBLISHING_ERROR') + ': ' + e.error);
				},
				onWebrtcState: function()
				{

				}

			});
		});
	};

	CallView.prototype.onPublisherAttached = function()
	{

	};

	CallView.prototype.onJanusError = function(error)
	{
		this.showError(BX.message('IM_CALL_ERROR_CONNECTION') + ' ' + error);
	};

	CallView.prototype.createLocalStream = function()
	{
		var self = this;
		this.log(BX.message('IM_CALL_HARDWARE_REQUEST'));
		return new Promise(function(resolve, reject)
		{
			navigator.mediaDevices.getUserMedia(self.getMediaConstraints()).then(
				function (stream)
				{
					self.log(BX.message('IM_CALL_ACCESS_GRANTED'));
					self.localStream = stream;
					return resolve(stream);
				},
				function (error)
				{
					self.log(BX.message('IM_CALL_ERROR_HARDWARE') + ' ' + error);
					reject(error);
				}
			);
		});
	};

	CallView.prototype.fillHardware = function()
	{
		var self = this;

		var videoTrackLabel = (function()
		{
			if(!self.localStream)
				return '';

			var videoTracks = self.localStream.getVideoTracks();
			if(videoTracks.length > 0 && videoTracks[0].label)
				return videoTracks[0].label;
			else
				return '';
		})();
		var audioTrackLabel = (function()
		{
			if(!self.localStream)
				return '';
			var audioTracks = self.localStream.getAudioTracks();
			if(audioTracks.length > 0 && audioTracks[0].label)
				return audioTracks[0].label;
			else
				return '';
		})();

		return new Promise(function(resolve, reject)
		{
			var option;
			self.elements.hardware.camera.options.length = 0;
			self.elements.hardware.mic.options.length = 0;
			self.elements.hardware.resolution.options.length = 0;

			for(resolution in self.resolutions)
			{
				option = BX.create('option', {text: self.resolutions[resolution].description, attrs:{value: resolution}});
				if(resolution === self.hardware.resolution)
				{
					option.selected = true;
				}
				self.elements.hardware.resolution.options.add(option);
			}

			navigator.mediaDevices.enumerateDevices().then(function(devices)
			{
				devices.forEach(function (device)
				{
					var option;
					if(device.label == '')
						return;

					if(device.kind === 'audioinput' || device.deviceId === self.hardware.mic)
					{
						option = BX.create('option', {text: device.label, attrs:{value: device.deviceId}});

						if(device.label === audioTrackLabel)
						{
							option.selected = true;
						}
						self.elements.hardware.mic.options.add(option);
					}
					else if(device.kind === 'videoinput')
					{
						option = BX.create('option', {text: device.label, attrs:{value: device.deviceId}});
						if(device.label === videoTrackLabel || device.deviceId == self.hardware.camera)
						{
							option.selected = true;
						}
						self.elements.hardware.camera.options.add(option);

					}

				});
				return resolve();
			}).catch(function(error)
			{
				return reject(error);
			})

		});
	};

	CallView.prototype.getMediaConstraints = function()
	{
		var constraints = {
			audio: {},
			video: false
		};

		if(this.hardware.mic)
		{
			constraints.audio.deviceId = {exact: this.hardware.mic};
		}

		if(this.state.camera)
		{
			constraints.video = {};
			if(this.hardware.camera)
			{
				constraints.video.deviceId = {exact: this.hardware.camera};
			}

			if(this.resolutions[this.hardware.resolution])
			{
				constraints.video.width = this.resolutions[this.hardware.resolution].width;
				constraints.video.height = this.resolutions[this.hardware.resolution].height;
			}
		}

		return constraints;
	};

	CallView.prototype.onRemoteFeed = function(feeds)
	{
		for(var feedIndex in feeds)
		{
			if(feeds.hasOwnProperty(feedIndex))
			{
				this.attachRemoteFeed(feeds[feedIndex]);
			}
		}
	};

	CallView.prototype.onPublisherLeft = function(feedId)
	{
		var self = this;
		console.log('onPublisherLeft: ', feedId);
		this.receivers.forEach(function(receiver, index)
		{
			if(receiver.feedId == feedId)
			{
				self.log(self.getUserName(receiver.userId) + ' ' + BX.message('IM_CALL_USER_DISCONNECTED'));
				receiver.dispose();
				self.receivers.splice(index, 1);
			}
		});

		if(self.elements.receivers[feedId])
		{
			BX.cleanNode(self.elements.receivers[feedId].main, true);
			delete(self.elements.receivers[feedId]);

			if(this.receivers.length == 0)
				BX.removeClass(this.elements.mainPlaceholder, 'im-call-hidden');
		}
	};

	CallView.prototype.onRemoteStream = function(e)
	{
		if(e.target)
		{
			this.renderReceiver(e.target);
		}
	};

	CallView.prototype.onChangeMicrophone = function(e)
	{
		var mic = e.target.value;
		localStorage.setItem('im-call-prototype-hardware-mic', mic);
		this.hardware.mic = mic;
		this.reapplyConstraints();
	};

	CallView.prototype.onChangeCamera = function(e)
	{
		var camera = e.target.value;
		this.hardware.camera = camera;
		localStorage.setItem('im-call-prototype-hardware-camera', camera);
		this.reapplyConstraints();
	};

	CallView.prototype.onChangeResolution = function(e)
	{
		var resolution = e.target.value;
		if(!this.resolutions[resolution])
			return;

		this.hardware.resolution = resolution;
		localStorage.setItem('im-call-prototype-hardware-resolution', resolution);
		this.reapplyConstraints();
	};

	CallView.prototype.onMicClick = function(e)
	{
		this.setMicState(!this.state.mic);
	};

	CallView.prototype.onCameraClick = function(e)
	{
		this.setCameraState(!this.state.camera);
	};

	CallView.prototype.onConnectClick = function(e)
	{

	};

	CallView.prototype.onLogClick = function(e)
	{
		this.setLogState(!this.state.log);
	};

	CallView.prototype.onSignOutClick = function(e)
	{
		this.dispose();
	};

	CallView.prototype.setMicState = function(state)
	{
		if(!this.localStream || !this.publisher.webrtcState)
			return;

		state = (state == true);
		this.state.mic = state;
		if(state)
		{
			this.publisher.pluginHandle.unmuteAudio();
		}
		else
		{
			this.publisher.pluginHandle.muteAudio();
		}

		this.renderButtons()
	};

	CallView.prototype.setCameraState = function(state)
	{
		state = (state == true);
		this.state.camera = state;

		if(this.localStream)
		{
			this.reapplyConstraints();
		}
		this.renderButtons();
	};

	CallView.prototype.setLogState = function(state)
	{
		state = (state == true);
		this.state.log = state;

		if(this.state.log)
			BX.removeClass(this.elements.log, 'im-call-hidden');
		else
			BX.addClass(this.elements.log, 'im-call-hidden');
	};

	CallView.prototype.reapplyConstraints = function()
	{
		var self = this;
		this.elements.self.video.src = null;
		this.elements.self.video.pause();
		BX.showWait(this.elements.self.main);
		self.state.selectCamera = false;
		self.state.selectMic = false;
		self.renderButtons();
		BX.webrtc.stopMediaStream(this.localStream);
		this.localStream = null;
		this.publisher.unpublishStream();

		this.createLocalStream().then(function()
		{
			self.publisher.changeStream(self.localStream).then(function()
			{
				BX.closeWait(self.elements.self.main);

				self.state.selectCamera = self.state.camera;
				self.state.selectMic = true;
				self.renderButtons();
				attachMediaStream(self.elements.self.video, self.localStream);
			});
		}).catch(function(error)
		{
			self.showError('IM_CALL_ERROR_HARDWARE' + ' ' + error);
			BX.closeWait(self.elements.self.main);

			self.state.selectCamera = self.state.camera;
			self.state.selectMic = true;
			self.renderButtons();

		});
	};

	CallView.prototype.attachRemoteFeed = function(feedParams)
	{
		var id = feedParams.id;
		var display = feedParams.display || '';
		var userId;

		if (display.search('user') === 0)
			userId = display.substring(4);

		this.log(this.getUserName(userId) + ' ' + BX.message('IM_CALL_USER_CONNECTED'));

		var receiver = new Receiver({
			feedId: id,
			userId: userId,
			onRemoteStream: this.onRemoteStream.bind(this)
		});

		var receiverExists = this.receivers.some(function(element)
		{
			return (element.feedId == id);
		});

		if(!receiverExists)
		{
			this.receivers.push(receiver);
		}

		this.renderReceiver(receiver);
	};

	CallView.prototype.renderReceiver = function(receiver)
	{
		if(!this.elements.receivers[receiver.feedId])
		{
			this.elements.receivers[receiver.feedId] = this.createReceiverNode(receiver);
			this.elements.main.appendChild(this.elements.receivers[receiver.feedId].main);
			BX.addClass(this.elements.mainPlaceholder, 'im-call-hidden');
		}

		this.elements.receivers[receiver.feedId].caption.innerText = this.getUserName(receiver.userId);
		this.elements.receivers[receiver.feedId].resolution.innerText = '';
		this.elements.receivers[receiver.feedId].bitrate.innerText = '';
		if(receiver.stream)
		{
			attachMediaStream(this.elements.receivers[receiver.feedId].streamVideo, receiver.stream);
			if(receiver.stream.getVideoTracks().length > 0)
			{
				BX.addClass(this.elements.receivers[receiver.feedId].avatar, 'im-call-hidden');
				BX.removeClass(this.elements.receivers[receiver.feedId].stream, 'im-call-hidden');
			}
			else
			{
				BX.removeClass(this.elements.receivers[receiver.feedId].avatar, 'im-call-hidden');
				BX.addClass(this.elements.receivers[receiver.feedId].stream, 'im-call-hidden');
			}
		}
		else
		{
			this.elements.receivers[receiver.feedId].avatarImg.src = this.getUserAvatar(receiver.userId, true);
			this.elements.receivers[receiver.feedId].streamVideo.src = '';
			BX.removeClass(this.elements.receivers[receiver.feedId].avatar, 'im-call-hidden');
			BX.addClass(this.elements.receivers[receiver.feedId].stream, 'im-call-hidden');
		}
	};

	CallView.prototype.renderButtons = function()
	{
		if(this.state.mic)
			BX.removeClass(this.elements.buttons.mic.icon, 'im-call-button-disabled');
		else
			BX.addClass(this.elements.buttons.mic.icon, 'im-call-button-disabled');

		if(this.state.camera)
		{
			BX.removeClass(this.elements.buttons.camera.icon, 'im-call-button-disabled');
			BX.removeClass(this.elements.self.main, 'im-call-hidden');
			this.elements.hardware.camera.disabled = false;
		}
		else
		{
			BX.addClass(this.elements.buttons.camera.icon, 'im-call-button-disabled');
			BX.addClass(this.elements.self.main, 'im-call-hidden');
			this.elements.hardware.camera.disabled = true;
		}

		if(this.state.connect)
			BX.removeClass(this.elements.buttons.connect.icon, 'im-call-button-disabled');
		else
			BX.addClass(this.elements.buttons.connect.icon, 'im-call-button-disabled');
	};

	CallView.prototype.showBitrate = function(receiver, bitrate)
	{

	};

	CallView.prototype.showResolution = function(receiver, resolution)
	{

	};

	CallView.prototype.createReceiverNode = function(receiver)
	{
		var streamNode, videoNode, captionNode, resolutionNode, bitrateNode, avatarNode, avatarImg, fullScreenNode;

		var mainNode = BX.create('div', {props: {className: 'im-video-other'}, children: [
			streamNode = BX.create('div', {props: {className: 'im-video-other-video im-call-hidden'}, children: [
				videoNode = BX.create('video'),
				fullScreenNode = BX.create('div', {
					props: {className: 'im-video-other-button im-video-other-button-fullscreen'},
					children: [
						BX.create('div', {props: {className: 'im-video-other-button-icon'}, children: [
							BX.create('i', {props: {className: 'fa fa-arrows-alt'}, attrs: {'aria-hidden': true}})
						]})
					],
					events: {
						click: function(e) {this.toggleFullScreen(receiver)}.bind(this)
					}
				})
				]}),
			avatarNode = BX.create('div', {props: {className: 'im-video-other-avatar'}, children: [
				avatarImg = BX.create('img', {props: {className: 'im-video-other-avatar-img'}})
			]}),
			captionNode = BX.create('div', {props: {className: 'im-video-other-caption'}}),
			resolutionNode = BX.create('div', {props: {className: 'im-video-other-resolution'}}),
			bitrateNode = BX.create('div', {props: {className: 'im-video-other-bitrate'}})
		]});

		var result = {
			main: mainNode,
			stream: streamNode,
			streamVideo: videoNode,
			buttons: {
				fullScreen: fullScreenNode
			},
			avatar: avatarNode,
			avatarImg: avatarImg,
			caption: captionNode,
			resolution: resolutionNode,
			bitrate: bitrateNode
		};
		return result;
	};

	CallView.prototype.toggleFullScreen = function(receiver)
	{
		if(!this.elements.receivers[receiver.feedId])
			return;

		var fullScreenElement = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;

		if(fullScreenElement)
		{
			if(document.cancelFullscreen)
				document.cancelFullscreen();
			else if (document.mozCancelFullScreen)
				document.mozCancelFullScreen();
			else if (document.webkitCancelFullScreen)
				document.webkitCancelFullScreen();

			return;
		}

		var videoElement = this.elements.receivers[receiver.feedId].stream;

		if(videoElement.requestFullScreen)
			videoElement.requestFullScreen();
		else if(videoElement.mozRequestFullScreen)
			videoElement.mozRequestFullScreen();
		else if(videoElement.webkitRequestFullScreen)
			videoElement.webkitRequestFullScreen();
		else
			console.log('fullscreen mode is not supported');
	};

	CallView.prototype.showError = function(errorText)
	{
		this.log(errorText);
		var errorNode = BX.create('div', {props: {className: 'im-call-errors-error'}, children: [
			BX.create('div', {props: {
				className: 'im-call-errors-error-close'},
				children: [
					BX.create('i', {props: {className: 'fa fa-times'}})
				],
				events: {
					click: function() {BX.cleanNode(errorNode, true);}
				}
			}),
			BX.create('div', {props: {className: 'im-call-errors-error-text'}, text: errorText})
		]});

		this.elements.errors.appendChild(errorNode);
	};

	CallView.prototype.getUserName = function(userId)
	{
		userId = parseInt(userId);
		var result = '';
		if(this.userDetails[userId])
		{
			result = this.userDetails[userId].name;
		}

		return result;
	};

	CallView.prototype.getUserAvatar = function(userId, hr)
	{
		hr = (hr == true);
		userId = parseInt(userId);
		var result = '';
		if(hr)
		{
			if(this.userDetails.hrphoto && this.userDetails.hrphoto[userId])
			{
				result = this.userDetails.hrphoto[userId];
			}
		}
		else
		{
			if(this.userDetails[userId])
			{
				result = this.userDetails[userId].avatar;
			}
		}

		return result;
	};

	CallView.prototype.render = function()
	{
		if(!this.elements.root)
		{
			this.elements.root = BX.create('div', {props: {className: 'im-call'}});
			document.body.appendChild(this.elements.root);
		}
		else
		{
			BX.addClass(this.elements.root, 'im-call');
		}

		BX.adjust(this.elements.root, {children: [
			this.elements.errors = BX.create('div', {props: {className: 'im-call-errors'}}),
			this.elements.main = BX.create('div', {props: {className: 'im-call-main'}, children: [
				this.elements.mainPlaceholder = BX.create('div', {props: {className: 'im-call-main-placeholder'}, text: BX.message('IM_CALL_WAITING_CONNECT')})
			]}),
			BX.create('div', {props: {className: 'im-call-buttons'}, children: [
				this.elements.buttons.mic.button = BX.create('div', {props: {className: 'im-call-button im-call-button-mic'}, children: [
					this.elements.buttons.mic.icon = BX.create('div', {props: {className: 'im-call-button-icon'}, html: '<i class="fa fa-microphone" aria-hidden="true"></i>'})
				]}),
				this.elements.buttons.camera.button = BX.create('div', {props: {className: 'im-call-button im-call-button-camera'}, children: [
					this.elements.buttons.camera.icon = BX.create('div', {props: {className: 'im-call-button-icon'}, html: '<i class="fa fa-video-camera" aria-hidden="true"></i>'})
				]}),
				this.elements.buttons.log.button = BX.create('div', {props: {className: 'im-call-button im-call-button-log '}, children: [
					this.elements.buttons.log.icon = BX.create('div', {props: {className: 'im-call-button-icon'}, html: '<i class="fa fa-list" aria-hidden="true"></i>'})
				]}),
				this.elements.buttons.signout.button = BX.create('div', {props: {className: 'im-call-button im-call-button-signout im-call-button-last'}, children: [
					this.elements.buttons.signout.icon = BX.create('div', {props: {className: 'im-call-button-icon'}, html: '<i class="fa fa-sign-out" aria-hidden="true"></i>'})
				]}),
				this.elements.hardware.main = BX.create('div', {props: {className: 'im-call-hardware im-call-hidden'}, children: [
					BX.create('div', {props: {className: 'im-call-hardware-mic'}, children: [
						BX.create('label', {attrs: {'for': 'im-call-hardware-select-mic'}, text: BX.message('IM_CALL_HARDWARE_MIC') + ':'}),
						this.elements.hardware.mic = BX.create('select', {attrs: {'id': 'im-call-hardware-select-mic'}})
					]}),
					BX.create('div', {props: {className: 'im-call-hardware-camera'}, children: [
						BX.create('label', {attrs: {'for': 'im-call-hardware-select-camera'}, text: BX.message('IM_CALL_HARDWARE_CAMERA') + ':'}),
						this.elements.hardware.camera = BX.create('select', {attrs: {'id': 'im-call-hardware-select-camera'}})
					]}),
					BX.create('div', {props: {className: 'im-call-hardware-resolution'}, children: [
						BX.create('label', {attrs: {'for': 'im-call-hardware-select-resolution'}, text: BX.message('IM_CALL_HARDWARE_RESOLUTION') + ':'}),
						this.elements.hardware.resolution = BX.create('select', {attrs: {'id': 'im-call-hardware-select-resolution'}})
					]})
				]}),
				this.elements.self.main = BX.create('div', {props: {className: 'im-call-video-self im-call-hidden'}, children: [
					BX.create('div', {props: {className: 'im-call-video-self-video'}, children: [
						this.elements.self.video = BX.create('video')
					]})
				]})
			]}),
			this.elements.log = BX.create('pre', {props: {className: 'im-call-log im-call-hidden'}})
		]});

		return this.elements.root;
	};

	CallView.prototype.dispose = function()
	{
		var self = this;
		
		for(feedId in this.elements.receivers)
		{
			this.elements.receivers[feedId].streamVideo.pause();
			this.elements.receivers[feedId].streamVideo.src = '';
		}
		
		this.receivers.forEach(function(receiver)
		{
			receiver.dispose();
		});
		this.receivers = [];
		if(this.publisher)
		{
			this.publisher.dispose();
		}

		if(this.localStream)
		{
			BX.webrtc.stopMediaStream(this.localStream);
		}

		this.localStream = null;
		
		BX.remove(this.elements.root, true);
	};

	CallView.prototype.log = function(text)
	{
		var date = new Date();
		var timeString = lpad(date.getHours(), 2, '0') + ':' + lpad(date.getMinutes(), 2, '0') + ':' + lpad(date.getSeconds(), 2, '0');

		var logString = timeString + ': ' + text + '\n';

		this.logText = this.logText + logString;

		this.elements.log.innerText = this.logText;
	};

	function lpad(string, length, filler)
	{
		if(!BX.type.isString(string))
			string = string.toString();

		if(string.length < length)
			return filler.repeat(length - string.length) + string;
		else
			return string;
	};

	function attachMediaStream(element, stream)
	{
		element.src = URL.createObjectURL(stream);
		element.load();
		element.play();
	};

	window.CallView = CallView;
})();
