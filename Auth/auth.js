function switchRole() {
    let role = document.getElementById("roleSelect").value;

    document.getElementById("alumniLogin").style.display =
        role === "alumni" ? "block" : "none";

    document.getElementById("adminLogin").style.display =
        role === "admin" ? "block" : "none";
}

function signup() {
    let name = document.getElementById("signupName").value.trim();
    let id = document.getElementById("signupID").value.trim();
    let pass = document.getElementById("signupPassword").value;

    if (!/^[0-9]{9}$/.test(id)) {
        alert("ID must be exactly 9 digits");
        return;
    }
    if (!name || !pass) {
        alert("Please fill out all fields");
        return;
    }

    let user = { name, id, pass };
    localStorage.setItem(id, JSON.stringify(user));

    alert("Account created!");
    window.location.href = "login.html";
}

function loginAlumni() {
    let id = document.getElementById("alumniID").value;
    let pass = document.getElementById("alumniPass").value;

    if (!/^\d{9}$/.test(id)) {
        alert("ID must be exactly 9 digits");
        return;
    }

    let stored = localStorage.getItem(id);
    if (!stored) {
        alert("User not found");
        return;
    }

    let user = JSON.parse(stored);
    if (user.pass === pass) {
        alert("Login successful");
        window.location.href = "../Homepage/index.html";
    } else {
        alert("Incorrect password");
    }
}

function loginAdmin() {
    let user = document.getElementById("adminUser").value;
    let pass = document.getElementById("adminPass").value;

    if (user === "admin" && pass === "1234") {
        alert("Admin login successful");
        window.location.href = "../Admin/dashboard.html";
    } else {
        alert("Invalid admin credentials");
    }
}