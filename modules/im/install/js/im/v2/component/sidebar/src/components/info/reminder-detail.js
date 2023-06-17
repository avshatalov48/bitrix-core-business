import {SidebarDetail} from '../detail';

// @vue/component
export const ReminderDetail = {
	name: 'ReminderDetail',
	components: {SidebarDetail},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
		service: {
			type: Object,
			required: true
		}
	},
	methods:
	{
		onScroll()
		{
			console.warn('onScroll');
		}
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-reminder-detail__container bx-im-sidebar-reminder-detail__scope"
		>
			<div v-for="i in 50">rem {{ i }}</div>
		</SidebarDetail>
		
	`
};