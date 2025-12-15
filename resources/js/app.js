// Modal functionality
$(document).ready(function() {
    // Open modal when login link is clicked
    $('#login-link').on('click', function(e) {
        e.preventDefault();
        $('#application-modal').addClass('is-active');
        $('body').css('overflow', 'hidden'); // Prevent body scroll
    });

    // Close modal when close button is clicked
    $('#modal-close, .c-modal-overlay').on('click', function() {
        $('#application-modal').removeClass('is-active');
        $('body').css('overflow', ''); // Restore body scroll
    });

    // Close modal on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#application-modal').hasClass('is-active')) {
            $('#application-modal').removeClass('is-active');
            $('body').css('overflow', '');
        }
    });
});

