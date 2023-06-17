import {Event} from 'main.core';

import {ReactionsSelect, reactionCssClass as ReactionIconClass} from 'ui.reactions-select';
import {Lottie} from 'ui.lottie';

import {ReactionUser} from './user';
import {AdditionalUsers} from './additional-users';

const USERS_TO_SHOW = 5;
const SHOW_USERS_DELAY = 1500;

// @vue/component
export const ReactionItem = {
	components: {ReactionUser, AdditionalUsers},
	props:
	{
		messageId: {
			type: Number,
			required: true
		},
		type: {
			type: String,
			required: true
		},
		counter: {
			type: Number,
			required: true
		},
		users: {
			type: Array,
			required: true
		},
		selected: {
			type: Boolean,
			required: true
		},
		animate: {
			type: Boolean,
			required: true
		}
	},
	emits: ['click'],
	data()
	{
		return {
			showAdditionalUsers: false
		};
	},
	computed:
	{
		showUsers(): boolean
		{
			const userLimitIsNotReached = this.counter <= USERS_TO_SHOW;
			const weHaveUsersData = this.counter === this.users.length;

			return userLimitIsNotReached && weHaveUsersData;
		},
		preparedUsers(): number[]
		{
			return [...this.users].sort((a, b) => a - b).reverse();
		},
		reactionClass(): string
		{
			return ReactionIconClass[this.type];
		}
	},
	mounted()
	{
		if (this.animate)
		{
			this.playAnimation();
		}
	},
	methods:
	{
		playAnimation()
		{
			this.animation = Lottie.loadAnimation({
				animationData: ReactionsSelect.getLottieAnimation(this.type),
				container: this.$refs['reactionIcon'],
				loop: false,
				autoplay: false,
				renderer: 'svg',
				rendererSettings: {
					viewBoxOnly: true,
				}
			});
			Event.bind(this.animation, 'complete', () => {
				this.animation.destroy();
			});
			Event.bind(this.animation, 'destroy', () => {
				this.animation = null;
			});
			this.animation.play();
		},
		startShowUsersTimer()
		{
			this.showUsersTimeout = setTimeout(() => {
				if (this.showUsers)
				{
					return;
				}
				this.showAdditionalUsers = true;
			}, SHOW_USERS_DELAY);
		},
		clearShowUsersTimer()
		{
			clearTimeout(this.showUsersTimeout);
		},
		onClick()
		{
			clearTimeout(this.showUsersTimeout);
			this.$emit('click', {animateItemFunction: this.playAnimation});
		}
	},
	template: `
		<div
			@click="onClick" 
			@mouseenter="startShowUsersTimer"
			@mouseleave="clearShowUsersTimer"
			class="bx-im-reaction-list__item"
			:class="{'--selected': selected}"
		>
			<div class="bx-im-reaction-list__item_icon" :class="reactionClass" ref="reactionIcon"></div>
			<div v-if="showUsers" class="bx-im-reaction-list__user_container">
				<TransitionGroup name="bx-im-reaction-list__user_animation">
					<ReactionUser v-for="user in preparedUsers" :key="user" :userId="user" />
				</TransitionGroup>
			</div>
			<div v-else class="bx-im-reaction-list__item_counter" ref="counter">{{ counter }}</div>
			<AdditionalUsers
				:show="showAdditionalUsers"
				:bindElement="$refs['counter'] || {}"
				:messageId="messageId"
				:reaction="type"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
};