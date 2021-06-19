import { BitrixVue } from "ui.vue";
import { Vuex } from "ui.vue.vuex";

const ConferenceInfo = {
	props: {
		compactMode: {
			type: Boolean,
			required: false,
			default: false
		}
	},
	data()
	{
		return {
			conferenceDuration: '',
			durationInterval: null
		}
	},
	created()
	{
		if (this.conferenceStarted)
		{
			this.updateConferenceDuration();
			this.durationInterval = setInterval(() => {
				this.updateConferenceDuration();
			}, 1000);
		}
	},
	beforeDestroy()
	{
		clearInterval(this.durationInterval);
	},
	computed:
	{
		conferenceStarted()
		{
			return this.conference.common.conferenceStarted;
		},
		conferenceStartDate()
		{
			return this.conference.common.conferenceStartDate;
		},
		conferenceTitle()
		{
			return this.conference.common.conferenceTitle;
		},
		userId()
		{
			return this.application.common.userId;
		},
		isBroadcast()
		{
			return this.conference.common.isBroadcast;
		},
		presentersList()
		{
			return this.conference.common.presenters;
		},
		presentersInfo()
		{
			return this.$store.getters['users/getList'](this.presentersList);
		},
		formattedPresentersList()
		{
			const presentersCount = this.presentersList.length;
			const prefix = presentersCount > 1 ? this.localize['BX_IM_COMPONENT_CALL_SPEAKERS_MULTIPLE'] : this.localize['BX_IM_COMPONENT_CALL_SPEAKER'];
			const presenters = this.presentersInfo.map(user => user.name).join(', ');

			return `${prefix}: ${presenters}`;
		},
		isCurrentUserPresenter()
		{
			return this.presentersList.includes(this.userId);
		},
		conferenceStatusText()
		{
			if (this.conferenceStarted === true)
			{
				return `${this.localize['BX_IM_COMPONENT_CALL_STATUS_STARTED']}, ${this.conferenceDuration}`;
			}
			else if (this.conferenceStarted === false)
			{
				return this.localize['BX_IM_COMPONENT_CALL_STATUS_NOT_STARTED'];
			}
			else if (this.conferenceStarted === null)
			{
				return this.localize['BX_IM_COMPONENT_CALL_STATUS_LOADING'];
			}
		},
		conferenceStatusClasses()
		{
			return [
				'bx-im-component-call-info-status',
				this.conferenceStarted? 'bx-im-component-call-info-status-active' : 'bx-im-component-call-info-status-not-active'
			];
		},
		containerClasses()
		{
			return [this.compactMode? 'bx-im-component-call-info-container-compact' : 'bx-im-component-call-info-container'];
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
		},
		...Vuex.mapState({
			conference: state => state.conference
		})
	},
	watch:
	{
		conferenceStarted(newValue)
		{
			if (newValue === true)
			{
				this.durationInterval = setInterval(() => {
					this.updateConferenceDuration();
				}, 1000);
			}

			this.updateConferenceDuration();
		},
	},
	methods:
	{
		updateConferenceDuration()
		{
			if (!this.conferenceStartDate)
			{
				return false;
			}

			const startDate = this.conferenceStartDate;
			const currentDate = new Date();

			let durationInSeconds = Math.floor((currentDate - startDate) / 1000);
			let minutes = 0;
			if (durationInSeconds > 60)
			{
				minutes = Math.floor(durationInSeconds / 60);
				if (minutes < 10)
				{
					minutes = '0' + minutes;
				}
			}
			let seconds = durationInSeconds - (minutes * 60);
			if (seconds < 10)
			{
				seconds = '0' + seconds;
			}
			this.conferenceDuration = `${minutes}:${seconds}`;

			return true;
		}
	},
	// language=Vue
	template: `
		<div :class="containerClasses">
			<template v-if="compactMode">
				<div class="bx-im-component-call-info-title-container">
					<div class="bx-im-component-call-info-logo"></div>
					<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
				</div>
				<div v-if="isBroadcast" class="bx-im-component-call-info-speakers">{{ formattedPresentersList }}</div>
			</template>
			<template v-else>
				<div class="bx-im-component-call-info-logo"></div>
				<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
			  	<div v-if="isBroadcast" class="bx-im-component-call-info-speakers">{{ formattedPresentersList }}</div>	
			</template>
			<div :class="conferenceStatusClasses">{{ conferenceStatusText }}</div>
		</div>
	`
};

export {ConferenceInfo};