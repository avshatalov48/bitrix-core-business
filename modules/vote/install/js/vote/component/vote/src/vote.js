import { Type, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MessageMenuManager } from 'im.v2.lib.menu';
import { MessageComponent } from 'im.v2.const';
import type { BaseEvent } from 'main.core.events';

import { VoteApplication } from 'vote.application';
import { ImVoteService } from 'vote.provider.service';
import { Loader } from 'vote.component.loader';
import { VoteMessageMenu } from './classes/message-menu/message-menu';
import type { QuestionCollectionType, VoteCollectionType, VoteElementState } from 'vote.store.vote';

import { ButtonArea } from './components/button-area/button-area';
import { VotePopup } from './components/popup/popup';
import { getMessage } from './components/helpers/helpers';
import { VoteQuestion } from './components/vote-question/vote-question';

import './vote.css';

import type { VoteInitQuestion, FormattedQuestionType, AnswersSelectedEvent } from './type';

const ANONYMOUS_VOTE = 2;
const ALLOW_REVOKING = 1;

// @vue/component
export const VoteDisplay = {
	name: 'VoteDisplay',
	components:
	{
		VoteQuestion,
		ButtonArea,
		Loader,
		VotePopup,
	},
	props:
	{
		voteItem: {
			type: Object,
			required: true,
		},
		entityId: {
			type: Number,
			required: true,
		},
		entityType: {
			type: String,
			required: true,
		},
		contextId: {
			type: String,
			required: true,
		},
	},
	emits: ['vote', 'revokeVote', 'copyLink', 'completeVote'],
	data(): JsonObject
	{
		return {
			isShowPopup: false,
			questionAnswers: {},
		};
	},
	computed:
	{
		getMessage: () => getMessage,
		firstQuestion(): Record<string, VoteInitQuestion>
		{
			const firstKey = Object.keys(this.voteItem.data?.questions)[0];

			return this.voteItem.data?.questions[firstKey];
		},
		questionCollection(): QuestionCollectionType
		{
			return this.app.getStore().getters['vote/getQuestionCollection'];
		},
		voteCollection(): VoteCollectionType
		{
			return this.app.getStore().getters['vote/getVoteCollection'];
		},
		currentVote(): VoteElementState
		{
			return this.voteCollection[this.voteItem.id];
		},
		formattedQuestion(): FormattedQuestionType
		{
			const storeQuestion = this.questionCollection[this.firstQuestion.id] || {};

			return {
				...this.firstQuestion,
				totalCounter: storeQuestion.totalCounter,
				isMultiple: storeQuestion.isMultiple,
			};
		},
		isUserVoted(): boolean
		{
			if (this.isLoading)
			{
				return false;
			}

			return this.currentVote.isVoted;
		},
		canEdit(): boolean
		{
			if (this.isLoading)
			{
				return false;
			}

			return this.currentVote.canEdit;
		},
		isAnonymous(): boolean
		{
			return this.voteItem.data?.anonymity === ANONYMOUS_VOTE;
		},
		isCompleted(): boolean
		{
			if (this.isLoading)
			{
				return false;
			}

			return this.currentVote.isCompleted;
		},
		hasSelectedAnswers(): boolean
		{
			return Type.isArrayFilled(this.questionAnswers[this.firstQuestion.id]);
		},
		voteTypeText(): string
		{
			return this.isAnonymous ? getMessage('VOTE_ANONYMOUS') : getMessage('VOTE_PUBLIC');
		},
		isLoading(): boolean
		{
			return this.currentVote?.isLoading ?? true;
		},
		showRevokeNotice(): boolean
		{
			if (!this.isLoading && this.currentVote.isCompleted)
			{
				return false;
			}

			return this.voteItem.data?.options !== ALLOW_REVOKING;
		},
	},
	created(): void
	{
		this.app = VoteApplication.init();
		this.voteService = ImVoteService.init();

		MessageMenuManager.getInstance().registerMenuByMessageType(MessageComponent.voteMessage, VoteMessageMenu);
		this.subscribeOnEvents();
	},
	mounted(): void
	{
		if (!this.currentVote || this.currentVote?.isLoading !== false)
		{
			EventEmitter.emit('vote-message-batch', { messageId: this.entityId });
		}
	},
	beforeUnmount(): void
	{
		this.unsubscribeFromEvents();
	},
	methods:
	{
		subscribeOnEvents(): void
		{
			EventEmitter.subscribe('vote:message-menu:complete-vote', this.isShowCompletePopup);
			EventEmitter.subscribe('vote:message-menu:revoke-vote', this.recallVote);
			EventEmitter.subscribe('vote:message-menu:results-vote', this.showResults);
		},
		unsubscribeFromEvents(): void
		{
			EventEmitter.unsubscribe('vote:message-menu:complete-vote', this.isShowCompletePopup);
			EventEmitter.unsubscribe('vote:message-menu:revoke-vote', this.recallVote);
			EventEmitter.unsubscribe('vote:message-menu:results-vote', this.showResults);
		},
		async answersSelected(event: AnswersSelectedEvent): void
		{
			this.questionAnswers[event.questionId] = event.answerIds;
			const currentQuestion = this.questionCollection[event.questionId];
			if (currentQuestion.isMultiple || this.isUserVoted)
			{
				return;
			}

			void this.submitVote();
		},
		async submitVote(): Promise<void>
		{
			try
			{
				this.app.getStore().dispatch('vote/setUserVoted', {
					voteId: this.currentVote.id,
				});
				await this.voteService.sendVote(this.questionAnswers, this.voteItem.id, this.entityId);
				this.$emit('vote');
				this.questionAnswers = {};
			}
			catch (e)
			{
				console.error('Vote: submit vote error', e);
				this.app.getStore().dispatch('vote/resetUserVoted', {
					voteId: this.currentVote.id,
				});
				BX.UI.Notification.Center.notify({
					content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_SUBMIT'),
					autoHideDelay: 4000,
				});
			}
		},
		onClickVoteButton(): void
		{
			if (Type.isArrayFilled(this.questionAnswers[this.formattedQuestion.id]))
			{
				this.submitVote();
			}
		},
		async showResults(event: BaseEvent): void
		{
			if (event && event.data?.entityId !== this.entityId)
			{
				return;
			}
			BX.SidePanel.Instance.open(this.currentVote.resultUrl, {
				cacheable: false,
				width: 480,
				copyLinkLabel: true,
				events: {
					onOpen: ({ slider }: BX.SidePanel.Event) => {
						const copyLink = slider.getCopyLinkLabel();
						copyLink.setOnclick(() => {
							this.$emit('copyLink');
						});
					},
				},
			});
		},
		async completeVote(): void
		{
			try
			{
				this.app.getStore().dispatch('vote/setVoteCompleted', {
					voteId: this.currentVote.id,
				});
				await this.voteService.completeVote(this.entityId);
				this.$emit('completeVote');
			}
			catch (e)
			{
				console.error('Vote: complete vote error', e);

				this.app.getStore().dispatch('vote/resetVoteCompleted', {
					voteId: this.currentVote.id,
				});

				BX.UI.Notification.Center.notify({
					content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_COMPLETE'),
					autoHideDelay: 4000,
				});
			}
		},
		isShowCompletePopup(event: BaseEvent): void
		{
			if (event.data?.entityId !== this.entityId)
			{
				return;
			}
			this.isShowPopup = true;
		},
		onCompletePopupConfirm(): void
		{
			this.isShowPopup = false;
			this.completeVote();
		},
		onCompletePopupCancel(): void
		{
			this.isShowPopup = false;
		},
		async recallVote(event: BaseEvent): Promise<void>
		{
			if (event.data?.entityId !== this.entityId)
			{
				return;
			}
			const previousSelectedAnswers = this.app.getStore().getters['vote/getCurrentUserVotes'][this.firstQuestion.id];
			try
			{
				this.app.getStore().dispatch('vote/clearVotes', {
					questionId: this.firstQuestion.id,
					voteId: this.currentVote.id,
				});
				this.app.getStore().dispatch('vote/resetUserVoted', {
					voteId: this.currentVote.id,
				});
				await this.voteService.revokeVote(this.entityId, this.currentVote.id);
				this.$emit('revokeVote');
			}
			catch (e)
			{
				console.error('Vote: recall vote error', e);
				this.app.getStore().dispatch('vote/updateCurrentUserVotes', {
					questionId: this.firstQuestion.id,
					answerIds: previousSelectedAnswers,
				});
				this.app.getStore().dispatch('vote/setUserVoted', {
					voteId: this.currentVote.id,
				});
				BX.UI.Notification.Center.notify({
					content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_REVOKE'),
					autoHideDelay: 4000,
				});
			}
		},
	},
	template: `
		<form class="vote-display">
			<div class="vote-display-inner">
				<VoteQuestion
					:key="formattedQuestion.id"
					:contextId="contextId"
					:isLoading="isLoading"
					:question="formattedQuestion"
					:isUserVoted="isUserVoted"
					:isCompleted="isCompleted"
					:answers="formattedQuestion.answers"
					@answersSelected="answersSelected"
				/>
				<div class="vote-display-bottom-container">
					<div v-if="isLoading" class="vote-display__loader">
						<Loader />
					</div>
					<ButtonArea v-else
						:question="formattedQuestion"
						:isLoading="isLoading"
						:isUserVoted="isUserVoted"
						:isCompleted="isCompleted"
						:isBtnActive="hasSelectedAnswers"
						@onClickVoteButton="onClickVoteButton"
						@showResults="showResults"
					/>
					<div class="vote__notice">
						<span class="vote__notice-text">{{ voteTypeText }}</span>
						<span v-if="showRevokeNotice" class="vote__notice-text">{{ getMessage('VOTE_NOTICE_REVOKE_IS_NOT_AVAILABLE') }}</span>
						<span v-if="isCompleted" class="vote__notice-text">{{ getMessage('VOTE_NOTICE_COMPLETED') }}</span>
					</div>
				</div>
			</div>
		</form>
		<VotePopup
			v-if="isShowPopup"
			@confirm="onCompletePopupConfirm"
			@cancel="onCompletePopupCancel"
		/>
	`,
};
