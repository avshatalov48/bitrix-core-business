import { Util } from 'calendar.util';
import { EventEmitter } from 'main.core.events';
import { Tag, Loc } from 'main.core';
import { Popup } from 'main.popup';

export class Search
{
	PRESET_INVITED = 'filter_calendar_meeting_status_q';
	EMPTY_RESULT_POPUP_WIDTH = 466;
	MIN_QUERY_LENGTH = 3;
	SHOW_LOADER_DELAY = 500;
	MIN_LOADER_DURATION = 1000;

	constructor(filterId)
	{
		this.BX = BX; // for calendar in slider
		this.filterId = filterId;
		this.filter = this.BX.Main.filterManager.getById(this.filterId);
		this.filterApi = this.filter.getApi();
		this.isActive = false;
		this.isInvitationEnabled = false;

		this.DOM = {
			filterWrap: this.filter.popupBindElement,
			filterInput: this.filter.popupBindElement.querySelector('input'),
		};

		this.updateActive();

		this.DOM.filterInput.removeEventListener('input', this.updateActive.bind(this));
		this.DOM.filterInput.addEventListener('input', this.updateActive.bind(this));

		EventEmitter.unsubscribe('BX.Filter.Search:input', this.updateActive.bind(this));
		EventEmitter.unsubscribe('BX.Main.Filter:beforeApply', this.beforeApplyFilterHandler.bind(this));
		EventEmitter.unsubscribe('BX.Main.Filter:apply', this.applyFilterHandler.bind(this));

		EventEmitter.subscribe('BX.Filter.Search:input', this.updateActive.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:beforeApply', this.beforeApplyFilterHandler.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:apply', this.applyFilterHandler.bind(this));
	}

	getFilter()
	{
		return this.filter;
	}

	beforeApplyFilterHandler()
	{
		const calendarContext = Util.getCalendarContext();

		this.filterDataLoaderStartTime = false;
		this.filterDataRequestSent = true;
		clearTimeout(this.showLoaderTimeout);
		this.showLoaderTimeout = setTimeout(() => {
			if (this.filterDataRequestSent)
			{
				this.filterDataLoaderStartTime = (new Date()).getTime();
				calendarContext.showLoader();
			}
		}, this.SHOW_LOADER_DELAY);

		this.updateActive();
	}

	applyFilterHandler()
	{
		this.isInvitationEnabled = false;
		this.applyFilter();
	}

	applyFilter()
	{
		const calendarContext = Util.getCalendarContext();

		if (this.isFilterEmpty())
		{
			if (calendarContext.getView().resetFilterMode)
			{
				calendarContext.getView().resetFilterMode({resetSearchFilter: false});
			}

			this.filterDataRequestSent = false;
			calendarContext.hideLoader();

			return;
		}

		BX.ajax.runAction('calendar.api.calendarajax.getFilterData', {
			data: {
				ownerId: calendarContext.util.config.ownerId,
				userId: calendarContext.util.config.userId,
				type: calendarContext.util.config.type,
			},
		}).then((response) => {
			this.filterDataRequestSent = false;

			if (this.filterDataLoaderStartTime)
			{
				const timePassed = (new Date()).getTime() - this.filterDataLoaderStartTime;
				const remainingTimeout = this.MIN_LOADER_DURATION - timePassed;
				if (remainingTimeout > 0)
				{
					setTimeout(() => {
						calendarContext.hideLoader();
						this.displaySearchResult(calendarContext, response.data.entries);
					}, remainingTimeout);
				}
				else
				{
					calendarContext.hideLoader();
					this.displaySearchResult(calendarContext, response.data.entries);
				}
			}
			else
			{
				this.displaySearchResult(calendarContext, response.data.entries);
			}
		}, (error) => {
			console.error(error);
			calendarContext.hideLoader();
		});
	}

	displaySearchResult(calendarContext, entries)
	{
		if (!entries || this.isFilterEmpty())
		{
			return;
		}

		if (!this.isInvitationPresetEnabled() && entries.length === 0)
		{
			this.showEmptyResultPopup();
			return;
		}

		calendarContext.viewNameBeforeFilter = calendarContext.getView().name;
		if (calendarContext.getView().getViewRange)
		{
			calendarContext.dateBeforeFilter = calendarContext.getView().getViewRange().start;
		}

		calendarContext.setView('list', {animation: true});
		calendarContext.getView().applyFilterMode();
		this.displayFilterResult(entries);
	}

	displayFilterResult(filteredEntries)
	{
		const calendarContext = Util.getCalendarContext();
		const entries = [];

		for (const entry of filteredEntries)
		{
			entries.push(new window.BXEventCalendar.Entry(calendarContext, entry));
		}

		calendarContext.getView().displayResult(entries);
	}

	showEmptyResultPopup()
	{
		const popupWidth = this.EMPTY_RESULT_POPUP_WIDTH;

		if (!this.emptyResultPopup)
		{
			this.emptyResultPopup = new Popup({
				className: 'calendar-search-no-result-popup',
				content: this.getEmptyResultPopupContent(),
				bindElement: this.DOM.filterWrap,
				offsetTop: 5,
				offsetLeft: (this.DOM.filterWrap.offsetWidth / 2) - popupWidth / 2,
				width: popupWidth,
				closeIcon: true,
				overlay: {
					opacity: 0,
				},
				autoHide: true,
				closeByEsc: true,
				animation: "fading-slide",
				angle: {
					position: 'top',
					offset: popupWidth / 2 - 10,
				},
			});
		}

		this.emptyResultPopup.show();
	}

	getEmptyResultPopupContent()
	{
		return Tag.render`<div class="calendar-search-no-result-popup-container">
			<div class="calendar-search-no-result-popup-title">${Loc.getMessage('EC_CALENDAR_EMPTY_SEARCH_RESULT_TITLE')}</div>
			<div class="calendar-search-no-result-popup-text">${Loc.getMessage('EC_CALENDAR_EMPTY_SEARCH_RESULT_TEXT')}</div>
		</div>`;
	}

	isFilterEmpty()
	{
		return this.arePresetsEmpty() && this.isSearchEmpty();
	}

	arePresetsEmpty()
	{
		const searchField = this.filter.getSearch();
		return !searchField.getLastSquare();
	}

	isSearchEmpty()
	{
		const query = this.getSearchQuery();
		return !query || query.length < this.MIN_QUERY_LENGTH;
	}

	getSearchQuery()
	{
		return this.filter.getSearch().getSearchString();
	}

	setPresetInvitation()
	{
		this.isInvitationEnabled = true;
		this.filterApi.setFilter({preset_id: this.PRESET_INVITED});
	}

	resetPreset()
	{
		this.filterApi.setFilter({preset_id: 'default_filter'});
	}

	resetFilter()
	{
		this.filter.resetFilter();
	}

	isInvitationPresetEnabled()
	{
		const filterFields = this.filter.getFilterFieldsValues();
		return this.getFilterPreset() === this.PRESET_INVITED || filterFields.MEETING_STATUS === 'Q';
	}

	updateActive()
	{
		if (this.emptyResultPopup)
		{
			this.emptyResultPopup.close();
		}

		this.setActive(this.isFilterActive());
	}

	isFilterActive()
	{
		if (this.isInvitationEnabled && !this.isActive)
		{
			return false;
		}

		const isPresetApplied = !['default_filter', 'tmp_filter'].includes(this.getFilterPreset());
		const isSearchEmpty = this.filter.getSearch().getSearchString() === '';
		return isPresetApplied || !isSearchEmpty || this.hasFilledFields();
	}

	hasFilledFields()
	{
		const fields = this.filter.getFilterFieldsValues();
		for (const fieldName in fields)
		{
			const field = fields[fieldName];
			const isFieldFilled = this.isArrayFieldFilled(field) || this.isStringFieldFilled(field);
			if (isFieldFilled)
			{
				return true;
			}
		}

		return false;
	}

	isArrayFieldFilled(field)
	{
		return BX.Type.isArrayFilled(field);
	}

	isStringFieldFilled(field)
	{
		return field !== 'NONE' && BX.Type.isStringFilled(field);
	}

	getFilterPreset()
	{
		return this.filter.getPreset().getCurrentPresetId();
	}

	setActive(isActive)
	{
		this.isActive = isActive;
		if (this.isActive)
		{
			BX.removeClass(this.DOM.filterWrap, 'main-ui-filter-default-applied');
			BX.addClass(this.DOM.filterWrap, 'main-ui-filter-search--showed');
		}
		else
		{
			BX.removeClass(this.DOM.filterWrap, 'main-ui-filter-search--showed');
			BX.addClass(this.DOM.filterWrap, 'main-ui-filter-default-applied');
		}
	}
}