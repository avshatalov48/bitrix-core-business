this.BX = this.BX || {};
(function (exports,calendar_controls,calendar_planner,ui_vue3,calendar_util,calendar_entry,main_core,main_core_events,calendar_sectionmanager) {
	'use strict';

	const UserAvatar = {
	  name: 'UserAvatar',
	  props: {
	    avatarSize: Number,
	    user: Object
	  },
	  // language=Vue
	  template: `
		<div class="calendar-slider-sidebar-user-block-item">
			<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing" :style="'width:' + avatarSize + 'px'"
				 v-if="user.SHARING_USER">
				<i></i>
			</div>
			<div class="ui-icon ui-icon-common-user-mail" :style="'width:' + avatarSize + 'px'"
				 v-else-if="user.EMAIL_USER">
				<i></i>
			</div>
			<span v-else>
				<img :src="encodeURI(user.AVATAR)" :width="avatarSize" :height="avatarSize" v-if="user.AVATAR && user.AVATAR !== '/bitrix/images/1.gif'"/>
				<div class="ui-icon ui-icon-common-user" :style="'width:' + avatarSize + 'px'" v-else>
					<i></i>
				</div>
			</span>
		</div>
	`
	};

	const ViewEventSlider = {
	  name: 'ViewEventSlider',
	  props: ['params', 'reloadPlannerCallback', 'showUserListPopupCallback'],
	  components: {
	    UserAvatar
	  },
	  data() {
	    return {
	      id: this.params.id,
	      eventId: parseInt(this.params.eventId),
	      name: this.params.name,
	      description: this.params.description,
	      timezoneHint: this.params.timezoneHint,
	      timezone: this.params.userTimezone,
	      fromToHtml: this.params.fromToHtml,
	      isMeeting: this.params.isMeeting,
	      meetingHost: this.params.meetingHost,
	      meetingHostDisplayName: this.params.meetingHostDisplayName,
	      meetingHostWorkPosition: this.params.meetingHostWorkPosition,
	      avatarSize: this.params.avatarSize,
	      attendees: this.params.attendees,
	      avatarUsers: [],
	      avatarMoreUsers: [],
	      avatarMoreCount: 0,
	      userList: {
	        y: [],
	        i: [],
	        q: [],
	        n: []
	      },
	      curUserStatus: this.params.curUserStatus,
	      meetingCreatorUrl: this.params.meetingCreatorUrl,
	      meetingCreatorDisplayName: this.params.meetingCreatorDisplayName,
	      isRemind: this.params.isRemind,
	      isWebdavEvent: this.params.isWebdavEvent,
	      isCrmEvent: this.params.isCrmEvent,
	      isHighImportance: this.params.isHighImportance,
	      isRrule: this.params.isRrule,
	      rruleDescription: this.params.rruleDescription,
	      detailFilesTemplate: this.params.detailFilesTemplate,
	      accessibility: this.params.accessibility,
	      isIntranetEnabled: this.params.isIntranetEnabled,
	      isPrivate: this.params.isPrivate,
	      location: this.params.location,
	      canEditCalendar: this.params.canEditCalendar,
	      canDeleteEvent: this.params.canDeleteEvent,
	      showComments: this.params.showComments,
	      filesView: this.getComponentHTML(this.params.filesView),
	      crmView: this.getComponentHTML(this.params.crmView),
	      entry: new calendar_entry.Entry({
	        data: this.params.entry,
	        userIndex: this.params.userIndex
	      }),
	      isInvited: false,
	      updateParamsDebounce: main_core.Runtime.debounce(this.updateParams, 500, this),
	      hasPulls: false,
	      backgroundPullEvent: null
	    };
	  },
	  created() {
	    this.isInvited = this.entry.isInvited();
	  },
	  mounted() {
	    if (this.params.eventExists) {
	      this.updateUserList();
	      if (this.showComments) {
	        this.loadCommentsView();
	      }
	      if (this.isWebdavEvent) {
	        this.executeScripts(this.$refs.filesView);
	      }
	      main_core.Event.bind(document, 'visibilitychange', this.handleBackgroundPulls);
	    }
	    main_core.Event.bind(this.$refs.sliderDetailContent, 'mouseup', this.quote);
	    main_core_events.EventEmitter.subscribe('AI.Copilot.Menu:open', this.handleCopilotMenuShow);
	  },
	  beforeMount() {
	    if (this.params.eventExists) {
	      main_core_events.EventEmitter.subscribe('onPullEvent-calendar', this.handlePullEvent);
	      main_core_events.EventEmitter.subscribe(`MeetingStatusControl_${this.id}:onSetStatus`, this.handleStatusUpdate);
	    }
	  },
	  beforeUnmount() {
	    if (this.params.eventExists) {
	      main_core_events.EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullEvent);
	      main_core_events.EventEmitter.unsubscribe(`MeetingStatusControl_${this.id}:onSetStatus`, this.handleStatusUpdate);
	      main_core.Event.unbind(this.$refs.sliderDetailContent, 'mouseup', this.quote);
	      main_core_events.EventEmitter.unsubscribe('AI.Copilot.Menu:open', this.handleCopilotMenuShow);
	    }
	  },
	  computed: {
	    authorNodeId() {
	      return this.id + '_detail-author-info';
	    }
	  },
	  methods: {
	    getComponentHTML(json) {
	      if (!json) {
	        return '';
	      }
	      return JSON.parse(json).data.html;
	    },
	    loadCommentsView() {
	      BX.ajax.runAction('calendar.api.calendareventviewform.getCommentsView', {
	        data: {
	          signedEvent: this.params.signedEvent
	        }
	      }).then(response => {
	        const commentsElement = document.createElement('div');
	        commentsElement.innerHTML = response.data.html;
	        this.$refs.commentsView.appendChild(commentsElement);
	        this.executeScripts(this.$refs.commentsView);
	      });
	    },
	    executeScripts(element) {
	      if (!element) {
	        return;
	      }
	      //run scripts
	      const scripts = element.querySelectorAll('script');
	      for (const script of scripts) {
	        const s = document.createElement('script');
	        s.innerHTML = script.innerHTML;
	        script.parentNode.appendChild(s);
	        script.remove();
	      }
	      //remove script elements
	      // element.querySelectorAll('script').forEach(e => e.remove());
	    },

	    quote(e) {
	      window.mplCheckForQuote(e, e.currentTarget, 'EVENT_' + this.eventId, this.authorNodeId);
	    },
	    updateUserList() {
	      var _this$attendees$y$fil, _this$attendees$y;
	      this.userList = {
	        y: [],
	        i: [],
	        q: [],
	        n: []
	      };
	      if (this.entry.isMeeting()) {
	        this.entry.getAttendees().forEach(function (user) {
	          if (user.STATUS === 'H') {
	            this.userList.y.push(user);
	          } else if (this.userList[user.STATUS.toLowerCase()]) {
	            this.userList[user.STATUS.toLowerCase()].push(user);
	          }
	        }, this);
	      }
	      const accepted = (_this$attendees$y$fil = (_this$attendees$y = this.attendees.y) == null ? void 0 : _this$attendees$y.filter(a => parseInt(this.meetingHost.ID) !== parseInt(a.ID))) != null ? _this$attendees$y$fil : [];
	      if (accepted.length <= 11) {
	        this.avatarUsers = accepted.slice(0, 11);
	        this.avatarMoreUsers = [];
	      } else {
	        this.avatarUsers = accepted.slice(0, 10);
	        this.avatarMoreUsers = accepted.slice(10);
	      }
	      this.avatarMoreCount = this.avatarMoreUsers.length;
	      if (this.avatarMoreCount >= 1000) {
	        this.avatarMoreCount = `${parseInt(this.avatarMoreUsers.length / 1000)}K`;
	      }
	    },
	    reloadPlanner() {
	      const plannerData = {
	        entryId: this.entry.id || 0,
	        entryLocation: this.entry.data.LOCATION || '',
	        ownerId: 0,
	        hostId: this.entry.getMeetingHost(),
	        type: 'user',
	        entityList: this.entry.getAttendeesEntityList(),
	        dateFrom: calendar_util.Util.formatDate(this.entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        dateTo: calendar_util.Util.formatDate(this.entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: this.timezone,
	        location: this.entry.getLocation(),
	        entry: this.entry
	      };
	      this.reloadPlannerCallback(plannerData);
	    },
	    handleBackgroundPulls() {
	      if (this.hasPulls) {
	        this.updateParamsDebounce(this.backgroundPullEvent);
	        this.hasPulls = false;
	      }
	    },
	    handleStatusUpdate(event) {
	      this.entry.data.MEETING_STATUS = event.getData().status;
	      this.isInvited = this.entry.isInvited();
	    },
	    handlePullEvent(event) {
	      if (event.data[0] === "refresh_sync_status") {
	        return;
	      }
	      if (event.data[1].fields.CAL_TYPE === 'location') {
	        return;
	      }

	      // debounce all pull events except location
	      // update only when page is active
	      if (BX.Calendar.Util.documentIsDisplayingNow()) {
	        this.updateParamsDebounce(event);
	      } else {
	        this.hasPulls = true;
	        this.backgroundPullEvent = event;
	      }
	    },
	    updateParams(event) {
	      if (parseInt(event.data[1].fields.PARENT_ID) !== parseInt(this.params.parentId)) {
	        this.reloadPlanner();
	        return;
	      }
	      const pullData = event.data[1].fields;
	      this.name = pullData.NAME;
	      this.accessibility = pullData.ACCESSIBILITY;
	      BX.ajax.runAction('calendar.api.calendareventviewform.getCalendarViewSliderParams', {
	        data: {
	          entryId: this.eventId,
	          dateFrom: calendar_util.Util.formatDate(pullData.DATE_FROM),
	          timezoneOffset: pullData.TZ_OFFSET_FROM
	        }
	      }).then(response => {
	        const newData = response.data;
	        this.description = newData.description;
	        this.isMeeting = newData.isMeeting;
	        this.timezone = newData.userTimezone;
	        this.timezoneHint = newData.timezoneHint;
	        this.fromToHtml = newData.fromToHtml;
	        this.meetingHost = newData.meetingHost;
	        this.meetingHostDisplayName = newData.meetingHostDisplayName;
	        this.meetingHostWorkPosition = newData.meetingHostWorkPosition;
	        this.avatarSize = newData.avatarSize;
	        this.attendees = newData.attendees;
	        this.meetingCreatorUrl = newData.meetingCreatorUrl;
	        this.meetingCreatorDisplayName = newData.meetingCreatorDisplayName;
	        this.isRemind = newData.isRemind;
	        this.isWebdavEvent = newData.isWebdavEvent;
	        this.isCrmEvent = newData.isCrmEvent;
	        this.isHighImportance = newData.isHighImportance;
	        this.isRrule = newData.isRrule;
	        this.rruleDescription = newData.rruleDescription;
	        this.detailFilesTemplate = newData.detailFilesTemplate;
	        this.isIntranetEnabled = newData.isIntranetEnabled;
	        this.isPrivate = newData.isPrivate;
	        this.location = newData.location;
	        this.canEditCalendar = newData.canEditCalendar;
	        this.canDeleteEvent = newData.canDeleteEvent;
	        this.showComments = newData.showComments;
	        this.filesView = this.getComponentHTML(newData.filesView);
	        if (this.filesView) {
	          //wait for div element created
	          setTimeout(() => {
	            this.executeScripts(this.$refs.filesView);
	          }, 1000);
	        }
	        this.crmView = this.getComponentHTML(newData.crmView);
	        this.entry = new calendar_entry.Entry({
	          data: newData.entry,
	          userIndex: newData.userIndex
	        });
	        this.updateUserList();
	        this.reloadPlanner();
	      });
	    },
	    handleCopilotMenuShow() {
	      const copilotPopups = [...document.querySelectorAll('.ai__copilot-menu-popup')];
	      const menu = copilotPopups.find(popup => popup.offsetHeight > 0);
	      const offset = menu.getBoundingClientRect().bottom - this.$refs.comments.getBoundingClientRect().bottom;
	      const marginBottom = parseInt(this.$refs.comments.style.marginBottom);
	      if (isNaN(marginBottom) && offset > 0 || !isNaN(marginBottom) && marginBottom < offset) {
	        this.$refs.comments.style.marginBottom = `${offset}px`;
	      }
	    },
	    highlightChange(element) {
	      if (!element) {
	        return;
	      }
	      const savedOpacity = element.style.opacity;
	      const savedTransition = element.style.transition;
	      element.style.opacity = '0.2';
	      setTimeout(() => {
	        element.style.transition = '1s';
	        element.style.opacity = savedOpacity;
	        setTimeout(() => {
	          element.style.transition = savedTransition;
	        }, 1000);
	      }, 100);
	    }
	  },
	  watch: {
	    name: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightName);
	      }
	    },
	    fromToHtml: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightFromTo);
	      }
	    },
	    description: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightDescription);
	      }
	    },
	    crmView: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightCrmView);
	      }
	    },
	    location: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightLocation);
	      }
	    },
	    accessibility: {
	      handler(newValue, oldValue) {
	        this.highlightChange(this.$refs.highlightAccessibility);
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div class="ui-alert ui-alert-danger ui-alert-icon-danger ui-alert-text-center" v-if="!params.eventExists">
			<span class="ui-alert-message">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_EVENT_NOT_FOUND')}}</span>
		</div>
		<div class="calendar-slider-calendar-wrap" v-else>
			<div class="calendar-slider-header">
				<div class="calendar-head-area">
					<div class="calendar-head-area-inner">
						<div class="calendar-head-area-title">
							<span class="calendar-event-invite-counter calendar-event-invite-counter-big" v-if="isInvited">1</span>
							<span :id="id + '_title'" class="calendar-head-area-title-name" ref="highlightName">{{name}}</span>
							<span :id="id + '_copy_url_btn'" class="calendar-page-link-btn" :title="$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_COPY_LINK')"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="calendar-slider-workarea">
				<div class="calendar-slider-sidebar">
					<div :id="id + '_time_wrap'" class="calendar-slider-sidebar-head" :title="timezoneHint" ref="highlightFromTo">
						<div :id="id + '_time_inner_wrap'" class="calendar-slider-sidebar-head-title">{{fromToHtml}}
							<div class="calendar-slider-sidebar-head-timezone" :title="timezoneHint" v-if="timezoneHint">
								<div class="calendar-slider-sidebar-head-timezone-icon"></div>
							</div>
						</div>
					</div>
					<div :id="id + '_sidebar_inner'" class="calendar-slider-sidebar-inner">
						<div class="calendar-slider-sidebar-videocall" style="display: none;"></div>
						<div class="calendar-slider-sidebar-layout calendar-slider-sidebar-user">
							<div class="calendar-slider-sidebar-layout-top calendar-slider-sidebar-user-top calendar-slider-sidebar-border-bottom">
								<div class="calendar-slider-sidebar-left-side">
									<div class="calendar-slider-sidebar-name">
										<span v-if="isMeeting">{{$Bitrix.Loc.getMessage('EC_VIEW_ATTENDEES_TITLE')}}</span>
										<span v-else>{{$Bitrix.Loc.getMessage('EC_VIEW_HOST')}}</span>
									</div>
								</div>
								<div class="calendar-slider-sidebar-right-side" :id="id + '_add_link'" style="display: none;">
									<div class="calendar-slider-sidebar-property calendar-slider-sidebar-link-user">
										{{$Bitrix.Loc.getMessage('EC_VIEW_ATTENDEES_ADD')}}
									</div>
								</div>
							</div>
							<div class="calendar-slider-sidebar-layout-main">
								<div class="calendar-slider-sidebar-user-block">
								<div v-if="isMeeting">
									<div class="calendar-slider-sidebar-user-container-holder">
										<div class="calendar-slider-sidebar-user-container">
											<div class="calendar-slider-sidebar-user-block-avatar">
												<a :href="meetingHost.URL">
													<UserAvatar :user="meetingHost" :avatarSize="avatarSize"/>
													<div class="calendar-slider-sidebar-user-icon-top"></div>
													<div class="calendar-slider-sidebar-user-icon-bottom"></div>
												</a>
											</div>
										</div>
										<div
											class="calendar-slider-sidebar-user-container"
											v-for="att in avatarUsers"
										>
											<div class="calendar-slider-sidebar-user-block-avatar">
												<a :href="att.URL">
													<UserAvatar :user="att" :avatarSize="avatarSize"/>
													<div class="calendar-slider-sidebar-user-icon-bottom"></div>
												</a>
											</div>
										</div>
										<div
											v-if="avatarMoreUsers.length > 0"
											class="calendar-slider-sidebar-user-more-container" ref="attendeesMore"
											@click="showUserListPopupCallback($refs.attendeesMore, avatarMoreUsers)"
										>
											<div class="calendar-slider-sidebar-user-more">
												+{{avatarMoreCount}}
											</div>
										</div>
									</div>
									<div class="calendar-slider-sidebar-row calendar-slider-sidebar-border-bottom" v-if="meetingCreatorUrl">
										<div class="calendar-slider-sidebar-string-name">{{$Bitrix.Loc.getMessage('EC_VIEW_CREATED_BY')}}:</div>
										<div class="calendar-slider-sidebar-string-value">
											<a :href="meetingCreatorUrl" class="calendar-slider-sidebar-user-info-name">{{meetingCreatorDisplayName}}</a>
										</div>
									</div>
								</div>
								<div class="calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card" v-else>
									<div class="calendar-slider-sidebar-user-block-avatar">
										<a :href="meetingHost.URL">
											<UserAvatar :user="meetingHost" :avatarSize="avatarSize"/>
										</a>
										<div class="calendar-slider-sidebar-user-icon-bottom"></div>
									</div>
									<div class="calendar-slider-sidebar-user-info">
										<a :href="meetingHost.URL" class="calendar-slider-sidebar-user-info-name">{{meetingHostDisplayName}}</a>
										<div class="calendar-slider-sidebar-user-info-status" v-if="meetingHostWorkPosition">{{meetingHostWorkPosition}}</div>
									</div>
								</div>
								
							</div>
								<div class="calendar-slider-sidebar-user-social calendar-slider-sidebar-border-bottom" v-if="isMeeting">
								<div class="calendar-slider-sidebar-user-social-left">
									<div class="calendar-slider-sidebar-user-social-item" ref="attendeesY"
										 @click="showUserListPopupCallback($refs.attendeesY, userList.y)">
										<span class="calendar-slider-sidebar-user-social-number">
											{{attendees.y.length}}
										</span>
										<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
											{{$Bitrix.Loc.getMessage('EC_VIEW_STATUS_TITLE_Y')}}
										</span>
									</div>
									<div class="calendar-slider-sidebar-user-social-item" ref="attendeesQ"
										 @click="showUserListPopupCallback($refs.attendeesQ, userList.q)">
										<span class="calendar-slider-sidebar-user-social-number">
											{{attendees.q.length}}
										</span>
										<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
											{{$Bitrix.Loc.getMessage('EC_VIEW_STATUS_TITLE_Q')}}
										</span>
									</div>
								</div>
								<div class="calendar-slider-sidebar-user-social-right">
									<div class="calendar-slider-sidebar-user-social-item" style="visibility: hidden;" ref="attendeesI"
										 @click="showUserListPopupCallback($refs.attendeesI, userList.i)">
										<span class="calendar-slider-sidebar-user-social-number">
											{{attendees.i.length}}
										</span>
										<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
											{{$Bitrix.Loc.getMessage('EC_VIEW_STATUS_TITLE_I')}}
										</span>
									</div>
									<div class="calendar-slider-sidebar-user-social-item" ref="attendeesN"
										 @click="showUserListPopupCallback($refs.attendeesN, userList.n)">
										<span class="calendar-slider-sidebar-user-social-number">
											{{attendees.n.length}}
										</span>
										<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
											{{$Bitrix.Loc.getMessage('EC_VIEW_STATUS_TITLE_N')}}
										</span>
									</div>
								</div>
							</div>
								
							</div>
						</div>
						<div class="calendar-slider-sidebar-layout-main calendar-slider-sidebar-border-bottom calendar-slider-sidebar-remind" v-if="isRemind">
							<div class="calendar-slider-sidebar-row">
								<div class="calendar-slider-sidebar-string-name">{{$Bitrix.Loc.getMessage('EC_VIEW_REMINDERS')}}:</div>
								<span class="calendar-slider-sidebar-remind-link calendar-slider-sidebar-string-value" :id="id + '_add_reminder_link'" style="display: none;">
									<span class="calendar-slider-sidebar-remind-link-name">{{$Bitrix.Loc.getMessage('EC_VIEW_REMINDER_ADD')}}</span>
								</span>
							</div>
							<div class="calendar-slider-sidebar-remind-wrap"></div>
						</div>
						<div class="calendar-slider-sidebar-row calendar-slider-sidebar-border-bottom" v-if="isRrule">
							<div class="calendar-slider-sidebar-string-name">{{$Bitrix.Loc.getMessage('EC_T_REPEAT')}}:</div>
							<div class="calendar-slider-sidebar-string-value">{{rruleDescription}}</div>
						</div>
						
					</div>
					<div class="calendar-slider-sidebar-copy" style="display: none;">
						<span class="calendar-slider-sidebar-copy-link">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_COPY_LINK')}}</span>
					</div>
				</div>
				<div class="calendar-slider-content">
					<div class="calendar-slider-detail calendar-slider-detail-panel">
						<div class="calendar-slider-detail-info">
							<div class="calendar-slider-detail-header">
								<div id="calendar-slider-detail-important-button" class="calendar-slider-info-panel-important mutable" v-if="isHighImportance">
									<span class="if-not-no">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_IMPORTANT_EVENT')}}</span>
								</div>
								<div class="calendar-slider-detail-subtitle-status" style="visibility: hidden">
									#calendar-slider-detail-subtitle-status#
									<span class="calendar-slider-detail-status-below-name"></span>
								</div>
							</div>

							<div class="calendar-slider-detail-content" ref="sliderDetailContent">
								<span
									class="calendar-detail-author-info"
									:id="authorNodeId"
									:bx-post-author-id="meetingHost.ID"
								>
									{{meetingCreatorDisplayName ?? meetingHostDisplayName}}
								</span>
								<div id="calendar-slider-detail-description" class="calendar-slider-detail-description" v-if="description"
									 ref="highlightDescription" v-html="description">
								</div>

								<div class="calendar-slider-detail-files" :id="id + '_' + eventId + '_files_wrap'" v-if="isWebdavEvent">
									<div v-html="filesView" ref="filesView"></div>
								</div>

								<div class="calendar-slider-detail-timeline hidden" :id="id + '_view_planner_wrap'">
									<div class="calendar-view-planner-wrap"></div>
								</div>

								<div class="calendar-slider-detail-option">
									<div class="calendar-slider-detail-option-block" v-if="isCrmEvent" ref="highlightCrmView">
										<div class="calendar-slider-detail-option-name">{{$Bitrix.Loc.getMessage('EC_CRM_TITLE')}}:</div>
										<div class="calendar-slider-detail-option-value calendar-slider-detail-option-crm">
											<div v-html="crmView"></div>
										</div>
									</div>

									<div class="calendar-slider-detail-option-block" v-if="accessibility && isIntranetEnabled" ref="highlightAccessibility">
										<div class="calendar-slider-detail-option-name">{{$Bitrix.Loc.getMessage('EC_ACCESSIBILITY_TITLE')}}:</div>
										<div class="calendar-slider-detail-option-value">{{$Bitrix.Loc.getMessage('EC_ACCESSIBILITY_' + accessibility.toUpperCase())}}</div>
									</div>
									
									<div class="calendar-slider-detail-option-block" v-if="isPrivate && isIntranetEnabled">
										<div class="calendar-slider-detail-option-name">{{$Bitrix.Loc.getMessage('EC_EDDIV_SPECIAL_NOTES')}}:</div>
										<div class="calendar-slider-detail-option-value">{{$Bitrix.Loc.getMessage('EC_PRIVATE_EVENT')}}</div>
									</div>
								</div>

								<div class="calendar-slider-detail-place" v-if="location" ref="highlightLocation">
									<div class="calendar-slider-detail-place-title">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_LOCATION')}}</div>
									<div class="calendar-slider-detail-place-name">{{location}}</div>
								</div>
							</div>

							<div class="calendar-slider-detail-buttons">
								<div class="calendar-slider-view-buttonset calendar-slider-view-button-more-right">
									<div :id="id + '_buttonset'" class="calendar-slider-view-buttonset-inner">
										<input type="hidden" :id="id + '_current_status'" :value="curUserStatus"/>
										<span :id="id + '_status_buttonset'"></span>

										<div>
											<button v-if="canEditCalendar" :id="id + '_but_edit'" class="ui-btn ui-btn-light-border">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_EDIT')}}</button>
											<button v-if="canDeleteEvent" :id="id + '_but_del'" class="ui-btn ui-btn-light-border">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_DEL')}}</button>
										</div>
										
									</div>
								</div>
							</div>
						</div>
						
						<div class="calendar-slider-comments" v-if="showComments" ref="comments">
							<div class="calendar-slider-comments-title">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_COMMENTS')}}</div>
							<div class="calendar-slider-comments-main" :id="id + 'comments-cont'" style="opacity: 1;">
								<div ref="commentsView"></div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	`
	};

	let _ = t => t,
	  _t,
	  _t2;
	class EventViewForm {
	  constructor(options = {}) {
	    this.permissions = {};
	    this.name = 'eventviewform';
	    this.uid = null;
	    this.DOM = {};
	    this.RELOAD_REQUESTED = 'RELOAD_REQUESTED';
	    this.RELOAD_FINISHED = 'RELOAD_FINISHED';
	    this.reloadStatus = null;
	    this.entityChanged = false;
	    this.LOAD_DELAY = 500;
	    this.app = null;
	    this.type = options.type || 'user';
	    this.attendees = [];
	    this.ownerId = options.ownerId || 0;
	    this.userId = options.userId || 0;
	    this.zIndex = 3100;
	    this.entryId = options.entryId || null;
	    this.calendarContext = options.calendarContext || null;
	    this.entryDateFrom = options.entryDateFrom || null;
	    this.timezoneOffset = options.timezoneOffset || null;
	    this.BX = calendar_util.Util.getBX();
	    this.sliderOnLoad = this.onLoadSlider.bind(this);
	    this.showUserListPopupBind = this.showUserListPopup.bind(this);
	    this.keyHandlerBind = this.keyHandler.bind(this);
	    this.destroyBind = this.destroy.bind(this);
	    this.loadPlannerDataDebounce = main_core.Runtime.debounce(this.loadPlannerData, this.LOAD_DELAY, this);
	    this.pullEventList = new Set();
	  }
	  initInSlider(slider, promiseResolve) {
	    this.slider = slider;
	    main_core_events.EventEmitter.subscribe(slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	    main_core_events.EventEmitter.subscribe(slider, "SidePanel.Slider:onCloseComplete", this.destroyBind);
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBind);
	    this.createContent(slider).then(function (html) {
	      if (main_core.Type.isFunction(promiseResolve)) {
	        promiseResolve(html);
	      }
	    }.bind(this));
	    this.opened = true;
	  }
	  isOpened() {
	    return this.opened;
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	    main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onCloseComplete", this.destroyBind);
	    main_core.Event.unbind(document, 'keydown', this.keyHandlerBind);
	    if (this.app) {
	      this.app.unmount();
	    }
	    if (this.intranetControllButton && this.intranetControllButton.destroy) {
	      this.intranetControllButton.destroy();
	    }
	    calendar_util.Util.closeAllPopups();
	    this.opened = false;
	  }
	  onLoadSlider(event) {
	    var _data$;
	    if (!event instanceof main_core_events.BaseEvent) {
	      return;
	    }
	    const data = event.getData();
	    const slider = (_data$ = data[0]) == null ? void 0 : _data$.slider;
	    this.DOM.content = slider.layout.content;

	    // Used to execute javasctipt and attach CSS from ajax responce
	    this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));
	    if (!main_core.Type.isNull(this.uid)) {
	      this.initControls(this.uid);
	    }
	    this.reloadStatus = this.RELOAD_FINISHED;
	  }
	  loadComponentAssets(json) {
	    if (!json) {
	      return;
	    }
	    let assets = JSON.parse(json).data.assets;
	    let promise = new Promise(function (resolve, reject) {
	      let css = assets.css;
	      BX.load(css, function () {
	        BX.loadScript(assets.js, resolve);
	      });
	    });
	    promise.then(function () {
	      let strings = assets.string;
	      let stringAsset = strings.join('\n');
	      BX.html(document.head, stringAsset, {
	        useAdjacentHTML: true
	      });
	    });
	  }
	  createContent(slider) {
	    return new Promise(resolve => {
	      this.BX.ajax.runAction('calendar.api.calendareventviewform.getCalendarViewSliderParams', {
	        analyticsLabel: {
	          calendarAction: 'view_event',
	          formType: 'full'
	        },
	        data: {
	          entryId: this.entryId,
	          dateFrom: calendar_util.Util.formatDate(this.entryDateFrom),
	          timezoneOffset: this.timezoneOffset
	        }
	      }).then(response => {
	        const viewEventSliderRoot = document.createElement('div');
	        if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	          let params = response.data;
	          params.eventExists = !!params.entry.ID;
	          this.attendees = [];
	          for (const status in params.attendees) {
	            this.attendees.push(...params.attendees[status]);
	          }

	          //load components' css and js
	          if (params.filesView) {
	            this.loadComponentAssets(params.filesView);
	          }
	          if (params.crmView) {
	            this.loadComponentAssets(params.crmView);
	            this.BX.ajax.runAction('calendar.api.calendareventviewform.getCrmView', {
	              data: {
	                signedEvent: params.signedEvent
	              }
	            });
	          }

	          //set vue component to slider
	          this.app = ui_vue3.BitrixVue.createApp(ViewEventSlider, {
	            params: params,
	            reloadPlannerCallback: this.loadPlannerDataDebounce,
	            showUserListPopupCallback: this.showUserListPopupBind
	          });
	          this.app.mount(viewEventSliderRoot);
	          slider.sliderContent = viewEventSliderRoot;

	          //set local params
	          this.userId = params.userId;
	          this.uid = params.id;
	          this.entryUrl = params.entryUrl;
	          this.userTimezone = params.userTimezone;
	          this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat;
	          this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;
	          if (this.planner && !this.plannerFeatureEnabled) {
	            this.planner.lock();
	          }
	          this.handleEntryData(params.entry, params.userIndex, params.section);
	        }
	        resolve(viewEventSliderRoot);
	      }, response => {
	        if (response.errors && response.errors.length) {
	          slider.getData().set("sliderContent", '<div class="calendar-slider-alert">' + '<div class="calendar-slider-alert-inner">' + '<div class="calendar-slider-alert-img"></div>' + '<h1 class="calendar-slider-alert-text">' + main_core.Text.encode(response.errors[0].message) + '</h1>' + '</div>' + '</div>');
	        }
	        if (response.data && !main_core.Type.isNil(response.data.isAvailable) && !response.data.isAvailable) {
	          const showHelperCallback = () => {
	            top.BX.UI.InfoHelper.show('limit_office_calendar_off', {
	              isLimit: true,
	              limitAnalyticsLabels: {
	                module: 'calendar',
	                source: 'eventViewForm'
	              }
	            });
	          };
	          if (this.slider) {
	            this.slider.close(true, showHelperCallback);
	          } else {
	            showHelperCallback();
	          }
	        }
	        this.displayError(response.errors);
	        resolve(response);
	      });
	    });
	  }
	  initControls(uid) {
	    var _BX, _BX$Intranet;
	    this.DOM.title = this.DOM.content.querySelector(`#${uid}_title`);
	    this.DOM.buttonSet = this.DOM.content.querySelector(`#${uid}_buttonset`);
	    this.DOM.editButton = this.DOM.content.querySelector(`#${uid}_but_edit`);
	    this.DOM.delButton = this.DOM.content.querySelector(`#${uid}_but_del`);
	    this.DOM.sidebarInner = this.DOM.content.querySelector(`#${uid}_sidebar_inner`);
	    if (this.DOM.buttonSet) {
	      this.initPlannerControl(uid);
	    }
	    const innerTimeWrap = this.DOM.content.querySelector(`#${uid}_time_inner_wrap`);
	    if (main_core.Type.isElementNode(innerTimeWrap) && innerTimeWrap.offsetHeight > 50) {
	      main_core.Dom.addClass(this.DOM.content.querySelector(`#${uid}_time_wrap`), 'calendar-slider-sidebar-head-long-time');
	    }
	    if (this.canDo(this.entry, 'edit') && this.DOM.editButton) {
	      main_core.Event.bind(this.DOM.editButton, 'click', () => {
	        this.BX.SidePanel.Instance.close(false, function () {
	          calendar_entry.EntryManager.openEditSlider({
	            entry: this.entry,
	            type: this.type,
	            ownerId: this.ownerId,
	            userId: this.userId
	          });
	        }.bind(this));
	      });
	    } else {
	      this.BX.remove(this.DOM.editButton);
	    }
	    if (this.DOM.sidebarInner) {
	      // Reminder
	      this.DOM.reminderWrap = this.DOM.sidebarInner.querySelector('.calendar-slider-sidebar-remind-wrap');
	      if (main_core.Type.isDomNode(this.DOM.reminderWrap)) {
	        main_core.Dom.clean(this.DOM.reminderWrap);
	        let viewMode = !this.canDo(this.entry, 'edit') && this.entry.getCurrentStatus() === false;
	        this.reminderControl = new this.BX.Calendar.Controls.Reminder({
	          wrap: this.DOM.reminderWrap,
	          zIndex: this.zIndex,
	          viewMode: viewMode
	        });
	        this.reminderControl.setValue(this.entry.getReminders());
	        if (!viewMode) {
	          this.reminderControl.subscribe('onChange', event => {
	            if (event instanceof main_core_events.BaseEvent) {
	              this.handleEntityChanges();
	              this.reminderValues = event.getData().values;
	              this.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
	                data: {
	                  entryId: this.entry.id,
	                  userId: this.userId,
	                  reminders: this.reminderValues
	                }
	              });
	            }
	          });
	        }
	      }
	      let items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');
	      if (items.length >= 2) {
	        this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
	      }
	    }
	    if (this.canDo(this.entry, 'delete')) {
	      main_core.Event.bind(this.DOM.delButton, 'click', () => {
	        main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', () => {
	          this.BX.SidePanel.Instance.close();
	        });
	        calendar_entry.EntryManager.deleteEntry(this.entry, this.calendarContext);
	      });
	    } else {
	      this.BX.remove(this.DOM.delButton);
	    }
	    const filesWrap = uid + '_' + this.entry.id + '_files_wrap';
	    if (filesWrap) {
	      const currentTop = typeof window.top.BX.viewElementBind === 'function' ? window.top.BX : window.BX;
	      currentTop.viewElementBind(filesWrap, {
	        showTitle: true
	      }, function (node) {
	        return main_core.Type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
	      });
	    }
	    if (this.entry && this.entry.isMeeting()) {
	      this.initAcceptMeetingControl(uid);
	    }
	    if (this.DOM.sidebarInner) {
	      let items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');
	      if (items.length >= 2) {
	        this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
	      }
	    }
	    this.DOM.copyButton = this.DOM.content.querySelector(`#${uid}_copy_url_btn`);
	    if (this.DOM.copyButton) {
	      main_core.Event.bind(this.DOM.copyButton, 'click', this.copyEventUrl.bind(this));
	    }

	    // Init "Videocall" control
	    this.DOM.videoCall = this.DOM.sidebarInner.querySelector('.calendar-slider-sidebar-videocall');
	    main_core.Dom.clean(this.DOM.videoCall);
	    if (main_core.Type.isElementNode(this.DOM.videoCall) && this.entry && this.entry.data['PARENT_ID'] && (this.entry.data['EVENT_TYPE'] === '#shared#' || this.entry.data['EVENT_TYPE'] === '#shared_crm#')) {
	      this.DOM.videoCall.style.display = '';
	      this.conferenceButton = main_core.Tag.render(_t || (_t = _`
				<div class="ui-btn-split ui-btn-icon-camera-blue intranet-control-btn ui-btn-light-border ui-btn-icon-inline" style="width: 100%">
					<button class="ui-btn-main calendar-slider-conference-button">
						${0}
					</button>
				</div>
			`), main_core.Loc.getMessage('EC_CALENDAR_CONFERENCE'));
	      main_core.Event.bind(this.conferenceButton, 'click', this.handleConferenceButtonClick.bind(this));
	      main_core.Dom.append(this.conferenceButton, this.DOM.videoCall);
	    } else if ((_BX = BX) != null && (_BX$Intranet = _BX.Intranet) != null && _BX$Intranet.ControlButton && main_core.Type.isElementNode(this.DOM.videoCall) && this.entry.getCurrentStatus() !== false) {
	      this.DOM.videoCall.style.display = '';
	      this.intranetControllButton = new calendar_controls.IntranetButton({
	        intranetControlButtonParams: {
	          container: this.DOM.videoCall,
	          entityType: 'calendar_event',
	          entityId: this.entry.parentId,
	          entityData: {
	            dateFrom: calendar_util.Util.formatDate(this.entry.from),
	            parentId: this.entry.parentId
	          },
	          analyticsLabel: {
	            formType: 'full'
	          }
	        },
	        callbacks: {
	          getUsersCount: () => this.attendees.length,
	          hasChat: () => {
	            var _this$entry$data, _this$entry$data$MEET;
	            return ((_this$entry$data = this.entry.data) == null ? void 0 : (_this$entry$data$MEET = _this$entry$data.MEETING) == null ? void 0 : _this$entry$data$MEET.CHAT_ID) > 0;
	          }
	        }
	      });
	    } else {
	      this.DOM.videoCall.style.display = 'none';
	    }
	  }
	  handleEntryData(entryData, userIndex, sectionData) {
	    this.entry = new calendar_entry.Entry({
	      data: entryData,
	      userIndex: userIndex
	    });
	    this.section = new calendar_sectionmanager.CalendarSection(sectionData);
	    if (main_core.Type.isPlainObject(sectionData)) {
	      this.permissions = sectionData.PERM;
	    }
	    calendar_entry.EntryManager.registerEntrySlider(this.entry, this);
	  }
	  initPlannerControl(uid) {
	    this.plannerId = uid + '_view_slider_planner';
	    this.DOM.plannerWrapOuter = this.DOM.content.querySelector(`.calendar-slider-detail-timeline`);
	    this.DOM.plannerWrap = this.DOM.plannerWrapOuter.querySelector(`.calendar-view-planner-wrap`);
	    main_core.Dom.clean(this.DOM.plannerWrap);
	    this.planner = new calendar_planner.Planner({
	      wrap: this.DOM.plannerWrap,
	      minWidth: parseInt(this.DOM.plannerWrap.offsetWidth),
	      solidStatus: true,
	      readonly: true,
	      locked: !this.plannerFeatureEnabled,
	      dayOfWeekMonthFormat: this.dayOfWeekMonthFormat
	    });
	    this.planner.show();
	    this.planner.showLoader();
	    setTimeout(() => {
	      if (this.DOM.plannerWrapOuter) {
	        main_core.Dom.removeClass(this.DOM.plannerWrapOuter, 'hidden');
	      }
	    }, 500);
	    const plannerData = {
	      entryId: this.entry.id || 0,
	      entryLocation: this.entry.data.LOCATION || '',
	      ownerId: this.ownerId,
	      hostId: this.entry.getMeetingHost(),
	      type: this.type,
	      entityList: this.entry.getAttendeesEntityList(),
	      dateFrom: calendar_util.Util.formatDate(this.entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	      dateTo: calendar_util.Util.formatDate(this.entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	      timezone: this.userTimezone,
	      location: this.entry.getLocation(),
	      entry: this.entry
	    };
	    this.loadPlannerDataDebounce(plannerData);
	  }
	  showUserListPopup(node, userList) {
	    if (this.userListPopup) {
	      this.userListPopup.close();
	    }
	    if (userList && userList.length) {
	      this.DOM.userListPopupWrap = this.BX.create('DIV', {
	        props: {
	          className: 'calendar-user-list-popup-block'
	        }
	      });
	      userList.forEach(function (user) {
	        let userAvatar = `
					<div class="ui-icon ui-icon-common-user"  style="width: 34px; height: 34px;">
						<i></i>
					</div>
				`;
	        if (user.AVATAR && user.AVATAR !== '/bitrix/images/1.gif') {
	          userAvatar = `<img src="${encodeURI(user.AVATAR)}" width="34" height="34">`;
	        }
	        if (user.EMAIL_USER) {
	          userAvatar = `
						<div class="ui-icon ui-icon ui-icon-common-user-mail" style="width: 34px; height: 34px;">
							<i></i>
						</div>
					`;
	        }
	        if (user.SHARING_USER) {
	          userAvatar = `
						<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing" style="width: 34px; height: 34px;">
							<i></i>
						</div>
					`;
	        }
	        const userWrap = main_core.Tag.render(_t2 || (_t2 = _`
					<div class="calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card">
						<div class="calendar-slider-sidebar-user-block-avatar">
							<div class="calendar-slider-sidebar-user-block-item">
								${0}
							</div>
						</div>
						<div class="calendar-slider-sidebar-user-info">
							<a href="${0}" class="calendar-slider-sidebar-user-info-name">${0}</a>
						</div>
					</div>
				`), userAvatar, user.URL ? user.URL : '#', main_core.Text.encode(user.DISPLAY_NAME));
	        this.DOM.userListPopupWrap.append(userWrap);
	      }, this);
	      this.userListPopup = this.BX.PopupWindowManager.create("user-list-popup-" + Math.random(), node, {
	        autoHide: true,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: node.offsetWidth / 2,
	        resizable: false,
	        lightShadow: true,
	        content: this.DOM.userListPopupWrap,
	        className: 'calendar-user-list-popup',
	        maxWidth: 300,
	        maxHeight: 500,
	        zIndex: 4000,
	        angle: {
	          position: 'top'
	        }
	      });
	      this.userListPopup.show();
	      this.BX.addCustomEvent(this.userListPopup, 'onPopupClose', () => {
	        this.userListPopup.destroy();
	      });
	    }
	  }
	  initAcceptMeetingControl(uid) {
	    this.DOM.statusButtonset = this.DOM.content.querySelector(`#${uid}_status_buttonset`);
	    this.DOM.statusButtonset.style.marginRight = '12px';
	    if (this.entry.getCurrentStatus() === 'H' || this.entry.getCurrentStatus() === false) {
	      main_core.Dom.remove(this.DOM.statusButtonset);
	    } else {
	      this.statusControl = new calendar_controls.MeetingStatusControl({
	        wrap: this.DOM.statusButtonset,
	        currentStatus: this.DOM.content.querySelector(`#${uid}_current_status`).value || this.entry.getCurrentStatus()
	      });
	      this.statusControl.subscribe('onSetStatus', event => {
	        if (event instanceof main_core_events.BaseEvent) {
	          this.handleEntityChanges();
	          calendar_entry.EntryManager.setMeetingStatus(this.entry, event.getData().status).then(() => {
	            this.statusControl.setStatus(this.entry.getCurrentStatus(), false);
	            this.statusControl.updateStatus();
	            main_core_events.EventEmitter.emit(`MeetingStatusControl_${uid}:onSetStatus`, event);
	          });
	        }
	      });
	    }
	  }
	  copyEventUrl() {
	    if (!this.entryUrl || !this.BX.clipboard.copy(this.entryUrl)) {
	      return;
	    }
	    this.timeoutIds = this.timeoutIds || [];
	    let popup = new this.BX.PopupWindow('calendar_clipboard_copy', this.DOM.copyButton, {
	      content: main_core.Loc.getMessage('CALENDAR_TIP_TEMPLATE_LINK_COPIED'),
	      darkMode: true,
	      autoHide: true,
	      zIndex: 1000,
	      angle: true,
	      offsetLeft: 20,
	      cachable: false
	    });
	    popup.show();
	    let timeoutId;
	    while (timeoutId = this.timeoutIds.pop()) {
	      clearTimeout(timeoutId);
	    }
	    this.timeoutIds.push(setTimeout(function () {
	      popup.close();
	    }, 1500));
	  }
	  displayError(errors = []) {
	    //errors
	  }
	  canDo(entry, action) {
	    if (action === 'edit' || action === 'delete') {
	      if (entry.isResourcebooking()) {
	        return false;
	      }
	      if (this.entry.permissions) {
	        var _this$entry$permissio;
	        return (_this$entry$permissio = this.entry.permissions) == null ? void 0 : _this$entry$permissio['edit'];
	      }
	      return this.section.canDo('edit');
	    }
	    if (action === 'view') {
	      if (this.entry.permissions) {
	        var _this$entry$permissio2;
	        return (_this$entry$permissio2 = this.entry.permissions) == null ? void 0 : _this$entry$permissio2['view_full'];
	      }
	      return this.permissions.view_full;
	    }
	    return false;
	  }
	  plannerIsShown() {
	    return this.DOM.plannerWrap && main_core.Dom.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
	  }
	  loadPlannerData(plannerData) {
	    return new Promise(resolve => {
	      this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	        data: plannerData
	      }).then(response => {
	        this.planner.hideLoader();
	        this.planner.update(response.data.entries, response.data.accessibility);
	        this.planner.updateSelector(calendar_util.Util.adjustDateForTimezoneOffset(plannerData.entry.from, plannerData.entry.userTimezoneOffsetFrom, plannerData.entry.fullDay), calendar_util.Util.adjustDateForTimezoneOffset(plannerData.entry.to, plannerData.entry.userTimezoneOffsetTo, plannerData.entry.fullDay), plannerData.entry.fullDay);
	        resolve(response);
	      }, response => {
	        resolve(response);
	      });
	    });
	  }
	  keyHandler(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('delete')
	    // || e.keyCode === Util.getKeyCode('backspace')
	    && this.canDo(this.entry, 'delete')) {
	      const target = event.target || event.srcElement;
	      const tagName = main_core.Type.isElementNode(target) ? target.tagName.toLowerCase() : null;
	      if (tagName && !['input', 'textarea'].includes(tagName)) {
	        main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', () => {
	          this.BX.SidePanel.Instance.close();
	        });
	        calendar_entry.EntryManager.deleteEntry(this.entry, this.calendarContext);
	      }
	    }
	  }
	  handleEntityChanges() {
	    this.entityChanged = true;
	  }
	  handleConferenceButtonClick() {
	    if (this.conferenceButton) {
	      main_core.Dom.addClass(this.conferenceButton, 'ui-btn-wait');
	    }
	    this.getConferenceChatId();
	    if (this.conferenceButton) {
	      main_core.Dom.removeClass(this.conferenceButton, 'ui-btn-wait');
	    }
	  }
	  getConferenceChatId() {
	    return this.BX.ajax.runAction('calendar.api.calendarajax.getConferenceChatId', {
	      data: {
	        eventId: this.entry.data['PARENT_ID']
	      }
	    }).then(response => {
	      if (top.window.BXIM && response.data && response.data.chatId) {
	        top.BXIM.openMessenger('chat' + parseInt(response.data.chatId));
	        return null;
	      }
	      alert(main_core.Loc.getMessage('EC_CONFERENCE_ERROR'));
	      return null;
	    }, response => {
	      alert(main_core.Loc.getMessage('EC_CONFERENCE_ERROR'));
	      return null;
	    });
	  }
	}

	exports.EventViewForm = EventViewForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar.Controls,BX.Calendar,BX.Vue3,BX.Calendar,BX.Calendar,BX,BX.Event,BX.Calendar));
//# sourceMappingURL=eventviewform.bundle.js.map
