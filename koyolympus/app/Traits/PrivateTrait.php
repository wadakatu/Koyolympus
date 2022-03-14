<?php
declare(strict_types=1);

namespace App\Traits;

use ReflectionClass;
use ReflectionException;

trait PrivateTrait
{
    /**
     * @throws ReflectionException
     */
    private function getPrivateProperty(object $class, string $property)
    {
        $reflectionClass = new ReflectionClass($class);
        $targetProperty = $reflectionClass->getProperty($property);
        $targetProperty->setAccessible(true);
        return $targetProperty->getValue($class);
    }

    /**
     * @throws ReflectionException
     */
    private function getPrivatePropertyForMockObject(object $class, string $property)
    {
        $reflectionClass = new ReflectionClass($class);
        $targetProperty = $reflectionClass->getParentClass()->getProperty($property);
        $targetProperty->setAccessible(true);
        return $targetProperty->getValue($class);
    }
}
