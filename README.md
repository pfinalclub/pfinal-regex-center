# PFinal Regex Center

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/pfinalclub/regex-center)

PHP 版本的正则表达式管理库，内置 100+ 精选正则，支持团队标准化和 PSR-4。

## 简介

Regex Center = 正则 + 管理，一个专业的正则表达式管理库，让团队和项目的正则变得可管理、可维护、可复用。

## 核心价值

- **开箱即用**：内置 100+ 精选正则，覆盖常见场景
- **团队管理**：搭建属于你的正则管理体系，统一团队标准
- **类型安全**：严格类型检查，减少运行时错误
- **高性能**：单例模式，内存占用小
- **可扩展**：支持自定义正则表达式注入
- **安全性**：内置 ReDoS 攻击防护

## 安装

```bash
composer require pfinal/regex-center
```

## 使用方法

### 方式一：直接使用内置正则表达式

```php
use pfinalclub\RegexCenter\RegexManager;

$regex = RegexManager::getInstance();

// 验证邮箱
if ($regex->test('email:basic', 'test@example.com')) {
    echo "邮箱格式正确";
}

// 验证手机号（中国）
if ($regex->test('phone:CN', '13812345678')) {
    echo "手机号格式正确";
}

// 提取文本中的所有邮箱
$text = "联系我们：admin@example.com 或 support@example.org";
$emails = $regex->extractAll('email:basic', $text);
print_r($emails);

// 替换文本中的所有URL
$text = "访问我们的网站 https://www.example.com 获取更多信息";
$newText = $regex->replaceAll('url', $text, '[链接]');
echo $newText;
```

### 方式二：团队自定义配置（推荐）

```php
use pfinalclub\RegexCenter\RegexManager;

$regex = RegexManager::getInstance();

// 团队自定义正则配置
$customPatterns = [
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
    'phone' => [
        'CN' => '/^1[3-9]\d{9}$/',
        'US' => '/^\+?1?-?\(?[0-9]{3}\)?-?[0-9]{3}-?[0-9]{4}$/',
        'UK' => '/^\+44[0-9]{10}$/'
    ],
    'id' => '/^[A-Za-z0-9]{8,10}$/',
    'custom' => '/^custom-pattern$/'
];

// 注入团队自定义配置（保留内置模式）
$regex->inject($customPatterns);

// 使用自定义的正则表达式
$regex->test('email', 'user@example.com');     // 使用自定义邮箱正则
$regex->test('phone:UK', '+441234567890');    // 使用自定义英国手机号正则
$regex->test('custom', 'custom-value');       // 使用自定义正则
```
```

## 内置正则表达式类型

### 邮箱验证
- `email:basic` - 基础邮箱格式
- `email:strict` - 严格邮箱格式
- `email:enterprise` - 企业邮箱格式

### 电话号码
- `phone:CN` - 中国手机号
- `phone:US` - 美国电话号码
- `phone:UK` - 英国电话号码
- `phone:JP` - 日本电话号码

### 身份证
- `idCard:CN` - 中国身份证
- `idCard:US` - 美国社会安全号

### URL 链接
- `url:basic` - 基础 URL 格式
- `url:strict` - 严格 URL 格式

### IP 地址
- `ip:v4` - IPv4 地址
- `ip:v6` - IPv6 地址

### 银行卡
- `bankCard:CN` - 中国银行卡
- `bankCard:VISA` - Visa 卡
- `bankCard:MASTERCARD` - Mastercard
- `bankCard:AMEX` - American Express

### 密码强度
- `password:strong` - 强密码（包含大小写、数字、特殊字符）
- `password:medium` - 中等密码（包含字母和数字）
- `password:weak` - 弱密码（仅长度要求）

### 其他类型
- `username` - 用户名
- `date` - 日期格式
- `time` - 时间格式
- `color` - 颜色代码
- `postalCode` - 邮政编码
- `currency` - 货币格式

## 高级用法

### 批量验证

```php
$regex = RegexManager::getInstance();

$data = [
    'email' => 'user@example.com',
    'phone' => '13812345678',
    'url' => 'https://www.example.com'
];

$rules = [
    'email' => 'email:basic',
    'phone' => 'phone:CN',
    'url' => 'url:basic'
];

foreach ($rules as $field => $pattern) {
    if (!$regex->test($pattern, $data[$field])) {
        echo "{$field} 格式不正确\n";
    }
}
```

### 文本处理

```php
$text = "联系我们：admin@example.com 或访问 https://www.example.com";

// 提取所有邮箱
$emails = $regex->extractAll('email:basic', $text);

// 高亮所有 URL
$highlighted = $regex->highlight('url:basic', $text, '<a href="$&">$&</a>');

// 替换敏感信息
$masked = $regex->replaceAll('email:basic', $text, '[邮箱]');
```

## 运行测试

```bash
# 首先安装依赖
composer install

# 运行测试
./vendor/bin/phpunit

# 运行测试并生成覆盖率报告
composer test-coverage

# 代码质量检查
composer quality

# 代码风格修复
composer cs-fix
```

## 贡献指南

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

## 许可证

MIT License - 详见 [LICENSE](LICENSE) 文件