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
	static Type = StrategyType

	call: VoximplantCall
	callView: View
	strategyType: $Keys<typeof StrategyType>
	users: {[string]: User}

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

	setType(strategyType: $Keys<typeof StrategyType>)
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
			let talkingUsers = this.getActiveUsers();
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
		let result = [];
		for (let userId in this.users)
		{
			let user = this.users[userId];
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
		const userId = data.userId;
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
		const userId = data.userId;
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
		const userId = event.data.userId;

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

		for (let userId in this.users)
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
	id: number
	talking = false
	sharing = false
	active = false

	constructor(config)
	{
		this.id = config.id;

		this.callbacks = {
			onActiveChanged: Type.isFunction(config.onActiveChanged) ? config.onActiveChanged : BX.DoNothing
		};

		this.turnOffVideoTimeout = null;
	};

	setTalking(talking: boolean)
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

	setSharing(sharing: boolean)
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
		const newActive = !!(this.sharing || this.talking || this.turnOffVideoTimeout);
		if (newActive !== this.active)
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