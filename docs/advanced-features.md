# 高级功能

本指南介绍 PFinal Regex Center 的高级特性和技巧。

## 🎯 回调函数高级用法

### 复杂数据脱敏

```php
function maskSensitiveData($text) {
    $regex = RegexManager::getInstance();
    
    // 邮箱脱敏：保留用户名首字母和域名
    $text = $regex->replaceAll('email:basic', $text, function($matches) {
        $email = $matches[0];
        [$user, $domain] = explode('@', $email);
        return substr($user, 0, 1) . '***@' . $domain;
    });
    
    // 手机号脱敏：保留前3位和后4位
    $text = $regex->replaceAll('phone:CN', $text, function($matches) {
        $phone = $matches[0];
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    });
    
    return $text;
}

// 使用示例
$text = "用户：张三，邮箱：zhangsan@company.com，电话：13812345678";
$masked = maskSensitiveData($text);
// 结果：用户：张三，邮箱：z***@company.com，电话：138****5678
```

### 条件替换

```php
function conditionalReplace($text) {
    $regex = RegexManager::getInstance();
    
    return $regex->replaceAll('phone:CN', $text, function($matches, $offset) {
        // 根据位置决定替换策略
        if ($offset < 50) {
            return '[主要联系方式]';
        } else {
            return '[备用联系方式]';
        }
    });
}
```

### 格式化处理

```php
function formatPhoneNumbers($text) {
    $regex = RegexManager::getInstance();
    
    return $regex->replaceAll('phone:CN', $text, function($matches) {
        $phone = $matches[0];
        // 格式化为标准格式：138-1234-5678
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
    });
}
```

## 🔧 自定义正则表达式管理

### 团队标准化配置

```php
class TeamRegexConfig {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
        $this->loadTeamStandards();
    }
    
    private function loadTeamStandards() {
        // 团队邮箱标准：只允许公司域名
        $this->regex->add('teamEmail', '/^[a-z0-9._%+-]+@company\.com$/i', 
            '团队邮箱：只允许 @company.com 域名', [
                'valid' => ['john@company.com', 'jane.doe@company.com'],
                'invalid' => ['john@gmail.com', 'jane@yahoo.com']
            ]);
        
        // 员工工号标准
        $this->regex->add('employeeId', '/^EMP-[A-Z0-9]{6}$/', 
            '员工工号：EMP- + 6位字母数字', [
                'valid' => ['EMP-A1B2C3', 'EMP-123456'],
                'invalid' => ['emp-a1b2c3', 'EMP-12345', '123456']
            ]);
        
        // 项目代码标准
        $this->regex->add('projectCode', '/^[A-Z]{2,4}-[0-9]{3,6}$/', 
            '项目代码：2-4位大写字母 + 3-6位数字', [
                'valid' => ['ABC-123', 'PROJ-123456'],
                'invalid' => ['abc-123', 'PROJ-12', 'PROJECT-123']
            ]);
    }
    
    public function validateTeamData($data) {
        $errors = [];
        
        if (!$this->regex->test('teamEmail', $data['email'])) {
            $errors[] = '请使用公司邮箱';
        }
        
        if (!$this->regex->test('employeeId', $data['employeeId'])) {
            $errors[] = '员工工号格式不正确';
        }
        
        if (!$this->regex->test('projectCode', $data['projectCode'])) {
            $errors[] = '项目代码格式不正确';
        }
        
        return $errors;
    }
}
```

### 环境配置管理

```php
class EnvironmentConfig {
    private $regex;
    
    public function __construct($environment = 'production') {
        $this->regex = RegexManager::getInstance();
        $this->loadEnvironmentConfig($environment);
    }
    
    private function loadEnvironmentConfig($env) {
        switch ($env) {
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
    
    private function loadDevConfig() {
        // 开发环境：宽松验证
        $this->regex->add('devEmail', '/^.+@.+\..+$/', '开发环境邮箱验证');
        $this->regex->add('devPhone', '/^\d{11}$/', '开发环境手机号验证');
    }
    
    private function loadTestConfig() {
        // 测试环境：测试数据验证
        $this->regex->add('testUser', '/^test_\w+$/', '测试用户名：test_ + 字母数字');
        $this->regex->add('testEmail', '/^test@test\.com$/', '测试邮箱');
    }
    
    private function loadProdConfig() {
        // 生产环境：严格验证
        $this->regex->add('prodEmail', '/^[a-zA-Z0-9._%+-]+@company\.com$/', '生产环境企业邮箱');
        $this->regex->add('prodPhone', '/^1[3-9]\d{9}$/', '生产环境手机号');
    }
}
```

## 🛡️ 安全防护高级配置

### 自定义安全策略

```php
class SecurityManager {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
        $this->setupSecurityPolicies();
    }
    
    private function setupSecurityPolicies() {
        // 高风险环境：严格安全保护
        if ($this->isHighRiskEnvironment()) {
            $this->regex->config(['securityEnabled' => true]);
        } else {
            $this->regex->config(['securityEnabled' => false]);
        }
    }
    
    public function addSecurePattern($key, $pattern, $description) {
        try {
            $this->regex->add($key, $pattern, $description);
            return true;
        } catch (RuntimeException $e) {
            // 记录安全事件
            $this->logSecurityEvent($key, $pattern, $e->getMessage());
            return false;
        }
    }
    
    private function logSecurityEvent($key, $pattern, $message) {
        error_log("安全事件: 尝试添加危险正则 {$key}: {$pattern} - {$message}");
    }
    
    private function isHighRiskEnvironment() {
        // 根据环境判断是否启用严格安全保护
        return $_ENV['APP_ENV'] === 'production';
    }
}
```

### 正则表达式审计

```php
class RegexAuditor {
    private $regex;
    private $auditLog = [];
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function auditPattern($key, $pattern) {
        $audit = [
            'key' => $key,
            'pattern' => $pattern,
            'timestamp' => time(),
            'complexity' => $this->calculateComplexity($pattern),
            'risk_level' => $this->assessRisk($pattern)
        ];
        
        $this->auditLog[] = $audit;
        return $audit;
    }
    
    private function calculateComplexity($pattern) {
        // 计算正则表达式复杂度
        $complexity = 0;
        $complexity += substr_count($pattern, '+') * 2;
        $complexity += substr_count($pattern, '*') * 2;
        $complexity += substr_count($pattern, '?') * 1;
        $complexity += substr_count($pattern, '|') * 3;
        $complexity += substr_count($pattern, '(') * 2;
        
        return $complexity;
    }
    
    private function assessRisk($pattern) {
        // 评估风险等级
        if (preg_match('/\([^)]*\)[+*]{2,}/', $pattern)) {
            return 'HIGH';
        } elseif (preg_match('/\([^)]*\)[+*]/', $pattern)) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }
    
    public function getAuditReport() {
        return $this->auditLog;
    }
}
```

## 📊 性能优化技巧

### 缓存策略

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
    
    public function clearCache() {
        $this->cache = [];
    }
    
    public function getCacheStats() {
        return [
            'cache_size' => count($this->cache),
            'memory_usage' => memory_get_usage(true)
        ];
    }
}
```

### 批量处理优化

```php
class BatchProcessor {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function batchValidate($data, $rules) {
        $results = [];
        $errors = [];
        
        foreach ($data as $index => $item) {
            $itemErrors = [];
            
            foreach ($rules as $field => $patternKey) {
                if (isset($item[$field])) {
                    if (!$this->regex->test($patternKey, $item[$field])) {
                        $itemErrors[$field] = "格式不正确";
                    }
                }
            }
            
            if (!empty($itemErrors)) {
                $errors[$index] = $itemErrors;
            } else {
                $results[] = $item;
            }
        }
        
        return [
            'valid' => $results,
            'errors' => $errors
        ];
    }
    
    public function batchExtract($text, $patterns) {
        $results = [];
        
        foreach ($patterns as $name => $patternKey) {
            $results[$name] = $this->regex->extractAll($patternKey, $text);
        }
        
        return $results;
    }
}
```

## 🔄 高级文本处理

### 智能数据清洗

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
        // 转换为小写
        return strtolower(trim($email));
    }
    
    private function normalizePhone($phone) {
        // 移除所有非数字字符，然后格式化
        $digits = preg_replace('/\D/', '', $phone);
        
        if (strlen($digits) === 11 && substr($digits, 0, 1) === '1') {
            return $digits;
        }
        
        return $phone;
    }
    
    private function normalizeName($name) {
        // 首字母大写
        return ucwords(strtolower(trim($name)));
    }
}
```

### 内容分析器

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
            'ips' => $this->analyzeIps($text),
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
    
    private function analyzePhones($text) {
        $phones = $this->regex->extractAll('phone:CN', $text);
        $analysis = [];
        
        foreach ($phones as $phone) {
            $analysis[] = [
                'phone' => $phone,
                'carrier' => $this->getCarrier($phone),
                'region' => $this->getRegion($phone)
            ];
        }
        
        return $analysis;
    }
    
    private function getStatistics($text) {
        return [
            'email_count' => $this->regex->count('email:basic', $text),
            'phone_count' => $this->regex->count('phone:CN', $text),
            'url_count' => $this->regex->count('url:basic', $text),
            'ip_count' => $this->regex->count('ip:v4', $text),
            'total_length' => strlen($text)
        ];
    }
    
    private function getEmailType($domain) {
        $enterpriseDomains = ['company.com', 'corp.com', 'enterprise.com'];
        return in_array($domain, $enterpriseDomains) ? 'enterprise' : 'personal';
    }
    
    private function getCarrier($phone) {
        $prefix = substr($phone, 0, 3);
        $carriers = [
            '138' => '中国移动',
            '139' => '中国移动',
            '159' => '中国联通',
            '188' => '中国电信'
        ];
        return $carriers[$prefix] ?? '未知运营商';
    }
    
    private function getRegion($phone) {
        // 根据手机号前3位判断地区
        $regionMap = [
            '138' => '北京',
            '139' => '上海',
            '159' => '广东'
        ];
        return $regionMap[substr($phone, 0, 3)] ?? '未知地区';
    }
}
```

## 🎨 框架集成

### Laravel 集成

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

### Symfony 集成

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

// src/Validator/Regex.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class Regex extends Constraint {
    public $patternKey;
    public $message = '值 "{{ value }}" 格式不正确';
}
```

## 📚 最佳实践总结

### 1. 性能优化

```php
// ✅ 好的做法：获取实例一次，重复使用
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

### 2. 错误处理

```php
// ✅ 好的做法：适当的错误处理
try {
    $isValid = $regex->test('email:basic', $email);
} catch (Exception $e) {
    error_log("正则验证失败: " . $e->getMessage());
    return false;
}

// ❌ 避免：忽略错误
$isValid = $regex->test('email:basic', $email); // 可能抛出异常
```

### 3. 安全考虑

```php
// ✅ 好的做法：启用安全保护
$regex->config(['securityEnabled' => true]);

// ❌ 避免：在生产环境关闭安全保护
$regex->config(['securityEnabled' => false]); // 安全风险
```

## 📖 扩展阅读

- [API 参考](api-reference.md) - 完整 API 文档
- [最佳实践](best-practices.md) - 更多最佳实践
- [性能优化](performance.md) - 性能优化建议

---

**您已经掌握了 PFinal Regex Center 的高级功能！** 🚀
