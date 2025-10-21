# 性能优化

本指南介绍如何优化 PFinal Regex Center 的性能。

## 🚀 性能优化策略

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

### 3. 批量处理

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

## 📊 性能监控

### 1. 性能指标监控

```php
class RegexPerformanceMonitor {
    private $metrics = [];
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function testWithMetrics($patternKey, $value) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $result = $this->regex->test($patternKey, $value);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $this->recordMetrics($patternKey, $endTime - $startTime, $endMemory - $startMemory);
        
        return $result;
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
    
    public function getMetrics() {
        $summary = [];
        
        foreach ($this->metrics as $patternKey => $measurements) {
            $executionTimes = array_column($measurements, 'execution_time');
            $memoryUsages = array_column($measurements, 'memory_usage');
            
            $summary[$patternKey] = [
                'count' => count($measurements),
                'avg_execution_time' => array_sum($executionTimes) / count($executionTimes),
                'max_execution_time' => max($executionTimes),
                'min_execution_time' => min($executionTimes),
                'avg_memory_usage' => array_sum($memoryUsages) / count($memoryUsages),
                'max_memory_usage' => max($memoryUsages),
                'min_memory_usage' => min($memoryUsages)
            ];
        }
        
        return $summary;
    }
}
```

### 2. 内存使用监控

```php
class MemoryMonitor {
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
    
    public function getMemoryStats() {
        $usage = $this->getMemoryUsage();
        
        return [
            'current_mb' => round($usage['current_usage'] / 1024 / 1024, 2),
            'peak_mb' => round($usage['peak_usage'] / 1024 / 1024, 2),
            'instance_count' => $usage['instance_count']
        ];
    }
}
```

## ⚡ 性能优化技巧

### 1. 正则表达式优化

```php
class RegexOptimizer {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function optimizePattern($pattern) {
        // 移除不必要的捕获组
        $optimized = preg_replace('/\(\?:([^)]+)\)/', '$1', $pattern);
        
        // 使用非贪婪匹配
        $optimized = str_replace('+', '+?', $optimized);
        
        // 使用字符类而不是选择
        $optimized = preg_replace('/\[a-z\]\|\[A-Z\]/', '[a-zA-Z]', $optimized);
        
        return $optimized;
    }
    
    public function testOptimizedPattern($originalPattern, $testData) {
        $optimizedPattern = $this->optimizePattern($originalPattern);
        
        $originalTime = $this->benchmarkPattern($originalPattern, $testData);
        $optimizedTime = $this->benchmarkPattern($optimizedPattern, $testData);
        
        return [
            'original_time' => $originalTime,
            'optimized_time' => $optimizedTime,
            'improvement' => ($originalTime - $optimizedTime) / $originalTime * 100
        ];
    }
    
    private function benchmarkPattern($pattern, $testData) {
        $startTime = microtime(true);
        
        foreach ($testData as $data) {
            preg_match($pattern, $data);
        }
        
        $endTime = microtime(true);
        
        return $endTime - $startTime;
    }
}
```

### 2. 缓存优化

```php
class AdvancedCache {
    private $cache = [];
    private $regex;
    private $maxCacheSize = 1000;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function testWithAdvancedCache($patternKey, $value) {
        $cacheKey = $this->generateCacheKey($patternKey, $value);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $result = $this->regex->test($patternKey, $value);
        
        // 实现 LRU 缓存
        if (count($this->cache) >= $this->maxCacheSize) {
            $this->evictOldest();
        }
        
        $this->cache[$cacheKey] = [
            'result' => $result,
            'timestamp' => time(),
            'access_count' => 1
        ];
        
        return $result;
    }
    
    private function generateCacheKey($patternKey, $value) {
        return $patternKey . ':' . md5($value);
    }
    
    private function evictOldest() {
        $oldestKey = null;
        $oldestTime = time();
        
        foreach ($this->cache as $key => $data) {
            if ($data['timestamp'] < $oldestTime) {
                $oldestTime = $data['timestamp'];
                $oldestKey = $key;
            }
        }
        
        if ($oldestKey !== null) {
            unset($this->cache[$oldestKey]);
        }
    }
    
    public function getCacheStats() {
        $totalAccess = 0;
        $totalItems = count($this->cache);
        
        foreach ($this->cache as $data) {
            $totalAccess += $data['access_count'];
        }
        
        return [
            'cache_size' => $totalItems,
            'max_size' => $this->maxCacheSize,
            'total_access' => $totalAccess,
            'avg_access_per_item' => $totalItems > 0 ? $totalAccess / $totalItems : 0
        ];
    }
}
```

### 3. 并发处理

```php
class ConcurrentProcessor {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function processConcurrently($data, $patternKey, $batchSize = 100) {
        $batches = array_chunk($data, $batchSize);
        $results = [];
        
        foreach ($batches as $batch) {
            $batchResults = $this->processBatch($batch, $patternKey);
            $results = array_merge($results, $batchResults);
        }
        
        return $results;
    }
    
    private function processBatch($batch, $patternKey) {
        $results = [];
        
        foreach ($batch as $item) {
            $results[] = $this->regex->test($patternKey, $item);
        }
        
        return $results;
    }
}
```

## 🔧 性能测试

### 1. 基准测试

```php
class RegexBenchmark {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function benchmarkPattern($patternKey, $testData, $iterations = 1000) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testData as $data) {
                $this->regex->test($patternKey, $data);
            }
        }
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        return [
            'pattern_key' => $patternKey,
            'iterations' => $iterations,
            'total_time' => $endTime - $startTime,
            'avg_time_per_iteration' => ($endTime - $startTime) / $iterations,
            'memory_usage' => $endMemory - $startMemory,
            'operations_per_second' => $iterations / ($endTime - $startTime)
        ];
    }
    
    public function comparePatterns($patterns, $testData, $iterations = 1000) {
        $results = [];
        
        foreach ($patterns as $patternKey) {
            $results[$patternKey] = $this->benchmarkPattern($patternKey, $testData, $iterations);
        }
        
        return $results;
    }
}
```

### 2. 压力测试

```php
class StressTest {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function stressTest($patternKey, $testData, $duration = 60) {
        $startTime = time();
        $endTime = $startTime + $duration;
        $operations = 0;
        $errors = 0;
        
        while (time() < $endTime) {
            try {
                foreach ($testData as $data) {
                    $this->regex->test($patternKey, $data);
                    $operations++;
                }
            } catch (Exception $e) {
                $errors++;
            }
        }
        
        return [
            'pattern_key' => $patternKey,
            'duration' => $duration,
            'operations' => $operations,
            'errors' => $errors,
            'operations_per_second' => $operations / $duration,
            'error_rate' => $errors / $operations * 100
        ];
    }
}
```

## 📈 性能分析

### 1. 性能分析器

```php
class PerformanceProfiler {
    private $profiles = [];
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function profile($patternKey, $value) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $result = $this->regex->test($patternKey, $value);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $profile = [
            'pattern_key' => $patternKey,
            'value' => $value,
            'execution_time' => $endTime - $startTime,
            'memory_usage' => $endMemory - $startMemory,
            'timestamp' => time()
        ];
        
        $this->profiles[] = $profile;
        
        return $result;
    }
    
    public function getProfileSummary() {
        if (empty($this->profiles)) {
            return [];
        }
        
        $executionTimes = array_column($this->profiles, 'execution_time');
        $memoryUsages = array_column($this->profiles, 'memory_usage');
        
        return [
            'total_operations' => count($this->profiles),
            'avg_execution_time' => array_sum($executionTimes) / count($executionTimes),
            'max_execution_time' => max($executionTimes),
            'min_execution_time' => min($executionTimes),
            'avg_memory_usage' => array_sum($memoryUsages) / count($memoryUsages),
            'max_memory_usage' => max($memoryUsages),
            'min_memory_usage' => min($memoryUsages)
        ];
    }
    
    public function getSlowestOperations($limit = 10) {
        usort($this->profiles, function($a, $b) {
            return $b['execution_time'] <=> $a['execution_time'];
        });
        
        return array_slice($this->profiles, 0, $limit);
    }
}
```

### 2. 性能报告

```php
class PerformanceReporter {
    private $profiler;
    
    public function __construct() {
        $this->profiler = new PerformanceProfiler();
    }
    
    public function generateReport($patternKey, $testData) {
        // 运行性能测试
        foreach ($testData as $data) {
            $this->profiler->profile($patternKey, $data);
        }
        
        $summary = $this->profiler->getProfileSummary();
        $slowest = $this->profiler->getSlowestOperations(5);
        
        return [
            'summary' => $summary,
            'slowest_operations' => $slowest,
            'recommendations' => $this->generateRecommendations($summary)
        ];
    }
    
    private function generateRecommendations($summary) {
        $recommendations = [];
        
        if ($summary['avg_execution_time'] > 0.1) {
            $recommendations[] = '执行时间较长，建议优化正则表达式';
        }
        
        if ($summary['avg_memory_usage'] > 1024 * 1024) {
            $recommendations[] = '内存使用较高，建议检查正则表达式复杂度';
        }
        
        if ($summary['max_execution_time'] > 1.0) {
            $recommendations[] = '存在执行时间超过1秒的操作，建议检查正则表达式安全性';
        }
        
        return $recommendations;
    }
}
```

## 🎯 性能最佳实践

### 1. 选择合适的方法

```php
// ✅ 推荐：使用内置方法
$regex->test('email:basic', $email);

// ❌ 避免：重复编译正则表达式
preg_match('/[^\s@]+@[^\s@]+\.[^\s@]+/', $email);
```

### 2. 避免不必要的操作

```php
// ✅ 推荐：批量处理
$results = [];
foreach ($data as $item) {
    $results[] = $regex->test('email:basic', $item);
}

// ❌ 避免：逐个处理
foreach ($data as $item) {
    $regex = RegexManager::getInstance(); // 不必要的开销
    $result = $regex->test('email:basic', $item);
}
```

### 3. 使用缓存

```php
// ✅ 推荐：使用缓存
$cache = new RegexCache();
$result = $cache->testWithCache('email:basic', $email);

// ❌ 避免：重复计算
$result = $regex->test('email:basic', $email);
```

### 4. 监控性能

```php
// ✅ 推荐：监控性能
$monitor = new RegexPerformanceMonitor();
$result = $monitor->testWithMetrics('email:basic', $email);

// ❌ 避免：忽略性能
$result = $regex->test('email:basic', $email);
```

## 📊 性能指标

### 关键指标

1. **执行时间**: 正则表达式执行所需的时间
2. **内存使用**: 正则表达式执行所需的内存
3. **操作频率**: 每秒可以执行的操作数
4. **错误率**: 执行失败的比例

### 性能目标

- **执行时间**: < 1ms（单次操作）
- **内存使用**: < 1MB（单次操作）
- **操作频率**: > 1000 ops/s
- **错误率**: < 0.1%

## 🔧 性能调优

### 1. 正则表达式优化

```php
// 优化前
$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

// 优化后
$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
// 使用非贪婪匹配
$pattern = '/^[a-zA-Z0-9._%+-]+?@[a-zA-Z0-9.-]+?\.[a-zA-Z]{2,}$/';
```

### 2. 缓存策略

```php
// 实现智能缓存
class SmartCache {
    private $cache = [];
    private $accessCounts = [];
    
    public function get($key) {
        if (isset($this->accessCounts[$key])) {
            $this->accessCounts[$key]++;
        } else {
            $this->accessCounts[$key] = 1;
        }
        
        return $this->cache[$key] ?? null;
    }
    
    public function set($key, $value) {
        $this->cache[$key] = $value;
    }
    
    public function evictLeastUsed() {
        $leastUsedKey = array_search(min($this->accessCounts), $this->accessCounts);
        unset($this->cache[$leastUsedKey]);
        unset($this->accessCounts[$leastUsedKey]);
    }
}
```

### 3. 批量处理优化

```php
// 实现批量处理
class BatchOptimizer {
    private $regex;
    
    public function __construct() {
        $this->regex = RegexManager::getInstance();
    }
    
    public function batchTest($patternKey, $values) {
        $results = [];
        
        // 预处理
        $preprocessed = $this->preprocess($values);
        
        // 批量处理
        foreach ($preprocessed as $value) {
            $results[] = $this->regex->test($patternKey, $value);
        }
        
        return $results;
    }
    
    private function preprocess($values) {
        // 去重
        $unique = array_unique($values);
        
        // 排序（可选）
        sort($unique);
        
        return $unique;
    }
}
```

---

**遵循这些性能优化建议，确保您的应用高效运行！** 🚀
