import {Tag} from 'main.core';
import {History} from './history';

export class StageChange extends History
{
	renderMain(): Element
	{
		let stageChange = this.renderStageChange();
		if(!stageChange)
		{
			stageChange = '';
		}

		let fieldsChange = this.renderFieldsChange();
		if(!fieldsChange)
		{
			fieldsChange = '';
		}

		return Tag.render`<div class="ui-item-detail-stream-content-detail">
			${stageChange}
			${fieldsChange}
		</div>`;
	}
}