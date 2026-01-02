import { ajax } from 'main.core';

export function doTaskAction(data: FormData | {}, slider: BX.SidePanel, isLast: boolean): Promise
{
	BX.SidePanel.Instance.postMessage(window, 'try-do-bp-task-event', { workflowId: data.get('workflowId') });

	return new Promise((resolve, reject) => {
		ajax.runAction('bizproc.task.do', { data })
			.then((response) => {
				if (isLast)
				{
					BX.SidePanel.Instance.postMessage(slider, 'success-do-bp-task-event', { taskName: data.get('taskName') });
				}

				resolve(response);
			})
			.catch((response) => {
				BX.SidePanel.Instance.postMessage(slider, 'error-do-bp-task-event', { workflowId: data.get('workflowId') });
				reject(response);
			});
	});
}
