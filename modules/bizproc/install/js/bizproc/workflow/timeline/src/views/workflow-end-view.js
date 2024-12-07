import { TimelineData } from 'bizproc.workflow.timeline';
import { Dom, Tag, Type } from 'main.core';
import { Timeline } from 'ui.timeline';

export class WorkflowEndView extends Timeline.Item
{
	#timelineData: TimelineData;

	constructor(props: TimelineData)
	{
		super({
			id: 'workflow-end',
			userId: props.startedBy,
			title: props.document.name,
		});

		this.setIsLast(true);
		this.setTimeFormat('j F H:i');
		this.setEventNamespace('BX.Bizproc.Workflow.Timeline.WorkflowEnd');

		if (Type.isPlainObject(props))
		{
			this.#timelineData = props;
			this.setUserData(props.users);

			const lastTask = this.#timelineData.tasks.at(-1);
			this.createdTimestamp = lastTask?.modified;
		}
	}

	render(): Element
	{
		this.layout.container = this.renderContainer();

		Dom.append(this.renderIcon(), this.layout.container);
		Dom.append(this.renderContent(), this.layout.container);

		return this.layout.container;
	}

	renderMain(): Element
	{
		this.layout.main = Tag.render`
			<div></div>
		`;

		return this.layout.main;
	}
}
