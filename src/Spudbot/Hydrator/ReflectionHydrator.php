<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Hydrator;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class ReflectionHydrator extends AbstractHydrator
{

    protected static array $reflectionProperties = [];

    public function extract(object $object, ?array $filter = []): array
    {
        $result = [];
        $reflProperties = self::getReflectionProperties($object);
        $this->getAttributeStrategies($object);
        foreach ($reflProperties as $property) {
            $propName = $this->extractName($property->getName(), $object);
            if (in_array($propName, $filter, true)) {
                continue;
            }
            $value = $property->isInitialized($object) ? $property->getValue($object) : null;
            $result[$propName] = $this->extractValue($propName, $value, $object);
        }
        return $result;
    }

    /**
     * @param object $input
     * @return ReflectionProperty[]
     * @throws ReflectionException
     */
    protected static function getReflectionProperties(object $input): array
    {
        $class = $input::class;

        if (isset(static::$reflectionProperties[$class])) {
            return static::$reflectionProperties[$class];
        }

        static::$reflectionProperties[$class] = [];
        $reflClass = new ReflectionClass($class);
        $reflProperties = $reflClass->getProperties();

        foreach ($reflProperties as $property) {
            static::$reflectionProperties[$class][$property->getName()] = $property;
        }

        return static::$reflectionProperties[$class];
    }

    protected function getAttributeStrategies(object $object): void
    {
        $reflProperties = self::getReflectionProperties($object);
        foreach ($reflProperties as $property) {
            $propName = $this->extractName($property->getName(), $object);
            if (isset($this->strategies[$propName])) {
                continue;
            }
            $attributes = $property->getAttributes();
            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    $this->addStrategy($propName, $attribute->newInstance()->strategy);
                }
            }
        }
    }

    public function hydrate(array $data, object $object)
    {
        $reflProperties = self::getReflectionProperties($object);
        $this->getAttributeStrategies($object);
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);
            $hydrateName = $name;
            if (!$this->hasStrategy($name) && $this->hasStrategy($key)) {
                $hydrateName = $key;
            }
            if (isset($reflProperties[$name])) {
                $reflProperties[$name]->setValue($object, $this->hydrateValue($hydrateName, $value, $data));
            }
        }
        return $object;
    }
}
