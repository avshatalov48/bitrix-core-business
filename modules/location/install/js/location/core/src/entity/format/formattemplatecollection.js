import FormatTemplate from './formattemplate';

export default class FormatTemplateCollection
{
	#templates = {};

	constructor(templateData: {})
	{
		for (const type in templateData)
		{
			// eslint-disable-next-line no-prototype-builtins
			if (templateData.hasOwnProperty(type))
			{
				this.setTemplate(
					new FormatTemplate(type, templateData[type])
				);
			}
		}
	}

	isTemplateExists(type: string): boolean
	{
		return typeof this.#templates[type] !== 'undefined';
	}

	getTemplate(type: string): string
	{
		return this.isTemplateExists(type) ? this.#templates[type] : null;
	}

	setTemplate(template: FormatTemplate)
	{
		if (!(template instanceof FormatTemplate))
		{
			throw new Error('Argument template must be instance of FormatTemplate!');
		}

		this.#templates[template.type] = template;
	}
}