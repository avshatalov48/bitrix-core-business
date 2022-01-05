// @flow
'use strict';
import {Type, Tag, Loc, Runtime, Dom} from 'main.core';
import {Util} from "calendar.util";
import {Popup} from 'main.popup';

export default class AfterSyncTour
{
	constructor(options = {})
	{
		this.options = options;
	}

	static createInstance(options)
	{
		return new this(options);
	}

	loadExtension()
	{
		return new Promise((resolve) => {
			Runtime.loadExtension('ui.tour').then((exports) => {
				if (exports && exports['Guide'] && exports['Manager'])
				{
					resolve();
				}
				else
				{
					console.error(`Extension "ui.tour" not found`);
				}
			});
		});
	}

	show()
	{
		this.loadExtension()
			.then(() => {
				this.guide = new BX.UI.Tour.Guide({
					steps: [
						{
							target: this.getTarget(),
							title: Loc.getMessage('CAL_AFTER_SYNC_AHA_TITLE'),
							text: Loc.getMessage('CAL_AFTER_SYNC_AHA_TEXT'),
						}
					],
					onEvents: true
				});

				this.guide.start();
			});
	}

	getTarget()
	{
		let target;
		const view = this.options.view;
		const viewWrap = view.getContainer();

		if (view.getName() === 'month')
		{
			target = viewWrap.querySelectorAll(".calendar-grid-today")[0];
		}
		else if (view.getName() === 'day'
		|| view.getName() === 'week')
		{
			const dayCode = Util.getDayCode(new Date());
			target = viewWrap.querySelector('div[data-bx-calendar-timeline-day="' + dayCode + '"] .calendar-grid-cell-inner');
		}
		else
		{
			target = document.querySelector('span[data-role="addButton"]');
		}

		return target;
	}
}
