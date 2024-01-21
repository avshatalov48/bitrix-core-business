import { Dom, Tag, Event } from 'main.core';
import { Loc } from '../controls/controls.loc';

import { Devices, DeviceItem } from './device.data';
import DeviceUI from './device.ui';

type Options = {
	editorFrameWrapper: HTMLElement,
	frameUrl: string,
	messages: {[type: string]: string},
};

export class Device
{
	#options;
	#frameUrl: string;
	#editorFrameWrapper: HTMLElement;
	#previewElement: HTMLDivElement;
	#previewWindow;// window object of iframe
	#previewLoader: HTMLDivElement;
	#currentDevice: ?DeviceItem = null;
	#editorEnabled: boolean = false;
	#pendingReload: boolean = false;
	#commandsToRefresh = [
		'Landing::upBlock',
		'Landing::downBlock',
		'Landing::showBlock',
		'Landing::hideBlock',
		'Landing::markDeletedBlock',
		'Landing::addBlock',
		'Landing::copyBlock',
		'Landing::moveBlock',
		'Block::changeNodeName',
		'Block::updateContent',
		'Block::getContent',
		'Landing\\Block::addCard',
		'Landing\\Block::cloneCard',
		'Landing\\Block::removeCard',
		'Landing\\Block::updateNodes',
		'Landing\\Block::updateStyles',
		'Landing\\Block::saveForm',// fake-action
	];

	/**
	 * Device constructor.
	 *
	 * @param {Options} options Constructor options.
	 */
	constructor(options: Options)
	{
		this.#frameUrl = options.frameUrl;
		this.#editorFrameWrapper = options.editorFrameWrapper;
		this.#options = options;
		this.#registerListeners(options);
		this.#buildPreview(options);

		this.#showPreview();
		this.#setDevice(this.#resolveDeviceByType('mobile'));
	}

	/**
	 * Registers Handlers you need.
	 *
	 * @param {Options} options Constructor options.
	 */
	#registerListeners(options: Options)
	{
		// when user click different window size
		BX.addCustomEvent('BX.Landing.Main:editorSizeChange', (deviceType: string) =>
		{
			this.#setDevice(this.#resolveDeviceByType(deviceType));
		});

		// listen messages from editor frame
		window.addEventListener('message', event => {
			const data = event.data || {};

			if (data.action === 'editorenable')
			{
				if (!!data.payload.enable)
				{
					this.#editorEnabled = true;
				}
				else
				{
					if (this.#pendingReload)
					{
						this.#reloadPreviewWindow();
					}
					this.#editorEnabled = false;
					this.#pendingReload = false;
				}
			}
			else if (data.action === 'backendaction')
			{
				this.#backendAction(data.payload);
			}
		});
	}

	/**
	 * Invokes when backend request occurred.
	 *
	 * @param {{action: string, data: Object}} payload Payload data.
	 */
	#backendAction(payload: {action: string, data: Object})
	{
		if (this.#commandsToRefresh.includes(payload.action))
		{
			if (this.#editorEnabled)
			{
				this.#pendingReload = true;
			}
			else
			{
				let blockId = null;

				if (payload.data?.block)
				{
					blockId = payload.data?.block;
				}

				if (payload.data?.updateNodes?.data?.block)
				{
					blockId = payload.data?.updateNodes?.data?.block;
				}

				this.#reloadPreviewWindow(blockId);
			}
		}
	}

	/**
	 * Reloads preview window.
	 * @param {number} blockId
	 */
	#reloadPreviewWindow(blockId: ?number)
	{
		if (this.#previewWindow)
		{
			const blockIdPrefix = 'editor';
			const timestamp = Date.now();

			this.#previewWindow.location.href = this.#frameUrl + '?ts=' + timestamp + '&scrollTo=' + blockIdPrefix + blockId;
		}
	}

	/**
	 * Scrolls preview window for some percent.
	 *
	 * @param {number} topInPercent Percent from top to scroll.
	 */
	#scrollDevice(topInPercent: number)
	{
		if (this.#previewWindow)
		{
			const document = this.#previewWindow.document;

			const scrollHeight = Math.max(
				document.body.scrollHeight, document.documentElement.scrollHeight,
				document.body.offsetHeight, document.documentElement.offsetHeight,
				document.body.clientHeight, document.documentElement.clientHeight
			);

			this.#previewWindow.scroll(0, scrollHeight * topInPercent / 100);
		}
	}

	/**
	 * Resolves and returns Device by its code.
	 *
	 * @param {string} deviceType Device type.
	 * @return {DeviceItem}
	 */
	#resolveDeviceByType(deviceType: string): DeviceItem
	{
		let deviceCode = localStorage.getItem('deviceCode');
		if (deviceCode && Devices.devices[deviceCode])
		{
			return Devices.devices[deviceCode];
		}

		deviceCode = Devices.defaultDevice?.[deviceType];
		if (!deviceCode)
		{
			return;
		}

		return Devices.devices[deviceCode];
	}

	#getPreviewNode(): HTMLDivElement
	{
		if (!this.#previewLoader)
		{
			Loc.loadMessages(this.#options.messages);

			this.#previewLoader = Tag.render`
				<div class="landing-device-loader">
					<div class="landing-device-loader-icon"></div>
					<div class="landing-device-loader-text">${Loc.getMessage('LANDING_TPL_PREVIEW_LOADING')}</div>
				</div>
			`;
		}

		return this.#previewLoader;
	}

	#setPreview(target: HTMLElement)
	{
		if (!target)
		{
			return;
		}

		Dom.append(this.#getPreviewNode(), target)
	}

	#removePreview()
	{
		Dom.addClass(this.#getPreviewNode(), '--hide');
		Event.bind(this.#getPreviewNode(), 'transitionend', () => {
			Dom.remove(this.#getPreviewNode());
		});
	}

	/**
	 * Sets new device.
	 *
	 * @param {DeviceItem|null} newDevice Device.
	 */
	#setDevice(newDevice: ?DeviceItem)
	{
		if (!newDevice)
		{
			return;
		}

		localStorage.setItem('deviceCode', newDevice.code);

		// remove old class within preview
		if (this.#currentDevice)
		{
			Dom.removeClass(this.#previewElement, this.#currentDevice.className);
			this.#previewElement.style.removeProperty(`top`);
		}

		this.#currentDevice = newDevice;
		this.#previewElement.querySelector('[data-role="device-name"]').innerHTML = newDevice.name;
		this.#previewElement.querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
		const frame = this.#previewElement.querySelector('[data-role="landing-device-preview-iframe"]');
		const frameWrapper = this.#previewElement.querySelector('[data-role="landing-device-preview"]');

		frame.onload = () => this.#removePreview();

		// scale for device
		if (frame
			&& frameWrapper
			&& this.#currentDevice.width
			&& this.#currentDevice.height)
		{
			const scale = window.innerHeight / (this.#currentDevice.height + 300);
			const padding = parseInt(window.getComputedStyle(frameWrapper).padding);

			let param1 = this.#currentDevice.width;
			let param2 = this.#currentDevice.height;

			if (localStorage.getItem('deviceOrientation') === 'landscape')
			{
				param1 = this.#currentDevice.height;
				param2 = this.#currentDevice.width;
			}

			frame.style.setProperty(`width`, `${param1}px`);
			frame.style.setProperty(`height`, `${param2}px`);
			frameWrapper.style.setProperty(`transform`, `scale(${scale})`);
			this.#previewElement.style.setProperty(`width`, `${(param1 + (padding * 2)) * scale}px`);
			this.#previewElement.style.setProperty(`height`, `${(param2 + (padding * 2)) * scale}px`);
		}

		Dom.addClass(this.#previewElement, this.#currentDevice.className);
	}

	/**
	 * Gets top scroll of editor window and adjusts device preview window.
	 */
	#adjustPreviewScroll()
	{
		const documentEditorFrame = this.#editorFrameWrapper.querySelector('iframe').contentWindow.document;
		const scrollHeight = Math.max(
			documentEditorFrame.body.scrollHeight, documentEditorFrame.documentElement.scrollHeight,
			documentEditorFrame.body.offsetHeight, documentEditorFrame.documentElement.offsetHeight,
			documentEditorFrame.body.clientHeight, documentEditorFrame.documentElement.clientHeight
		);
		const scrollTop = documentEditorFrame.documentElement.scrollTop || documentEditorFrame.body.scrollTop;

		this.#scrollDevice(scrollTop / scrollHeight * 100);
	}

	/**
	 * Creates Preview Popup.
	 *
	 * @param {Options} options Preview options.
	 */
	#buildPreview(options: Options)
	{
		if (!this.#previewElement)
		{
			this.#previewElement = DeviceUI.getPreview({
				frameUrl: options.frameUrl,
				clickHandler: this.#onClickDeviceSelector.bind(this),
				messages: options.messages,
			});
			Dom.hide(this.#previewElement);
			document.body.appendChild(this.#previewElement);

			//#170065
			//this.#previewElement.querySelector('iframe').contentWindow.addEventListener('load', () => {
				if (!this.#previewWindow)
				{
					this.#previewWindow = this.#previewElement.querySelector('iframe').contentWindow;
					const previewDocument = this.#previewElement.querySelector('iframe').contentWindow.document
					Dom.removeClass(previewDocument.querySelector('html'), 'bx-no-touch');
					Dom.addClass(previewDocument.querySelector('html'), 'bx-touch');
				}
				this.#adjustPreviewScroll();
			//});
		}
	}

	/**
	 * Invokes by clicking on device selector.
	 */
	#onClickDeviceSelector()
	{
		DeviceUI.openDeviceMenu(
			this.#previewElement.querySelector('[data-role="device-name"]'),
			Object.values(Devices.devices),
			this.#setDevice.bind(this)
		);
	}

	/**
	 * Creates and Shows Preview Popup.
	 */
	#showPreview()
	{
		Dom.show(this.#previewElement);
		this.#setPreview(this.#previewElement);
	}

	/**
	 * Hides Preview Popup.
	 */
	#hidePreview()
	{
		Dom.hide(this.#previewElement);
	}
}
