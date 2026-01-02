import { AirButtonStyle, Button, ButtonColor, ButtonManager, ButtonSize } from 'ui.buttons';
import { Dom, Extension } from 'main.core';

export type RightButtonsOptions = {
	buttonsContainer: HTMLElement;
	collapsable: ?boolean;
}

export class RightButtons
{
	#buttonsContainer: HTMLElement;
	#buttons: Button[];
	#resizeObserver: ResizeObserver;
	#mutationObserver: MutationObserver;
	#deltas: number[];
	#collapsable: boolean;

	static #shift = 32;

	constructor(options: RightButtonsOptions)
	{
		this.#buttonsContainer = options.buttonsContainer;
		this.#collapsable = options.collapsable === true;
	}

	init(): void
	{
		if (this.#useAirDesign() === false)
		{
			return;
		}

		this.#initButtons();

		if (this.#collapsable)
		{
			this.#handleContainerWidthUpdate();
			this.#initResizeObserver();
		}

		this.#initMutationObserver();

		this.#observe();
	}

	#initButtons(): void
	{
		const buttonElements = this.#buttonsContainer.querySelectorAll('.ui-btn, .ui-btn-split');

		this.#buttons = [...buttonElements].map((button) => {
			const btn = ButtonManager.createFromNode(button);
			this.#styleButton(btn);

			return btn;
		});

		this.#deltas = this.#buttons.map(() => 0);
	}

	#initResizeObserver(): void
	{
		this.#resizeObserver = new ResizeObserver((entries) => {
			// eslint-disable-next-line no-unused-vars
			for (const entry of entries)
			{
				this.#handleContainerWidthUpdate();
			}
		});
	}

	#initMutationObserver(): void
	{
		this.#mutationObserver = new MutationObserver((mutationsList) => {
			mutationsList.forEach((mutation) => {
				if (mutation.type !== 'childList')
				{
					return;
				}

				mutation.addedNodes.forEach((node) => {
					if (node.nodeType !== Node.ELEMENT_NODE)
					{
						return;
					}

					if (Dom.hasClass(node, 'ui-btn') || Dom.hasClass(node, 'ui-btn-split'))
					{
						this.#initButtons();
					}

					const foundButtons = node.querySelectorAll('.ui-btn, .ui-btn-split');

					if (foundButtons.length > 0)
					{
						this.#initButtons();
					}
				});
			});
		});
	}

	#observe(): void
	{
		this.#resizeObserver?.observe(this.#buttonsContainer);
		this.#mutationObserver?.observe(this.#buttonsContainer, {
			childList: true,
			subtree: true,
		});
	}

	#handleContainerWidthUpdate(): void
	{
		if (this.#isButtonsOverflowContainer())
		{
			this.#collapseOneMoreButton();
		}
		else if (this.#isEnoughSpaceForExpandedButton())
		{
			this.#expandOneMoreButton();
		}
	}

	#isButtonsOverflowContainer(): boolean
	{
		return this.#getButtonRelativePositionLeft() + RightButtons.#shift >= 0;
	}

	#isEnoughSpaceForExpandedButton(): boolean
	{
		return this.#getButtonRelativePositionLeft() + this.#getDelta() + RightButtons.#shift < 0;
	}

	#getButtonRelativePositionLeft(): number
	{
		return Dom.getRelativePosition(this.#buttonsContainer, this.#buttons[0].getContainer()).left;
	}

	#expandOneMoreButton(): void
	{
		const collapsedButtonIndex = this.#buttons.findIndex((button) => button.isCollapsed());

		if (collapsedButtonIndex < 0)
		{
			return;
		}

		const collapsedButton = this.#buttons[collapsedButtonIndex];

		collapsedButton.setCollapsed(false);
		this.#deltas[collapsedButtonIndex] = 0;

		if (this.#isEnoughSpaceForExpandedButton())
		{
			this.#expandOneMoreButton();
		}
	}

	#collapseOneMoreButton(): void
	{
		const notCollapsedButtonIndex = this.#buttons.findLastIndex((button) => button.isCollapsed() === false);

		if (notCollapsedButtonIndex < 0)
		{
			return;
		}

		const notCollapsedButton = this.#buttons[notCollapsedButtonIndex];

		this.#deltas[notCollapsedButtonIndex] += notCollapsedButton.getContainer().offsetWidth;
		notCollapsedButton.setCollapsed(true);
		this.#deltas[notCollapsedButtonIndex] -= notCollapsedButton.getContainer().offsetWidth;

		if (this.#isButtonsOverflowContainer())
		{
			this.#collapseOneMoreButton();
		}
	}

	#getDelta(): number
	{
		return this.#deltas.find((delta) => delta > 0) ?? 0;
	}

	#useAirDesign(): boolean
	{
		return Extension.getSettings('ui.actions-bar').get('useAirDesign');
	}

	#styleButton(button: Button): void
	{
		const isButtonHasAirDesign = button.hasAirDesign();

		button.setAirDesign(true);
		button.setSize(ButtonSize.SMALL);
		if (isButtonHasAirDesign === false)
		{
			button.setStyle(this.#buttonColorStyleMap(button.getColor()));
		}
		button.setNoCaps(true);
	}

	#buttonColorStyleMap(color: ButtonColor): string
	{
		if (color === ButtonColor.PRIMARY)
		{
			return AirButtonStyle.FILLED;
		}

		return AirButtonStyle.OUTLINE;
	}
}
