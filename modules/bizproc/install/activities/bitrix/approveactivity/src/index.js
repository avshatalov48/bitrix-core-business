import { Tag, Text } from 'main.core';

const ParallelActivity = window.ParallelActivity;

export class ApproveActivity extends ParallelActivity
{
	constructor()
	{
		super();
		this.Type = 'ApproveActivity';
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
		this.__parallelActivityInitType = 'SequenceActivity';

		// compatibility
		this.DrawParallelActivity = this.Draw;
		this.Draw = this.#draw;
	}

	#draw(wrapper)
	{
		this.activityContent = Tag.render`
			<table style="font-size: 11px; width: 100%" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td align="left" valign="center" width="33">
							&nbsp;<span style="color: #007700">${Text.encode(window.BPMESS.APPR_YES)}</span>
						</td>
						<td 
							align="center"
							valign="center"
							style="background: url(${this.Icon}) 2px 2px no-repeat; height: 24px; width: 24px"
						></td>
						<td align="left" valign="center">${Text.encode(this.Properties.Title)}</td>
						<td align="right" valign="center">
							<span style="color: #770000">${Text.encode(window.BPMESS.APPR_NO)}</span>&nbsp;
						</td>
					</tr>
				</tbody>
			</table>
		`;
		this.activityHeight = '30px';
		this.activityWidth = '200px';

		this.DrawParallelActivity(wrapper);
	}
}
