(function () {
    var emailElements = document.querySelectorAll('[data-admin-email]');

    if (!emailElements.length) {
        return;
    }

    var endpoint = new URL('admin-email.json', window.location.href);

    fetch(endpoint.toString(), {
        cache: 'no-store',
        headers: {
            Accept: 'application/json'
        }
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to load email');
            }

            return response.json();
        })
        .then(function (data) {
            if (!data.email) {
                throw new Error('Email not found');
            }

            emailElements.forEach(function (element) {
                element.textContent = data.email;
                element.setAttribute('href', 'mailto:' + data.email);
            });
        })
        .catch(function () {
            emailElements.forEach(function (element) {
                element.textContent = 'Email unavailable';
                element.removeAttribute('href');
            });
        });
})();
