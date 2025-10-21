# 常见问题解答

本指南回答使用 PFinal Regex Center 时的常见问题。

## 🔧 基础问题

### Q: 如何安装 PFinal Regex Center？

A: 使用 Composer 安装：

```bash
composer require pfinalclub/regex-center
```

或者手动下载源码并包含自动加载文件：

```php
require_once 'vendor/autoload.php';
```

### Q: 如何获取 RegexManager 实例？

A: 使用单例模式获取实例：

```php
use pfinalclub\RegexCenter\RegexManager;

$regex = RegexManager::getInstance();
```

### Q: 支持哪些 PHP 版本？

A: PFinal Regex Center 支持 PHP 7.4+ 版本。

## 🎯 使用问题

### Q: 如何验证邮箱格式？

A: 使用内置的邮箱验证模式：

```php
$regex = RegexManager::getInstance();

// 基础邮箱验证
$isValid = $regex->test('email:basic', 'user@example.com');

// 严格邮箱验证
$isValid = $regex->test('email:strict', 'user@example.com');

// 企业邮箱验证
$isValid = $regex->test('email:enterprise', 'user@company.com');
```

### Q: 如何验证中国手机号？

A: 使用内置的中国手机号验证：

```php
$regex = RegexManager::getInstance();

// 验证中国手机号
$isValid = $regex->test('phone:CN', '13812345678');
```

### Q: 如何从文本中提取所有邮箱？

A: 使用 `extractAll()` 方法：

```php
$text = "联系我们：admin@example.com 或 support@company.org";
$emails = $regex->extractAll('email:basic', $text);
// 结果: ['admin@example.com', 'support@company.org']
```

### Q: 如何替换文本中的内容？

A: 使用 `replaceAll()` 方法：

```php
// 简单替换
$newText = $regex->replaceAll('url:basic', $text, '[链接]');

// 回调函数替换
$newText = $regex->replaceAll('phone:CN', $text, function($matches) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});
```

### Q: 如何统计匹配数量？

A: 使用 `count()` 方法：

```php
$count = $regex->count('email:basic', $text);
echo "找到 {$count} 个邮箱";
```

## 🔧 自定义正则表达式

### Q: 如何添加自定义正则表达式？

A: 使用 `add()` 方法：

```php
$regex->add('custom', '/^custom-pattern$/', '自定义模式', [
    'valid' => ['custom-pattern'],
    'invalid' => ['invalid-pattern']
]);
```

### Q: 如何批量添加正则表达式？

A: 使用 `inject()` 方法：

```php
$regex->inject([
    'custom1' => '/^pattern1$/',
    'custom2' => '/^pattern2$/',
    'group' => [
        'sub1' => '/^subpattern1$/',
        'sub2' => '/^subpattern2$/'
    ]
]);
```

### Q: 如何完全替换内置正则表达式？

A: 使用 `use()` 方法：

```php
$regex->use([
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    'phone' => '/^1[3-9]\d{9}$/'
]);
```

## ⚙️ 配置问题

### Q: 如何配置安全选项？

A: 使用 `config()` 方法：

```php
$regex->config([
    'securityEnabled' => true,  // 启用安全保护
    'caseSensitive' => false  // 大小写不敏感
]);
```

### Q: 如何关闭安全保护？

A: 不推荐在生产环境关闭安全保护：

```php
// 开发环境可以关闭（不推荐）
$regex->config(['securityEnabled' => false]);

// 生产环境应该保持启用（推荐）
$regex->config(['securityEnabled' => true]);
```

### Q: 如何设置大小写敏感？

A: 使用 `caseSensitive` 配置：

```php
// 大小写敏感
$regex->config(['caseSensitive' => true]);

// 大小写不敏感（默认）
$regex->config(['caseSensitive' => false]);
```

## 🛡️ 安全问题

### Q: 什么是 ReDoS 攻击？

A: ReDoS（Regular Expression Denial of Service）是一种通过恶意正则表达式导致服务器资源耗尽的攻击。PFinal Regex Center 内置了 ReDoS 攻击防护。

### Q: 如何避免 ReDoS 攻击？

A: 遵循以下原则：

1. 启用安全保护（默认启用）
2. 避免使用危险的正则表达式模式
3. 测试正则表达式的性能

```php
// ✅ 安全的正则表达式
$regex->add('safe', '/^[a-z]+$/', '安全模式');

// ❌ 危险的正则表达式
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    echo $e->getMessage(); // Unsafe regex pattern: potential ReDoS risk
}
```

### Q: 如何测试正则表达式的安全性？

A: 使用内置的安全检测：

```php
try {
    $regex->add('test', $pattern, '测试模式');
    echo "正则表达式安全";
} catch (RuntimeException $e) {
    echo "正则表达式不安全: " . $e->getMessage();
}
```

## 🚀 性能问题

### Q: 如何优化性能？

A: 遵循以下最佳实践：

1. 使用单例模式，避免重复获取实例
2. 实现缓存策略
3. 批量处理数据

```php
// ✅ 推荐：获取实例一次，重复使用
$regex = RegexManager::getInstance();
foreach ($data as $item) {
    $regex->test('email:basic', $item['email']);
}

// ❌ 避免：每次都获取实例
foreach ($data as $item) {
    $regex = RegexManager::getInstance(); // 不必要的开销
    $regex->test('email:basic', $item['email']);
}
```

### Q: 如何监控性能？

A: 实现性能监控：

```php
class RegexPerformanceMonitor {
    private $metrics = [];
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function testWithMetrics($patternKey, $value) {
        $startTime = microtime(true);
        $result = $this->regex->test($patternKey, $value);
        $endTime = microtime(true);
        
        $this->recordMetric($patternKey, $endTime - $startTime);
        
        return $result;
    }
    
    private function recordMetric($patternKey, $duration) {
        if (!isset($this->metrics[$patternKey])) {
            $this->metrics[$patternKey] = [];
        }
        
        $this->metrics[$patternKey][] = $duration;
    }
    
    public function getMetrics() {
        $summary = [];
        
        foreach ($this->metrics as $patternKey => $durations) {
            $summary[$patternKey] = [
                'count' => count($durations),
                'avg_duration' => array_sum($durations) / count($durations),
                'max_duration' => max($durations),
                'min_duration' => min($durations)
            ];
        }
        
        return $summary;
    }
}
```

## 🔍 调试问题

### Q: 如何调试正则表达式？

A: 使用以下方法：

1. 检查正则表达式是否正确
2. 使用测试数据验证
3. 查看错误日志

```php
// 检查正则表达式
$pattern = $regex->get('email:basic');
echo "正则表达式: {$pattern}";

// 测试验证
$testData = [
    'user@example.com',  // 应该返回 true
    'invalid-email',     // 应该返回 false
    'user@',            // 应该返回 false
];

foreach ($testData as $data) {
    $result = $regex->test('email:basic', $data);
    echo "测试 '{$data}': " . ($result ? '通过' : '失败') . "\n";
}
```

### Q: 如何处理验证错误？

A: 使用适当的错误处理：

```php
try {
    $isValid = $regex->test('email:basic', $email);
    if (!$isValid) {
        throw new ValidationException('邮箱格式不正确');
    }
} catch (RuntimeException $e) {
    // 处理正则表达式错误
    error_log("正则验证失败: " . $e->getMessage());
    throw new ValidationException('验证失败');
}
```

### Q: 如何记录验证日志？

A: 实现日志记录：

```php
class RegexLogger {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function testWithLog($patternKey, $value) {
        $result = $this->regex->test($patternKey, $value);
        
        $logData = [
            'pattern_key' => $patternKey,
            'value' => $value,
            'result' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        error_log("正则验证: " . json_encode($logData));
        
        return $result;
    }
}
```

## 🧪 测试问题

### Q: 如何编写单元测试？

A: 使用 PHPUnit 编写测试：

```php
class RegexServiceTest extends TestCase {
    private $regexService;
    
    protected function setUp(): void {
        $this->regexService = new RegexService();
    }
    
    public function testValidateEmail() {
        $this->assertTrue($this->regexService->validateEmail('user@example.com'));
        $this->assertFalse($this->regexService->validateEmail('invalid-email'));
    }
    
    public function testValidatePhone() {
        $this->assertTrue($this->regexService->validatePhone('13812345678'));
        $this->assertFalse($this->regexService->validatePhone('12345678901'));
    }
}
```

### Q: 如何测试自定义正则表达式？

A: 测试自定义正则表达式：

```php
public function testCustomPattern() {
    $regex = RegexManager::getInstance();
    
    // 添加自定义正则表达式
    $regex->add('custom', '/^custom-pattern$/', '自定义模式');
    
    // 测试有效数据
    $this->assertTrue($regex->test('custom', 'custom-pattern'));
    
    // 测试无效数据
    $this->assertFalse($regex->test('custom', 'invalid-pattern'));
}
```

## 🔧 集成问题

### Q: 如何与 Laravel 集成？

A: 创建服务类：

```php
// app/Services/RegexService.php
namespace App\Services;

use pfinalclub\RegexCenter\RegexManager;

class RegexService {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function validateEmail($email) {
        return $this->regex->test('email:strict', $email);
    }
    
    public function validatePhone($phone) {
        return $this->regex->test('phone:CN', $phone);
    }
}
```

### Q: 如何与 Symfony 集成？

A: 创建验证器：

```php
// src/Validator/RegexValidator.php
namespace App\Validator;

use pfinalclub\RegexCenter\RegexManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RegexValidator extends ConstraintValidator {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function validate($value, Constraint $constraint) {
        if (!$this->regex->test($constraint->patternKey, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
```

## 📚 其他问题

### Q: 如何获取所有可用的模式？

A: 使用 `PatternCollection::getBuiltInPatterns()` 方法：

```php
$patterns = PatternCollection::getBuiltInPatterns();
foreach ($patterns as $type => $groups) {
    echo "类型: {$type}\n";
    foreach ($groups as $group => $pattern) {
        if ($group !== 'default') {
            echo "  {$group}: {$pattern}\n";
        }
    }
}
```

### Q: 如何获取模式信息？

A: 使用 `get()` 方法：

```php
$pattern = $regex->get('email:basic');
if ($pattern) {
    echo "正则表达式: {$pattern}";
} else {
    echo "模式不存在";
}
```

### Q: 如何处理不存在的模式？

A: 检查返回值：

```php
$pattern = $regex->get('nonexistent');
if ($pattern === null) {
    echo "模式不存在";
}

$isValid = $regex->test('nonexistent', 'value');
if ($isValid === false) {
    echo "验证失败或模式不存在";
}
```

## 🆘 获取帮助

### Q: 如何获取技术支持？

A: 通过以下方式获取帮助：

1. **GitHub Issues**: [提交问题](https://github.com/pfinalclub/regex-center/issues)
2. **邮箱**: lampxiezi@gmail.com
3. **文档**: 查看完整文档

### Q: 如何报告 Bug？

A: 在 GitHub Issues 中报告：

1. 描述问题
2. 提供复现步骤
3. 包含错误信息
4. 提供环境信息

### Q: 如何贡献代码？

A: 欢迎贡献：

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 创建 Pull Request

---

**如果您有其他问题，请随时联系我们！** 🤝
