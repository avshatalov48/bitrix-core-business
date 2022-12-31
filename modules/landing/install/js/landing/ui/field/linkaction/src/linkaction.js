import {Type} from 'main.core';

export class Linkaction
{
	constructor()
	{
		console.log('BX.Landing.UI.Field.LinkActions');
		this.createEl = BX.Landing.UI.Field.Link.createElement();
	}

	static createElement()
	{
		return BX.create("div", {props: {className: "landing-ui-field-link-actions"}});
	}
}