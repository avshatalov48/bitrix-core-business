import {Type} from 'main.core';
import {CallEvent, UserMnemonic} from './engine/engine';
import {View} from './view/view';

const StrategyType = {
	AllowAll: 'AllowAll',
	AllowNone: 'AllowNone',
	OnlySpeaker: 'OnlySpeaker',
	CurrentlyTalking: 'CurrentlyTalking'
};

const HOLD_VIDEO_SECONDS = 20;

export class VideoStrategy
{
	static Type = Type

	call: VoximplantCall
	callView: View
	strategyType: string

	constructor(config)
	{
		this.call = config.call;
		this.callView = config.callView;
		this.strategyType = config.strategyType || StrategyType.AllowAll;

		// event handlers
		this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
		this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
		this.onCallViewSetCentralUserHandler = this.onCallViewSetCentralUser.bind(this);
		this.onCallViewLayoutChangeHandler = this.onCallViewLayoutChange.bind(this);

		this.users = {};

		this.init();
	};

	init()
	{
		if (this.strategyType === StrategyType.AllowAll)
		{
			this.call.allowVideoFrom(UserMnemonic.all);
		}
		else if (this.strategyType === StrategyType.AllowNone)
		{
			this.call.allowVideoFrom(UserMnemonic.none);
		}
		this.bindEvents();
	};

	bindEvents()
	{
		this.call.addEventListener(CallEvent.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.call.addEventListener(CallEvent.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		this.callView.subscribe(View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
		this.callView.subscribe(View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
	};

	removeEvents()
	{
		if (this.call)
		{
			this.call.removeEventListener(CallEvent.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
			this.call.removeEventListener(CallEvent.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		}

		if (this.callView)
		{
			this.callView.unsubscribe(View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
			this.callView.unsubscribe(View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
		}
	};

	setType(strategyType)
	{
		if (strategyType == this.strategyType)
		{
			return;
		}
		this.strategyType = strategyType;
		this.applyVideoLimit();
	};

	applyVideoLimit()
	{
		if (this.strategyType === StrategyType.AllowAll)
		{
			this.call.allowVideoFrom(UserMnemonic.all);
		}
		else if (this.strategyType === StrategyType.AllowNone)
		{
			this.call.allowVideoFrom(UserMnemonic.none);
		}
		else if (this.strategyType === StrategyType.CurrentlyTalking)
		{
			var talkingUsers = this.getActiveUsers();
			console.log("talking users", talkingUsers);
			if (talkingUsers.length === 0)
			{
				this.call.allowVideoFrom(UserMnemonic.none);
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
	getActiveUsers()
	{
		var result = [];
		for (var userId in this.users)
		{
			var user = this.users[userId];
			if (user.active)
			{
				result.push(user.id)
			}
		}

		return result;
	};

	onUserActiveChanged()
	{
		if (this.strategyType == StrategyType.CurrentlyTalking)
		{
			this.applyVideoLimit();
		}
	};

	onCallUserVoiceStarted(data)
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

	onCallUserVoiceStopped(data)
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

	onCallViewSetCentralUser(event)
	{
		var userId = event.data.userId;

		if (this.strategyType === StrategyType.OnlySpeaker)
		{
			this.call.allowVideoFrom([userId]);
		}
	};

	onCallViewLayoutChange(event)
	{

	};

	destroy()
	{
		this.removeEvents();
		this.call = null;
		this.callView = null;

		for (var userId in this.users)
		{
			if (this.users.hasOwnProperty(userId))
			{
				this.users[userId].destroy();
			}
		}
		this.users = {};
	};
}

class User
{
	constructor(config)
	{
		this.id = config.id;
		this.talking = false;
		this.sharing = false;

		this.active = false;

		this.callbacks = {
			onActiveChanged: Type.isFunction(config.onActiveChanged) ? config.onActiveChanged : BX.DoNothing
		};

		this.turnOffVideoTimeout = null;
	};

	setTalking(talking)
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

	setSharing(sharing)
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

	updateActive()
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

	scheduleTurnOffVideo()
	{
		clearTimeout(this.turnOffVideoTimeout);
		this.turnOffVideoTimeout = setTimeout(
			() =>
			{
				this.turnOffVideoTimeout = null;
				this.updateActive();
			},
			HOLD_VIDEO_SECONDS * 1000
		);
	};

	cancelTurnOffVideo()
	{
		clearTimeout(this.turnOffVideoTimeout);
		this.turnOffVideoTimeout = null;
	};

	destroy()
	{
		this.callbacks.onActiveChanged = BX.DoNothing;
		clearTimeout(this.turnOffVideoTimeout);
	};
}