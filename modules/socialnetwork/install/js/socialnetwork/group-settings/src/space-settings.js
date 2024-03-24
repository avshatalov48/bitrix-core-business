import { Loc, Tag } from 'main.core';
import { Label, LabelColor } from 'ui.label';
import { Controller } from 'socialnetwork.controller';

import type { GroupData } from './type';

import { Tools } from './layout/space/tools';
import { Tags } from './layout/space/tags';

import 'ui.alerts';
import 'ui.icon-set.main';
import 'ui.sidepanel-content';

type Params = {
	width: number,
	spaceId: number,
}

export class SpaceSettings
{
	#params: Params;

	#tools: ?Tools = null;
	#tags: ?Tags = null;

	constructor(params: Params)
	{
		this.#params = params;
	}

	openInSlider(): void
	{
		BX.SidePanel.Instance.open('spaces-settings-space-settings', {
			cacheable: false,
			title: Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_TITLE'),
			contentCallback: () => {
				return Controller.getGroupData(
					this.#params.spaceId,
					[
						'FEATURES',
					],
				)
					.then((groupData: GroupData) => {
						this.#tools = new Tools(this.#params.spaceId, groupData.features);
						this.#tags = new Tags(this.#params.spaceId);

						return this.#render();
					})
				;
			},
			width: this.#params.width,
			events: {
				onLoad: this.#onLoad.bind(this),
			},
		});
	}

	#onLoad()
	{
		this.#tags.renderSelector();
	}

	#render(): HTMLElement
	{
		const uiStyles = 'ui-sidepanel-layout-content ui-sidepanel-layout-content-margin';

		const { node } = Tag.render`
			<div ref="node" class="ui-sidepanel-layout">
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">
						${Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_TITLE')}
					</div>
				</div>
				<div class="${uiStyles} sn-side-panel__space-settings">
					${this.#tools.render()}
					${this.#tags.render()}
					${this.#renderOtherBlock()}
				</div>
			</div>
		`;

		return node;
	}

	#renderOtherBlock(): HTMLElement
	{
		const { node } = Tag.render`
			<div ref="node" class="ui-slider-section sn-side-panel__space-settings_section --disabled">
				<div class="sn-side-panel__space-settings_section-title">
					<div class="ui-icon-set --more"></div>
					<div class="sn-side-panel__space-settings_section-title-text">
						${Loc.getMessage('SN_SIDE_PANEL_SPACE_OTHER')}
						${this.#renderLabel()}
					</div>
				</div>
				<div class="sn-side-panel__space-settings_section-content">
					<div class="sn-side-panel__space-settings_section-content-wrapper">
					<div class="sn-side-panel__space-settings_section-content-wrapper-block">
					</div>
					</div>
				</div>
			</div>
		`;

		return node;
	}

	#renderLabel(): Label
	{
		const label = new Label({
			text: Loc.getMessage('SN_SIDE_PANEL_SPACE_SETTINGS_SOON'),
			color: LabelColor.PRIMARY,
			fill: true,
		});

		return label.render();
	}
}
