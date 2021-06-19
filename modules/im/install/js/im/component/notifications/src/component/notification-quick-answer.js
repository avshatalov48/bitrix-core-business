export const NotificationQuickAnswer = {
	props: ['listItem'],
	data()
	{
		return {
			quickAnswerText: '',
			quickAnswerResultMessage: '',
			showQuickAnswer: false,
			isSendingQuickAnswer: false,
			successSentQuickAnswer: false
		};
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
					this.$refs['input'].focus();
				});
			}
		},
		sendQuickAnswer(event)
		{
			if (this.quickAnswerText.trim() === '')
			{
				return;
			}
			this.isSendingQuickAnswer = true;
			const notificationId = event.item.id;

			this.$Bitrix.RestClient.get().callMethod('im.notify.answer', {
				notify_id: notificationId,
				answer_text: this.quickAnswerText
			}).then((result) => {
				this.quickAnswerResultMessage = result.data().result_message[0];
				this.successSentQuickAnswer = true;
				this.quickAnswerText = '';
				this.isSendingQuickAnswer = false;
			}).catch((error) => {
				console.error(error);
				this.quickAnswerResultMessage = result.data().result_message[0];
				this.isSendingQuickAnswer = false;
			});
		},
	},
	//language=Vue
	template: `
		<div class="bx-notifier-item-text-vue">
			<div class="bx-notifier-answer-link-vue">
				<span class="bx-notifier-answer-reply bx-messenger-ajax" @click="toggleQuickAnswer()" @dblclick.stop>
					{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_BUTTON') }}
				</span>
			</div>
			<transition name="quick-answer-slide">
				<div v-if="showQuickAnswer && !successSentQuickAnswer" class="bx-notifier-answer-box-vue">
					<span v-if="isSendingQuickAnswer" class="bx-notifier-answer-progress-vue bx-messenger-content-load-img"></span>
					<span class="bx-notifier-answer-input">
						<input
							type="text"
							ref="input"
							autofocus
							class="bx-messenger-input"
							v-model="quickAnswerText"
							:disabled="isSendingQuickAnswer"
							@keyup.enter="sendQuickAnswer({item: listItem, event: $event})"
						>
					</span>
					<div class="bx-notifier-answer-button" @click="sendQuickAnswer({item: listItem, event: $event})"></div>
				</div>
			</transition>
			<div v-if="successSentQuickAnswer" class="bx-notifier-answer-text-vue">
				{{ quickAnswerResultMessage }}
			</div>
		</div>
	`
};