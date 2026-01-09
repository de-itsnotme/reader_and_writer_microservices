<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Messaging;

use App\Application\Messaging\ProductImportMessageParser;
use App\Domain\Product;
use PHPUnit\Framework\TestCase;

class ProductImportMessageParserTest extends TestCase
{
    private ProductImportMessageParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ProductImportMessageParser();
    }

    public function test_it_parses_valid_message(): void
    {
        $json = json_encode([
            'products' => [
                [
                    'gtin' => '123',
                    'language' => 'en',
                    'title' => 'Test',
                    'picture' => 'url',
                    'description' => 'desc',
                    'price' => 10.5,
                    'stock' => 5,
                ]
            ]
        ]);

        $products = $this->parser->parse($json);

        $this->assertCount(1, $products);
        $this->assertInstanceOf(Product::class, $products[0]);
        $this->assertSame('123', $products[0]->getGtin());
    }

    public function test_it_throws_on_missing_products_array(): void
    {
        $json = json_encode(['foo' => 'bar']);

        $this->expectException(\RuntimeException::class);
        $this->parser->parse($json);
    }

    public function test_it_throws_on_missing_required_field(): void
    {
        $json = json_encode([
            'products' => [
                ['gtin' => '123'] // missing many fields
            ]
        ]);

        $this->expectException(\RuntimeException::class);
        $this->parser->parse($json);
    }

    public function test_it_throws_on_invalid_json(): void
    {
        $invalidJson = '{invalid json';

        $this->expectException(\JsonException::class);
        $this->parser->parse($invalidJson);
    }
}
