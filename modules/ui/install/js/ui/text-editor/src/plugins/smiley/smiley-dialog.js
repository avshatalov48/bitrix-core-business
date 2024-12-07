import { Type, Runtime } from 'main.core';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';

export type SmileyDialogOptions = {
	targetNode?: HTMLElement,
	events?: Object<string, (event: BaseEvent) => {}>,
}

export class SmileyDialog extends EventEmitter
{
	#popup: Popup = null;
	#targetNode: HTMLElement = null;

	constructor(dialogOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.SmileyDialog');

		const options: SmileyDialogOptions = Type.isPlainObject(dialogOptions) ? dialogOptions : {};

		this.setTargetNode(options.targetNode);
		this.subscribeFromOptions(options.events);
	}

	show(): void
	{
		this.getPopup().adjustPosition({ forceBindPosition: true });
		this.getPopup().show();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	isShown(): boolean
	{
		return this.#popup !== null && this.#popup.isShown();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	setTargetNode(container: HTMLElement): void
	{
		if (Type.isElementNode(container))
		{
			this.#targetNode = container;
		}
	}

	getTargetNode(): HTMLElement | null
	{
		return this.#targetNode;
	}

	getPopup(): Popup
	{
		if (this.#popup === null)
		{
			const popupWidth = 360;
			const targetNode = this.getTargetNode();
			const rect = targetNode.getBoundingClientRect();
			const targetNodeWidth = rect.width;

			this.#popup = new Popup({
				autoHide: true,
				padding: 0,
				closeByEsc: true,
				width: popupWidth,
				height: 250,
				bindElement: this.getTargetNode(),
				events: {
					onClose: () => {
						this.emit('onClose');
					},
					onDestroy: () => {
						this.emit('onDestroy');
					},
					onFirstShow: () => {
						const dialog = this;
						Runtime.loadExtension('ui.vue3', 'ui.vue3.components.smiles')
							.then((exports) => {
								const { BitrixVue, Smiles } = exports;
								const app = BitrixVue.createApp({
									methods: {
										handleSelect(text) {
											dialog.emit('onSelect', { smiley: text.trim() });
										},
									},
									components: {
										Smiles,
									},
									template: '<Smiles @selectSmile="handleSelect($event.text)"/>',
								});

								app.mount(this.#popup.getContentContainer());
							}).catch(() => {
								this.#popup.close();
							})
						;
					},
					onShow: (event) => {
						const popup = event.getTarget();
						const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
						const angleShift = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

						popup.setAngle({ offset: popupWidth / 2 - angleShift });
						popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });
					},
				},
			});
		}

		return this.#popup;
	}
}
