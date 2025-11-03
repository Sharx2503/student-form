$(document).ready(function() {
    // Phone number formatting
    $('#phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Zipcode formatting
    $('#zipcode').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Submitting...');
        
        // Get form data
        const formData = $(this).serialize();
        
        // AJAX request
        $.ajax({
            url: 'process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide form
                    $('#registrationForm').hide();
                    
                    // Display success message with formatted data
                    const successHTML = `
                        <h2>✓ Registration Successful!</h2>
                        <p style="text-align: center; margin-bottom: 20px;">
                            Thank you for registering. Your application ID is: 
                            <strong>#${response.data.id}</strong>
                        </p>
                        <div class="success-content">
                            <h3 style="margin-bottom: 15px; color: #667eea;">Registration Details</h3>
                            
                            <div class="info-row">
                                <div class="info-label">Full Name:</div>
                                <div class="info-value">${response.data.firstName} ${response.data.lastName}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">${response.data.email}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value">${response.data.phone}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Date of Birth:</div>
                                <div class="info-value">${formatDate(response.data.dob)}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Gender:</div>
                                <div class="info-value">${response.data.gender}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Address:</div>
                                <div class="info-value">${response.data.address}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">City:</div>
                                <div class="info-value">${response.data.city}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">State:</div>
                                <div class="info-value">${response.data.state}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Zip Code:</div>
                                <div class="info-value">${response.data.zipcode}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Country:</div>
                                <div class="info-value">${response.data.country}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Qualification:</div>
                                <div class="info-value">${response.data.qualification}</div>
                            </div>
                            
                            ${response.data.comments ? `
                            <div class="info-row">
                                <div class="info-label">Comments:</div>
                                <div class="info-value">${response.data.comments}</div>
                            </div>
                            ` : ''}
                            
                            <div class="info-row">
                                <div class="info-label">Submitted On:</div>
                                <div class="info-value">${formatDateTime(response.data.createdAt)}</div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 25px;">
                            <button onclick="location.reload()" class="btn btn-primary">
                                Submit Another Application
                            </button>
                        </div>
                    `;
                    
                    $('#successMessage').html(successHTML).fadeIn('slow');
                    
                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: $('#successMessage').offset().top - 100
                    }, 500);
                    
                } else {
                    showError(response.message || 'An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                showError('Server error. Please try again later.');
                submitBtn.prop('disabled', false).text(originalText);
                console.error('Error:', error);
            }
        });
    });
    
    // Format date function
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }
    
    // Format datetime function
    function formatDateTime(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }
    
    // Show error message
    function showError(message) {
        const errorHTML = `
            <div class="error-message">
                <strong>Error:</strong> ${message}
            </div>
        `;
        $('#successMessage').html(errorHTML).fadeIn('slow');
        
        setTimeout(function() {
            $('#successMessage').fadeOut('slow', function() {
                $(this).empty();
            });
        }, 5000);
    }
    
    // Form validation feedback
    $('input, select, textarea').on('blur', function() {
        if (this.checkValidity()) {
            $(this).css('border-color', '#28a745');
        } else {
            $(this).css('border-color', '#e74c3c');
        }
    });
    
    $('input, select, textarea').on('focus', function() {
        $(this).css('border-color', '#667eea');
    });
});