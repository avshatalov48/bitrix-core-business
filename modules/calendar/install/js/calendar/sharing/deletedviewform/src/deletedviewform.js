import { Type, Tag, Dom } from 'main.core';
import { Event } from "calendar.sharing.public-v2";
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
				const link = response.data.link;
				const userTimezone = response.data.userTimezone;

				const deletedEvent = new Event({
					isHiddenOnStart: false,
					owner: {
						name: entry.HOST_NAME,
						lastName: '',
					},
					event: {
						timestampFromUTC: entry.timestampFromUTC,
						timestampToUTC: entry.timestampToUTC,
						canceledTimestamp: link.canceledTimestamp,
						externalUserName: `<a href="/company/personal/user/${link.externalUserId}/" target="_blank" class="calendar-sharing-deletedviewform_open-profile">${entry.HOST_NAME}</a>`,
					},
					timezone: userTimezone,
					state: 'declined',
					isView: true,
					inDeletedSlider: true,
				});

				const deletedViewSliderRoot = Tag.render`
					<div class="calendar-shared-event-deleted-view-slider-root">
						<div class="calendar-pub__block calendar-pub__state">
							${deletedEvent.render()}
						</div>
					</div>
				`;

				slider.sliderContent = deletedViewSliderRoot;
				resolve(deletedViewSliderRoot);
			})
		});
	}
}