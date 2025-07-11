document.querySelector('.contact-form').addEventListener('submit', function(e) {
    e.preventDefault();

    emailjs.sendForm('service_zn3vnvi', 'template_p06x8ci', this)
        .then(function() {
            alert('Your message has been sent!');
        }, function(error) {
            alert('Failed to send message. Please try again later.');
        });
});