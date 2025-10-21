<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use pfinalclub\RegexCenter\RegexManager;

echo "PFinal Regex Center - 基础使用示例\n";
echo str_repeat("=", 50) . "\n";

$regex = RegexManager::getInstance();

// 1. 邮箱验证
echo "1. 邮箱验证示例\n";
$emails = [
    'user@example.com',
    'admin@company.org',
    'invalid-email',
    'test@domain.co.uk'
];

foreach ($emails as $email) {
    $isValid = $regex->test('email:basic', $email);
    echo "   {$email}: " . ($isValid ? '✓ 有效' : '✗ 无效') . "\n";
}

// 2. 手机号验证
echo "\n2. 手机号验证示例\n";
$phones = [
    '13812345678',  // 中国手机号
    '1381234567',   // 位数不足
    '+8613812345678', // 带国际区号
    '12345678901'   // 无效号码
];

foreach ($phones as $phone) {
    $isValid = $regex->test('phone:CN', $phone);
    echo "   {$phone}: " . ($isValid ? '✓ 有效' : '✗ 无效') . "\n";
}

// 3. URL 提取
echo "\n3. URL 提取示例\n";
$text = "访问我们的网站 https://www.example.com 或 http://test.org 获取更多信息";
$urls = $regex->extractAll('url:basic', $text);
echo "   原文: {$text}\n";
echo "   提取到的 URL: " . implode(', ', $urls) . "\n";

// 4. 文本替换
echo "\n4. 文本替换示例\n";
$originalText = "联系我们：admin@example.com 或访问 https://www.example.com";
$maskedText = $regex->replaceAll('email:basic', $originalText, '[邮箱]');
echo "   原文: {$originalText}\n";
echo "   替换后: {$maskedText}\n";

// 5. 高亮显示
echo "\n5. 高亮显示示例\n";
$highlightedText = $regex->highlight('email:basic', $originalText, '<strong>$&</strong>');
echo "   高亮后: {$highlightedText}\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "示例完成！\n";
