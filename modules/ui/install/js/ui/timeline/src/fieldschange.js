import {Tag} from 'main.core';
import {History} from './history';

export class FieldsChange extends History
{
	renderMain(): Element
	{
		let fieldsChange = this.renderFieldsChange();
		if(!fieldsChange)
		{
			fieldsChange = '';
		}
		return Tag.render`<div class="ui-item-detail-stream-content-detail">
			${fieldsChange}
		</div>`;
	}
}