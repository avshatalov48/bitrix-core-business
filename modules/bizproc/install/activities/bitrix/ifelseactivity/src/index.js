const ParallelActivity = window.ParallelActivity;

export class IfElseActivity extends ParallelActivity
{
	constructor()
	{
		super();
		this.Type = 'IfElseActivity';

		this.allowSort = true;
		this.childActivities = [];
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private, no-underscore-dangle
		this.__parallelActivityInitType = 'IfElseBranchActivity';
	}
}
