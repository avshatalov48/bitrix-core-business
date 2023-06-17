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
	 * @return {HTMLDivElement}
	 */
	static getPreview(options: Options): HTMLDivElement
	{
		if (options.messages)
		{
			DeviceUI.messages = options.messages;
		}

		if (!localStorage.getItem('deviceOrientation'))
		{
			localStorage.setItem('deviceOrientation', 'portrait');
		}

		const switcherClick = (event) => {
			localStorage.setItem('deviceHidden', !Dom.hasClass(layout.switcher, 'landing-switcher-hide'));
			Dom.toggleClass(layout.switcher, 'landing-switcher-hide');
			Dom.toggleClass(layout.wrapper, 'landing-device-wrapper-hidden');
		};

		const rotateClick = (event) => {
			if (localStorage.getItem('deviceOrientation') === 'portrait')
			{
				localStorage.setItem('deviceOrientation', 'landscape');
			}
			else
			{
				localStorage.setItem('deviceOrientation', 'portrait');
			}

			layout.wrapper.style.setProperty(`width`, `${layout.wrapper.offsetHeight}px`);
			layout.wrapper.style.setProperty(`height`, `${layout.wrapper.offsetWidth}px`);
			layout.frame.style.setProperty(`width`, `${layout.frame.offsetHeight}px`);
			layout.frame.style.setProperty(`height`, `${layout.frame.offsetWidth}px`);
			layout.wrapper.querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
		};

		const hidden = localStorage.getItem('deviceHidden') === 'true';

		const layout = {
			wrapper: Tag.render`
				<div class="landing-device-wrapper${hidden ? ' landing-device-wrapper-hidden' : ''}">
					<div class="landing-device-name" onclick="${options.clickHandler}">
						<span data-role="device-name">Device</span>
						<span data-role="device-orientation" class="landing-device-orientation">Orientation</span>
					</div>
				</div>
			`,
			switcher: Tag.render`<div class="landing-device-switcher${hidden ? ' landing-switcher-hide' : ''}" onclick="${switcherClick}" data-role="landing-device-switcher"></div>`,
			rotate: Tag.render`<div class="landing-device-rotate" onclick="${rotateClick}" data-role="landing-device-rotate"></div>`,
			frame: Tag.render`<iframe data-role="landing-device-preview-iframe" src="${options.frameUrl}"></iframe>`,
			frameWrapper: Tag.render`<div class="landing-device-preview" data-role="landing-device-preview"></div>`
		};

		layout.wrapper.appendChild(layout.switcher);
		layout.wrapper.appendChild(layout.rotate);
		layout.wrapper.appendChild(layout.frameWrapper);
		layout.frameWrapper.appendChild(layout.frame);

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

		devices.map((device) => {
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
				html: `${device.name}`,
				onclick: () => {
					MenuManager.getMenuById(menuId).close();
					clickHandler(device);
				}
			}));
		});

		if (bindElement)
		{
			bindElement = bindElement.parentNode;
		}

		menu = MenuManager.create({
			id: menuId,
			bindElement,
			className: 'landing-ui-block-actions-popup',
			items: menuItems,
			angle: true,
			offsetTop: 0,
			offsetLeft: 40,
			minWidth: bindElement.offsetWidth,
			animation: 'fading-slide'
		});

		menu.show();
	}
}

export default DeviceUI;
