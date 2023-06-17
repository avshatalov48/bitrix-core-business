import {DateTimeFormat} from 'main.date';

import {DateFormatter, DateTemplate} from 'im.v2.lib.date-formatter';

import '../../css/meeting/meeting-item.css';

import type {ImModelSidebarMeetingItem} from 'im.v2.model';

// @vue/component
export const MeetingItem = {
	name: 'MeetingItem',
	props: {
		meeting: {
			type: Object,
			required: true
		}
	},
	emits: ['contextMenuClick'],
	data() {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		meetingItem(): ImModelSidebarMeetingItem
		{
			return this.meeting;
		},
		title(): string
		{
			return this.meetingItem.meeting.title;
		},
		date(): string
		{
			const meetingDate = this.meetingItem.meeting.dateFrom;

			return DateFormatter.formatByTemplate(meetingDate, DateTemplate.meeting);
		},
		day(): string
		{
			return this.meetingItem.meeting.dateFrom.getDate().toString();
		},
		monthShort(): string
		{
			return DateTimeFormat.format('M', this.meetingItem.meeting.dateFrom);
		},
		isActive(): boolean
		{
			return this.meetingItem.meeting.dateFrom.getTime() > Date.now();
		}
	},
	methods:
	{
		onMeetingClick()
		{
			// todo replace this call to something
			new (window.top.BX || window.BX).Calendar.SliderLoader(this.meetingItem.meeting.id).show();
		},
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				meeting: this.meetingItem,
				source: this.meetingItem.meeting.source,
				messageId: this.meetingItem.messageId,
			}, event.currentTarget);
		}
	},
	template: `
		<div 
			class="bx-im-sidebar-meeting-item__container bx-im-sidebar-meeting-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div 
				class="bx-im-sidebar-meeting-item__icon-container"
				:class="[isActive ? '--active' : '--inactive']"
			>
				<div class="bx-im-sidebar-meeting-item__day-text">{{ day }}</div>
				<div class="bx-im-sidebar-meeting-item__month-text">{{ monthShort }}</div>
			</div>
			<div class="bx-im-sidebar-meeting-item__content-container" @click="onMeetingClick">
				<div class="bx-im-sidebar-meeting-item__content">
					<div class="bx-im-sidebar-meeting-item__title">{{ title }}</div>
					<div class="bx-im-sidebar-meeting-item__date">{{ date }}</div>
				</div>
			</div>
			<button 
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`
};