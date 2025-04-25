document.getElementById("superAdminSignupForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const name = document.getElementById("name")?.value.trim();
    const email = document.getElementById("email")?.value.trim();
    const staff_number = document.getElementById("staff_number")?.value.trim();
    const designation = document.getElementById("designation")?.value.trim();
    const phone_number = document.getElementById("phone_number")?.value.trim();
    const password = document.getElementById("password")?.value;
    const confirmPassword = document.getElementById("confirm_password")?.value;

    const messageBox = document.getElementById("signupMessage");

    if (!name || !email || !staff_number || !designation || !phone_number || !password || !confirmPassword) {
        messageBox.textContent = "Please fill in all fields!";
        return;
    }

    if (password !== confirmPassword) {
        messageBox.textContent = "Passwords do not match!";
        return;
    }

    if (!validatePassword(password)) {
        messageBox.textContent = "Password does not meet strength requirements.";
        return;
    }

    const formData = {
        name,
        email,
        staff_number,
        designation,
        phone_number,
        password
    };

    try {
        const res = await fetch("../api/auth/superadmin_signup.php", {
            method: "POST",
            body: JSON.stringify(formData),
            headers: { "Content-Type": "application/json" }
        });

        const data = await res.json();
        messageBox.textContent = data.message;

        if (data.success) {
            alert("Super Admin registered successfully! Redirecting to login...");
            window.location.href = "login.html";
        }
    } catch (error) {
        console.error("Error:", error);
        messageBox.textContent = "Something went wrong!";
    }
});

function validatePassword(password) {
    return (
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[\W]/.test(password)
    );
}
