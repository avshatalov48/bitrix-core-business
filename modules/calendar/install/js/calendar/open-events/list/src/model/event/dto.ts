type EventDto = {
	id: number;
	name: string;
	dateFromTs: number;
	dateToTs: number;
	commentsCount: number;
	isAttendee: boolean;
	attendeesCount: number;
	creatorId: number;
	eventOptions: {
		maxAttendees: number;
	};
	categoryId: number;
	categoryName: string;
	color: string;
	categoryChannelId: number;
	threadId: number;
	isNew: boolean;
	rrule: string;
	rruleDescription: string;
	exdate: string;
};

type DateRange = {
	from: Date,
	to: Date,
};

type RRule = {
	FREQ: string,
	BYDAY: string,
	INTERVAL: string,
	COUNT: string,
	UNTIL: string,
	num?: number,
	amount?: number,
};
