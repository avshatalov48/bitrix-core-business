import { ajax } from 'main.core';

export function startWorkflowAction(data: FormData | {}): Promise
{
	return new Promise((resolve, reject) => {
		ajax.runAction('bizproc.workflow.starter.startWorkflow', { data })
			.then((response) => {
				const slider = BX.SidePanel.Instance.getSliderByWindow(window);
				if (slider)
				{
					const dictionary: BX.SidePanel.Dictionary = slider.getData();
					dictionary.set('data', { workflowId: response.data.workflowId });
				}

				resolve(response);
			})
			.catch(reject)
		;
	});
}
