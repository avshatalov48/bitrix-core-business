import { EventEmitter } from 'main.core.events';
import { Type, Tag, Dom } from 'main.core';
import 'ui.hint';



export default class StepByStepItem extends EventEmitter
{
	constructor(options = {}, number)
	{
		super();

		this.header = options?.header;
		this.node = options?.node;
		this.number = number;
		this.isFirst = options?.isFirst || '';
		this.isLast = options?.isLast || ''
		this.class = Type.isString(options?.nodeClass) ? options.nodeClass : null;
		this.backgroundColor = Type.isString(options?.backgroundColor) ? options.backgroundColor : null;
		this.layout = {
			container: null
		};
	}

	getHeader()
	{
		if (Type.isString(this.header))
		{
			return Tag.render`
				<div class="ui-stepbystep__section-item--title">${this.header}</div>
			`;
		}

		if (Type.isObject(this.header))
		{

			let titleWrapper = Tag.render`
				<div class="ui-stepbystep__section-item--title">

				</div>
			`;

			if (this.header.title)
			{
				titleWrapper.innerText = this.header.title;
			}

			if (Type.isString(this.header.hint))
			{
				let hintNode = Tag.render`
					<span data-hint="${this.header.hint}" class="ui-hint ui-stepbystep__section-item--hint">
						<i class="ui-hint-icon"></i>
					</span>
				`;

				titleWrapper.appendChild(hintNode);

				this.initHint(titleWrapper);
			}

			return titleWrapper;
		}

		return '';
	}


	initHint(node: HTMLElement)
	{
		BX.UI.Hint.init(node);
	}

	getContent()
	{

		if (this.node)
		{
			return Tag.render`
				<div class="ui-stepbystep__section-item--content">
					${this.node}
				</div>
			`;
		}

		return '';
	}

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-stepbystep__section-item">
					<div class="ui-stepbystep__section-item--counter">
						<div class="ui-stepbystep__section-item--counter-number ${this.isFirst} ${this.isLast}">
							<span>${this.number}</span>
						</div>
					</div>
					<div class="ui-stepbystep__section-item--information">
						${this.getHeader()}
						${this.getContent()}
					</div>
				</div>
			`;

			if (this.backgroundColor)
			{
				this.layout.container.style.backgroundColor = this.backgroundColor;
			}

			if (this.class)
			{
				this.layout.container.classList.add(this.class);
			}
		}

		return this.layout.container;
	}
}