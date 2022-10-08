import {BaseCard} from 'landing.ui.card.basecard';
import {Dom, Tag, Type} from 'main.core';

import 'ui.fonts.opensans';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Card
 */
export class HeaderCard extends BaseCard
{
	constructor(options)
	{
		super(options);
		Dom.addClass(this.getLayout(), 'landing-ui-card-headercard');

		if (options.bottomMargin === false)
		{
			this.setBottomMargin(options.bottomMargin);
		}

		if (Type.isNumber(options.level))
		{
			Dom.addClass(this.getLayout(), `landing-ui-card-headercard-${options.level}`);
		}

		if (Type.isStringFilled(options.description))
		{
			this.setDescription(options.description);
		}
	}

	getDescriptionLayout(): HTMLSpanElement
	{
		return this.cache.remember('descriptionLayout', () => {
			return Tag.render`
				<span class="landing-ui-card-headercard-description"></span>
			`;
		});
	}

	setDescription(descriptionText: string)
	{
		const descriptionLayout = this.getDescriptionLayout();
		if (!this.body.contains(descriptionLayout))
		{
			Dom.append(descriptionLayout, this.body);
		}

		descriptionLayout.textContent = descriptionText;
	}

	setBottomMargin(value)
	{
		if (value === true)
		{
			Dom.removeClass(this.getLayout(), 'landing-ui-card-headercard-without-bottom-margin');
		}
		else
		{
			Dom.addClass(this.getLayout(), 'landing-ui-card-headercard-without-bottom-margin');
		}
	}
}