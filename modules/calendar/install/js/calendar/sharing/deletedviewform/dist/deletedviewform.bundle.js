this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core,calendar_sharing_publicV2) {
	'use strict';

	let _ = t => t,
	  _t;
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
	        const link = response.data.link;
	        const userTimezone = response.data.userTimezone;
	        const deletedEvent = new calendar_sharing_publicV2.Event({
	          isHiddenOnStart: false,
	          owner: {
	            name: entry.HOST_NAME,
	            lastName: ''
	          },
	          event: {
	            timestampFromUTC: entry.timestampFromUTC,
	            timestampToUTC: entry.timestampToUTC,
	            canceledTimestamp: link.canceledTimestamp,
	            externalUserName: `<a href="/company/personal/user/${link.externalUserId}/" target="_blank" class="calendar-sharing-deletedviewform_open-profile">${entry.HOST_NAME}</a>`
	          },
	          timezone: userTimezone,
	          state: 'declined',
	          isView: true,
	          inDeletedSlider: true
	        });
	        const deletedViewSliderRoot = main_core.Tag.render(_t || (_t = _`
					<div class="calendar-shared-event-deleted-view-slider-root">
						<div class="calendar-pub__block calendar-pub__state">
							${0}
						</div>
					</div>
				`), deletedEvent.render());
	        slider.sliderContent = deletedViewSliderRoot;
	        resolve(deletedViewSliderRoot);
	      });
	    });
	  }
	}

	exports.DeletedViewForm = DeletedViewForm;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX,BX.Calendar.Sharing));
//# sourceMappingURL=deletedviewform.bundle.js.map
