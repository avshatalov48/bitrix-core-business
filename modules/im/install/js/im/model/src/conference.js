/**
 * Bitrix Messenger
 * Call Application model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {VuexBuilderModel} from 'ui.vue.vuex';
import {ConferenceStateType, ConferenceRightPanelMode as RightPanelMode} from 'im.const';

export class ConferenceModel extends VuexBuilderModel
{
	getName()
	{
		return 'conference';
	}

	getState()
	{
		return {
			common:
			{
				inited: false,
				passChecked: true,
				showChat: false,
				userCount: 0,
				messageCount: 0,
				userInCallCount: 0,
				state: ConferenceStateType.preparation,
				callEnded: false,
				showSmiles: false,
				error: '',
				conferenceTitle: '',
				alias: '',
				permissionsRequested: false,
				conferenceStarted: null,
				conferenceStartDate: null,
				joinWithVideo: null,
				userReadyToJoin: false,
				isBroadcast: false,
				users: [],
				usersInCall: [],
				presenters: [],
				rightPanelMode: RightPanelMode.hidden,
				hasErrorInCall: false,
			},
			user:
			{
				id: -1,
				hash: ''
			},
		}
	}

	getActions()
	{
		return {
			showChat: (store, payload) =>
			{
				if (typeof payload.newState !== 'boolean')
				{
					return false;
				}

				store.commit('showChat', payload);
			},
			changeRightPanelMode: (store, payload) =>
			{
				if (!RightPanelMode[payload.mode])
				{
					return false;
				}

				store.commit('changeRightPanelMode', payload);
			},
			setPermissionsRequested: (store, payload) =>
			{
				if (typeof payload.status !== 'boolean')
				{
					return false;
				}
				store.commit('setPermissionsRequested', payload);
			},
			setPresenters: (store, payload) =>
			{
				if (!Array.isArray(payload.presenters))
				{
					payload.presenters = [payload.presenters];
				}

				store.commit('setPresenters', payload);
			},
			setUsers: (store, payload) =>
			{
				if (!Array.isArray(payload.users))
				{
					payload.users = [payload.users];
				}

				store.commit('setUsers', payload);
			},
			removeUsers: (store, payload) =>
			{
				if (!Array.isArray(payload.users))
				{
					payload.users = [payload.users];
				}

				store.commit('removeUsers', payload);
			},
			setUsersInCall: (store, payload) =>
			{
				if (!Array.isArray(payload.users))
				{
					payload.users = [payload.users];
				}

				store.commit('setUsersInCall', payload);
			},
			removeUsersInCall: (store, payload) =>
			{
				if (!Array.isArray(payload.users))
				{
					payload.users = [payload.users];
				}

				store.commit('removeUsersInCall', payload);
			},
			setConferenceTitle: (store, payload) =>
			{
				if (typeof payload.conferenceTitle !== 'string')
				{
					return false;
				}

				store.commit('setConferenceTitle', payload);
			},
			setBroadcastMode: (store, payload) =>
			{
				if (typeof payload.broadcastMode !== 'boolean')
				{
					return false;
				}

				store.commit('setBroadcastMode', payload);
			}
		}
	}

	getMutations()
	{
		return {
			common: (state, payload) =>
			{
				if (typeof payload.inited === 'boolean')
				{
					state.common.inited = payload.inited;
				}
				if (typeof payload.passChecked === 'boolean')
				{
					state.common.passChecked = payload.passChecked;
				}
				if (typeof payload.userCount === 'number' || typeof payload.userCount === 'string')
				{
					state.common.userCount = parseInt(payload.userCount);
				}
				if (typeof payload.messageCount === 'number' || typeof payload.messageCount === 'string')
				{
					state.common.messageCount = parseInt(payload.messageCount);
				}
				if (typeof payload.userInCallCount === 'number' || typeof payload.userInCallCount === 'string')
				{
					state.common.userInCallCount = parseInt(payload.userInCallCount);
				}
				if (typeof payload.componentError === 'string')
				{
					state.common.componentError = payload.componentError;
				}
				if (typeof payload.isBroadcast === 'boolean')
				{
					state.common.isBroadcast = payload.isBroadcast;
				}
				if (Array.isArray(payload.presenters))
				{
					state.common.presenters = payload.presenters;
				}
				if (typeof payload.hasErrorInCall === 'boolean')
				{
					state.common.hasErrorInCall = payload.hasErrorInCall;
				}
			},
			user: (state, payload) =>
			{
				if (typeof payload.id === 'number')
				{
					state.user.id = payload.id;
				}
				if (typeof payload.hash === 'string' && payload.hash !== state.user.hash)
				{
					state.user.hash = payload.hash;
				}
				if (this.isSaveNeeded({user: payload}))
				{
					this.saveState(state);
				}
			},
			showChat: (state, {newState}) =>
			{
				state.common.showChat = newState;
			},
			changeRightPanelMode: (state, {mode}) =>
			{
				state.common.rightPanelMode = mode;
			},
			setPermissionsRequested: (state, payload) =>
			{
				state.common.permissionsRequested = payload.status;
			},
			startCall: (state, payload) =>
			{
				state.common.state = ConferenceStateType.call;
				state.common.callEnded = false;
			},
			endCall: (state, payload) =>
			{
				state.common.state = ConferenceStateType.preparation;
				state.common.callEnded = true;
			},
			returnToPreparation: (state, payload) =>
			{
				state.common.state = ConferenceStateType.preparation;
			},
			toggleSmiles: (state, payload) =>
			{
				state.common.showSmiles = !state.common.showSmiles;
			},
			setError: (state, payload) =>
			{
				if (typeof payload.errorCode === 'string')
				{
					state.common.error = payload.errorCode;
				}
			},
			setConferenceTitle: (state, payload) =>
			{
				state.common.conferenceTitle = payload.conferenceTitle;
			},
			setBroadcastMode: (state, payload) =>
			{
				state.common.isBroadcast = payload.broadcastMode;
			},
			setAlias: (state, payload) =>
			{
				if (typeof payload.alias === 'string')
				{
					state.common.alias = payload.alias;
				}
			},
			setJoinType: (state, payload) =>
			{
				if (typeof payload.joinWithVideo === 'boolean')
				{
					state.common.joinWithVideo = payload.joinWithVideo;
				}
			},
			setConferenceStatus: (state, payload) =>
			{
				if (typeof payload.conferenceStarted === 'boolean')
				{
					state.common.conferenceStarted = payload.conferenceStarted;
				}
			},
			setConferenceHasErrorInCall: (state, payload) =>
			{
				if (typeof payload.hasErrorInCall === 'boolean')
				{
					state.common.hasErrorInCall = payload.hasErrorInCall;
				}
			},
			setConferenceStartDate: (state, payload) =>
			{
				if (payload.conferenceStartDate instanceof Date)
				{
					state.common.conferenceStartDate = payload.conferenceStartDate;
				}
			},
			setUserReadyToJoin: (state, payload) =>
			{
				state.common.userReadyToJoin = true;
			},
			setPresenters: (state, payload) =>
			{
				if (payload.replace)
				{
					state.common.presenters = payload.presenters;
				}
				else
				{
					payload.presenters.forEach(presenter => {
						presenter = parseInt(presenter);
						if (!state.common.presenters.includes(presenter))
						{
							state.common.presenters.push(presenter);
						}
					});
				}
			},
			setUsers: (state, payload) =>
			{
				payload.users.forEach(user => {
					user = parseInt(user);
					if (!state.common.users.includes(user))
					{
						state.common.users.push(user);
					}
				});
			},
			removeUsers: (state, payload) =>
			{
				state.common.users = state.common.users.filter(user => {
					return !payload.users.includes(parseInt(user));
				});
			},
			setUsersInCall: (state, payload) =>
			{
				payload.users.forEach(user => {
					user = parseInt(user);
					if (!state.common.usersInCall.includes(user))
					{
						state.common.usersInCall.push(user);
					}
				});
			},
			removeUsersInCall: (state, payload) =>
			{
				state.common.usersInCall = state.common.usersInCall.filter(user => {
					return !payload.users.includes(parseInt(user));
				});
			}
		}
	}

	getStateSaveException()
	{
		return {
			common: {
				inited: null,
				state: null,
				showSmiles: null,
				userCount: null,
				messageCount: null,
				userInCallCount: null,
				error: null,
				conferenceTitle: null,
				alias: null,
				conferenceStarted: null,
				conferenceStartDate: null,
				joinWithVideo: null,
				userReadyToJoin: null,
				rightPanelMode: null,
				presenters: null,
				users: null,
				hasErrorInCall: null,
			},
		}
	}
}