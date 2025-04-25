// window.addEventListener("DOMContentLoaded", () => {
//     const emailField = document.getElementById("email");
//     const storedEmail = sessionStorage.getItem("resetEmail");
  
//     if (storedEmail) {
//       emailField.value = storedEmail;
//     } else {
//       alert("No email found. Please login again.");
//       window.location.href = "../login.html";
//     }
//   });
  
//   document.getElementById("passwordForm").addEventListener("submit", async function (e) {
//     e.preventDefault();
  
//     const email = document.getElementById("email").value;
//     const newPassword = document.getElementById("newPassword").value;
//     const confirmPassword = document.getElementById("confirmPassword").value;
  
//     if (!newPassword || !confirmPassword) {
//       alert("Please fill in all fields.");
//       return;
//     }
  
//     if (newPassword !== confirmPassword) {
//       alert("Passwords do not match!");
//       return;
//     }
  
//     try {
//       const res = await fetch("reset_password.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({ email, password: newPassword })
//       });
  
//       const data = await res.json();
  
//       if (data.success) {
//         alert("Password reset successful!");
//         sessionStorage.removeItem("resetEmail");
//         window.location.href = "../login.html";
//       } else {
//         alert(data.message);
//       }
//     } catch (err) {
//       console.error("Reset error:", err);
//       alert("Something went wrong. Try again.");
//     }
//   });


window.addEventListener("DOMContentLoaded", () => {
  const emailField = document.getElementById("email");
  const storedEmail = sessionStorage.getItem("resetEmail");

  if (storedEmail) {
    emailField.value = storedEmail;
  } else {
    alert("No email found. Please login again.");
    window.location.href = "../login.html";
  }
});

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

passwordInput.addEventListener("focus", () => {
  requirementsList.style.display = "block";
});

passwordInput.addEventListener("input", () => {
  const value = passwordInput.value;

  for (const key in requirements) {
    const item = document.getElementById(key);
    if (requirements[key].test(value)) {
      item.style.color = "green";
    } else {
      item.style.color = "red";
    }
  }
});

document.getElementById("passwordForm").addEventListener("submit", async function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value;
  const newPassword = passwordInput.value;
  const confirmPassword = confirmPasswordInput.value;

  if (!newPassword || !confirmPassword) {
    alert("Please fill in all fields.");
    return;
  }

  // Check if password meets all requirements
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
    const res = await fetch("reset_password.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password: newPassword })
    });

    const data = await res.json();

    if (data.success) {
      alert("Password reset successful!");
      sessionStorage.removeItem("resetEmail");
      window.location.href = "../login.html";
    } else {
      alert(data.message);
    }
  } catch (err) {
    console.error("Reset error:", err);
    alert("Something went wrong. Try again.");
  }
});
  