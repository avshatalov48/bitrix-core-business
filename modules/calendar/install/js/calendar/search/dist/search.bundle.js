this.BX = this.BX || {};
(function (exports,calendar_util,main_core_events) {
	'use strict';

	class Search {
	  constructor(filterId) {
	    this.BX = BX; // for calendar in slider

	    this.filterId = filterId;
	    this.minSearchStringLength = 2;
	    this.filter = this.BX.Main.filterManager.getById(this.filterId);
	    this.filterApi = this.filter.getApi();
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.applyFilter.bind(this));
	  }

	  getFilter() {
	    return this.filter;
	  }

	  displaySearchResult(response) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    const entries = [];

	    for (const entry of response.entries) {
	      entries.push(new window.BXEventCalendar.Entry(calendarContext, entry));
	    }

	    calendarContext.getView().displayResult(entries);
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

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Event));
//# sourceMappingURL=search.bundle.js.map
