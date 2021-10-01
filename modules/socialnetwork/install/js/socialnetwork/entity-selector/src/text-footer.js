import {DefaultFooter} from "ui.entity-selector";
import {Tag} from "main.core";

export default class TextFooter extends DefaultFooter
{
	render(): HTMLElement
	{
		return Tag.render`
			<div class="ui-selector-footer-default ui-selector-footer-long-text">
				${this.getContent() ? this.getContent() : '' }
			</div>
		`;
	}
}
