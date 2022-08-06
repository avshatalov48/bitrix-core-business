import {EventEmitter} from 'main.core.events';
import {ChatTypes, EventType, AvatarSize} from 'im.v2.const';
import {Avatar, ChatTitle} from 'im.v2.component.elements';

import '../css/search.css';

// @vue/component
export const SearchResultItem = {
	name: 'SearchResultItem',
	components: {Avatar, ChatTitle},
	props: {
		dialogId: {
			type: String,
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
			return this.dialog.type === ChatTypes.user;
		},
		userOnlineStatus()
		{
			return this.$store.getters['users/getLastOnline'](this.dialogId);
		},
		workPosition()
		{
			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		AvatarSize: () => AvatarSize,
	},
	methods:
	{
		onClick()
		{
			console.warn('onClick', this.dialog);
			EventEmitter.emit(EventType.dialog.open, {dialogId: this.dialogId});
			EventEmitter.emit(EventType.search.selectItem, this.dialogId);
		},
		onRightClick()
		{
			console.warn('onRightClick');
		}
	},
	// language=Vue
	template: `
		<div @click="onClick" @click.right.prevent="onRightClick" class="bx-im-search-item" :class="[this.child ? 'bx-im-search-sub-item' : '']">
			<div class="bx-im-search-avatar-wrap">
				<Avatar :dialogId="dialogId" :size="AvatarSize.L" />
			</div>
			<div v-if="isUser" class="bx-im-search-result-item-content">
				<div class="bx-im-search-result-item-content-header">
					<ChatTitle :dialogId="dialogId" />
				</div>
				<div class="bx-im-recent-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-message-text">{{ workPosition }}</div>
						<div class="bx-im-search-result-item-message-text">{{ userOnlineStatus }}</div>
					</div>
				</div>
			</div>
			<div v-else class="bx-im-search-result-item-title-content">
				<ChatTitle :dialogId="dialogId" />
			</div>
		</div>
	`
};