export type WorkflowFacesData = {
	workflowId: string,
	target: HTMLElement,
	targetUserId: number, // positive, integer
	data: FacesData,
	showArrow: boolean,
	showTimeline: boolean,
	subscribeToPushes: boolean,
};

export type FacesData = {
	avatars: {
		author: Array<Avatar>,
		running: Array<Avatar>,
		completed: Array<Avatar>,
		done: Array<Avatar>,
	},
	statuses: {
		completedSuccess: boolean,
		doneSuccess: boolean,
	},
	time: {
		author: ?number,
		running: ?number,
		completed: ?number,
		done: ?number,
		total: ?number,
	},
	completedTaskCount: number,
	workflowIsCompleted: boolean,
	runningTaskId: number,
};

export type Avatar = {
	id: number,
	avatarUrl: ?string,
};
