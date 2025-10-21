<?php

declare(strict_types=1);

namespace pfinalclub\RegexCenter\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * 测试基类
 */
class TestCase extends BaseTestCase
{
    /**
     * 断言两个值相等
     *
     * @param mixed $expected 期望值
     * @param mixed $actual 实际值
     * @param string $message 断言消息
     * @return void
     */
    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        parent::assertEquals($expected, $actual, $message);
    }

    /**
     * 断言条件为真
     *
     * @param mixed $condition 条件
     * @param string $message 断言消息
     * @return void
     */
    public static function assertTrue($condition, string $message = ''): void
    {
        parent::assertTrue($condition, $message);
    }

    /**
     * 断言条件为假
     *
     * @param mixed $condition 条件
     * @param string $message 断言消息
     * @return void
     */
    public static function assertFalse($condition, string $message = ''): void
    {
        parent::assertFalse($condition, $message);
    }
}