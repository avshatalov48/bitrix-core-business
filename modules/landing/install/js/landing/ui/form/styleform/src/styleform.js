import { Dom, Event, Type, Tag, Loc } from 'main.core';
import { BaseForm } from 'landing.ui.form.baseform';
import { Highlight } from 'landing.ui.highlight';
import { BaseField } from 'landing.ui.field.basefield';
import {Env} from 'landing.env';
import { fetchEventsFromOptions } from 'landing.ui.component.internal';

import './css/style_form.css';
import 'ui.design-tokens';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class StyleForm extends BaseForm
{
	#styleFields: Map;

	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Form.StyleForm');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		Dom.addClass(this.layout, 'landing-ui-form-style');
		this.iframe = 'iframe' in options ? options.iframe : null;
		this.node = 'node' in options ? options.node : null;
		this.selector = 'selector' in options ? options.selector : null;
		this.collapsed = 'collapsed' in options ? options.collapsed : null;
		this.currentTarget = 'currentTarget' in options ? options.currentTarget : null;
		this.specialType = 'specialType' in options ? options.specialType : null;
		this.#styleFields = new Map();

		this.onHeaderEnter = this.onHeaderEnter.bind(this);
		this.onHeaderLeave = this.onHeaderLeave.bind(this);
		this.onHeaderClick = this.onHeaderClick.bind(this);

		this.prepareHeader();

		Event.bind(this.header, 'click', this.onHeaderClick);
		Event.bind(this.header, 'mouseenter', this.onHeaderEnter);
		Event.bind(this.header, 'mouseleave', this.onHeaderLeave);

		if (this.iframe)
		{
			this.onFrameLoad();
		}

		if (this.collapsed)
		{
			Dom.addClass(this.layout, 'landing-ui-form-style--collapsed');
		}

		if (
			this.specialType && this.specialType === 'crm_forms'
			&& Env.getInstance().getOptions().specialType === 'crm_forms'
		)
		{
			this.#addReplaceByTemplateCard();
		}
	}

	onFrameLoad()
	{
		if (!this.node)
		{
			this.node = [...this.iframe.document.querySelectorAll(this.selector)];
		}
	}

	onHeaderEnter()
	{
		Highlight.getInstance().show(this.node);
	}

	// eslint-disable-next-line class-methods-use-this
	onHeaderLeave()
	{
		Highlight.getInstance().hide();
	}

	// eslint-disable-next-line class-methods-use-this
	onHeaderClick(event: MouseEvent)
	{
		event.preventDefault();
		Dom.toggleClass(this.layout, 'landing-ui-form-style--collapsed');
	}

	addField(field: BaseField)
	{
		if (field)
		{
			const attrKey = field?.data?.attrKey;

			field.subscribe('onChange', this.onChange.bind(this));
			field.subscribe('onInit', this.onInit.bind(this));

			this.fields.add(field);
			BX.Dom.append(field.layout, this.body);

			if (attrKey)
			{
				this.#styleFields.set(attrKey, field.getLayout());
			}
		}
	}

	onChange(event)
	{
		this.#toggleLinkedFields(event.getData());
		this.emit('onChange');
	}

	onInit(event)
	{
		this.#toggleLinkedFields(event.getData());
		this.emit('onInit');
	}

	#toggleLinkedFields(fieldData: Object)
	{
		// hide linked fields
		if (fieldData.hide && Type.isArray(fieldData.hide))
		{
			fieldData.hide.map((attr) => {
				const layout = this.#styleFields.get(attr);
				if (layout)
				{
					BX.Dom.style(layout, 'display', 'none');
				}

				return null;
			});
		}

		// show linked fields
		if (fieldData.show && Type.isArray(fieldData.show))
		{
			fieldData.show.map((attr) => {
				const layout = this.#styleFields.get(attr);
				if (layout)
				{
					BX.Dom.style(layout, 'display', 'block');
				}

				return null;
			});
		}
	}

	prepareHeader()
	{
		const headerText = BX.Dom.create({
			tag: 'div',
			props: {
				classList: 'landing-ui-form-header-text',
			},
		});
		if (this.header.childNodes)
		{
			this.header.childNodes.forEach((childNode) => {
				BX.Dom.append(childNode, headerText);
			});
		}
		BX.Dom.append(headerText, this.header);
	}

	#addReplaceByTemplateCard()
	{
		const isMinisitesAllowed = Env.getInstance().getOptions().allow_minisites;

		const lockIcon = (
			isMinisitesAllowed
				? ''
				: Tag.render`<span class="landing-ui-form-lock-icon"></span>`
		);
		const button = Tag.render`
			<span class="landing-ui-form-replace-by-templates-card-button ui-btn ui-btn-sm ui-btn-primary ui-btn-hover ui-btn-round">
				${Loc.getMessage('LANDING_REPLACE_BY_TEMPLATES_BUTTON')}
				${lockIcon}
			</span>
		`;
		const card = Tag.render`<div class="landing-ui-form-replace-by-templates-card">
			<div class="landing-ui-form-replace-by-templates-card-title">
				${Loc.getMessage('LANDING_REPLACE_BY_TEMPLATES_TITLE')}
			</div>
			${button}
		</div>`;
		Dom.insertBefore(card, this.header);

		Event.bind(button, 'click', () => {
			if (!isMinisitesAllowed)
			{
				BX.UI.InfoHelper.show('limit_crm_forms_templates');

				return;
			}

			// todo: migrate to new analytics?
			const metrika = new BX.Landing.Metrika(true);
			metrika.sendLabel(
				null,
				'templateMarket',
				'open&replaceLid=' + landingParams['LANDING_ID']
			);

			const templatesMarketUrl = landingParams['PAGE_URL_LANDING_REPLACE_FROM_STYLE'];
			if (templatesMarketUrl)
			{
				BX.SidePanel.Instance.open(
					templatesMarketUrl,
					{
						allowChangeHistory: false,
						cacheable: false,
						customLeftBoundary: 0,
					}
				);
			}
		});
	}
}
