;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaPlayer");

	const getQueryParameters = BX.Landing.Utils.getQueryParams;
	const isFunction = BX.Landing.Utils.isFunction;

	/**
	 * Implements base base interface
	 * @param {HTMLIFrameElement} iframe
	 * @param {Object} additionalParameters - additional params for player
	 * @constructor
	 */
	BX.Landing.MediaPlayer.BasePlayer = function(iframe, additionalParameters)
	{
		this.iframe = iframe;
		this.parameters = getQueryParameters(iframe.src);
		Object.assign(this.parameters, additionalParameters);
		Object.keys(this.parameters).forEach(function(key) {
			if (!isNaN(parseFloat(this.parameters[key])))
			{
				this.parameters[key] = parseFloat(this.parameters[key]);
			}
		}, this);
		this.onPlayerReady = isFunction(additionalParameters.onPlayerReadyHandler)
			? additionalParameters.onPlayerReadyHandler
			: () => {}
		;
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