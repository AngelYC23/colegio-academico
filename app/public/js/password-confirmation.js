document.querySelectorAll('form').forEach((form) => {
    const password = form.querySelector('input[name="password"]');
    const confirmation = form.querySelector('input[name="password_confirmation"]');
    if (!password || !confirmation) return;
    const validate = () => {
        confirmation.required = password.required || password.value.length > 0;
        const different = confirmation.value.length > 0 && confirmation.value !== password.value;
        confirmation.setCustomValidity(different ? 'La confirmación debe ser exactamente igual a la contraseña.' : '');
        return !different;
    };
    password.addEventListener('input', validate);
    confirmation.addEventListener('input', validate);
    form.addEventListener('submit', (event) => {
        if (!validate()) {
            event.preventDefault();
            confirmation.reportValidity();
        }
    });
});
