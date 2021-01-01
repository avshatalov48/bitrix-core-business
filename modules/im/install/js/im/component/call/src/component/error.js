import { Vue } from "ui.vue";
import { CallApplicationErrorCode, CallErrorCode } from "im.const";
import { Cookie } from 'im.lib.cookie';
import { Utils } from "im.lib.utils";
import { Vuex } from "ui.vue.vuex";

const ErrorComponent = {
	props: ['errorCode'],
	data()
	{
		return {
			downloadAppArticleCode: 11387752
		}
	},
	computed:
	{
		bitrix24only()
		{
			return this.errorCode === CallApplicationErrorCode.bitrix24only;
		},
		detectIntranetUser()
		{
			return this.errorCode === CallApplicationErrorCode.detectIntranetUser;
		},
		userLimitReached()
		{
			return this.errorCode === CallApplicationErrorCode.userLimitReached;
		},
		kickedFromCall()
		{
			return this.errorCode === CallApplicationErrorCode.kickedFromCall;
		},
		wrongAlias()
		{
			return this.errorCode === CallApplicationErrorCode.wrongAlias;
		},
		conferenceFinished()
		{
			return this.errorCode === CallApplicationErrorCode.finished;
		},
		unsupportedBrowser()
		{
			return this.errorCode === CallApplicationErrorCode.unsupportedBrowser;
		},
		missingMicrophone()
		{
			return this.errorCode === CallApplicationErrorCode.missingMicrophone;
		},
		unsafeConnection()
		{
			return this.errorCode === CallApplicationErrorCode.unsafeConnection;
		},
		noSignalFromCamera()
		{
			return this.errorCode === CallErrorCode.noSignalFromCamera;
		},
		userLeftCall()
		{
			return this.errorCode === CallApplicationErrorCode.userLeftCall;
		},
		localize()
		{
			return Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
		},
		...Vuex.mapState({
			callApplication: state => state.callApplication
		})
	},
	methods:
	{
		reloadPage()
		{
			location.reload();
		},
		redirectToAuthorize()
		{
			location.href = location.origin + '/auth/?backurl=' + location.pathname;
		},
		continueAsGuest()
		{
			Cookie.set(null, `VIDEOCONF_GUEST_${this.callApplication.common.alias}`, '', {path: '/'});
			location.reload(true);
		},
		getBxLink()
		{
			return `bx://videoconf/code/${this.$root.$bitrixApplication.getAlias()}`;
		},
		openHelpArticle()
		{
			if (BX.Helper)
			{
				BX.Helper.show("redirect=detail&code=" + this.downloadAppArticleCode);
			}
		},
		isMobile()
		{
			return Utils.device.isMobile();
		}
	},
	template: `
		<div class="bx-im-component-call-error-wrap">
			<template v-if="bitrix24only">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-b24only"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_B24_ONLY'] }}</div>
						<template v-if="!isMobile()">
							<a @click.prevent="openHelpArticle" class="bx-im-component-call-error-more-link">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>
						</template>
					</div>
				</div>
			</template>
			<template v-if="detectIntranetUser">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-intranet"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_PLEASE_LOG_IN'] }}</div>
						<div class="bx-im-component-call-error-buttons">
							<button @click="redirectToAuthorize" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-error-button-authorize">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AUTHORIZE'] }}</button>
							<button @click="continueAsGuest" class="ui-btn ui-btn-sm bx-im-component-call-error-button-as-guest">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AS_GUEST'] }}</button>
						</div>
					</div>
				</div>
			</template>
			<template v-if="userLimitReached">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-full"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_USER_LIMIT'] }}</div>
					</div>
				</div>
			</template>
			<template v-if="kickedFromCall">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-kicked"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'] }}</div>
					</div>
				</div>
			</template>
			<template v-if="wrongAlias || conferenceFinished">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-finished"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_FINISHED'] }}</div>
						<template v-if="!isMobile()">
							<a @click.prevent="openHelpArticle" class="bx-im-component-call-error-more-link">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>
						</template>
					</div>
				</div>
			</template>
			<template v-if="unsupportedBrowser">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-browser"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_UNSUPPORTED_BROWSER'] }}</div>
						<template v-if="!isMobile()">
							<a @click.prevent="openHelpArticle" class="bx-im-component-call-error-more-link">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_DETAILS'] }}</a>
						</template>
					</div>
				</div>
			</template>
			<template v-if="missingMicrophone">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_MIC'] }}</div>
					</div>
				</div>
			</template>
			<template v-if="unsafeConnection">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-icon bx-im-component-call-error-icon-https"></div>
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_HTTPS'] }}</div>
					</div>
				</div>
			</template>
			<template v-if="noSignalFromCamera">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_SIGNAL_FROM_CAMERA'] }}</div>
						<div class="bx-im-component-call-error-buttons">
							<button @click="reloadPage" class="ui-btn ui-btn-sm ui-btn-no-caps">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_RELOAD'] }}</button>
						</div>
					</div>
				</div>
			</template>
			<template v-if="userLeftCall">
				<div class="bx-im-component-call-error-container">
					<div class="bx-im-component-call-error-content">
						<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_USER_LEFT_THE_CALL'] }}</div>
					</div>
				</div>
			</template>
		</div>
	`
};

export {ErrorComponent};