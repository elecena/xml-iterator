<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\XMLParser;

/**
 * A PHP stream wrapper that inserts empty reads at regular intervals to simulate
 * compressed or network streams (e.g. zlib flush markers, HTTP chunked boundaries).
 *
 * @see https://www.php.net/manual/en/stream.streamwrapper.example-1.php
 */
class IntermittentReadStreamWrapper
{
    public $context;

    private static string $content = '';
    private static int $emptyEveryN = 3;
    private int $position = 0;
    private int $readCount = 0;

    public static function open(string $content, int $emptyEveryN = 3): string
    {
        self::$content = $content;
        self::$emptyEveryN = $emptyEveryN;

        if (in_array('intermittent', stream_get_wrappers())) {
            stream_wrapper_unregister('intermittent');
        }

        stream_wrapper_register('intermittent', self::class);

        return 'intermittent://stream';
    }

    public static function unregister(): void
    {
        if (in_array('intermittent', stream_get_wrappers())) {
            stream_wrapper_unregister('intermittent');
        }
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        $this->position = 0;
        $this->readCount = 0;
        return true;
    }

    public function stream_read(int $count): string
    {
        $this->readCount++;

        // Return empty string every N reads unless already at EOF
        if ($this->readCount % self::$emptyEveryN === 0 && !$this->stream_eof()) {
            return '';
        }

        $chunk = substr(self::$content, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$content);
    }

    public function stream_stat(): array
    {
        return [];
    }
}

/**
 * Tests XML parsing of nested elements that share the same tag name,
 * as seen in the Discogs data dump (label > sublabels > label).
 *
 * @see https://github.com/elecena/xml-iterator/issues/13
 */
class XMLParserNestedElementsTest extends XMLParserTestCase
{
    private static string $xml = '';

    public static function setUpBeforeClass(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><labels>';
        for ($i = 0; $i < 300; $i++) {
            $xml .= sprintf(
                '<label id="%d"><name>Label %d</name><sublabels><label>Sub %d</label></sublabels></label>',
                $i,
                $i,
                $i
            );
        }
        $xml .= '</labels>';
        self::$xml = $xml;
    }

    protected function tearDown(): void
    {
        IntermittentReadStreamWrapper::unregister();
    }

    protected function getParserStream()
    {
        return self::streamFromString(self::$xml);
    }

    public function testParsesNestedSameNameElements(): void
    {
        $labelCount = 0;

        foreach ($this->getParser() as $node) {
            if ($node instanceof XMLNodeContent && $node->name === 'label') {
                $labelCount++;
            }
        }

        // 300 outer labels + 300 inner labels inside sublabels
        $this->assertSame(600, $labelCount);
    }

    public function testParsesStreamWithIntermittentEmptyReads(): void
    {
        $url = IntermittentReadStreamWrapper::open(self::$xml, emptyEveryN: 3);
        $parser = new XMLParser(stream: fopen($url, 'r'));

        $labelCount = 0;

        foreach ($parser as $node) {
            if ($node instanceof XMLNodeContent && $node->name === 'label') {
                $labelCount++;
            }
        }

        // 300 outer labels + 300 inner labels inside sublabels
        $this->assertSame(600, $labelCount);
    }
}
