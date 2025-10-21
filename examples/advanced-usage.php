<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use pfinalclub\RegexCenter\RegexManager;

echo "PFinal Regex Center - 高级使用示例\n";
echo str_repeat("=", 50) . "\n";

$regex = RegexManager::getInstance();

// 1. 自定义正则表达式注入
echo "1. 自定义正则表达式注入\n";
$customPatterns = [
    'username' => [
        'strict' => '/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/',
        'default' => 'strict'
    ],
    'company' => [
        'name' => '/^[a-zA-Z0-9\s&.-]{2,50}$/',
        'code' => '/^[A-Z]{2,10}$/'
    ]
];

$regex->inject($customPatterns);
echo "   已注入自定义正则表达式\n";

// 测试自定义正则
$usernames = ['john_doe', 'admin123', 'user', '123invalid'];
foreach ($usernames as $username) {
    $isValid = $regex->test('username:strict', $username);
    echo "   用户名 '{$username}': " . ($isValid ? '✓ 有效' : '✗ 无效') . "\n";
}

// 2. 批量数据验证
echo "\n2. 批量数据验证\n";
$userData = [
    'email' => 'user@example.com',
    'phone' => '13812345678',
    'username' => 'john_doe',
    'website' => 'https://www.example.com'
];

$validationRules = [
    'email' => 'email:basic',
    'phone' => 'phone:CN',
    'username' => 'username:strict',
    'website' => 'url:basic'
];

$errors = [];
foreach ($validationRules as $field => $pattern) {
    if (!$regex->test($pattern, $userData[$field])) {
        $errors[] = "{$field} 格式不正确";
    }
}

if (empty($errors)) {
    echo "   ✓ 所有数据验证通过\n";
} else {
    echo "   ✗ 验证失败:\n";
    foreach ($errors as $error) {
        echo "     - {$error}\n";
    }
}

// 3. 文本内容分析
echo "\n3. 文本内容分析\n";
$content = "联系我们：admin@example.com 或 support@company.org，电话：13812345678，网站：https://www.example.com";
echo "   原文: {$content}\n";

// 提取所有邮箱
$emails = $regex->extractAll('email:basic', $content);
echo "   邮箱地址: " . implode(', ', $emails) . "\n";

// 提取所有电话号码
$phones = $regex->extractAll('phone:CN', $content);
echo "   电话号码: " . implode(', ', $phones) . "\n";

// 提取所有 URL
$urls = $regex->extractAll('url:basic', $content);
echo "   网站链接: " . implode(', ', $urls) . "\n";

// 4. 敏感信息脱敏
echo "\n4. 敏感信息脱敏\n";
$sensitiveText = "用户信息：张三，邮箱：zhangsan@example.com，电话：13812345678，身份证：110101199001011234";
echo "   原文: {$sensitiveText}\n";

// 脱敏处理
$maskedText = $sensitiveText;
$maskedText = $regex->replaceAll('email:basic', $maskedText, '[邮箱]');
$maskedText = $regex->replaceAll('phone:CN', $maskedText, '[电话]');
$maskedText = $regex->replaceAll('idCard:CN', $maskedText, '[身份证]');

echo "   脱敏后: {$maskedText}\n";

// 5. 多语言支持示例
echo "\n5. 多语言支持示例\n";
$internationalData = [
    'email' => 'user@example.com',
    'phone_CN' => '13812345678',
    'phone_US' => '+1-555-123-4567',
    'phone_UK' => '+441234567890',
    'postal_CN' => '100000',
    'postal_US' => '12345',
    'postal_UK' => 'SW1A 1AA'
];

$internationalRules = [
    'email' => 'email:basic',
    'phone_CN' => 'phone:CN',
    'phone_US' => 'phone:US',
    'phone_UK' => 'phone:UK',
    'postal_CN' => 'postalCode:CN',
    'postal_US' => 'postalCode:US',
    'postal_UK' => 'postalCode:UK'
];

foreach ($internationalRules as $field => $pattern) {
    $value = $internationalData[$field] ?? '';
    $isValid = $regex->test($pattern, $value);
    echo "   {$field}: {$value} - " . ($isValid ? '✓ 有效' : '✗ 无效') . "\n";
}

// 6. 新增功能演示
echo "\n6. 新增功能演示\n";

// 测试 count 方法
$text = "联系我们：admin@example.com 或 support@example.org，电话：13812345678";
$emailCount = $regex->count('email:basic', $text);
$phoneCount = $regex->count('phone:CN', $text);
echo "   文本中的邮箱数量: {$emailCount}\n";
echo "   文本中的电话数量: {$phoneCount}\n";

// 测试 add 方法
$regex->add('companyCode', '/^[A-Z]{2,4}$/', '公司代码：2-4位大写字母', [
    'valid' => ['ABC', 'XYZ', 'COMP'],
    'invalid' => ['abc', '123', 'TOOLONG']
]);
echo "   自定义正则表达式添加成功\n";

// 测试回调函数替换
$text = "用户手机：13812345678，备用手机：13987654321";
$maskedText = $regex->replaceAll('phone:CN', $text, function($matches) {
    $phone = $matches[0];
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
});
echo "   脱敏处理: {$maskedText}\n";

// 测试新增的正则表达式类型
echo "\n7. 新增正则表达式类型测试\n";
$testData = [
    'creditCard' => ['4111111111111111', '5555555555554444'],
    'macAddress' => ['00:1B:44:11:3A:B7', '00-1B-44-11-3A-B7'],
    'uuid' => ['550e8400-e29b-41d4-a716-446655440000'],
    'hash' => ['5d41402abc4b2a76b9719d911017c592', 'aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434d'],
    'semanticVersion' => ['1.0.0', '2.1.0-beta.1+build.123']
];

foreach ($testData as $type => $values) {
    echo "   {$type}:\n";
    foreach ($values as $value) {
        $isValid = $regex->test($type, $value);
        echo "     {$value}: " . ($isValid ? '✓ 有效' : '✗ 无效') . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "高级示例完成！\n";
