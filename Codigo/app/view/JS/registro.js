const username = document.getElementById('username');
const email = document.getElementById('email');
const password = document.getElementById('password');
const repeatPassword = document.getElementById('repeatPassword');
const passwordError = document.getElementById('passwordError');
const submitBtn = document.getElementById('submitBtn');

// Función para cambiar el estilo de validación (sin validaciones por ahora)
function toggleValidationStyle(input, isValid) {
  if (input.value.length === 0) {
    input.classList.remove('valid-input', 'invalid-input');
    return;
  }

  if (isValid) {
    input.classList.add('valid-input');
    input.classList.remove('invalid-input');
  } else {
    input.classList.add('invalid-input');
    input.classList.remove('valid-input');
  }
}

// Función para validar si las contraseñas coinciden
function validatePasswordMatch() {
  const match = password.value === repeatPassword.value;
  toggleValidationStyle(repeatPassword, match);
  passwordError.style.display = match || repeatPassword.value.length === 0 ? 'none' : 'block';
}

// Eventos de entrada para las contraseñas
password.addEventListener('input', validatePasswordMatch);
repeatPassword.addEventListener('input', validatePasswordMatch);

// Comprobamos si las contraseñas coinciden al enviar el formulario
document.getElementById("signupForm").addEventListener("submit", function(event) {
  if (password.value !== repeatPassword.value) {
    event.preventDefault(); // Evitar que el formulario se envíe
    passwordError.style.display = 'block'; // Mostrar el mensaje de error
  }
});
