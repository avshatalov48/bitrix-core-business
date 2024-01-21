import {Event, Dom} from "main.core";
import 'ui.design-tokens';
import 'ui.forms';
import {EventEmitter} from 'main.core.events';
import './css/style.css';

type LayoutOptions = {
	container?: HTMLElement
}

export class LayoutForm extends EventEmitter
{
	static HIDDEN_ATTRIBUTE = 'data-form-row-hidden';
	static SHOW_CLASS = 'ui-form-row-hidden-show';
	static CHECKBOX_SELECTOR = '.ui-ctl-element[type="checkbox"]';

	params: LayoutOptions;
	container: HTMLElement;
	nodes: [HTMLElement];

	constructor(params: ?LayoutOptions)
	{
		super();
		this.setEventNamespace('BX.UI.LayoutForm');

		this.params = params ?? {};
		this.container = this.params.container ?? document.documentElement;

		this.nodes = null;

		this.init();
	}

	init(): void
	{
		this.nodes = [].slice.call(this.container.querySelectorAll('[' + LayoutForm.HIDDEN_ATTRIBUTE + ']'));
		this.nodes.forEach(node =>
		{
			Event.bind(node, "click", event => {
				event.preventDefault();
				this.toggleBLock(node);
				this.emit('onToggle', {
					checkbox: node.querySelector(LayoutForm.CHECKBOX_SELECTOR),
				});
			});
			node.querySelector(LayoutForm.CHECKBOX_SELECTOR).style.pointerEvents = 'none';
			this.checkInitialBlockVisibility(node);
		});
	}

	checkInitialBlockVisibility(node: HTMLElement): void
	{
		const checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);
		if (checkbox && checkbox.checked)
		{
			const content = node.nextElementSibling;
			if (content)
			{
				content.style.height = 'auto';
				Dom.addClass(content, LayoutForm.SHOW_CLASS);
			}
		}
	}

	toggleBLock(node: HTMLElement): void
	{
		const checkbox = node.querySelector(LayoutForm.CHECKBOX_SELECTOR);
		if (checkbox)
		{
			const content = node.nextElementSibling;
			if (content)
			{
				const height = content.scrollHeight;
				if (height > 0)
				{
					if (!checkbox.checked)
					{
						checkbox.checked = true;
						content.style.height = height + 'px';
						Dom.addClass(content, LayoutForm.SHOW_CLASS);
						const onTransitionEnd = () =>
						{
							content.style.height = 'auto';
							Event.unbind(content, 'transitionend', onTransitionEnd);
						};
						Event.bind(content, 'transitionend', onTransitionEnd);
					}
					else
					{
						checkbox.checked = false;
						content.style.height = height + 'px';
						requestAnimationFrame(() => {
							content.style.height = 0;
							Dom.removeClass(content, LayoutForm.SHOW_CLASS);
						});
					}
				}
			}
		}
	}
}
