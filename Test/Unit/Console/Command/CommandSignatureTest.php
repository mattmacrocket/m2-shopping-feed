<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Console\Command;

use MageOS\ShoppingFeed\Console\Command\GenerateCommand;
use MageOS\ShoppingFeed\Console\Command\ScheduleCommand;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CommandSignatureTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function commandProvider(): array
    {
        return [
            'generate' => [GenerateCommand::class],
            'schedule' => [ScheduleCommand::class],
        ];
    }

    /**
     * @param class-string $commandClass
     * @dataProvider commandProvider
     */
    public function testExecuteDeclaresIntegerReturnType(string $commandClass): void
    {
        $returnType = (new ReflectionMethod($commandClass, 'execute'))->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('int', (string) $returnType);
    }

    /**
     * @param class-string $commandClass
     * @dataProvider commandProvider
     */
    public function testConfigureDeclaresVoidReturnType(string $commandClass): void
    {
        $returnType = (new ReflectionMethod($commandClass, 'configure'))->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('void', (string) $returnType);
    }
}
