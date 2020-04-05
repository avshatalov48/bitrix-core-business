BX.namespace('BX.Sale.PersonalProfileComponent');

(function() {
	BX.Sale.PrivateProfileComponent = {
		init: function ()
		{
			var passwordNode = BX('main-profile-password');
			var confirmNode = BX('main-profile-password-confirm');
			BX.ready(function(){
				BX.bind(confirmNode, 'input', function(){
					if (!BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (!BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				});
				BX.bind(passwordNode, 'input', function(){
					if (BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				})
			});
		},
	}
})();