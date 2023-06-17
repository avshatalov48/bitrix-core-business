import { Loc, Tag } from 'main.core';
import BaseContent from './base-content';
import { Loader } from 'main.loader';

export class Success extends BaseContent
{

	getContent()
	{
		return Tag.render`
			<div id="intranet-license-partner-form">
				<div class="license-intranet-popup__content --partner-success">
					<div class="intranet-license-partner-form__success-icon"></div>
					<div class="license-intranet-popup__title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER_SUCCESS')}</div>
				</div>
			</div>
		`
	}

	init(popup: Popup): void
	{
		popup.setContent(this.getContent());
		popup.setButtons(this.getButtonCollection());
	}
}