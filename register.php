<!-- <?php
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

      fetch('auth_register.php', {
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
</html> -->


<?php
session_start();
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
    <title>Register Account</title>
    
    <!-- 1. Tích hợp Tailwind CSS (Để chạy được style trong code React của bạn) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- 2. Font Awesome (Thay thế cho Lucide Icons trong React) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Hiệu ứng loading quay quay */
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: none; /* Mặc định ẩn */
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <!-- Container chính (Card màu trắng) -->
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        
        <!-- Tiêu đề -->
        <h1 class="text-3xl font-bold text-center mb-2">Let's Register</h1>
        <h1 class="text-3xl font-bold text-center mb-8">Account</h1>
        
        <!-- Thông báo lỗi/thành công (Thêm vào để hiển thị) -->
        <div id="alert-msg" class="hidden mb-4 p-3 rounded text-sm text-center"></div>

        <!-- Form -->
        <form id="registerForm" class="space-y-4">
            
            <!-- Student ID -->
            <input
                type="text"
                name="student_id"
                placeholder="Student ID / Member ID"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            
            <!-- Full Name -->
            <input
                type="text"
                name="full_name"
                placeholder="Full Name"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            
            <!-- Phone -->
            <input
                type="tel"
                name="phone"
                placeholder="Phone"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            
            <!-- Email -->
            <input
                type="email"
                name="email"
                placeholder="Email"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            
            <!-- Password & Eye Icon -->
            <div class="relative">
                <input
                    type="password"
                    name="password"
                    id="passwordInput"
                    placeholder="Password"
                    required
                    minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                    type="button"
                    id="togglePassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                    <!-- Mặc định là icon mắt mở -->
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </button>
            </div>
            
            <!-- Nút Đăng ký -->
            <button
                type="submit"
                id="submitBtn"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition flex justify-center items-center gap-2"
            >
                <span>Sign Up</span> <!-- Chỉnh lại text nút cho đúng nghĩa -->
                <div class="spinner" id="loadingSpinner"></div>
            </button>
            
            <!-- Link quay lại Login -->
            <div class="text-center text-sm">
                Already have an account ? 
                <a href="login.php" class="text-black font-semibold ml-1 hover:underline">
                    Login
                </a>
            </div>
        </form>
    </div>

    <!-- Script xử lý Logic -->
    <script>
        // 1. Xử lý ẩn/hiện mật khẩu
        const toggleBtn = document.getElementById('togglePassword');
        const passInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');

        toggleBtn.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            
            // Đổi icon
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // 2. Xử lý Submit Form bằng Fetch API (AJAX)
        const form = document.getElementById('registerForm');
        const alertMsg = document.getElementById('alert-msg');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('loadingSpinner');
        const btnText = submitBtn.querySelector('span');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Hiệu ứng Loading
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            spinner.style.display = 'block';
            btnText.textContent = 'Processing...';
            alertMsg.classList.add('hidden');

            const formData = new FormData(form);

            try {
                // Gọi đến file Backend auth_register.php mà chúng ta đã tạo trước đó
                const response = await fetch('auth_register.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Hiển thị thông báo
                alertMsg.classList.remove('hidden');
                
                if (data.status === 'success') {
                    // Thành công: Màu xanh
                    alertMsg.className = 'mb-4 p-3 rounded text-sm text-center bg-green-100 text-green-700 border border-green-200';
                    alertMsg.textContent = data.message;
                    
                    form.reset();
                    
                    // Chuyển hướng sau 1.5s
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    // Thất bại: Màu đỏ
                    alertMsg.className = 'mb-4 p-3 rounded text-sm text-center bg-red-100 text-red-700 border border-red-200';
                    alertMsg.textContent = data.message;
                    
                    // Reset nút bấm
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    spinner.style.display = 'none';
                    btnText.textContent = 'Sign Up';
                }

            } catch (error) {
                console.error('Error:', error);
                alertMsg.classList.remove('hidden');
                alertMsg.className = 'mb-4 p-3 rounded text-sm text-center bg-red-100 text-red-700 border border-red-200';
                alertMsg.textContent = 'Lỗi kết nối server!';
                
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                spinner.style.display = 'none';
                btnText.textContent = 'Sign Up';
            }
        });
    </script>
</body>
</html>