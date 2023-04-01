this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3,calendar_sharing_publicevent,main_core,calendar_util) {
	'use strict';

	const DeletedViewFormTemplate = {
	  props: {
	    eventInfo: Object,
	    hostName: String
	  },
	  components: {
	    EventInfo: calendar_sharing_publicevent.EventInfo
	  },
	  template: `
		<div class="calendar-shared-event-container calendar-sharing--subtract calendar-sharing--error">
			<div class="calendar-shared-event_icon"></div>
			<div class="calendar-shared-event_deleted-title">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_IS_DELETED') }}</div>
			<EventInfo
				:event-info="eventInfo"
				:current-meeting-status="'N'"
				:is-deleted="true"
				:show-host="true"
			/>
		</div>
	`
	};

	class DeletedViewForm {
	  constructor(entryId) {
	    this.entryId = entryId;
	  }

	  initInSlider(slider, promiseResolve) {
	    this.createContent(slider).then(html => {
	      if (main_core.Type.isFunction(promiseResolve)) {
	        promiseResolve(html);
	      }
	    });
	  }

	  createContent(slider) {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.sharingajax.getDeletedSharedEvent', {
	        data: {
	          entryId: parseInt(this.entryId)
	        }
	      }).then(response => {
	        const entry = response.data.entry;
	        const userTimezone = response.data.userTimezone;
	        const deletedViewSliderRoot = document.createElement('div');
	        deletedViewSliderRoot.className = 'calendar-sharing--bg-red calendar-shared-event-deleted-view-slider-root';
	        ui_vue3.BitrixVue.createApp(DeletedViewFormTemplate, {
	          eventInfo: {
	            dateFrom: calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(entry.timestampFromUTC) * 1000, userTimezone),
	            dateTo: calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(entry.timestampToUTC) * 1000, userTimezone),
	            timezone: calendar_util.Util.getFormattedTimezone(userTimezone),
	            name: entry.NAME,
	            hostName: entry.HOST_NAME,
	            hostId: entry.MEETING_HOST
	          }
	        }).mount(deletedViewSliderRoot);
	        slider.sliderContent = deletedViewSliderRoot;
	        resolve(deletedViewSliderRoot);
	      });
	    });
	  }

	}

	exports.DeletedViewForm = DeletedViewForm;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Vue3,BX.Calendar.Sharing,BX,BX.Calendar));
//# sourceMappingURL=deletedviewform.bundle.js.map
