import { Tag } from 'main.core';
import './css/style.css';
import 'ui.buttons';
import 'ui.forms';
import 'ui.fonts.opensans';

export class SidePanelWrapper
{
	static open(config = {
		id: '',
		content: '',
		titleText: '',
		footerIsActive: false,
		cancelButton: {},
		consentButton: {
			function: () => {
			},
		},
	})
	{
		let wrapper = Tag.render`<div class="mail-slider-wrapper"></div>`;
		let header = Tag.render`<div class="mail-slider-wrapper-header"></div>`;
		let title = Tag.render`
			<div class="mail-slider-wrapper-header-title">
				${config['titleText']}
			</div>
		`;
		let footer = Tag.render`<div></div>`;

		if (config['footerIsActive'])
		{
			footer = Tag.render`<div class="mail-slider-wrapper-footer-fixed"></div>`;

			if (config['consentButton'] !== undefined)
			{
				let consentButton = new BX.UI.Button({
					text: config['consentButton']['text'],
					color: BX.UI.Button.Color.SUCCESS,
					events: {},
					onclick: function() {
						config['consentButton']['function'](consentButton);
					},
				});
				footer.append(consentButton.getContainer());
			}

			if (config['cancelButton'] !== undefined)
			{
				let cancelButton = Tag.render`
					<button class="ui-btn ui-btn-md ui-btn-link">
						${config['cancelButton']['text']}
					</button>
				`;

				cancelButton.onclick = () => {
					cancelButton.onclick = () => {
					};

					BX.SidePanel.Instance.close();
				};

				footer.append(cancelButton);
			}
		}

		let content = Tag.render`<div class="mail-slider-wrapper-content"></div>`;

		if (typeof config['content'] === "string")
		{
			content = Tag.render`
				<div class="mail-slider-wrapper-content">
					${config['content']}
				</div>
			`;
		}
		else
		{
			content.append(config['content']);
		}

		header.append(title);
		wrapper.append(header);
		wrapper.append(content);
		wrapper.append(footer);

		BX.SidePanel.Instance.open(config['id'], {
			id: config['id'],
			contentCallback: () => new Promise(resolve => resolve(wrapper)),
			width: 735,
			cacheable: false,
		});
	}
}