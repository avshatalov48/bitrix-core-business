;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaPlayer");

	const addQueryParams = BX.Landing.Utils.addQueryParams;
	const getQueryParams = BX.Landing.Utils.getQueryParams;

	/**
	 * Implements interface for works with youtube player
	 * @extends {BX.Landing.MediaPlayer.BasePlayer}
	 * @param {HTMLIFrameElement} iframe
	 * @param {Object} additionalParameters - additional params for player
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.MediaPlayer.Youtube = function(iframe, additionalParameters)
	{
		BX.Landing.MediaPlayer.BasePlayer.apply(this, arguments);

		// load API
		if (!BX.Landing.MediaPlayer.Youtube.isApiAdded)
		{
			const tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			const firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

			BX.Landing.MediaPlayer.Youtube.isApiAdded = true;

			window.onYouTubeIframeAPIReady = BX.Landing.MediaPlayer.Youtube.onApiReady;
			window.onPlayerReady = BX.Landing.MediaPlayer.Youtube.onPlayerReady;
		}

		if (BX.Landing.MediaPlayer.Youtube.isApiLoaded())
		{
			this.init();
		}
		else
		{
			BX.Landing.MediaPlayer.Youtube.sheduldedPlayers.push(this);
		}

	};

	/**
	 * Check is YT iframe Api loaded
	 */
	BX.Landing.MediaPlayer.Youtube.isApiLoaded = function()
	{
		return (typeof YT !== "undefined" && typeof YT.Player !== "undefined");
	};

	/**
	 * Flag, indicating whether API was added on page (It doesn't matter if it's loaded or not yet)
	 * @type {boolean}
	 */
	BX.Landing.MediaPlayer.Youtube.isApiAdded = false;

	/**
	 * List of players, then added before api loaded, for lazy load
	 * @type {*[]}
	 */
	BX.Landing.MediaPlayer.Youtube.sheduldedPlayers = [];

	BX.Landing.MediaPlayer.Youtube.onApiReady = function()
	{
		BX.Landing.MediaPlayer.Youtube.sheduldedPlayers.forEach(mediaPlayer => {
			mediaPlayer.init();
		});
	}

	BX.Landing.MediaPlayer.Youtube.prototype = {
		constructor: BX.Landing.MediaPlayer.Youtube,
		__proto__: BX.Landing.MediaPlayer.BasePlayer.prototype,

		init: function()
		{
			let src = this.iframe.src;
			if ((new RegExp("^\/\/")).test(src))
			{
				src = src.replace("//", "https://");
			}
			if ((new RegExp("^http:\/\/")).test(src))
			{
				src = src.replace("http://", "https://");
			}

			this.iframe.src = addQueryParams(src, {
				enablejsapi: 1,
				origin: window.location.protocol + '//' + window.location.host,
			});

			this.player = new YT.Player(this.iframe, {
				events: {
					onReady: () => {
						void (this.parameters.autoplay ? this.play() : this.pause());
						void (this.parameters.mute ? this.mute() : this.unMute());
						void (this.parameters.loop ? this.setLoop(true) : this.setLoop(false));
						this.onPlayerReady();
					}
				}
			});
		},

		/**
		 * Starts playback
		 */
		play: function()
		{
			this.player.playVideo();
		},

		/**
		 * Stops playback
		 */
		pause: function()
		{
			this.player.pauseVideo();
		},

		/**
		 * Stops video
		 */
		stop: function()
		{
			this.player.stopVideo();
		},

		/**
		 * Sets loop playback
		 * @param {Boolean} value
		 */
		setLoop: function(value)
		{
			this.parameters.loop = value;

			if (!this.loopInited)
			{
				this.loopInited = true;
				this.player.addEventListener("onStateChange", function(event) {
					void (this.parameters.loop && event.data === 0 && this.play());
				}.bind(this));
			}
		},

		/**
		 * Sets start video
		 * @param seconds
		 */
		seekTo: function(seconds)
		{
			this.player.seekTo(seconds);
		},

		/**
		 * Disables sound
		 */
		mute: function()
		{
			this.player.mute();
		},

		/**
		 * Enables sound
		 */
		unMute: function()
		{
			this.player.unMute();
		}
	};
})();