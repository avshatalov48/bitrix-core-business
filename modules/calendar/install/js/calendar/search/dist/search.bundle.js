this.BX = this.BX || {};
(function (exports,main_core,calendar_util,main_core_events) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2,
	    _t3;
	class Search {
	  constructor(filterId, counters = '') {
	    this.BX = BX; // for calendar in slider

	    this.filterId = filterId;
	    this.minSearchStringLength = 2;

	    if (counters) {
	      this.counters = [{
	        id: 'invitation',
	        className: 'calendar-counter-invitation',
	        pluralMessageId: 'EC_COUNTER_INVITATION_PLURAL_',
	        value: counters.invitation || 0
	      }];
	    }

	    this.filter = this.BX.Main.filterManager.getById(this.filterId);

	    if (this.filter) {
	      this.filterApi = this.filter.getApi();
	      this.applyFilterBinded = this.applyFilter.bind(this);
	      main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.applyFilterBinded);
	    }
	  }

	  getFilter() {
	    return this.filter;
	  }

	  updateCounters() {
	    this.showCounters = false;
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    this.BX.cleanNode(calendarContext.countersCont);
	    this.countersWrap = main_core.Tag.render(_t || (_t = _`<div class="calendar-counter-title"></div>`));
	    main_core.Dom.append(this.countersWrap, calendarContext.countersCont);

	    for (const counter of this.counters) {
	      if (counter && counter.value > 0) {
	        this.showCounters = true;
	        break;
	      }
	    }

	    if (this.showCounters) {
	      this.countersPage = main_core.Tag.render(_t2 || (_t2 = _`<span class="calendar-counter-page-name">${0}</span>`), main_core.Loc.getMessage('EC_COUNTER_TOTAL'));
	      main_core.Dom.append(this.countersPage, this.countersWrap);

	      for (const counter of this.counters) {
	        if (counter && counter.value > 0) {
	          const pluralNumber = main_core.Loc.getPluralForm(counter.value);
	          this.countersContainer = main_core.Tag.render(_t3 || (_t3 = _`
					<span class="calendar-counter-container ${0}" data-bx-counter="${0}">
						<span class="calendar-counter-inner">
							<span class="calendar-counter-number">${0}</span>
							<span class="calendar-counter-text">
								 ${0}
							</span>
						</span>
					</span>`), counter.className, counter.id, counter.value, main_core.Loc.getMessage(counter.pluralMessageId + pluralNumber));
	          main_core.Dom.append(this.countersContainer, this.countersWrap);
	          main_core.Event.bind(this.countersContainer, 'click', () => {
	            this.applyCounterEntries(counter.id);
	          });
	        }
	      }
	    } else {
	      this.countersWrap.innerHTML = main_core.Loc.getMessage('EC_NO_COUNTERS');
	    }
	  }

	  setCountersValue(counters) {
	    if (main_core.Type.isPlainObject(counters)) {
	      for (const counter of this.counters) {
	        if (!main_core.Type.isUndefined(counters[counter.id])) {
	          counter.value = counters[counter.id] || 0;
	        }
	      }

	      this.updateCounters();
	    }
	  }

	  displaySearchResult(response) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    const entries = [];

	    for (const entry of response.entries) {
	      entries.push(new window.BXEventCalendar.Entry(calendarContext, entry));
	    }

	    calendarContext.getView().displayResult(entries);

	    if (response.counters) {
	      this.setCountersValue(response.counters);
	    }
	  }

	  applyCounterEntries(counterId) {
	    if (counterId === 'invitation') {
	      this.filterApi.setFilter({
	        preset_id: "filter_calendar_meeting_status_q"
	      });
	    }
	  }

	  applyFilter(id, data, ctx, promise, params) {
	    if (params) {
	      params.autoResolve = false;
	    }

	    this.applyFilterHandler(promise).then(() => {});
	  }

	  applyFilterHandler(promise) {
	    return new Promise(resolve => {
	      const calendarContext = calendar_util.Util.getCalendarContext();

	      if (this.isFilterEmpty()) {
	        if (calendarContext.getView().resetFilterMode) {
	          calendarContext.getView().resetFilterMode({
	            resetSearchFilter: false
	          });
	        }

	        if (promise) {
	          promise.fulfill();
	        }
	      } else {
	        calendarContext.setView('list', {
	          animation: false
	        });
	        calendarContext.getView().applyFilterMode();
	        BX.ajax.runAction('calendar.api.calendarajax.getFilterData', {
	          data: {
	            ownerId: calendarContext.util.config.ownerId,
	            userId: calendarContext.util.config.userId,
	            type: calendarContext.util.config.type
	          }
	        }).then(response => {
	          if (response.data.entries) {
	            if (!calendarContext.getView().filterMode) {
	              calendarContext.getView().applyFilterMode();
	              this.displaySearchResult(response.data);
	            } else {
	              this.displaySearchResult(response.data);
	            }
	          }

	          if (promise) {
	            promise.fulfill();
	          }

	          resolve(response.data);
	        }, response => {
	          resolve(response.data);
	        });
	      }
	    });
	  }

	  isFilterEmpty() {
	    const searchField = this.filter.getSearch();
	    return !searchField.getLastSquare() && (!searchField.getSearchString() || searchField.getSearchString().length < this.minSearchStringLength);
	  }

	  resetFilter() {
	    this.filter.resetFilter();
	  }

	}

	exports.Search = Search;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar,BX.Event));
//# sourceMappingURL=search.bundle.js.map
