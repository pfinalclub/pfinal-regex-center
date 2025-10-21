# 最佳实践

本指南提供使用 PFinal Regex Center 的最佳实践和建议。

## 🎯 性能优化

### 1. 单例模式使用

```php
// ✅ 推荐：获取实例一次，重复使用
$regex = RegexManager::getInstance();

// 批量验证
foreach ($emails as $email) {
    $regex->test('email:basic', $email);
}

// ❌ 避免：每次都获取实例
foreach ($emails as $email) {
    $regex = RegexManager::getInstance(); // 不必要的开销
    $regex->test('email:basic', $email);
}
```

### 2. 缓存策略

```php
class RegexCache {
    private $cache = [];
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function testWithCache($patternKey, $value) {
        $cacheKey = $patternKey . ':' . md5($value);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $result = $this->regex->test($patternKey, $value);
        $this->cache[$cacheKey] = $result;
        
        return $result;
    }
}
```

### 3. 批量处理

```php
// ✅ 推荐：批量处理
function batchValidate($data, $rules) {
    $regex = RegexManager::getInstance();
    $results = [];
    
    foreach ($data as $item) {
        $itemResults = [];
        foreach ($rules as $field => $patternKey) {
            $itemResults[$field] = $regex->test($patternKey, $item[$field]);
        }
        $results[] = $itemResults;
    }
    
    return $results;
}

// ❌ 避免：逐个处理
function individualValidate($data, $rules) {
    $regex = RegexManager::getInstance();
    $results = [];
    
    foreach ($data as $item) {
        foreach ($rules as $field => $patternKey) {
            // 每次都重新获取实例
            $regex = RegexManager::getInstance();
            $results[$field] = $regex->test($patternKey, $item[$field]);
        }
    }
    
    return $results;
}
```

## 🛡️ 安全最佳实践

### 1. 启用安全保护

```php
// ✅ 生产环境：始终启用安全保护
$regex->config(['securityEnabled' => true]);

// ❌ 避免：在生产环境关闭安全保护
$regex->config(['securityEnabled' => false]);
```

### 2. 验证用户输入

```php
// ✅ 推荐：验证用户输入
function validateUserInput($input) {
    $regex = RegexManager::getInstance();
    
    try {
        if (!$regex->test('email:strict', $input['email'])) {
            throw new InvalidArgumentException('邮箱格式不正确');
        }
        
        if (!$regex->test('phone:CN', $input['phone'])) {
            throw new InvalidArgumentException('手机号格式不正确');
        }
        
        return true;
    } catch (RuntimeException $e) {
        // 记录安全事件
        error_log("安全事件: " . $e->getMessage());
        throw new SecurityException('输入验证失败');
    }
}
```

### 3. 自定义正则表达式安全

```php
// ✅ 推荐：使用安全的正则表达式
$regex->add('safe', '/^[a-zA-Z0-9_]{3,20}$/', '安全用户名');

// ❌ 避免：使用危险的正则表达式
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    // 记录并处理安全事件
    error_log("尝试添加危险正则: " . $e->getMessage());
}
```

## 🏗️ 架构设计

### 1. 服务层封装

```php
class ValidationService {
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
    
    public function validateUser($userData) {
        $errors = [];
        
        if (!$this->validateEmail($userData['email'])) {
            $errors[] = '邮箱格式不正确';
        }
        
        if (!$this->validatePhone($userData['phone'])) {
            $errors[] = '手机号格式不正确';
        }
        
        return $errors;
    }
}
```

### 2. 配置管理

```php
class RegexConfig {
    private $regex;
    private $environment;
    
    public function __construct($environment = 'production') {
        $this->regex = RegexManager::getInstance();
        $this->environment = $environment;
        $this->loadConfiguration();
    }
    
    private function loadConfiguration() {
        switch ($this->environment) {
            case 'development':
                $this->loadDevConfig();
                break;
            case 'testing':
                $this->loadTestConfig();
                break;
            case 'production':
                $this->loadProdConfig();
                break;
        }
    }
    
    private function loadProdConfig() {
        // 生产环境：严格验证
        $this->regex->config(['securityEnabled' => true]);
        
        // 添加生产环境特定的正则表达式
        $this->regex->add('prodEmail', '/^[a-zA-Z0-9._%+-]+@company\.com$/', '生产环境企业邮箱');
    }
}
```

### 3. 错误处理

```php
class RegexValidator {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function validate($patternKey, $value) {
        try {
            return $this->regex->test($patternKey, $value);
        } catch (RuntimeException $e) {
            // 记录错误
            error_log("正则验证失败: {$patternKey} - " . $e->getMessage());
            return false;
        }
    }
    
    public function safeAdd($key, $pattern, $description = '') {
        try {
            $this->regex->add($key, $pattern, $description);
            return true;
        } catch (RuntimeException $e) {
            // 记录安全事件
            error_log("安全事件: 尝试添加危险正则 {$key} - " . $e->getMessage());
            return false;
        }
    }
}
```

## 📊 数据处理最佳实践

### 1. 数据清洗

```php
class DataCleaner {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function cleanUserData($data) {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'email':
                    $cleaned[$key] = $this->normalizeEmail($value);
                    break;
                case 'phone':
                    $cleaned[$key] = $this->normalizePhone($value);
                    break;
                case 'name':
                    $cleaned[$key] = $this->normalizeName($value);
                    break;
                default:
                    $cleaned[$key] = $value;
            }
        }
        
        return $cleaned;
    }
    
    private function normalizeEmail($email) {
        return strtolower(trim($email));
    }
    
    private function normalizePhone($phone) {
        // 移除所有非数字字符
        $digits = preg_replace('/\D/', '', $phone);
        
        // 验证格式
        if ($this->regex->test('phone:CN', $digits)) {
            return $digits;
        }
        
        return $phone;
    }
    
    private function normalizeName($name) {
        return ucwords(strtolower(trim($name)));
    }
}
```

### 2. 数据脱敏

```php
class DataMasker {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function maskSensitiveData($text) {
        // 邮箱脱敏
        $text = $this->regex->replaceAll('email:basic', $text, function($matches) {
            $email = $matches[0];
            [$user, $domain] = explode('@', $email);
            return substr($user, 0, 1) . '***@' . $domain;
        });
        
        // 手机号脱敏
        $text = $this->regex->replaceAll('phone:CN', $text, function($matches) {
            $phone = $matches[0];
            return substr($phone, 0, 3) . '****' . substr($phone, -4);
        });
        
        return $text;
    }
}
```

### 3. 内容分析

```php
class ContentAnalyzer {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function analyzeText($text) {
        return [
            'emails' => $this->analyzeEmails($text),
            'phones' => $this->analyzePhones($text),
            'urls' => $this->analyzeUrls($text),
            'statistics' => $this->getStatistics($text)
        ];
    }
    
    private function analyzeEmails($text) {
        $emails = $this->regex->extractAll('email:basic', $text);
        $analysis = [];
        
        foreach ($emails as $email) {
            $domain = substr($email, strpos($email, '@') + 1);
            $analysis[] = [
                'email' => $email,
                'domain' => $domain,
                'type' => $this->getEmailType($domain)
            ];
        }
        
        return $analysis;
    }
    
    private function getStatistics($text) {
        return [
            'email_count' => $this->regex->count('email:basic', $text),
            'phone_count' => $this->regex->count('phone:CN', $text),
            'url_count' => $this->regex->count('url:basic', $text),
            'total_length' => strlen($text)
        ];
    }
}
```

## 🔧 框架集成最佳实践

### 1. Laravel 集成

```php
// app/Services/RegexService.php
namespace App\Services;

use pfinalclub\RegexCenter\RegexManager;

class RegexService {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function validateUser($userData) {
        $validator = app('validator');
        
        $rules = [
            'email' => ['required', function($attribute, $value, $fail) {
                if (!$this->regex->test('email:strict', $value)) {
                    $fail('邮箱格式不正确');
                }
            }],
            'phone' => ['required', function($attribute, $value, $fail) {
                if (!$this->regex->test('phone:CN', $value)) {
                    $fail('手机号格式不正确');
                }
            }]
        ];
        
        return $validator->make($userData, $rules);
    }
}

// 在控制器中使用
class UserController extends Controller {
    public function store(Request $request, RegexService $regexService) {
        $validator = $regexService->validateUser($request->all());
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // 处理用户数据
    }
}
```

### 2. Symfony 集成

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

## 📝 代码组织

### 1. 命名规范

```php
// ✅ 推荐：清晰的命名
$regex->add('userEmail', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', '用户邮箱');
$regex->add('companyPhone', '/^1[3-9]\d{9}$/', '公司电话');

// ❌ 避免：模糊的命名
$regex->add('pattern1', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', '模式1');
$regex->add('pattern2', '/^1[3-9]\d{9}$/', '模式2');
```

### 2. 文档注释

```php
/**
 * 用户验证服务
 */
class UserValidationService {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    /**
     * 验证用户邮箱
     * 
     * @param string $email 邮箱地址
     * @return bool 是否有效
     */
    public function validateEmail($email) {
        return $this->regex->test('email:strict', $email);
    }
    
    /**
     * 验证用户手机号
     * 
     * @param string $phone 手机号
     * @return bool 是否有效
     */
    public function validatePhone($phone) {
        return $this->regex->test('phone:CN', $phone);
    }
}
```

### 3. 错误处理

```php
class RegexValidationException extends Exception {
    private $patternKey;
    private $value;
    
    public function __construct($patternKey, $value, $message = '') {
        $this->patternKey = $patternKey;
        $this->value = $value;
        parent::__construct($message ?: "验证失败: {$patternKey}");
    }
    
    public function getPatternKey() {
        return $this->patternKey;
    }
    
    public function getValue() {
        return $this->value;
    }
}

// 使用示例
try {
    if (!$regex->test('email:strict', $email)) {
        throw new RegexValidationException('email:strict', $email, '邮箱格式不正确');
    }
} catch (RegexValidationException $e) {
    // 处理验证错误
    error_log("验证失败: " . $e->getMessage());
}
```

## 🧪 测试最佳实践

### 1. 单元测试

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

### 2. 集成测试

```php
class RegexIntegrationTest extends TestCase {
    public function testBatchValidation() {
        $regex = RegexManager::getInstance();
        
        $data = [
            ['email' => 'user1@example.com', 'phone' => '13812345678'],
            ['email' => 'user2@example.com', 'phone' => '13987654321'],
        ];
        
        $rules = [
            'email' => 'email:basic',
            'phone' => 'phone:CN'
        ];
        
        foreach ($data as $item) {
            foreach ($rules as $field => $patternKey) {
                $this->assertTrue($regex->test($patternKey, $item[$field]));
            }
        }
    }
}
```

## 📊 性能监控

### 1. 性能指标

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

### 2. 内存使用监控

```php
class RegexMemoryMonitor {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function getMemoryUsage() {
        return [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'instance_count' => 1 // 单例模式
        ];
    }
}
```

## 🎯 总结

### 关键要点

1. **性能优化**: 使用单例模式，实现缓存策略，批量处理数据
2. **安全考虑**: 启用安全保护，验证用户输入，使用安全的正则表达式
3. **架构设计**: 服务层封装，配置管理，错误处理
4. **数据处理**: 数据清洗，脱敏处理，内容分析
5. **框架集成**: Laravel、Symfony 等框架的集成最佳实践
6. **代码组织**: 命名规范，文档注释，错误处理
7. **测试实践**: 单元测试，集成测试
8. **性能监控**: 性能指标，内存使用监控

### 避免的常见错误

1. ❌ 每次都获取 RegexManager 实例
2. ❌ 在生产环境关闭安全保护
3. ❌ 使用危险的正则表达式
4. ❌ 忽略错误处理
5. ❌ 缺乏适当的测试
6. ❌ 不监控性能指标

---

**遵循这些最佳实践，您将能够高效、安全地使用 PFinal Regex Center！** 🚀
