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
	 * - onJoin
	 * - onLeave
	 * - onUserStateChanged
	 * - onStreamReceived
	 * - onStreamRemoved
	 * - onCallFailure
	 * - onDestroy
	 */

	BX.namespace('BX.Call');

	BX.Call.AbstractCall = function(params)
	{
		var self = this;
		this.id = params.id;
		this.instanceId = params.instanceId;
		this.parentId = params.parentId || null;
		this.direction = params.direction;
		this.type = BX.prop.getInteger(params, "type", BX.Call.Type.Instant); // @see {BX.Call.Type}
		this.state = BX.prop.getString(params, "state", BX.Call.State.Idle);

		this.ready = false;
		this.userId = BX.Call.Engine.getInstance().getCurrentUserId();

		this.initiatorId = params.initiatorId || '';
		this.users = BX.type.isArray(params.users) ? params.users.filter(function(userId){return userId != self.userId}) : [];

		this.associatedEntity = BX.type.isPlainObject(params.associatedEntity) ? params.associatedEntity : {};
		this.startDate = new Date(BX.prop.getString(params, "startDate", ""));

		// media constraints
		this.videoEnabled = params.videoEnabled === true;
		this.videoHd = params.videoHd === true;
		this.cameraId = params.cameraId || '';
		this.microphoneId = params.microphoneId || '';

		this.muted = params.muted === true;

		this.wasConnected = false;

		this.logToken = params.logToken || '';
		if(BX.CallEngine.getLogService() && this.logToken)
		{
			this.logger = new BX.Call.Logger(BX.CallEngine.getLogService(), this.logToken);
		}

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

		Object.defineProperty(this, "provider", {
			get: function()
			{
				if(this instanceof BX.Call.PlainCall)
				{
					return BX.Call.Provider.Plain;
				}
				else if (this instanceof BX.Call.VoximplantCall)
				{
					return BX.Call.Provider.Voximplant;
				}
				else
				{
					return "";
				}
			}
		})

		this._microphoneLevel = 0;
		Object.defineProperty(this, "microphoneLevel", {
			get: function()
			{
				return this._microphoneLevel
			},
			set: function(level)
			{
				if (level != this._microphoneLevel)
				{
					this._microphoneLevel = level;
					this.runCallback(BX.Call.Event.onMicrophoneLevel, {
						level: level
					});
				}
			}
		})
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
		//console.log(eventName, eventFields);
		if(BX.type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].length > 0)
		{

			if(eventName === null || typeof (eventFields) !== "object")
			{
				eventFields = {};
			}
			eventFields.call = this;
			for (var i = 0; i < this.eventListeners[eventName].length; i++)
			{
				try 
				{
					this.eventListeners[eventName][i].call(this, eventFields);	
				}
				catch (err)
				{
					console.error(eventName + " callback error: ", err);
					this.log(eventName + " callback error: ", err);
				}
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

	BX.Call.AbstractCall.prototype.isAnyoneParticipating = function()
	{
		throw new Error("isAnyoneParticipating should be implemented");
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
		var text = BX.Call.Util.getLogMessage.apply(BX.Call.Util, arguments);

		if (BX.desktop && BX.desktop.ready())
		{
			BX.desktop.log(BX.message('USER_ID')+'.video.log', text.substr(3));
		}
		if (BX.CallEngine.debugFlag && console)
		{
			var a = ['Call log [' + BX.Call.Util.getTimeForLog() + ']: '];
			console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
		}
		if(this.logger)
		{
			this.logger.log(text);
		}

		if(BX.MessengerDebug)
		{
			BX.MessengerDebug.addLog(this.id, text);
		}
	};

	BX.Call.AbstractCall.prototype.destroy = function()
	{
		if (this.logger)
		{
			this.logger.destroy();
			this.logger = null;
		}

		this.state = BX.Call.State.Finished;
		this.runCallback(BX.Call.Event.onDestroy);
	}
})();