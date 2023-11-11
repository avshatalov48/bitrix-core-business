import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Avatar, AvatarSize } from 'im.v2.component.elements';

import { SearchContextMenu } from '../classes/search-context-menu';

import '../css/carousel-user.css';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const CarouselUser = {
	name: 'CarouselUser',
	components: { Avatar },
	props: {
		userId: {
			type: Number,
			required: true,
		},
	},
	emits: ['clickItem'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialogId(): number
		{
			return this.userId.toString();
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
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
				dialogId: this.dialogId,
				nativeEvent: event,
			});
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const item = { dialogId: this.dialogId };
			EventEmitter.emit(EventType.search.openContextMenu, { item, nativeEvent: event });
		},
	},
	template: `
		<div 
			class="bx-im-carousel-user__container bx-im-carousel-user__scope"
			:class="{'--extranet': isExtranet}"
			@click="onClick" 
			@click.right.prevent="onRightClick"
		>
			<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
			<div class="bx-im-carousel-user__title" :title="name">
				{{ name }}
			</div>
		</div>
	`,
};
