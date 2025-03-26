/* global window, document  */
window.simple_password_policy = window.simple_password_policy || [];
window.simple_password_policy.functions = window.simple_password_policy.functions || [];
/**
 * functions
 */
window.simple_password_policy.functions.passed = function( element_to_change, condition ) {
	element_to_change.innerHTML = condition.messages.pass;
	element_to_change.classList.add('passed');
};
window.simple_password_policy.functions.failed = function( element_to_change, condition ) {
	element_to_change.innerHTML = condition.messages.ask;
	element_to_change.classList.remove('passed');
};
window.simple_password_policy.functions.check = function( element ) {
	var settings = window.iworks_simple_password_policy_data;
	var value = element.value;
	settings.conditions.forEach( function( condition ) {
		var element_to_change = document.getElementById( condition.id );
		if ( 'string' === typeof condition.regexp ) {
			var re = new RegExp( condition.regexp );
			if ( re.test(value) ) {
				window.simple_password_policy.functions.passed( element_to_change, condition );
			} else {
				window.simple_password_policy.functions.failed( element_to_change, condition );
			}
		} else{
			switch( condition.option_name ) {
				case 'length':
					if ( value.length >= condition.use ) {
						window.simple_password_policy.functions.passed( element_to_change, condition );
					} else {
						window.simple_password_policy.functions.failed( element_to_change, condition );
					}
					break;
			}
		}
	});
};
/**
 * load
 */
window.addEventListener('load', function(event) {
	var settings = window.iworks_simple_password_policy_data;
	var list;
	/**
	 * check conditions
	 */
	if ( 0 === settings.conditions.length) {
		return;
	}
	/**
	 * check elements
	 */
	list = document.getElementById(settings.list.id);
	if ( ! list ) {
		return;
	}
	settings.fields.ids.forEach( function( element_id, index ) {
		var element = document.getElementById(element_id);
		if ( element ) {
			window.simple_password_policy.functions.check( element );
			'change focus inputs cancel select keyup keydown load'.split(' ').forEach( function(event) {
				element.addEventListener( event, function( e ) {
					window.simple_password_policy.functions.check( element );
				});
			});
		}
	});
});

