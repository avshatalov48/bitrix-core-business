exports.Task = extend(TolokaHandlebarsTask, function(options) {
	TolokaHandlebarsTask.call(this, options);
}, {
	validate: function(solution) {
		if (!this.getTask().videoPlayed)
		{
			return {
				task_id: this.getTask().id,
				errors: {
					"__TASK__": {
						message: "You didn't complete task"
					}
				}
			};
		}
		else
		{
			return TolokaHandlebarsTask.prototype.validate.apply(this, arguments);
		}
	},

	onRender: function() {
		var videos = this.getDOMElement().querySelectorAll("button");
		this.getTask().videoPlayed = false;
		for (var i = 0, l = videos.length; i < l; i++)
		{
			this.getTask().href = videos[i].getAttribute('data-href');
			videos[i].addEventListener('click', function() {
				window.open(this.getTask().href, '_blank');
				this.getTask().videoPlayed = true;
			}.bind(this));
		}
	}
});

function extend(ParentClass, constructorFunction, prototypeHash)
{
	constructorFunction = constructorFunction || function() {
	};
	prototypeHash = prototypeHash || {};

	if (ParentClass)
	{
		constructorFunction.prototype = Object.create(ParentClass.prototype);
	}
	for (var i in prototypeHash)
	{
		constructorFunction.prototype[i] = prototypeHash[i];
	}
	return constructorFunction;
}