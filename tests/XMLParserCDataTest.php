<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;

class XMLParserCDataTest extends XMLParserTestCase
{
	protected function getParserStream() {
		return self::streamFromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item>
  <title>Title of Feed Item</title>
  <link>/mylink/article1</link>
  <description>
  	<![CDATA[
      <p>
      <a href="/mylink/article1"><img style="float: left; margin-right: 5px;" height="80" src="/mylink/image" alt=""/></a>
      Author Names
      <br/><em>Date</em>
      <br/>Paragraph of text describing the article to be displayed</p>
    ]]>
  </description>
</item>
XML);
	}

	public function testCDataIsProperlyParsed(): void
	{
		$node = null;

		foreach ($this->getParser() as $item) {
			if ($item instanceof XMLNodeContent && $item->name === 'description') {
				if (trim($item->content) !== '') {
					$node = $item;
					break;
				}
			}
		}

		$this->assertInstanceOf(XMLNodeContent::class, $node);
		$this->assertStringStartsWith("<p>\n      <a href=\"/mylink/article1\">", trim($node->content));
	}
}