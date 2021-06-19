import { Type } from 'main.core';

export default class AjaxProcessor
{
	htmlWasInserted: boolean = false;
	scriptsLoaded:boolean = false;

	constructor()
	{

	}

	processCSS(block, callback: Function): void
	{
		if (
			Type.isArray(block.CSS)
			&& block.CSS.length > 0
		)
		{
			BX.load(block.CSS, callback);
		}
		else
		{
			callback();
		}
	};

	processExternalJS(block, callback: Function): void
	{
		if (
			Type.isArray(block.JS)
			&& block.JS.length > 0
		)
		{
			BX.load(block.JS, callback);
		}
		else
		{
			callback();
		}
	}

	processAjaxBlockInsertHTML(block, container: HTMLElement, callbackExternal: Function): void
	{
		container.appendChild(BX.create('DIV', {
			html: block.CONTENT
		}));

		this.htmlWasInserted = true;
		if (this.scriptsLoaded)
		{
			this.processInlineJS(block, callbackExternal);
		}
	}

	processInlineJS(block, callbackExternal: Function): void
	{
		this.scriptsLoaded = true;
		if (this.htmlWasInserted)
		{
			BX.ajax.processRequestData(block.CONTENT, {
				scriptsRunFirst: false,
				dataType: 'HTML'
			});
			callbackExternal();
		}
	}
}