<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\NewRelicBundle\Tests\NewRelic;

use Ekino\NewRelicBundle\NewRelic\LoggingInteractorDecorator;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggingInteractorDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideMethods
     */
    public function testGeneric(string $method, array $arguments, $return)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $decorated = $this->createMock(LoggingInteractorDecorator::class);
        $interactor = new LoggingInteractorDecorator($decorated, $logger);

        $logger->expects($this->once())->method('debug');
        $decorated->expects($this->once())->method($method)
            ->with(...$arguments)
            ->willReturn($return);

        $result = $interactor->$method(...$arguments);

        $this->assertSame($return, $result);
    }

    public function provideMethods()
    {
        $reflection = new \ReflectionClass(NewRelicInteractorInterface::class);
        foreach ($reflection->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }
            if ($method->isStatic()) {
                continue;
            }

            $arguments = \array_map(function (\ReflectionParameter $parameter) {
                return $this->getTypeStub($parameter->getType());
            }, $method->getParameters());

            $return = $method->hasReturnType() ? $this->getTypeStub($method->getReturnType()) : null;

            yield [$method->getName(), $arguments, $return];
        }
    }

    private function getTypeStub(?\ReflectionType $type)
    {
        if (null === $type) {
            return \uniqid('', true);
        }

        switch ($type->getName()) {
            case 'string':
                return \uniqid('', true);
            case 'bool':
                return (bool) \rand(0, 1);
            case 'float':
                return \rand(0, 100) / \rand(1, 10);
            case 'int':
                return \rand(0, 100);
            case 'void':
                return null;
            case 'Throwable':
                return new \Exception();
            case 'callable':
                return function () {};
            case 'array':
                return \array_fill(0, 2, \uniqid('', true));
            default:
                throw new \UnexpectedValueException('Unknow type. '.$type->getName());
        }
    }
}
