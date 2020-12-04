"use strict";
import {Loc, Runtime, Type} from "main.core";
export class SliderLoader
{
	constructor(eventId, options = {})
	{
		this.extensionName = (Type.isString(eventId) && (eventId === 'NEW' || eventId.substr(0, 4) === 'EDIT'))
			? 'EventEditForm'
			: 'EventViewForm';

		this.entryId = (Type.isString(eventId) && eventId.substr(0, 4) === 'EDIT')
			? parseInt(eventId.substr(4))
			: parseInt(eventId);

		this.entry = options.entry || null;
		this.formDataValue = options.formDataValue || null;
		this.entryDateFrom = Type.isDate(options.entryDateFrom) ? options.entryDateFrom : null;
		this.timezoneOffset = options.timezoneOffset;
		this.type = options.type;
		this.ownerId = options.ownerId;
		this.userId = options.userId;
		this.sliderId = "calendar:slider-" + Math.random();
	}

	show()
	{
		BX.SidePanel.Instance.open(this.sliderId, {
			contentCallback: this.loadExtension.bind(this),
			label: {
				text: Loc.getMessage('CALENDAR_EVENT'),
				bgColor: "#55D0E0"
			}
		});
	}

	loadExtension(slider)
	{
		return new Promise((resolve) => {

			const extensionName = 'calendar.' + this.extensionName.toLowerCase();
			Runtime.loadExtension(extensionName).then((exports) => {
				if (exports && exports[this.extensionName])
				{
					const calendarForm = new exports[this.extensionName](
						{
							entryId: this.entryId,
							entry: this.entry,
							entryDateFrom: this.entryDateFrom,
							timezoneOffset: this.timezoneOffset,
							type: this.type,
							ownerId: this.ownerId,
							userId: this.userId,
							formDataValue: this.formDataValue
						}
					);
					if (typeof calendarForm.initInSlider)
					{
						calendarForm.initInSlider(slider, resolve);
					}
				}
				else
				{
					console.error(`Extension "calendar.${extensionName}" not found`);
				}
			});
		});
	}
}