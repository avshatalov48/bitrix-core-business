import {Button as MessengerButton, ButtonSize, ButtonColor} from 'im.v2.component.elements';
import '../css/notification-quick-answer.css';

// @vue/component
export const NotificationQuickAnswer = {
	name: 'NotificationQuickAnswer',
	components: {MessengerButton},
	props: {
		notification: {
			type: Object,
			required: true,
		}
	},
	emits: ['sendQuickAnswer'],
	data()
	{
		return {
			quickAnswerText: '',
			quickAnswerResultMessage: '',
			showQuickAnswer: false,
			isSending: false,
			successSentQuickAnswer: false
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor
	},
	methods:
	{
		toggleQuickAnswer()
		{
			if (this.successSentQuickAnswer)
			{
				this.showQuickAnswer = true;
				this.successSentQuickAnswer = false;
				this.quickAnswerResultMessage = '';
			}
			else
			{
				this.showQuickAnswer = !this.showQuickAnswer;
			}

			if (this.showQuickAnswer)
			{
				this.$nextTick(() => {
					this.$refs['textarea'].focus();
				});
			}
		},
		sendQuickAnswer()
		{
			if (this.isSending || this.quickAnswerText.trim() === '')
			{
				return;
			}

			this.isSending = true;

			this.$emit('sendQuickAnswer', {
				id: this.notification.id,
				text: this.quickAnswerText.trim(),
				callbackSuccess: (response) => {
					const {result_message: resultMessage} = response.data();
					const [message] = resultMessage;
					this.quickAnswerResultMessage = message;
					this.successSentQuickAnswer = true;
					this.quickAnswerText = '';
					this.isSending = false;
				},
				callbackError: () => {
					this.isSending = false;
				}
			});
		},
	},
	template: `
		<div class="bx-im-content-notification-quick-answer__container">
			<button 
				v-if="!showQuickAnswer"
				class="bx-im-content-notification-quick-answer__reply-link" 
				@click="toggleQuickAnswer" 
				@dblclick.stop
			>
				{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_BUTTON') }}
			</button>
			<transition name="quick-answer-slide">
				<div 
					v-if="showQuickAnswer && !successSentQuickAnswer" 
					class="bx-im-content-notification-quick-answer__textarea-container"
				>
					<textarea
						ref="textarea"
						autofocus
						class="bx-im-content-notification-quick-answer__textarea"
						v-model="quickAnswerText"
						:disabled="isSending"
						@keydown.enter.prevent
						@keyup.enter.prevent="sendQuickAnswer"
					/>
					<div 
						v-if="!successSentQuickAnswer" 
						class="bx-im-content-notification-quick-answer__buttons-container"
					>
						<MessengerButton
							:color="ButtonColor.Primary"
							:size="ButtonSize.M"
							:isRounded="true"
							:isUppercase="false"
							:text="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_SEND')"
							:isLoading="isSending"
							@click="sendQuickAnswer"
						/>
						<MessengerButton
							:color="ButtonColor.LightBorder"
							:size="ButtonSize.M"
							:isRounded="true"
							:isUppercase="false"
							:text="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_CANCEL')"
							:isDisabled="isSending"
							@click="toggleQuickAnswer"
						/>
					</div>
				</div>
			</transition>
			<div v-if="successSentQuickAnswer" class="bx-im-content-notification-quick-answer__result">
				<div class="bx-im-content-notification-quick-answer__success-icon"></div>
				<div class="bx-im-content-notification-quick-answer__success-text">{{ quickAnswerResultMessage }}</div>
			</div>
		</div>
	`
};