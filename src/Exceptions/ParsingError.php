<?php

namespace Elecena\XmlIterator\Exceptions;

class ParsingError extends \Exception
{

    public function __construct(string $message = "", int $code = 0, public ?string $xml = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromParserInstance(\XMLParser $parser, ?string $data): self
    {
        /**
         * For unsuccessful parses, error information can be retrieved with
         * xml_get_error_code, xml_error_string, xml_get_current_line_number,
         * xml_get_current_column_number and xml_get_current_byte_index.
         */
        $code = xml_get_error_code($parser);

        return new self(
            message: xml_error_string($code),
            code: $code,
            xml: $data,
        );
    }
}
