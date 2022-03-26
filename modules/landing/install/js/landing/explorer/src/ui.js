import { Loc, Tag, Text} from 'main.core';

export type DataType = {
	ID: number,
	TITLE: string
};

export type FolderType = {
	ID: number,
	TITLE: string,
	PARENT_ID: ?number
};

import 'ui.icons.disk';

export class ExplorerUI
{
	static getLoader(): HTMLElement
	{
		return Tag.render`<div class="landing-explorer-loader">
			<div class="main-ui-loader">
				<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
					<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
				</svg>
			</div>
		</div>`;
	}

	static getActionButton(title: string, hadnler: () => {}): BX.UI.Button
	{
		return new BX.UI.Button({
			id: 'landing-explorer-action',
			size: BX.UI.Button.Size.MEDIUM,
			color: BX.UI.Button.Color.SUCCESS,
			text: title,
			events: {
				click: hadnler
			}
		});
	}

	static getCancelButton(hadnler: () => {}): BX.UI.Button
	{
		return new BX.UI.Button({
			id: 'landing-explorer-cancel',
			size: BX.UI.Button.Size.MEDIUM,
			color: BX.UI.Button.Color.LINK,
			text: Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_CANCEL'),
			events: {
				click: hadnler
			}
		});
	}

	static getSiteList(data: Array<DataType>, onClick: () => {}): HTMLElement
	{
		return Tag.render`
			<ul class="landing-site-selector-list">
				${data.map(item => {
					return Tag.render`
						<li class="landing-site-selector-item" data-explorer-depth="0" data-explorer-siteId="${item.ID}" onclick="${() => onClick(item.ID)}">
							<span class="ui-icon ui-icon-file-folder"><i></i></span>
							<span class="landing-site-selector-item-value">
								${Text.encode(item.TITLE)}
							</span>
						</li>
					`;
				})}
			</ul>
		`;
	}

	static getFolderItem(item: DataType, depth: number, onClick: () => {}): HTMLElement
	{
		return Tag.render`
			<li style="padding-left: ${30 * depth}px" class="landing-site-selector-item landing-site-selector-item-lower" data-explorer-depth="${depth}" data-explorer-folderId="${item.ID}" onclick="${() => onClick(item.ID)}">
				<span class="ui-icon ui-icon-file-folder"><i></i></span>
				<span class="landing-site-selector-item-value"> 
					${Text.encode(item.TITLE)}
				</span>
			</li>
		`;
	}
}
