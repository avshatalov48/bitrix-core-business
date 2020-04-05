;(function()
{
	/**
	 * Abstract call class
	 * Public methods:
	 * - inviteUsers
	 * - cancel
	 * - answer
	 * - decline
	 * - hangup
	 *
	 * Events:
	 * - onUserStateChanged
	 * - onStreamReceived
	 * - onStreamRemoved
	 * - onCallFailure
	 * - onDestroy
	 *
	 */

	BX.namespace('BX.Call');

	BX.Call.AbstractCall = function(params)
	{
		var self = this;
		this.id = params.id;
		this.instanceId = params.instanceId;
		this.parentId = params.parentId || null;
		this.direction = params.direction;

		this.debug = params.debug || false;

		this.ready = false;
		this.userId = BX.Call.Engine.getInstance().getCurrentUserId();

		this.initiatorId = params.initiatorId || '';
		this.users = BX.type.isArray(params.users) ? params.users.filter(function(userId){return userId != self.userId}) : [];

		this.associatedEntity = BX.type.isPlainObject(params.associatedEntity) ? params.associatedEntity : {};

		// media constraints
		this.videoEnabled = params.videoEnabled === true;
		this.cameraId = params.cameraId || '';
		this.microphoneId = params.microphoneId || '';

		this.muted = params.muted === true;

		this.localStreams =
		{
			main: null,
			screen: null
		};

		this.eventListeners = {};

		if(BX.type.isPlainObject(params.events))
		{
			this.initEventListeners(params.events);
		}
	};

	BX.Call.AbstractCall.prototype.initEventListeners = function(eventListeners)
	{
		for(var eventName in eventListeners)
		{
			this.addEventListener(eventName, eventListeners[eventName]);
		}
	};

	BX.Call.AbstractCall.prototype.addEventListener = function(eventName, listener)
	{
		if(!BX.type.isArray(this.eventListeners[eventName]))
		{
			this.eventListeners[eventName] = [];
		}
		if(BX.type.isFunction(listener))
		{
			this.eventListeners[eventName].push(listener);
		}
	};

	BX.Call.AbstractCall.prototype.removeEventListener = function(eventName, listener)
	{
		if(BX.type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].indexOf(listener) >= 0)
		{
			var listenerIndex = this.eventListeners[eventName].indexOf(listener);
			if(listenerIndex >= 0)
			{
				this.eventListeners[eventName].splice(listenerIndex, 1);
			}
		}
	};

	BX.Call.AbstractCall.prototype.runCallback = function(eventName, eventFields)
	{
		if(BX.type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].length > 0)
		{
			if(!BX.type.isPlainObject(eventFields))
			{
				eventFields = {};
			}
			eventFields.call = this;
			for (var i = 0; i < this.eventListeners[eventName].length; i++)
			{
				this.eventListeners[eventName][i].call(this, eventFields);
			}
		}
	};

	BX.Call.AbstractCall.prototype.getLocalStream = function(tag)
	{
		return this.localStreams[tag];
	};

	BX.Call.AbstractCall.prototype.setLocalStream = function(mediaStream, tag)
	{
		tag = tag || "main";

		this.localStreams[tag] = mediaStream;
	};

	BX.Call.AbstractCall.prototype.isVideoEnabled = function()
	{
		return this.videoEnabled;
	};

	BX.Call.AbstractCall.prototype.__onPullEvent = function(command, params)
	{
		throw new Error("__onPullEvent should be implemented");
	};

	BX.Call.AbstractCall.prototype.inviteUsers = function()
	{
		throw new Error("inviteUsers is not implemented");
	};

	BX.Call.AbstractCall.prototype.cancel = function()
	{
		throw new Error("cancel is not implemented");
	};

	BX.Call.AbstractCall.prototype.answer = function()
	{
		throw new Error("answer is not implemented");
	};

	BX.Call.AbstractCall.prototype.decline = function(code, reason)
	{
		throw new Error("decline is not implemented");
	};

	BX.Call.AbstractCall.prototype.hangup = function()
	{
		throw new Error("hangup is not implemented");
	};

	BX.Call.AbstractCall.prototype.log = function()
	{
		var text = '';
		if (BX.desktop && BX.desktop.ready())
		{
			for (var i = 0; i < arguments.length; i++)
			{
				try
				{
					text = text+' | '+(typeof(arguments[i]) == 'object'? JSON.stringify(arguments[i]): arguments[i]);
				}
				catch (e)
				{
					text = text+' | (circular structure)';
				}

			}
			BX.desktop.log(BX.message('USER_ID')+'.video.log', text.substr(3));
		}
		if (this.debug)
		{
			if (console)
			{
				var a = ['Call log; '];
				console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
			}
		}
	};


})();