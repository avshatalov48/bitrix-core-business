import {EventEmitter} from 'main.core.events';
import {DialogType, EventType} from 'im.v2.const';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.old-chat-embedding.elements';

import '../css/search.css';

export const SearchResultItem = {
	name: 'SearchResultItem',
	components: {Avatar, ChatTitle},
	props: {
		item: {
			type: Object,
			required: true
		},
		child: {
			type: Boolean,
			default: false,
			required: false
		},
	},
	computed:
	{
		dialogId()
		{
			return this.item.getDialogId();
		},
		user()
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		dialog()
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isChat()
		{
			return !this.isUser;
		},
		isUser()
		{
			return this.dialog.type === DialogType.user;
		},
		userItemText()
		{
			if (!this.isUser)
			{
				return '';
			}

			const status = this.$store.getters['users/getLastOnline'](this.dialogId);
			if (status)
			{
				return status;
			}

			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		chatItemText()
		{
			if (this.isUser)
			{
				return '';
			}

			if (this.dialog.type === DialogType.open)
			{
				return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_OPEN');
			}

			return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_GROUP');
		},
		searchEntityId()
		{
			if (this.isUser)
			{
				return this.user.bot ? 'im-bot' : 'user';
			}

			return 'im-chat';
		},
		searchItemId()
		{
			if (this.dialogId.startsWith('chat'))
			{
				return Number.parseInt(this.dialogId.slice(4), 10);
			}

			return Number.parseInt(this.dialogId, 10);
		},
		AvatarSize: () => AvatarSize,
	},
	methods:
	{
		onClick(event)
		{
			const selectedItem = {
				id: this.searchItemId,
				entityId: this.searchEntityId,
				dialogId: this.dialogId,
			};

			EventEmitter.emit(
				EventType.search.selectItem,
				{
					selectedItem: selectedItem,
					onlyOpen: false,
					nativeEvent: event
				}
			);
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const item = {dialogId: this.dialogId};
			EventEmitter.emit(EventType.search.openContextMenu, {item, event});
		},
	},
	template: `
		<div @click="onClick" @click.right.prevent="onRightClick" class="bx-im-search-item" :class="[this.child ? 'bx-im-search-sub-item' : '']">
			<div class="bx-im-search-avatar-wrap">
				<Avatar :dialogId="dialogId" :size="AvatarSize.L" />
			</div>
			<div v-if="isUser" class="bx-im-search-result-item-content">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">
							{{ userItemText }}
						</div>
					</div>
				</div>
			</div>
			<div v-else class="bx-im-search-result-item-content">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">{{ chatItemText }}</div>
					</div>
				</div>
			</div>
		</div>
	`
};