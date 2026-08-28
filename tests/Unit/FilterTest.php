<?php

namespace Tests\Unit;

use CatLab\Laravel\Table\Models\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testLabelsAreHumanizedFromCamelAndSnakeCase()
    {
        $this->assertSame('Name', (new Filter('name'))->getLabel());
        $this->assertSame('Created at', (new Filter('createdAt'))->getLabel());
        $this->assertSame('Created at', (new Filter('created_at'))->getLabel());
        $this->assertSame('Opt in', (new Filter('optIn'))->getLabel());
    }

    public function testAFilterIsActiveWhenItHasANonBlankValue()
    {
        $this->assertFalse((new Filter('name'))->isActive());
        $this->assertFalse((new Filter('name', ''))->isActive());
        $this->assertTrue((new Filter('name', 'x'))->isActive());
        $this->assertTrue((new Filter('name', '0'))->isActive());
    }
}
