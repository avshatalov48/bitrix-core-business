/**
 * Bitrix Im
 * Application External Call
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from "ui.vue";

import "im.component.dialog";
import "im.component.call";
import "pull.component.status";
import "./view.css";
import {Vuex} from "ui.vue.vuex";
import {CallApplicationErrorCode} from "im.const";
import {Cookie} from 'im.lib.cookie';

Vue.component('bx-im-application-call',
{
	data(){
		return {
			downloadAppArticleCode: 11387752
		}
	},
	props:
	{
		chatId: { default: 0 },
		dialogId: { default: '0' },
		startupErrorCode: { default: '' }
	},
	methods:
	{
		redirectToAuthorize()
		{
			location.href = location.origin + '/auth/?backurl=' + location.pathname;
		},
		continueAsGuest()
		{
			Cookie.set(null, 'VIDEOCONF_GUEST', '', {path: '/'});
			location.reload(true);
		},
		getBxLink()
		{
			return `bx://videoconf/code/${this.$root.$bitrixApplication.getAlias()}`;
		},
		getErrorFromCode()
		{
			if (this.startupErrorCode)
			{
				if (this.startupErrorCode === CallApplicationErrorCode.bitrix24only)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_B24_ONLY'];
				}
				else if (this.startupErrorCode === CallApplicationErrorCode.detectIntranetUser)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_PLEASE_LOG_IN'];
				}
				else if (this.startupErrorCode === CallApplicationErrorCode.userLimitReached)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_USER_LIMIT'];
				}
				else if (this.startupErrorCode === CallApplicationErrorCode.kickedFromCall)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'];
				}
			}
			else if (this.callApplication.common.componentError)
			{
				if (this.callApplication.common.componentError === CallApplicationErrorCode.kickedFromCall)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'];
				}
				else if (this.callApplication.common.componentError === CallApplicationErrorCode.unsupportedBrowser)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_UNSUPPORTED_BROWSER'];
				}
				else if (this.callApplication.common.componentError === CallApplicationErrorCode.missingMicrophone)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_NO_MIC'];
				}
				else if (this.callApplication.common.componentError === CallApplicationErrorCode.unsafeConnection)
				{
					return this.localize['BX_IM_COMPONENT_CALL_ERROR_NO_HTTPS'];
				}
			}
		},
		openHelpArticle()
		{
			if (BX.Helper)
			{
				BX.Helper.show("redirect=detail&code=" + this.downloadAppArticleCode);
			}
		}
	},
	computed:
	{
		authorizeButtonClasses()
		{
			return ['ui-btn', 'ui-btn-sm', 'ui-btn-success-dark', 'ui-btn-no-caps', 'bx-im-application-call-button-authorize'];
		},
		continueAsGuestButtonClasses()
		{
			return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-application-call-button-as-guest'];
		},
		isIntranetUserError()
		{
			return this.startupErrorCode === CallApplicationErrorCode.detectIntranetUser;
		},
		isUnsupportedBrowserError()
		{
			return this.callApplication.common.componentError === CallApplicationErrorCode.unsupportedBrowser;
		},
		localize()
		{
			return Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
		},
		...Vuex.mapState({
			callApplication: state => state.callApplication,
			application: state => state.application
		})
	},
	template: `
		<div class="bx-im-application-call">
			<template v-if="startupErrorCode || callApplication.common.componentError">
				<template v-if="isIntranetUserError">
					<div class="bx-im-application-call-error-message">
						<div>{{ getErrorFromCode() }}</div>
						<div class="bx-im-application-call-error-message-buttons">
							<button @click="redirectToAuthorize" :class="authorizeButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AUTHORIZE'] }}</button>
							<button @click="continueAsGuest" :class="continueAsGuestButtonClasses">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AS_GUEST'] }}</button>
						</div>
					</div>
				</template>
				<template v-else-if="isUnsupportedBrowserError">
					<div class="bx-im-application-call-error-message">
						<div>{{ getErrorFromCode() }}</div>
						<div class="bx-im-application-call-error-message-links">
							<div class="bx-im-application-call-error-message-links-link">
								{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_OPEN_APP'] }} 
								<a :href="getBxLink()">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_OPEN_APP_LINK'] }}</a>
							</div>
							<div class="bx-im-application-call-error-message-links-link">
								{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_DOWNLOAD_APP'] }} -
								<a href="" @click.prevent="openHelpArticle" target="_blank">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_DOWNLOAD_APP_LINK'] }}</a>
							</div>
						</div>
					</div>
				</template>
				<template v-else>
					<div class="bx-im-application-call-error-message">{{ getErrorFromCode() }}</div>
				</template>
			</template>
			<template v-else>
				<bx-pull-component-status/>
				<bx-im-component-call :chatId="chatId" dialogId="dialogId" />
			</template>
		</div>
	`
});