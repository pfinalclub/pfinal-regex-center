<?php

declare(strict_types=1);

namespace pfinalclub\RegexCenter;

/**
 * 正则表达式管理器
 */
class RegexManager
{
    /**
     * @var RegexManager|null 单例实例
     */
    private static ?RegexManager $instance = null;

    /**
     * @var array 内置正则表达式
     */
    private array $patterns = [];

    /**
     * @var array 自定义正则表达式
     */
    private array $customPatterns = [];

    /**
     * @var array 配置选项
     */
    private array $config = [
        'securityEnabled' => true,
        'caseSensitive' => false
    ];

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->patterns = PatternCollection::getBuiltInPatterns();
    }

    /**
     * 获取单例实例
     *
     * @return RegexManager
     */
    public static function getInstance(): RegexManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取正则表达式
     *
     * @param string $patternKey 正则表达式键名，支持 type:group 格式
     * @return string|null 正则表达式
     */
    public function get(string $patternKey): ?string
    {
        // 处理 type:group 格式
        if (strpos($patternKey, ':') !== false) {
            [$type, $group] = explode(':', $patternKey, 2);
            
            // 先查找自定义模式
            if (isset($this->customPatterns[$type][$group])) {
                $pattern = $this->customPatterns[$type][$group];
                return is_array($pattern) && isset($pattern['pattern']) ? $pattern['pattern'] : $pattern;
            }
            
            // 再查找内置模式
            if (isset($this->patterns[$type][$group])) {
                return $this->patterns[$type][$group];
            }
            
            // 如果请求的是默认组
            if ($group === 'default' && isset($this->patterns[$type]['default'])) {
                $defaultGroup = $this->patterns[$type]['default'];
                if (isset($this->patterns[$type][$defaultGroup])) {
                    return $this->patterns[$type][$defaultGroup];
                }
            }
        } else {
            // 查找简单键名
            if (isset($this->customPatterns[$patternKey])) {
                $pattern = $this->customPatterns[$patternKey];
                return is_array($pattern) && isset($pattern['pattern']) ? $pattern['pattern'] : $pattern;
            }
            
            if (isset($this->patterns[$patternKey])) {
                // 如果是数组且有默认值，返回默认值
                if (is_array($this->patterns[$patternKey])) {
                    if (isset($this->patterns[$patternKey]['default'])) {
                        $defaultGroup = $this->patterns[$patternKey]['default'];
                        if (isset($this->patterns[$patternKey][$defaultGroup])) {
                            return $this->patterns[$patternKey][$defaultGroup];
                        }
                    }
                    // 返回第一个值
                    $firstValue = reset($this->patterns[$patternKey]);
                    return is_string($firstValue) ? $firstValue : null;
                }
                return $this->patterns[$patternKey];
            }
        }
        
        return null;
    }

    /**
     * 测试值是否匹配正则表达式
     *
     * @param string $patternKey 正则表达式键名
     * @param string $value 要测试的值
     * @return bool 是否匹配
     */
    public function test(string $patternKey, string $value): bool
    {
        $pattern = $this->get($patternKey);
        if ($pattern === null) {
            return false;
        }
        
        // 检查是否可能引起 ReDoS 攻击的正则表达式
        if ($this->isPossiblyVulnerableToRedos($pattern)) {
            throw new \RuntimeException("Pattern may be vulnerable to ReDoS attack: $pattern");
        }
        
        return preg_match($pattern, $value) === 1;
    }

    /**
     * 设置自定义正则表达式集合（完全替换内置模式）
     *
     * @param array $patterns 自定义正则表达式集合
     * @return void
     */
    public function use(array $patterns): void
    {
        $this->customPatterns = $patterns;
    }

    /**
     * 注入额外的正则表达式（保留内置模式）
     *
     * @param array $patterns 额外的正则表达式
     * @return void
     */
    public function inject(array $patterns): void
    {
        $this->customPatterns = array_merge_recursive($this->customPatterns, $patterns);
    }

    /**
     * 添加单个正则表达式
     *
     * @param string $key 键名
     * @param string $pattern 正则表达式
     * @param string $description 描述
     * @param array $examples 示例
     * @return void
     */
    public function add(string $key, string $pattern, string $description = '', array $examples = []): void
    {
        // 检查安全性
        if ($this->config['securityEnabled']) {
            $this->isPatternSafe($pattern, $key);
        }
        
        $this->customPatterns[$key] = [
            'pattern' => $pattern,
            'description' => $description,
            'examples' => $examples
        ];
    }

    /**
     * 设置配置
     *
     * @param array $config 配置数组
     * @return void
     */
    public function config(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 提取文本中所有匹配的项
     *
     * @param string $patternKey 正则表达式键名
     * @param string $text 文本
     * @return array 匹配的项数组
     */
    public function extractAll(string $patternKey, string $text): array
    {
        $pattern = $this->get($patternKey);
        if ($pattern === null) {
            return [];
        }
        
        $result = preg_match_all($pattern, $text, $matches);
        if ($result === false || $result === 0) {
            return [];
        }
        
        return $matches[0] ?? [];
    }

    /**
     * 替换文本中所有匹配的项（支持回调函数）
     *
     * @param string $patternKey 正则表达式键名
     * @param string $text 文本
     * @param callable|string $replacement 替换字符串或回调函数
     * @return string 替换后的文本
     */
    public function replaceAll(string $patternKey, string $text, $replacement): string
    {
        $pattern = $this->get($patternKey);
        if ($pattern === null) {
            return $text;
        }
        
        if (is_callable($replacement)) {
            return preg_replace_callback($pattern, $replacement, $text);
        }
        
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * 高亮文本中所有匹配的项
     *
     * @param string $patternKey 正则表达式键名
     * @param string $text 文本
     * @param string $replacement 替换字符串，可以使用 $& 表示匹配的内容
     * @return string 高亮后的文本
     */
    public function highlight(string $patternKey, string $text, string $replacement): string
    {
        $pattern = $this->get($patternKey);
        if ($pattern === null) {
            return $text;
        }
        
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * 统计匹配数量
     *
     * @param string $patternKey 正则表达式键名
     * @param string $text 文本
     * @return int 匹配数量
     */
    public function count(string $patternKey, string $text): int
    {
        $pattern = $this->get($patternKey);
        if ($pattern === null) {
            return 0;
        }
        
        $result = preg_match_all($pattern, $text, $matches);
        return $result === false ? 0 : $result;
    }

    /**
     * 检查正则表达式是否安全
     *
     * @param string $pattern 正则表达式
     * @param string $key 键名
     * @return void
     * @throws \RuntimeException 如果正则表达式不安全
     */
    private function isPatternSafe(string $pattern, string $key): void
    {
        if (!$this->config['securityEnabled']) {
            return;
        }
        
        // 检查 ReDoS 攻击模式
        if ($this->isPossiblyVulnerableToRedos($pattern)) {
            throw new \RuntimeException("Unsafe regex pattern for type \"{$key}\": potential ReDoS risk");
        }
    }

    /**
     * 检查正则表达式是否可能容易受到 ReDoS 攻击
     *
     * @param string $pattern 正则表达式
     * @return bool 是否可能容易受到 ReDoS 攻击
     */
    private function isPossiblyVulnerableToRedos(string $pattern): bool
    {
        // 简单检查一些常见的 ReDoS 模式
        // 注意：这只是一个基本检查，不能完全防止 ReDoS
        
        // 检查是否有嵌套的重复量词
        if (preg_match('/\([^)]*\[[^\]]*\][^)]*\)[+*]/', $pattern)) {
            return true;
        }
        
        // 检查是否有交替的重复模式
        if (preg_match('/\([^|)]*\|[^|)]*\)[+*]/', $pattern)) {
            return true;
        }
        
        // 检查是否有指数级回溯的模式（更精确的检测）
        if (preg_match('/\([^)]*\)\*\)[+*]{2,}/', $pattern)) {
            return true;
        }
        
        // 检查是否有复杂的嵌套重复（更精确的检测）
        if (preg_match('/\([^)]*\([^)]*\)[^)]*\)[+*]{2,}/', $pattern)) {
            return true;
        }
        
        return false;
    }
}