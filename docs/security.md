# 安全指南

本指南介绍 PFinal Regex Center 的安全特性和最佳实践。

## 🛡️ 安全特性

### ReDoS 攻击防护

PFinal Regex Center 内置了 ReDoS（Regular Expression Denial of Service）攻击防护机制。

#### 什么是 ReDoS 攻击？

ReDoS 攻击是一种通过恶意正则表达式导致服务器资源耗尽的攻击方式。攻击者利用正则表达式的回溯机制，构造特殊的输入字符串，使正则表达式引擎消耗大量 CPU 时间和内存。

#### 危险的正则表达式模式

以下模式可能导致 ReDoS 攻击：

```php
// ❌ 危险模式：嵌套重复量词
'/^(a+)+$/'

// ❌ 危险模式：指数级回溯
'/^(a|a)*$/'

// ❌ 危险模式：复杂嵌套
'/^((a+)+)+$/'

// ❌ 危险模式：多重选择
'/^(a|a|a)*$/'
```

#### 安全的正则表达式模式

```php
// ✅ 安全模式：简单重复
'/^[a-z]+$/'

// ✅ 安全模式：有限重复
'/^[a-z]{1,20}$/'

// ✅ 安全模式：非贪婪匹配
'/^[a-z]+?$/'

// ✅ 安全模式：原子组
'/^(?>[a-z]+)$/'
```

### 内置安全检测

PFinal Regex Center 会自动检测危险的正则表达式模式：

```php
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    echo $e->getMessage(); // Unsafe regex pattern: potential ReDoS risk
}
```

#### 检测规则

1. **嵌套重复量词检测**
   ```php
   // 检测模式：/\([^)]*\)[+*]{2,}/
   if (preg_match('/\([^)]*\)[+*]{2,}/', $pattern)) {
       return true; // 危险
   }
   ```

2. **指数级回溯检测**
   ```php
   // 检测模式：/\([^)]*\)\*\)[+*]{2,}/
   if (preg_match('/\([^)]*\)\*\)[+*]{2,}/', $pattern)) {
       return true; // 危险
   }
   ```

3. **复杂嵌套检测**
   ```php
   // 检测模式：/\([^)]*\([^)]*\)[^)]*\)[+*]{2,}/
   if (preg_match('/\([^)]*\([^)]*\)[^)]*\)[+*]{2,}/', $pattern)) {
       return true; // 危险
   }
   ```

## 🔧 安全配置

### 启用安全保护

```php
// 启用安全保护（推荐）
$regex->config(['securityEnabled' => true]);

// 禁用安全保护（不推荐）
$regex->config(['securityEnabled' => false]);
```

### 安全级别配置

```php
class SecurityConfig {
    private $regex;
    
    public function __construct($environment = 'production') {
        $this->regex = RegexManager::getInstance();
        $this->setupSecurity($environment);
    }
    
    private function setupSecurity($environment) {
        switch ($environment) {
            case 'production':
                $this->setupProductionSecurity();
                break;
            case 'development':
                $this->setupDevelopmentSecurity();
                break;
            case 'testing':
                $this->setupTestingSecurity();
                break;
        }
    }
    
    private function setupProductionSecurity() {
        // 生产环境：严格安全保护
        $this->regex->config(['securityEnabled' => true]);
    }
    
    private function setupDevelopmentSecurity() {
        // 开发环境：可以关闭安全保护（仅用于测试）
        $this->regex->config(['securityEnabled' => false]);
    }
    
    private function setupTestingSecurity() {
        // 测试环境：启用安全保护
        $this->regex->config(['securityEnabled' => true]);
    }
}
```

## 🚨 安全最佳实践

### 1. 输入验证

```php
class SecureValidator {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function validateInput($input) {
        // 验证输入长度
        if (strlen($input) > 1000) {
            throw new SecurityException('输入过长');
        }
        
        // 验证输入内容
        if (!$this->isSafeInput($input)) {
            throw new SecurityException('输入包含危险字符');
        }
        
        return true;
    }
    
    private function isSafeInput($input) {
        // 检查是否包含危险字符
        $dangerousChars = ['<', '>', '"', "'", '&', ';', '(', ')', '{', '}'];
        
        foreach ($dangerousChars as $char) {
            if (strpos($input, $char) !== false) {
                return false;
            }
        }
        
        return true;
    }
}
```

### 2. 正则表达式安全

```php
class SafeRegexManager {
    private $regex;
    private $allowedPatterns;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
        $this->loadAllowedPatterns();
    }
    
    private function loadAllowedPatterns() {
        // 只允许预定义的安全模式
        $this->allowedPatterns = [
            'email:basic',
            'phone:CN',
            'url:basic',
            'ip:v4'
        ];
    }
    
    public function safeTest($patternKey, $value) {
        // 检查是否允许使用该模式
        if (!in_array($patternKey, $this->allowedPatterns)) {
            throw new SecurityException('不允许使用该正则表达式模式');
        }
        
        // 验证输入
        if (!$this->isSafeInput($value)) {
            throw new SecurityException('输入不安全');
        }
        
        return $this->regex->test($patternKey, $value);
    }
    
    private function isSafeInput($input) {
        // 检查输入长度
        if (strlen($input) > 1000) {
            return false;
        }
        
        // 检查是否包含危险字符
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload/i',
            '/onerror/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return false;
            }
        }
        
        return true;
    }
}
```

### 3. 审计日志

```php
class SecurityAuditor {
    private $auditLog = [];
    
    public function logSecurityEvent($event, $details) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->auditLog[] = $logEntry;
        
        // 写入日志文件
        error_log("安全事件: " . json_encode($logEntry));
    }
    
    public function logRegexAttempt($patternKey, $pattern, $success) {
        $this->logSecurityEvent('regex_attempt', [
            'pattern_key' => $patternKey,
            'pattern' => $pattern,
            'success' => $success
        ]);
    }
    
    public function logDangerousPattern($patternKey, $pattern, $reason) {
        $this->logSecurityEvent('dangerous_pattern', [
            'pattern_key' => $patternKey,
            'pattern' => $pattern,
            'reason' => $reason
        ]);
    }
    
    public function getAuditLog() {
        return $this->auditLog;
    }
}
```

## 🔍 安全检测工具

### 正则表达式安全检测器

```php
class RegexSecurityDetector {
    private $dangerousPatterns = [
        // 嵌套重复量词
        '/\([^)]*\)[+*]{2,}/',
        // 指数级回溯
        '/\([^)]*\)\*\)[+*]{2,}/',
        // 复杂嵌套
        '/\([^)]*\([^)]*\)[^)]*\)[+*]{2,}/',
        // 多重选择
        '/\([^)]*\|[^)]*\)[+*]{2,}/'
    ];
    
    public function isSafePattern($pattern) {
        foreach ($this->dangerousPatterns as $dangerousPattern) {
            if (preg_match($dangerousPattern, $pattern)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function analyzePattern($pattern) {
        $analysis = [
            'safe' => true,
            'risks' => [],
            'complexity' => $this->calculateComplexity($pattern)
        ];
        
        foreach ($this->dangerousPatterns as $index => $dangerousPattern) {
            if (preg_match($dangerousPattern, $pattern)) {
                $analysis['safe'] = false;
                $analysis['risks'][] = $this->getRiskDescription($index);
            }
        }
        
        return $analysis;
    }
    
    private function calculateComplexity($pattern) {
        $complexity = 0;
        $complexity += substr_count($pattern, '+') * 2;
        $complexity += substr_count($pattern, '*') * 2;
        $complexity += substr_count($pattern, '?') * 1;
        $complexity += substr_count($pattern, '|') * 3;
        $complexity += substr_count($pattern, '(') * 2;
        
        return $complexity;
    }
    
    private function getRiskDescription($index) {
        $descriptions = [
            '嵌套重复量词可能导致指数级回溯',
            '指数级回溯模式可能导致 ReDoS 攻击',
            '复杂嵌套模式可能导致性能问题',
            '多重选择模式可能导致性能问题'
        ];
        
        return $descriptions[$index] ?? '未知风险';
    }
}
```

### 性能监控器

```php
class SecurityPerformanceMonitor {
    private $metrics = [];
    private $thresholds = [
        'max_execution_time' => 1.0, // 最大执行时间（秒）
        'max_memory_usage' => 10 * 1024 * 1024, // 最大内存使用（字节）
        'max_complexity' => 100 // 最大复杂度
    ];
    
    public function monitorRegex($patternKey, $pattern, $value) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            $result = $this->executeRegex($pattern, $value);
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;
            
            $this->recordMetrics($patternKey, $executionTime, $memoryUsage);
            
            // 检查是否超过阈值
            if ($executionTime > $this->thresholds['max_execution_time']) {
                $this->logPerformanceIssue($patternKey, 'execution_time', $executionTime);
            }
            
            if ($memoryUsage > $this->thresholds['max_memory_usage']) {
                $this->logPerformanceIssue($patternKey, 'memory_usage', $memoryUsage);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logPerformanceIssue($patternKey, 'exception', $e->getMessage());
            throw $e;
        }
    }
    
    private function executeRegex($pattern, $value) {
        return preg_match($pattern, $value);
    }
    
    private function recordMetrics($patternKey, $executionTime, $memoryUsage) {
        if (!isset($this->metrics[$patternKey])) {
            $this->metrics[$patternKey] = [];
        }
        
        $this->metrics[$patternKey][] = [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'timestamp' => time()
        ];
    }
    
    private function logPerformanceIssue($patternKey, $type, $value) {
        error_log("性能问题: {$patternKey} - {$type}: {$value}");
    }
    
    public function getMetrics() {
        return $this->metrics;
    }
}
```

## 🚨 安全事件处理

### 安全事件响应

```php
class SecurityIncidentResponse {
    private $incidents = [];
    
    public function handleSecurityIncident($type, $details) {
        $incident = [
            'id' => uniqid(),
            'type' => $type,
            'details' => $details,
            'timestamp' => time(),
            'status' => 'open'
        ];
        
        $this->incidents[] = $incident;
        
        // 根据事件类型采取相应措施
        switch ($type) {
            case 'redos_attempt':
                $this->handleRedosAttempt($incident);
                break;
            case 'dangerous_pattern':
                $this->handleDangerousPattern($incident);
                break;
            case 'performance_issue':
                $this->handlePerformanceIssue($incident);
                break;
        }
        
        return $incident;
    }
    
    private function handleRedosAttempt($incident) {
        // 记录安全事件
        error_log("ReDoS 攻击尝试: " . json_encode($incident));
        
        // 可以采取的措施：
        // 1. 暂时禁用相关功能
        // 2. 发送告警通知
        // 3. 记录到安全日志
    }
    
    private function handleDangerousPattern($incident) {
        // 记录危险模式
        error_log("危险正则表达式模式: " . json_encode($incident));
        
        // 可以采取的措施：
        // 1. 阻止模式添加
        // 2. 发送告警通知
        // 3. 记录到安全日志
    }
    
    private function handlePerformanceIssue($incident) {
        // 记录性能问题
        error_log("正则表达式性能问题: " . json_encode($incident));
        
        // 可以采取的措施：
        // 1. 优化正则表达式
        // 2. 添加缓存机制
        // 3. 发送告警通知
    }
    
    public function getIncidents() {
        return $this->incidents;
    }
}
```

## 📋 安全检查清单

### 开发阶段

- [ ] 启用安全保护
- [ ] 使用安全的正则表达式模式
- [ ] 验证用户输入
- [ ] 实现审计日志
- [ ] 进行安全测试

### 部署阶段

- [ ] 配置安全选项
- [ ] 设置监控告警
- [ ] 实施访问控制
- [ ] 定期安全审计
- [ ] 更新安全策略

### 运维阶段

- [ ] 监控安全事件
- [ ] 分析性能指标
- [ ] 更新安全规则
- [ ] 响应安全事件
- [ ] 定期安全评估

## 🔒 安全建议

### 1. 始终启用安全保护

```php
// ✅ 推荐：生产环境启用安全保护
$regex->config(['securityEnabled' => true]);

// ❌ 避免：在生产环境关闭安全保护
$regex->config(['securityEnabled' => false]);
```

### 2. 验证用户输入

```php
// ✅ 推荐：验证输入长度和内容
if (strlen($input) > 1000 || !$this->isSafeInput($input)) {
    throw new SecurityException('输入不安全');
}
```

### 3. 使用安全的正则表达式

```php
// ✅ 推荐：使用简单、安全的正则表达式
$regex->add('safe', '/^[a-zA-Z0-9_]{3,20}$/', '安全用户名');

// ❌ 避免：使用复杂、危险的正则表达式
$regex->add('dangerous', '/^(a+)+$/', '危险模式');
```

### 4. 实施监控和审计

```php
// ✅ 推荐：记录安全事件
$auditor->logSecurityEvent('regex_attempt', $details);
```

### 5. 定期安全评估

- 定期检查正则表达式的安全性
- 监控性能指标
- 更新安全策略
- 响应安全事件

---

**遵循这些安全最佳实践，确保您的应用安全可靠！** 🛡️
