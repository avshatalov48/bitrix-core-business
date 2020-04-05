/*global define, YT*/
(function (root, factory) {
	if(typeof exports==='object' && typeof module!=='undefined') {
		module.exports = factory(require('video.js'));
	} else if(typeof define === 'function' && define.amd) {
		define(['videojs'], function(videojs){
			return (root.Wmv = factory(videojs));
		});
	} else {
		root.Wmv = factory(root.videojs);
	}
}(this, function(videojs) {
	'use strict';

	var Tech = videojs.getTech('Tech');

	var Wmv = videojs.extend(Tech, {

		constructor: function(options, ready) {
			this.options_ = options;
			Tech.call(this, options, ready);
			this.triggerReady();
		},

		src: function(s) {
		},

		controls: function(){
		},

		currentSrc: function() {
		},

		play: function () {
		},

		pause: function() {
		},

		paused: function() {
		},

		currentTime: function() {
		},

		setVolume: function() {
		},

		setPoster: function() {
		},

		volume: function() {
		},

		duration: function() {
		},

		buffered: function() {
		},

		supportsFullScreen: function() {
		},

		dispose: function() {
			Tech.prototype.dispose.call(this);
		},

		createEl: function() {
			var wmvPlayerId = this.options_.playerId + '_wmv_player';
			var wmvPlayerDiv = document.createElement('div');
			wmvPlayerDiv.setAttribute('id', wmvPlayerId);
			wmvPlayerDiv.setAttribute('class', 'vjs-tech');
			wmvPlayerDiv.style.zIndex = 5;
			this.wrapper = wmvPlayerDiv;
			return wmvPlayerDiv;
		}
	});

	Wmv.canPlaySource = function(e) {
		return Wmv.canPlayType(e.type);
	};

	Wmv.canPlayType = function(e) {
		return (e === 'video/x-ms-wmv');
	};

	Wmv.isSupported = function() {
		return true;
	};

	if (typeof videojs.registerTech !== 'undefined') {
		videojs.registerTech('Wmv', Wmv);
	} else {
		videojs.registerComponent('Wmv', Wmv);
	}
}));
