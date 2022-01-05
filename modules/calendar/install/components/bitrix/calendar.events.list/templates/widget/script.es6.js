import { Reflection, Dom, Tag, Type, Runtime, Text, Event} from 'main.core';
import { Util } from 'calendar.util';
import { EventEmitter } from 'main.core.events';

class NextEventList
{
	DOM = {};

	constructor(options = {})
	{
		this.maxEntryAmount = options.maxEntryAmount || 5;
		if (options && options.entries)
		{
			this.renderList(options.entries);
		}
		else
		{
			this.displayEventList();
		}

		this.displayEventListDebounce = Runtime.debounce(this.displayEventList, 3000, this);

		Event.bind(document, 'visibilitychange', this.checkDisplayEventList.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.checkDisplayEventList.bind(this));

		EventEmitter.subscribe('onPullEvent-calendar', this.displayEventListDebounce);
	}

	checkDisplayEventList()
	{
		if (this.needReload)
		{
			this.displayEventListDebounce();
		}
	}

	displayEventList()
	{
		if (this.isDisplayingNow())
		{
			this.showLoader();
			this.getEventList()
				.then((entryList) => {
					this.hideLoader();
					this.renderList(entryList);
				});
		}
		else
		{
			this.needReload = true;
		}
	}

	getEventList()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.calendarentryajax.getnearestevents', {
				data: {
					ownerId: this.ownerId,
					type: this.type,
					futureDaysAmount: 60,
					maxEntryAmount: this.maxEntryAmount
				}
			})
			.then((response) => {
				resolve(response?.data?.entries);
			});
		});
	}

	showWidget()
	{
		this.getOuterWrap().style.display = '';
	}

	hideWidget()
	{
		this.getOuterWrap().style.display = 'none';
	}

	showLoader()
	{
		this.hideLoader();
		this.DOM.loader = this.getEventListWrap()
			.appendChild(Util.getLoader(40, 'next-events-loader'));
	}

	hideLoader()
	{
		if(Type.isDomNode(this.DOM.loader))
		{
			Dom.remove(this.DOM.loader);
		}
	}

	renderList(entryList = [])
	{
		if (!Type.isArray(entryList))
		{
			entryList = [];
		}

		entryList = entryList.slice(0, this.maxEntryAmount);

		Dom.clean(this.getEventListWrap());

		const wrap = this.getEventListWrap();
		entryList.forEach((entry, i) => {
			if (i === 0)
			{
				this.setReloadTimeout(entry);
			}

			wrap.appendChild(this.renderEntry(entry));
		});

		if (entryList.length)
		{
			this.showWidget();
		}
		else
		{
			this.hideWidget();
		}

		this.needReload = false;
	}

	renderEntry(entry)
	{
		const fromDate = BX.Calendar.Util.parseDate(entry['DATE_FROM']);

		return Tag.render`
			<a href="${Text.encode(entry['~URL'])}" class="sidebar-widget-item">
				<span class="calendar-item-date">${entry['~FROM_TO_HTML']}</span>
				<span class="calendar-item-text">
					<span class="calendar-item-link">${Text.encode(entry['NAME'])}</span>
				</span>
				<span class="calendar-item-icon">
					<span class="calendar-item-icon-day">${Text.encode(entry['~WEEK_DAY'])}</span>
					<span class="calendar-item-icon-date">${fromDate.getDate()}</span>
				</span>
			</a>
		`;
	}

	getOuterWrap()
	{
		if (!this.DOM.outerWrap)
		{
			this.DOM.outerWrap = document.querySelector('.sidebar-widget.sidebar-widget-calendar');
		}
		return this.DOM.outerWrap;
	}

	getEventListWrap()
	{
		if (!this.DOM.listWrap)
		{
			this.DOM.listWrap = this.getOuterWrap().querySelector('.calendar-events-wrap');
		}
		return this.DOM.listWrap;
	}

	setReloadTimeout(entry)
	{
		if (this.reloadTimeout)
		{
			clearTimeout(this.reloadTimeout);
			this.reloadTimeout = null;
		}

		const finishEventDate = BX.Calendar.Util.parseDate(entry['DATE_TO']);
		if (Type.isDate(finishEventDate))
		{
			const currentDate = new Date();
			const offset = Math.min(
				Math.max(
					finishEventDate.getTime() - currentDate.getTime() + 60000,
					60000),
				86400000
			);

			this.reloadTimeout = setTimeout(this.displayEventList.bind(this), offset);
		}
	}

	isDisplayingNow()
	{
		return !document.hidden && !BX.SidePanel.Instance.getOpenSliders().length;
	}
}

Reflection.namespace('BX.Calendar').NextEventList = NextEventList;