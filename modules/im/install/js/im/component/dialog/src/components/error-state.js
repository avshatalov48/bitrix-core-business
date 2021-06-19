import { Vuex } from "ui.vue.vuex";
import { Vue } from "ui.vue";

export const ErrorState = {
	computed:
	{
		...Vuex.mapState({
			application: state => state.application,
		}),
	},
	// language=Vue
	template: `
		<div class="bx-mobilechat-body">
			<div class="bx-mobilechat-warning-window">
				<div class="bx-mobilechat-warning-icon"></div>
				<template v-if="application.error.description">
					<div class="bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg" v-html="application.error.description"></div>
				</template>
				<template v-else>
					<div class="bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-warning-msg">{{$Bitrix.Loc.getMessage('IM_DIALOG_ERROR_TITLE')}}</div>
					<div class="bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg">{{$Bitrix.Loc.getMessage('IM_DIALOG_ERROR_DESC')}}</div>
				</template>
			</div>
		</div>
	`
};