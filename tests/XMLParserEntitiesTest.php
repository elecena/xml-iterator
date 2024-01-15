<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;
use Elecena\XmlIterator\Nodes\XMLNodeClose;

class XMLParserEntitiesTest extends XMLParserTestCase
{
    protected function getParserStream()
    {
        return fopen(__DIR__ . '/fixtures/sitemap-entities.xml', mode: 'rt');
    }

    public function testParsesTheLocNodesWithAmpersands(): void
    {
        $locations = [];

        foreach($this->getParser()->iterateByNodeContent('loc') as $item) {
            $locations[] = $item->content;
        }

        $this->assertCount(8, $locations);
        $this->assertEquals('https://www.reichelt.com/index.html?ACTION=1004&SITE=1', $locations[0]);
        $this->assertEquals('https://www.reichelt.com/magazin/en/sitemap.xml', $locations[7]);
    }
}
