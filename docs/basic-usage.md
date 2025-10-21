# 基础使用

本指南详细介绍 PFinal Regex Center 的核心功能和使用方法。

## 🏗️ 核心架构

### 单例模式

PFinal Regex Center 使用单例模式，确保全局只有一个实例：

```php
use pfinalclub\RegexCenter\RegexManager;

// 获取单例实例
$regex = RegexManager::getInstance();

// 多次调用返回同一个实例
$regex1 = RegexManager::getInstance();
$regex2 = RegexManager::getInstance();
// $regex1 === $regex2 (true)
```

### 模式键名系统

使用 `type:group` 格式来标识正则表达式：

```php
// 格式：类型:分组
$regex->test('email:basic', 'user@example.com');
$regex->test('phone:CN', '13812345678');
$regex->test('url:strict', 'https://www.example.com');
```

## 🔍 验证功能

### 基本验证

```php
// 验证邮箱
$isValid = $regex->test('email:basic', 'user@example.com');
if ($isValid) {
    echo "邮箱格式正确";
}

// 验证手机号
$isValid = $regex->test('phone:CN', '13812345678');
if ($isValid) {
    echo "手机号格式正确";
}
```

### 多种验证级别

```php
// 邮箱验证的不同严格程度
$regex->test('email:basic', 'user@example.com');     // 基础验证
$regex->test('email:strict', 'user@example.com');   // 严格验证
$regex->test('email:enterprise', 'user@company.com'); // 企业邮箱验证

// 密码强度验证
$regex->test('password:weak', '123456');     // 弱密码
$regex->test('password:medium', 'abc123');  // 中等密码
$regex->test('password:strong', 'Abc123!'); // 强密码
```

## 📤 提取功能

### 提取所有匹配

```php
$text = "联系我们：admin@example.com 或 support@company.org，电话：13812345678";

// 提取所有邮箱
$emails = $regex->extractAll('email:basic', $text);
// 结果: ['admin@example.com', 'support@company.org']

// 提取所有手机号
$phones = $regex->extractAll('phone:CN', $text);
// 结果: ['13812345678']
```

### 统计匹配数量

```php
$text = "邮箱：user1@example.com, user2@example.com, user3@example.com";

// 统计邮箱数量
$count = $regex->count('email:basic', $text);
echo "找到 {$count} 个邮箱"; // 找到 3 个邮箱
```

## 🔄 替换功能

### 简单替换

```php
$text = "访问 https://www.example.com 获取更多信息";

// 替换所有 URL
$newText = $regex->replaceAll('url:basic', $text, '[链接]');
echo $newText; // 访问 [链接] 获取更多信息
```

### 高亮显示

```php
$text = "邮箱：user@example.com";

// 高亮邮箱
$highlighted = $regex->highlight('email:basic', $text, '<strong>$&</strong>');
echo $highlighted; // 邮箱：<strong>user@example.com</strong>
```

## 🎨 回调函数替换

### 数据脱敏

```php
$text = "用户手机：13812345678，备用手机：13987654321";

// 使用回调函数进行脱敏
$masked = $regex->replaceAll('phone:CN', $text, function($matches) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});

echo $masked; // 用户手机：138****5678，备用手机：139****4321
```

### 条件处理

```php
$text = "主要联系方式：13812345678，备用：13987654321";

$result = $regex->replaceAll('phone:CN', $text, function($matches, $offset) {
    return $offset < 20 ? '[主要联系方式]' : '[备用联系方式]';
});
```

## 🔧 自定义配置

### 添加单个正则表达式

```php
// 添加自定义正则
$regex->add('companyCode', '/^[A-Z]{2,4}$/', '公司代码：2-4位大写字母', [
    'valid' => ['ABC', 'XYZ', 'COMP'],
    'invalid' => ['abc', '123', 'TOOLONG']
]);

// 使用自定义正则
$isValid = $regex->test('companyCode', 'ABC');
```

### 批量注入配置

```php
// 注入团队自定义配置
$customPatterns = [
    'username' => [
        'strict' => '/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/',
        'default' => 'strict'
    ],
    'company' => [
        'name' => '/^[a-zA-Z0-9\s&.-]{2,50}$/',
        'code' => '/^[A-Z]{2,10}$/'
    ]
];

$regex->inject($customPatterns);
```

### 配置选项

```php
// 配置安全选项
$regex->config([
    'securityEnabled' => true,  // 启用安全保护
    'caseSensitive' => false    // 大小写不敏感
]);
```

## 🌍 多语言支持

### 国际电话号码

```php
// 不同国家的电话号码
$regex->test('phone:CN', '13812345678');    // 中国
$regex->test('phone:US', '+1-555-123-4567'); // 美国
$regex->test('phone:UK', '+441234567890');   // 英国
$regex->test('phone:JP', '+81123456789');   // 日本
```

### 国际邮政编码

```php
// 不同国家的邮政编码
$regex->test('postalCode:CN', '100000');    // 中国
$regex->test('postalCode:US', '12345');     // 美国
$regex->test('postalCode:UK', 'SW1A 1AA');  // 英国
```

### 多货币格式

```php
// 不同货币格式
$regex->test('currency:CNY', '100.50');     // 人民币
$regex->test('currency:USD', '$100.50');   // 美元
$regex->test('currency:EUR', '€100,50');   // 欧元
$regex->test('currency:JPY', '¥1000');      // 日元
```

## 🛡️ 安全特性

### ReDoS 攻击防护

PFinal Regex Center 内置 ReDoS 攻击防护：

```php
// 安全的正则表达式
$regex->add('safe', '/^[a-z]+$/', '安全模式'); // ✅ 成功

// 危险的正则表达式会被自动阻止
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    echo $e->getMessage(); // Unsafe regex pattern: potential ReDoS risk
}
```

### 配置安全级别

```php
// 关闭安全保护（不推荐）
$regex->config(['securityEnabled' => false]);
```

## 📊 实际应用场景

### 表单验证

```php
function validateForm($data) {
    $regex = RegexManager::getInstance();
    $errors = [];
    
    if (!$regex->test('email:strict', $data['email'])) {
        $errors[] = '邮箱格式不正确';
    }
    
    if (!$regex->test('phone:CN', $data['phone'])) {
        $errors[] = '手机号格式不正确';
    }
    
    return $errors;
}
```

### 数据清洗

```php
function cleanData($text) {
    $regex = RegexManager::getInstance();
    
    // 移除所有邮箱
    $text = $regex->replaceAll('email:basic', $text, '[邮箱]');
    
    // 移除所有手机号
    $text = $regex->replaceAll('phone:CN', $text, '[手机号]');
    
    return $text;
}
```

### 日志分析

```php
function analyzeLog($logText) {
    $regex = RegexManager::getInstance();
    
    return [
        'ip_count' => $regex->count('ip:v4', $logText),
        'email_count' => $regex->count('email:basic', $logText),
        'ips' => $regex->extractAll('ip:v4', $logText),
        'emails' => $regex->extractAll('email:basic', $logText)
    ];
}
```

## 🎯 最佳实践

### 1. 使用默认值

```php
// 使用默认分组
$regex->test('email', 'user@example.com');        // 等同于 email:basic
$regex->test('phone', '13812345678');             // 等同于 phone:CN
$regex->test('password', 'Abc123!');              // 等同于 password:medium
```

### 2. 错误处理

```php
try {
    $isValid = $regex->test('email:basic', $email);
} catch (Exception $e) {
    // 处理错误
    error_log("正则验证失败: " . $e->getMessage());
}
```

### 3. 性能优化

```php
// 获取实例一次，重复使用
$regex = RegexManager::getInstance();

// 批量验证
foreach ($emails as $email) {
    $regex->test('email:basic', $email);
}
```

## 📚 下一步

现在您已经掌握了基础功能，可以继续学习：

- [高级功能](advanced-features.md) - 探索更多高级特性
- [内置正则表达式](built-in-patterns.md) - 查看所有可用模式
- [最佳实践](best-practices.md) - 学习最佳实践

---

**您已经掌握了 PFinal Regex Center 的核心功能！** 🎉
