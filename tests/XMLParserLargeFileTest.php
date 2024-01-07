<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;

class XMLParserLargeFileTest extends XMLParserTestCase
{
    protected function getParserStream()
    {
        return fopen(__DIR__ . '/fixtures/wp-sitemap-posts-product-1.xml', mode: 'rt');
    }

    public function testParsesTheUrls(): void
    {
        $locations = [];
        $urlTagsCounter = 0;

        foreach($this->getParser() as $item) {
            if ($item instanceof XMLNodeContent && $item->name === 'loc') {
                $locations[] = $item->content;
            } elseif ($item instanceof XMLNodeOpen && $item->name === 'url') {
                $urlTagsCounter++;
            }
        }

        $this->assertCount(1857, $locations);
        $this->assertEquals(1857, $urlTagsCounter);
    }

    public function testIterateByNodeContent()
    {
        $cnt = 0;

        // <url><loc>https://sklepzamel.com/produkt/sonda-temperatury-ntc-03/</loc></url>
        foreach($this->getParser()->iterateByNodeContent(name: 'loc') as $node) {
            $this->assertInstanceOf(XMLNodeContent::class, $node);
            $this->assertEquals('loc', $node->name);
            $this->assertEquals('url', $node->parentName);
            $this->assertStringStartsWith('http', $node->content);

            $cnt++;
        }

        $this->assertEquals(1857, $cnt);
    }
}
