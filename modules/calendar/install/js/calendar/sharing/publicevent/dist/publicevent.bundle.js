this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3,main_date,main_core,main_popup,calendar_util) {
	'use strict';

	const EventInfo = {
	  props: {
	    eventInfo: Object,
	    currentMeetingStatus: String,
	    isDeclined: Boolean,
	    isDeleted: Boolean,
	    showHost: Boolean
	  },

	  data() {
	    return {
	      loc: {
	        today: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_TODAY'),
	        tomorrow: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_TOMORROW')
	      }
	    };
	  },

	  computed: {
	    ownerStatusText() {
	      const key = 'CALENDAR_SHARING_EVENT_OWNER_STATUS_' + this.currentMeetingStatus.toUpperCase();
	      return main_core.Loc.getMessage(key);
	    }

	  },
	  methods: {
	    getEventWeekDayShort() {
	      return main_date.DateTimeFormat.format('D', this.eventInfo.dateFrom.getTime() / 1000).toLowerCase();
	    },

	    getEventDate() {
	      let dayPhrase = '';
	      const dateFormat = calendar_util.Util.getDayMonthFormat();
	      const today = new Date();
	      const eventDay = new Date(this.eventInfo.dateFrom.getFullYear(), this.eventInfo.dateFrom.getMonth(), this.eventInfo.dateFrom.getDate());

	      if (today.getTime() > eventDay.getTime() && today.getTime() < eventDay.getTime() + 86000000) {
	        dayPhrase = this.loc.today;
	      } else if (today.getTime() < eventDay.getTime() && today.getTime() > eventDay.getTime() - 86000000) {
	        dayPhrase = this.loc.tomorrow;
	      } else {
	        dayPhrase = main_date.DateTimeFormat.format('l', this.eventInfo.dateFrom.getTime() / 1000).toLowerCase();
	      }

	      return main_date.DateTimeFormat.format(dateFormat, this.eventInfo.dateFrom.getTime() / 1000) + ', ' + dayPhrase;
	    },

	    getEventTime() {
	      return calendar_util.Util.formatTimeInterval(this.eventInfo.dateFrom, this.eventInfo.dateTo);
	    },

	    downloadIcsFile() {
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:downloadIcsFile');
	    }

	  },
	  template: `
		<div class="calendar-shared-event__head">
			<div class="calendar-shared-event__icon" :class="{'--cancel': this.isDeclined && !this.isDeleted}">
				<div
					class="calendar-shared-event__icon_status" 
					:class="
					{
						'--approved': this.currentMeetingStatus === 'Y',
						'--cancel': this.currentMeetingStatus === 'N',
					}"
					v-if="!isDeleted"
				></div>
				<div class="calendar-shared-event__icon_text">{{ this.getEventWeekDayShort() }}</div>
				<div class="calendar-shared-event__icon_num">{{ this.eventInfo.dateFrom.getDate() }}</div>
			</div>
			<div class="calendar-shared-event__head_data">
				<div class="calendar-shared-event_title">{{ eventInfo.name }}</div>
				<div class="calendar-shared-event_start">{{ this.getEventDate() }}</div>
				<div class="calendar-shared-event_time-container">
					<div class="calendar-shared-event_end">{{ this.getEventTime() }}</div>
				</div>
				<div class="calendar-shared-event_timezone">{{ eventInfo.timezone }}</div>
				<div
					class="calendar-shared-event_owner-status"
					:class="
					{
						'--accepted': this.currentMeetingStatus === 'Y',
						'--declined': this.currentMeetingStatus === 'N',
					}"
					v-if="!isDeleted"
				>
					{{ ownerStatusText }}
				</div>
				<div
					class="calendar-shared-event_ics"
					@click="downloadIcsFile"
					v-if="!isDeclined && !isDeleted"
				>
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_ICS') }}
				</div>
				<div class="calendar-shared-event-host-info calendar-shared-event__list_item calendar-shared-event__list_item&#45;&#45;extranet" v-if="showHost">
					<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing"><i></i></div>
					<a :href="'/company/personal/user/' + eventInfo.hostId + '/'" target="_blank" class="calendar-shared-event__list_name">{{ eventInfo.hostName }}</a>
				</div>
			</div>
		</div>
	`
	};

	const Loader = {
	  template: `
		<div class="calendar-sharing-event__loader_box">
			<svg class="calendar-sharing-event_circular" viewBox="25 25 50 50">
				<circle class="calendar-sharing-event_path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
	`
	};

	let _ = t => t,
	    _t,
	    _t2,
	    _t3;
	const SharedEvent = {
	  props: {
	    link: Object,
	    event: Object,
	    owner: Object,
	    ownerMeetingStatus: String,
	    action: String
	  },
	  components: {
	    EventInfo,
	    Loader
	  },

	  data() {
	    return {
	      deleteButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_DELETE_BUTTON'),
	        disabled: false
	      },
	      conferenceButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_CONFERENCE'),
	        disabled: false
	      },
	      icsButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_ICS'),
	        disabled: false
	      },
	      returnButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_RETURN_BUTTON'),
	        disabled: false
	      },
	      acceptedMeetingStatuses: ['Q', 'Y', 'N'],
	      nowTimezoneOffset: new Date().getTimezoneOffset() * 60000,
	      nowTimeInUtc: new Date(new Date().getTime() + new Date().getTimezoneOffset() * 60000),
	      currentMeetingStatus: this.ownerMeetingStatus,
	      currentTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
	      isDeleted: false,
	      isDeclined: false,
	      conferenceError: false,
	      warningPopup: null,
	      loadingProcess: false,
	      eventInfo: {}
	    };
	  },

	  created() {
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:downloadIcsFile', () => {
	      this.downloadIcsFile();
	    });
	    this.eventInfo = {
	      dateFrom: calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(this.event.timestampFromUTC) * 1000, this.currentTimezone),
	      dateTo: calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(this.event.timestampToUTC) * 1000, this.currentTimezone),
	      timezone: calendar_util.Util.getFormattedTimezone(this.currentTimezone),
	      name: this.event.name
	    };

	    if (!this.acceptedMeetingStatuses.includes(this.currentMeetingStatus.toUpperCase())) {
	      this.currentMeetingStatus = 'Q';
	    }

	    if (this.currentMeetingStatus === 'N') {
	      this.isDeclined = true;
	    }

	    if (this.action === 'cancel') {
	      this.showConfirmPopup();
	    }

	    if (this.action === 'videoconference') {
	      this.handleConferenceButtonClick();
	    }

	    if (this.action === 'ics') {
	      this.downloadIcsFile();
	    }

	    this.setPageVisualSettings();
	  },

	  methods: {
	    async downloadIcsFile() {
	      if (!this.icsFile) {
	        const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
	          data: {
	            eventLinkHash: this.link.hash
	          }
	        });
	        this.icsFile = response.data;
	      }

	      calendar_util.Util.downloadIcsFile(this.icsFile, 'event');
	    },

	    enableButton(button) {
	      button.disabled = false;
	    },

	    disableButton(button) {
	      button.disabled = true;
	    },

	    setPageVisualSettings() {
	      const htmlNode = document.querySelector('html');
	      const bodyNode = document.querySelector('body');

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html');
	      }

	      if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body')) {
	        main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body');
	      }

	      if (this.isDeclined) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
	      } else {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-blue');
	      }

	      if (calendar_util.Util.isMobileBrowser()) {
	        if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile')) {
	          main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
	        }

	        if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile')) {
	          main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
	        }
	      }
	    },

	    showConfirmPopup() {
	      if (!this.warningPopup) {
	        this.warningPopup = new main_popup.Popup({
	          content: this.getConfirmPopupContent(),
	          width: 351,
	          lightShadow: true,
	          animation: 'fading-slide',
	          contentBackground: "#fff",
	          overlay: {
	            opacity: 50
	          },
	          zIndexAbsolute: 3100,
	          cacheable: false,
	          closeByEsc: true,
	          draggable: true,
	          borderRadius: '20px',
	          events: {
	            onPopupClose: this.closePopup.bind(this)
	          }
	        });
	      }

	      this.warningPopup.show();
	    },

	    getConfirmPopupContent() {
	      return main_core.Tag.render(_t || (_t = _`
				<div class="calendar-sharing-confirm-popup__container">
					<div class="calendar-sharing-confirm-popup__title">
						${0}
					</div>
					<div class="ui-btn-container ui-btn-container-center calendar-shared-event_btn-box">
						${0}
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_MESSAGE'), this.getConfirmSubmitButton(), this.getConfirmCancelButton());
	    },

	    getConfirmSubmitButton() {
	      if (!this.submitPopupButton) {
	        this.submitPopupButton = main_core.Tag.render(_t2 || (_t2 = _`
				<button class="ui-btn ui-btn-success ui-btn-round">
					${0}
				</button>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_SUBMIT_BUTTON'));
	        main_core.Event.bind(this.submitPopupButton, 'click', this.handleDeleteButtonClick);
	      }

	      return this.submitPopupButton;
	    },

	    getConfirmCancelButton() {
	      if (!this.cancelPopupButton) {
	        this.cancelPopupButton = main_core.Tag.render(_t3 || (_t3 = _`
				<button class="ui-btn ui-btn-light-border ui-btn-round">
					${0}
				</button>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_CANCEL_BUTTON'));
	        main_core.Event.bind(this.cancelPopupButton, 'click', this.closePopup);
	      }

	      return this.cancelPopupButton;
	    },

	    closePopup() {
	      if (this.warningPopup) {
	        this.warningPopup.destroy();
	        this.warningPopup = null;
	      }
	    },

	    async handleDeleteButtonClick() {
	      this.loadingProcess = true;

	      if (this.warningPopup) {
	        this.warningPopup.close();
	      }

	      this.disableButton(this.deleteButton);
	      const isSuccess = await this.deleteEvent();
	      this.enableButton(this.deleteButton);
	      this.loadingProcess = false;

	      if (isSuccess) {
	        const htmlNode = document.querySelector('html');
	        main_core.Dom.removeClass(htmlNode, 'calendar-sharing--bg-blue');
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
	        this.isDeleted = true;
	      }
	    },

	    async deleteEvent() {
	      let response = null;

	      try {
	        response = await BX.ajax.runAction('calendar.api.sharingajax.deleteEvent', {
	          data: {
	            eventId: this.event.id,
	            eventLinkHash: this.link.hash
	          }
	        });
	      } catch (e) {
	        response = e;
	      }

	      return response.errors.length === 0;
	    },

	    handleReturnButtonClick() {
	      this.disableButton(this.returnButton);

	      if (this.link.userLinkHash) {
	        const sharingPath = '/pub/calendar-sharing/';
	        window.location.href = document.location.origin + sharingPath + this.link.userLinkHash;
	      }

	      this.enableButton(this.returnButton);
	    },

	    async handleConferenceButtonClick() {
	      this.disableButton(this.conferenceButton);
	      const conferenceLink = await this.connectToConference();
	      this.enableButton(this.conferenceButton);

	      if (conferenceLink) {
	        window.location.href = conferenceLink;
	      } else {
	        this.conferenceError = true;
	      }
	    },

	    async connectToConference() {
	      var _response$data;

	      let response = null;

	      try {
	        response = await BX.ajax.runAction('calendar.api.sharingajax.getConferenceLink', {
	          data: {
	            eventLinkHash: this.link.hash
	          }
	        });
	      } catch (e) {
	        response = e;
	      }

	      return ((_response$data = response.data) == null ? void 0 : _response$data.conferenceLink) || null;
	    }

	  },
	  template: `
		<div class="calendar-shared-event-container" v-if="!isDeleted">
			<div>
				<EventInfo
					:event-info="eventInfo"
					:current-meeting-status="currentMeetingStatus"
					:is-declined="isDeclined"
					:is-deleted="isDeleted"
				/>
			</div>
			<div>
				<div class="calendar-shared-event__list">
					<div class="calendar-shared-event__list_inner">
						<div class="calendar-shared-event__list_invited">
							<div class="calendar-shared-event__list_title">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_YOU_INVITED') }}</div>
							<div class="calendar-shared-event__list_item">
								<div class="ui-icon ui-icon-common-user">
									<img class="calendar-shared-event__list_icon" :src="owner.photo" alt="" v-if="owner.photo">
									<i class="calendar-shared-event__list_icon" v-else></i>
								</div>
								<div class="calendar-shared-event__list_name">
									{{ owner.name }} {{ owner.lastName}}
								</div>
							</div>
						</div>
<!--						<div class="calendar-shared-event__list_members">-->
<!--							<div class="calendar-shared-event__list_title">More members</div>-->
<!--							<div class="calendar-shared-event__list_item calendar-shared-event__list_item&#45;&#45;email">-->
<!--								<div class="calendar-shared-event__list_icon"></div>-->
<!--								<div class="calendar-shared-event__list_name">Konstantin Myasnikov</div>-->
<!--							</div>-->
<!--							<div class="calendar-shared-event__list_item calendar-shared-event__list_item&#45;&#45;sms">-->
<!--								<div class="calendar-shared-event__list_icon"></div>-->
<!--								<div class="calendar-shared-event__list_name">Konstantin Myasnikov</div>-->
<!--							</div>-->
<!--						</div>-->
					</div>
				</div>
			</div>
			<div>
				<div class="ui-btn-container ui-btn-container-center calendar-shared-event_btn-box">
					<button
						class="ui-btn ui-btn-success ui-btn-round"
						@click="handleConferenceButtonClick"
						:disabled="conferenceButton.disabled"
						v-if="!isDeclined"
					>
						{{ conferenceButton.text }}
					</button>
					<button
						class="ui-btn ui-btn-light-border ui-btn-round"
						@click="showConfirmPopup"
						:disabled="deleteButton.disabled"
						v-if="!isDeclined"
					>
						{{ deleteButton.text }}
					</button>
					<button
						class="ui-btn ui-btn-success ui-btn-round"
						@click="handleReturnButtonClick"
						:disabled="returnButton.disabled"
						v-if="isDeclined"
					>
						{{ returnButton.text }}
					</button>
				</div>
			</div>
		</div>
		<div class="calendar-shared-event-container calendar-sharing--subtract calendar-sharing--error" v-else>
			<div class="calendar-shared-event_icon"></div>
			<div class="calendar-shared-event_deleted-title">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_IS_DELETED') }}</div>
			<EventInfo
				:event-info="eventInfo"
				:current-meeting-status="currentMeetingStatus"
				:is-declined="true"
				:is-deleted="isDeleted"
			/>
			<div class="calendar-shared-event__list_item">
				<div class="ui-icon ui-icon-common-user">
					<img class="calendar-shared-event__list_icon" :src="owner.photo" alt="" v-if="owner.photo">
					<i class="calendar-shared-event__list_icon" v-else></i>	
				</div>
				<div class="calendar-shared-event__list_name">
					{{ owner.name }} {{ owner.lastName}}
				</div>
			</div>
			<div class="ui-btn-container ui-btn-container-center calendar-shared-event_btn-box">
				<button
					class="ui-btn ui-btn-success ui-btn-round"
					@click="handleReturnButtonClick"
					:disabled="returnButton.disabled"
				>
					{{ returnButton.text }}
				</button>
			</div>
		</div>
		<Loader v-if="loadingProcess"/>
	`
	};

	const Application = {
	  components: {
	    SharedEvent
	  },
	  props: {
	    link: Object,
	    event: Object,
	    owner: Object,
	    ownerMeetingStatus: String,
	    action: String
	  },

	  created() {},

	  template: `
		<SharedEvent
			:link="link"
			:event="event"
			:owner="owner"
			:ownerMeetingStatus="ownerMeetingStatus"
			:action="action"
		/>
	`
	};

	class PublicEvent {
	  constructor(options) {
	    this.link = options.link;
	    this.event = options.event;
	    this.owner = options.owner;
	    this.ownerMeetingStatus = options.ownerMeetingStatus;
	    this.action = options.action;
	    this.rootNode = BX('calendar-sharing-event-main');
	    this.buildView();
	  }

	  buildView() {
	    this.application = ui_vue3.BitrixVue.createApp(Application, {
	      link: this.link,
	      event: this.event,
	      owner: this.owner,
	      ownerMeetingStatus: this.ownerMeetingStatus,
	      action: this.action
	    }).mount(this.rootNode);
	  }

	}

	exports.PublicEvent = PublicEvent;
	exports.EventInfo = EventInfo;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Vue3,BX.Main,BX,BX.Main,BX.Calendar));
//# sourceMappingURL=publicevent.bundle.js.map
