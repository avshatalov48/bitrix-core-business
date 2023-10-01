import { Dom, Event, Type } from 'main.core';
import { BaseForm } from 'landing.ui.form.baseform';
import { Highlight } from 'landing.ui.highlight';
import { BaseField } from 'landing.ui.field.basefield';
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
		const headerText = document.createElement('div');
		BX.Dom.addClass(headerText, 'landing-ui-form-header-text');
		while (this.header.childNodes[0])
		{
			BX.Dom.append(this.header.childNodes[0], headerText);
		}
		BX.Dom.append(headerText, this.header);
	}
}
