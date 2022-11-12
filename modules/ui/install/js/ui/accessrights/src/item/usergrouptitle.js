import {Tag, Text} from "main.core";
import Title from "./title";

export default class UserGroupTitle extends Title
{
	static TYPE = 'userGroupTitle';

	render(): HTMLElement
	{
		return Tag.render`
			<div 
				class='ui-access-rights-column-item-text'
				data-id='${this.getId()}'
			>
				${Text.encode(this.text)}
			</div>
		`;
	}
}