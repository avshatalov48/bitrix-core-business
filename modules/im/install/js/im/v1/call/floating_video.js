;(function () {
	BX.namespace('BX.Call');

	if (BX.Call.FloatingVideo)
	{
		return;
	}

	var Events = {
		setStream: "FloatingVideo::setStream",
		setAudioMuted: "FloatingVideo::setAudioMuted",

		onMainAreaClick: "FloatingVideo::onMainAreaClick",
		onMicButtonClick: "FloatingVideo::onMicButtonClick",
		onHangupButtonClick: "FloatingVideo::onHangupButtonClick"
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
		this.stream = config.stream && BX.Call.Util.containsVideoTrack(config.stream) ? URL.createObjectURL(config.stream) : null;
		this.audioMuted = config.audioMuted || false;

		this.window = null;
		this.visible = false;

		this.callbacks = {
			onMainAreaClick: BX.type.isFunction(config.onMainAreaClick) ? config.onMainAreaClick : BX.DoNothing,
			onButtonClick: BX.type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};

		this._onContentMainAreaClickHandler = this._onContentMainAreaClick.bind(this);
		this._onContentMicButtonClickHandler = this._onContentMicButtonClick.bind(this);
		this._onContentHangupButtonClickHandler = this._onContentHangupButtonClick.bind(this);

		this.bindEventHandlers();
	};

	BX.Call.FloatingVideo.prototype =
	{
		bindEventHandlers: function ()
		{
			BX.desktop.addCustomEvent(Events.onMainAreaClick, this._onContentMainAreaClickHandler);
			BX.desktop.addCustomEvent(Events.onMicButtonClick, this._onContentMicButtonClickHandler);
			BX.desktop.addCustomEvent(Events.onHangupButtonClick, this._onContentHangupButtonClickHandler);
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
				this.stream = URL.createObjectURL(stream);
			}
			else
			{
				this.stream = null;
			}


			if(this.window && this.visible)
			{
				BX.desktop.onCustomEvent(this.window, Events.setStream, [this.stream]);
			}
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
				BX.desktop.onCustomEvent(this.window, Events.setStream, [this.stream]);
			}
			else
			{
				var params = {
					stream: this.stream,
					audioMuted: this.audioMuted
				};

				this.window = BXDesktopSystem.ExecuteCommand('topmost.show.html', BX.desktop.getHtmlPage("", "window.FVC = new BX.Call.FloatingVideoContent(" + JSON.stringify(params) + ");"));
			}
			this.visible = true;
		},

		hide: function ()
		{
			if (!this.window || !this.window.document)
			{
				return false;
			}
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

		this.elements = {
			container: null,
			video: null,
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
	};

	BX.Call.FloatingVideoContent.prototype = {
		bindEventHandlers: function () {
			BX.desktop.addCustomEvent(Events.setStream, this.setStream.bind(this));
			BX.desktop.addCustomEvent(Events.setAudioMuted, this.setAudioMuted.bind(this));

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
							BX.create("span", {
								props: {className: 'bx-messenger-call-float-button-text'},
								text: BX.message('IM_M_CALL_BTN_UNFOLD')
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

			document.body.appendChild(this.elements.container);
		},

		adjustWindow: function ()
		{
			var minCallWidth = this.stream ? VIDEO_WIDTH : AUDIO_WIDTH;
			var minCallHeight = this.stream ? VIDEO_HEIGHT : AUDIO_HEIGHT;

			BX.desktop.setWindowMinSize({Width: minCallWidth, Height: minCallHeight});
			BX.desktop.setWindowResizable(false);
			BX.desktop.setWindowClosable(false);
			BX.desktop.setWindowResizable(false);
			BX.desktop.setWindowTitle("");

			if (BXDesktopSystem.QuerySettings('global_topmost_x', null))
			{
				BX.desktop.setWindowPosition({
					X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)),
					Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)),
					Width: minCallWidth,
					Height: minCallHeight,
					Mode: STP_FRONT
				});
				if (!BX.browser.IsMac())
					BX.desktop.setWindowPosition({
						X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)),
						Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)),
						Width: minCallWidth,
						Height: minCallHeight,
						Mode: STP_FRONT
					});
			}
			else
			{
				BX.desktop.setWindowPosition({
					X: STP_RIGHT,
					Y: STP_TOP,
					Width: minCallWidth,
					Height: minCallHeight,
					Mode: STP_FRONT
				});
				if (!BX.browser.IsMac())
					BX.desktop.setWindowPosition({
						X: STP_RIGHT,
						Y: STP_TOP,
						Width: minCallWidth,
						Height: minCallHeight,
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
					this.elements.video.src = this.stream;
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

		onMainAreaClick: function(e)
		{
			BX.desktop.onCustomEvent("main", Events.onMainAreaClick, []);
			e.stopPropagation();
		},

		onMicButtonClick: function(e)
		{
			//this.audioMuted = !this.audioMuted;
			BX.desktop.onCustomEvent("main", Events.onMicButtonClick, [this.audioMuted]);

			//BX.toggleClass(BX.proxy_context, 'bx-messenger-call-float-button-mic-disabled');
			//var text = BX.findChildByClassName(BX.proxy_context, "bx-messenger-call-float-button-text");
			//text.innerHTML = BX.message('IM_M_CALL_BTN_MIC') + ' ' + BX.message('IM_M_CALL_BTN_MIC_' + (this.audioMuted ? 'OFF' : 'ON'));

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