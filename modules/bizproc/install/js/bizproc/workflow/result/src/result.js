import { Type, Dom, Tag, Loc, Text } from 'main.core';
import { WorkflowResultStatus } from 'bizproc.types';
import type { ResultData } from './types';

import 'ui.hint';

import './css/style.css';

export class WorkflowResult
{
	static #LENGTH_LIMIT = 74; // 80 symbols without length of "...etc."
	#status: string;
	#text: string;
	#node: ?HTMLElement = null;

	constructor(data: ResultData)
	{
		this.#status = data.status;
		this.#text = Type.isStringFilled(data.text) ? data.text : '';
	}

	render(): HTMLElement
	{
		if (!Type.isNil(this.#node))
		{
			return this.#node;
		}

		let result = '';
		switch (this.#status)
		{
			case WorkflowResultStatus.BB_CODE_RESULT:
				result = this.#renderBBCodeResult();
				break;
			case WorkflowResultStatus.NO_RIGHTS_RESULT:
				result = this.#renderNoRightResult();
				break;
			default:
				result = this.#text;
		}

		const cleanedResult = this.#replaceNewLine(this.#clearTags(this.#replaceBrTag(result)));

		this.#node = Tag.render`
			<div class="bp-workflow-result">
				${this.#isNeedCollapse(cleanedResult) ? this.#renderCollapsedResult(cleanedResult) : null}
				<span class="bp-workflow-result-full">
					${result}
				</span>
			</div>
		`;

		if (this.#isNoRightResult())
		{
			Dom.addClass(this.#node, 'no-rights');
		}

		if (!this.#isNeedCollapse(cleanedResult))
		{
			this.#handleExpandResult();
		}

		BX.UI.Hint.init(this.#node);

		return this.#node;
	}

	renderTo(container: HTMLElement): void
	{
		if (Type.isDomNode(container))
		{
			Dom.append(this.render(), container);
		}
	}

	#renderBBCodeResult(): string
	{
		return `${Loc.getMessage('BP_JS_WF_RESULT_VALUE')}<br>${this.#text}`;
	}

	#renderNoRightResult(): string
	{
		return `
			${Loc.getMessage('BP_JS_WF_RESULT_NO_RIGHTS_VIEW')} 
			<span data-hint="${Loc.getMessage('BP_JS_WF_RESULT_NO_RIGHTS_TOOLTIP')}"></span>
		`;
	}

	#renderCollapsedResult(result: string): HTMLElement
	{
		const collapsedResult = result.slice(0, this.constructor.#LENGTH_LIMIT);

		return Tag.render`
			<span class="bp-workflow-result-collapsed">
				${Text.encode(collapsedResult)}
				...
				<a href="#" onclick="${this.#handleExpandResult.bind(this)}">
					${Loc.getMessage('BP_JS_WF_RESULT_MORE')}
				</a>
			</span>
		`;
	}

	#replaceBrTag(text: string): string
	{
		return text.replaceAll(/(<br\s?\/?>)+/gm, ' ');
	}

	#clearTags(text: string): string
	{
		return Tag.render`<span>${text}</span>`.textContent;
	}

	#replaceNewLine(text: string): string
	{
		return text.replaceAll(/\n+/gm, ' ');
	}

	#handleExpandResult(event: PointerEvent): boolean
	{
		if (event)
		{
			event.preventDefault();
		}

		Dom.addClass(this.#node, '--expanded');
	}

	#isNoRightResult(): boolean
	{
		return this.#status === WorkflowResultStatus.NO_RIGHTS_RESULT;
	}

	#isNeedCollapse(result: string): boolean
	{
		return result.length > this.constructor.#LENGTH_LIMIT;
	}
}
