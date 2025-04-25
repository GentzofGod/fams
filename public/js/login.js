document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();
  
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
  
    if (!email || !password) {
      alert("Please enter both email and password.");
      return;
    }
  
    try {
      const res = await fetch("../api/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
      });
  
      const data = await res.json();
      alert(data.message);
  
      if (data.success) {
        if (data.redirect) {
          // ⏺ Save email for reset page
          sessionStorage.setItem("resetEmail", email);
          window.location.href = data.redirect;
          return;
        }
  
        // ⏺ Regular dashboard redirects
        let redirectPath = "../public/";
        switch (data.role) {
          case "superadmin":
            redirectPath += "superadmin/index.html"; break;
          case "HOD":
            redirectPath += "hod/index.html"; break;
          case "Lecturer":
            redirectPath += "Lecturer/index.html"; break;
          default:
            alert("Unauthorized role!");
            return;
        }
  
        window.location.href = redirectPath;
      }
    } catch (err) {
      console.error("Login error:", err);
      alert("An error occurred. Try again.");
    }
  });
  