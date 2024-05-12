import { Loc } from 'main.core';
import { EventLayout, Props as EventLayoutProps } from 'calendar.sharing.public-v2';
import { Util } from 'calendar.util';

type Action = 'accept' | 'decline' | 'ics';
type Status = 'Q' | 'Y' | 'N';

type User = {
	name: string,
	lastName: string,
	avatar: string,
	status: Status,
	isOwner: boolean,
};

type File = {
	link: string,
	name: string,
	size: number,
};

type Params = {
	container: HTMLElement,
	event: {
		id: number,
		hash: string,
		isDeleted: boolean,
		name: string,
		timestampFrom: number,
		timestampTo: number,
		timezone: string,
		isFullDay: boolean,
		location: string,
		description: string,
		files: File[],
		rruleDescription: string,
		members: User[],
	},
	title: string,
	isRu: boolean,
	action: Action,
};

export class PublicEvent
{
	#params: Params;

	#icsFile: string;

	constructor(params: Params)
	{
		this.#params = params;
		this.#handleAction(params.action);
	}

	#handleAction(action: Action): void
	{
		if (action === 'accept')
		{
			this.#handleDecisionAction('Y');
		}

		if (action === 'decline')
		{
			this.#handleDecisionAction('N');
		}

		if (action === 'ics')
		{
			this.#downloadIcsFile();
		}
	}

	render(): void
	{
		const eventLayout = new EventLayout(this.#getLayoutProps());
		this.#params.container.innerHTML = '';
		this.#params.container.append(eventLayout.render());
	}

	#getLayoutProps(): EventLayoutProps
	{
		if (!this.#params.event)
		{
			return {
				eventNotFound: {
					title: Loc.getMessage('CALENDAR_PUBLIC_EVENT_TITLE_NOT_ATTENDEES'),
					subtitle: Loc.getMessage('CALENDAR_PUBLIC_EVENT_DESCRIPTION_NOT_ATTENDEES'),
				},
			};
		}

		let offset = 0;
		if (this.#params.event.timezone)
		{
			offset = (Util.getTimeZoneOffset() - Util.getTimeZoneOffset(this.#params.event.timezone)) * 60 * 1000;
		}

		return {
			eventName: this.#params.event.name,
			from: new Date(parseInt(this.#params.event.timestampFrom) * 1000 + offset),
			to: new Date(parseInt(this.#params.event.timestampTo) * 1000 + offset),
			timezone: this.#params.event.timezone,
			browserTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
			isFullDay: this.#params.event.isFullDay,
			location: this.#params.event.location,
			description: this.#params.event.description,
			rruleDescription: this.#params.event.rruleDescription,
			members: this.#prepareMembers(),
			files: this.#params.event.files,
			allAttendees: true,
			filled: true,

			onDeclineEvent: this.#getStatus() === 'Y' ? this.#declineInvitation.bind(this) : null,

			title: this.#getTitle(),
			iconClassName: this.#getIconClassName(),
			bottomButtons: this.#getBottomButtons(),

			poweredLabel: {
				isRu: this.#params.isRu,
			},
		};
	}

	#prepareMembers(): User[]
	{
		if (this.#params.event.members?.length === 1 && this.#params.event.members[0].isOwner)
		{
			return [];
		}

		return [...this.#params.event.members].sort((member1, member2) => {
			const value1 = member1.isOwner ? 1 : 0;
			const value2 = member2.isOwner ? 1 : 0;

			return value2 - value1;
		});
	}

	#getTitle(): string
	{
		if (this.#params.event.isDeleted || !this.#getStatus())
		{
			return Loc.getMessage('CALENDAR_PUBLIC_EVENT_MEETING_IS_CANCELLED');
		}

		if (this.#getStatus() === 'Q')
		{
			return Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_WAS_INVITED');
		}

		if (this.#getStatus() === 'Y')
		{
			return Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_ACCEPTED_MEETING');
		}

		if (this.#getStatus() === 'N')
		{
			return Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_DECLINED_MEETING');
		}

		return '';
	}

	#getIconClassName(): string
	{
		if (this.#getStatus() === 'N' || !this.#getStatus() || this.#params.event.isDeleted)
		{
			return '--decline';
		}

		return '--accept';
	}

	#getBottomButtons(): any
	{
		if (this.#params.event.isDeleted)
		{
			return {};
		}

		if (this.#getStatus() === 'Q')
		{
			return {
				onAcceptInvitation: this.#acceptInvitation.bind(this),
				onDeclineInvitation: this.#declineInvitation.bind(this),
			};
		}

		if (this.#getStatus() === 'Y')
		{
			return {
				onDownloadIcs: this.#downloadIcsFile.bind(this),
			};
		}

		if (this.#getStatus() === 'N')
		{
			return {
				onAcceptInvitation: this.#acceptInvitation.bind(this),
			};
		}

		return {};
	}

	#acceptInvitation(): void
	{
		this.#handleDecisionAction('Y');
	}

	#declineInvitation(): void
	{
		this.#handleDecisionAction('N');
	}

	#handleDecisionAction(decision: Status): void
	{
		BX.ajax.runAction('calendar.api.publicevent.handleDecision', {
			data: {
				decision,
				eventId: this.#params.event.id,
				hash: this.#params.event.hash,
			},
		}).then((response) => {
			this.#updateStatus(response.data);
		});
	}

	#getStatus(): Status
	{
		const owner = this.#getOwner();

		return owner.status;
	}

	#updateStatus(status): void
	{
		const owner = this.#getOwner();
		owner.status = status;
		this.render();
	}

	#getOwner(): User
	{
		return this.#params.event.members.find((member) => member.isOwner) ?? {};
	}

	async #downloadIcsFile(): Promise
	{
		if (!this.#icsFile)
		{
			const response = await BX.ajax.runAction('calendar.api.publicevent.getIcsFileContent', {
				data: {
					eventId: this.#params.event.id,
					hash: this.#params.event.hash,
				},
			});
			this.#icsFile = response.data;
		}

		Util.downloadIcsFile(this.#icsFile, 'event');
	}
}