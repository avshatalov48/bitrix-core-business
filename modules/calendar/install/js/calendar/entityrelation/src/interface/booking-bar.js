import { Loc, Event, Runtime } from 'main.core';
import type Bar from './bar';
import type { RelationData } from '../type/data';

export class BookingBar
{
	#bar: Bar;
	#BookingEventPopup = null;

	constructor(bar: Bar)
	{
		this.#bar = bar;
	}

	render(relationData: RelationData): ?HTMLElement
	{
		Runtime.loadExtension('booking.application.booking-event-popup').then((exports): void => {
			this.#BookingEventPopup = exports.BookingEventPopup;
		}).catch((error): void => {
			console.error('Calendar. EntityRelation. Load BookingEventPopup extension error', error);
		});

		return this.#bar.render(
			relationData,
			this.#getEntityLink(relationData),
		);
	}

	#getEntityLink(relationData: RelationData): ?HTMLElement
	{
		const entityLink = this.#bar.getEntityLink({
			link: relationData.entity.link,
			text: Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_BOOKING'),
			title: Loc.getMessage('CALENDAR_RELATION_ENTITY_LINK_BOOKING'),
		});

		Event.bind(entityLink, 'click', async (event): Promise<void> => {
			event.preventDefault();
			await this.#openBooking(relationData.entity.id);
		});

		return entityLink;
	}

	async #openBooking(bookingId: number): void
	{
		if (!this.#BookingEventPopup)
		{
			return;
		}

		await new this.#BookingEventPopup({ bookingId }).show();
	}
}
