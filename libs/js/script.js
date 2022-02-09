// Setting default date selector to today's date.
$('#email-date').val(new Date().toISOString().split("T")[0]);

$.ajax({
	url: 'libs/php/getLabels.php',
	type: 'GET',
	dataType: 'json',
	success: result => {
		data = result.data;
		data.forEach(label => $('#label').append('<option selected value="' + label.id + '">' + label.name + '</option>'));
		//$(`#label[value="${data[0].id}"]`).attr("selected",true);
	},
	error: error => console.error(error)
});

// Fetching Gmail API
$('#gmail').click(() => {
	// getting the event date from the input.
	let date = $('#email-date').val();
	// If user didn't choose event date, then prompt him to select one.
	if (date == '') {
		$('#gmail-row').html(`
					<h4 style="color: red"> MAKE SURE TO SELECT A DATE, THEN SUBMIT!!</h4>
		`);
	} else {
		// using ajax to make a POST request to getEvents.php, with the date chosen by the user, as a parameter.
		$.ajax({
			url: 'libs/php/getEmails.php',
			type: 'POST',
			dataType: 'json',
			data: {
				date: new Date(date).toISOString(),
				label: $('#label').val(),
			},
			success: result => {
				console.log(result);
				$('#gmail-row').html('');
				// If it was successfull
				if (result.status.name == 'ok') {
					if (result.data.length == 0) {
						// Tell the user there is no events for this day.
						$('#gmail-row').html(`
							<h4 style="color: red"> There are no emails for this day and category!!</h4>
						`);
					} else {
						// For each email, show its information.
						result.data.forEach(email => {
							let from, to;
							email.payload.headers.forEach(
								header => header.name == 'From'
									? from = header.value
									: header.name == 'To' ? to = header.value : null);
							$('#gmail-row').append(`
							<div class="card" >
							<div class="card-body">
							  		<h5 class="card-title">from ${from + ' to ' + to}</h5>
									<p class="card-text">${email.snippet}</p>
								</div>
							</div>
							`)
						});

					}
				}
			},
			// In case of any error, log the errors, also let the user, there has been an internal error and ask him to try again
			// later
			error: (jqXHR, textStatus, errorThrown) => {
				console.log(jqXHR, textStatus, errorThrown);
				$('#google-calendar-row').html(`
					<h4 style="color: red"> THERE HAS BEEN AN INTERNAL ERROR, PLEASE TRY AGAIN LATER!!</h4>
				`);
			}
		})
	}
})