import { BitrixVue } from "ui.vue";
import { Vuex } from "ui.vue.vuex";

const WaitingForStart = {
	computed:
	{
		userCounter()
		{
			return this.dialog.userCounter;
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
		},
		...Vuex.mapState({
			conference: state => state.conference,
			dialog: state => state.dialogues.collection[state.application.dialog.dialogId]
		})
	},
	// language=Vue
	template: `
		<div class="bx-im-component-call-wait-container">
			<div class="bx-im-component-call-wait-logo"></div>
			<div class="bx-im-component-call-wait-title">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_TITLE'] }}</div>
			<div class="bx-im-component-call-wait-user-counter">
				{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_USER_COUNT'] }} {{ userCounter }}
			</div>
			<slot></slot>
		</div>
	`
};

export {WaitingForStart};