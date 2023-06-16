<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spudbot\Helpers\Collection;

final class CollectionsTest extends TestCase
{
    public Collection $collection;
    public function setUp(): void
    {
        $this->collection = new Collection();
    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function successfullyCreatesAnEmptyCollection(): void
    {
        $items = $this->collection->getAll();

        $this->assertEmpty($items);
    }

    /**
     * @test
     * @covers  \Spudbot\Helpers\Collection
     */
    public function successfullySetsAndGetsAnItemInACollection(): void
    {
        $value = md5((string)random_int(1, 999999));

        $this->collection->set('key', $value);

        $this->assertEquals($value, $this->collection->get('key'));
        $this->assertCount(1, $this->collection->getAll());
    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function successfullyPushesAnItemInACollection(): void
    {
        $this->collection->push('push');

        $this->assertEquals('push', $this->collection->get(0));
        $this->assertCount(1, $this->collection->getAll());
    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function collectionIsCountable(): void
    {

        $this->collection->push(1);
        $this->collection->push(2);
        $this->collection->push(3);

        $this->assertCount(3, $this->collection);
    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function collectionIsIterable(): void
    {
        $this->collection->push(0);
        $this->collection->push(1);
        $this->collection->push(2);
        $this->collection->push(3);

        foreach($this->collection as $i => $item){
            $this->assertEquals($i, $item);
        }

        $this->assertCount(4, $this->collection);
    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function collectionImplementsArrayAccess(): void
    {
        $this->collection->set('removed', 'value');
        $this->collection->set('kept', 'value');

        $this->collection['key'] = 'value';
        unset($this->collection['removed']);

        $this->assertEquals('value', $this->collection->get('key'));
        $this->assertEquals('value', $this->collection['key']);
        $this->assertEquals('value', $this->collection['kept']);
        $this->assertTrue(isset($this->collection['key']));
        $this->assertTrue(isset($this->collection['kept']));
        $this->assertFalse(isset($this->collection['removed']));

    }

    /**
     * @test
     * @covers \Spudbot\Helpers\Collection
     */
    public function successfullyClearsCollection(): void
    {
        for ($i=0; $i < 100; $i++)
        {
            $this->collection->push($i);
        }

        $this->assertCount(100, $this->collection);

        $this->collection->clear();

        $this->assertCount(0, $this->collection);
    }
}