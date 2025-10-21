<?php

declare(strict_types=1);

namespace pfinalclub\RegexCenter\Tests;

use pfinalclub\RegexCenter\RegexManager;

/**
 * RegexManager 类测试
 */
class RegexManagerTest extends TestCase
{
    /**
     * @var RegexManager 正则表达式管理器实例
     */
    private RegexManager $regexManager;

    /**
     * 设置测试环境
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->regexManager = RegexManager::getInstance();
    }

    /**
     * 测试获取单例实例
     *
     * @return void
     */
    public function testGetInstance(): void
    {
        $instance1 = RegexManager::getInstance();
        $instance2 = RegexManager::getInstance();
        
        $this->assertInstanceOf(RegexManager::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * 测试获取正则表达式
     *
     * @return void
     */
    public function testGet(): void
    {
        // 测试获取简单的正则表达式
        $urlPattern = $this->regexManager->get('url');
        $this->assertNotNull($urlPattern);
        $this->assertIsString($urlPattern);
        
        // 测试获取带组的正则表达式
        $emailBasicPattern = $this->regexManager->get('email:basic');
        $this->assertNotNull($emailBasicPattern);
        $this->assertIsString($emailBasicPattern);
        
        // 测试获取默认组的正则表达式
        $phonePattern = $this->regexManager->get('phone');
        $this->assertNotNull($phonePattern);
        $this->assertIsString($phonePattern);
        
        // 测试获取不存在的正则表达式
        $nonExistentPattern = $this->regexManager->get('nonexistent');
        $this->assertNull($nonExistentPattern);
    }

    /**
     * 测试测试值是否匹配正则表达式
     *
     * @return void
     */
    public function testTest(): void
    {
        // 测试有效的邮箱地址
        $isValidEmail = $this->regexManager->test('email:basic', 'test@example.com');
        $this->assertTrue($isValidEmail);
        
        // 测试无效的邮箱地址
        $isInvalidEmail = $this->regexManager->test('email:basic', 'invalid-email');
        $this->assertFalse($isInvalidEmail);
        
        // 测试有效的 URL
        $isValidUrl = $this->regexManager->test('url', 'https://www.example.com');
        $this->assertTrue($isValidUrl);
        
        // 测试无效的 URL
        $isInvalidUrl = $this->regexManager->test('url', 'invalid-url');
        $this->assertFalse($isInvalidUrl);
    }

    /**
     * 测试设置自定义正则表达式集合
     *
     * @return void
     */
    public function testUse(): void
    {
        $customPatterns = [
            'custom' => '/^custom-pattern$/'
        ];
        
        $this->regexManager->use($customPatterns);
        
        $customPattern = $this->regexManager->get('custom');
        $this->assertEquals('/^custom-pattern$/', $customPattern);
    }

    /**
     * 测试注入额外的正则表达式
     *
     * @return void
     */
    public function testInject(): void
    {
        $additionalPatterns = [
            'additional' => '/^additional-pattern$/'
        ];
        
        $this->regexManager->inject($additionalPatterns);
        
        $additionalPattern = $this->regexManager->get('additional');
        $this->assertEquals('/^additional-pattern$/', $additionalPattern);
    }

    /**
     * 测试提取文本中所有匹配的项
     *
     * @return void
     */
    public function testExtractAll(): void
    {
        $text = 'Contact us at test@example.com or support@example.org';
        $emails = $this->regexManager->extractAll('email:basic', $text);
        
        $this->assertCount(2, $emails);
        $this->assertContains('test@example.com', $emails);
        $this->assertContains('support@example.org', $emails);
    }

    /**
     * 测试替换文本中所有匹配的项
     *
     * @return void
     */
    public function testReplaceAll(): void
    {
        $text = 'Visit https://www.example.com or http://www.test.org';
        $replacedText = $this->regexManager->replaceAll('url', $text, '[LINK]');
        
        $this->assertEquals('Visit [LINK] or [LINK]', $replacedText);
    }

    /**
     * 测试高亮文本中所有匹配的项
     *
     * @return void
     */
    public function testHighlight(): void
    {
        $text = 'Email: test@example.com';
        $highlightedText = $this->regexManager->highlight('email:basic', $text, '<em>$0</em>');
        
        $this->assertEquals('Email: <em>test@example.com</em>', $highlightedText);
    }

    /**
     * 测试边界情况
     *
     * @return void
     */
    public function testEdgeCases(): void
    {
        // 测试空字符串
        $this->assertFalse($this->regexManager->test('email:basic', ''));
        $this->assertFalse($this->regexManager->test('phone:CN', ''));
        
        // 测试不存在的模式
        $this->assertNull($this->regexManager->get('nonexistent'));
        $this->assertFalse($this->regexManager->test('nonexistent', 'test'));
        
        // 测试不存在的子模式
        $this->assertNull($this->regexManager->get('email:nonexistent'));
        $this->assertFalse($this->regexManager->test('email:nonexistent', 'test@example.com'));
    }


    /**
     * 测试 count 方法
     *
     * @return void
     */
    public function testCount(): void
    {
        $text = 'Contact us at test@example.com or support@example.org';
        $count = $this->regexManager->count('email:basic', $text);
        $this->assertEquals(2, $count);
        
        $text2 = 'No emails here';
        $count2 = $this->regexManager->count('email:basic', $text2);
        $this->assertEquals(0, $count2);
    }

    /**
     * 测试 add 方法
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->regexManager->add('custom', '/^custom-pattern$/', 'Custom pattern for testing', [
            'valid' => ['custom-pattern'],
            'invalid' => ['invalid-pattern']
        ]);
        
        $this->assertTrue($this->regexManager->test('custom', 'custom-pattern'));
        $this->assertFalse($this->regexManager->test('custom', 'invalid-pattern'));
    }

    /**
     * 测试 config 方法
     *
     * @return void
     */
    public function testConfig(): void
    {
        $this->regexManager->config(['securityEnabled' => false]);
        
        // 现在应该可以添加危险的正则表达式（因为安全保护被关闭）
        $this->regexManager->add('dangerous', '/^(a+)+$/', 'Dangerous pattern');
        $this->assertTrue($this->regexManager->test('dangerous', 'aaaaaaaaaa'));
    }

    /**
     * 测试 replaceAll 回调函数
     *
     * @return void
     */
    public function testReplaceAllWithCallback(): void
    {
        $text = 'Phone: 13812345678';
        $result = $this->regexManager->replaceAll('phone:CN', $text, function($matches) {
            // $matches 是数组，第一个元素是完整匹配
            $phone = $matches[0];
            return substr($phone, 0, 3) . '****' . substr($phone, -4);
        });
        
        $this->assertEquals('Phone: 138****5678', $result);
    }

    /**
     * 测试新增的正则表达式类型
     *
     * @return void
     */
    public function testNewPatternTypes(): void
    {
        // 测试用户名
        $this->assertTrue($this->regexManager->test('username:basic', 'john_doe'));
        $this->assertTrue($this->regexManager->test('username:strict', 'john_doe'));
        $this->assertFalse($this->regexManager->test('username:strict', '123invalid'));
        
        // 测试日期
        $this->assertTrue($this->regexManager->test('date:YYYY-MM-DD', '2023-12-25'));
        $this->assertTrue($this->regexManager->test('date:DD/MM/YYYY', '25/12/2023'));
        
        // 测试时间
        $this->assertTrue($this->regexManager->test('time:HH:MM', '14:30'));
        $this->assertTrue($this->regexManager->test('time:HH:MM:SS', '14:30:45'));
        
        // 测试颜色
        $this->assertTrue($this->regexManager->test('color:hex', '#FF0000'));
        $this->assertTrue($this->regexManager->test('color:rgb', 'rgb(255, 0, 0)'));
        
        // 测试邮政编码
        $this->assertTrue($this->regexManager->test('postalCode:CN', '100000'));
        $this->assertTrue($this->regexManager->test('postalCode:US', '12345'));
        
        // 测试货币
        $this->assertTrue($this->regexManager->test('currency:CNY', '100.50'));
        $this->assertTrue($this->regexManager->test('currency:USD', '$100.50'));
        
        // 测试信用卡
        $this->assertTrue($this->regexManager->test('creditCard:VISA', '4111111111111111'));
        $this->assertTrue($this->regexManager->test('creditCard:MASTERCARD', '5555555555554444'));
        
        // 测试 MAC 地址
        $this->assertTrue($this->regexManager->test('macAddress:basic', '00:1B:44:11:3A:B7'));
        $this->assertTrue($this->regexManager->test('macAddress:colon', '00:1B:44:11:3A:B7'));
        
        // 测试 UUID
        $this->assertTrue($this->regexManager->test('uuid:v4', '550e8400-e29b-41d4-a716-446655440000'));
        
        // 测试哈希值
        $this->assertTrue($this->regexManager->test('hash:md5', '5d41402abc4b2a76b9719d911017c592'));
        $this->assertTrue($this->regexManager->test('hash:sha1', 'aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434d'));
        
        // 测试语义化版本
        $this->assertTrue($this->regexManager->test('semanticVersion:basic', '1.0.0'));
        $this->assertTrue($this->regexManager->test('semanticVersion:full', '1.0.0-alpha+build'));
    }
}