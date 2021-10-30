import {Dom, Runtime, Tag, Type} from 'main.core';
import {BaseForm} from 'landing.ui.form.baseform';

import './css/card_form.css';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class CardForm extends BaseForm
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Form.CardForm');
		Dom.addClass(this.layout, 'landing-ui-form-card');

		this.onItemClick = Runtime.throttle(this.onItemClick, 200, this);
		this.onRemoveItemClick = this.onRemoveItemClick.bind(this);

		this.wrapper = this.getWrapper();

		this.labelBindings = options.labelBindings;
		this.preset = options.preset;
		[, this.oldIndex] = this.selector.split('@');
	}

	getWrapper(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-form-cards-item">
				<div class="landing-ui-form-cards-item-inner">
					<div class="landing-ui-form-card-item-header" onclick="${this.onItemClick}">
						<div class="landing-ui-form-card-item-header-left">
							<div class="landing-ui-form-card-item-header-left-inner">
								<span class="landing-ui-form-card-item-header-drag landing-ui-drag"></span>
								<span class="landing-ui-form-card-item-header-title">${this.label}</span>
							</div>
							<div class="landing-ui-form-card-item-header-edit">
								<span class="fa fa-pencil"></span>
							</div>
						</div>
						<div class="landing-ui-form-card-item-header-right">
							<div 
								class="landing-ui-form-card-item-header-remove"
								onclick="${this.onRemoveItemClick}"
							>
								<span class="fa fa-remove"></span>
							</div>
						</div>
					</div>
					${this.getNode()}
				</div>
			</div>
		`;
	}

	// eslint-disable-next-line class-methods-use-this
	onItemClick(event: MouseEvent)
	{
		event.preventDefault();

		if (Type.isDomNode(event.currentTarget))
		{
			const target = event.currentTarget.closest('.landing-ui-form-cards-item');
			if (!Dom.hasClass(target, 'landing-ui-form-cards-item-expand'))
			{
				Dom.addClass(target, 'landing-ui-form-cards-item-expand');

				BX.Landing.Utils.onTransitionEnd(target).then(() => {
					Dom.style(target, {
						overflow: 'visible',
					});
				});

				Dom.style(target, {
					height: 'auto',
				});
			}
			else
			{
				Dom.removeClass(target, 'landing-ui-form-cards-item-expand');
				Dom.style(target, null);
			}
		}
	}

	onRemoveItemClick(event: MouseEvent)
	{
		event.preventDefault();
		event.stopPropagation();
		if (!this.getLayout().closest('.landing-ui-disallow-remove'))
		{
			Dom.remove(this.wrapper);
			this.emit('onRemove');
		}
	}

	serialize(): {[key: string]: any}
	{
		return this.fields
			.reduce((res, field) => {
				const [index] = field.selector.split('@');
				res[index] = field.getValue();
				return res;
			}, {});
	}

	getPreset()
	{
		return this.preset || null;
	}
}