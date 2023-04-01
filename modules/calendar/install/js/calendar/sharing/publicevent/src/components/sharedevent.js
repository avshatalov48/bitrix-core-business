import { EventInfo } from './eventinfo';
import { Loader } from './loader';
import { Dom, Loc, Tag, Event } from 'main.core';
import { Popup } from 'main.popup';
import { Util } from 'calendar.util';
import 'ui.buttons';
import '../css/sharingevent.css';

export const SharedEvent = {
	props: {
		link: Object,
		event: Object,
		owner: Object,
		ownerMeetingStatus: String,
		action: String,
	},
	components: {
		EventInfo,
		Loader,
	},
	data()
	{
		return {
			deleteButton: {
				text: Loc.getMessage('CALENDAR_SHARING_EVENT_DELETE_BUTTON'),
				disabled: false,
			},
			conferenceButton: {
				text: Loc.getMessage('CALENDAR_SHARING_EVENT_CONFERENCE'),
				disabled: false,
			},
			icsButton: {
				text: Loc.getMessage('CALENDAR_SHARING_EVENT_ICS'),
				disabled: false,
			},
			returnButton: {
				text: Loc.getMessage('CALENDAR_SHARING_EVENT_RETURN_BUTTON'),
				disabled: false,
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
			eventInfo: {},
		};
	},
	created()
	{
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:downloadIcsFile', () => {
			this.downloadIcsFile();
		});

		this.eventInfo = {
			dateFrom: Util.getTimezoneDateFromTimestampUTC(parseInt(this.event.timestampFromUTC) * 1000, this.currentTimezone),
			dateTo: Util.getTimezoneDateFromTimestampUTC(parseInt(this.event.timestampToUTC) * 1000, this.currentTimezone),
			timezone: Util.getFormattedTimezone(this.currentTimezone),
			name: this.event.name,
		};

		if (!this.acceptedMeetingStatuses.includes(this.currentMeetingStatus.toUpperCase()))
		{
			this.currentMeetingStatus = 'Q';
		}

		if (this.currentMeetingStatus === 'N')
		{
			this.isDeclined = true;
		}
		if (this.action === 'cancel')
		{
			this.showConfirmPopup();
		}
		if (this.action === 'videoconference')
		{
			this.handleConferenceButtonClick();
		}
		if (this.action === 'ics')
		{
			this.downloadIcsFile();
		}

		this.setPageVisualSettings();
	},
	methods: {
		async downloadIcsFile()
		{
			if (!this.icsFile)
			{
				const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
					data: {
						eventLinkHash: this.link.hash,
					},
				});
				this.icsFile = response.data;
			}

			Util.downloadIcsFile(this.icsFile, 'event');
		},
		enableButton(button)
		{
			button.disabled = false;
		},
		disableButton(button)
		{
			button.disabled = true;
		},
		setPageVisualSettings()
		{
			const htmlNode = document.querySelector('html');
			const bodyNode = document.querySelector('body');

			if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing--public-html');
			}
			if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body'))
			{
				Dom.addClass(bodyNode, 'calendar-sharing--public-body');
			}

			if (this.isDeclined)
			{
				Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
			}
			else
			{
				Dom.addClass(htmlNode,'calendar-sharing--bg-blue');
			}

			if (Util.isMobileBrowser())
			{
				if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile'))
				{
					Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
				}
				if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile'))
				{
					Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
				}
			}
		},
		showConfirmPopup()
		{
			if (!this.warningPopup)
			{
				this.warningPopup = new Popup({
					content: this.getConfirmPopupContent(),
					width: 351,
					lightShadow: true,
					animation: 'fading-slide',
					contentBackground: "#fff",
					overlay: {opacity: 50},
					zIndexAbsolute: 3100,
					cacheable: false,
					closeByEsc: true,
					draggable: true,
					borderRadius: '20px',
					events: {
						onPopupClose: this.closePopup.bind(this)
					},
				})
			}

			this.warningPopup.show();
		},
		getConfirmPopupContent()
		{
			return Tag.render`
				<div class="calendar-sharing-confirm-popup__container">
					<div class="calendar-sharing-confirm-popup__title">
						${Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_MESSAGE')}
					</div>
					<div class="ui-btn-container ui-btn-container-center calendar-shared-event_btn-box">
						${this.getConfirmSubmitButton()}
						${this.getConfirmCancelButton()}
					</div>
				</div>
			`
		},
		getConfirmSubmitButton()
		{
			if (!this.submitPopupButton)
			{
				this.submitPopupButton = Tag.render`
				<button class="ui-btn ui-btn-success ui-btn-round">
					${Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_SUBMIT_BUTTON')}
				</button>
			`
				Event.bind(this.submitPopupButton, 'click', this.handleDeleteButtonClick);
			}

			return this.submitPopupButton;
		},
		getConfirmCancelButton()
		{
			if (!this.cancelPopupButton)
			{
				this.cancelPopupButton = Tag.render`
				<button class="ui-btn ui-btn-light-border ui-btn-round">
					${Loc.getMessage('CALENDAR_SHARING_EVENT_CONFIRM_CANCEL_BUTTON')}
				</button>
			`
				Event.bind(this.cancelPopupButton, 'click', this.closePopup);
			}

			return this.cancelPopupButton;
		},
		closePopup()
		{
			if (this.warningPopup)
			{
				this.warningPopup.destroy();
				this.warningPopup = null;
			}
		},
		async handleDeleteButtonClick()
		{
			this.loadingProcess = true;
			if (this.warningPopup)
			{
				this.warningPopup.close();
			}
			this.disableButton(this.deleteButton);
			const isSuccess = await this.deleteEvent();
			this.enableButton(this.deleteButton);
			this.loadingProcess = false;
			if (isSuccess)
			{
				const htmlNode = document.querySelector('html');
				Dom.removeClass(htmlNode, 'calendar-sharing--bg-blue');
				Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
				this.isDeleted = true;
			}
		},
		async deleteEvent()
		{
			let response = null;
			try
			{
				response = await BX.ajax.runAction('calendar.api.sharingajax.deleteEvent', {
					data: {
						eventId: this.event.id,
						eventLinkHash: this.link.hash,
					}
				});
			}
			catch (e)
			{
				response = e;
			}

			return response.errors.length === 0;
		},
		handleReturnButtonClick()
		{
			this.disableButton(this.returnButton);
			if (this.link.userLinkHash)
			{
				const sharingPath = '/pub/calendar-sharing/';

				window.location.href = document.location.origin
					+ sharingPath
					+ this.link.userLinkHash
				;
			}

			this.enableButton(this.returnButton);
		},
		async handleConferenceButtonClick()
		{
			this.disableButton(this.conferenceButton);
			const conferenceLink = await this.connectToConference();
			this.enableButton(this.conferenceButton);
			if (conferenceLink)
			{
				window.location.href = conferenceLink;
			}
			else
			{
				this.conferenceError = true;
			}
		},
		async connectToConference()
		{
			let response = null;
			try
			{
				response = await BX.ajax.runAction('calendar.api.sharingajax.getConferenceLink', {
					data: {
						eventLinkHash: this.link.hash,
					}
				});
			}
			catch (e)
			{
				response = e;
			}

			return response.data?.conferenceLink || null;
		},
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