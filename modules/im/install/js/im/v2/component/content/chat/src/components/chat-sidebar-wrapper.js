import {ChatSidebar} from 'im.v2.component.sidebar';
import '../css/chat-sidebar-wrapper.css';

// @vue/component
export const SidebarWrapper = {
	name: 'SidebarWrapper',
	components: {ChatSidebar},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		sidebarDetailBlock: {
			type: String,
			default: null
		}
	},
	emits: ['back'],
	methods:
	{
		onClickBack()
		{
			this.$emit('back');
		}
	},
	template: `
		<div class="bx-im-sidebar-wrapper__scope bx-im-sidebar-wrapper__container">
			<ChatSidebar
				:dialogId="dialogId" 
				:key="dialogId" 
				:sidebarDetailBlock="sidebarDetailBlock"
				@back="onClickBack"
			/>
		</div>
	`
};