import {Reflection, Dom, Tag, Loc, Type} from "main.core";

class CalendarEvent
{
	constructor(options = {})
	{
		this.eventId = options.eventId;
		this.hasDecision = options.hasDecision;
		this.isPositiveDecision = options.isPositiveDecision;
		this.hash = options.hash;
		this.downnoloadLink = options.downloadLink;
		this.decisionButtonsBlock = document.querySelector('.calendar-pub-event-btn-container');
		this.titleBlock = document.querySelector('.calendar-pub-event-title-main');
		this.eventWrapper = document.querySelector('.calendar-pub-event-wrap');
		this.listBoxWrapper = document.querySelector('.calendar-pub-event-user-list-box');
		this.decisionBlockWrapper = decisionBlock;
		this.buttonsContainer = buttonsContainer;

		this.init();
	}

	init()
	{
		this.initWrappersForButtons();
		this.initHandler();
	}

	initWrappersForButtons()
	{
		this.primaryButtonWrapper = this.buttonsContainer.children[0];
		this.secondButtonWrapper = this.buttonsContainer.children[1];
	}

	initHandler()
	{
		if (this.hasDecision)
		{
			this.initChangeDecisionButton();
			Dom.append(this.changeDecisionButton, this.primaryButtonWrapper)
			if (this.isPositiveDecision)
			{
				this.initDownloadButton();
				Dom.append(this.downloadButton, this.secondButtonWrapper);
			}
		}
		else
		{
			this.initAcceptButton();
			this.initDeclineButton();
			Dom.append(this.acceptDecisionButton, this.primaryButtonWrapper);
			Dom.append(this.declineDecisionButton, this.secondButtonWrapper);
		}

		this.initListBoxHandlers();
	}

	initChangeDecisionButton()
	{
		this.changeDecisionButton = this.getChangeDecisionButton();
		this.changeDecisionButton.addEventListener('click', () => {this.changeStateWithoutDecision()})
	}

	initAcceptButton()
	{
		this.acceptDecisionButton = this.getAcceptDecisionButton();
		this.acceptDecisionButton.addEventListener('click', () => {this.changeStateWithDecision(true)});
	}

	initDeclineButton()
	{
		this.declineDecisionButton = this.getDeclineDecisionButton();
		this.declineDecisionButton.addEventListener('click', () => {this.changeStateWithDecision(false)});
	}

	changeStateWithDecision(decision)
	{
		this.hasDecision = true;

		Dom.remove(this.acceptDecisionButton);
		this.acceptDecisionButton = undefined;
		Dom.remove(this.declineDecisionButton);
		this.declineDecisionButton = undefined;

		this.showChangeDecisionButton();

		if (decision)
		{
			this.showAcceptDecisionBlock();
			this.showDownloadButton();
		}
		else
		{
			this.showDeclineDecisionBlock();
		}

		this.isPositiveDecision = decision;

		BX.ajax.runComponentAction('bitrix:calendar.pub.event', 'handleDecision', {
			mode: 'class',
			data: {
				'decision': decision ? 'Y' : 'N',
				'eventId': this.eventId,
				'hash': this.hash,
			}
		}).then((response) => {
			if (response.data.attendeesList.length > 0)
			{
				this.rebuildUserList(response.data.attendeesList);
			}
		});
	}

	showChangeDecisionButton()
	{
		if (!this.changeDecisionButton)
		{
			this.initChangeDecisionButton();
		}

		Dom.append(this.changeDecisionButton, this.primaryButtonWrapper);
	}

	getChangeDecisionButton()
	{
		return Tag.render `
			<button id="changeDecisionButton" class="ui-btn ui-btn-round ui-btn-lg ui-btn-success calendar-pub-event-btn calendar-pub-event-btn-change-decision">
				${Loc.getMessage('EC_CALENDAR_CHANGE_DECISION_TITLE')}
			</button>
		`;
	}

	showAcceptDecisionBlock()
	{
		const decisionBlock = this.decisionBlockWrapper.children[1];
		decisionBlock.innerText = Loc.getMessage('EC_CALENDAR_PUB_EVENT_DECISION_YES');
		Dom.removeClass(this.eventWrapper, 'calendar-pub-event--decline ');
		Dom.addClass(this.eventWrapper, 'calendar-pub-event--accept');
	}

	showDeclineDecisionBlock()
	{
		const decisionBlock = this.decisionBlockWrapper.children[1];
		decisionBlock.innerText = Loc.getMessage('EC_CALENDAR_PUB_EVENT_DECISION_NO');
		Dom.removeClass(this.eventWrapper, 'calendar-pub-event--accept');
		Dom.addClass(this.eventWrapper, 'calendar-pub-event--decline ');
	}

	changeStateWithoutDecision()
	{
		Dom.remove(this.changeDecisionButton);
		this.changeDecisionButton = undefined;
		if (this.downloadButton)
		{
			Dom.remove(this.downloadButton);
			this.downloadButton = undefined;
		}
		this.showAcceptDecisionButton();
		this.showDeclineDecisionButton();
	}

	showAcceptDecisionButton()
	{
		if (!this.acceptDecisionButton)
		{
			this.initAcceptButton();
		}

		Dom.append(this.acceptDecisionButton, this.primaryButtonWrapper);
	}

	getAcceptDecisionButton()
	{
		return Tag.render`
			<button id="acceptDecisionButton" class="ui-btn ui-btn-round ui-btn-lg ui-btn-success calendar-pub-event-btn">
				${Loc.getMessage('EC_CALENDAR_DECISION_TITLE_YES')}
			</button>
		`;
	}

	showDeclineDecisionButton()
	{
		if (!this.declineDecisionButton)
		{
			this.initDeclineButton();
		}

		Dom.append(this.declineDecisionButton, this.secondButtonWrapper);
	}

	getDeclineDecisionButton()
	{
		return Tag.render`
			<button id="declineDecisionButton" class="ui-btn ui-btn-link ui-btn-lg calendar-pub-event-btn" data-decision="N">
			${Loc.getMessage('EC_CALENDAR_DECISION_TITLE_NO')}
		</button>
		`;
	}

	initListBoxHandlers()
	{
		this.initAttendeesListBoxHandlers();
		this.initAttachmentsListBoxHandlers();
	}

	initAttendeesListBoxHandlers()
	{
		const attendeesListButton = document.querySelector('.calendar-pub-event-user-list-btn');
		const contentBox = document.querySelector('.calendar-pub-event-user-list-content');

		if (Type.isDomNode(attendeesListButton))
		{
			attendeesListButton.addEventListener('click', () =>
			{
				const contentHeight = contentBox.scrollHeight;
				contentBox.style.height = contentHeight + 'px';
				contentBox.style.maxHeight = contentHeight + 'px';
				attendeesListButton.style.display = 'none';
			})
		}
	}

	initAttachmentsListBoxHandlers()
	{
		const attachmentBtn = document.querySelector('.calendar-pub-event-user-attachment-btn');
		const attachmentContentBox = document.querySelector('.calendar-pub-event-user-attachment-content');
		if (Type.isDomNode(attachmentBtn))
		{
			attachmentBtn.addEventListener('click', () =>
			{
				const contentHeight = attachmentContentBox.scrollHeight;
				attachmentContentBox.style.height = contentHeight + 'px';
				attachmentContentBox.style.maxHeight = contentHeight + 'px';
				attachmentBtn.style.display = 'none';
			})
		}
	}

	initDownloadButton()
	{
		this.downloadButton = this.getDownloadButton();
	}

	showDownloadButton()
	{
		this.initDownloadButton();
		Dom.append(this.downloadButton, this.secondButtonWrapper);
	}

	getDownloadButton()
	{
		return Tag.render`
			<a id="downloadButton" href="${BX.util.htmlspecialchars(this.downnoloadLink)}" class="ui-btn ui-btn-link ui-btn-lg calendar-pub-event-btn" >
			${Loc.getMessage('EC_CALENDAR_ICAL_INVITATION_DOWNLOAD_INVITATION')}
		</a>
		`;
	}

	getDecisionBlock()
	{
		if (this.hasDecision)
		{
			return document.querySelector('.calendar-pub-event-desc');
		}

		return null;
	}

	rebuildUserList(attendeesList)
	{
		if (Type.isArray(attendeesList))
		{
			const userListContainer = Tag.render`
				<div class="calendar-pub-event-user-list-content">
					${attendeesList.map((attendee) => Tag.render`
						<div class="calendar-pub-event-user-list-item ${this.getAdditionalClassForAttendeesList(attendee['status'])}">
							${attendee['name']}
						</div>
					`)}
				</div>
			`;
			const oldAttendeesContainer = document.querySelector('.calendar-pub-event-user-list-content');
			const oldAttendeesListButton = document.querySelector('.calendar-pub-event-user-list-btn');
			if (Type.isDomNode(oldAttendeesContainer))
			{
				const wrapper = oldAttendeesContainer.parentElement;
				Dom.remove(oldAttendeesContainer);
				if (Type.isDomNode(oldAttendeesListButton))
				{
					Dom.remove(oldAttendeesListButton);
				}

				Dom.append(userListContainer, wrapper);

				if (attendeesList.length > 3)
				{
					const attendeesListButton = Tag.render`
						<div data-button="users" class="calendar-pub-event-user-list-btn">
							${Loc.getMessage('EC_CALENDAR_PUB_EVENT_ALL_ATTENDEES_TITLE')}
							<span>(${attendeesList.length})</span>
						</div>
					`;
					Dom.append(attendeesListButton, wrapper);
					this.initAttendeesListBoxHandlers();
				}

			}
		}
	}

	getAdditionalClassForAttendeesList(status)
	{
		switch (status)
		{
			case 'ACCEPTED':
				return 'calendar-pub-event-user--accept';
			case 'DECLINED':
				return 'calendar-pub-event-user--cancel';
			default:
				return 'calendar-pub-event-user--waiting';
		}
	}
}

Reflection.namespace('BX.Calendar.Pub').CalendarEvent = CalendarEvent;
