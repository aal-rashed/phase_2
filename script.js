function showSignup() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("signupForm").classList.remove("hidden");
}

function showLogin() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}

function signup() {
    let name = document.getElementById("signupName").value;
    let email = document.getElementById("signupEmail").value;
    let password = document.getElementById("signupPassword").value;

    if (!name || !email || !password) {
        alert("Please fill all fields");
        return;
    }

    let user = { name, email, password };
    localStorage.setItem(email, JSON.stringify(user));

    alert("Account created successfully!");
    showLogin();
}

function login() {
    let email = document.getElementById("loginEmail").value;
    let password = document.getElementById("loginPassword").value;

    let storedUser = localStorage.getItem(email);

    if (!storedUser) {
        alert("User not found");
        return;
    }

    let user = JSON.parse(storedUser);

    if (user.password === password) {
        alert("Login successful!");
        // Redirect to dashboard page
        window.location.href = "dashboard.html";
    } else {
        alert("Incorrect password");
    }
}