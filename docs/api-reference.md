# API 参考

PFinal Regex Center 完整的 API 文档。

## 📋 目录

- [RegexManager 类](#regexmanager-类)
- [PatternCollection 类](#patterncollection-类)
- [配置选项](#配置选项)
- [异常处理](#异常处理)
- [类型定义](#类型定义)

## RegexManager 类

### 获取实例

#### `getInstance(): RegexManager`

获取 RegexManager 单例实例。

```php
$regex = RegexManager::getInstance();
```

**返回值**: `RegexManager` - 单例实例

---

### 验证方法

#### `test(string $patternKey, string $value): bool`

测试值是否匹配指定的正则表达式。

```php
$isValid = $regex->test('email:basic', 'user@example.com');
```

**参数**:
- `$patternKey` (string): 正则表达式键名，格式为 `type:group`
- `$value` (string): 要测试的值

**返回值**: `bool` - 是否匹配

**异常**: 可能抛出 `RuntimeException` 如果正则表达式不安全

---

### 获取正则表达式

#### `get(string $patternKey): string|null`

获取指定的正则表达式。

```php
$pattern = $regex->get('email:basic');
// 返回: /[^\s@]+@[^\s@]+\.[^\s@]+/
```

**参数**:
- `$patternKey` (string): 正则表达式键名

**返回值**: `string|null` - 正则表达式或 null（如果不存在）

---

### 文本处理方法

#### `extractAll(string $patternKey, string $text): array`

提取文本中所有匹配的项。

```php
$text = "联系我们：admin@example.com 或 support@company.org";
$emails = $regex->extractAll('email:basic', $text);
// 返回: ['admin@example.com', 'support@company.org']
```

**参数**:
- `$patternKey` (string): 正则表达式键名
- `$text` (string): 要搜索的文本

**返回值**: `array` - 匹配的项数组

---

#### `replaceAll(string $patternKey, string $text, callable|string $replacement): string`

替换文本中所有匹配的项。

```php
// 简单替换
$newText = $regex->replaceAll('url:basic', $text, '[链接]');

// 回调函数替换
$newText = $regex->replaceAll('phone:CN', $text, function($matches) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});
```

**参数**:
- `$patternKey` (string): 正则表达式键名
- `$text` (string): 要处理的文本
- `$replacement` (callable|string): 替换字符串或回调函数

**返回值**: `string` - 替换后的文本

---

#### `highlight(string $patternKey, string $text, string $replacement): string`

高亮文本中所有匹配的项。

```php
$highlighted = $regex->highlight('email:basic', $text, '<strong>$&</strong>');
```

**参数**:
- `$patternKey` (string): 正则表达式键名
- `$text` (string): 要处理的文本
- `$replacement` (string): 替换字符串，可以使用 `$&` 表示匹配的内容

**返回值**: `string` - 高亮后的文本

---

#### `count(string $patternKey, string $text): int`

统计匹配数量。

```php
$count = $regex->count('email:basic', $text);
// 返回: 3
```

**参数**:
- `$patternKey` (string): 正则表达式键名
- `$text` (string): 要统计的文本

**返回值**: `int` - 匹配数量

---

### 自定义正则表达式

#### `add(string $key, string $pattern, string $description = '', array $examples = []): void`

添加单个正则表达式。

```php
$regex->add('custom', '/^custom-pattern$/', '自定义模式', [
    'valid' => ['custom-pattern'],
    'invalid' => ['invalid-pattern']
]);
```

**参数**:
- `$key` (string): 正则表达式键名
- `$pattern` (string): 正则表达式
- `$description` (string): 描述（可选）
- `$examples` (array): 示例（可选）

**异常**: 可能抛出 `RuntimeException` 如果正则表达式不安全

---

#### `inject(array $patterns): void`

注入额外的正则表达式（保留内置模式）。

```php
$regex->inject([
    'custom' => [
        'pattern1' => '/^pattern1$/',
        'pattern2' => '/^pattern2$/'
    ]
]);
```

**参数**:
- `$patterns` (array): 正则表达式数组

---

#### `use(array $patterns): void`

设置自定义正则表达式集合（完全替换内置模式）。

```php
$regex->use([
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    'phone' => '/^1[3-9]\d{9}$/'
]);
```

**参数**:
- `$patterns` (array): 正则表达式数组

---

### 配置方法

#### `config(array $config): void`

设置配置选项。

```php
$regex->config([
    'securityEnabled' => true,
    'caseSensitive' => false
]);
```

**参数**:
- `$config` (array): 配置数组

**配置选项**:
- `securityEnabled` (bool): 是否启用安全保护，默认 `true`
- `caseSensitive` (bool): 是否大小写敏感，默认 `false`

---

## PatternCollection 类

### 静态方法

#### `getBuiltInPatterns(): array`

获取内置的正则表达式模式。

```php
$patterns = PatternCollection::getBuiltInPatterns();
```

**返回值**: `array` - 内置正则表达式数组

**返回格式**:
```php
[
    'email' => [
        'basic' => '/[^\s@]+@[^\s@]+\.[^\s@]+/',
        'strict' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        'enterprise' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov)/i',
        'default' => 'basic'
    ],
    'phone' => [
        'CN' => '/1[3-9]\d{9}/',
        'US' => '/\+?1?-?\(?[0-9]{3}\)?-?[0-9]{3}-?[0-9]{4}/',
        'UK' => '/\+44[0-9]{10}/',
        'JP' => '/\+81[0-9]{10,11}/',
        'default' => 'CN'
    ],
    // ... 更多模式
]
```

---

## 配置选项

### 安全配置

#### `securityEnabled`

控制是否启用 ReDoS 攻击防护。

```php
$regex->config(['securityEnabled' => true]);  // 启用（推荐）
$regex->config(['securityEnabled' => false]); // 禁用（不推荐）
```

**默认值**: `true`

**说明**: 启用后会自动检测危险的正则表达式模式，防止 ReDoS 攻击。

---

#### `caseSensitive`

控制正则表达式是否大小写敏感。

```php
$regex->config(['caseSensitive' => true]);  // 大小写敏感
$regex->config(['caseSensitive' => false]); // 大小写不敏感
```

**默认值**: `false`

---

## 异常处理

### RuntimeException

当尝试添加不安全的正则表达式时抛出。

```php
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    echo $e->getMessage(); // Unsafe regex pattern for type "dangerous": potential ReDoS risk
}
```

**常见原因**:
- 正则表达式包含 ReDoS 攻击模式
- 嵌套的重复量词
- 指数级回溯模式

---

## 类型定义

### 模式键名格式

模式键名使用 `type:group` 格式：

- `type`: 模式类型（如 `email`, `phone`, `url`）
- `group`: 分组名称（如 `basic`, `strict`, `CN`）

**示例**:
```php
'email:basic'     // 基础邮箱验证
'phone:CN'        // 中国手机号
'url:strict'      // 严格 URL 格式
'password:strong' // 强密码验证
```

### 回调函数格式

#### replaceAll 回调函数

```php
function(string $matches, int $offset): string
```

**参数**:
- `$matches` (array): 匹配结果数组，`$matches[0]` 是完整匹配
- `$offset` (int): 匹配在文本中的位置

**返回值**: `string` - 替换字符串

**示例**:
```php
$regex->replaceAll('phone:CN', $text, function($matches, $offset) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});
```

---

## 内置模式类型

### 邮箱验证

| 键名 | 描述 | 示例 |
|------|------|------|
| `email:basic` | 基础邮箱格式 | `user@example.com` |
| `email:strict` | 严格邮箱格式 | `user+tag@example.com` |
| `email:enterprise` | 企业邮箱格式 | `user@company.com` |

### 电话号码

| 键名 | 描述 | 示例 |
|------|------|------|
| `phone:CN` | 中国手机号 | `13812345678` |
| `phone:US` | 美国电话号码 | `+1-555-123-4567` |
| `phone:UK` | 英国电话号码 | `+441234567890` |
| `phone:JP` | 日本电话号码 | `+81123456789` |

### URL 链接

| 键名 | 描述 | 示例 |
|------|------|------|
| `url:basic` | 基础 URL 格式 | `https://www.example.com` |
| `url:strict` | 严格 URL 格式 | `https://www.example.com` |

### IP 地址

| 键名 | 描述 | 示例 |
|------|------|------|
| `ip:v4` | IPv4 地址 | `192.168.1.1` |
| `ip:v6` | IPv6 地址 | `2001:0db8:85a3::8a2e:370:7334` |

### 密码强度

| 键名 | 描述 | 示例 |
|------|------|------|
| `password:weak` | 弱密码 | `123456` |
| `password:medium` | 中等密码 | `abc123` |
| `password:strong` | 强密码 | `Abc123!` |

### 更多类型

- **用户名**: `username:basic`, `username:strict`
- **日期**: `date:YYYY-MM-DD`, `date:DD/MM/YYYY`, `date:MM/DD/YYYY`
- **时间**: `time:HH:MM`, `time:HH:MM:SS`
- **颜色**: `color:hex`, `color:rgb`
- **邮政编码**: `postalCode:CN`, `postalCode:US`, `postalCode:UK`
- **货币**: `currency:CNY`, `currency:USD`, `currency:EUR`
- **信用卡**: `creditCard:VISA`, `creditCard:MASTERCARD`, `creditCard:AMEX`
- **MAC 地址**: `macAddress:basic`, `macAddress:colon`, `macAddress:dash`
- **UUID**: `uuid:v4`, `uuid:any`
- **哈希值**: `hash:md5`, `hash:sha1`, `hash:sha256`, `hash:sha512`
- **语义化版本**: `semanticVersion:basic`, `semanticVersion:full`

---

## 使用示例

### 基本验证

```php
$regex = RegexManager::getInstance();

// 验证邮箱
if ($regex->test('email:basic', 'user@example.com')) {
    echo "邮箱格式正确";
}

// 验证手机号
if ($regex->test('phone:CN', '13812345678')) {
    echo "手机号格式正确";
}
```

### 文本处理

```php
$text = "联系我们：admin@example.com 或 support@company.org";

// 提取所有邮箱
$emails = $regex->extractAll('email:basic', $text);

// 替换所有邮箱
$newText = $regex->replaceAll('email:basic', $text, '[邮箱]');

// 统计邮箱数量
$count = $regex->count('email:basic', $text);
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
$regex->config(['securityEnabled' => true]);

// 注入自定义配置
$regex->inject([
    'team' => [
        'email' => '/^[a-z0-9._%+-]+@company\.com$/i'
    ]
]);
```

---

## 性能考虑

### 单例模式

RegexManager 使用单例模式，确保全局只有一个实例：

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

### 缓存策略

对于重复验证的场景，建议使用缓存：

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

---

## 安全注意事项

### ReDoS 攻击防护

PFinal Regex Center 内置 ReDoS 攻击防护：

```php
// ✅ 安全的正则表达式
$regex->add('safe', '/^[a-z]+$/', '安全模式');

// ❌ 危险的正则表达式会被自动阻止
try {
    $regex->add('dangerous', '/^(a+)+$/', '危险模式');
} catch (RuntimeException $e) {
    echo $e->getMessage(); // Unsafe regex pattern: potential ReDoS risk
}
```

### 配置安全级别

```php
// 生产环境：启用安全保护
$regex->config(['securityEnabled' => true]);

// 开发环境：可以关闭安全保护（不推荐）
$regex->config(['securityEnabled' => false]);
```

---

## 常见问题

### Q: 如何处理不存在的模式键名？

A: `get()` 方法会返回 `null`，`test()` 方法会返回 `false`：

```php
$pattern = $regex->get('nonexistent'); // 返回 null
$isValid = $regex->test('nonexistent', 'value'); // 返回 false
```

### Q: 如何添加多个自定义正则表达式？

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

---

**这就是 PFinal Regex Center 的完整 API 参考！** 📚
