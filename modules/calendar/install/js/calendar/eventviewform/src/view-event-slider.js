"use strict";

import { Util } from 'calendar.util';
import { Entry } from 'calendar.entry';
import { Runtime, Event } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { UserAvatar	} from './user-avatar';

export const ViewEventSlider = {
	name: 'ViewEventSlider',
	props: ['params', 'reloadPlannerCallback', 'showUserListPopupCallback'],
	components: {
		UserAvatar,
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
			userList: {y : [], i: [], q: [], n: []},
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
			showComments: this.params.showComments,
			filesView: this.getComponentHTML(this.params.filesView),
			crmView: this.getComponentHTML(this.params.crmView),
			entry: new Entry({data: this.params.entry, userIndex: this.params.userIndex}),
			isInvited: false,

			updateParamsDebounce: Runtime.debounce(this.updateParams, 500, this),
			hasPulls: false,
			backgroundPullEvent: null,
		}
	},
	created()
	{
		this.isInvited = this.entry.isInvited();
	},
	mounted()
	{
		if (this.params.eventExists)
		{
			this.updateUserList();
			if (this.showComments)
			{
				this.loadCommentsView();
			}
			if (this.isWebdavEvent)
			{
				this.executeScripts(this.$refs.filesView);
			}
			Event.bind(document, 'visibilitychange', this.handleBackgroundPulls);
		}
	},
	beforeMount()
	{
		if (this.params.eventExists)
		{
			EventEmitter.subscribe('onPullEvent-calendar', this.handlePullEvent);
			EventEmitter.subscribe(`MeetingStatusControl_${this.id}:onSetStatus`, this.handleStatusUpdate);
		}
	},
	beforeUnmount()
	{
		if (this.params.eventExists)
		{
			EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullEvent);
			EventEmitter.unsubscribe(`MeetingStatusControl_${this.id}:onSetStatus`, this.handleStatusUpdate);
		}
	},
	methods: {
		getComponentHTML(json)
		{
			if (!json)
			{
				return '';
			}
			return JSON.parse(json).data.html;
		},
		loadCommentsView()
		{
			BX.ajax.runAction('calendar.api.calendareventviewform.getCommentsView', {
				data: {
					signedEvent: this.params.signedEvent,
				}
			}).then(response => {
				const commentsElement = document.createElement('div');
				commentsElement.innerHTML = response.data.html;
				this.$refs.commentsView.appendChild(commentsElement);
				this.executeScripts(this.$refs.commentsView);
			});
		},
		executeScripts(element)
		{
			if (!element)
			{
				return;
			}
			//run scripts
			const scripts = element.querySelectorAll('script');
			for (const script of scripts)
			{
				const s = document.createElement('script');
				s.innerHTML = script.innerHTML;
				script.parentNode.appendChild(s);
				script.remove();
			}
			//remove script elements
			// element.querySelectorAll('script').forEach(e => e.remove());
		},
		updateUserList()
		{
			this.userList = {y : [], i: [], q: [], n: []};
			if (this.entry.isMeeting())
			{
				this.entry.getAttendees().forEach(function(user) {
					if (user.STATUS === 'H')
					{
						this.userList.y.push(user);
					}
					else if (this.userList[user.STATUS.toLowerCase()])
					{
						this.userList[user.STATUS.toLowerCase()].push(user);
					}
				}, this);
			}
		},
		reloadPlanner()
		{
			const plannerData = {
				entryId: this.entry.id || 0,
				entryLocation: this.entry.data.LOCATION || '',
				ownerId: 0,
				hostId: this.entry.getMeetingHost(),
				type: 'user',
				entityList: this.entry.getAttendeesEntityList(),
				dateFrom: Util.formatDate(this.entry.from.getTime() - Util.getDayLength() * 3),
				dateTo: Util.formatDate(this.entry.to.getTime() + Util.getDayLength() * 10),
				timezone: this.timezone,
				location: this.entry.getLocation(),
				entry: this.entry
			};
			this.reloadPlannerCallback(plannerData);
		},
		handleBackgroundPulls()
		{
			if (this.hasPulls)
			{
				this.updateParamsDebounce(this.backgroundPullEvent);
				this.hasPulls = false;
			}
		},
		handleStatusUpdate(event)
		{
			this.entry.data.MEETING_STATUS = event.getData().status;
			this.isInvited = this.entry.isInvited();
		},
		handlePullEvent(event: BaseEvent)
		{
			if (event.data[0] === "refresh_sync_status")
			{
				return;
			}
			if (event.data[1].fields.CAL_TYPE === 'location')
			{
				return;
			}

			// debounce all pull events except location
			// update only when page is active
			if (BX.Calendar.Util.documentIsDisplayingNow())
			{
				this.updateParamsDebounce(event);
			}
			else
			{
				this.hasPulls = true;
				this.backgroundPullEvent = event;
			}
		},
		updateParams(event: BaseEvent)
		{
			if (parseInt(event.data[1].fields.PARENT_ID) !== parseInt(this.params.parentId))
			{
				this.reloadPlanner();
				return;
			}
			const pullData = event.data[1].fields;

			this.name = pullData.NAME;
			this.accessibility = pullData.ACCESSIBILITY;

			BX.ajax.runAction('calendar.api.calendareventviewform.getCalendarViewSliderParams', {
				data: {
					entryId: this.eventId,
					dateFrom: Util.formatDate(pullData.DATE_FROM),
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
				this.showComments = newData.showComments;
				this.filesView = this.getComponentHTML(newData.filesView);
				if (this.filesView)
				{
					//wait for div element created
					setTimeout(() => {this.executeScripts(this.$refs.filesView)}, 1000);
				}
				this.crmView = this.getComponentHTML(newData.crmView);
				this.entry = new Entry({data: newData.entry, userIndex: newData.userIndex});

				this.updateUserList();
				this.reloadPlanner();
			});
		},
		highlightChange(element)
		{
			if (!element)
			{
				return;
			}
			const savedOpacity = element.style.opacity;
			const savedTransition = element.style.transition;
			element.style.opacity = '0.2';
			setTimeout(() => {
				element.style.transition = '1s';
				element.style.opacity = savedOpacity;
				setTimeout(() => { element.style.transition = savedTransition; }, 1000);
			}, 100);
		}
	},
	watch: {
		name: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightName); } },
		fromToHtml: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightFromTo); } },
		description: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightDescription); } },
		crmView: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightCrmView); } },
		location: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightLocation); } },
		accessibility: { handler(newValue, oldValue) { this.highlightChange(this.$refs.highlightAccessibility); } },
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
									<div class="calendar-slider-sidebar-user-container">
										<div class="calendar-slider-sidebar-user-block-avatar">
											<a :href="meetingHost.URL">
												<UserAvatar :user="meetingHost" :avatarSize="avatarSize"/>
												<div class="calendar-slider-sidebar-user-icon-top"></div>
												<div class="calendar-slider-sidebar-user-icon-bottom"></div>
											</a>
										</div>
									</div>
									<div class="calendar-slider-sidebar-user-container" v-for="att in attendees.y.slice(0,10)">
										<div class="calendar-slider-sidebar-user-block-avatar" v-if="meetingHost.ID != att.ID">
											<a :href="att.URL">
												<UserAvatar :user="att" :avatarSize="avatarSize"/>
												<div class="calendar-slider-sidebar-user-icon-bottom"></div>
											</a>
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

							<div class="calendar-slider-detail-content">
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

										<div v-if="canEditCalendar">
											<button :id="id + '_but_edit'" class="ui-btn ui-btn-light-border">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_EDIT')}}</button>
											<button :id="id + '_but_del'" class="ui-btn ui-btn-light-border">{{$Bitrix.Loc.getMessage('EC_VIEW_SLIDER_DEL')}}</button>
										</div>
										
									</div>
								</div>
							</div>
						</div>
						
						<div class="calendar-slider-comments" v-if="showComments">
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
