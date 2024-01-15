<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;
use Elecena\XmlIterator\Nodes\XMLNodeClose;

class XMLParserTest extends XMLParserTestCase
{
    protected function getParserStream()
    {
        return fopen(__DIR__ . '/fixtures/wp-sitemap.xml', mode: 'rt');
    }

    public function testParsesTheOpeningTags(): void
    {
        $sitemapIndex = null;

        foreach($this->getParser() as $item) {
            if ($item instanceof XMLNodeOpen && $item->name === 'sitemapindex') {
                $sitemapIndex = $item;
                break;
            }
        }

        $this->assertInstanceOf(XMLNodeOpen::class, $sitemapIndex);
        $this->assertEquals('sitemapindex', $sitemapIndex->name);
        $this->assertNull($sitemapIndex->parentName, 'Root nodes will get null as their parent');
        $this->assertEquals('http://www.sitemaps.org/schemas/sitemap/0.9', $sitemapIndex->attributes['xmlns'] ?? null);
    }

    public function testParsesTheClosingTags(): void
    {
        $closingTag = null;

        foreach($this->getParser() as $item) {
            if ($item instanceof XMLNodeClose) {
                $closingTag = $item;
            }
        }

        $this->assertInstanceOf(XMLNodeClose::class, $closingTag);
        $this->assertEquals('sitemapindex', $closingTag->name);
        $this->assertNull($closingTag->parentName);
    }

    public function testParsesTheLocNodes(): void
    {
        $locations = [];

        foreach($this->getParser()->iterateByNodeContent('loc') as $item) {
            $locations[] = $item->content;
            $this->assertEquals('sitemap', $item->parentName);
        }

        $this->assertCount(8, $locations);
        $this->assertEquals('https://sklepzamel.com/wp-sitemap-posts-page-1.xml', $locations[0]);
        $this->assertEquals('https://sklepzamel.com/wp-sitemap-users-1.xml', $locations[7]);
    }
}
