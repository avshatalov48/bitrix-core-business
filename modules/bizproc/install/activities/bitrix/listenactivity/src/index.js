const ParallelActivity = window.ParallelActivity;

export class ListenActivity extends ParallelActivity
{
	constructor()
	{
		super();
		this.Type = 'ListenActivity';
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
		this.__parallelActivityInitType = 'EventDrivenActivity';
	}
}
