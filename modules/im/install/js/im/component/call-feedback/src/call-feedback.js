import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { BitrixVue } from "ui.vue";
import "ui.forms";
import { Menu } from "main.popup";

import './call-feedback.css';
import { Logger } from "im.lib.logger";

BitrixVue.component('bx-im-component-call-feedback',
{
	props: {
		darkMode: {
			type: Boolean,
			required: false,
			default: false
		},
		callDetails: {
			type: Object,
			required: false,
			default: () => {
				return {
					id: 0,
					provider: '',
					userCount: 0,
					browser: '',
					isMobile: false,
					isConference: false
				}
			}
		}
	},
	data()
	{
		return {
			selectedRating: 0,
			hoveredRating: 0,
			selectedProblem: '',
			problemDescription: '',
			isFilled: false
		}
	},
	created()
	{
		this.initProblemsList();
		this.selectedProblem = this.problemsList.noProblem;
	},
	computed:
	{
		showTextarea()
		{
			return this.selectedProblem === this.problemsList.other;
		},
		wrapClasses()
		{
			return ['bx-im-call-feedback-wrap', this.darkMode? 'bx-im-call-feedback-wrap-dark': '']
		}
	},
	methods:
	{
		onRatingMouseover(index)
		{
			this.hoveredRating = index;
		},
		onRatingMouseOut(index)
		{
			this.hoveredRating = 0;
		},
		onRatingClick(index)
		{
			this.selectedRating = index;
		},
		prepareFeedback()
		{
			return {
				event: 'call_feedback',
				call_id: this.callDetails.id,
				kind: this.callDetails.provider,
				userCount: this.callDetails.userCount,
				browser: this.callDetails.browser,
				isMobile: this.callDetails.isMobile,
				isConference: this.callDetails.isConference,
				callRating: this.selectedRating,
				callProblem: this.getProblemCode(),
				problemDescription: this.problemDescription
			};
		},
		getProblemCode()
		{
			let problem = '';
			for (const [key, value] of Object.entries(this.problemsList))
			{
				if (this.selectedProblem === value)
				{
					problem = key;
				}
			}

			return problem;
		},
		sendFeedback()
		{
			this.isFilled = true;
			const feedback = this.prepareFeedback();
			Logger.warn('Call feedback', feedback);
			this.$emit('feedbackSent');

			if (this.selectedRating === 0 && this.selectedProblem === this.problemsList.noProblem)
			{
				return;
			}

			BX.Call.Util.sendTelemetryEvent(feedback);
		},
		getRatingStarClasses(index)
		{
			return [
				'bx-im-call-feedback-rating-star',
				this.hoveredRating >= index || this.selectedRating >= index ? 'bx-im-call-feedback-rating-star-filled': 'bx-im-call-feedback-rating-star-empty'
			];
		},
		initProblemsList()
		{
			this.problemsList = {
				noProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_NO_ISSUE'),
				videoQuality: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_VIDEO_QUALITY'),
				cantSeeEachOther: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CANT_SEE_EACH_OTHER'),
				cantHearEachOther: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CANT_HEAR_EACH_OTHER'),
				audioQuality: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_AUDIO_QUALITY'),
				screenSharingProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_SCREEN_SHARING_PROBLEM'),
				recordingProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_RECORDING_PROBLEM'),
				callInterfaceProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CALL_INTERFACE_PROBLEM'),
				gotDisconnected: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_GOT_DISCONNECTED'),
				other: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_OTHER')
			};
		},
		createProblemSelectPopup()
		{
			const problemSelect = this.$refs['problemSelect'];
			const className = 'bx-im-call-feedback-problem-select' + (this.darkMode ? ' bx-im-call-feedback-problem-select-dark': '');
			const items = [];
			for (const problem of Object.values(this.problemsList)) {
				items.push({
					text: problem,
					onclick: (event, item) => {
						this.onProblemClick(item);
					},
					className: 'bx-im-call-feedback-problem-option'
				});
			}
			this.problemSelectPopup = new Menu({
				bindElement: problemSelect,
				items,
				className,
				offsetTop: 0
			});
		},
		toggleProblemSelectPopup()
		{
			if (!this.problemSelectPopup)
			{
				this.createProblemSelectPopup();
			}

			this.problemSelectPopup.toggle();
		},
		onProblemClick(problem)
		{
			this.selectedProblem = problem.text;
			this.problemSelectPopup.toggle();
		}
	},
	// language=Vue
	template: `
		<div :class="wrapClasses">
			<div class="bx-im-call-feedback-header">
		  		<div class="bx-im-call-feedback-header-icon"></div>
				<div class="bx-im-call-feedback-header-title">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_VIDEOCALL_FINISHED') }}</div>
			</div>
			<div class="bx-im-call-feedback-content">
			  	<template v-if="!isFilled">
					<div class="bx-im-call-feedback-content-title">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_RATE_QUALITY') }}</div>
					<div class="bx-im-call-feedback-rating-wrap">
					  	<template v-for="i in 5">
							<div
						  		@click="onRatingClick(i)"
								@mouseover="onRatingMouseover(i)"
								@mouseout="onRatingMouseOut(i)"
							  	:class="getRatingStarClasses(i)"
							></div>
						</template>
					</div>
					<div class="bx-im-call-feedback-problem">
						<div @click="toggleProblemSelectPopup" class="bx-im-call-feedback-problem-selected ui-ctl ui-ctl-after-icon ui-ctl-dropdown" ref="problemSelect">
							<div class="ui-ctl-after ui-ctl-icon-angle"></div>
							<div class="ui-ctl-element">{{ selectedProblem }}</div>
						</div>
					</div>
				  	<template v-if="showTextarea">
				  		<textarea
						  class="bx-im-call-feedback-problem-description"
						  v-model="problemDescription"
						  :placeholder="$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_DESCRIPTION')"
						></textarea>
					</template>
				  	<div class="bx-im-call-feedback-submit-wrap">
						<button @click="sendFeedback" class="ui-btn ui-btn-lg ui-btn-primary bx-im-call-feedback-submit">
							{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_SEND') }}
						</button>
					</div>
				</template>
			  	<template v-else>
				  	<div class="bx-im-call-feedback-filled-wrap">
						<div class="bx-im-call-feedback-filled-icon"></div>
						<div class="bx-im-call-feedback-filled-text">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_FILLED') }}</div>
					</div>
				</template>
			</div>
		</div>
	`
});