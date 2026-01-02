import { BaseField } from './base-field';
import { Tag, Type } from 'main.core';

export type MonthlySentCountFieldType = {
	monthlySentCount: number,
	monthlySentLimit: ?number,
}

export class MonthlySentCountField extends BaseField
{
	render(params: MonthlySentCountFieldType): void
	{
		if (Type.isNull(params.monthlySentLimit) || !params.monthlySentLimit > 0)
		{
			this.#renderCount(params.monthlySentCount);

			return;
		}

		this.#renderCountWithLimit(params.monthlySentCount, params.monthlySentLimit);
	}

	#renderCountWithLimit(monthlySentCount: number, monthlySentLimit: number): void
	{
		const percentagePrecision = 2;
		const percentageMultiplier = 100;

		const percent = (monthlySentCount / monthlySentLimit * percentageMultiplier).toFixed(percentagePrecision);

		const dailySentContainer = Tag.render`
			<div class="mailbox-grid_daily-sent-count-container">
				${monthlySentCount}/${monthlySentLimit} (${percent}%)
			</div>
		`;

		this.appendToFieldNode(dailySentContainer);
	}

	#renderCount(monthlySentCount: number): void
	{
		const monthlySentContainer = Tag.render`
			<div class="mailbox-grid_monthly-sent-count-container">
				${monthlySentCount}
			</div>
		`;

		this.appendToFieldNode(monthlySentContainer);
	}
}
