import { BitrixVue } from "ui.vue";
import { Vuex } from "ui.vue.vuex";
import { EventEmitter } from "main.core.events";
import { EventType } from "im.const";

const PasswordCheck = {
	data()
	{
		return {
			password: '',
			checkingPassword: '',
			wrongPassword: ''
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.conference.setPasswordFocus, this.onSetPasswordFocus);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.conference.setPasswordFocus, this.onSetPasswordFocus);
	},
	computed:
	{
		conferenceTitle()
		{
			return this.conference.common.conferenceTitle;
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
		onSetPasswordFocus()
		{
			this.$refs['passwordInput'].focus();
		},
		checkPassword()
		{
			if (!this.password || this.checkingPassword)
			{
				this.wrongPassword = true;

				return false;
			}
			this.checkingPassword = true;
			this.wrongPassword = false;
			this.getApplication().checkPassword(this.password)
				.catch(() => {
					this.wrongPassword = true;
				})
				.finally(() => {
					this.checkingPassword = false;
				});
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		}
	},
	// language=Vue
	template: `
		<div>
			<div class="bx-im-component-call-info-container">
				<div class="bx-im-component-call-info-logo"></div>
				<div class="bx-im-component-call-info-title">{{ conferenceTitle }}</div>
			</div>
			<div class="bx-im-component-call-password-container">
				<template v-if="wrongPassword">
					<div class="bx-im-component-call-password-error">
						{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_WRONG'] }}
					</div>
				</template>
				<template v-else>
					<div class="bx-im-component-call-password-title">
						<div class="bx-im-component-call-password-title-logo"></div>
						<div class="bx-im-component-call-password-title-text">
							{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_TITLE'] }}
						</div>
					</div>
				</template>
				<input
					@keyup.enter="checkPassword"
					type="text"
					v-model="password"
					class="bx-im-component-call-password-input"
					:placeholder="localize['BX_IM_COMPONENT_CALL_PASSWORD_PLACEHOLDER']"
					ref="passwordInput"
				/>
				<button @click="checkPassword" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-password-button">
			  		{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_JOIN'] }}
				</button>
			</div>
		</div>
	`
};

export {PasswordCheck};