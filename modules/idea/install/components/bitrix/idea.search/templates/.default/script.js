BX.ready(function(){
	BX.bind(
		BX('bx-idea-lifesearch-field'),
		'focus',
		function(){
			if(this.value == BX.message('IDEA_SEARCH_DEFAULT'))
				this.value = '';
		}
	);

	BX.bind(
		BX('bx-idea-lifesearch-field'),
		'blur',
		function(){
			if(this.value == "")
				this.value = BX.message('IDEA_SEARCH_DEFAULT');
		}
	);

	BX.fireEvent(BX('bx-idea-lifesearch-field'), 'blur');
})