import { BitrixVue } from "ui.vue";
import { ConferenceErrorCode } from "im.const";
import { Cookie } from 'im.lib.cookie';
import { Utils } from "im.lib.utils";
import { Vuex } from "ui.vue.vuex";

import 'im.component.call-feedback';

const Error = {
	data()
	{
		return {
			downloadAppArticleCode: 11387752,
			callFeedbackSent: false
		}
	},
	computed:
	{
		errorCode()
		{
			return this.conference.common.error;
		},
		bitrix24only()
		{
			return this.errorCode === ConferenceErrorCode.bitrix24only;
		},
		detectIntranetUser()
		{
			return this.errorCode === ConferenceErrorCode.detectIntranetUser;
		},
		userLimitReached()
		{
			return this.errorCode === ConferenceErrorCode.userLimitReached;
		},
		kickedFromCall()
		{
			return this.errorCode === ConferenceErrorCode.kickedFromCall;
		},
		wrongAlias()
		{
			return this.errorCode === ConferenceErrorCode.wrongAlias;
		},
		conferenceFinished()
		{
			return this.errorCode === ConferenceErrorCode.finished;
		},
		unsupportedBrowser()
		{
			return this.errorCode === ConferenceErrorCode.unsupportedBrowser;
		},
		missingMicrophone()
		{
			return this.errorCode === ConferenceErrorCode.missingMicrophone;
		},
		unsafeConnection()
		{
			return this.errorCode === ConferenceErrorCode.unsafeConnection;
		},
		noSignalFromCamera()
		{
			return this.errorCode === ConferenceErrorCode.noSignalFromCamera;
		},
		userLeftCall()
		{
			return this.errorCode === ConferenceErrorCode.userLeftCall;
		},
		showFeedback()
		{
			console.warn('this.$Bitrix.Application.get()', this.$Bitrix.Application.get());
			console.warn('this.$Bitrix.Application.get().showFeedback', this.$Bitrix.Application.get().showFeedback);
			return this.$Bitrix.Application.get().showFeedback;
		},
		callDetails()
		{
			console.warn('this.$Bitrix.Application.get().callDetails', this.$Bitrix.Application.get().callDetails);
			return this.$Bitrix.Application.get().callDetails;
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
			Cookie.set(null, `VIDEOCONF_GUEST_${this.conference.common.alias}`, '', {path: '/'});
			location.reload(true);
		},
		getBxLink()
		{
			return `bx://videoconf/code/${this.$Bitrix.Application.get().getAlias()}`;
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
		},
		onFeedbackSent()
		{
			setTimeout(() => {
				this.callFeedbackSent = true;
			}, 1500);
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
				<template v-if="!callFeedbackSent && showFeedback">
					<bx-im-component-call-feedback @feedbackSent="onFeedbackSent" :callDetails="callDetails" :darkMode="true"/>
				</template>
				<template v-else>
					<div class="bx-im-component-call-error-container">
						<div class="bx-im-component-call-error-content">
							<div class="bx-im-component-call-error-text">{{ localize['BX_IM_COMPONENT_CALL_ERROR_USER_LEFT_THE_CALL'] }}</div>
						</div>
					</div>
				</template>
			</template>
		</div>
	`
};

export {Error};