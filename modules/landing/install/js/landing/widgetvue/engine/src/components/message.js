import { Loc } from 'main.core';
import LoaderImage from '../images/emptystate.svg';

export const Message = {
	props: {
		message: {
			type: String,
			default: Loc.getMessage('LANDING_WIDGETVUE_LOADER_DEFAULT_MESSAGE'),
		},
		link: {
			type: String,
			default: null,
		},
		linkText: {
			type: String,
			default: Loc.getMessage('LANDING_WIDGETVUE_ERROR_DEFAULT_LINK_TEXT'),
		},
	},

	template: `
		<div class="w-loader">
			<div class="w-loader-icon"></div>
			<div class="w-loader-text">
				<div>{{message}}</div>
			</div>
		</div>
	`,
};
