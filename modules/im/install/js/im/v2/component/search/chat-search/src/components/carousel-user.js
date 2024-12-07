import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';

import { SearchContextMenu } from '../classes/search-context-menu';

import '../css/carousel-user.css';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const CarouselUser = {
	name: 'CarouselUser',
	components: { ChatAvatar },
	props: {
		userId: {
			type: Number,
			required: true,
		},
		selected: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['clickItem', 'openContextMenu'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		userDialogId(): string
		{
			return this.userId.toString();
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.userDialogId, true);
		},
		name(): string
		{
			return this.user.firstName ?? this.user.name;
		},
		isExtranet(): boolean
		{
			return this.user.extranet;
		},
	},
	created()
	{
		this.contextMenuManager = new SearchContextMenu();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		onClick(event)
		{
			this.$emit('clickItem', {
				dialogId: this.userDialogId,
				nativeEvent: event,
			});
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			this.$emit('openContextMenu', { dialogId: this.userDialogId, nativeEvent: event });
		},
	},
	template: `
		<div 
			class="bx-im-carousel-user__container bx-im-carousel-user__scope"
			:class="{'--extranet': isExtranet, '--selected': selected}"
			@click="onClick" 
			@click.right.prevent="onRightClick"
		>
			<div v-if="selected" class="bx-im-carousel-user__selected-mark"></div>
			<ChatAvatar 
				:avatarDialogId="userDialogId" 
				:contextDialogId="userDialogId" 
				:size="AvatarSize.XL" 
			/>
			<div class="bx-im-carousel-user__title" :title="name">
				{{ name }}
			</div>
		</div>
	`,
};
