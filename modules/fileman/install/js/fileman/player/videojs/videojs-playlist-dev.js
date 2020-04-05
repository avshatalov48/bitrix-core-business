/**
 * videojs-playlist-thumbs
 * @version 0.1.5
 * @copyright 2016 Emmanuel Alves <manel.pb@gmail.com>
 * @license MIT
 */
(function(f){
	if(typeof exports==="object" && typeof module!=="undefined")
	{
		module.exports=f()
	}
	else if(typeof define==="function"&&define.amd)
	{
		define([],f)
	}
	else
	{
		var g;
		if(typeof this!=="undefined")
		{
			g=this
		}
		else if(typeof self!=="undefined")
		{
			g=self
		}
		else if(typeof window!=="undefined")
		{
			g=window
		}
		else if(typeof global!=="undefined")
		{
			g=global
		}
		g.videojsPlaylist = f()
	}
})(function()
{
	var define,module,exports;
	return (function e(t,n,r)
	{
		function s(o,u)
		{
			if(!n[o])
			{
				if(!t[o])
				{
					var a=typeof require=="function"&&require;
					if(!u&&a)
						return a(o,!0);
					if(i)
						return i(o,!0);
					var f=new Error("Cannot find module '"+o+"'");
					throw f.code="MODULE_NOT_FOUND",f
				}
				var l=n[o]={exports:{}};
				t[o][0].call(l.exports,function(e)
				{
					var n=t[o][1][e];
					return s(n?n:e)
				},l,l.exports,e,t,n,r)
			}return n[o].exports
		}
		var i=typeof require=="function"&&require;
		for(var o=0;o<r.length;o++)
			s(r[o]);
		return s
	})({1:[function(require,module,exports){
		(function (global){
			"use strict";

			Object.defineProperty(exports, "__esModule", {
				value: true
			});

			function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

			var _videoJs = (typeof window !== "undefined" ? window['videojs'] : typeof global !== "undefined" ? global['videojs'] : null);

			var _videoJs2 = _interopRequireDefault(_videoJs);

//var _videoJs2 = _interopRequireDefault(_videoJs);

// Default options for the plugin.
			var defaults = {
				thumbnailSize: 190,
				playlistItems: 3,
				hideIcons: false,
				upNext: true,
				hideSidebar: false,
				mobile: false
			};

			var player = undefined;
			var mobile = false;
			var currentIdx = [];
			var videos = [];
			var players = [];
			var playlistsElemens = [];
			var playlistsElemen = null;

			/**
			 * creates each video on the playlist
			 */
			var createVideoElement = function createVideoElement(player_id, idx, title, thumbnail) {
				var videoElement = document.createElement("li");
				var videoTitle = document.createElement("div");
				videoTitle.className = "vjs-playlist-video-title";
				videoTitle.setAttribute("data-index", idx);

				if (idx == 0) {
					if (defaults.upNext) {
						var upNext = document.createElement("div");
						upNext.className = "vjs-playlist-video-upnext";
						upNext.innerText = "UP Next";

						videoTitle.appendChild(upNext);
					}
				}

				if (title) {
					var videoTitleText = document.createElement("div");
					videoTitleText.innerText = title;
					videoTitleText.setAttribute("data-index", idx);

					videoTitle.appendChild(videoTitleText); // = "<span>" + title + "</span>";

					videoElement.appendChild(videoTitle);
				}

				if (thumbnail)
					videoElement.setAttribute("style", "background-image: url('" + thumbnail + "');");
				videoElement.setAttribute("data-index", idx);

				// when the user clicks on the playlist, the video will start playing
				videoElement.onclick = function (ev) {

					var idx = parseInt(ev.target.getAttribute("data-index"));

					// and play this video
					playVideo(player_id, idx, true);
				};

				return videoElement;
			};

			/**
			 * Function to invoke when the player is ready.
			 *
			 * This is a great place for your plugin to initialize itself. When this
			 * function is called, the player will have its DOM and child components
			 * in place.
			 *
			 * @function onPlayerReady
			 * @param    {Player} player
			 * @param    {Object} [options={}]
			 */
			var onPlayerReady = function onPlayerReady(player, options) {
				videos[player.id_] = options.videos;
				currentIdx[player.id_] = 0;
				mobile = options.playlist.mobile;

				if (options.playlist && options.playlist.thumbnailSize) {
					defaults.thumbnailSize = options.playlist.thumbnailSize.toString().replace("px", "");
				}

				if (options.playlist && options.playlist.items) {
					defaults.playlistItems = options.playlist.items;
				}

				if (options.playlist && options.playlist.hideIcons) {
					defaults.hideIcons = options.playlist.hideIcons;
				}

				if (options.playlist && options.playlist.hideSidebar) {
					defaults.hideSidebar = options.playlist.hideSidebar;
				}

				createElements(player, options);
				updateElementWidth(player);
			};

			/**
			 * Creates the root html elements for the playlist
			 */
			var createElements = function createElements(player, options) {
				// creates the playlist items and add on the video player
				playlistsElemen = document.createElement("ul");
				playlistsElemen.className = "vjs-playlist-items";

				if (!defaults.hideSidebar) {
					player.el().appendChild(playlistsElemen);
				}

				playlistsElemens[player.id_] = playlistsElemen;

				// plays the first video
				if (videos[player.id_].length > 0) {
					videos[player.id_].map(function (video, idx) {
						playlistsElemens[player.id_].appendChild(createVideoElement(player.id_, idx, video.title, video.thumbnail));
					});
					playVideo(player.id_, 0, false);
				}

				// create next and previous button
				if (!defaults.hideIcons) {
					var prevBtn = document.createElement("button");
					prevBtn.className = "vjs-button-prev";
					prevBtn.onclick = onPrevClick;

					player.controlBar.el().insertBefore(prevBtn, player.controlBar.playToggle.el());

					var nextBtn = document.createElement("button");
					nextBtn.className = "vjs-button-next";
					nextBtn.onclick = onNextClick;

					player.controlBar.el().insertBefore(nextBtn, player.controlBar.volumeMenuButton.el());
				}

				// creates the loading next on video ends
				player.on("ended", createPlayingNext);

				// adds the main class on the player
				player.addClass('vjs-playlist');

			};

			var createPlayingNext = function createPlayingNext() {
				nextVideo();
			};

			var onNextClick = function onNextClick(ev) {
				var player_id = ev.target.parentNode.parentNode.id;
				nextVideo(player_id);
			};

			var onPrevClick = function onPrevClick(ev) {
				var player_id = ev.target.parentNode.parentNode.id;
				previousVideo(player_id);
			};

			/**
			 * updates the main video player width
			 */
			var updateElementWidth = function updateElementWidth(player) {
				var resize = function resize(p) {
					var itemWidth = defaults.thumbnailSize;
					var playerId = p.el().id;
					var playerWidth = p.el().offsetWidth;
					var playerHeight = p.el().offsetHeight;
					var itemHeight = Math.floor((playerHeight - 10) / defaults.playlistItems) - 10;

					var youtube = p.$(".vjs-tech");
					var newSize = playerWidth - itemWidth;

					if (newSize >= 0) {
						var styleTagId = playerId + '_styles';
						var style = document.getElementById(styleTagId);
						if (!style)
							style = document.createElement('style');
						var def = ' #' + playerId + '.vjs-playlist .vjs-poster { width: ' + newSize + 'px !important; }' +
							' #' + playerId + '.vjs-playlist .vjs-playlist-items { width: ' + itemWidth + 'px !important; }' +
							' #' + playerId + '.vjs-playlist .vjs-playlist-items li { height: ' + itemHeight + 'px !important; }' +
							' #' + playerId + '.vjs-playlist .vjs-modal-dialog { width: ' + newSize + 'px !important; } ' +
							' #' + playerId + '.vjs-playlist .vjs-control-bar, #' + playerId + '.vjs-playlist .vjs-tech { width: ' + newSize + 'px !important; } ' +
							' #' + playerId + '.vjs-playlist .vjs-big-play-button, #' + playerId + '.vjs-playlist .vjs-loading-spinner { left: ' + Math.round(newSize / 2) + 'px !important; } ' +
							' #' + playerId + ' .vimeoFrame { width: ' + newSize + 'px !important; } ' +
							' #' + playerId + ' .vimeoFrame.vimeoHidden { padding-bottom: 0 !important; } ';
						style.setAttribute('id', styleTagId);
						style.setAttribute('type', 'text/css');
						document.getElementsByTagName('head')[0].appendChild(style);

						if (style.firstChild) {
							style.firstChild.textContent = def;
						} else {
							style.appendChild(document.createTextNode(def));
						}
					}
				};

				if (!defaults.hideSidebar) {
					player.on('fullscreenchange', function () {
						resize(this);
					});
					window.addEventListener('resize', function(event){
						resize(player);
					});

					if (player) {
						resize(player);
					}
				}
			};

			/**
			 * plays the video based on an index
			 */
			var playVideo = function playVideo(player_id, idx, autoPlay) {

				if (!player_id)
				{
					player_id = player.id_;
				}
				currentIdx[player_id] = idx;
				var playlistsElemen = players[player_id].el().querySelectorAll('ul.vjs-playlist-items li');
				for (var i = 0; i < playlistsElemen.length; i++) {
					if (i == idx)
					{
						playlistsElemen[i].classList.add('current');
						//playlistsElemen[i].parentNode.scrollTop = playlistsElemen[i].offsetHeight * i;
					}
					else
					{
						playlistsElemen[i].classList.remove('current');
					}
				}
				try
				{
					if(!players[player_id].paused())
					{
						players[player_id].pause();
						players[player_id].error(null);
					}
				}
				catch (e) {}
				var video = { type: videos[player_id][idx].type, src: videos[player_id][idx].src };
				var wmvPlayerId = player_id + '_wmv_player';
				if (video.type == 'video/x-ms-wmv')
				{
					var wmvPlayerDiv = document.getElementById(wmvPlayerId);
					if (!wmvPlayerDiv)
					{
						wmvPlayerDiv = document.createElement('div');
						wmvPlayerDiv.setAttribute('id', wmvPlayerId);
						wmvPlayerDiv.setAttribute('class', 'vjs-tech');
						players[player_id].el().appendChild(wmvPlayerDiv);
					}
					var wmvConfig = players[player_id].wmvConfig;
					wmvConfig.file = video.src;
					if (!!videos[player_id][idx].thumbnail)
						wmvConfig.image = videos[player_id][idx].thumbnail;
					var wmvPlayer = new window.jeroenwijering.Player(wmvPlayerDiv, '/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml',  players[player_id].wmvConfig);
				}
				else
				{
					// disable wmv-player
					var wmvPlayerDiv = document.getElementById (wmvPlayerId);
					if (!!wmvPlayerDiv)
						wmvPlayerDiv.parentNode.removeChild (wmvPlayerDiv);
					var vimeos = players[player_id].el().getElementsByClassName('vimeoFrame');
					for (var i = 0; i < vimeos.length; i++)
					{
						vimeos[i].classList.add('vimeoHidden');
					}
					if (video.type == 'video/vimeo')
					{
						var curVideoId = 'vimeo_wrapper_' + player_id;
						if (!!document.getElementById(curVideoId))
							document.getElementById(curVideoId).classList.remove('vimeoHidden');
					}
					players[player_id].src(video);
					players[player_id].poster(videos[player_id][idx].thumbnail);
					if (!mobile && (autoPlay || players[player_id].options_.autoplay)) {
						try {
							players[player_id].play();
						} catch (e) {}
					}
				}
			};

			/**
			 * plays the next video, if it comes to the end, loop
			 */
			var nextVideo = function nextVideo(player_id) {

				if (!player_id)
				{
					player_id = player.id_;
				}

				if (currentIdx[player_id] < videos[player_id].length - 1) {
					currentIdx[player_id]++;
				} else {
					currentIdx[player_id] = 0;
				}

				playVideo(player_id, currentIdx[player_id], true);
			};

			/**
			 * plays the previous video, if it comes to the first video, loop
			 */
			var previousVideo = function previousVideo(player_id) {
				if (!player_id)
				{
					player_id = player.id_;
				}
				if (currentIdx[player_id] > 0) {
					currentIdx[player_id]--;
				} else {
					currentIdx[player_id] = videos[player_id].length - 1;
				}
				playVideo(player_id, currentIdx[player_id], true);
			};

			/**
			 * A video.js plugin.
			 *
			 * In the plugin function, the value of `this` is a video.js `Player`
			 * instance. You cannot rely on the player being in a "ready" state here,
			 * depending on how the plugin is invoked. This may or may not be important
			 * to you; if not, remove the wait for "ready"!
			 *
			 * @function playlist
			 * @param    {Object} [options={}]
			 *           An object of options left to the plugin author to define.
			 */
			var playlist = function playlist(options) {

				var _this = this;

				this.ready(function () {
					player = _this;
					players[player.id_] = player;
					onPlayerReady(_this, _videoJs2["default"].mergeOptions(options, defaults));
				});
			};

			_videoJs2["default"].plugin('playlist', playlist);

			exports["default"] = playlist;
			module.exports = exports["default"];
		}).call(this,
			typeof this !== "undefined" ? this:
				typeof self !== "undefined" ? self :
					typeof global !== "undefined" ? global :
						typeof window !== "undefined" ? window : {}
		)
	},{}]},{},[1])(1)
});