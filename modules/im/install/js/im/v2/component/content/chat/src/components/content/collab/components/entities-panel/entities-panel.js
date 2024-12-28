import { CollabEntityType } from 'im.v2.const';

import { EntityLink } from './components/entity-link';

import './css/entities-panel.css';

import type { ImModelChat, ImModelCollabInfo } from 'im.v2.model';

// @vue/component
export const EntitiesPanel = {
	name: 'EntitiesPanel',
	components: { EntityLink },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		compactMode: {
			type: Boolean,
			required: true,
		},
	},
	computed:
	{
		CollabEntityType: () => CollabEntityType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		collabInfo(): ImModelCollabInfo
		{
			return this.$store.getters['chats/collabs/getByChatId'](this.dialog.chatId);
		},
		tasksInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.tasks;
		},
		tasksUrl(): string
		{
			return this.tasksInfo.url;
		},
		tasksCounter(): number
		{
			return this.tasksInfo.counter;
		},
		filesInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.files;
		},
		filesUrl(): string
		{
			return this.filesInfo.url;
		},
		calendarInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.calendar;
		},
		calendarUrl(): string
		{
			return this.calendarInfo.url;
		},
		calendarCounter(): number
		{
			return this.calendarInfo.counter;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-collab-header__links-container" :class="{'--compact': compactMode}">
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="tasksUrl"
				:type="CollabEntityType.tasks"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_TASKS')"
				:counter="tasksCounter"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="filesUrl"
				:type="CollabEntityType.files"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_FILES')"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="calendarUrl"
				:type="CollabEntityType.calendar"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_CALENDAR')"
				:counter="calendarCounter"
			/>
		</div>
	`,
};
