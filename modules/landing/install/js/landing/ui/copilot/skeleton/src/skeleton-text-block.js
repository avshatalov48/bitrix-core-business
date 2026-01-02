import { bind, bindOnce, unbind, Dom, Tag } from 'main.core';
import { hexToRgb } from './helpers/hex-to-rgb';
import { wait } from './helpers/wait';
import { isTextNodeInButton } from './helpers/is-text-node-in-button';

import './css/skeleton-text-block.css';
import { SkeletonBlock } from './skeleton-block';

export type SkeletonTextBlockOptions = {
	textNode: Text;
};

export class SkeletonTextBlock extends SkeletonBlock
{
	#textNode: Text = null;
	#textNodeContainer: HTMLElement = null;
	#text: string = '';
	#rectangles: HTMLElement[] = [];

	#windowResizeHandler: Function;

	constructor(options: SkeletonTextBlockOptions)
	{
		super(options);

		this.#textNode = options.textNode;
		this.#textNodeContainer = this.#textNode.parentElement;
		this.#text = options.textNode.data;

		this.#windowResizeHandler = this.#updateRectanglesPosition.bind(this);
	}

	hide(): void
	{
		this.#fixWidthAndHeightForTextNodeContainer();
		if (this.#isShowRectanglesOnText())
		{
			this.#showRectanglesOnText();
		}
		bind(window, 'resize', this.#windowResizeHandler);

		// the space is need for inline element's height and element with :before,:after
		this.#textNodeContainer.innerText = ' ';
	}

	async show(): void
	{
		if (this.#isShowRectanglesOnText())
		{
			await this.#hideAllRectangles();
		}

		Dom.style(this.#textNodeContainer, {
			whiteSpace: null,
		});

		await this.#addTextWithAnimation();

		Dom.style(this.#textNodeContainer, {
			display: null,
			width: null,
			height: null,
		});

		unbind(window, 'resize', this.#windowResizeHandler);
	}

	#fixWidthAndHeightForTextNodeContainer(): void
	{
		const textNodeContainerRect = Dom.getPosition(this.#textNodeContainer);

		Dom.style(this.#textNodeContainer, {
			whiteSpace: 'break-spaces',
			width: `${textNodeContainerRect.width}px`,
			height: `${textNodeContainerRect.height}px`,
		});
	}

	async #addTextWithAnimation(): Promise<void>
	{
		if (this.#text.length > 30)
		{
			await this.#addTextWithWordAnimation();
		}
		else
		{
			await this.#addTextWithLetterAnimation();
		}
	}

	async #addTextWithWordAnimation(): Promise<void>
	{
		const span = this.#renderAnimatedTextElement();

		this.#textNodeContainer.innerText = '';
		Dom.append(span, this.#textNodeContainer);

		const words = this.#text.split(' ');

		span.innerText = words[0];
		for (const word of words.slice(1))
		{
			// eslint-disable-next-line no-await-in-loop
			await wait(50);
			span.innerText = `${span.innerText} ${word}`;
		}

		Dom.removeClass(span, 'landing__copilot-skeleton_animated-text');
		this.#textNodeContainer.innerText = span.innerText;
	}

	async #addTextWithLetterAnimation(): Promise<void>
	{
		const span = this.#renderAnimatedTextElement();

		this.#textNodeContainer.innerText = '';
		Dom.append(span, this.#textNodeContainer);

		let spaceWithLetter = '';

		for (const letter of this.#text)
		{
			if (letter === ' ')
			{
				spaceWithLetter += letter;
			}
			else if (spaceWithLetter.length > 0)
			{
				span.innerText += spaceWithLetter + letter;
				spaceWithLetter = '';
			}
			else
			{
				span.innerText += letter;
			}

			// eslint-disable-next-line no-await-in-loop
			await wait(50);
		}

		Dom.removeClass(span, 'landing__copilot-skeleton_animated-text');
		this.#textNodeContainer.innerText = span.innerText;
	}

	#renderAnimatedTextElement(): HTMLElement
	{
		const textBlockLineHeight = parseFloat(getComputedStyle(this.#textNodeContainer).lineHeight, 10) || 0;
		const textBlockFontSize = parseFloat(getComputedStyle(this.#textNodeContainer).fontSize, 10) || 24;
		const labelSize = textBlockFontSize > 24 ? 24 : textBlockFontSize;
		const copilotAnimationLabelBottomIndent = (textBlockLineHeight - labelSize) / 2;

		const span = Tag.render`<span class="landing__copilot-skeleton_animated-text"></span>`;

		Dom.style(span, {
			'--copilot-label-bottom-indent': `${copilotAnimationLabelBottomIndent}px`,
			'--copilot-label-size': `${labelSize}px`,
			display: 'inline-block',
			minHeight: `${textBlockLineHeight}px`,
		});

		span.innerText = '';

		return span;
	}

	#hideAllRectangles(): Promise<void>
	{
		const promises = this.#rectangles.map(
			(rectangle) => this.#hideRectangleWithAnimation(rectangle),
		);

		return Promise.all(promises);
	}

	async #hideRectangleWithAnimation(rectangle: HTMLElement): Promise<void>
	{
		return new Promise((resolve) => {
			Dom.addClass(rectangle, '--hiding');

			bindOnce(rectangle, 'transitionend', () => {
				rectangle.remove();
				resolve();
			});
		});
	}

	#isShowRectanglesOnText(): boolean
	{
		return isTextNodeInButton(this.#textNode) === false;
	}

	#showRectanglesOnText(): void
	{
		const rectangles = this.#createRectanglesFromRects(this.#getTextNodeRects());

		rectangles.forEach((rectangle) => {
			Dom.append(rectangle, this.#textNodeContainer.ownerDocument.defaultView.document.body);
		});
	}

	#getTextNodeRects(): DOMRect[]
	{
		const range = this.#textNodeContainer.ownerDocument.defaultView.document.createRange();
		range.selectNodeContents(this.#textNode);
		const rects = range.getClientRects();

		return [...rects].map((rect: DOMRect) => {
			const scrollTop = window.pageYOffset
				|| this.#textNodeContainer.ownerDocument.defaultView.document.documentElement.scrollTop;

			const heightReductionPercent = 0.5;
			const heightReduction = parseInt(rect.height * heightReductionPercent, 10);

			return new DOMRect(
				rect.left,
				rect.top + scrollTop + (heightReduction / 2),
				rect.width,
				rect.height - heightReduction,
			);
		});
	}

	#createRectanglesFromRects(rects: DOMRect[]): HTMLElement[]
	{
		this.#rectangles = rects.map((rect: DOMRect) => {
			const rectangle = Tag.render`<div class="landing__copilot-skeleton-rectangle --showing"></div>`;

			Dom.style(rectangle, {
				backgroundColor: this.#getTextNodeColor(),
			});

			this.#updateRectanglePosition(rectangle, rect);

			return rectangle;
		});

		return this.#rectangles;
	}

	#updateRectanglesPosition(): void
	{
		this.#getTextNodeRects().forEach((rect: DOMRect, index) => {
			const rectangle = this.#rectangles[index];

			this.#updateRectanglePosition(rectangle, rect);
		});
	}

	#updateRectanglePosition(rectangle: HTMLElement, rect: DOMRect): void
	{
		const documentWidth = this.#textNodeContainer.ownerDocument.defaultView.document.documentElement.clientWidth;

		const overWidth = 5;
		const overHeight = 0;
		const rectWidth = rect.width + overWidth;
		const rectHeight = rect.height + overHeight;
		const rectLeft = rect.left - overWidth / 2;
		const rectTop = rect.top - overHeight / 2;
		const rectRight = documentWidth - rectLeft - rectWidth;

		Dom.style(rectangle, {
			top: `${rectTop}px`,
			left: `${rectLeft}px`,
			right: `${rectRight}px`,
			width: `${rectWidth}px`,
			height: `${rectHeight}px`,
		});
	}

	#getTextNodeColor(): string
	{
		const opacity = 0.25;
		const textNodeContainerElement = this.#textNode.parentElement;

		const colorString = getComputedStyle(textNodeContainerElement).color;

		if (colorString.includes('rgb'))
		{
			const rgba = colorString.match(/[\d.]+/g);

			const [r, g, b] = rgba;

			return `rgba(${r}, ${g}, ${b}, ${opacity})`;
		}

		const { r, g, b } = hexToRgb(colorString);

		return `rgba(${r},${g},${b}, ${opacity})`;
	}
}
