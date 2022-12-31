import {Type, Tag, Dom} from 'main.core';

import './advice.css';

export const AdviceAnglePosition = Object.freeze({
	TOP: 'top',
	BOTTOM: 'bottom',
});

export type AdviceContent = HTMLElement | string | null;

export type AdviceOptions = {
	avatarImg?: string,
	content?: AdviceContent,
	text?: string,
	anglePosition?: string,
}

export class Advice
{
	#container: HTMLElement = null;
	#avatar: string;
	#anglePosition: AdviceAnglePosition;

	#content: AdviceContent = '';
	#textBoxBaseClassname = 'ui-advice__text-box';
	#containerBaseClassname = 'ui-advice';

	static AnglePosition = AdviceAnglePosition;

	constructor(options: AdviceOptions)
	{
		this.#avatar = Type.isString(options.avatarImg) ? options.avatarImg : '';
		this.#anglePosition = this.#isValidAnglePosition(options.anglePosition) ? options.anglePosition : Advice.AnglePosition.TOP;
		this.#content = this.#isValidContent(options.content) ? options.content : '';

		this.#createContainer();
	}

	getAvatar(): string
	{
		return this.#avatar;
	}

	setAvatar(avatarImg: string): string
	{
		this.#avatar = avatarImg;
		return this.#avatar;
	}

	getContent(): AdviceContent
	{
		return this.#content;
	}

	setContent(content: AdviceContent): AdviceContent
	{
		if (this.#isValidContent(content))
		{
			this.#content = content;
			return this.#content;
		}

		return null;
	}

	#getTextBoxClassname(): string
	{
		let className = this.#textBoxBaseClassname;

		if (this.#anglePosition === AdviceAnglePosition.BOTTOM)
		{
			className += ' --angle-bottom';
		}
		else if (this.#anglePosition === AdviceAnglePosition.TOP)
		{
			className += ' --angle-top';
		}
		else
		{
			className += ' --angle-bottom';
		}

		return className;
	}

	#getContainerClassname(): string
	{
		let className = this.#containerBaseClassname;
		switch (this.#anglePosition)
		{
			case AdviceAnglePosition.BOTTOM: className += ' --angle-bottom'; break;
			case AdviceAnglePosition.TOP: className+= ' --angle-top'; break;
			default: className += ' --angle-bottom';
		}

		return className;
	}

	#getHtmlContent(): HTMLElement
	{
		if (Type.isString(this.#content))
		{
			return Tag.render`<span>${this.#content}</span>`;
		}

		return this.#content;
	}

	#createContainer(): HTMLElement
	{
		if (!this.#container)
		{
			this.#container = Tag.render`
				<div class="${this.#getContainerClassname()}">
					<div class="ui-advice__avatar-box">
						<span class="ui-advice__avatar ui-icon ui-icon-common-user">
							<i style="background-image: url('${encodeURI(this.getAvatar())}')"></i>
						</span>
					</div>
					<div class="${this.#getTextBoxClassname()}"></div>
				</div>
				`;

			const contentContainer = this.#container.querySelector(`.${this.#textBoxBaseClassname}`);

			Dom.append(this.#getHtmlContent(), contentContainer);
		}

		return this.#container;
	}

	getContainer(): HTMLElement
	{
		return this.#container;
	}

	#isValidAnglePosition(anglePosition: string): boolean
	{
		return Type.isString(anglePosition) && Object.values(AdviceAnglePosition).includes(anglePosition);
	}

	#isValidContent(content: HTMLElement | string): boolean {
		return Type.isString(content) || Type.isDomNode(content);
	}

	renderTo(targetContainer: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(targetContainer))
		{
			Dom.append(this.#container, targetContainer);
			return targetContainer;
		}
		else
		{
			return null;
		}
	}
}
