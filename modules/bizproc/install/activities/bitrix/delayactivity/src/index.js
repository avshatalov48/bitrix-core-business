const BizProcActivity = window.BizProcActivity;

export class DelayActivity extends BizProcActivity
{
	constructor()
	{
		super();
		this.Type = 'DelayActivity';

		this.CheckFields = this.#checkFields.bind(this);
	}

	#checkFields(): boolean
	{
		return !!this.Properties.TimeoutDuration || !!this.Properties.TimeoutTime;
	}
}
