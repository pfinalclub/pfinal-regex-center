# 快速开始

本指南将帮助您在 5 分钟内快速上手 PFinal Regex Center。

## 📦 安装

### 使用 Composer 安装

```bash
composer require pfinalclub/regex-center
```

### 手动安装

下载源码并包含自动加载文件：

```php
require_once 'vendor/autoload.php';
```

## 🚀 第一个示例

### 1. 基本验证

```php
<?php
use pfinalclub\RegexCenter\RegexManager;

// 获取实例
$regex = RegexManager::getInstance();

// 验证邮箱
if ($regex->test('email:basic', 'user@example.com')) {
    echo "邮箱格式正确！\n";
}

// 验证手机号
if ($regex->test('phone:CN', '13812345678')) {
    echo "手机号格式正确！\n";
}
```

### 2. 提取信息

```php
// 从文本中提取所有邮箱
$text = "联系我们：admin@example.com 或 support@company.org";
$emails = $regex->extractAll('email:basic', $text);
print_r($emails);
// 输出: ['admin@example.com', 'support@company.org']
```

### 3. 替换文本

```php
// 替换所有 URL 为链接
$text = "访问 https://www.example.com 获取更多信息";
$newText = $regex->replaceAll('url:basic', $text, '[链接]');
echo $newText; // 访问 [链接] 获取更多信息
```

## 🎯 核心概念

### 模式键名格式

PFinal Regex Center 使用 `type:group` 格式来标识正则表达式：

- `email:basic` - 基础邮箱验证
- `phone:CN` - 中国手机号
- `url:basic` - 基础 URL 格式

### 内置模式类型

| 类型 | 描述 | 示例 |
|------|------|------|
| `email` | 邮箱验证 | `email:basic`, `email:strict` |
| `phone` | 电话号码 | `phone:CN`, `phone:US` |
| `url` | URL 链接 | `url:basic`, `url:strict` |
| `ip` | IP 地址 | `ip:v4`, `ip:v6` |
| `password` | 密码强度 | `password:strong`, `password:medium` |

## 🔧 常用方法

### 验证方法

```php
// 基本验证
$isValid = $regex->test('email:basic', 'user@example.com');

// 获取正则表达式
$pattern = $regex->get('email:basic');
```

### 文本处理方法

```php
// 提取所有匹配
$matches = $regex->extractAll('email:basic', $text);

// 替换所有匹配
$newText = $regex->replaceAll('email:basic', $text, '[邮箱]');

// 统计匹配数量
$count = $regex->count('email:basic', $text);
```

## 🌟 高级特性预览

### 回调函数替换

```php
// 使用回调函数进行复杂替换
$result = $regex->replaceAll('phone:CN', $text, function($matches) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});
```

### 自定义正则表达式

```php
// 添加自定义正则
$regex->add('custom', '/^custom-pattern$/', '自定义模式');

// 使用自定义正则
$isValid = $regex->test('custom', 'custom-pattern');
```

### 配置选项

```php
// 配置安全选项
$regex->config(['securityEnabled' => false]);
```

## 📚 下一步

现在您已经了解了基本用法，可以继续学习：

- [基础使用](basic-usage.md) - 深入了解核心功能
- [内置正则表达式](built-in-patterns.md) - 查看所有可用模式
- [高级功能](advanced-features.md) - 探索高级特性

## ❓ 需要帮助？

如果您在使用过程中遇到问题：

- 查看 [常见问题](faq.md)
- 提交 [GitHub Issue](https://github.com/pfinalclub/regex-center/issues)
- 发送邮件至 lampxiezi@gmail.com

---

**恭喜！您已经成功开始使用 PFinal Regex Center！** 🎉
