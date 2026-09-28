const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phoneRegex = /^[0-9+\-\s]{7,15}$/;

function showError(fieldId, message) {
  document.getElementById(fieldId + '-error').textContent = message;
}

document.getElementById('registerForm').addEventListener('submit', function (event) {
  let formIsValid = true;

  const errorMessages = document.querySelectorAll('.error-msg');
  errorMessages.forEach(function (span) {
    span.textContent = '';
  });

  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const pass = document.getElementById('pass').value;
  const city = document.getElementById('city').value.trim();
  const contact = document.getElementById('contact').value.trim();

  if (name === '') {
    showError('name', 'Please enter your full name.');
    formIsValid = false;
  }

  if (!emailRegex.test(email)) {
    showError('email', 'Please enter a valid email address.');
    formIsValid = false;
  }

  if (pass.length < 6) {
    showError('pass', 'Password must be at least 6 characters.');
    formIsValid = false;
  }

  if (city === '') {
    showError('city', 'Please enter your city.');
    formIsValid = false;
  }

  if (!phoneRegex.test(contact)) {
    showError('contact', 'Please enter a valid phone number.');
    formIsValid = false;
  }

  if (!formIsValid) {
    event.preventDefault();
  }
});