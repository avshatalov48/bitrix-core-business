import './css/rich-menu.css';

// @vue/component
export const RichMenu = {
	name: 'RichMenu',
	template: `
		<div class="ui-rich-menu__container">
			<slot name="header"></slot>
			<slot></slot>
			<slot name="footer"></slot>
		</div>
	`,
};
