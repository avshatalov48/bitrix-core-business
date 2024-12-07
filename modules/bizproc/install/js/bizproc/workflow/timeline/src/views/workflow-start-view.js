import { Type, Tag, Dom } from 'main.core';
import type { TimelineData } from 'bizproc.workflow.timeline';
import { Timeline } from 'ui.timeline';

export class WorkflowStartView extends Timeline.Item
{
	#timelineData: TimelineData = {};

	constructor(props: TimelineData)
	{
		super({
			id: 'workflow-start',
			createdTimestamp: props.started,
			userId: props.startedBy,
			title: props.document.name,
		});

		this.setTimeFormat('j F H:i');
		this.setEventNamespace('BX.Bizproc.Workflow.Timeline.WorkflowStart');

		if (Type.isPlainObject(props))
		{
			this.#timelineData = props;
			this.setUserData(props.users);
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
