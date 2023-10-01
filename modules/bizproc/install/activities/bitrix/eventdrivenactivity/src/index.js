import { Dom } from 'main.core';

const SequentialWorkflowActivity = window.SequentialWorkflowActivity;

export class EventDrivenActivity extends SequentialWorkflowActivity
{
	constructor()
	{
		super();
		this.Type = 'EventDrivenActivity';
		this.DrawSequentialWorkflowActivity = this.Draw;
		this.Draw = this.#draw.bind(this);
		this.AfterSDraw = this.#afterSequenceDraw.bind(this);
		this.SetError = this.#setError.bind(this);
	}

	#draw(wrapper)
	{
		if (this.parentActivity.Type === 'StateActivity')
		{
			this.DrawSequentialWorkflowActivity(wrapper);
		}
		else
		{
			this.DrawSequenceActivity(wrapper);
		}
	}

	#afterSequenceDraw()
	{
		if (this.parentActivity.Type === 'StateActivity' && this.childsContainer.rows.length > 2)
		{
			Dom.style(this.childsContainer.rows[0], 'display', 'none');
			Dom.style(this.childsContainer.rows[1], 'display', 'none');
		}
	}

	#setError(hasError, setFocus)
	{
		this.parentActivity.SetError(hasError, setFocus);
	}
}
