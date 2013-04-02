head.ready(function() {
	$('form').submit(function() {
		//validation filter
		var mailRegex = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var errorMSG = '';
		//remove previous error
		$('#mailGroup').removeClass('error');
		$('#passGroup').removeClass('error');
		//check email
		if ($('#txtMail').val() == "") {
			errorMSG += "<li>Enter your email.</li>";
			$('#mailGroup').addClass('error');
		} else {
			//validate email
			if (mailRegex.test($('#txtMail').val()) == false) {
				errorMSG += "<li>Your email is invalid.</li>";
				$('#mailGroup').addClass('error');
			}
		}
		//check password
		if ($('#txtPass').val() == "") {
			errorMSG += "<li>Enter Your password.</li>";
			$('#passGroup').addClass('error');
		}
		//error trap
		if(errorMSG == '') {
			return true;
		} else {
			$('#error').html('<div class="alert alert-error"><h3>Error!</h3><ul>'+errorMSG+'</ul></div>');
			return false;
		}
	});
});
