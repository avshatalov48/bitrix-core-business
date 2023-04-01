import { BitrixVue } from 'ui.vue3';
import { DeletedViewFormTemplate } from './template';
import { Type } from 'main.core';
import { Util } from 'calendar.util';
import './css/deletedviewform.css';

export class DeletedViewForm
{
	constructor(entryId)
	{
		this.entryId = entryId;
	}

	initInSlider(slider, promiseResolve)
	{
		this.createContent(slider).then((html) => {
			if (Type.isFunction(promiseResolve))
			{
				promiseResolve(html);
			}
		});
	}

	createContent(slider)
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.sharingajax.getDeletedSharedEvent', {
				data: {
					entryId: parseInt(this.entryId),
				}
			}).then(response => {
				const entry = response.data.entry;
				const userTimezone = response.data.userTimezone;

				const deletedViewSliderRoot = document.createElement('div');
				deletedViewSliderRoot.className = 'calendar-sharing--bg-red calendar-shared-event-deleted-view-slider-root';
				BitrixVue.createApp(DeletedViewFormTemplate, {
					eventInfo: {
						dateFrom: Util.getTimezoneDateFromTimestampUTC(parseInt(entry.timestampFromUTC) * 1000, userTimezone),
						dateTo: Util.getTimezoneDateFromTimestampUTC(parseInt(entry.timestampToUTC) * 1000, userTimezone),
						timezone: Util.getFormattedTimezone(userTimezone),
						name: entry.NAME,
						hostName: entry.HOST_NAME,
						hostId: entry.MEETING_HOST,
					},
				}).mount(deletedViewSliderRoot);
				slider.sliderContent = deletedViewSliderRoot;

				resolve(deletedViewSliderRoot);
			})
		});
	}
}