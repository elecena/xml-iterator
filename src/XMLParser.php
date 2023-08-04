<?php

namespace Elecena\XmlIterator;

use Elecena\XmlIterator\Exceptions\ParsingError;

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

	/**
	 * Holds nodes to iterate over as we parse through XML
	 *
	 * @var Nodes\XMLNode[]
	 */
	private array $nodesStack = [];
	private int $index;

	// how much data to read from the XML at a time
	const BATCH_READ_SIZE = 256;

	/**
	 * @param resource $stream
	 */
	public function __construct(private $stream) {}

	public function setUp(): void {
		$this->index = 0;
		$this->nodesStack = [];
		$this->currentTagName = null;
		$this->currentTagAttributes = [];

		$this->parser = \xml_parser_create();

		/**
		 * Set XML parsing handling methods
		 *
		 * These methods will push @see Nodes\XMLNode classes to the nodesStack array.
		 *
		 * Once the iterator goes through them all, the self::next() method
		 * will read and parse the next portion of the input XML stream.
		 */
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startXML', 'endXML');
		xml_set_character_data_handler($this->parser, 'charXML');

		// @see https://www.php.net/manual/en/function.xml-parser-set-option.php
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
	}

	private function close(): void {
		xml_parser_free($this->parser);
	}

	/**
	 * @param \XMLParser $parser
	 * @param string $tagName
	 * @param array $attributes
	 * @return void
	 */
	public function startXML(\XMLParser $parser, string $tagName, array $attributes): void {
		$this->currentTagName = $tagName;
		$this->currentTagAttributes = $attributes;

		// append to the stack of items to iterate over
		$this->nodesStack[] = new Nodes\XMLNodeOpen(
			tagName: $this->currentTagName,
			tagAttributes: $this->currentTagAttributes,
		);
	}

	public function charXML(\XMLParser $parser, string $tagContent): void {
		// append to the stack of items to iterate over
		$this->nodesStack[] = new Nodes\XMLNodeContent(
			tagName: $this->currentTagName,
			tagAttributes: $this->currentTagAttributes,
			tagContent: $tagContent,
		);
	}

	public function endXML(\XMLParser $parser, string $tagName): void {
		// append to the stack of items to iterate over
		$this->nodesStack[] = new Nodes\XMLNodeClose(
			tagName: $tagName,
		);
	}

	public function current(): Nodes\XMLNode
	{
		return array_shift($this->nodesStack);
	}

	/**
	 * @throws ParsingError
	 */
	public function next(): void
	{
		// we still have some already parsed nodes on the stack
		if (!empty($this->nodesStack)) {
			$this->index++;
			return;
		}

		// the nodes stack has been iterated over, consume and parse the next piece of the XML stream
		$data = stream_get_contents( $this->stream, length: self::BATCH_READ_SIZE );
		$res = xml_parse($this->parser, $data, is_final: $data === false);

		if ($res === 0 /* returns 0 on failure */ ) {
			// take more details from the parser instance and throw an exception
			throw ParsingError::fromParserInstance($this->parser, is_string($data) ? $data : null);
		}

		// we're done with reading and parsing the stream, close the XML parser instance
		if ($data === false) {
			$this->close();
		}
	}

	public function key(): int
	{
		return $this->index;
	}

	public function valid(): bool
	{
		return !empty($this->nodesStack);
	}

	/**
	 * @throws ParsingError
	 */
	public function rewind(): void
	{
		$this->setUp();
		$this->next();
	}
}