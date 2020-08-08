;(function () {
	BX.namespace('BX.Call');

	if (BX.Call.FloatingVideo)
	{
		return;
	}

	var Events = {
		setStream: "FloatingVideo::setStream",
		setAudioMuted: "FloatingVideo::setAudioMuted",
		setTitle: "FloatingVideo::setTitle",
		setAvatars: "FloatingVideo::setAvatars",
		setTalking: "FloatingVideo::setTalking",

		onMainAreaClick: "FloatingVideo::onMainAreaClick",
		onMicButtonClick: "FloatingVideo::onMicButtonClick",
		onHangupButtonClick: "FloatingVideo::onHangupButtonClick",

		connectionOffer: "FloatingVideo::connectionOffer",
		connectionAnswer: "FloatingVideo::connectionAnswer",
		connectionClose: "FloatingVideo::connectionClose",
		connectionIceCandidate: "FloatingVideo::connectionIceCandidate",
	};

	var VIDEO_WIDTH = 320;
	var VIDEO_HEIGHT = 180;
	var AUDIO_WIDTH = 320;
	var AUDIO_HEIGHT = 70;

	/**
	 *
	 * @param {object} config
	 * @param {MediaStream} [config.stream]
	 * @param {bool} [config.audioMuted]
	 * @param {function} [config.onMainAreaClick]
	 * @param {function} [config.onButtonClick]
	 * @constructor
	 */
	BX.Call.FloatingVideo = function (config)
	{
		if(typeof(config) !== "object")
		{
			config = {};
		}
		this.stream = config.stream && BX.Call.Util.containsVideoTrack(config.stream) ? config.stream : null;
		this.audioMuted = config.audioMuted || false;

		this.title = config.title || "";
		this.avatars = config.avatars || {};

		this.window = null;
		this.visible = false;
		this.peerConnection = null;

		this.callbacks = {
			onMainAreaClick: BX.type.isFunction(config.onMainAreaClick) ? config.onMainAreaClick : BX.DoNothing,
			onButtonClick: BX.type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};

		this._onContentMainAreaClickHandler = this._onContentMainAreaClick.bind(this);
		this._onContentMicButtonClickHandler = this._onContentMicButtonClick.bind(this);
		this._onContentHangupButtonClickHandler = this._onContentHangupButtonClick.bind(this);

		this._onPCNegotiationNeededHandler = this._onPCNegotiationNeeded.bind(this);
		this._onPCIceCandidateHandler = this._onPCIceCandidate.bind(this);
		this._onConnectionAnswerHandler = this._onConnectionAnswer.bind(this);
		this._onConnectionIceCandidateHandler = this._onConnectionIceCandidate.bind(this);

		this.bindEventHandlers();
	};

	BX.Call.FloatingVideo.prototype =
	{
		bindEventHandlers: function ()
		{
			BX.desktop.addCustomEvent(Events.onMainAreaClick, this._onContentMainAreaClickHandler);
			BX.desktop.addCustomEvent(Events.onMicButtonClick, this._onContentMicButtonClickHandler);
			BX.desktop.addCustomEvent(Events.onHangupButtonClick, this._onContentHangupButtonClickHandler);

			BX.desktop.addCustomEvent(Events.connectionAnswer, this._onConnectionAnswerHandler);
			BX.desktop.addCustomEvent(Events.connectionIceCandidate, this._onConnectionIceCandidateHandler);
		},

		_onContentMainAreaClick: function()
		{
			this.callbacks.onMainAreaClick();
		},

		_onContentMicButtonClick: function()
		{
			this.callbacks.onButtonClick({
				buttonName: "toggleMute",
				muted: !this.audioMuted
			})
		},

		_onContentHangupButtonClick: function()
		{
			this.callbacks.onButtonClick({
				buttonName: "hangup"
			})
		},

		setStream: function(stream)
		{
			if(BX.Call.Util.containsVideoTrack(stream))
			{
				this.stream = stream;
			}
			else
			{
				this.stream = null;
			}

			if(this.window && this.visible)
			{
				if(this.stream)
				{
					this.sendVideo();
				}
				else
				{
					this.stopSendingVideo();
				}
			}
		},

		setTitle: function(title)
		{
			this.title = title;
			if(this.window)
			{
				BX.desktop.onCustomEvent(this.window, Events.setTitle, [this.title]);
			}
		},

		setAvatars: function(avatarList)
		{
			this.avatars = avatarList;
			if(this.window && this.visible)
			{
				BX.desktop.onCustomEvent(this.window, Events.setAvatars, [this.avatars]);
			}
		},

		setTalking: function(talking)
		{
			if(this.window)
			{
				BX.desktop.onCustomEvent(this.window, Events.setTalking, [talking]);
			}
		},

		sendVideo: function()
		{
			if(!this.peerConnection)
			{
				this.peerConnection = new RTCPeerConnection();
				this.peerConnection.addEventListener("negotiationneeded", this._onPCNegotiationNeededHandler);
				this.peerConnection.addEventListener("icecandidate", this._onPCIceCandidateHandler);
			}
			this.peerConnection.addTrack(this.stream.getVideoTracks()[0], this.stream);
		},

		_onPCNegotiationNeeded: function()
		{
			var connectionOffer;
			this.peerConnection.createOffer().then(function(offer)
			{
				connectionOffer = offer;
				return this.peerConnection.setLocalDescription(offer)
			}.bind(this)).then(function()
			{
				BX.desktop.onCustomEvent(this.window, Events.connectionOffer, [connectionOffer.sdp]);
			}.bind(this));
		},

		_onPCIceCandidate: function(e)
		{
			var candidate = e.candidate;
			if(candidate)
			{
				BX.desktop.onCustomEvent(this.window, Events.connectionIceCandidate, [candidate.toJSON()]);
			}
		},

		_onConnectionAnswer: function(sdp)
		{
			if(this.peerConnection)
			{
				var sessionDescription = new RTCSessionDescription({
					type: "answer",
					sdp: sdp
				});

				this.peerConnection.setRemoteDescription(sessionDescription);
			}
		},

		_onConnectionIceCandidate: function(candidate)
		{
			if(this.peerConnection)
			{
				this.peerConnection.addIceCandidate(candidate);
			}
		},

		stopSendingVideo: function()
		{
			if(this.peerConnection)
			{
				this.peerConnection.close();
				this.peerConnection.removeEventListener("negotiationneeded", this._onPCNegotiationNeededHandler);
				this.peerConnection.removeEventListener("icecandidate", this._onPCIceCandidateHandler);
				this.peerConnection = null;
			}

			BX.desktop.onCustomEvent(this.window, Events.connectionClose, []);
		},

		setAudioMuted: function(audioMuted)
		{
			if(this.audioMuted == audioMuted)
			{
				return;
			}

			this.audioMuted = audioMuted;

			if(this.window && this.visible)
			{
				BX.desktop.onCustomEvent(this.window, Events.setAudioMuted, [this.audioMuted]);
			}
		},

		show: function ()
		{
			if (!BX.desktop)
			{
				return;
			}

			if(this.window)
			{
				this.window.BXDesktopWindow.ExecuteCommand("show");
				BX.desktop.onCustomEvent(this.window, Events.setAudioMuted, [this.audioMuted]);
				BX.desktop.onCustomEvent(this.window, Events.setAvatars, [this.avatars]);
				if(this.stream)
				{
					this.sendVideo();
				}
			}
			else
			{
				var params = {
					audioMuted: this.audioMuted,
					title: this.title,
					avatars: this.avatars
				};

				this.window = BXDesktopSystem.ExecuteCommand('topmost.show.html', BX.desktop.getHtmlPage("", "window.FVC = new BX.Call.FloatingVideoContent(" + JSON.stringify(params) + ");"));
				setTimeout(function()
				{
					if(this.stream && this.visible)
					{
						this.sendVideo();
					}
				}.bind(this), 2000)
			}

			this.visible = true;
		},

		hide: function ()
		{
			if (!this.window || !this.window.document)
			{
				return false;
			}
			this.stopSendingVideo();
			this.window.BXDesktopWindow.ExecuteCommand("hide");
			this.visible = false;
		},

		close: function ()
		{
			if (!this.window || !this.window.document)
			{
				return false;
			}

			this.window.BXDesktopWindow.ExecuteCommand("close");
			this.window = null;
			this.visible = false;
		},

		destroy: function ()
		{
			if(this.window)
			{
				this.window.BXDesktopWindow.ExecuteCommand("close");
				this.window = null;
			}

			this.stream = null;

			BX.desktop.removeCustomEvents(Events.onMainAreaClick);
			BX.desktop.removeCustomEvents(Events.onMicButtonClick);
			BX.desktop.removeCustomEvents(Events.onHangupButtonClick);
		}
	};

	BX.Call.FloatingVideoContent = function (config)
	{
		this.stream = config.stream;
		this.audioMuted = config.audioMuted;

		this.avatars = config.avatars || {};
		this.title = config.title || "";
		this.talking = [];

		this.elements = {
			container: null,
			video: null,
			avatars: null,
			title: null,
			micButton: null,
			micButtonText: null,
			hangupButton: null
		};

		this.render();
		this.adjustWindow();
		this.bindEventHandlers();

		this.callAspectHorizontal = true;

		if (this.stream)
		{
			this.callAspectCheckInterval = setInterval(this.checkVideoAspect.bind(this), 500);
		}

		this.slavePeerConnection = null;
		this._onSlavePCIceCandidateHandler = this._onSlavePCIceCandidate.bind(this);
		this._onSlavePCIceConnectionStateChangeHandler = this._onSlavePCIceConnectionStateChange.bind(this);
	};

	BX.Call.FloatingVideoContent.prototype = {
		bindEventHandlers: function () {
			BX.desktop.addCustomEvent(Events.setStream, this.setStream.bind(this));
			BX.desktop.addCustomEvent(Events.setAudioMuted, this.setAudioMuted.bind(this));
			BX.desktop.addCustomEvent(Events.setTitle, this.setTitle.bind(this));
			BX.desktop.addCustomEvent(Events.setAvatars, this.setAvatars.bind(this));
			BX.desktop.addCustomEvent(Events.setTalking, this.setTalking.bind(this));

			BX.desktop.addCustomEvent(Events.connectionOffer, this._onConnectionOfferFromMain.bind(this));
			BX.desktop.addCustomEvent(Events.connectionIceCandidate, this._onIceCandidateFromMain.bind(this));
			BX.desktop.addCustomEvent(Events.connectionClose, this._onRTCconnectionClose.bind(this));

			window.addEventListener("beforeunload", this.destroy.bind(this));
		},

		render: function () {
			var minCallWidth = this.stream ? VIDEO_WIDTH : AUDIO_WIDTH;
			var minCallHeight = this.stream ? VIDEO_HEIGHT : AUDIO_HEIGHT;

			var callOverlayStyle = {
				width: minCallWidth + 'px',
				height: minCallHeight + 'px'
			};

			this.elements.container = BX.create("div", {
				props: {className: 'bx-messenger-call-float' + (this.stream ? '' : ' bx-messenger-call-float-audio')},
				style: callOverlayStyle,
				events: {
					click: this.onMainAreaClick.bind(this)
				},
				children: [
					BX.create("div", {
						props: {
							className: 'bx-messenger-call-float-audio-unfold'
						},
						children: [
							this.elements.avatars = BX.create("div", {
								props: {className: 'bx-messenger-call-float-avatars'},
							}),
							this.elements.title = BX.create("span", {
								props: {className: 'bx-messenger-call-float-button-text'},
								text: this.title
							})
						]
					}),
					BX.create("div", {
						props: {className: 'bx-messenger-call-float-buttons'},
						children: [
							this.elements.micButton = BX.create("div", {
								props: {className: 'bx-messenger-call-float-button bx-messenger-call-float-button-mic' + (this.audioMuted ? ' bx-messenger-call-float-button-mic-disabled' : '')},
								events: {
									click: this.onMicButtonClick.bind(this)
								},
								children: [
									BX.create("span", {props: {className: 'bx-messenger-call-float-button-icon'}}),
									this.elements.micButtonText = BX.create("span", {
										props: {className: 'bx-messenger-call-float-button-text'},
										text: BX.message('IM_M_CALL_BTN_MIC') + ' ' + BX.message('IM_M_CALL_BTN_MIC_' + (this.audioMuted ? 'OFF' : 'ON'))
									})
								]
							}),
							this.elements.hangupButton = BX.create("div", {
								props: {className: 'bx-messenger-call-float-button bx-messenger-call-float-button-decline'},
								events: {
									click: this.onHangupButtonClick.bind(this)
								},
								children: [
									BX.create("span", {props: {className: 'bx-messenger-call-float-button-icon'}}),
									BX.create("span", {
										props: {className: 'bx-messenger-call-float-button-text'},
										text: BX.message('IM_M_CALL_BTN_HANGUP')
									})
								]
							})
						]
					})
				]
			});

			this.elements.video = BX.create("video", {
				attrs: {
					autoplay: true,
					src: this.stream,

				},
				props: {className: 'bx-messenger-call-float-video', volume: 0},

			});

			if(this.stream)
			{
				BX.prepend(this.elements.video, this.elements.container);
			}

			this.setAvatars(this.avatars);

			document.body.appendChild(this.elements.container);
		},

		adjustWindow: function (width, height)
		{
			var minCallWidth = this.stream ? VIDEO_WIDTH : AUDIO_WIDTH;
			var minCallHeight = this.stream ? VIDEO_HEIGHT : AUDIO_HEIGHT;

			width = width || minCallWidth;
			height = height || minCallHeight;

			if(!this.stream)
			{
				var rows = Math.ceil(Object.keys(this.avatars).length / 4);
				height += rows * 74 + 10; // avatar height + top padding
			}

			this.elements.container.style.width = width +"px";
			this.elements.container.style.height = height +"px";

			BX.desktop.setWindowMinSize({Width: width, Height: height});
			BX.desktop.setWindowResizable(false);
			BX.desktop.setWindowClosable(false);
			BX.desktop.setWindowResizable(false);
			BX.desktop.setWindowTitle(this.title);

			if (BXDesktopSystem.QuerySettings('global_topmost_x', null))
			{
				BX.desktop.setWindowPosition({
					X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)),
					Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)),
					Width: width,
					Height: height,
					Mode: STP_FRONT
				});
				if (!BX.browser.IsMac())
					BX.desktop.setWindowPosition({
						X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)),
						Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)),
						Width: width,
						Height: height,
						Mode: STP_FRONT
					});
			}
			else
			{
				BX.desktop.setWindowPosition({
					X: STP_RIGHT,
					Y: STP_TOP,
					Width: width,
					Height: height,
					Mode: STP_FRONT
				});
				if (!BX.browser.IsMac())
					BX.desktop.setWindowPosition({
						X: STP_RIGHT,
						Y: STP_TOP,
						Width: width,
						Height: height,
						Mode: STP_FRONT
					});
			}
		},

		checkVideoAspect: function()
		{
			if (this.elements.video.videoWidth < this.elements.video.videoHeight)
			{
				if (this.callAspectHorizontal)
				{
					this.callAspectHorizontal = false;
					BX.addClass(this.elements.container, 'bx-messenger-call-overlay-aspect-vertical');
					BX.desktop.setWindowSize({
						Width: VIDEO_HEIGHT,
						Height: VIDEO_WIDTH
					});
				}
			}
			else
			{
				if (!this.callAspectHorizontal)
				{
					this.callAspectHorizontal = true;
					BX.removeClass(this.elements.container, 'bx-messenger-call-overlay-aspect-vertical');
					BX.desktop.setWindowSize({
						Width: VIDEO_WIDTH,
						Height: VIDEO_HEIGHT
					});
				}
			}
		},

		_onConnectionOfferFromMain: function(sdp)
		{
			if(this.slavePeerConnection)
			{
				//remove events
				this.slavePeerConnection.removeEventListener("icecandidate", this._onSlavePCIceCandidateHandler);
				this.slavePeerConnection.removeEventListener("iceconnectionstatechange", this._onSlavePCIceConnectionStateChangeHandler);

				this.slavePeerConnection.close();
			}

			this.slavePeerConnection = new RTCPeerConnection();
			this.slavePeerConnection.addEventListener("icecandidate", this._onSlavePCIceCandidateHandler);
			this.slavePeerConnection.addEventListener("iceconnectionstatechange", this._onSlavePCIceConnectionStateChangeHandler);

			var sessionDescription = new RTCSessionDescription({
				type: "offer",
				sdp: sdp
			});

			this.slavePeerConnection.setRemoteDescription(sessionDescription).then(
				function()
				{
					if(this.slavePeerConnection)
					{
						this.slavePeerConnection.createAnswer(/*{offerToReceiveVideo: true}*/).then(
							function(answer)
							{
								this.slavePeerConnection.setLocalDescription(answer);
								BX.desktop.onCustomEvent("main", Events.connectionAnswer, [answer.sdp]);
							}.bind(this)
						)
					}
				}.bind(this)
			)
		},

		_onIceCandidateFromMain: function(candidate)
		{
			setTimeout(function()
			{
				if(this.slavePeerConnection)
				{
					this.slavePeerConnection.addIceCandidate(candidate);
				}
			}.bind(this), 200);
		},

		_onRTCconnectionClose: function()
		{
			if(this.slavePeerConnection)
			{
				this.slavePeerConnection.removeEventListener("icecandidate", this._onSlavePCIceCandidateHandler);
				this.slavePeerConnection.removeEventListener("iceconnectionstatechange", this._onSlavePCIceConnectionStateChangeHandler);
				this.slavePeerConnection.close();
				this.slavePeerConnection = null;
			}
			this.setStream(null);
		},

		_onSlavePCIceCandidate: function(e)
		{
			var candidate = e.candidate;
			if (candidate)
			{
				BX.desktop.onCustomEvent("main", Events.connectionIceCandidate, [candidate.toJSON()]);
			}
		},

		_onSlavePCIceConnectionStateChange: function(e)
		{
			if(this.slavePeerConnection.iceConnectionState === "connected")
			{
				this.setStream(this.slavePeerConnection.getRemoteStreams()[0]);
			}
			else if(this.slavePeerConnection.iceConnectionState === "disconnected")
			{
				this.setStream(null);

				this.slavePeerConnection.removeEventListener("icecandidate", this._onSlavePCIceCandidateHandler);
				this.slavePeerConnection.removeEventListener("iceconnectionstatechange", this._onSlavePCIceConnectionStateChangeHandler);
			}
		},

		setStream: function(stream)
		{
			clearInterval(this.callAspectCheckInterval);
			this.stream = stream;

			var minCallWidth = this.stream ? VIDEO_WIDTH : AUDIO_WIDTH;
			var minCallHeight = this.stream ? VIDEO_HEIGHT : AUDIO_HEIGHT;

			var callOverlayStyle = {
				width: minCallWidth + 'px',
				height: minCallHeight + 'px'
			};

			if(this.elements.container)
			{
				if(this.stream)
				{
					if(!this.elements.video.parentNode)
					{
						BX.prepend(this.elements.video, this.elements.container);
						BX.removeClass(this.elements.container, "bx-messenger-call-float-audio");
					}
					this.elements.video.srcObject = this.stream;
				}
				else
				{
					BX.remove(this.elements.video);
					BX.addClass(this.elements.container, "bx-messenger-call-float-audio");
				}

				BX.adjust(this.elements.container, {style: callOverlayStyle});
			}

			if(this.stream)
			{
				this.callAspectCheckInterval = setInterval(this.checkVideoAspect.bind(this), 500);
			}

			this.adjustWindow();
		},

		setAudioMuted: function(audioMuted)
		{
			this.audioMuted = audioMuted;

			if(this.audioMuted)
			{
				BX.addClass(this.elements.micButton, "bx-messenger-call-float-button-mic-disabled");
				BX.adjust(this.elements.micButtonText, {text: BX.message('IM_M_CALL_BTN_MIC') + ' ' + BX.message('IM_M_CALL_BTN_MIC_OFF')});
			}
			else
			{
				BX.removeClass(this.elements.micButton, "bx-messenger-call-float-button-mic-disabled");
				BX.adjust(this.elements.micButtonText, {text: BX.message('IM_M_CALL_BTN_MIC') + ' ' + BX.message('IM_M_CALL_BTN_MIC_ON')});
			}
		},

		setTitle: function(title)
		{
			this.title = title;
			this.elements.title.innerText = title;
			BX.desktop.setWindowTitle(this.title);
		},

		setAvatars: function(avatars)
		{
			this.avatars = avatars;

			this.elements.avatars.innerHTML = "";

			for (var userId in this.avatars)
			{
				var avatar = this.avatars[userId];
				var a = BX.create("div", {
					props: {className: "bx-messenger-call-float-avatar"},
					dataset: {
						userId: userId
					}
				});

				if (avatar != '')
				{
					a.style.backgroundImage = "url('" + avatar + "')";
				}

				this.elements.avatars.appendChild(a);
			}

			this.adjustWindow();
		},

		setTalking: function(talking)
		{
			this.talking = talking;

			if(this.elements.avatars)
			{
				for(var i = 0; i < this.elements.avatars.children.length; i++)
				{
					var element = this.elements.avatars.children[i];
					var userId = Number(element.dataset.userId);
					if (this.talking.includes(userId))
					{
						element.classList.add("talking");
					}
					else
					{
						//element.classList.add("talking");
						element.classList.remove("talking");
					}
				}
			}
		},

		onMainAreaClick: function(e)
		{
			BX.desktop.onCustomEvent("main", Events.onMainAreaClick, []);
			e.stopPropagation();
		},

		onMicButtonClick: function(e)
		{
			BX.desktop.onCustomEvent("main", Events.onMicButtonClick, [this.audioMuted]);

			e.stopPropagation();
		},

		onHangupButtonClick: function(e)
		{
			BX.desktop.onCustomEvent("main", Events.onHangupButtonClick, []);

			e.stopPropagation();
		},

		destroy: function()
		{
			this.stream = null;
			BX.desktop.removeCustomEvents(Events.setStream);
			BX.desktop.removeCustomEvents(Events.setAudioMuted);
		}
	};

})();