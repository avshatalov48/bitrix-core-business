import { BitrixVue } from "ui.vue";

const LoadingStatus = {
	computed:
	{
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
		}
	},
	// language=Vue
	template: `
		<div class="bx-im-component-call-loading">
			<div class="bx-im-component-call-loading-text">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>
		</div>
	`
};

export {LoadingStatus};