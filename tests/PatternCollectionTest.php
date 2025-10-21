<?php

declare(strict_types=1);

namespace pfinalclub\RegexCenter\Tests;

use pfinalclub\RegexCenter\PatternCollection;

/**
 * PatternCollection 类测试
 */
class PatternCollectionTest extends TestCase
{
    /**
     * 测试获取内置模式
     *
     * @return void
     */
    public function testGetBuiltInPatterns(): void
    {
        $patterns = PatternCollection::getBuiltInPatterns();
        
        // 检查是否返回了数组
        $this->assertIsArray($patterns);
        
        // 检查是否包含基本的正则表达式类型
        $this->assertArrayHasKey('email', $patterns);
        $this->assertArrayHasKey('phone', $patterns);
        $this->assertArrayHasKey('idCard', $patterns);
        $this->assertArrayHasKey('url', $patterns);
        $this->assertArrayHasKey('ip', $patterns);
        $this->assertArrayHasKey('bankCard', $patterns);
        $this->assertArrayHasKey('password', $patterns);
        $this->assertArrayHasKey('username', $patterns);
        $this->assertArrayHasKey('date', $patterns);
        $this->assertArrayHasKey('time', $patterns);
        $this->assertArrayHasKey('color', $patterns);
        $this->assertArrayHasKey('postalCode', $patterns);
        $this->assertArrayHasKey('currency', $patterns);
        
        // 检查 email 正则表达式
        $this->assertArrayHasKey('basic', $patterns['email']);
        $this->assertArrayHasKey('strict', $patterns['email']);
        $this->assertArrayHasKey('enterprise', $patterns['email']);
        
        // 检查 phone 正则表达式
        $this->assertArrayHasKey('CN', $patterns['phone']);
        $this->assertArrayHasKey('US', $patterns['phone']);
        $this->assertArrayHasKey('default', $patterns['phone']);
        
        // 检查 idCard 正则表达式
        $this->assertArrayHasKey('CN', $patterns['idCard']);
        
        // 检查 ip 正则表达式
        $this->assertArrayHasKey('v4', $patterns['ip']);
        
        // 检查 bankCard 正则表达式
        $this->assertArrayHasKey('CN', $patterns['bankCard']);
        
        // 检查 password 正则表达式
        $this->assertArrayHasKey('strong', $patterns['password']);
        $this->assertArrayHasKey('medium', $patterns['password']);
        $this->assertArrayHasKey('default', $patterns['password']);
    }

    /**
     * 测试所有类型都有默认值
     *
     * @return void
     */
    public function testAllTypesHaveDefaults(): void
    {
        $patterns = PatternCollection::getBuiltInPatterns();
        
        foreach ($patterns as $type => $typePatterns) {
            if (is_array($typePatterns)) {
                $this->assertArrayHasKey('default', $typePatterns, "Type {$type} should have a default value");
            }
        }
    }

    /**
     * 测试正则表达式的有效性
     *
     * @return void
     */
    public function testPatternValidity(): void
    {
        $patterns = PatternCollection::getBuiltInPatterns();
        
        foreach ($patterns as $type => $typePatterns) {
            if (is_array($typePatterns)) {
                foreach ($typePatterns as $key => $pattern) {
                    if ($key !== 'default' && is_string($pattern)) {
                        // 测试正则表达式是否有效
                        $isValid = @preg_match($pattern, '') !== false;
                        $this->assertTrue($isValid, "Pattern {$type}:{$key} is not valid: {$pattern}");
                    }
                }
            } elseif (is_string($typePatterns)) {
                // 测试简单正则表达式
                $isValid = @preg_match($typePatterns, '') !== false;
                $this->assertTrue($isValid, "Pattern {$type} is not valid: {$typePatterns}");
            }
        }
    }
}