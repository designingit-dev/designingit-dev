$(function() {
  var email = document.getElementById("fields-email")
    , confirm_email = document.getElementById("fields-confirmEmail")

  if(!email || !confirm_email) { return }

  function validateEmail(){
    if(email.value != confirm_email.value) {
      confirm_email.setCustomValidity("Email addresses must match.")
    } else {
      confirm_email.setCustomValidity('')
    }
  }

  email.onchange = validateEmail
  confirm_email.onkeyup = validateEmail
});

