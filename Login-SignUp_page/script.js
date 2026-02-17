function showSignup() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("signupForm").classList.remove("hidden");
}

function showLogin() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}

function signup() {
    let studentId = document.getElementById("signupID").value;
    let email = document.getElementById("signupEmail").value;
    let password = document.getElementById("signupPassword").value;

    if (!studentId || !email || !password) {
        alert("Please fill all fields");
        return;
    }

    // Get existing users
    let allUsers = JSON.parse(localStorage.getItem("allUsers") || "[]");
    
    // Check if user already exists
    if (allUsers.find(u => u.studentId === studentId)) {
        alert("Student ID already registered");
        return;
    }

    let user = { studentId, email, password };
    allUsers.push(user);
    localStorage.setItem("allUsers", JSON.stringify(allUsers));

    alert("Account created successfully!");
    document.getElementById("signupForm").reset();
    showLogin();
}

function login() {
    let studentId = document.getElementById("loginID").value;
    let password = document.getElementById("loginPassword").value;

    if (!studentId || !password) {
        alert("Please fill all fields");
        return;
    }

    // Get all stored users
    let allUsers = JSON.parse(localStorage.getItem("allUsers") || "[]");
    let user = allUsers.find(u => u.studentId === studentId);

    if (!user) {
        alert("User not found");
        return;
    }

    if (user.password === password) {
        alert("Login successful!");
        // Redirect to dashboard page
        window.location.href = "dashboard.html";
    } else {
        alert("Incorrect password");
    }
}