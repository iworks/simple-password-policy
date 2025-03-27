/* global window, document, setTimeout  */
window.simple_password_policy = window.simple_password_policy || [];
window.simple_password_policy.functions = window.simple_password_policy.functions || [];
/**
 * function: init
 */
window.simple_password_policy.functions.init = function() {
	if ( window.simple_password_policy.functions.is_available() ) {
		window.iworks_simple_password_policy_data.fields.ids.forEach( function( element_id, index ) {
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
	}
};
/**
 * function: is_available
 */
window.simple_password_policy.functions.is_available = function() {
	/**
	 * check conditions
	 */
	if ( 0 === window.iworks_simple_password_policy_data.conditions.length) {
		return false;
	}
	/**
	 * check elements
	 */
	return document.getElementById(window.iworks_simple_password_policy_data.list.id)? true:false;
};
/**
 * function: passed
 */
window.simple_password_policy.functions.passed = function( element_to_change, condition ) {
	element_to_change.innerHTML = condition.messages.pass;
	element_to_change.classList.add('passed');
};
/**
 * function: failed
 */
window.simple_password_policy.functions.failed = function( element_to_change, condition ) {
	element_to_change.innerHTML = condition.messages.ask;
	element_to_change.classList.remove('passed');
};
/**
 * function: check
 */
window.simple_password_policy.functions.check = function( element ) {
	var value = element.value;
	var success = true;
	window.iworks_simple_password_policy_data.conditions.forEach( function( condition ) {
		var element_to_change = document.getElementById( condition.id );
		if ( 'string' === typeof condition.regexp ) {
			var re = new RegExp( condition.regexp );
			if ( re.test(value) ) {
				window.simple_password_policy.functions.passed( element_to_change, condition );
			} else {
				success = false;
				window.simple_password_policy.functions.failed( element_to_change, condition );
			}
		} else{
			switch( condition.option_name ) {
				case 'length':
					window.console.log ( value, value.length, condition.use, value.length >= condition.use );
					if ( value.length >= condition.use ) {
						window.simple_password_policy.functions.passed( element_to_change, condition );
					} else {
						success = false;
						window.simple_password_policy.functions.failed( element_to_change, condition );
					}
					break;
			}
		}
		if ( success ) {
			document.getElementById('wp-submit').removeAttribute('disabled');
		} else {
			document.getElementById('wp-submit').setAttribute( 'disabled', 'disabled' );
		}
	});
};
/**
 * load
 */
window.addEventListener('load', function(event) {
	window.simple_password_policy.functions.init();
	setTimeout(
		function() {
			if ( window.simple_password_policy.functions.is_available() ) {
				window.iworks_simple_password_policy_data.fields.ids.forEach( function( element_id, index ) {
					var element = document.getElementById(element_id);
					if ( element ) {
						window.simple_password_policy.functions.check( element );
					}
				});
			}
		},
		1000
	);
});


