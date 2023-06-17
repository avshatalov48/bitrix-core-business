import {Type} from 'main.core'
import {PlainCall} from './plain_call'
import {VoximplantCall} from './voximplant_call'
import {CallStub} from './stub'
import Util from '../util'
import {AbstractCall} from './abstract_call';

export const CallState = {
	Idle: 'Idle',
	Proceeding: 'Proceeding',
	Connected: 'Connected',
	Finished: 'Finished'
};

export const UserState = {
	Idle: 'Idle',
	Busy: 'Busy',
	Calling: 'Calling',
	Unavailable: 'Unavailable',
	Declined: 'Declined',
	Ready: 'Ready',
	Connecting: 'Connecting',
	Connected: 'Connected',
	Failed: 'Failed'
};

export const EndpointDirection = {
	SendOnly: 'send',
	RecvOnly: 'recv',
	SendRecv: 'sendrecv',
};

export const CallType = {
	Instant: 1,
	Permanent: 2
};

export const Provider = {
	Plain: 'Plain',
	Voximplant: 'Voximplant',
};

export const StreamTag = {
	Main: 'main',
	Screen: 'screen'
};

export const Direction = {
	Incoming: 'Incoming',
	Outgoing: 'Outgoing'
};

export const Quality = {
	VeryHigh: "very_high",
	High: "high",
	Medium: "medium",
	Low: "low",
	VeryLow: "very_low"
};

export const UserMnemonic = {
	all: 'all',
	none: 'none'
};

type CreateCallOptions = {
	type: number,
	provider: string,
	entityType: string,
	entityId: string,
	joinExisting: boolean,
	userIds?: number[],
	videoEnabled?: boolean,
	enableMicAutoParameters?: boolean,
	debug?: boolean
}

export const CallEvent = {
	onUserInvited: 'onUserInvited',
	onUserStateChanged: 'onUserStateChanged',
	onUserMicrophoneState: 'onUserMicrophoneState',
	onUserCameraState: 'onUserCameraState',
	onUserVideoPaused: 'onUserVideoPaused',
	onUserScreenState: 'onUserScreenState',
	onUserRecordState: 'onUserRecordState',
	onUserVoiceStarted: 'onUserVoiceStarted',
	onUserVoiceStopped: 'onUserVoiceStopped',
	onUserFloorRequest: 'onUserFloorRequest', // request for a permission to speak
	onUserEmotion: 'onUserEmotion',
	onCustomMessage: 'onCustomMessage',
	onLocalMediaReceived: 'onLocalMediaReceived',
	onLocalMediaStopped: 'onLocalMediaStopped',
	onMicrophoneLevel: 'onMicrophoneLevel',
	onDeviceListUpdated: 'onDeviceListUpdated',
	onRTCStatsReceived: 'onRTCStatsReceived',
	onCallFailure: 'onCallFailure',
	onRemoteMediaReceived: 'onRemoteMediaReceived',
	onRemoteMediaStopped: 'onRemoteMediaStopped',
	onNetworkProblem: 'onNetworkProblem',
	onReconnecting: 'onReconnecting',
	onReconnected: 'onReconnected',
	onJoin: 'onJoin',
	onLeave: 'onLeave',
	onJoinRoomOffer: 'onJoinRoomOffer',
	onJoinRoom: 'onJoinRoom',
	onLeaveRoom: 'onLeaveRoom',
	onListRooms: 'onListRooms',
	onUpdateRoom: 'onUpdateRoom',
	onTransferRoomSpeakerRequest: 'onTransferRoomSpeakerRequest',
	onTransferRoomSpeaker: 'onTransferRoomSpeaker',
	onDestroy: 'onDestroy',
};

const ajaxActions = {
	createCall: 'im.call.create',
	createChildCall: 'im.call.createChildCall',
	getPublicChannels: 'pull.channel.public.list',
	getCall: 'im.call.get'
};

class Engine
{
	handlers = {
		'Call::incoming': this.#onPullIncomingCall.bind(this),
	}

	constructor()
	{
		this.debugFlag = false;
		this.calls = {};
		this.userId = Number(BX.message('USER_ID'));
		this.siteId = '';

		this.unknownCalls = {};

		this.restClient = null;
		this.pullClient = null;

		this.init();
	};

	init()
	{
		BX.addCustomEvent("onPullEvent-im", this.#onPullEvent.bind(this));
		BX.addCustomEvent("onPullClientEvent-im", this.#onPullClientEvent.bind(this));
	};

	getSiteId()
	{
		return this.siteId || BX.message('SITE_ID') || '';
	};

	setSiteId(siteId)
	{
		this.siteId = siteId;
	};

	getCurrentUserId()
	{
		return this.userId;
	};

	setCurrentUserId(userId)
	{
		this.userId = Number(userId);
	};

	setRestClient(restClient)
	{
		this.restClient = restClient;
	};

	setPullClient(pullClient)
	{
		this.pullClient = pullClient;
	};

	getRestClient()
	{
		return this.restClient || BX.rest;
	};

	getPullClient()
	{
		return this.pullClient || BX.PULL;
	};

	getLogService()
	{
		return BX.message("call_log_service");
	};

	createCall(config: CreateCallOptions): Promise<AbstractCall>
	{
		return new Promise((resolve, reject) =>
		{
			const callType = config.type || CallType.Instant;
			const callProvider = config.provider || this.getDefaultProvider();

			if (config.joinExisting)
			{
				for (let callId in this.calls)
				{
					if (this.calls.hasOwnProperty(callId))
					{
						const call: AbstractCall = this.calls[callId];
						if (call.provider == config.provider && call.associatedEntity.type == config.entityType && call.associatedEntity.id == config.entityId)
						{
							this.log(callId, "Found existing call, attaching to it");
							return resolve({
								call: call,
								isNew: false
							});
						}
					}
				}
			}

			let callParameters = {
				type: callType,
				provider: callProvider,
				entityType: config.entityType,
				entityId: config.entityId,
				joinExisting: !!config.joinExisting,
				userIds: Type.isArray(config.userIds) ? config.userIds : []
			};

			this.getRestClient().callMethod(ajaxActions.createCall, callParameters).then((response) =>
			{
				if (response.error())
				{
					const error = response.error().getError();
					return reject({
						code: error.error,
						message: error.error_description
					});
				}

				const createCallResponse = response.data();
				if (createCallResponse.userData)
				{
					Util.setUserData(createCallResponse.userData)
				}
				if (createCallResponse.publicChannels)
				{
					this.getPullClient().setPublicIds(Object.values(createCallResponse.publicChannels))
				}
				const callFields = createCallResponse.call;
				if (this.calls[callFields['ID']])
				{
					if (this.calls[callFields['ID']] instanceof CallStub)
					{
						this.calls[callFields['ID']].destroy();
					}
					else
					{
						console.error("Call " + callFields['ID'] + " already exists");
						return resolve({
							call: this.calls[callFields['ID']],
							isNew: false
						});
					}
				}

				const callFactory = this.#getCallFactory(callFields['PROVIDER']);
				const call = callFactory.createCall({
					id: parseInt(callFields['ID']),
					instanceId: Util.getUuidv4(),
					direction: Direction.Outgoing,
					users: createCallResponse.users,
					videoEnabled: (config.videoEnabled === true),
					enableMicAutoParameters: (config.enableMicAutoParameters !== false),
					associatedEntity: callFields.ASSOCIATED_ENTITY,
					type: callFields.TYPE,
					startDate: callFields.START_DATE,
					events: {
						onDestroy: this.#onCallDestroy.bind(this)
					},
					debug: config.debug === true,
					logToken: createCallResponse.logToken
				});

				this.calls[callFields['ID']] = call;

				if (createCallResponse.isNew)
				{
					this.log(call.id, "Creating new call");
				}
				else
				{
					this.log(call.id, "Server returned existing call, attaching to it");
				}

				BX.onCustomEvent(window, "CallEvents::callCreated", [{
					call: call
				}]);

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			}).catch(function (error)
			{
				if (Type.isFunction(error.error))
				{
					error = error.error().getError();
				}
				reject({
					code: error.error,
					message: error.error_description
				})
			})
		});

	};

	createChildCall(parentId, newProvider, newUsers)
	{
		if (!this.calls[parentId])
		{
			return Promise.reject('Parent call is not found');
		}

		return new Promise((resolve) =>
		{
			const parentCall = this.calls[parentId];
			const callParameters = {
				parentId: parentId,
				newProvider: newProvider,
				newUsers: newUsers
			};

			this.getRestClient().callMethod(ajaxActions.createChildCall, callParameters, (response) =>
			{
				const createCallResponse = response.data();
				const callFields = createCallResponse.call;
				const callFactory = this.#getCallFactory(callFields['PROVIDER']);

				const call = callFactory.createCall({
					id: parseInt(callFields['ID']),
					instanceId: Util.getUuidv4(),
					parentId: callFields['PARENT_ID'],
					direction: Direction.Outgoing,
					users: createCallResponse.users,
					videoEnabled: parentCall.isVideoEnabled(),
					enableMicAutoParameters: parentCall.enableMicAutoParameters !== false,
					associatedEntity: callFields.ASSOCIATED_ENTITY,
					type: callFields.TYPE,
					startDate: callFields.START_DATE,
					events: {
						onDestroy: this.#onCallDestroy.bind(this)
					},
					logToken: createCallResponse.logToken
				});

				this.calls[callFields['ID']] = call;
				BX.onCustomEvent(window, "CallEvents::callCreated", [{
					call: call
				}]);

				resolve({
					call: call,
					isNew: createCallResponse.isNew
				});
			});
		});
	};

	#instantiateCall(callFields, users, logToken): AbstractCall
	{
		if (this.calls[callFields['ID']])
		{
			console.error("Call " + callFields['ID'] + " already exists");
			return this.calls[callFields['ID']];
		}

		const callFactory = this.#getCallFactory(callFields['PROVIDER']);
		const call = callFactory.createCall({
			id: parseInt(callFields['ID']),
			instanceId: Util.getUuidv4(),
			initiatorId: parseInt(callFields['INITIATOR_ID']),
			parentId: callFields['PARENT_ID'],
			direction: callFields['INITIATOR_ID'] == this.userId ? Direction.Outgoing : Direction.Incoming,
			users: users,
			associatedEntity: callFields.ASSOCIATED_ENTITY,
			type: callFields.TYPE,
			startDate: callFields['START_DATE'],
			logToken: logToken,

			events: {
				onDestroy: this.#onCallDestroy.bind(this)
			}
		});

		this.calls[callFields['ID']] = call;

		BX.onCustomEvent(window, "CallEvents::callCreated", [{
			call: call
		}]);

		return call;
	};

	getCallWithId(id): Promise<{ call: AbstractCall, isNew: boolean }>
	{
		if (this.calls[id])
		{
			return Promise.resolve({
				call: this.calls[id],
				isNew: false
			});
		}

		return new Promise((resolve, reject) =>
		{
			this.getRestClient().callMethod(ajaxActions.getCall, {callId: id}).then((answer) =>
			{
				const data = answer.data();
				resolve({
					call: this.#instantiateCall(data.call, data.users, data.logToken),
					isNew: false
				})
			}).catch((error) =>
			{
				console.error(error);
				if (Type.isFunction(error.error))
				{
					error = error.error().getError();
				}
				reject({
					code: error.error,
					message: error.error_description
				})
			})
		})
	};

	#onPullEvent(command: string, params, extra)
	{
		if (command.startsWith('Call::'))
		{
			if (params.publicIds)
			{
				this.getPullClient().setPublicIds(Object.values(params.publicIds));
			}
			if (params.userData)
			{
				Util.setUserData(params.userData);
			}
		}

		if (this.handlers[command])
		{
			this.handlers[command].call(this, params, extra);
		}
		else if (command.startsWith('Call::') && (params['call'] || params['callId']))
		{
			const callId = params['call'] ? params['call']['ID'] : params['callId'];
			if (this.calls[callId])
			{
				this.calls[callId].__onPullEvent(command, params, extra);
			}
			else if (command === 'Call::ping')
			{
				this.#onUnknownCallPing(params, extra).then((result) =>
				{
					if (result && this.calls[callId])
					{
						this.calls[callId].__onPullEvent(command, params, extra);
					}
				});
			}
		}
	};

	#onPullClientEvent(command: string, params, extra)
	{
		if (command.startsWith('Call::') && params['callId'])
		{
			const callId = params['callId'];
			if (this.calls[callId])
			{
				this.calls[callId].__onPullEvent(command, params, extra);
			}
			else if (command === 'Call::ping')
			{
				this.#onUnknownCallPing(params, extra).then((result) =>
				{
					if (result && this.calls[callId])
					{
						this.calls[callId].__onPullEvent(command, params, extra);
					}
				});
			}
		}
	};

	#onPullIncomingCall(params, extra)
	{
		if (extra.server_time_ago > 30)
		{
			console.error("Call was started too long time ago");
			return;
		}

		const callFields = params.call;
		const callId = parseInt(callFields.ID);
		let call;

		if (params.publicIds)
		{
			this.getPullClient().setPublicIds(Object.values(params.publicIds));
		}

		if (params.userData)
		{
			Util.setUserData(params.userData);
		}

		if (this.calls[callId])
		{
			call = this.calls[callId];
		}
		else
		{
			const callFactory = this.#getCallFactory(callFields.PROVIDER);
			call = callFactory.createCall({
				id: callId,
				instanceId: Util.getUuidv4(),
				parentId: callFields.PARENT_ID || null,
				callFromMobile: params.isLegacyMobile === true,
				direction: Direction.Incoming,
				users: params.users,
				initiatorId: params.senderId,
				associatedEntity: callFields.ASSOCIATED_ENTITY,
				type: callFields.TYPE,
				startDate: callFields.START_DATE,
				logToken: params.logToken,
				events: {
					onDestroy: this.#onCallDestroy.bind(this)
				}
			});

			this.calls[callId] = call;

			BX.onCustomEvent(window, "CallEvents::callCreated", [{
				call: call
			}]);
		}

		call.addInvitedUsers(params.invitedUsers);
		if (call)
		{
			BX.onCustomEvent(window, "CallEvents::incomingCall", [{
				call: call,
				video: params.video === true,
				isLegacyMobile: params.isLegacyMobile === true
			}]);
		}
		this.log(call.id, "Incoming call " + call.id);
	};

	#onUnknownCallPing(params, extra)
	{
		const callId = Number(params.callId);
		if (extra.server_time_ago > 10)
		{
			this.log(callId, "Error: Ping was sent too long time ago");
			return Promise.resolve(false);
		}
		if (!this.#isCallAppInitialized())
		{
			return Promise.resolve(false);
		}

		if (this.unknownCalls[callId])
		{
			return Promise.resolve(false);
		}

		this.unknownCalls[callId] = true;

		if (params.userData)
		{
			Util.setUserData(params.userData);
		}

		return new Promise((resolve) =>
		{
			this.getCallWithId(callId).then(() =>
			{
				this.unknownCalls[callId] = false;
				resolve(true);
			}).catch((error) =>
			{
				this.unknownCalls[callId] = false;
				this.log(callId, "Error: Could not instantiate call", error);
				resolve(false);
			});
		});
	};

	#onCallDestroy(e)
	{
		const callId = e.call.id;
		this.calls[callId] = new CallStub({
			callId: callId,
			onDelete: () =>
			{
				if (this.calls[callId])
				{
					delete this.calls[callId];
				}
			}
		});

		BX.onCustomEvent(window, "CallEvents::callDestroyed", [{
			callId: e.call.id
		}]);
	};

	#isCallAppInitialized()
	{
		if ('BXIM' in window && 'init' in window.BXIM)
		{
			return BXIM.init;
		}
		else if (BX.Messenger && BX.Messenger.Application && BX.Messenger.Application.conference)
		{
			return BX.Messenger.Application.conference.inited;
		}

		//TODO: support new chat
		return true;
	};

	getDefaultProvider()
	{
		return Provider.Plain;
	};

	getConferencePageTag(chatDialogId)
	{
		return "conference-open-" + chatDialogId;
	};

	#getCallFactory(providerType: string)
	{
		if (providerType == Provider.Plain)
		{
			return PlainCallFactory;
		}
		else if (providerType == Provider.Voximplant)
		{
			return VoximplantCallFactory;
		}

		throw new Error("Unknown call provider type " + providerType);
	};

	debug(debugFlag: boolean = true): boolean
	{
		this.debugFlag = !!debugFlag;

		return this.debugFlag;
	};

	log()
	{
		const text = Util.getLogMessage.call(Util, arguments);

		if (BX.desktop && BX.desktop.ready())
		{
			BX.desktop.log(BX.message('USER_ID') + '.video.log', text);
		}
		if (this.debugFlag)
		{
			if (console)
			{
				const a = ['Call log [' + Util.getTimeForLog() + ']: '];
				console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
			}
		}
	};

	getAllowedVideoQuality(participantsCount)
	{
		if (participantsCount < 5)
		{
			return Quality.VeryHigh
		}
		else if (participantsCount < 10)
		{
			return Quality.High
		}
		else if (participantsCount < 16)
		{
			return Quality.Medium
		}
		else if (participantsCount < 32)
		{
			return Quality.Low
		}
		else
		{
			return Quality.VeryLow
		}
	};
}

class PlainCallFactory
{
	static createCall(config): PlainCall
	{
		return new PlainCall(config);
	}
}

class VoximplantCallFactory
{
	static createCall(config): VoximplantCall
	{
		return new VoximplantCall(config);
	}
}

export const CallEngine = new Engine();