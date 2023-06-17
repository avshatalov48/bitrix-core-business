import {ReactionsSelect, reactionType as ReactionType} from 'ui.reactions-select';

import {ReactionService} from '../classes/reaction-service';

import '../css/selector.css';

import type {ImModelReactions} from 'im.v2.model';

const SHOW_DELAY = 500;
const HIDE_DELAY = 500;

// @vue/component
export const ReactionSelector = {
	props:
	{
		messageId: {
			type: Number,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		reactionsData(): ImModelReactions
		{
			return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
		},
		ownReactionSet(): boolean
		{
			return this.reactionsData?.ownReactions?.size > 0;
		}
	},
	methods:
	{
		startShowTimer()
		{
			this.showTimeout = setTimeout(() => {
				this.showSelector();
			}, SHOW_DELAY);
		},
		clearShowTimer()
		{
			clearTimeout(this.showTimeout);
			this.setHideTimeout();
		},
		showSelector()
		{
			this.selector = new ReactionsSelect({
				name: 'im-base-message-reaction-selector',
				position: this.$refs['container']
			});
			this.subscribeToSelectorEvents();
			this.selector.show();
		},
		subscribeToSelectorEvents()
		{
			this.selector.subscribe('select', (selectEvent) => {
				const {reaction} = selectEvent.getData();
				this.getReactionService().setReaction(this.messageId, reaction);
				this.selector?.hide();
			});

			this.selector.subscribe('mouseleave', this.setHideTimeout);

			this.selector.subscribe('mouseenter', () => {
				clearTimeout(this.hideTimeout);
			});

			this.selector.subscribe('hide', () => {
				clearTimeout(this.hideTimeout);
				this.selector = null;
			});
		},
		setHideTimeout()
		{
			this.hideTimeout = setTimeout(() => {
				this.selector?.hide();
			}, HIDE_DELAY);
		},
		onIconClick()
		{
			this.clearShowTimer();
			if (this.ownReactionSet)
			{
				const [currentReaction] = [...this.reactionsData.ownReactions];
				this.getReactionService().removeReaction(this.messageId, currentReaction);
				return;
			}

			this.getReactionService().setReaction(this.messageId, ReactionType.like);
		},
		getReactionService(): ReactionService
		{
			if (!this.reactionService)
			{
				this.reactionService = new ReactionService();
			}

			return this.reactionService;
		}
	},
	template: `
		<div
			@mouseenter="startShowTimer"
			@mouseleave="clearShowTimer"
			class="bx-im-reaction-selector__container"
			ref="container"
		>
			<div @click="onIconClick" class="bx-im-reaction-selector__icon" :class="{'--active': ownReactionSet}"></div>
		</div>
	`
};