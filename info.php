<?php
phpinfo();
?>
```

Mở `http://localhost/school-management-system/info.php` trong browser:
- Nếu thấy **trang PHP info** → PHP hoạt động
- Nếu thấy **source code** → PHP không được cài đặt/cấu hình đúng

### **6. Nếu PHP hoàn toàn không chạy:**

**Reinstall XAMPP hoặc check Apache config:**

1. Mở file `httpd.conf` trong `xampp/apache/conf/`
2. Tìm dòng:
```
   LoadModule php_module "path/to/php/php8apache2_4.dll"