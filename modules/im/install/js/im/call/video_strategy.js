;(function()
{
	BX.namespace("BX.Call");
	if (BX.Call.VideoStrategy)
	{
		return;
	}

	var Type = {
		AllowAll: 'AllowAll',
		AllowNone: 'AllowNone',
		OnlySpeaker: 'OnlySpeaker',
		CurrentlyTalking: 'CurrentlyTalking'
	};

	var HOLD_VIDEO_SECONDS = 20;

	BX.Call.VideoStrategy = function(config)
	{
		/** @var {BX.Call.VoximplantCall} this.call */
		this.call = config.call;

		/** @var {BX.Call.View} this.callView */
		this.callView = config.callView;

		this.strategyType = config.strategyType || Type.AllowAll;

		// event handlers
		this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
		this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
		this.onCallViewSetCentralUserHandler = this.onCallViewSetCentralUser.bind(this);
		this.onCallViewLayoutChangeHandler = this.onCallViewLayoutChange.bind(this);

		this.users = {};

		this.init();
	};

	BX.Call.VideoStrategy.prototype.init = function()
	{
		if (this.strategyType === BX.Call.VideoStrategy.AllowAll)
		{
			this.call.allowVideoFrom(BX.Call.UserMnemonic.all);
		}
		else if (this.strategyType === BX.Call.VideoStrategy.AllowNone)
		{
			this.call.allowVideoFrom(BX.Call.UserMnemonic.none);
		}
		this.bindEvents();
	};

	BX.Call.VideoStrategy.prototype.bindEvents = function()
	{
		this.call.addEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.call.addEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		this.callView.subscribe(BX.Call.View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
		this.callView.subscribe(BX.Call.View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
	};

	BX.Call.VideoStrategy.prototype.removeEvents = function()
	{
		if (this.call)
		{
			this.call.removeEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
			this.call.removeEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		}

		if (this.callView)
		{
			this.callView.unsubscribe(BX.Call.View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
			this.callView.unsubscribe(BX.Call.View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
		}
	};

	BX.Call.VideoStrategy.prototype.setType = function(strategyType)
	{
		if (strategyType == this.strategyType)
		{
			return;
		}
		this.strategyType = strategyType;
		this.applyVideoLimit();
	};

	BX.Call.VideoStrategy.prototype.applyVideoLimit = function()
	{
		if (this.strategyType === Type.AllowAll)
		{
			this.call.allowVideoFrom(BX.Call.UserMnemonic.all);
		}
		else if (this.strategyType === Type.AllowNone)
		{
			this.call.allowVideoFrom(BX.Call.UserMnemonic.none);
		}
		else if (this.strategyType === Type.CurrentlyTalking)
		{
			var talkingUsers = this.getActiveUsers();
			console.log("talking users", talkingUsers);
			if (talkingUsers.length === 0)
			{
				this.call.allowVideoFrom(BX.Call.UserMnemonic.none);
			}
			else
			{
				this.call.allowVideoFrom(this.getActiveUsers());
			}
		}
	};

	/**
	 * return int[]
	 */
	BX.Call.VideoStrategy.prototype.getActiveUsers = function()
	{
		var result = [];
		for (var userId in this.users)
		{
			var user = this.users[userId];
			if(user.active)
			{
				result.push(user.id)
			}
		}

		return result;
	};

	BX.Call.VideoStrategy.prototype.onUserActiveChanged = function()
	{
		if (this.strategyType == Type.CurrentlyTalking)
		{
			this.applyVideoLimit();
		}
	};

	BX.Call.VideoStrategy.prototype.onCallUserVoiceStarted = function(data)
	{
		var userId = data.userId;
		if (!this.users[userId])
		{
			this.users[userId] = new User({
				id: userId,
				onActiveChanged: this.onUserActiveChanged.bind(this)
			});
		}

		this.users[userId].setTalking(true);
	};

	BX.Call.VideoStrategy.prototype.onCallUserVoiceStopped = function(data)
	{
		var userId = data.userId;
		if (!this.users[userId])
		{
			this.users[userId] = new User({
				id: userId,
				onActiveChanged: this.onUserActiveChanged.bind(this)
			});
		}

		this.users[userId].setTalking(false);
	};

	BX.Call.VideoStrategy.prototype.onCallViewSetCentralUser = function(event)
	{
		var userId = event.data.userId;

		if (this.strategyType === Type.OnlySpeaker)
		{
			console.log('requesting video only from ' + userId);
			this.call.allowVideoFrom([userId]);
		}
	};

	BX.Call.VideoStrategy.prototype.onCallViewLayoutChange = function(event)
	{

	};

	BX.Call.VideoStrategy.prototype.destroy = function()
	{
		this.removeEvents();
		this.call = null;
		this.callView = null;

		for(var userId in this.users)
		{
			if(this.users.hasOwnProperty(userId))
			{
				this.users[userId].destroy();
			}
		}
		this.users = {};
	};

	var User = function(config)
	{
		this.id = config.id;
		this.talking = false;
		this.sharing = false;

		this.active = false;

		this.callbacks = {
			onActiveChanged: BX.type.isFunction(config.onActiveChanged) ? config.onActiveChanged : BX.DoNothing
		};

		this.turnOffVideoTimeout = null;
	};

	User.prototype.setTalking = function(talking)
	{
		if (this.talking == talking)
		{
			return;
		}
		this.talking = talking;
		if (this.talking)
		{
			this.cancelTurnOffVideo();
			this.updateActive();
		}
		else
		{
			this.scheduleTurnOffVideo();
		}
	};

	User.prototype.setSharing = function(sharing)
	{
		if (this.sharing == sharing)
		{
			return;
		}
		this.sharing = sharing;
		if (this.sharing)
		{
			this.cancelTurnOffVideo();
			this.updateActive();
		}
		else
		{
			this.scheduleTurnOffVideo();
		}
	};

	User.prototype.updateActive = function()
	{
		var newActive = !!(this.sharing || this.talking || this.turnOffVideoTimeout);
		if (newActive != this.active)
		{
			this.active = newActive;
		}
		this.callbacks.onActiveChanged({
			userId: this.id,
			active: this.active
		});
	};

	User.prototype.scheduleTurnOffVideo = function()
	{
		clearTimeout(this.turnOffVideoTimeout);
		this.turnOffVideoTimeout = setTimeout(function ()
		{
			this.turnOffVideoTimeout = null;
			this.updateActive();
		}.bind(this), HOLD_VIDEO_SECONDS * 1000);
	};

	User.prototype.cancelTurnOffVideo = function()
	{
		clearTimeout(this.turnOffVideoTimeout);
		this.turnOffVideoTimeout = null;
	};

	User.prototype.destroy = function()
	{
		this.callbacks.onActiveChanged = BX.DoNothing;
		clearTimeout(this.turnOffVideoTimeout);
	};

	BX.Call.VideoStrategy.Type = Type;
})();