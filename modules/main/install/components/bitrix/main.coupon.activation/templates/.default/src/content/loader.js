import { Loc, Tag } from 'main.core';
import BaseContent from './base-content';
import { Loader } from 'main.loader';

export class Loading extends BaseContent
{

	getContent()
	{
		const loaderNode = Tag.render`
			<div class="license-intranet-popup__loader"></div>
		`;

		const primaryColor = getComputedStyle(document.body).getPropertyValue('--ui-color-primary');

		const loader = new Loader({
			target: loaderNode,
			size: 133,
			color: primaryColor || '#2fc6f6',
		});

		loader.show();

		return Tag.render`
			<div class="license-intranet-popup__content --loader">
				${loaderNode}
				<div class="license-intranet-popup__loader-title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_PLEASE_WAIT')}</div>
			</div>
		`;
	}

	init(popup: Popup): void
	{
		popup.setContent(this.getContent());
		popup.setButtons(this.getButtonCollection());
	}
}