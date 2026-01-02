import { EventEmitter, BaseEvent } from 'main.core.events';

import { Settings, EventType } from 'im.v2.const';
import { ThemeManager, type BackgroundStyle } from 'im.v2.lib.theme';

import { TaskChatContent } from '../content/task-content';
import { initDemoState } from './functions/init-demo-state';

import './css/placeholder.css';

type MembersCountChangeEvent = BaseEvent<{ taskId: number | string, userCounter: number }>;

// @vue/component
export const TaskChatPlaceholder = {
	name: 'TaskChatPlaceholder',
	components: { TaskChatContent },
	props:
	{
		taskId: {
			type: [Number, String],
			required: true,
		},
	},
	data(): { fakeDialogId: string }
	{
		return {
			fakeDialogId: '',
			chatMembersCount: 1,
		};
	},
	computed:
	{
		containerClasses(): string[]
		{
			const alignment = this.$store.getters['application/settings/get'](Settings.appearance.alignment);

			return [`--${alignment}-align`];
		},
		backgroundStyle(): BackgroundStyle
		{
			return ThemeManager.getCurrentBackgroundStyle();
		},
	},
	created(): void
	{
		this.fakeDialogId = initDemoState();
		this.bindEvents();
	},
	beforeUnmount(): void
	{
		this.unbindEvents();
	},
	methods:
	{
		bindEvents()
		{
			EventEmitter.subscribe(EventType.task.onMembersCountChange, this.onMembersCountChange);
		},
		unbindEvents()
		{
			EventEmitter.unsubscribe(EventType.task.onMembersCountChange, this.onMembersCountChange);
		},
		onMembersCountChange(event: MembersCountChangeEvent): void
		{
			const { taskId, userCounter } = event.getData();
			if (taskId !== this.taskId)
			{
				return;
			}

			this.$store.dispatch('chats/update', {
				dialogId: this.fakeDialogId,
				fields: { userCounter },
			});
		},
	},
	template: `
		<div class="bx-im-task-chat-placeholder__container bx-im-messenger__scope">
			<TaskChatContent :dialogId="fakeDialogId" :withSidebar="false" />
			<div class="bx-im-task-chat-placeholder__overlay"></div>
		</div>
	`,
};
