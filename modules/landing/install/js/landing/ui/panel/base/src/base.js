import {Type, Text, Tag, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import './css/style.css';
import 'landing.utils';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class BasePanel extends EventEmitter
{
	static makeId(): string
	{
		return `landing_ui_panel_${Text.getRandom()}`;
	}

	static createLayout(id)
	{
		return Tag.render`
			<div class="landing-ui-panel landing-ui-hide" data-id="${id}"></div>
		`;
	}

	constructor(id = null)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.BasePanel');
		this.id = Type.isString(id) ? id : BasePanel.makeId();
		this.layout = BasePanel.createLayout(this.id);
		this.classShow = 'landing-ui-show';
		this.classHide = 'landing-ui-hide';
		this.forms = new BX.Landing.UI.Collection.FormCollection();
		this.contextDocument = document;
		this.contextWindow = this.contextDocument.defaultView;
	}

	// eslint-disable-next-line no-unused-vars
	show(options?: any): Promise<any>
	{
		if (!this.isShown())
		{
			return BX.Landing.Utils.Show(this.layout);
		}

		return Promise.resolve();
	}

	hide(): Promise<any>
	{
		if (this.isShown())
		{
			return BX.Landing.Utils.Hide(this.layout);
		}

		return Promise.resolve();
	}

	isShown(): boolean
	{
		return !Dom.hasClass(this.layout, this.classHide);
	}

	setContent(content: string)
	{
		this.clear();

		if (Type.isString(content))
		{
			this.layout.innerHTML = content;
		}
		else if (Type.isDomNode(content))
		{
			this.appendContent(content);
		}
		else if (Type.isArray(content))
		{
			content.forEach(this.appendContent, this);
		}
	}

	appendContent(content: HTMLElement)
	{
		if (Type.isDomNode(content))
		{
			this.layout.appendChild(content);
		}
	}

	prependContent(content: HTMLElement)
	{
		if (Type.isDomNode(content))
		{
			Dom.prepend(content, this.layout);
		}
	}

	renderTo(target: HTMLElement)
	{
		if (Type.isDomNode(target))
		{
			Dom.append(this.layout, target);
		}
	}

	remove()
	{
		Dom.remove(this.layout);
	}

	appendForm(form)
	{
		this.layout.appendChild(form.getNode());
	}

	clear()
	{
		Dom.clean(this.layout);
	}

	setLayoutClass(className: string)
	{
		Dom.addClass(this.layout, className);
	}

	setContextDocument(contextDocument: Document)
	{
		this.contextDocument = contextDocument;
		this.contextWindow = this.contextDocument.defaultView;
	}
}