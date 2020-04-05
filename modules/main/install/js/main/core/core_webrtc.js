/**
 * Class for unification and work with WebRTC commands
 * @param params
 * @constructor
 */

;(function (window)
{
	if (window.BX.webrtc) return;

	var BX = window.BX;

	// this is constructor, you must inherit
	BX.webrtc = function ()
	{
		this.debug = false;
		this.audioMuted = false;
		this.videoMuted = false;
		this.enabled = false;
		this.detectedBrowser = 'none';
		this.pcConfig = {};
		this.oneway = false;
		this.lastUserMediaParams = {};
		this.pcConstraints = {};
		this.sdpConstraints = {'mandatory': { 'OfferToReceiveAudio':true, 'OfferToReceiveVideo':true }};
		this.defaultMicrophone = null;
		this.defaultCamera = null;
		this.defaultSpeaker = null;
		this.enableMicAutoParameters = true;
		this.useFallbackConstraints = false;

		this.configVideo = {
			aspectRatio: 1.7777777778
		};
		this.configVideoGroup = this.configVideo;
		this.configVideoMobile = this.configVideo;
		this.configVideoAfterError = { };

		this.callStreamSelf = null;
		this.callStreamMain = null;
		this.callStreamUsers = {};

		this.pc = {};
		this.pcStart = {};
		this.connected = {};

		this.iceCandidates = {};

		this.initiator = false;
		this.callUserId = 0;
		this.callChatId = 0;
		this.callToGroup = false;
		this.callGroupUsers = [];
		this.callInit = false;
		this.callInitUserId = 0;
		this.callActive = false;
		this.callVideo = false;
		this.callRequestUserMedia = {};
		this.needPeerConnection = true;

		this.createAnswerTimeout = {};
		this.initPeerConnectionTimeout = {};
		this.iceCandidateTimeout = {};
		this.pcConnectTimeout = {};

		this.init();
		this.setTurnServer();
	};

	// this function needed to execute, after constructor
	BX.inheritWebrtc = function(child)
	{
		child.prototype = Object.create(BX.webrtc.prototype);
		child.prototype.constructor = child;
		child.prototype.parent = BX.webrtc.prototype;
	};

	BX.webrtc.prototype.init = function()
	{
		if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia)
			this.enabled = true;
		else
			return;

		if (navigator.userAgent.substr(navigator.userAgent.indexOf('Firefox/')+8, 2) >= 27)
			this.detectedBrowser = 'firefox';
		else if (navigator.appVersion.substr(navigator.appVersion.indexOf('Chrome/')+7, 2) >= 29)
			this.detectedBrowser = 'chrome';
	};

	BX.webrtc.prototype.attachMediaStream = function(element, stream)
	{
		element.src = URL.createObjectURL(stream);
		if(BX.type.isNotEmptyString(this.defaultSpeaker) && element.setSinkId)
		{
			this.log('Trying to set output device: ' + this.defaultSpeaker);
			element.setSinkId(this.defaultSpeaker);
		}
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.ready = function ()
	{
		return this.enabled;
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.setTurnServer = function (params)
	{
		if (!this.ready()) return false;

		params = params || {};

		this.turnServer = params.turnServer || 'turn.calls.bitrix24.com';
		this.turnServerFirefox = params.turnServerFirefox || '54.217.240.163';
		this.turnServerLogin = params.turnServerLogin || 'bitrix';
		this.turnServerPassword = params.turnServerPassword || 'bitrix';

		if (this.detectedBrowser == 'firefox')
		{
			this.pcConfig = { "iceServers": [ { url:"stun:"+this.turnServerFirefox}, { url:"turn:"+this.turnServerFirefox, credential:this.turnServerPassword, username: this.turnServerLogin} ] };
			this.pcConstraints = {"optional": [{"DtlsSrtpKeyAgreement": true}]};
		}
		else if (this.detectedBrowser == 'chrome')
		{
			this.pcConfig = { "iceServers": [ { url:"stun:"+this.turnServer}, { url:"turn:"+this.turnServerLogin+"@"+this.turnServer, username: this.turnServerLogin, credential:this.turnServerPassword} ] };
			this.pcConstraints = {"optional": [{"DtlsSrtpKeyAgreement": true}]};
		}

		return true;
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.toggleAudio = function(changeVariable)
	{
		if (!this.ready()) return false;

		changeVariable = typeof(changeVariable) != 'undefined'? changeVariable: true;
		if (changeVariable)
			this.audioMuted = this.audioMuted? false: true;

		if (this.callStreamSelf && this.callStreamSelf.getAudioTracks() && this.callStreamSelf.getAudioTracks().length>0)
		{
			for (var i = 0; i < this.callStreamSelf.getAudioTracks().length; i++)
			{
				this.callStreamSelf.getAudioTracks()[i].enabled = !this.audioMuted;
			}
		}

		return true;
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.toggleVideo = function(changeVariable)
	{
		if (!this.ready()) return false;

		changeVariable = typeof(changeVariable) != 'undefined'? changeVariable: true;
		if (changeVariable)
			this.videoMuted = this.videoMuted? false: true;

		if (this.callStreamSelf && this.callStreamSelf.getVideoTracks() && this.callStreamSelf.getVideoTracks().length>0)
		{
			for (var i = 0; i < this.callStreamSelf.getVideoTracks().length; i++)
			{
				this.callStreamSelf.getVideoTracks()[i].enabled = !this.videoMuted;
			}
		}

		return true;
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.signalingReady = function()
	{
		return (this.ready() && BX.PULL && BX.PULL.getPullServerStatus());
	};

	// this is public function, don't overwrite
	BX.webrtc.prototype.log = function()
	{
		var text = '';
		if (BX.desktop && BX.desktop.ready())
		{
			for (var i = 0; i < arguments.length; i++)
			{
				try
				{
					text = text+' | '+(typeof(arguments[i]) == 'object'? JSON.stringify(arguments[i]): arguments[i]);
				}
				catch (e)
				{
					text = text+' | (circular structure)';
				}

			}
			BX.desktop.log(BX.message('USER_ID')+'.video.log', text.substr(3));
		}
		if (this.debug)
		{
			if (console) console.log('WebRTC Log', arguments);
		}
	};

	/* UserMedia API */

	// this is public function, you can inherit
	BX.webrtc.prototype.startGetUserMedia = function(video, audio)
	{
		if (this.oneway && !this.initiator)
		{
			this.onUserMediaSuccess();
			return true;
		}

		this.lastUserMediaParams = {video: video, audio: audio};

		if (this.callRequestUserMedia[this.callVideo? 'video': 'audio'])
			return false;

		if(typeof(video) != 'undefined' && video !== true)
		{
			//nop
		}
		else
		{
			if(this.useFallbackConstraints)
				video = this.configVideoAfterError;
			else if(this.callToGroup)
				video = this.configVideoGroup;
			else
				video = this.configVideo;
		}

		if(video && this.defaultCamera)
		{
			video.deviceId = {ideal: this.defaultCamera};
		}

		audio = typeof(audio) != 'undefined' && audio !== true? audio: {};
		if(audio && this.defaultMicrophone)
		{
			audio.deviceId = {ideal: this.defaultMicrophone};
		}

		/* for unknown reason, these constraints do not work in chrome, when paired with video resolution constraints */
		if(false && this.enableMicAutoParameters === false)
		{
			audio.optional = [
				{echoCancellation:false},
				{googEchoCancellation:false},
				{googEchoCancellation2:false},
				{googDAEchoCancellation:false},
				{googAutoGainControl: false},
				{googAutoGainControl2: false},
				{mozAutoGainControl: false},
				{googNoiseSuppression: false},
				{googNoiseSuppression2: false},
				{googHighpassFilter: false},
				{googTypingNoiseDetection: false},
				{googAudioMirroring: false}
			];
		}

		var constraints = {
			"audio": audio,
			"video": video
		};

		this.log("Requested access to local media with constraints:  \"" + JSON.stringify(constraints) + "\"");

		try {
			this.callRequestUserMedia[this.callVideo? 'video': 'audio'] = true;
			navigator.mediaDevices.getUserMedia(constraints).then(BX.delegate(this.onUserMediaSuccess, this), BX.delegate(this.onUserMediaError, this));
		} catch (e) {
			this.debug = true;
			this.log("Method getUserMedia failed with exception: " + e.message);
		}

		return true;
	}

	// this is protected function, you must inherit
	BX.webrtc.prototype.onUserMediaSuccess = function(stream)
	{
		this.log('Media stream received');
		if(stream && stream.getTracks)
		{
			stream.getTracks().forEach(function(track)
			{
				this.log(BX.webrtc.mediaStreamTrackToString(track));
			}.bind(this));
		}

		if (!this.oneway || this.initiator)
		{
			this.callRequestUserMedia[this.callVideo? 'video': 'audio'] = false;
			if (this.callStreamSelf)
				return false;

			if (!this.callActive && stream)
			{
				BX.webrtc.stopMediaStream(stream);
				return false;
			}

			this.log("User has granted access to local media.");

			this.callStreamSelf = stream;
			this.toggleAudio(false);
		}

		if (!this.needPeerConnection)
			return true;

		if (this.initiator)
		{
			if (this.callToGroup)
			{
				for (var i = 0; i < this.callGroupUsers.length; i++)
				{
					var userId = this.callGroupUsers[i];
					if (userId != BX.message('USER_ID'))
					{
						clearTimeout(this.initPeerConnectionTimeout[userId]);
						this.initPeerConnection(userId);
					}
				}
			}
			else
			{
				clearTimeout(this.initPeerConnectionTimeout[this.callUserId]);
				this.initPeerConnection(this.callUserId);
			}
		}
		else
		{
			if (this.callToGroup)
			{
				for (var i = 0; i < this.callGroupUsers.length; i++)
				{
					var userId = this.callGroupUsers[i];
					if (userId != BX.message('USER_ID') && userId != this.callInitUserId && !this.callStreamUsers[userId])
					{
						clearTimeout(this.initPeerConnectionTimeout[userId]);
						this.initPeerConnection(userId, true);
					}
				}
			}
		}
		return true;
	}

	// this is protected function, you can inherit
	BX.webrtc.prototype.onUserMediaError = function(error)
	{
		this.debug = true;
		this.callRequestUserMedia[this.callVideo? 'video': 'audio'] = false;
		if (!this.callActive)
			return false;

		this.log("Failed to get access to local media. Error code was " + JSON.stringify(error));

		return true;
	}

	/* PeerConnection API */

	// this is public function, don't overwrite or inherit
	BX.webrtc.prototype.initPeerConnection = function(userId, initiator)
	{
		if (!this.callActive)
			return false;

		initiator = initiator === true;
		if (this.debug) console.log(userId, 'wait initPeerConnection')
		if (!this.pcStart[userId] && (!this.oneway && this.callStreamSelf || this.oneway && this.initiator && this.callStreamSelf || this.oneway && !this.initiator))
		{
			if (this.connected[userId])
			{
				this.log("Creating PeerConnection.", userId);
				this.createPeerConnection(userId);
				if (!this.pc[userId])
				{
					clearTimeout(this.initPeerConnectionTimeout[userId]);
					this.initPeerConnectionTimeout[userId] = setTimeout(BX.delegate(function(){
						this.initPeerConnection(userId, initiator);
					}, this), 2000);
					return false;
				}

				this.log("Adding local stream.", userId, this.pc[userId]);

				this.pcStart[userId] = true;

				if (this.initiator || initiator)
					this.sendOfferToPeer(userId);
			}
			else
			{
				clearTimeout(this.initPeerConnectionTimeout[userId]);
				this.initPeerConnectionTimeout[userId] = setTimeout(BX.delegate(function(){
					this.initPeerConnection(userId, initiator);
				}, this), 2000);
			}
		}
		else if (!this.connected[userId])
		{
			clearTimeout(this.initPeerConnectionTimeout[userId]);
			this.initPeerConnectionTimeout[userId] = setTimeout(BX.delegate(function(){
				this.initPeerConnection(userId, initiator);
			}, this), 2000);
		}

		return true;
	}

	// this protected function, you must inherit
	BX.webrtc.prototype.setLocalAndSend = function(userId, desc)
	{
		if (this.pc[userId] == null || !this.callActive)
			return false;

		this.log('set local description and send', userId, JSON.stringify(desc));
		this.pc[userId].setLocalDescription(desc, BX.delegate(function(a){this.log('setLocalDescription', a)}, this), BX.delegate(function(a){this.log('setLocalDescription', a)}, this));

		// need to send local description to "userId" peer

		return true;
	}

	// this is blank function, you must overwrite
	BX.webrtc.prototype.onRemoteStreamAdded = function(userId, event, setMainVideo)
	{
	}

	// this is blank function, you can overwrite
	BX.webrtc.prototype.onRemoteStreamRemoved = function(userId, event)
	{
	}

	// this is blank function, you must overwrite
	BX.webrtc.prototype.onIceCandidate = function (userId, candidates)
	{
		// need to send IceCandidate to "userId" peer
	}

	// this is blank function, you can overwrite
	BX.webrtc.prototype.onIceConnectionStateChange = function(userId, event)
	{
	}

	// this is blank function, you can overwrite
	BX.webrtc.prototype.onSignalingStateChange = function(userId, event)
	{
	}

	// this is blank function, you can overwrite
	BX.webrtc.prototype.peerConnectionError = function (userId, event)
	{
	}

	// this is public function, you must inherit
	BX.webrtc.prototype.peerConnectionReconnect = function (userId)
	{
		if (!this.pc[userId])
			return false;

		if ((this.pc[userId].iceConnectionState == 'connected' || this.pc[userId].iceConnectionState == 'completed') && this.pc[userId].signalingState == 'stable')
			return false;

		this.log('peerConnectionReconnect', this.pc[userId].iceConnectionState, this.pc[userId].signalingState)
		this.pc[userId].close();

		delete this.pc[userId];
		delete this.pcStart[userId];

		if (this.callStreamMain == this.callStreamUsers[userId])
			this.callStreamMain = null;
		this.callStreamUsers[userId] = null;

		clearTimeout(this.initPeerConnectionTimeout[userId]);

		// need to send reconnect command to "userId" peer in your class
		return true;
	}

	// this is public function, you can inherit
	BX.webrtc.prototype.deleteEvents = function()
	{
		this.initiator = false;
		this.callUserId = 0;
		this.callChatId = 0;
		this.callToGroup = false;
		this.callGroupUsers = [];
		this.callInit = false;
		this.callInitUserId = 0;
		this.callActive = false;
		this.callVideo = false;
		this.callRequestUserMedia = {};

		this.audioMuted = false;
		this.videoMuted = false;
		this.needPeerConnection = true;

		var i = 0;
		for (i in this.pc)
		{
			if(this.pc.hasOwnProperty(i))
			{
				this.pc[i].close();
				delete this.pc[i];
			}
		}

		this.pc = {};
		this.pcStart = {};
		this.connected = {};
		this.iceCandidates = {};

		for (i in this.iceCandidateTimeout)
		{
			if (this.iceCandidateTimeout.hasOwnProperty(i))
			{
				clearTimeout(this.iceCandidateTimeout[i]);
			}
		}
		this.iceCandidateTimeout = {};

		for (i in this.pcConnectTimeout)
		{
			if (this.pcConnectTimeout.hasOwnProperty(i))
			{
				clearTimeout(this.pcConnectTimeout[i]);
			}
		}
		this.pcConnectTimeout = {};

		for (i in this.createAnswerTimeout)
		{
			if (this.createAnswerTimeout.hasOwnProperty(i))
			{
				clearTimeout(this.createAnswerTimeout[i]);
			}
		}
		this.createAnswerTimeout = {};

		for (i in this.initPeerConnectionTimeout)
		{
			if (this.initPeerConnectionTimeout.hasOwnProperty(i))
			{
				clearTimeout(this.initPeerConnectionTimeout[i]);
			}
		}
		this.initPeerConnectionTimeout = {};

		if (this.callStreamSelf)
		{
			BX.webrtc.stopMediaStream(this.callStreamSelf);
			this.callStreamSelf = null;
		}
		if (this.callStreamMain)
		{
			BX.webrtc.stopMediaStream(this.callStreamMain);
			this.callStreamMain = null;
		}
		for (i in this.callStreamUsers)
		{
			if (this.callStreamUsers.hasOwnProperty(i))
			{
				if (this.callStreamUsers[i])
					BX.webrtc.stopMediaStream(this.callStreamUsers[i]);
				delete this.callStreamUsers[i];
			}
		}
	}

	/* Private function */

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.mergeConstraints = function (cons1, cons2)
	{
		if (!this.ready()) return false;

		var merged = cons1;
		for (var name in cons2.mandatory)
		{
			if (cons2.mandatory.hasOwnProperty(name))
			{
				merged.mandatory[name] = cons2.mandatory[name];
			}
		}
		merged.optional.concat(cons2.optional);
		return merged;
	};

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.sendOfferToPeer = function(userId)
	{
		if (!this.pc[userId])
			return false;

		var constraints = {"optional": [], "mandatory": {"MozDontOfferDataChannel": true}};

		if (this.detectedBrowser === "chrome")
		{
			for (var prop in constraints.mandatory)
			{
				if (constraints.mandatory.hasOwnProperty(prop))
				{
					if (prop.indexOf("Moz") != -1)
						delete constraints.mandatory[prop];
				}
			}
		}
		this.log('Constraints', constraints);
		constraints = this.mergeConstraints(constraints, this.sdpConstraints);

		this.log("Sending offer to peer, with constraints:", userId,  "\"" + JSON.stringify(constraints) + "\".");
		this.pc[userId].createOffer(BX.delegate(function(desc){this.setLocalAndSend(userId, desc)}, this), BX.delegate(function(a){this.log('createOffer failure', a)}, this), constraints);

		return true;
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.createPeerConnection = function(userId)
	{
		try
		{
			this.pc[userId] = new RTCPeerConnection(this.pcConfig, this.pcConstraints);
			this.pc[userId].onicecandidate = BX.delegate(function(event) { this.onIceCandidateEvent(userId, event) }, this);
			this.pc[userId].onaddstream = BX.delegate(function(event) { this.onRemoteStreamAddedEvent(userId, event) }, this);
			this.pc[userId].onremovestream = BX.delegate(function(event) { this.onRemoteStreamRemovedEvent(userId, event)}, this);
			this.pc[userId].oniceconnectionstatechange = BX.delegate(function(event) { this.onIceConnectionStateChangeEvent(userId, event)}, this);
			this.pc[userId].onsignalingstatechange = BX.delegate(function(event) { this.onSignalingStateChangeEvent(userId, event)}, this);
			if (!this.oneway || this.initiator)
			{
				this.pc[userId].addStream(this.callStreamSelf);
			}

			this.log("Created RTCPeerConnnection for "+userId+" with:\n" +
			"  config: \"" + JSON.stringify(this.pcConfig) + "\";\n" +
			"  constraints: \"" + JSON.stringify(this.pcConstraints) + "\".");
		}
		catch (e)
		{
			if (this.callToGroup && this.callStreamUsers[userId])
				return false;

			this.log('PeerConnection: ', JSON.stringify(this.pcConfig), JSON.stringify(this.pcConstraints));
			this.log("Failed to create PeerConnection, exception: " + e.message);

			this.peerConnectionError(userId, e);
		}
		return true;
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.signalingPeerData = function(userId, peerData)
	{
		var signal = JSON.parse(peerData);
		if (signal.type === 'offer')
		{
			if (!this.pcStart[userId])
				this.initPeerConnection(userId);

			this.createAnswer(userId, signal);
		}
		else if (signal.type === 'answer' && this.pcStart[userId])
		{
			if (this.pc[userId] == null)
				return false;

			this.pc[userId].setRemoteDescription(new RTCSessionDescription(signal), function(){}, function(){});
		}
		else if (signal.type === 'candidate' && this.pcStart[userId])
		{
			if (!this.pc[userId] || this.pc[userId] == null)
				return false;

			for (var i = 0; i < signal.candidates.length; i++)
			{
				var candidate = new RTCIceCandidate({sdpMLineIndex:signal.candidates[i].label, candidate:signal.candidates[i].candidate});
				try{
					this.pc[userId].addIceCandidate(candidate);
				}
				catch(e)
				{
					this.log('Error addIceCandidate', JSON.stringify(e));
					this.peerConnectionReconnect(userId);
					return false;
				}
			}
		}
		else
		{
			this.log('Signaling command "'+(signal && signal.type? signal.type: 'undefined')+'" from user "'+userId+'" skip');
		}

		return true;
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.onRemoteStreamAddedEvent = function (userId, event)
	{
		if (!this.pc[userId]) return false;

		this.log('Remote stream added', userId, JSON.stringify(event));

		var setMainVideo = false;
		if (!this.callStreamMain)
		{
			this.callStreamMain = event.stream;
			setMainVideo = true;
		}
		this.callStreamUsers[userId] = event.stream;
		this.onRemoteStreamAdded(userId, event, setMainVideo)
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.onRemoteStreamRemovedEvent = function(userId, event)
	{
		if (!this.pc[userId]) return false;

		this.callStreamUsers[userId] = null;
		this.onRemoteStreamRemoved(userId, event);
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.onIceCandidateEvent = function (userId, event)
	{
		if (!this.pc[userId]) return false;

		if (!this.iceCandidates[userId])
			this.iceCandidates[userId] = [];

		if (event.candidate)
		{
			this.log("New ICE candidate: ", event.candidate);
			this.iceCandidates[userId].push({type: 'candidate', label: event.candidate.sdpMLineIndex, id: event.candidate.sdpMid, candidate: event.candidate.candidate});

			clearTimeout(this.iceCandidateTimeout[userId]);
			this.iceCandidateTimeout[userId] = setTimeout(BX.delegate(function(){
				if (this.iceCandidates[userId].length === 0)
					return false;

				this.onIceCandidate(userId, {'type': 'candidate', 'candidates': this.iceCandidates[userId]});
				this.iceCandidates[userId] = [];
			}, this), 250);
		}
		else
		{
			this.log("End of candidates of "+userId);
		}
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.onIceConnectionStateChangeEvent = function(userId, event)
	{
		clearTimeout(this.pcConnectTimeout[userId]);
		if (!this.pc[userId] || this.pc[userId].iceConnectionState == "close")
			return false;

		this.log('iceConnectionStateChange', userId, this.pc[userId].iceConnectionState, this.pc[userId].signalingState);
		this.pcConnectTimeout[userId] = setTimeout(BX.delegate(function(){
			this.peerConnectionReconnect(userId);
		}, this), 15000);

		this.onIceConnectionStateChange(userId, event);

		return true;
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.onSignalingStateChangeEvent = function(userId, event)
	{
		clearTimeout(this.pcConnectTimeout[userId]);
		if (!this.pc[userId] || this.pc[userId].signalingState == "close")
			return false;

		this.log('signalingStateChange', userId, this.pc[userId].iceConnectionState, this.pc[userId].signalingState);
		this.pcConnectTimeout[userId] = setTimeout(BX.delegate(function(){
			this.peerConnectionReconnect(userId);
		}, this), 15000);

		this.onSignalingStateChange(userId, event)
	}

	// this is private function, don't use or overwrite or inherit
	BX.webrtc.prototype.createAnswer = function(userId, signal)
	{
		if (!this.callActive)
			return false;

		if (!this.pcStart[userId])
		{
			clearTimeout(this.createAnswerTimeout[userId]);
			this.createAnswerTimeout[userId] = setTimeout(BX.delegate(function(){
				this.createAnswer(userId, signal);
			}, this), 2000);

			return false;
		}
		this.pc[userId].setRemoteDescription(new RTCSessionDescription(signal), BX.delegate(function()
		{
			if (!this.pc[userId]) return false;
			this.pc[userId].createAnswer(BX.delegate(function(desc){this.setLocalAndSend(userId, desc)}, this), BX.delegate(function(a){this.log('createAnswer failure', a)}, this), this.sdpConstraints);
		}, this), function(){});

		return true;
	}

	BX.webrtc.prototype.setDefaultCamera = function(defaultCamera)
	{
		this.defaultCamera = defaultCamera;
	};

	BX.webrtc.prototype.setDefaultMicrophone = function(defaultMicrophone)
	{
		this.defaultMicrophone = defaultMicrophone;
	};

	BX.webrtc.prototype.logDevices = function()
	{
		var self = this;
		if(navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)
		{
			self.log('Enumerating media devices');
			navigator.mediaDevices.enumerateDevices().then(function(devices)
			{
				devices.forEach(function(device)
				{
					try
					{
						self.log(JSON.stringify(device));
					}
					catch (e)
					{
						self.log(device);
					}
				});
			})
		}
		else
		{
			self.log('Could not enumerate devices, api is not supported');
		}
	};

	BX.webrtc.mediaStreamTrackToString = function(track)
	{
		var result = '';
		for(key in track)
		{
			if(!BX.type.isFunction(track[key]))
			{
				result = result + ' ' + key + ': ' + track[key] + ';';
			}
		}
		return result;
	};

	BX.webrtc.stopMediaStream = function(mediaStream)
	{
		if(!(mediaStream instanceof MediaStream))
			return;

		if (typeof mediaStream.getTracks === 'undefined')
		{
			// Support for legacy browsers
			mediaStream.stop();
		}
		else
		{
			mediaStream.getTracks().forEach(function(track)
			{
				track.stop();
			});
		}
	};

})(window);
