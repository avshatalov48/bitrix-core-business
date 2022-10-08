;(function() {
	"use strict";

	BX.namespace("BX.Landing.MediaPlayer");

	var matchers = BX.Landing.Utils.Matchers;

	/**
	 * Implements players factory
	 * @param {HTMLIFrameElement} iframe
	 * @constructor
	 */
	BX.Landing.MediaPlayer.Factory = function(iframe)
	{
		this.player = BX.Landing.MediaPlayer.Factory.create(iframe);
	};

	/**
	 * Creates player instance
	 * @param {HTMLIFrameElement} iframe
	 * @param {Object} params - additional params for player
	 * @return {BX.Landing.MediaPlayer.BasePlayer}
	 */
	BX.Landing.MediaPlayer.Factory.create = function(iframe, params)
	{
		if (matchers.youtube.test(iframe.src))
		{
			return new BX.Landing.MediaPlayer.Youtube(iframe, params);
		}
	};
})();