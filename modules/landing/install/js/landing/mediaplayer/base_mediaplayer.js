;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaPlayer");

	var getQueryParameters = BX.Landing.Utils.getQueryParams;

	/**
	 * Implements base base interface
	 * @param {HTMLIFrameElement} iframe
	 * @constructor
	 */
	BX.Landing.MediaPlayer.BasePlayer = function(iframe)
	{
		this.iframe = iframe;
		this.parameters = getQueryParameters(iframe.src);

		Object.keys(this.parameters).forEach(function(key) {
			if (!isNaN(parseFloat(this.parameters[key])))
			{
				this.parameters[key] = parseFloat(this.parameters[key]);
			}
		}, this);
	};

	BX.Landing.MediaPlayer.BasePlayer.prototype = {
		/**
		 * Starts playback
		 * @abstract
		 */
		play: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Stops playback
		 * @abstract
		 */
		pause: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Stops video
		 */
		stop: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Sets loop playback
		 * @abstract
		 * @param {Boolean} value
		 */
		setLoop: function(value)
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Sets start video
		 * @abstract
		 * @param seconds
		 */
		seekTo: function(seconds)
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Disables sound
		 * @abstract
		 */
		mute: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Enables sound
		 * @abstract
		 */
		unMute: function()
		{
			throw new Error("Must be implemented by subclass");
		}
	};
})();