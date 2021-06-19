import {Tag} from 'main.core';
import {Loc} from 'landing.loc';

export class DesignerBlockUI
{
	static getHoverDiv(): HTMLDivElement
	{
		return Tag.render`<div class="landing-designer-block-node-hover"></div>`;
	}

	static getPseudoLast(): HTMLElement
	{
		return Tag.render`<div class="landing-designer-block-pseudo-last"></div>`;
	}

	static getAddNodeButton(): HTMLElement
	{
		return Tag.render`
			<div class="landing-designer-block-node-hover-add">
				<span class="landing-designer-block-node-hover-add-title">
					${Loc.getMessage('LANDING_DESIGN_BLOCK_REPO_BUTTON')}
				</span>
			</div>`;
	}
}
