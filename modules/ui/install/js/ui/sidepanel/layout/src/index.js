import './style.css';
import 'sidepanel';
import {Tag, Type, BaseError} from 'main.core';
import {CloseButton, CancelButton, BaseButton} from 'ui.buttons';

const UI = BX.UI;
const SidePanel = BX.SidePanel;

type DesignOptions = {
	margin: ?boolean;
	section: ?boolean;
	alignButtonsLeft: ?boolean;
};

type Options = {
	extensions: ?Array<string>;
	title: ?string;
	toolbar: ?Function;
	content: string|Element|Promise|BX.Promise;
	buttons: ?Function;
	design: ?DesignOptions;
};

function prepareOptions(options: Options = {})
{
	options = Object.assign({}, options);
	options.design = Object.assign({}, options.design || {});
	options.design = {
		margin: true,
		section: true,
		...options.design,
	};

	options.extensions = (options.extensions || []).concat([
		'ui.sidepanel.layout',
		'ui.buttons',
	]);
	if (options.toolbar)
	{
		options.extensions.push('ui.buttons.icons');
	}
	if (options.design.section)
	{
		options.extensions.push('ui.sidepanel-content');
	}

	return options;
}

export class Layout
{
	static createContent(options: Options = {})
	{
		options = prepareOptions(options);
		return top.BX.Runtime
			.loadExtension(options.extensions)
			.then(() => (new Layout(options)).render())
		;
	}

	#container;
	#options;

	constructor(options: Options = {})
	{
		this.#options = prepareOptions(options);
	}

	getContainer()
	{
		if (!this.#container)
		{
			this.#container = Tag.render`<div class="ui-sidepanel-layout"></div>`;
		}
		return this.#container;
	}

	render(content: string = '', promised: boolean = false)
	{
		if (this.#options.content && !promised)
		{
			content = this.#options.content();
			if (
				Object.prototype.toString.call(content) === "[object Promise]"
				|| (content.toString && content.toString() === "[object BX.Promise]")
			)
			{
				return content.then(content => this.render(content, true));
			}
		}

		const container = this.getContainer();
		container.innerHTML = '';

		// HEADER
		if (this.#options.title)
		{
			const title = Tag.safe`${this.#options.title}`;
			const header = Tag.render`
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">${title}</div>
				</div>
			`;

			if (Type.isFunction(this.#options.toolbar))
			{
				const toolbar = Tag.render`<div class="ui-sidepanel-layout-toolbar"></div>`;
				this.#options.toolbar({...UI}).forEach(button => {
					if (button instanceof BaseButton)
					{
						button.renderTo(toolbar)
					}
					else if (Type.isDomNode(button))
					{
						toolbar.appendChild(button);
					}
					else
					{
						throw BaseError('Wrong button type ' + button);
					}
				});
				header.appendChild(toolbar);
			}

			container.appendChild(header);
		}

		// CONTENT
		{
			const design = this.#options.design;
			const classes = ['ui-sidepanel-layout-content'];
			const styles = [];
			if (design.margin)
			{
				if (design.margin === true)
				{
					classes.push('ui-sidepanel-layout-content-margin');
				}
				else
				{
					styles.push('margin: ' + design.margin);
				}
			}
			let contentElement = Tag.render`<div class="${classes.join(' ')}" style="${styles.join('; ')}"></div>`;
			container.appendChild(contentElement);
			if (design.section)
			{
				contentElement.appendChild(Tag.render`<div class="ui-slider-section ui-sidepanel-layout-content-fill-height"></div>`);
				contentElement = contentElement.firstElementChild;
			}
			if (typeof content === 'string')
			{
				contentElement.innerHTML = content;
			}
			else if (content instanceof Element)
			{
				contentElement.appendChild(content);
			}
		}

		// FOOTER
		const isButtonsUndefined = typeof this.#options.buttons === 'undefined';
		if (typeof this.#options.buttons === 'function' || isButtonsUndefined)
		{
			const cancelButton = new CancelButton({onclick: () => SidePanel.Instance.close()});
			const closeButton = new CloseButton({onclick: () => SidePanel.Instance.close()});
			const defaults = {
				...UI,
				cancelButton,
				closeButton,
			};
			if (isButtonsUndefined)
			{
				this.#options.buttons = () => [closeButton];
			}

			const buttonList = this.#options.buttons(defaults);
			if (buttonList && buttonList.length > 0)
			{
				const footer = Tag.render`<div class="ui-sidepanel-layout-footer"></div>`;
				const classes = ['ui-sidepanel-layout-buttons'];
				if (this.#options.design.alignButtonsLeft)
				{
					classes.push('ui-sidepanel-layout-buttons-align-left');
				}
				const buttons = Tag.render`<div class="${classes.join(' ')}"></div>`;
				footer.appendChild(buttons);
				buttonList.forEach(button => {
					if (button instanceof BaseButton)
					{
						button.renderTo(buttons)
					}
					else if (Type.isDomNode(button))
					{
						buttons.appendChild(button);
					}
					else
					{
						throw BaseError('Wrong button type ' + button);
					}
				});
				container.appendChild(footer);
			}
		}

		return container;
	}
}