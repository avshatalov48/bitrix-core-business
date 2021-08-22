import {AdjustFieldController} from "./adjustfieldcontroller";
import {EditFieldController} from "./editfieldcontroller";
import {Type} from "main.core";
import {Resourcebooking, BookingUtil} from "calendar.resourcebooking";
import {customizeCrmEntityEditor} from "./customizecrmentityeditor"
import {CalendarViewSettingsSlider} from "./calendarviewsettingsslider"
import {AdminSettingsViewer} from "./adminsettingsviewer"

export {Resourcebooking, BookingUtil, AdminSettingsViewer};
export class ResourcebookingUserfield
{
	static fieldParamsCache = {};
	static socnetDestination = null;

	/**
	 * Creates instance of Resourcebooking field for crm form edit interface (not for live form)
	 * and initialize it with given field params
	 * Used in CRM webform module to display and adjust resourcebooking field
	 *
	 * @param {array} params - incoming data params
	 */
	static initCrmFormFieldController(params)
	{
		if (!Type.isPlainObject(params))
		{
			params = {
				field:{}
			};
		}

		let bookingFieldParams = {};
		if (Type.isDomNode(params.field.node))
		{
			bookingFieldParams.outerWrap = params.field.node;
		}
		else
		{
			throw new Error("The argument \"params.field.node\" must be a DOM node.");
		}

		bookingFieldParams.innerWrap = bookingFieldParams.outerWrap.querySelector('.crm-webform-resourcebooking-wrap');
		if (!bookingFieldParams.innerWrap)
		{
			throw new Error("Can't find necessary DOM node \"div.crm-webform-resourcebooking-wrap\"");
		}

		bookingFieldParams.name = params.field.name;
		bookingFieldParams.formName = 'FIELD[' + params.field.name + ']';
		bookingFieldParams.captionNode = params.field.lblCaption;
		bookingFieldParams.entityFieldName = params.field.entity_field_name;
		bookingFieldParams.entityName = params.field.dict.entity_field_name;

		bookingFieldParams.settings = {
			caption: params.field.captionValue || params.field.dict.caption,
			required: params.field.isRequired || params.field.dict.required,
			data: (Type.isPlainObject(params.field.booking) && Type.isPlainObject(params.field.booking.settings_data))
				? params.field.booking.settings_data
				: (params.field.settingsData || [])
		};

		let adjustFieldController = new AdjustFieldController(bookingFieldParams);
		adjustFieldController.init();

		return adjustFieldController;
	}

	static initEditFieldController(params)
	{
		let editFieldController = new EditFieldController(params);
		editFieldController.init();

		return editFieldController;
	}

	static getCrmFieldConfigurator(id, settings)
	{
		if(window.BX && BX.Crm && Type.isFunction(BX.Crm.EntityEditorUserFieldConfigurator))
		{
			return customizeCrmEntityEditor(BX.Crm.EntityEditorUserFieldConfigurator).create(id, settings);
		}
	}

	static getUserFieldParams(params = {})
	{
		return new Promise((resolve) => {
			let fieldName = params.fieldName || '';

			if (params.clearCache || !ResourcebookingUserfield.fieldParamsCache[params.fieldName])
			{
				BX.ajax.runAction('calendar.api.resourcebookingajax.getfieldparams', {
					data: {
						fieldname: params.fieldName,
						selectedUsers: params.selectedUsers || []
					}
				}).then((response) => {
						ResourcebookingUserfield.fieldParamsCache[fieldName] = response.data;
						resolve(response.data);
					},
					(response) => {}
				);
			}
			else
			{
				resolve(ResourcebookingUserfield.fieldParamsCache[fieldName]);
			}
		});
	}

	static getPluralMessage(messageId, number)
	{
		let pluralForm, langId;

		langId = BX.message('LANGUAGE_ID') || 'en';
		number = parseInt(number);

		if (number < 0)
		{
			number = -1*number;
		}

		if (langId)
		{
			switch (langId)
			{
				case 'ru':
				case 'ua':

					if ((number % 10 === 1) && (number % 100 !== 11))
					{
						pluralForm = 0;
					}
					else
					{
						pluralForm = ((number%10 >= 2) && (number%10 <= 4) && ((number%100 < 10) || (number%100 >= 20)))
							? 1
							: 2;
					}
					break;
				case 'pl':
					if (number <= 4)
					{
						pluralForm = number === 1 ? 0 : 1;
					}
					else
					{
						pluralForm = 2;
					}
					break;
				default: // en, de and other languages
					pluralForm = (number !== 1) ? 1 : 0;
					break;
			}
		}
		else
		{
			pluralForm = 1;
		}

		return BX.message(messageId + '_PLURAL_' + pluralForm);
	}

	static getParamsFromHash(userfieldId)
	{
		let
			params, regRes,
			hash = unescape(window.location.hash);

		if (hash)
		{
			regRes = new RegExp('#calendar:' + userfieldId + '\\|(.*)', 'ig').exec(hash);
			if (regRes && regRes.length > 1)
			{
				params = regRes[1].split('|');
			}
		}
		return params;
	}

	static openExternalSettingsSlider(params)
	{
		let settingsSlider = new CalendarViewSettingsSlider(params);
		settingsSlider.show();
	}

	static setSocnetDestination(socnetDestination)
	{
		ResourcebookingUserfield.socnetDestination = socnetDestination;
	}

	static getSocnetDestination()
	{
		return ResourcebookingUserfield.socnetDestination;
	}
}