import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';
import {Avatar, AvatarSize} from 'im.v2.component.old-chat-embedding.elements';
import '../css/search.css';
import {SearchContextMenu} from '../search-context-menu';

export const CarouselUser = {
	name: 'CarouselUser',
	components: {Avatar},
	props: {
		user: {
			type: Object,
			required: true
		}
	},
	computed:
	{
		name()
		{
			return this.user.dialog.name.split(' ')[0];
		},
		isExtranet(): boolean
		{
			return this.user.user.extranet;
		},
		AvatarSize: () => AvatarSize,
	},
	created()
	{
		this.contextMenuManager = new SearchContextMenu(this.$Bitrix);
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		onClick()
		{
			EventEmitter.emit(EventType.dialog.open, {
				dialogId: this.user.dialogId,
				chat: this.user.dialog,
				user: this.user.user
			});
			BX.MessengerProxy.clearSearchInput();
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const item = {dialogId: this.user.dialogId};
			EventEmitter.emit(EventType.search.openContextMenu, {item, event});
		},
	},
	template: `
		<div class="bx-messenger-carousel-item" @click="onClick" @click.right.prevent="onRightClick">
			<Avatar :dialogId="user.dialogId" :size="AvatarSize.L" />
			<div :class="[isExtranet ? 'bx-messenger-carousel-item-extranet' : '', 'bx-messenger-carousel-item-title']">
				{{name}}
			</div>
		</div>
	`
};