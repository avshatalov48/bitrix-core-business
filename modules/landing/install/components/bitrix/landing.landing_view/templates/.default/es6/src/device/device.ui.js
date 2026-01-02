import { Dom, Tag } from 'main.core';
import { MenuItem, MenuManager } from 'main.popup';

import { DeviceItem } from './device.data';

type Options = {
	frameUrl: string,
	clickHandler: () => {},
	messages: {[type: string]: string},
};

class DeviceUI
{
	static messages: {[type: string]: string};

	/**
	 * Returns Landing Preview Block above the screen.
	 *
	 * @param {Options} options Preview options.
	 * @return {HTMLElement}
	 */
	static getPreview(options: Options): HTMLElement
	{
		if (options.messages)
		{
			DeviceUI.messages = options.messages;
		}

		if (!localStorage.getItem('deviceOrientation'))
		{
			localStorage.setItem('deviceOrientation', 'portrait');
		}

		const rotateClick = () => {
			if (localStorage.getItem('deviceOrientation') === 'portrait')
			{
				localStorage.setItem('deviceOrientation', 'landscape');
			}
			else
			{
				localStorage.setItem('deviceOrientation', 'portrait');
			}

			Dom.style(layout.wrapper, 'width', `${layout.wrapper.offsetHeight}px`);
			Dom.style(layout.wrapper, 'height', `${layout.wrapper.offsetWidth}px`);
			Dom.style(layout.frame, 'width', `${layout.frame.offsetHeight}px`);
			Dom.style(layout.frame, 'height', `${layout.frame.offsetWidth}px`);
			layout.wrapper.querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
		};

		const hidden = localStorage.getItem('deviceHidden') === 'true';

		const layout = {
			wrapper: null,
			rotate: Tag.render`<div class="landing-device-rotate" onclick="${rotateClick}" data-role="landing-device-rotate"></div>`,
			frame: Tag.render`<iframe data-role="landing-device-preview-iframe" src="${options.frameUrl}"></iframe>`,
		};

		layout.wrapper = Tag.render`
			<div class="landing-device-wrapper${hidden ? ' landing-device-wrapper-hidden' : ''}">
				<div class="landing-device-name" onclick="${options.clickHandler}">
					<span data-role="device-name">Device</span>
					<span data-role="device-orientation" class="landing-device-orientation">Orientation</span>
				</div>
				${layout.rotate}
				<div class="landing-device-preview" data-role="landing-device-preview">
					${layout.frame}
				</div>
			</div>
		`;

		return layout.wrapper;
	}

	/**
	 * Creates and open menu with list of devices.
	 *
	 * @param {HTMLElement} bindElement HTML element to bind position of menu.
	 * @param {Array<DeviceItem>} devices List of devices.
	 * @param {(device: DeviceItem) => {}} clickHandler Invokes when user clicked on the menu item.
	 */
	static openDeviceMenu(bindElement: HTMLElement, devices: Array<DeviceItem>, clickHandler: (device: DeviceItem) => {})
	{
		const menuId = 'device_selector';
		let menu = MenuManager.getMenuById(menuId);

		if (menu)
		{
			menu.show();

			return;
		}

		const menuItems = [];

		devices.forEach((device) => {
			if (device.code === 'delimiter')
			{
				menuItems.push(new MenuItem({
					delimiter: true,
					text: device.langCode ? DeviceUI.messages[device.langCode] : '',
				}));

				return;
			}
			menuItems.push(new MenuItem({
				id: device.className,
				html: String(device.name),
				onclick: () => {
					MenuManager.getMenuById(menuId).close();
					clickHandler(device);
				},
			}));
		});

		const bindNode = bindElement.parentNode || document.body;

		menu = MenuManager.create({
			id: menuId,
			bindElement: bindNode,
			className: 'landing-ui-block-actions-popup',
			items: menuItems,
			offsetTop: 0,
			offsetLeft: 40,
			minWidth: bindNode.offsetWidth,
			animation: 'fading-slide',
			events: {
				onPopupShow: () => {
					menu.getPopupWindow().setMinWidth(bindNode.offsetWidth);
				},
			},
		});

		menu.show();
	}
}

export default DeviceUI;
