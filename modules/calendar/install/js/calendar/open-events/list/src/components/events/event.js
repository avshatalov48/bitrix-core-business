import { Runtime } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Messenger } from 'im.public.iframe';
import { EventType } from 'im.v2.const';
import { mapGetters } from 'ui.vue3.vuex';
import { AppSettings } from '../../helpers/app-settings';
import { EventManager } from '../../data-manager/event-manager/event-manager';
import { EventModel } from '../../model/event/open-event';
import { CalendarSheet } from './parts/calendar-sheet';
import { AttendButton } from './parts/attend-button';
import { CommentCounter } from './parts/comment-counter';
import { AttendeeCounter } from './parts/attendee-counter';
import { NameWithCounter } from './parts/name-with-counter';

import './css/event-list.css';

export const Event = {
	props: {
		event: EventModel,
	},
	computed: {
		...mapGetters({
			selectedCategoryId: 'selectedCategoryId',
		}),
	},
	methods: {
		async openComments(): Promise<void>
		{
			const categoryChannelId = this.event.categoryChannelId;
			const messageId = this.event.threadId;

			await Messenger.openChat(`chat${categoryChannelId}`, messageId);

			EventEmitter.emit(EventType.dialog.openComments, { messageId });
		},
		async openEvent(): void
		{
			const { EntryManager, Entry, CalendarSection } = await Runtime.loadExtension('calendar.entry');

			const section = new CalendarSection({
				...AppSettings.openEventSection,
				PERM: {
					'view_time': true,
					'view_title': true,
					'view_full': true,
					'add': false,
					'edit': false,
					'edit_section': false,
					'access': false,
				},
			});

			const entry = new Entry({
				data: {
					ID: this.event.id,
					NAME: this.event.name,
					SKIP_TIME: this.event.isFullDay,
					dateFrom: this.event.dateFrom,
					dateTo: this.event.dateTo,
					SECT_ID: section.getId(),
					RRULE: this.event.fields.rrule,
					COLOR: this.event.color,
					'~RRULE_DESCRIPTION': this.event.rruleDescription,
				},
			});

			EntryManager.openCompactViewForm({
				entry,
				sections: [section],
			});

			if (this.event.isNew)
			{
				EventManager.setEventWatched(this.event.id);
			}
		},
		async attendEvent(isAttendee: boolean): Promise<void>
		{
			EventManager.setEventAttendee(this.event.id, isAttendee);
		},
	},
	components: {
		CalendarSheet,
		AttendButton,
		CommentCounter,
		AttendeeCounter,
		NameWithCounter,
	},
	template: `
		<div class="calendar-open-events-list-item">
			<div class="calendar-open-events-list-item-info">
				<CalendarSheet :event="event"/>
				<NameWithCounter
					:event="event"
					@openEvent="openEvent()"
				/>
			</div>
			<div class="calendar-open-events-list-item-actions">
				<CommentCounter :commentsCount="event.commentsCount" @click="openComments()"/>
				<AttendeeCounter
					:attendeesCount="event.attendeesCount"
					:maxAttendees="event.eventOptions.maxAttendees"
				/>
				<AttendButton :isAttendee="event.isAttendee" @click="attendEvent(!event.isAttendee)"/>
			</div>
		</div>
	`,
};
