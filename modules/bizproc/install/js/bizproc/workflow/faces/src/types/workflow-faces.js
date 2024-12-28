export type WorkflowFacesData = {
	workflowId: string,
	target: HTMLElement,
	targetUserId?: number, // positive, integer
	data: FacesData,
	showArrow?: boolean,
	showTimeStep?: boolean,
	subscribeToPushes?: boolean,
	isWorkflowFinished?: boolean,
};

export type FacesData = {
	steps: Array<StepData>,
	progressBox: ?{
		text: string,
		progressTasksCount: number,
	},
	timeStep: ?StepData,
};

export type StepData = {
	id: string,
	name: string,
	avatars: [],
	avatarsData: Array<Avatar>,
	duration: number | string,
	success: ?boolean,
	status: null | 'success' | 'wait' | 'not-success',
	taskId: number,
};

export type Avatar = {
	id: number,
	avatarUrl: ?string,
};
