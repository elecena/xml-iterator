<?php

namespace Elecena\XmlIterator;

use Elecena\XmlIterator\Exceptions\ParsingError;
use Elecena\XmlIterator\Nodes\XMLNodeContent;

/**
 * Implements a fast and memory-efficient XML parser with the iterator interface.
 *
 * @see https://www.php.net/manual/en/function.xml-parse.php
 */
class XMLParser implements \Iterator
{
    private \XMLParser $parser;

    private ?string $currentTagName = null;
    private array $currentTagAttributes = [];

    private string $currentTagContent = '';

    private bool $streamFinished = false;

    /**
     * The stack of the XML node names as go deeper into the tree.
     *
     * @var string[]
     */
    private array $nodeNamesStack = [];

    /**
     * Holds nodes to iterate over as we parse through XML
     *
     * @var Nodes\XMLNode[]
     */
    private array $nodesQueue = [];
    private int $index;

    // how much data to read from the XML at a time
    const BATCH_READ_SIZE = 4096;

    /**
     * @param resource $stream
     */
    public function __construct(private $stream)
    {
    }

    public function setUp(): void
    {
        $this->index = 0;
        $this->nodesQueue = [];
        $this->currentTagName = null;
        $this->currentTagAttributes = [];
        $this->nodeNamesStack = [];
        $this->streamFinished = false;

        $this->parser = \xml_parser_create();

        /**
         * Set XML parsing handling methods
         *
         * These methods will push @see Nodes\XMLNode classes to the nodesStack array.
         *
         * Once the iterator goes through them all, the self::next() method
         * will read and parse the next portion of the input XML stream.
         */
        xml_set_element_handler($this->parser, [$this,'startXML'], [$this, 'endXML']);
        xml_set_character_data_handler($this->parser, [$this,'charXML']);

        // @see https://www.php.net/manual/en/function.xml-parser-set-option.php
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
    }

    private function close(): void
    {
        xml_parser_free($this->parser);
    }

    /**
     * @param \XMLParser $parser
     * @param string $tagName
     * @param array $attributes
     * @return void
     */
    public function startXML(\XMLParser $parser, string $tagName, array $attributes): void
    {
        $this->currentTagName = $tagName;
        $this->currentTagAttributes = $attributes;

        $this->currentTagContent = '';

        // append to the queue of items to iterate over
        $this->nodesQueue[] = new Nodes\XMLNodeOpen(
            name: $this->currentTagName,
            attributes: $this->currentTagAttributes,
            parentName: end($this->nodeNamesStack) ?: null
        );

        $this->nodeNamesStack[] = $tagName;
    }

    /**
     * The XML parser "emits" separate characters when the node has the content with XML entities.
     *
     * For instance: <loc>https://example.com/index.html?ACTION=1004&amp;SITE=3</loc>
     *
     * Would emit: 'https://example.com/index.html?ACTION=1004', '&' and 'SITE=3' separately.
     *
     * So, just accumulate the characters as we're getting them and "emit" the XMLNodeContent instance
     * when the node is closed.
     */
    public function charXML(\XMLParser $parser, string $tagContent): void
    {
        $this->currentTagContent .= $tagContent;
    }

    public function endXML(\XMLParser $parser, string $tagName): void
    {
        // append to the queue of items to iterate over
        $this->nodesQueue[] = new Nodes\XMLNodeContent(
            name: $this->currentTagName,
            attributes: $this->currentTagAttributes,
            content: $this->currentTagContent,
            parentName: array_slice($this->nodeNamesStack, -2, 1)[0] ?: null
        );

        // Pop the node name off the end of stack
        array_pop($this->nodeNamesStack);

        // append to the queue of items to iterate over
        $this->nodesQueue[] = new Nodes\XMLNodeClose(
            name: $tagName,
            parentName: end($this->nodeNamesStack) ?: null
        );

        // and update the current tag name to properly handle consecutive closing tag and whitespaces
        // e.g. </foo>\n\n</bar>
        $this->currentTagName = end($this->nodeNamesStack);
    }

    public function current(): Nodes\XMLNode
    {
        return array_shift($this->nodesQueue);
    }

    /**
     * @throws ParsingError
     */
    public function next(): void
    {
        // we still have some already parsed nodes on the queue
        if (!empty($this->nodesQueue)) {
            $this->index++;
            return;
        }

        $this->parseNextChunk();
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return !empty($this->nodesQueue);
    }

    /**
     * @throws ParsingError
     */
    public function rewind(): void
    {
        $this->setUp();
        $this->parseNextChunk();
    }

    /**
     * Takes the next chunk of data from the XML stream and parses the data.
     *
     * Callbacks are called, nodes are pushed to the stack and iterator can go over them.
     *
     * Loops until at least one node is queued or the stream is exhausted.
     * This handles compressed or network streams that may return empty reads mid-stream
     * (e.g. zlib flush markers or TCP packet boundaries) without signalling EOF.
     *
     * @return void
     * @throws ParsingError
     */
    private function parseNextChunk(): void
    {
        if ($this->streamFinished) {
            return;
        }

        while (empty($this->nodesQueue)) {
            $data = stream_get_contents($this->stream, length: self::BATCH_READ_SIZE);
            $isFinal = ($data === false || feof($this->stream));

            if ($data === false) {
                $data = '';
            }

            $res = xml_parse($this->parser, $data, $isFinal);

            if ($res === 0 /* returns 0 on failure */) {
                // take more details from the parser instance and throw an exception
                throw ParsingError::fromParserInstance($this->parser, $data !== '' ? $data : null);
            }

            if ($isFinal) {
                $this->streamFinished = true;
                $this->close();
                return;
            }
        }
    }

    /**
     * Parses the XML and yields only the @see XMLNodeContent items with matching node name
     *
     * @param string $name
     * @return \Generator<XMLNodeContent>
     */
    public function iterateByNodeContent(string $name): \Generator
    {
        foreach($this as $node) {
            if ($node instanceof XMLNodeContent && $node->name === $name) {
                yield $node;
            }
        }
    }
}
