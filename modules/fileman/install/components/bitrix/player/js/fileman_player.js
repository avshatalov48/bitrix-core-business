;(function(){

BX.namespace('BX.Fileman');

if(window.BX.Fileman.PlayerManager)
{
	return;
}

BX.Fileman.PlayerManager = {
	isStarted: false,
	players: [],
	playing: false,
	slider: null,
	addPlayer: function(player)
	{
		this.players.push(player);

		this.bindPlayerEvents(player);

		if(player.autostart || player.lazyload)
		{
			this.init();
		}
	},
	init: function()
	{
		if(this.isStarted)
		{
			return;
		}

		this.isStarted = true;

		BX.ready(BX.proxy(function () {
			BX.bind(window, 'scroll', BX.throttle(BX.Fileman.PlayerManager.onScroll, 300, BX.Fileman.PlayerManager));
			setTimeout(BX.delegate(BX.Fileman.PlayerManager.onScroll, BX.Fileman.PlayerManager), 50);
			if(window !== window.top)
			{
				if(BX.getClass('top.BX.SidePanel.Instance'))
				{
					var currentSlider = top.BX.SidePanel.Instance.getSliderByWindow(window);
					if(currentSlider)
					{
						this.slider = currentSlider;
						BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(function(event) {
							if(event.getSlider() === this.slider)
							{
								for(var i in this.players)
									{
										this.players[i].pause();
									}
								}
							}, this));
						}
					}
				}
		}, this));
	},
	bindPlayerEvents: function(player)
	{
		var events = player.getEventList();
		if(events)
		{
			for(var i = 0; i < events.length; i++)
			{
				BX.addCustomEvent(player, events[i], BX.proxy(function(player, eventName)
				{
					BX.onCustomEvent(BX.Fileman.PlayerManager, 'PlayerManager.' + eventName, [player])
				}, this));
			}
		}
	},
	onScroll: function()
	{
		if(this.players.length == 0)
		{
			return;
		}

		var topVisiblePlayer = false;
		var isAnyPlaying = false;

		for(var i in this.players)
		{
			var player = this.players[i];

			if(!BX(player.id))
			{
				this.players.splice(i, 1);
				continue;
			}

			if(player.lazyload && !player.inited)
			{
				if(this.isVisibleOnScreen(player.id, 2))
				{
					player.init();
				}
			}

			if(!player.autostart)
			{
				continue;
			}

			if(player.active)
			{
				continue;
			}

			if(player.isEnded())
			{
				continue;
			}

			if(this.isVisibleOnScreen(player.id, 1))
			{
				if(topVisiblePlayer === false)
				{
					topVisiblePlayer = player;
				}
			}
			else
			{
				if(player.isPlaying())
				{
					player.pause();
				}
			}

			if(player.isPlaying())
			{
				isAnyPlaying = true;
			}
		}

		if(isAnyPlaying)
		{
			return;
		}

		if(topVisiblePlayer !== false)
		{
			if(!topVisiblePlayer.inited)
			{
				topVisiblePlayer.autostart = true;
			}
			else if(topVisiblePlayer.isReady() && !topVisiblePlayer.isEnded())
			{
				topVisiblePlayer.mute(true);
				BX.addCustomEvent(topVisiblePlayer, 'Player:onClick', BX.proxy(topVisiblePlayer.disableMute, topVisiblePlayer));
				topVisiblePlayer.play();
			}
		}
	},
	getElementCoords: function(id)
	{
		var VISIBLE_OFFSET = 0.25;

		var box = BX(id).getBoundingClientRect();

		var elementHeight = box.bottom - box.top;
		var top = box.top + VISIBLE_OFFSET * elementHeight;
		var bottom = box.bottom - VISIBLE_OFFSET * elementHeight;

		var elementWidth = box.right - box.left;
		var left = box.left + VISIBLE_OFFSET * elementWidth;
		var right = box.right - VISIBLE_OFFSET * elementWidth;

		coords = {
			top: top + window.pageYOffset,
			bottom: bottom + window.pageYOffset,
			left: left + window.pageXOffset,
			right: right + window.pageXOffset,
			originTop: top,
			originLeft: left,
			originBottom: bottom,
			originRight: right
		};

		return coords;
	},
	isVisibleOnScreen: function (id, screens)
	{
		var onScreen,
			visible = false;

		var coords = this.getElementCoords(id);
		var clientHeight = document.documentElement.clientHeight;

		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + clientHeight;

		if(screens)
		{
			screens = parseInt(screens);
		}
		if(screens > 1)
		{
			windowTop -= clientHeight * (screens - 1);
			windowBottom += clientHeight * (screens - 1);
		}
		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		onScreen = topVisible || bottomVisible;

		if(onScreen && screens > 1)
		{
			return true;
		}

		if(!onScreen)
		{
			return false;
		}

		var playerElement = BX(id);
		var playerCenterX = coords.originLeft + (coords.originRight - coords.originLeft) / 2;
		var playerCenterY = coords.originTop + (coords.originBottom - coords.originTop) / 2 + 20;

		var currentPlayerCenterElement = document.elementFromPoint(playerCenterX, playerCenterY);

		if(!!currentPlayerCenterElement)
		{
			if(currentPlayerCenterElement === playerElement ||
				currentPlayerCenterElement.parentNode === playerElement ||
				currentPlayerCenterElement.parentNode.parentNode === playerElement)
			{
				visible = true;
			}
		}

		return (onScreen && visible);
	},
	getPlayerById: function(id)
	{
		if(!id)
		{
			return null;
		}
		for(var i in this.players)
		{
			if(this.players[i].id === id)
			{
				return this.players[i];
			}
		}

		return null;
	}
};

BX.Fileman.Player = function(id, params)
{
	this.inited = false;
	this.id = id;
	this.fillParameters(params);
	BX.Fileman.PlayerManager.addPlayer(this);
	this.fireEvent('onCreate');
	BX.bind(BX(this.id), 'click', BX.proxy(this.onClick, this));
	BX.bind(BX(this.id), 'keydown', BX.proxy(this.onKeyDown, this));
};

BX.Fileman.Player.prototype.onClick = function()
{
	var playButton = BX.findChildByClassName(this.getElement(), 'vjs-play-control');
	if(playButton)
	{
		playButton.focus();
	}
	this.active = true;
	this.fireEvent('onClick');
};

BX.Fileman.Player.prototype.isPlaying = function()
{
	if(this.vjsPlayer)
	{
		return (this.vjsPlayer.isReady_ && !this.vjsPlayer.paused());
	}
	return false;
};

BX.Fileman.Player.prototype.pause = function()
{
	try
	{
		this.vjsPlayer.pause();
	}
	catch(e) {}
	this.fireEvent('onPause');
};

BX.Fileman.Player.prototype.isEnded = function()
{
	if(this.vjsPlayer)
	{
		return this.vjsPlayer.ended();
	}
	return false;
};

BX.Fileman.Player.prototype.isReady = function()
{
	return this.vjsPlayer.isReady_;
};

BX.Fileman.Player.prototype.play = function()
{
	this.setPlayedState();
	try
	{
		this.vjsPlayer.play();
	}
	catch(e) {}
	this.fireEvent('onPlay');
};

BX.Fileman.Player.prototype.setPlayedState = function()
{
	var storageHash = this.__getStorageHash();
	if(BX.localStorage)
	{
		BX.localStorage.set(storageHash, 'played', 1209600);
	}
};

BX.Fileman.Player.prototype.isPlayed = function()
{
	var storageHash = this.__getStorageHash();
	if(BX.localStorage)
	{
		return (BX.localStorage.get(storageHash) === 'played');
	}
	return true;
};

BX.Fileman.Player.prototype.__getStorageHash = function()
{
	var storageHash = this.id;
	if(this.params.sources && BX.type.isArray(this.params.sources) && this.params.sources[0].src)
	{
		storageHash = this.params.sources[0].src;
	}

	return 'player_' + storageHash;
};

BX.Fileman.Player.prototype.getElement = function()
{
	return BX(this.id);
};

BX.Fileman.Player.prototype.createElement = function()
{
	var node = this.getElement();
	if(node)
	{
		return node;
	}
	if(!this.id)
	{
		return null;
	}
	var tagName = 'video';
	if(this.isAudio)
	{
		tagName = 'audio';
	}
	var className = 'video-js vjs-big-play-centered';
	if(this.skin)
	{
		className += ' ' + this.skin;
	}
	var attrs = {
		'id': this.id,
		'className': className,
		'width': this.width,
		'height': this.height,
		'controls': true
	};
	if(this.muted)
	{
		attrs['muted'] = true;
	}
	node = BX.create(tagName, {
		'attrs': attrs
	});
	if(this.params.sources)
	{
		if(BX.type.isArray(this.params.sources))
		{
			for(var i in this.params.sources)
			{
				if(!this.params.sources[i].src || !this.params.sources[i].type)
				{
					continue;
				}
				var source = BX.create('source', {
					'attrs': {
						'src': this.params.sources[i].src,
						'type': this.params.sources[i].type
					}
				});
				BX.append(source, node);
			}
		}
	}
	return node;
};

BX.Fileman.Player.prototype.fillParameters = function(params)
{
	this.autostart = params.autostart || false;
	this.width = params.width || 400;
	this.height = params.height || 300;
	this.hasFlash = params.hasFlash || false;
	if(params.playbackRate && !params.hasFlash)
	{
		params.playbackRate = parseInt(params.playbackRate);
		if(params.playbackRate != 1)
		{
			if(params.playbackRate <= 0)
			{
				params.playbackRate = 1;
			}
			if(params.playbackRate > 3)
			{
				params.playbackRate = 3;
			}
		}
		if(params.playbackRate != 1)
		{
			this.playbackRate = params.playbackRate;
		}
	}
	this.volume = params.volume || 0.8;
	this.playlistParams = params.playlistParams || false;
	this.startTime = params.startTime || 0;
	this.wmvConfig = params.wmvConfig || false;
	this.onInit = params.onInit;
	this.lazyload = params.lazyload;
	this.skin = params.skin || '';
	this.params = params;
	this.active = this.isPlayed();
};

BX.Fileman.Player.prototype.onKeyDown = function(event)
{
	if(event.which == 32)
	{
		this.onClick();
		if(this.isPlaying())
		{
			this.pause();
		}
		else
		{
			this.play();
		}
		event.preventDefault();
		event.stopPropagation();
		return false;
	}
	this.fireEvent('onKeyDown');
};

BX.Fileman.Player.prototype.setSource = function(source)
{
	if(!source)
	{
		return false;
	}
	this.vjsPlayer.src(source);
	this.fireEvent('onSetSource');
};

BX.Fileman.Player.prototype.getSource = function()
{
	return this.vjsPlayer.src();
};

BX.Fileman.Player.prototype.init = function()
{
	this.fireEvent('onBeforeInit');
	if(videojs.players[this.id])
	{
		delete videojs.players[this.id];
	}
	this.vjsPlayer = videojs(this.id, this.params);
	this.vjsPlayer.on('error', BX.proxy(function()
	{
		this.fireEvent('onError');
		if(!this.isFlashErrrorShown && this.hasFlash)
		{
			this.isFlashErrrorShown = true;
			var error = this.vjsPlayer.error();
			if(error && error.code === 4)
			{
				error.message = error.message + '. ' + BX.message('PLAYER_FLASH_CHECK');
				this.vjsPlayer.errorDisplay.content(error.message);
			}
		}
	}, this));
	if(this.hasFlash)
	{
		setTimeout(BX.proxy(function()
		{
			if(!this.inited)
			{
				this.vjsPlayer.error(BX.message('PLAYER_FLASH_REQUIRED'));
				this.inited = true;
			}
		}, this), 3000);
	}
	this.vjsPlayer.ready(BX.proxy(function(){
		var playButton = BX.findChildByClassName(BX(this.id), 'vjs-play-control');
		if(playButton)
		{
			playButton.addEventListener('click', BX.proxy(this.onClick, this));
		}
		this.vjsPlayer.volume(this.volume);
		this.vjsPlayer.one('play', BX.proxy(function()
		{
			if(this.playbackRate != 1)
			{
				this.vjsPlayer.playbackRate(this.playbackRate);
			}
			if(this.volume)
			{
				this.vjsPlayer.volume(this.volume);
			}
			if(this.startTime > 0)
			{
				try
				{
					this.vjsPlayer.currentTime(this.startTime);
					var spinner = BX.findChild(BX(this.id),
						{
							"class" : "vjs-loading-spinner"
						},
						false
					);
					if(spinner)
					{
						spinner.remove();
					}
				}
				catch(error)
				{

				}
			}
		}, this));
		if(this.playlistParams)
		{
			this.vjsPlayer.playlist(this.playlistParams);
		}
		if(this.wmvConfig)
		{
			this.vjsPlayer.wmvConfig = this.wmvConfig;
		}
		this.inited = true;
		if(BX.type.isFunction(this.onInit))
		{
			this.onInit(this);
		}
		this.fireEvent('onAfterInit');
		this.proxyEvents();
	}, this));
};

BX.Fileman.Player.prototype.getEventList = function()
{
	return [
		'Player:onBeforeInit',
		'Player:onAfterInit',
		'Player:onCreate',
		'Player:onSetSource',
		'Player:onKeyDown',
		'Player:onPlay',
		'Player:onPause',
		'Player:onClick',
		'Player:onError',
		'Player:onEnded',
	];
};

BX.Fileman.Player.prototype.fireEvent = function(eventName)
{
	if (BX.type.isNotEmptyString(eventName))
	{
		eventName = 'Player:' + eventName;
		BX.onCustomEvent(this, eventName, [this, eventName]);
	}
};

BX.Fileman.Player.prototype.mute = function(mute)
{
	return this.vjsPlayer.muted(mute);
};

BX.Fileman.Player.prototype.disableMute = function()
{
	BX.removeCustomEvent(this, 'Player:onClick', BX.proxy(this.disableMute, this));
	setTimeout(BX.proxy(function()
	{
		this.mute(false);
	}, this), 100);
};

BX.Fileman.Player.prototype.proxyEvents = function()
{
	if(!this.inited)
	{
		return;
	}
	this.vjsPlayer.on('play', BX.proxy(function(){this.fireEvent('onPlay');}, this));
	this.vjsPlayer.on('pause', BX.proxy(function(){this.fireEvent('onPause');}, this));
	this.vjsPlayer.on('ended', BX.proxy(function(){this.fireEvent('onEnded');}, this));
}

})(window);