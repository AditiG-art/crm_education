const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";

        // Password is visible → show normal eye
        this.classList.remove("fa-eye-slash");
        this.classList.add("fa-eye");

    } 
    else {

        password.type = "password";

        // Password is hidden → show crossed eye
        this.classList.remove("fa-eye");
        this.classList.add("fa-eye-slash");

    }

});