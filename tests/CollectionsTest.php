<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spudbot\Collection;

final class CollectionsTest extends TestCase
{
    /**
     * @test
     * @covers \Spudbot\Collection
     */
    public function successfullyCreatesAnEmptyCollection(): void
    {
        $collection = new Collection();

        $items = $collection->getAll();

        $this->assertEmpty($items);
    }

    /**
     * @test
     * @covers  \Spudbot\Collection
     */
    public function successfullySetsAndGetsAnItemInACollection(): void
    {
        $collection = new Collection();
        $value = md5((string)random_int(1, 999999));

        $collection->set('key', $value);

        $this->assertEquals($value, $collection->get('key'));
        $this->assertCount(1, $collection->getAll());
    }

    /**
     * @test
     * @covers \Spudbot\Collection
     */
    public function successfullyPushesAnItemInACollection(): void
    {
        $collection = new Collection();

        $collection->push('push');

        $this->assertEquals('push', $collection->get(0));
        $this->assertCount(1, $collection->getAll());
    }

    /**
     * @test
     * @covers \Spudbot\Collection
     */
    public function collectionIsCountable(): void
    {
        $collection = new Collection();

        $collection->push(1);
        $collection->push(2);
        $collection->push(3);

        $this->assertCount(3, $collection);
    }

    /**
     * @test
     * @covers \Spudbot\Collection
     */
    public function collectionIsIterable(): void
    {
        $collection = new Collection();

        $collection->push(0);
        $collection->push(1);
        $collection->push(2);
        $collection->push(3);

        foreach($collection as $i => $item){
            $this->assertEquals($i, $item);
        }

        $this->assertCount(4, $collection);
    }

    /**
     * @test
     * @covers \Spudbot\Collection
     */
    public function collectionImplementsArrayAccess(): void
    {
        $collection = new Collection();
        $collection->set('removed', 'value');
        $collection->set('kept', 'value');

        $collection['key'] = 'value';
        unset($collection['removed']);

        $this->assertEquals('value', $collection->get('key'));
        $this->assertEquals('value', $collection['key']);
        $this->assertEquals('value', $collection['kept']);
        $this->assertTrue(isset($collection['key']));
        $this->assertTrue(isset($collection['kept']));
        $this->assertFalse(isset($collection['removed']));

    }
}