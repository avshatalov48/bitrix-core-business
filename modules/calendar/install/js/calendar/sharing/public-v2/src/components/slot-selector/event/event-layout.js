import { Dom, Event, Loc, Tag, Type, Browser, Text } from 'main.core';
import { Popup } from 'main.popup';
import { Member, MembersList } from '../../layout/members-list';
import WidgetDate from '../widget-date';
import { DateTimeFormat } from 'main.date';
import { BottomSheet } from 'ui.bottomsheet';
import 'ui.icon-set.actions';

type File = {
	link: string,
	name: string,
	size: number,
};

export type Props = {
	eventNotFound: {
		title: string,
		subtitle: string,
	},

	eventName: string,
	from: Date,
	to: Date,
	timezone: string,
	browserTimezone: string,
	isFullDay: boolean,
	members: Member[],
	location: string,
	description: string,
	files: File[],
	rruleDescription: string,
	allAttendees: boolean,
	filled: boolean,

	title: string,

	iconClassName: string,

	onDeleteEvent: function,
	cancelledInfo: {
		date: Date,
		name: string,
	},

	showBackCalendarButton: boolean,
	bottomButtons: {
		onAcceptInvitation: function,
		onDeclineInvitation: function,
		onDownloadIcs: function,
		onStartVideoconference: function,
		onReturnToCalendar: function,
	},

	poweredLabel: {
		isRu: boolean,
	},
};

export default class EventLayout
{
	#props;
	#layout: {
		wrap: HTMLElement,
		acceptButton: HTMLElement,
		declineButton: HTMLElement,
		icsButton: HTMLElement,
		videoconferenceButton: HTMLElement,

		description: HTMLElement,
		expandButton: HTMLElement,
		collapseButton: HTMLElement,
	};

	static descriptionCollapsed = true;

	constructor(props: Props)
	{
		this.#props = props;
		this.#layout = {};
	}

	update(props)
	{
		this.#props = props;

		this.render();
	}

	render(): HTMLElement
	{
		const wrap = this.#renderEvent();

		this.#layout.wrap?.replaceWith(wrap);
		this.#layout.wrap = wrap;

		return wrap;
	}

	#renderEvent(): HTMLElement
	{
		if (this.#props.eventNotFound)
		{
			return this.#renderEventNotFound();
		}

		return Tag.render`
			<div class="calendar-sharing__form-result">
				${this.#renderPoweredLabel()}
				${this.#renderBackButton()}
				<div class="calendar-sharing__calendar-block --form --center">
					${this.#renderNodeIcon()}
					${this.#renderEventNameNode()}
					${this.#renderStateTitleNode()}
				</div>

				<div class="calendar-sharing__calendar-block --form --center">
					${this.#renderWidgetDate()}
					${this.#renderProps()}
				</div>

				<div class="calendar-sharing__calendar-block --form --center">
					${this.#renderCancelContent()}
				</div>

				<div class="calendar-sharing__calendar-block --top-auto">
					${this.#renderBottomButtons()}
				</div>
			</div>
		`;
	}

	#renderEventNotFound(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__form-result">
				<div class="calendar-sharing__calendar-block --form --center">
					<div class="calendar-sharing__form-result_icon --decline"></div>
					<div class="calendar-pub-ui__typography-title --center --line-height-normal">
						${this.#props.eventNotFound.title}
					</div>
					<div class="calendar-pub-ui__typography-s --center">
						${this.#props.eventNotFound.subtitle}
					</div>
				</div>
			</div>
		`;
	}

	#renderPoweredLabel(): HTMLElement|string
	{
		if (!this.#props.poweredLabel)
		{
			return '';
		}

		return Tag.render`
			<div class="calendar-pub__block-label ${this.#props.poweredLabel.isRu ? '--ru' : ''}"></div>
		`;
	}

	#renderWidgetDate(): HTMLElement
	{
		const widgetDate = new WidgetDate({
			allAttendees: this.#props.allAttendees,
			filled: this.#props.filled,
			browserTimezone: this.#props.browserTimezone,
		});

		if (this.#props.from && this.#props.to)
		{
			widgetDate.updateValue({
				from: this.#props.from,
				to: this.#props.to,
				timezone: this.#props.timezone,
				isFullDay: this.#props.isFullDay,
				rruleDescription: this.#props.rruleDescription,
				members: this.#props.members,
			});
		}

		return widgetDate.render();
	}

	#renderProps(): HTMLElement|string
	{
		if (!this.#props.allAttendees)
		{
			return '';
		}

		return Tag.render`
			<div class="calendar-pub__event-props">
				${this.#renderMembers()}
				${this.#renderLocation()}
				${this.#renderFiles()}
				${this.#renderDescription()}
			</div>
		`;
	}

	#renderMembers(): HTMLElement
	{
		return new MembersList({
			className: 'calendar-pub__event-prop',
			textClassName: 'calendar-pub-ui__typography-xs',
			avatarSize: 30,
			members: this.#props.members,
			allAttendees: this.#props.allAttendees,
			maxAvatarsCount: 8,
		}).render();
	}

	#renderLocation(): HTMLElement|string
	{
		if (!Type.isStringFilled(this.#props.location))
		{
			return '';
		}

		return Tag.render`
			<div class="calendar-pub__event-prop">
				<div class="calendar-pub-ui__typography-xs">
					${Loc.getMessage('CALENDAR_SHARING_MEETING_LOCATION')}
				</div>
				<div class="calendar-pub-ui__typography-sm">
					${Text.encode(this.#props.location)}
				</div>
			</div>
		`;
	}

	#renderDescription(): HTMLElement|string
	{
		if (!Type.isStringFilled(this.#props.description))
		{
			return '';
		}

		this.#layout.description = Tag.render`
			<div class="calendar-pub-ui__typography-sm">
				${this.#props.description}
			</div>
		`;

		if (EventLayout.descriptionCollapsed)
		{
			const collapseHeight = 100;
			this.#layout.description.style.overflow = 'hidden';
			this.#layout.description.style.maxHeight = `${collapseHeight}px`;
			setTimeout(() => this.#collapseDescription(collapseHeight, false));
		}
		else
		{
			this.#layout.description.append(this.#renderCollapseButton());
			this.#updateExpandCollapseButtonMargin(this.#layout.collapseButton);
		}

		return Tag.render`
			<div class="calendar-pub__event-prop">
				<div class="calendar-pub-ui__typography-xs">
					${Loc.getMessage('CALENDAR_SHARING_MEETING_DESCRIPTION')}
				</div>
				${this.#layout.description}
			</div>
		`;
	}

	#renderFiles(): HTMLElement|string
	{
		if (!Type.isArrayFilled(this.#props.files))
		{
			return '';
		}

		return Tag.render`
			<div class="calendar-pub__event-prop">
				<div class="calendar-pub-ui__typography-xs">
					${Loc.getMessage('CALENDAR_SHARING_MEETING_FILES')}
				</div>
				<div class="calendar-pub-ui__typography-sm">
					${this.#props.files.map((file) => this.#renderFile(file))}
				</div>
			</div>
		`;
	}

	#renderFile(file: File): HTMLElement
	{
		return Tag.render`
			<span class="calendar-pub__event-file">
				<a class="calendar-pub__event-file-name" href="${encodeURI(file.link)}">
					${Text.encode(file.name)}
				</a>
				<span class="calendar-pub__event-file-size">${file.size}</span>
			</span>
		`;
	}

	#collapseDescription(maxHeight: number, animate: boolean = true): void
	{
		this.#setExpandCollapseButtonMargin(this.#layout.expandButton);
		this.#layout.collapseButton?.remove();
		const startHeight = this.#layout.description.offsetHeight;

		const children = [...this.#layout.description.childNodes];
		let lastVisible;
		let height = 0;
		for (let child of children)
		{
			if (child.nodeName === '#text')
			{
				const span = Tag.render`<span>${Text.encode(child.textContent)}</span>`;
				child.replaceWith(span);
				child = span;
			}
			if (height > maxHeight)
			{
				child.style.display = 'none';
				continue;
			}
			let childHeight = child.getBoundingClientRect().height;
			if (child.nodeName === 'BR' && child.previousSibling.nodeName !== 'BR')
			{
				continue;
			}
			if (height < maxHeight && height + childHeight > maxHeight)
			{
				lastVisible = child;
				child.after(this.#renderExpandButton());
			}
			height += childHeight;
		}

		this.#layout.description.style.height = '';
		this.#layout.description.style.maxHeight = '';

		if (lastVisible)
		{
			const extraLines = (this.#layout.description.offsetHeight - maxHeight) / 20;
			if (extraLines > 2)
			{
				lastVisible.innerText = lastVisible.innerText.slice(0, -35 * (extraLines - 1));
			}

			while (this.#layout.description.offsetHeight > maxHeight)
			{
				lastVisible.innerText = lastVisible.innerText.slice(0, -2);
				if (lastVisible.innerText === '')
				{
					const previousVisible = lastVisible.previousSibling;
					lastVisible.remove();
					lastVisible = previousVisible;
				}
				lastVisible.innerHTML += '&mldr;';
			}
		}

		this.#updateExpandCollapseButtonMargin(this.#layout.expandButton);

		if (animate)
		{
			this.#animateDescriptionHeight(startHeight, maxHeight);
		}

		EventLayout.descriptionCollapsed = true;
	}

	#expandDescription(): void
	{
		this.#setExpandCollapseButtonMargin(this.#layout.collapseButton);
		const height = this.#layout.description.offsetHeight;
		this.#layout.description.innerHTML = this.#props.description;
		this.#layout.description.append(this.#renderCollapseButton());
		this.#updateExpandCollapseButtonMargin(this.#layout.collapseButton);
		this.#animateDescriptionHeight(height, this.#layout.description.offsetHeight);
		EventLayout.descriptionCollapsed = false;
	}

	#updateExpandCollapseButtonMargin(button: HTMLElement): void
	{
		const span = Tag.render`
			<span>
				${button.previousSibling.cloneNode(true)}
			</span>
		`;

		button.previousSibling.replaceWith(span);

		if (span.offsetTop !== button.offsetTop)
		{
			Dom.style(span, 'margin-right', '5px');
			Dom.style(button, 'margin-left', '');
		}
	}

	#setExpandCollapseButtonMargin(button: HTMLElement): void
	{
		Dom.style(button, 'margin-left', '5px');
	}

	#animateDescriptionHeight(startHeight: number, endHeight: number): void
	{
		const animationDuration = 200;
		this.#layout.description.style.height = `${startHeight}px`;
		this.#layout.description.style.transition = `height ${animationDuration}ms ease`;
		setTimeout(() => {
			this.#layout.description.style.height = `${endHeight}px`;
			setTimeout(() => {
				this.#layout.description.style.height = '';
				this.#layout.description.style.transition = '';
			}, animationDuration);
		});
	}

	#renderExpandButton(): HTMLElement
	{
		if (this.#layout.expandButton)
		{
			return this.#layout.expandButton;
		}

		this.#layout.expandButton = Tag.render`
			<div class="calendar-pub__link-button" style="margin-left: 5px;">
				${Loc.getMessage('CALENDAR_SHARING_EXPAND')}
			</div>
		`;

		Event.bind(this.#layout.expandButton, 'click', () => this.#expandDescription());

		return this.#layout.expandButton;
	}

	#renderCollapseButton(): HTMLElement
	{
		if (this.#layout.collapseButton)
		{
			return this.#layout.collapseButton;
		}

		this.#layout.collapseButton = Tag.render`
			<div class="calendar-pub__link-button" style="margin-left: 5px;">
				${Loc.getMessage('CALENDAR_SHARING_COLLAPSE')}
			</div>
		`;

		Event.bind(this.#layout.collapseButton, 'click', () => this.#collapseDescription(100));

		return this.#layout.collapseButton;
	}

	#renderNodeIcon(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__form-result_icon ${this.#props.iconClassName}"></div>
		`;
	}

	#renderBackButton(): HTMLElement
	{
		if (this.#props.showBackCalendarButton)
		{
			return Tag.render`
				<div class="calendar-sharing__calendar-bar --arrow">
					<div class="calendar-sharing__calendar-back" onclick="${this.#onReturnButtonClick.bind(this)}"></div>
				</div>
			`;
		}

		return Tag.render`<div class="calendar-sharing__calendar-bar --no-margin"></div>`;
	}

	#renderEventNameNode(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-pub-ui__typography-title --center --line-height-normal">
				${Text.encode(this.#props.eventName)}
			</div>
		`;
	}

	#renderStateTitleNode(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-pub-ui__typography-s --center">
				${this.#props.title}
			</div>
		`;
	}

	#renderCancelContent(): HTMLElement|string
	{
		if (this.#props.onDeleteEvent)
		{
			return Tag.render`
				<div onclick="${this.showCancelEventPopup.bind(this)}" class="calendar-pub__form-status --decline">
					<div class="ui-icon-set --undo-1"></div>
					<div class="calendar-pub__form-status_text">
						${Loc.getMessage('CALENDAR_SHARING_DECLINE_MEETING')}
					</div>
				</div>
			`;
		}

		if (this.#props.onDeclineEvent)
		{
			return Tag.render`
				<div onclick="${this.#props.onDeclineEvent}" class="calendar-pub__form-status --decline">
					<div class="ui-icon-set --cross-45"></div>
					<div class="calendar-pub__form-status_text">
						${Loc.getMessage('CALENDAR_SHARING_DECISION_DECLINE_MEETING')}
					</div>
				</div>
			`;
		}

		if (this.#props.cancelledInfo)
		{
			const dayMonthFormat = DateTimeFormat.getFormat('DAY_MONTH_FORMAT');
			const shortTimeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
			const format = `${dayMonthFormat} ${shortTimeFormat}`;
			const dateFormatted = DateTimeFormat.format(format, this.#props.cancelledInfo.date.getTime() / 1000);

			const cancelledByEncoded = Text.encode(this.#props.cancelledInfo.name);
			const cancelledByText = `${Loc.getMessage('CALENDAR_SHARING_WHO_CANCELED')}: ${cancelledByEncoded}`;

			return Tag.render`
				<div class="calendar-pub__form-status">
					<div class="calendar-pub__form-status_text">
						${cancelledByText}<br> ${dateFormatted}
					</div>
				</div>
			`;
		}

		return '';
	}

	#renderBottomButtons(): HTMLElement
	{
		return Tag.render`
			<div>
				${this.#getBottomButtons()}
			</div>
		`;
	}

	#getBottomButtons(): HTMLElement[]
	{
		const buttons = [];

		if (this.#props.bottomButtons.onAcceptInvitation)
		{
			buttons.push(this.#renderAcceptButton());
		}

		if (this.#props.bottomButtons.onDeclineInvitation)
		{
			buttons.push(this.#renderDeclineButton());
		}

		if (this.#props.bottomButtons.onStartVideoconference)
		{
			buttons.push(this.#renderVideoconferenceButton());
		}

		if (this.#props.bottomButtons.onDownloadIcs)
		{
			buttons.push(this.#renderIcsButton());
		}

		if (this.#props.bottomButtons.onReturnToCalendar)
		{
			buttons.push(this.#renderReturnToCalendarButton());
		}

		return buttons;
	}

	#renderAcceptButton(): HTMLElement
	{
		this.#layout.acceptButton = this.#renderButton(
			Loc.getMessage('CALENDAR_SHARING_ACCEPT'),
			this.#onAcceptButtonClick.bind(this),
		);

		return this.#layout.acceptButton;
	}

	async #onAcceptButtonClick(): Promise
	{
		Dom.addClass(this.#layout.acceptButton, '--wait');
		await this.#props.bottomButtons.onAcceptInvitation();
		Dom.removeClass(this.#layout.acceptButton, '--wait');
	}

	#renderDeclineButton(): HTMLElement
	{
		this.#layout.declineButton = this.#renderButton(
			Loc.getMessage('CALENDAR_SHARING_DECLINE'),
			this.#onDeclineButtonClick.bind(this),
			'--light-border',
		);

		return this.#layout.declineButton;
	}

	async #onDeclineButtonClick(): Promise
	{
		Dom.addClass(this.#layout.declineButton, '--wait');
		await this.#props.bottomButtons.onDeclineInvitation();
		Dom.removeClass(this.#layout.declineButton, '--wait');
	}

	#renderVideoconferenceButton(): HTMLElement
	{
		this.#layout.videoconferenceButton = this.#renderButton(
			Loc.getMessage('CALENDAR_SHARING_OPEN_VIDEOCONFERENCE'),
			this.#onVideoconferenceButtonClick.bind(this),
		);

		return this.#layout.videoconferenceButton;
	}

	async #onVideoconferenceButtonClick(): Promise
	{
		Dom.addClass(this.#layout.videoconferenceButton, '--wait');
		await this.#props.bottomButtons.onStartVideoconference();
		Dom.removeClass(this.#layout.videoconferenceButton, '--wait');
	}

	#renderIcsButton(): HTMLElement
	{
		this.#layout.icsButton = this.#renderButton(
			Loc.getMessage('CALENDAR_SHARING_ADD_TO_CALENDAR'),
			this.#onIcsButtonClick.bind(this),
			'--light-border',
		);

		return this.#layout.icsButton;
	}

	async #onIcsButtonClick(): Promise
	{
		Dom.addClass(this.#layout.icsButton, '--wait');
		await this.#props.bottomButtons.onDownloadIcs();
		Dom.removeClass(this.#layout.icsButton, '--wait');
	}

	#renderReturnToCalendarButton(): HTMLElement
	{
		return this.#renderButton(
			Loc.getMessage('CALENDAR_SHARING_RETURN_TO_SLOT_LIST'),
			this.#onReturnButtonClick.bind(this),
			'--light-border',
		);
	}

	#onReturnButtonClick(): void
	{
		this.#props.bottomButtons.onReturnToCalendar();
	}

	#renderButton(text, action, className): HTMLElement
	{
		return Tag.render`
			<div
				onclick="${action}"
				class="calendar-pub-ui__btn ${className} --m calendar-pub-action-btn"
			>
				<div class="calendar-pub-ui__btn-text">${text}</div>
			</div>
		`;
	}

	showCancelEventPopup(): void
	{
		this.#getPopup().show();
	}

	#getPopup(): Popup
	{
		if (!this.popup)
		{
			const popupContent = Tag.render`
				<div>
					<div class="calendar-pub__cookies-title">${Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED')}</div>
					<div class="calendar-pub__cookies-info">${Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED_INFO')}</div>
					<div class="calendar-pub__cookies-buttons ${Browser.isMobile() ? '--center' : '--flex-end'}">
						<div onclick="${this.#closeCancelEventPopup.bind(this)}" class="calendar-pub-ui__btn --inline --m --light-border">
							<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_POPUP_LEAVE')}</div>
						</div>
						<div onclick="${this.#onDeleteButtonClick.bind(this)}" class="calendar-pub-ui__btn --inline --m --secondary">
							<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_POPUP_CANCEL')}</div>
						</div>
					</div>
				</div>
			`;

			if (Browser.isMobile())
			{
				this.popup = new BottomSheet({
					className: 'calendar-pub__state',
					content: popupContent,
					padding: '20px 25px',
				});
			}
			else
			{
				this.popup = new Popup({
					className: 'calendar-pub__popup',
					contentBackground: 'transparent',
					width: 380,
					animation: 'fading-slide',
					content: popupContent,
					overlay: true,
				});
			}
		}

		return this.popup;
	}

	#closeCancelEventPopup(): void
	{
		this.#getPopup().close();
	}

	async #onDeleteButtonClick(): void
	{
		this.#closeCancelEventPopup();
		this.#props.onDeleteEvent();
	}
}
