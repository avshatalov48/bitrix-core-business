/**
 * Bitrix Messenger
 * Call Application model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {VuexBuilderModel} from 'ui.vue.vuex';
import {CallStateType} from 'im.const';

export class CallApplicationModel extends VuexBuilderModel
{
	getName()
	{
		return 'callApplication';
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
				userInCallCount: 0,
				state: CallStateType.preparation,
				showSmiles: false,
				error: '',
				conferenceTitle: '',
				alias: '',
				conferenceStarted: null,
				conferenceStartDate: null,
				joinWithVideo: null,
				userReadyToJoin: false
			},
			user:
			{
				id: -1,
				hash: ''
			},
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
				if (typeof payload.showChat === 'boolean')
				{
					state.common.showChat = payload.showChat;
				}
				if (typeof payload.userCount === 'number' || typeof payload.userCount === 'string')
				{
					state.common.userCount = parseInt(payload.userCount);
				}
				if (typeof payload.userInCallCount === 'number' || typeof payload.userInCallCount === 'string')
				{
					state.common.userInCallCount = parseInt(payload.userInCallCount);
				}
				if (typeof payload.componentError === 'string')
				{
					state.common.componentError = payload.componentError;
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
			startCall: (state, payload) =>
			{
				state.common.state = CallStateType.call;
			},
			endCall: (state, payload) =>
			{
				state.common.state = CallStateType.preparation;
			},
			returnToPreparation: (state, payload) =>
			{
				state.common.state = CallStateType.preparation;
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
				if (typeof payload.conferenceTitle === 'string')
				{
					state.common.conferenceTitle = payload.conferenceTitle;
				}
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
				userInCallCount: null,
				error: null,
				conferenceTitle: null,
				alias: null,
				conferenceStarted: null,
				conferenceStartDate: null,
				joinWithVideo: null,
				userReadyToJoin: null
			},
		}
	}
}