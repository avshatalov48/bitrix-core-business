import { TaskControls } from 'bizproc.task';
import { WorkflowId } from 'bizproc.types';
import { ajax } from 'main.core';

export type LoadWorkflowsResponseData = {
	data: {
		workflows: Array<WorkflowData>,
	},
};

export type WorkflowData = {
	workflowId: string,
	userId: number,
	startedById: number,
	startedBy?: string,
	taskProgress: {
		steps: [],
		timeStep: {},
		isWorkflowFinished: boolean,
		progressBox?: {},
	},
	name: string,
	description: string,
	typeName: string,
	statusText: string,
	modified: string,
	templateName?: string,
	workflowStarted: string,
	document: {
		url?: string,
		name: string,
	},
	task: Partial<{
		id: number,
		name: string,
		description: string,
		url: string,
		users: Array<{ id: number, status: number }>,
		status: number | string,
		controls: TaskControls,
		approveType: string,
		comments: number,
		modified: string,
		modifiedDate: number,
		overdueDate: string,
	}>,
	taskCnt: number,
	commentCnt: number,
	isCompleted: boolean,
	workflowUrl: ?string,
	workflowResult: ?string,
};

export class WorkflowLoader
{
	loadWorkflows(ids: WorkflowId[]): Promise<LoadWorkflowsResponseData, Array<{ message: string }>>
	{
		return this.#runComponentAction('loadWorkflows', { ids });
	}

	#runComponentAction(action: string, data: Object = {}): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction('bitrix:bizproc.user.processes', action, {
				mode: 'class',
				data,
			}).catch((response) => {
				reject(response);
			}).then((response) => {
				resolve(response);
			});
		});
	}
}
