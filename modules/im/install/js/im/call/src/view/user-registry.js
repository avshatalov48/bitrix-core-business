import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {EndpointDirection, UserState} from '../engine/engine';

export class UserModel
{
	id: number
	name: string
	avatar: string
	gender: string
	state: string
	talking: boolean
	cameraState: boolean
	microphoneState: boolean
	screenState: boolean
	videoPaused: boolean
	floorRequestState: boolean
	localUser: boolean
	centralUser: boolean
	pinned: boolean
	presenter: boolean
	order: number
	allowRename: boolean
	wasRenamed: boolean
	renameRequested: boolean
	direction: string

	constructor(config)
	{
		this.data = {
			id: BX.prop.getInteger(config, "id", 0),
			name: BX.prop.getString(config, "name", ""),
			avatar: BX.prop.getString(config, "avatar", ""),
			gender: BX.prop.getString(config, "gender", ""),
			state: BX.prop.getString(config, "state", UserState.Idle),
			talking: BX.prop.getBoolean(config, "talking", false),
			cameraState: BX.prop.getBoolean(config, "cameraState", true),
			microphoneState: BX.prop.getBoolean(config, "microphoneState", true),
			screenState: BX.prop.getBoolean(config, "screenState", false),
			videoPaused: BX.prop.getBoolean(config, "videoPaused", false),
			floorRequestState: BX.prop.getBoolean(config, "floorRequestState", false),
			localUser: BX.prop.getBoolean(config, "localUser", false),
			centralUser: BX.prop.getBoolean(config, "centralUser", false),
			pinned: BX.prop.getBoolean(config, "pinned", false),
			presenter: BX.prop.getBoolean(config, "presenter", false),
			order: BX.prop.getInteger(config, "order", false),
			allowRename: BX.prop.getBoolean(config, "allowRename", false),
			wasRenamed: BX.prop.getBoolean(config, "wasRenamed", false),
			renameRequested: BX.prop.getBoolean(config, "renameRequested", false),
			direction: BX.prop.getString(config, "direction", EndpointDirection.SendRecv),
		};

		for (let fieldName in this.data)
		{
			if (this.data.hasOwnProperty(fieldName))
			{
				Object.defineProperty(this, fieldName, {
					get: this._getField(fieldName).bind(this),
					set: this._setField(fieldName).bind(this),
				});
			}
		}

		this.onUpdate = {
			talking: this._onUpdateTalking.bind(this),
			state: this._onUpdateState.bind(this),
		};

		this.talkingStop = null;

		this.eventEmitter = new EventEmitter(this, 'UserModel');
	};

	_getField(fieldName)
	{
		return function ()
		{
			return this.data[fieldName];
		}
	};

	_setField(fieldName)
	{
		return function (newValue)
		{
			var oldValue = this.data[fieldName];
			if (oldValue == newValue)
			{
				return;
			}
			this.data[fieldName] = newValue;

			if (this.onUpdate.hasOwnProperty(fieldName))
			{
				this.onUpdate[fieldName](newValue, oldValue);
			}

			this.eventEmitter.emit("changed", {
				user: this,
				fieldName: fieldName,
				oldValue: oldValue,
				newValue: newValue,
			});
		}
	};

	_onUpdateTalking(talking)
	{
		if (talking)
		{
			this.floorRequestState = false;
		}
		else
		{
			this.talkingStop = (new Date()).getTime();
		}
	};

	_onUpdateState(newValue)
	{
		if (newValue != UserState.Connected)
		{
			this.talking = false;
			this.screenState = false;
		}
	};

	wasTalkingAgo()
	{
		if (this.state != UserState.Connected)
		{
			return +Infinity;
		}
		if (this.talking)
		{
			return 0;
		}
		if (!this.talkingStop)
		{
			return +Infinity;
		}

		return ((new Date()).getTime() - this.talkingStop);
	};

	subscribe(event, handler)
	{
		this.eventEmitter.subscribe(event, handler);
	};

	unsubscribe(event, handler)
	{
		this.eventEmitter.unsubscribe(event, handler);
	};
}

export class UserRegistry extends EventEmitter
{
	users: UserModel[];

	constructor(config:{users: UserModel[]} = {})
	{
		super();
		this.setEventNamespace('BX.Call.UserRegistry')
		this.users = Type.isArray(config.users) ? config.users : [];

		this._sort();
	};

	/**
	 *
	 * @param {int} userId
	 * @returns {UserModel|null}
	 */
	get(userId): ?UserModel
	{
		for (let i = 0; i < this.users.length; i++)
		{
			if (this.users[i].id == userId)
			{
				return this.users[i];
			}
		}
		return null;
	};

	push(user: UserModel)
	{
		if (!(user instanceof UserModel))
		{
			throw Error("user should be instance of UserModel")
		}

		this.users.push(user);
		this._sort();
		user.subscribe("changed", this._onUserChanged.bind(this));
		this.emit("userAdded", {
			user: user
		})
	};

	_onUserChanged(event)
	{
		if (event.data.fieldName === 'order')
		{
			this._sort();
		}
		this.emit("userChanged", event.data)
	};

	_sort()
	{
		this.users = this.users.sort(function (a, b)
		{
			return a.order - b.order;
		});
	}
}