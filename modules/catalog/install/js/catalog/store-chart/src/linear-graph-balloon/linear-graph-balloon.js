import { Text, Tag, Dom } from 'main.core';
import './linear-graph-balloon.css';
import 'ui.design-tokens';
import 'ui.fonts.opensans';

export type LinearGraphBalloonType = {
	title: string,
	value: string,
	htmlValue: string,
};

export class LinearGraphBalloon
{
	static renderBalloon(graphDataItem, graph): string
	{
		const data = graphDataItem.dataContext.balloon;
		const items = data.items || [];

		const mainWrapper = Tag.render`<div class="store-chart-linear-graph-balloon-main"></div>`;
		const balloonContainer = Tag.render`
			<div class="store-chart-linear-graph-balloon-wrapper">
				<div class="store-chart-linear-graph-balloon-title">
					${Text.encode(data.title)}	
				</div>	
				${mainWrapper}
			</div>
		`;

		items.forEach((balloon: LinearGraphBalloonType) => {
			const value = balloon.htmlValue || Text.encode(balloon.value);
			const item = Tag.render`
				<div class="store-chart-linear-graph-balloon-item">
					<div class="store-chart-linear-graph-balloon-subtitle">
						${Text.encode(balloon.title)}	
					</div>	
					<div class="store-chart-linear-graph-balloon-modal-content">
						<div class="store-chart-linear-graph-balloon-modal-value">
							${value}
						</div>
					</div>		
				</div>
			`;
			Dom.append(item, mainWrapper);
		});

		return balloonContainer.outerHTML;
	}
}
