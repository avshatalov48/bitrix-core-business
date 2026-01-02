import { BaseField } from './base-field';
import { Tag, Type } from 'main.core';

export type DailySentCountFieldType = {
	dailySentCount: number,
	dailySentLimit: ?number,
}

export class DailySentCountField extends BaseField
{
	render(params: DailySentCountFieldType): void
	{
		if (Type.isNull(params.dailySentLimit))
		{
			this.#renderCount(params.dailySentCount);

			return;
		}

		this.#renderCountWithLimit(params.dailySentCount, params.dailySentLimit);
	}

	#renderCountWithLimit(dailySentCount: number, dailySentLimit: number): void
	{
		const dailySentContainer = Tag.render`
			<div class="mailbox-grid_daily-sent-count-container">
				${dailySentCount}/${dailySentLimit}
			</div>
		`;

		this.appendToFieldNode(dailySentContainer);
	}

	#renderCount(dailySentCount: number): void
	{
		const dailySentContainer = Tag.render`
			<div class="mailbox-grid_daily-sent-count-container">
				${dailySentCount}
			</div>
		`;

		this.appendToFieldNode(dailySentContainer);
	}
}
