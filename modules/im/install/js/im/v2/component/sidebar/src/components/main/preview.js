import {SidebarBlock, SidebarDetailBlock} from 'im.v2.const';
import {PersonalChatPreview} from './preview-personal-chat';
import {GroupChatPreview} from './preview-group-chat';

// @vue/component
export const MainPreview = {
	name: 'MainPreview',
	components: {GroupChatPreview, PersonalChatPreview},
	inheritAttrs: false,
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	computed:
	{
		isGroupChat(): boolean
		{
			return this.dialogId.startsWith('chat');
		},
	},
	methods:
	{
		onOpenDetail()
		{
			this.$emit('openDetail', {block: SidebarBlock.main, detailBlock: SidebarDetailBlock.main});
		}
	},
	template: `
		<GroupChatPreview 
			v-if="isGroupChat" 
			:dialogId="dialogId"
			:isLoading="isLoading" 
			@openDetail="onOpenDetail"
			class="bx-im-sidebar__box"
		/>
		<PersonalChatPreview 
			v-else 
			:dialogId="dialogId"
			:isLoading="isLoading"
			class="bx-im-sidebar__box"
		/>
	`
};