const passwordInput = document.getElementById("newPassword");
const confirmPasswordInput = document.getElementById("confirmPassword");
const requirementsList = document.getElementById("passwordRequirements");

const requirements = {
  length: /^.{8,}$/,
  uppercase: /[A-Z]/,
  lowercase: /[a-z]/,
  number: /[0-9]/,
  symbol: /[!@#\$%\^&\*]/,
};

// Show requirements when focusing password field
passwordInput.addEventListener("focus", () => {
  requirementsList.style.display = "block";
});

// Live check of password input
passwordInput.addEventListener("input", () => {
  const value = passwordInput.value;
  for (const key in requirements) {
    const item = document.getElementById(key);
    item.style.color = requirements[key].test(value) ? "green" : "red";
  }
});


window.addEventListener("DOMContentLoaded", () => {
    const storedEmail = sessionStorage.getItem("forgotEmail");
    if (storedEmail) {
      document.getElementById("email").value = storedEmail;
    }
  });
  
  document.getElementById("forgotForm").addEventListener("submit", async (e) => {
    e.preventDefault();
  
    const email = document.getElementById("email").value.trim();
    const staffNumber = document.getElementById("staffNumber").value.trim();
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const passwordFields = document.getElementById("passwordFields");
    const submitBtn = document.getElementById("submitBtn");
  
    if (passwordFields.style.display === "none") {
      // First step: verify staff number
      try {
        const res = await fetch("../api/auth/verify_staff.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, staff_number: staffNumber })
        });
  
        const data = await res.json();
        if (data.success) {
          alert("Verification successful! You can now reset your password.");
  
          passwordFields.style.display = "block";
          submitBtn.textContent = "Reset Password";
          document.getElementById("staffNumber").readOnly = true;
  
          // Dynamically make password fields required
          document.getElementById("newPassword").required = true;
          document.getElementById("confirmPassword").required = true;
        } else {
          alert(data.message);
        }
      } catch (err) {
        alert("Verification failed. Please try again.");
        console.error(err);
      }
      return;
    }
  
    // Second step: reset password
    const isValid = Object.values(requirements).every(regex => regex.test(newPassword));
if (!isValid) {
  alert("Password does not meet all requirements.");
  return;
}

    if (newPassword !== confirmPassword) {
      alert("Passwords do not match!");
      return;
    }
  
    try {
      const res = await fetch("../api/auth/reset_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password: newPassword })
      });
  
      const data = await res.json();
      if (data.success) {
        alert("Password reset successful!");
        sessionStorage.removeItem("forgotEmail");
        window.location.href = "login.html";
      } else {
        alert(data.message);
      }
    } catch (error) {
      alert("Error resetting password.");
      console.error(error);
    }
  });
  