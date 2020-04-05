;(function(){
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Field");
	BX.Report.VisualConstructor.Field.Base = function(options)
	{
		this.form = options.form || null;
		this.events = options.events || [];
		this.behaviours = options.behaviours || [];
		this.fieldScope = options.fieldScope || null;

		if (this.fieldScope)
		{
			this.form = BX.findParent(this.fieldScope, {
				tag: 'form'
			});
		}

		for (var b = 0; b < this.behaviours.length; b++)
		{
			var behaviour = this.behaviours[b];
			BX.addCustomEvent(this.fieldScope, behaviour['eventName'], this.baseEventHandler.bind(this, behaviour));
		}


		BX.Report.VisualConstructor.Field.InitFieldRepository.add(this);
	};

	BX.Report.VisualConstructor.Field.Base.prototype = {
		baseEventHandler: function(event, ownerElement, optionsFromEvent)
		{
			var currentFieldDomeElement = this.getForm().querySelector(event['behaviorOwnerSelector']);
			var options = {
				action: event['handlerParams']['action'],
				currentField: currentFieldDomeElement,
				ownerField: ownerElement.fieldScope,
				additionalParams: event['handlerParams']['additionalParams'] || [],
				currentFieldObject: this.findElementInRenderedElementsRepository(currentFieldDomeElement),
				ownerFieldObject: ownerElement,
				optionsFromEvent: optionsFromEvent
			};
			var eventHandler = null;
			if (event.handlerParams.class)
			{
				var callbackClass = BX.Report.VC.Core.getClass(event.handlerParams.class);
				if (callbackClass)
				{
					eventHandler = new callbackClass(options);
					eventHandler.process();
				}
				else
				{
					throw "Class with name: " + event.handlerParams.class + " not exist";
				}

			}
			else
			{
				eventHandler = new BX.Report.VisualConstructor.Field.BaseHandler(options);
				eventHandler.process();
			}

		},
		findElementInRenderedElementsRepository: function(domElement)
		{
			return BX.Report.VisualConstructor.Field.InitFieldRepository.getByDomElement(domElement);
		},
		getForm: function()
		{
			return this.form;
		}
	};


	BX.Report.VisualConstructor.Field.BaseHandler = function(options)
	{
		this.action = options.action || '';
		this.currentField = options.currentField || {};
		this.currentFieldObject = options.currentFieldObject || {};
		this.ownerField = options.ownerField || {};
		this.ownerFieldObject = options.ownerFieldObject || {};
		this.additionalParams = options.additionalParams || {};
		this.optionsFromEvent = options.optionsFromEvent || {};
	};

	BX.Report.VisualConstructor.Field.BaseHandler.prototype = {
		//TODO: refactor to map @important
		process: function()
		{
			switch (this.action)
			{
				case 'setValue':
					this.setValue();
					break;
			}
		},
		setValue: function()
		{
			this.currentField.value =  this.additionalParams.value;
		}
	};


	BX.Report.VisualConstructor.Field.InitFieldRepository = {
		fields: [],
		add: function(field)
		{
			this.fields.push(field)
		},
		getByDomElement: function(element)
		{
			for (var i in this.fields)
			{
				if (this.fields[i].fieldScope === element)
				{
					return 	this.fields[i];
				}
			}
			return null;
		}
	}

})();