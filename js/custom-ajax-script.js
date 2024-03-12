jQuery(document).ready(function($) {
    $('#submit_data').click(function() {
        // Client-side validation
        if (validateForm()) {
            // Show loader icon
            $('.loader').show();

            // Gather form data as an array
            var formData = $('#custom-input-form').serializeArray();

            // Convert the form data array to an object
            var formDataObject = {};
            $.each(formData, function(index, field) {
                formDataObject[field.name] = field.value;
            });

            // AJAX request
            $.ajax({
                type: 'POST',
                url: custom_ajax_object.ajax_url,
                data: {
                    action: 'handle_custom_input_form_submission',
                    security: custom_ajax_object.nonce,
                    formData: formDataObject,
                },
                success: function(response) {
                    // Hide loader icon on success
                    $('.loader').hide();

                    if (response.data.status == 'success') {
                        // Access the image URL and append image tag to a specific div
                        console.log(response.data.image_url);
                         window.location.href = '../game/?response=' +response.data.image_url;

                    }
					else if(response.data.status == 'not_found') {
						$('#full_image').html(response.data.message);
						console.log(response.data.message);
					}
					else {
                     $('#full_image').html('');

                    }
					
                },
                error: function(error) {
                    console.log(error.responseText);
                    // Hide loader icon on error
                    $('.loader').hide();
                    // Handle error
                },
            });
        }
    });

	 // Function to validate form fields
	function validateForm() {
		var isValid = true;
		$('#custom-input-form .validation-error-message').remove();
		$('#custom-input-form [required]').each(function() {
			if ($(this).val().trim() === '') {
				isValid = false;
				// Optionally, you can add visual indications for the user (e.g., red borders)
				$(this).addClass('validation-error');

				// Add a span for validation message
				$('<span class="validation-error-message">This field is required.</span>').insertAfter($(this));
			} else {
				// Remove any previous validation errors
				$(this).removeClass('validation-error');
			}
		});

		// Return the validation result
		return isValid;
	}

});
