import { Dom, Tag, Text, Event } from 'main.core';

const BizProcActivity = window.BizProcActivity;

export class ForEachActivity extends BizProcActivity
{
	constructor()
	{
		super();
		this.Type = 'ForEachActivity';

		// compatibility
		this.BizProcActivityDraw = this.Draw.bind(this);
		this.Draw = this.#draw.bind(this);
		this.CheckFields = () => true;
		this.OnHideClick = this.#onHideClick.bind(this);
	}

	#draw(wrapper)
	{
		if (this.childActivities.length === 0)
		{
			this.childActivities = [new window.SequenceActivity()];
			this.childActivities[0].parentActivity = this;
		}

		this.container = Tag.render`<div class="parallelcontainer"></div`;
		Dom.append(this.container, wrapper);
		this.BizProcActivityDraw(this.container);
		this.activityContent = null;

		Dom.style(this.div, { position: 'relative', top: '12px' });

		this.hideContainer = Tag.render`
			<div 
				style="
					background: #fff;
					border: 1px #CCCCCC dashed;
					width: 250px;
					color: #aaa;
					padding: 13px 0 3px 0;
					cursor: pointer;
				"
			>${Text.encode(window.BPMESS.PARA_MIN)}</div>
		`;
		Event.bind(this.hideContainer, 'click', this.OnHideClick.bind(this));
		Dom.append(this.hideContainer, this.container);

		this.childsContainer = Tag.render`
			<table id="${Text.encode(this.Name)}" width="100%" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<tr>
						<td align="center" valign="center" width="15%"></td>
						<td align="center" valign="center" width="70%" style="border: 2px #dfdfdf dashed; padding: 10px"></td>
						<td align="center" valign="center" width="15%"></td>
					</tr>
				</tbody>
			</table>
		`;
		Dom.append(this.childsContainer, this.container);

		// eslint-disable-next-line no-underscore-dangle
		if (this.Properties._DesMinimized === 'Y')
		{
			Dom.hide(this.childsContainer);
		}
		else
		{
			Dom.hide(this.hideContainer);
		}

		this.childActivities[0].Draw(this.childsContainer.rows[0].cells[1]);
	}

	#onHideClick()
	{
		// eslint-disable-next-line no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private
		this.Properties._DesMinimized = this.Properties._DesMinimized === 'Y' ? 'N' : 'Y';
		Dom.toggle(this.childsContainer);
		Dom.toggle(this.hideContainer);
	}
}
