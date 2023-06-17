import {Type} from 'main.core'
import {Logger} from './logger'
import {CallType, CallEvent, CallState, CallEngine, Provider} from './engine'
import Util from '../util'

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
export class AbstractCall
{
	logger: ?Logger
	localStreams: {[key: string]: ?MediaStream}

	constructor(params)
	{
		this.id = params.id;
		this.instanceId = params.instanceId;
		this.parentId = params.parentId || null;
		this.direction = params.direction;
		this.type = BX.prop.getInteger(params, "type", CallType.Instant); // @see {BX.Call.Type}
		this.state = BX.prop.getString(params, "state", CallState.Idle);

		this.ready = false;
		this.userId = CallEngine.getCurrentUserId();

		this.initiatorId = params.initiatorId || '';
		this.users = Type.isArray(params.users) ? params.users.filter(userId => userId != this.userId) : [];

		this.associatedEntity = Type.isPlainObject(params.associatedEntity) ? params.associatedEntity : {};
		this.startDate = new Date(BX.prop.getString(params, "startDate", ""));

		// media constraints
		this.videoEnabled = params.videoEnabled === true;
		this.videoHd = params.videoHd === true;
		this.cameraId = params.cameraId || '';
		this.microphoneId = params.microphoneId || '';

		this.muted = params.muted === true;

		this.wasConnected = false;

		this.logToken = params.logToken || '';
		if (CallEngine.getLogService() && this.logToken)
		{
			this.logger = new Logger(CallEngine.getLogService(), this.logToken);
		}

		this.localStreams = {
			main: null,
			screen: null
		};

		this.eventListeners = {};

		if (Type.isPlainObject(params.events))
		{
			this.initEventListeners(params.events);
		}

		this._microphoneLevel = 0;
	};

	get provider()
	{
		throw new Error("must be overwritten")
	}

	get microphoneLevel()
	{
		return this._microphoneLevel
	}

	set microphoneLevel(level)
	{
		if (level != this._microphoneLevel)
		{
			this._microphoneLevel = level;
			this.runCallback(CallEvent.onMicrophoneLevel, {
				level: level
			});
		}
	}

	initEventListeners(eventListeners)
	{
		for (var eventName in eventListeners)
		{
			this.addEventListener(eventName, eventListeners[eventName]);
		}
	};

	addEventListener(eventName, listener)
	{
		if (!Type.isArray(this.eventListeners[eventName]))
		{
			this.eventListeners[eventName] = [];
		}
		if (Type.isFunction(listener))
		{
			this.eventListeners[eventName].push(listener);
		}
	};

	removeEventListener(eventName, listener)
	{
		if (Type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].indexOf(listener) >= 0)
		{
			var listenerIndex = this.eventListeners[eventName].indexOf(listener);
			if (listenerIndex >= 0)
			{
				this.eventListeners[eventName].splice(listenerIndex, 1);
			}
		}
	};

	runCallback(eventName, eventFields)
	{
		//console.log(eventName, eventFields);
		if (Type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].length > 0)
		{
			if (eventName === null || typeof (eventFields) !== "object")
			{
				eventFields = {};
			}
			eventFields.call = this;
			for (let i = 0; i < this.eventListeners[eventName].length; i++)
			{
				try
				{
					this.eventListeners[eventName][i].call(this, eventFields);
				} catch (err)
				{
					console.error(eventName + " callback error: ", err);
					this.log(eventName + " callback error: ", err);
				}
			}
		}
	};

	getLocalStream(tag)
	{
		return this.localStreams[tag];
	};

	setLocalStream(mediaStream, tag)
	{
		tag = tag || "main";

		this.localStreams[tag] = mediaStream;
	};

	isVideoEnabled()
	{
		return this.videoEnabled;
	};

	isAnyoneParticipating()
	{
		throw new Error("isAnyoneParticipating should be implemented");
	};

	__onPullEvent(command, params)
	{
		throw new Error("__onPullEvent should be implemented");
	};

	inviteUsers()
	{
		throw new Error("inviteUsers is not implemented");
	};

	cancel()
	{
		throw new Error("cancel is not implemented");
	};

	answer()
	{
		throw new Error("answer is not implemented");
	};

	decline(code, reason)
	{
		throw new Error("decline is not implemented");
	};

	hangup()
	{
		throw new Error("hangup is not implemented");
	};

	log()
	{
		let text = Util.getLogMessage.apply(null, arguments);

		if (BX.desktop && BX.desktop.ready())
		{
			BX.desktop.log(BX.message('USER_ID') + '.video.log', text.substr(3));
		}
		if (CallEngine.debugFlag && console)
		{
			let a = ['Call log [' + Util.getTimeForLog() + ']: '];
			console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
		}
		if (this.logger)
		{
			this.logger.log(text);
		}

		if (BX.MessengerDebug)
		{
			BX.MessengerDebug.addLog(this.id, text);
		}
	};

	destroy()
	{
		if (this.logger)
		{
			this.logger.destroy();
			this.logger = null;
		}

		this.state = CallState.Finished;
		this.runCallback(CallEvent.onDestroy);
	}
}