import { DateSelector } from './calendar/dateselector';
import { EventSlotList } from './calendar/eventslotlist';
import { AddForm } from './event/addform';
import { ViewForm } from "./event/viewform";
import { WelcomePage } from './welcomepage';
import { Confetti } from 'ui.confetti';

export const Application = {
	props: {
		link: Object,
		owner: Object,
		sharingUser: Object,
		calendarSettings: Object,
		userAccessibility: Object,
		timezoneList: Object,
		welcomePageVisited: String,
	},
	components: {
		DateSelector,
		EventSlotList,
		AddForm,
		ViewForm,
		WelcomePage,
	},
	name: 'Application',
	data()
	{
		return {
			type: '',
			lastEventName: '',
			viewFormError: false,
			eventData: {
				timeFrom: new Date(),
				timeTo: new Date(),
				timezone: this.timezoneList[Intl.DateTimeFormat().resolvedOptions().timeZone],
			},
		};
	},
	created()
	{
		this.type = this.welcomePageVisited ? 'calendar' : 'welcomePage';

		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:changeApplicationType', (event) => {
			this.changeApplicationTypeHandler(event);
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:changeEventData', (event) => {
			this.changeEventDataHandler(event);
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onSetEventData', (event) => {
			this.onSetEventDataHandler(event)
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:setEventViewError', (event) => {
			this.viewFormError = event.data.viewFormError;
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:updateAddFormDefaultParams', (event) => {
			this.updateAddFormDefaultParams(event);
		});
	},
	mounted()
	{
		this.DOM = {
			welcomePage: document.querySelector('.calendar-sharing-welcome-page'),
			calendarContainer: document.querySelector('.calendar-sharing__calendar'),
			dateSelectorContainer: document.querySelector('.calendar-sharing__date-selector'),
			addFormElement: document.querySelector('.calendar-sharing__add-form').firstElementChild,
		};
	},
	methods: {
		changeApplicationTypeHandler(event)
		{
			const currentType = this.type;
			const newType = event.data.type;

			if (currentType === 'welcomePage' && newType === 'calendar')
			{
				this.transitFromWelcomePageToCalendar();
			}

			if (currentType === 'calendar' && newType === 'addForm')
			{
				this.transitFromCalendarToAddForm();
			}

			if (currentType === 'addForm' && newType === 'calendar')
			{
				this.transitFromAddFormToCalendar();
			}

			if (currentType === 'addForm' && newType === 'viewForm')
			{
				this.transitFromAddFormToViewForm();
			}

			if (currentType === 'viewForm' && newType === 'calendar')
			{
				this.transitFromViewFormToCalendar();
			}

			this.type = newType;
		},
		changeEventDataHandler(event)
		{
			this.eventData = event.data;
		},
		onSetEventDataHandler(event)
		{
			if (event.data.eventId)
			{
				this.eventData.id = event.data.eventId;
			}
			if (event.data.eventName)
			{
				this.eventData.name = event.data.eventName;
			}
			if (event.data.eventLinkId)
			{
				this.eventData.linkId = event.data.eventLinkId;
			}
			if (event.data.eventLinkHash)
			{
				this.eventData.linkHash = event.data.eventLinkHash;
			}
			if (event.data.eventLinkShortUrl)
			{
				this.eventData.eventLinkShortUrl = event.data.eventLinkShortUrl;
			}
		},
		updateAddFormDefaultParams(event)
		{
			this.sharingUser.userName = event.data.userName;
			this.sharingUser.personalMailbox = event.data.contactData;

			if (this.viewFormError)
			{
				this.lastEventName = event.data.eventName;
			}
			else
			{
				this.lastEventName = '';
			}
		},
		transitFromWelcomePageToCalendar()
		{
			this.DOM.calendarContainer.style.transform = 'scale(1.5)';
			this.DOM.calendarContainer.style.filter = 'blur(1px)';
			this.DOM.calendarContainer.style.opacity = 0;
			this.DOM.calendarContainer.style.transition = '300ms all ease';

			this.DOM.welcomePage.style.transition = '300ms all ease';
			this.DOM.welcomePage.style.transform = 'scale(1.5)';
			this.DOM.welcomePage.style.filter = 'blur(1px)';
			this.DOM.welcomePage.style.opacity = 0;

			setTimeout(() => {
				this.DOM.welcomePage.remove();
				this.DOM.calendarContainer.style.display = '';
				this.$Bitrix.eventEmitter.emit('calendar:sharing:onShowCalendar');

				setTimeout(() => {
					this.DOM.calendarContainer.style.transform = '';
					this.DOM.calendarContainer.style.filter = '';
					this.DOM.calendarContainer.style.opacity = '';

					setTimeout(() => {
						this.DOM.calendarContainer.style.transition = '';
					}, 300);
				}, 100);
			}, 300);
		},
		transitFromCalendarToAddForm()
		{
			this.DOM.calendarContainer.style.height = this.DOM.dateSelectorContainer.offsetHeight + 'px';
			new BX.easing({
				duration: 150,
				start: {
					scrollLeft: this.DOM.calendarContainer.scrollLeft,
					height: this.DOM.dateSelectorContainer.offsetHeight,
				},
				finish: {
					scrollLeft: this.DOM.calendarContainer.scrollWidth - this.DOM.calendarContainer.offsetWidth,
					height: this.DOM.addFormElement.offsetHeight,
				},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: (state) => {
					this.DOM.calendarContainer.scrollLeft = state.scrollLeft;
					this.DOM.calendarContainer.style.height = state.height + 'px';
				},
				complete: () => {}
			}).animate();
		},
		transitFromAddFormToCalendar()
		{
			this.DOM.calendarContainer.style.height = this.DOM.addFormElement.offsetHeight + 'px';
			new BX.easing({
				duration: 150,
				start: {
					scrollLeft: this.DOM.calendarContainer.scrollLeft,
					height: this.DOM.addFormElement.offsetHeight,
				},
				finish: {
					scrollLeft: 0,
					height: this.DOM.dateSelectorContainer.offsetHeight,
				},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: (state) => {
					this.DOM.calendarContainer.scrollLeft = state.scrollLeft;
					this.DOM.calendarContainer.style.height = state.height + 'px';
				},
				complete: () => {
					this.DOM.calendarContainer.style.height = '';
				}
			}).animate();
		},
		transitFromAddFormToViewForm()
		{
			this.DOM.calendarContainer.scrollLeft = 0;
			this.DOM.calendarContainer.style.height = '';
			this.DOM.calendarContainer.style.display = 'none';

			if (!this.viewFormError)
			{
				Confetti.fire({
					particleCount: 240,
					spread: 70,
					origin: { y: 0.3, x: 0.5 },
					zIndex: 2,
				});
			}
		},
		transitFromViewFormToCalendar()
		{
			this.DOM.calendarContainer.style.display = '';
			this.eventData.name = false;
			this.$Bitrix.eventEmitter.emit('calendar:sharing:onShowCalendar');
		},
	},
	template: `
		<div class="calendar-sharing-welcome-page">
			<WelcomePage
				:owner="owner"
			/>
		</div>
		<div class="calendar-sharing__calendar" style="display: none;">
			<div class="calendar-sharing__date-selector calendar-sharing--bg-gray">
				<DateSelector
					:userAccessibility="userAccessibility"
					:calendarSettings="calendarSettings"
					:timezoneList="timezoneList"
					:owner="owner"
				/>
				<EventSlotList
					:timezoneList="timezoneList"
				/>
			</div>
			<div class="calendar-sharing__add-form">
				<div class="calendar-sharing-main__container calendar-sharing__form_box">
					<AddForm
						:owner="owner"
						:sharingUser="sharingUser"
						:event="eventData"
						:last-event-name="lastEventName"
						:phone-feature-enabled="calendarSettings.phoneFeatureEnabled"
						:userLinkHash="link.hash"
					/>
				</div>
			</div>
		</div>
		<div v-if="type === 'viewForm'">
			<ViewForm
				:owner="owner"
				:event="eventData"
				:view-form-error="viewFormError"
			/>
			<div 
				class="calendar-sharing-event-created-info" v-if="!viewFormError"
				v-html="$Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATED_INFO').replace('#EVENT_LINK#', eventData.eventLinkShortUrl)"
			>
			</div>
		</div>
	`
};
