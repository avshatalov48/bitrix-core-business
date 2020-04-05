;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaPlayer");

	var addQueryParams = BX.Landing.Utils.addQueryParams;
	var getQueryParams = BX.Landing.Utils.getQueryParams;

	/**
	 * Implements interface for works with youtube player
	 * @extends {BX.Landing.MediaPlayer.BasePlayer}
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.MediaPlayer.Youtube = function(iframe)
	{
		BX.Landing.MediaPlayer.BasePlayer.apply(this, arguments);

		var src = iframe.src;

		if ((new RegExp("^\/\/")).test(src))
		{
			src = src.replace("//", "https://");
		}

		if ((new RegExp("^http:\/\/")).test(src))
		{
			src = src.replace("http://", "https://");
		}

		iframe.src = addQueryParams(src, {
			enablejsapi: 1
		});

		iframe.onload = function() {
			this.player = new YT.Player(iframe);

			this.player.addEventListener("onReady", function() {
				void (this.parameters.autoplay ? this.play() : this.pause());
				void (this.parameters.mute ? this.mute() : this.unMute());
				void (this.parameters.loop ? this.setLoop(true) : this.setLoop(false));
			}.bind(this));
		}.bind(this);
	};

	BX.Landing.MediaPlayer.Youtube.prototype = {
		constructor: BX.Landing.MediaPlayer.Youtube,
		__proto__: BX.Landing.MediaPlayer.BasePlayer.prototype,

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