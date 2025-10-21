# 内置正则表达式大全

PFinal Regex Center 内置了 100+ 精选正则表达式，覆盖常见使用场景。

## 📧 邮箱验证

### 基础邮箱格式

```php
$regex->test('email:basic', 'user@example.com');        // ✅ 基础邮箱
$regex->test('email:basic', 'admin@company.org');       // ✅ 基础邮箱
$regex->test('email:basic', 'invalid-email');           // ❌ 无效邮箱
```

### 严格邮箱格式

```php
$regex->test('email:strict', 'user@example.com');       // ✅ 严格邮箱
$regex->test('email:strict', 'user+tag@example.com');   // ✅ 支持加号
$regex->test('email:strict', 'user@sub.example.com');   // ✅ 支持子域名
```

### 企业邮箱格式

```php
$regex->test('email:enterprise', 'user@company.com');   // ✅ 企业邮箱
$regex->test('email:enterprise', 'user@gmail.com');     // ❌ 非企业邮箱
```

## 📱 电话号码

### 中国手机号

```php
$regex->test('phone:CN', '13812345678');                 // ✅ 中国移动
$regex->test('phone:CN', '15912345678');                 // ✅ 中国联通
$regex->test('phone:CN', '18812345678');                 // ✅ 中国电信
$regex->test('phone:CN', '12812345678');                 // ❌ 无效号段
```

### 美国电话号码

```php
$regex->test('phone:US', '+1-555-123-4567');            // ✅ 美国格式
$regex->test('phone:US', '1-555-123-4567');             // ✅ 无国际区号
$regex->test('phone:US', '(555) 123-4567');             // ✅ 括号格式
```

### 英国电话号码

```php
$regex->test('phone:UK', '+441234567890');               // ✅ 英国格式
$regex->test('phone:UK', '01234567890');                // ✅ 国内格式
```

### 日本电话号码

```php
$regex->test('phone:JP', '+81123456789');                // ✅ 日本格式
$regex->test('phone:JP', '0123456789');                  // ✅ 国内格式
```

## 🆔 身份证验证

### 中国身份证

```php
$regex->test('idCard:CN', '110101199001011234');        // ✅ 有效身份证
$regex->test('idCard:CN', '11010119900101123X');        // ✅ 末位X
$regex->test('idCard:CN', '123456789012345678');        // ❌ 无效身份证
```

### 美国社会安全号

```php
$regex->test('idCard:US', '123-45-6789');                // ✅ 美国SSN
$regex->test('idCard:US', '123456789');                  // ❌ 格式错误
```

## 🌐 URL 链接

### 基础 URL 格式

```php
$regex->test('url:basic', 'https://www.example.com');    // ✅ HTTPS
$regex->test('url:basic', 'http://example.com');         // ✅ HTTP
$regex->test('url:basic', 'ftp://files.example.com');    // ✅ FTP
$regex->test('url:basic', 'invalid-url');                // ❌ 无效URL
```

### 严格 URL 格式

```php
$regex->test('url:strict', 'https://www.example.com');   // ✅ 严格URL
$regex->test('url:strict', 'http://example.com');        // ✅ 严格URL
$regex->test('url:strict', 'ftp://files.example.com');  // ❌ 不支持FTP
```

## 🌍 IP 地址

### IPv4 地址

```php
$regex->test('ip:v4', '192.168.1.1');                    // ✅ 有效IPv4
$regex->test('ip:v4', '10.0.0.1');                       // ✅ 私有IP
$regex->test('ip:v4', '256.256.256.256');                // ❌ 无效IPv4
```

### IPv6 地址

```php
$regex->test('ip:v6', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'); // ✅ 有效IPv6
$regex->test('ip:v6', '::1');                            // ✅ 本地回环
$regex->test('ip:v6', '192.168.1.1');                    // ❌ IPv4格式
```

## 💳 银行卡

### 中国银行卡

```php
$regex->test('bankCard:CN', '6222021234567890');         // ✅ 中国银行卡
$regex->test('bankCard:CN', '1234567890123456');         // ✅ 16位卡号
$regex->test('bankCard:CN', '12345678901234567890');     // ✅ 20位卡号
```

### 国际信用卡

```php
// Visa 卡
$regex->test('creditCard:VISA', '4111111111111111');     // ✅ Visa
$regex->test('creditCard:VISA', '4111111111111');       // ✅ 13位Visa

// Mastercard
$regex->test('creditCard:MASTERCARD', '5555555555554444'); // ✅ Mastercard

// American Express
$regex->test('creditCard:AMEX', '378282246310005');       // ✅ Amex

// Discover
$regex->test('creditCard:DISCOVER', '6011111111111117');  // ✅ Discover

// Diners Club
$regex->test('creditCard:DINERS', '30569309025904');     // ✅ Diners

// JCB
$regex->test('creditCard:JCB', '3530111333300000');       // ✅ JCB
```

## 🔐 密码强度

### 弱密码

```php
$regex->test('password:weak', '123456');                  // ✅ 6位数字
$regex->test('password:weak', 'abcdef');                  // ✅ 6位字母
$regex->test('password:weak', '12345');                   // ❌ 少于6位
```

### 中等密码

```php
$regex->test('password:medium', 'abc123');                // ✅ 字母+数字
$regex->test('password:medium', 'ABC123');               // ✅ 大写+数字
$regex->test('password:medium', '123456');                // ❌ 纯数字
```

### 强密码

```php
$regex->test('password:strong', 'Abc123!');               // ✅ 强密码
$regex->test('password:strong', 'MyP@ssw0rd');           // ✅ 强密码
$regex->test('password:strong', 'abc123');                // ❌ 缺少大写和特殊字符
```

## 👤 用户名

### 基础用户名

```php
$regex->test('username:basic', 'john_doe');               // ✅ 基础用户名
$regex->test('username:basic', 'user123');                // ✅ 字母数字
$regex->test('username:basic', 'admin');                  // ✅ 纯字母
$regex->test('username:basic', 'user@name');              // ❌ 包含特殊字符
```

### 严格用户名

```php
$regex->test('username:strict', 'john_doe');              // ✅ 严格用户名
$regex->test('username:strict', 'user123');               // ✅ 字母开头
$regex->test('username:strict', '123user');                // ❌ 数字开头
```

## 📅 日期格式

### YYYY-MM-DD 格式

```php
$regex->test('date:YYYY-MM-DD', '2023-12-25');            // ✅ 标准格式
$regex->test('date:YYYY-MM-DD', '2023-02-29');            // ✅ 闰年
$regex->test('date:YYYY-MM-DD', '23-12-25');              // ❌ 年份格式错误
```

### DD/MM/YYYY 格式

```php
$regex->test('date:DD/MM/YYYY', '25/12/2023');            // ✅ 欧洲格式
$regex->test('date:DD/MM/YYYY', '01/01/2023');            // ✅ 年初
$regex->test('date:DD/MM/YYYY', '2023/12/25');            // ❌ 格式错误
```

### MM/DD/YYYY 格式

```php
$regex->test('date:MM/DD/YYYY', '12/25/2023');            // ✅ 美国格式
$regex->test('date:MM/DD/YYYY', '01/01/2023');            // ✅ 年初
$regex->test('date:MM/DD/YYYY', '25/12/2023');            // ❌ 格式错误
```

## ⏰ 时间格式

### HH:MM 格式

```php
$regex->test('time:HH:MM', '14:30');                      // ✅ 24小时制
$regex->test('time:HH:MM', '09:15');                      // ✅ 上午时间
$regex->test('time:HH:MM', '25:30');                       // ❌ 无效时间
```

### HH:MM:SS 格式

```php
$regex->test('time:HH:MM:SS', '14:30:45');                // ✅ 包含秒
$regex->test('time:HH:MM:SS', '09:15:30');                // ✅ 上午时间
$regex->test('time:HH:MM:SS', '14:30');                   // ❌ 缺少秒
```

## 🎨 颜色代码

### 十六进制颜色

```php
$regex->test('color:hex', '#FF0000');                      // ✅ 6位十六进制
$regex->test('color:hex', '#F00');                        // ✅ 3位十六进制
$regex->test('color:hex', '#ff0000');                     // ✅ 小写
$regex->test('color:hex', 'FF0000');                      // ❌ 缺少#号
```

### RGB 颜色

```php
$regex->test('color:rgb', 'rgb(255, 0, 0)');              // ✅ RGB格式
$regex->test('color:rgb', 'rgb(0, 255, 0)');              // ✅ 绿色
$regex->test('color:rgb', 'rgb(300, 0, 0)');              // ❌ 超出范围
```

## 📮 邮政编码

### 中国邮政编码

```php
$regex->test('postalCode:CN', '100000');                   // ✅ 北京
$regex->test('postalCode:CN', '200000');                 // ✅ 上海
$regex->test('postalCode:CN', '12345');                   // ❌ 格式错误
```

### 美国邮政编码

```php
$regex->test('postalCode:US', '12345');                   // ✅ 5位格式
$regex->test('postalCode:US', '12345-6789');              // ✅ 9位格式
$regex->test('postalCode:US', '1234');                     // ❌ 位数不足
```

### 英国邮政编码

```php
$regex->test('postalCode:UK', 'SW1A 1AA');                // ✅ 伦敦
$regex->test('postalCode:UK', 'M1 1AA');                  // ✅ 曼彻斯特
$regex->test('postalCode:UK', '12345');                   // ❌ 格式错误
```

## 💰 货币格式

### 人民币

```php
$regex->test('currency:CNY', '100.50');                    // ✅ 人民币
$regex->test('currency:CNY', '1000');                      // ✅ 整数
$regex->test('currency:CNY', '100.5');                     // ❌ 小数位数错误
```

### 美元

```php
$regex->test('currency:USD', '$100.50');                   // ✅ 美元符号
$regex->test('currency:USD', '100.50');                   // ✅ 无符号
$regex->test('currency:USD', '€100.50');                  // ❌ 错误符号
```

### 欧元

```php
$regex->test('currency:EUR', '€100,50');                  // ✅ 欧元格式
$regex->test('currency:EUR', '100,50');                   // ✅ 无符号
$regex->test('currency:EUR', '$100.50');                  // ❌ 错误符号
```

### 日元

```php
$regex->test('currency:JPY', '¥1000');                    // ✅ 日元符号
$regex->test('currency:JPY', '1000');                     // ✅ 无符号
$regex->test('currency:JPY', '1000.50');                   // ❌ 日元无小数
```

### 英镑

```php
$regex->test('currency:GBP', '£100.50');                  // ✅ 英镑符号
$regex->test('currency:GBP', '100.50');                   // ✅ 无符号
$regex->test('currency:GBP', '$100.50');                  // ❌ 错误符号
```

## 🔧 技术格式

### MAC 地址

```php
$regex->test('macAddress:basic', '00:1B:44:11:3A:B7');    // ✅ 冒号格式
$regex->test('macAddress:colon', '00:1B:44:11:3A:B7');    // ✅ 冒号格式
$regex->test('macAddress:dash', '00-1B-44-11-3A-B7');     // ✅ 短横线格式
$regex->test('macAddress:basic', '00:1B:44:11:3A');       // ❌ 格式不完整
```

### UUID

```php
$regex->test('uuid:v4', '550e8400-e29b-41d4-a716-446655440000'); // ✅ UUID v4
$regex->test('uuid:any', '550e8400-e29b-41d4-a716-446655440000'); // ✅ 任意UUID
$regex->test('uuid:v4', '550e8400-e29b-41d4-a716-44665544000');  // ❌ 格式错误
```

### 哈希值

```php
// MD5
$regex->test('hash:md5', '5d41402abc4b2a76b9719d911017c592'); // ✅ MD5
$regex->test('hash:md5', '5d41402abc4b2a76b9719d911017c59');  // ❌ 长度错误

// SHA1
$regex->test('hash:sha1', 'aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434d'); // ✅ SHA1
$regex->test('hash:sha1', 'aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434'); // ❌ 长度错误

// SHA256
$regex->test('hash:sha256', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'); // ✅ SHA256

// SHA512
$regex->test('hash:sha512', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e'); // ✅ SHA512
```

### 语义化版本

```php
$regex->test('semanticVersion:basic', '1.0.0');           // ✅ 基础版本
$regex->test('semanticVersion:full', '1.0.0-alpha');     // ✅ 预发布版本
$regex->test('semanticVersion:full', '1.0.0+build');    // ✅ 构建版本
$regex->test('semanticVersion:basic', '1.0');            // ❌ 格式不完整
```

## 🎯 使用建议

### 选择合适的验证级别

```php
// 用户输入验证 - 使用严格模式
$regex->test('email:strict', $userInput);

// 数据提取 - 使用基础模式
$emails = $regex->extractAll('email:basic', $text);

// 企业应用 - 使用企业模式
$regex->test('email:enterprise', $businessEmail);
```

### 性能优化

```php
// 获取实例一次，重复使用
$regex = RegexManager::getInstance();

// 批量验证
foreach ($data as $item) {
    $regex->test('email:basic', $item['email']);
}
```

### 错误处理

```php
try {
    $isValid = $regex->test('email:basic', $email);
} catch (Exception $e) {
    // 处理正则表达式错误
    error_log("验证失败: " . $e->getMessage());
}
```

## 📚 扩展阅读

- [高级功能](advanced-features.md) - 探索更多高级特性
- [最佳实践](best-practices.md) - 学习最佳实践
- [性能优化](performance.md) - 优化性能建议

---

**现在您已经了解了所有内置正则表达式！** 🎉
