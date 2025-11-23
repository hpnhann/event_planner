<?php
error_reporting(0);
session_start();

// Nếu đã login, redirect về homepage
if (isset($_SESSION['uid'])) {
  header('Location: index.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Volunteer Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
  <link rel="icon" type="image/x-icon" href="images/1.png">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: "Poppins", sans-serif;
      padding: 20px;
    }
    .register-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      padding: 40px;
      width: 100%;
      max-width: 450px;
      animation: slideUp 0.5s ease;
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      display: block;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 10px;
      font-size: 28px;
      font-weight: 700;
    }
    .subtitle {
      text-align: center;
      color: #7f8c8d;
      margin-bottom: 30px;
      font-size: 14px;
    }
    .input-group {
      margin-bottom: 20px;
      position: relative;
    }
    .input-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s;
      outline: none;
    }
    .input-group input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .input-group .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #7f8c8d;
      font-size: 18px;
    }
    .error-message {
      display: none;
      background: #fee;
      color: #c33;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: center;
    }
    .success-message {
      display: none;
      background: #efe;
      color: #3c3;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: center;
    }
    .btn-register {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 10px;
    }
    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    .btn-register:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
    }
    .login-link {
      text-align: center;
      margin-top: 20px;
      color: #7f8c8d;
      font-size: 14px;
    }
    .login-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <img src="images/1.png" alt="Logo" class="logo" onerror="this.style.display='none'">
    <h2>Let's Register Account</h2>
    <p class="subtitle">Join our volunteer community</p>

    <div class="error-message" id="error-msg"></div>
    <div class="success-message" id="success-msg"></div>

    <form id="register-form">
      <div class="input-group">
        <input type="text" name="student_id" id="student_id" placeholder="Student ID / Member ID" required>
      </div>

      <div class="input-group">
        <input type="text" name="full_name" id="full_name" placeholder="Full Name" required>
      </div>

      <div class="input-group">
        <input type="tel" name="phone" id="phone" placeholder="Phone Number" required>
      </div>

      <div class="input-group">
        <input type="email" name="email" id="email" placeholder="Email" required>
      </div>

      <div class="input-group">
        <input type="password" name="password" id="password" placeholder="Password" required minlength="8">
        <i class="bi bi-eye-fill toggle-password" id="togglePassword"></i>
      </div>

      <button type="submit" class="btn-register" id="submitBtn">Sign Up</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>

  <script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('bi-eye-fill');
      this.classList.toggle('bi-eye-slash-fill');
    });

    // Handle form submission
    document.getElementById('register-form').addEventListener('submit', function(e) {
      e.preventDefault();

      const errorMsg = document.getElementById('error-msg');
      const successMsg = document.getElementById('success-msg');
      const submitBtn = document.getElementById('submitBtn');

      errorMsg.style.display = 'none';
      successMsg.style.display = 'none';
      submitBtn.disabled = true;
      submitBtn.textContent = 'Registering...';

      const formData = new FormData(this);

      fetch('register_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign Up';

        if (data.status === 'success') {
          successMsg.textContent = data.message;
          successMsg.style.display = 'block';
          this.reset();
          
          // Redirect to login after 2 seconds
          setTimeout(() => {
            window.location.href = 'login.php';
          }, 2000);
        } else {
          errorMsg.textContent = data.message;
          errorMsg.style.display = 'block';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign Up';
        errorMsg.textContent = 'An error occurred. Please try again.';
        errorMsg.style.display = 'block';
      });
    });
  </script>
</body>
</html>