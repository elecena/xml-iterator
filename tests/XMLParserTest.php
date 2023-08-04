<?php

use PHPUnit\Framework\TestCase;
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;

class XMLParserTest extends TestCase
{
	private function openFixture() {
		return fopen( __DIR__ . '/fixtures/wp-sitemap.xml', mode: 'rt');
	}

	public function testParsesTheOpeningTags(): void {
		$parser = new XMLParser(stream: $this->openFixture());
		$sitemapIndex = null;

		foreach($parser as $item) {
			if ($item instanceof XMLNodeOpen && $item->tagName === 'sitemapindex') {
				$sitemapIndex = $item;
				break;
			}
		}

		$this->assertInstanceOf(XMLNodeOpen::class, $sitemapIndex);
		$this->assertEquals('sitemapindex', $sitemapIndex->tagName);
		$this->assertEquals('http://www.sitemaps.org/schemas/sitemap/0.9', $sitemapIndex->tagAttributes['xmlns'] ?? null);
	}

	public function testParsesTheLocNodes(): void {
		$parser = new XMLParser(stream: $this->openFixture());
		$locations = [];

		foreach($parser as $item) {
			if ($item instanceof XMLNodeContent && $item->tagName === 'loc') {
				$locations[] = $item->tagContent;
			}
		}

		$this->assertCount(8, $locations);
		$this->assertEquals('https://sklepzamel.com/wp-sitemap-posts-page-1.xml', $locations[0]);
		$this->assertEquals('https://sklepzamel.com/wp-sitemap-users-1.xml', $locations[7]);
	}
}
