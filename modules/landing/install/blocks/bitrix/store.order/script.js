BX.ready(function()
{
	$('form input[type="text"]').on('keydown', function(event)
	{
		if (event.keyCode === 13)
		{
			let element  = event.currentTarget;

			$(element).blur();
			event.preventDefault();
			event.stopPropagation();
		}
	});
});
