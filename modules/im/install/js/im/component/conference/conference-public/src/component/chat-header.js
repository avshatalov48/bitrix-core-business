import { BitrixVue } from "ui.vue";
import { Vuex } from "ui.vue.vuex";
import { Utils } from "im.lib.utils";
import { Desktop } from "im.lib.desktop";

const ChatHeader = {
	created()
	{
		this.desktop = new Desktop();
	},
	computed:
	{
		showTotalCounter()
		{
			return Utils.platform.isBitrixDesktop()
				&& (this.desktop.getApiVersion() >= 60 || !Utils.platform.isWindows())
				&& !this.getApplication().isExternalUser()
				&& this.messageCount > 0;
		},
		messageCount()
		{
			return this.conference.common.messageCount;
		},
		formattedCounter()
		{
			return this.messageCount > 99 ? '99+' : this.messageCount;
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
		},
		...Vuex.mapState({
			conference: state => state.conference
		})
	},
	methods:
	{
		onCloseChat()
		{
			this.getApplication().toggleChat();
		},
		onTotalCounterClick()
		{
			if (opener && opener.BXDesktopWindow)
			{
				opener.BXDesktopWindow.ExecuteCommand('show.active');
			}
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		}
	},
	template: `
		<div class="bx-im-component-call-right-header">
			<div class="bx-im-component-call-right-header-left">
				<div @click="onCloseChat" class="bx-im-component-call-right-header-close" :title="localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']"></div>
				<div class="bx-im-component-call-right-header-title">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>
			</div>
			<template v-if="showTotalCounter">
				<div @click="onTotalCounterClick" class="bx-im-component-call-right-header-right bx-im-component-call-right-header-all-chats">
					<div class="bx-im-component-call-right-header-all-chats-title">{{ localize['BX_IM_COMPONENT_CALL_ALL_CHATS'] }}</div>
					<div class="bx-im-component-call-right-header-all-chats-counter">{{ messageCount }}</div>
				</div>
			</template>
		</div>
	`
};

export {ChatHeader};