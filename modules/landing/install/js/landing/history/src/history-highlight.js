import {PageObject} from 'landing.pageobject';
import {Highlight as HighlightNode} from 'landing.ui.highlight';

export default class Highlight extends HighlightNode
{
	constructor()
	{
		super();
		this.layout.classList.add('landing-ui-highlight-animation');
		this.animationDuration = 300;
	}

	static getInstance()
	{
		const rootWindow = PageObject.getRootWindow();
		if (!rootWindow.BX.Landing.History.Highlight.instance)
		{
			rootWindow.BX.Landing.History.Highlight.instance = new Highlight();
		}

		return rootWindow.BX.Landing.History.Highlight.instance;
	}

	show(element, rect): Promise<any>
	{
		BX.Landing.UI.Highlight.prototype.show.call(this, element, rect);

		return new Promise(((resolve) => {
			setTimeout(resolve, this.animationDuration);
			this.hide();
		}));
	}
}