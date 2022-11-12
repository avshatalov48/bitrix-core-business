import {Tag} from 'main.core';
import ColumnItemOptions from "../columnitem";
import Base from "./base";

export default class Hint extends Base
{
	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.hint = options.hint;
		this.className = options.className;

		this.hintNode = null;
	}

	render(): ?HTMLElement
	{
		if (!this.hintNode && this.hint)
		{
			const hintManager = BX.UI.Hint.createInstance({
				id: 'access-rights-ui-hint-' + this.getId(),
				popupParameters: {
					className: 'ui-access-rights-popup-pointer-events ui-hint-popup',
					autoHide: true,
					darkMode: true,
					maxWidth: 280,
					offsetTop: 0,
					offsetLeft: 8,
					angle: true,
					animation: 'fading-slide',
				},
			});

			this.hintNode = Tag.render`<span class='${this.className}'></span>`;
			this.hintNode.setAttribute(hintManager.attributeName, this.hint);
			this.hintNode.setAttribute(hintManager.attributeHtmlName, true);
			this.hintNode.setAttribute(hintManager.attributeInteractivityName, true);

			hintManager.initNode(this.hintNode);
		}

		return this.hintNode;
	}
}
