<?php

namespace PHPCodeCoverageVerifier;

class UnifiedDiffParser
{
	private $log = false;

	private $token_to_method = array(
		'Index' => 'parse_index',
		'===' => 'parse_separator',
		'---' => 'parse_source',
		'+++' => 'parse_destination',
		'@@' => 'parse_range',
		' ' => 'parse_unchanged',
		'+' => 'parse_added',
		'-' => 'parse_removed',
		'\\' => 'parse_comment'
	);

	private $contextual_line_count = 0;
	private $added_line_count = 0;
	private $removed_line_count = 0;

	private $current_file = null;
	private $extracted_data = array();

	public function parse($string)
	{
		$lines = explode("\n", $string);
		foreach ($lines as $line_number => $line) {
			$line = trim($line, "\r");

			// Generally only for EOF
			if ($line === '') {
				continue;
			}

			$found = false;
			foreach ($this->token_to_method as $token => $method) {
				if ($this->startsWith($line, $token)) {
					$this->$method($line);
					$found = true;
					break;
				}
			}

			if (!$found) {
				throw new \Exception('Could not find parser for line #'.($line_number+1).', text "'.$line.'"');
			}
		}
	}

	private function parse_index($line)
	{
		$this->log('contextual: '.$this->contextual_line_count.', added: '.$this->added_line_count.', removed: '.$this->removed_line_count);
		$this->contextual_line_count = 0;
		$this->added_line_count = 0;
		$this->removed_line_count = 0;
		$this->log('index '.$line);
	}

	private function parse_separator($line)
	{
		$this->log('separator '.$line);
	}

	private function parse_source($line)
	{
		preg_match('/\-\-\- (\S+)/', $line, $matches);

		if (count($matches) === 0) return;

		array_shift($matches);

		$this->extracted_data[$matches[0]] = array();
		$this->current_file = &$this->extracted_data[$matches[0]];

		$this->log('source('.$matches[0].') '.$line);
	}

	private function parse_destination($line)
	{
		preg_match('/\+\+\+ (\S+)/', $line, $matches);

		if (count($matches) === 0) return;

		array_shift($matches);
		$this->log('destination('.$matches[0].') '.$line);
	}

	private function parse_range($line)
	{
		preg_match('/@@ \-(\d+),(\d+) \+(\d+),(\d+) @@/', $line, $matches);

		if (count($matches) === 0) return;

		array_shift($matches);

		$this->current_file['line'][] = array(
			'range' => array(
				'source'		=> array(
					'line_start' => $matches[0],
					'line_count' => $matches[1]
				),
				'destination' 	=> array(
					'line_start' => $matches[2],
					'line_count' => $matches[3]
				),
			),
		);

		$this->log('range('.$matches[0].', '.$matches[1].', '.$matches[2].', '.$matches[3].') '.$line);
	}

	private function parse_unchanged($line)
	{
		++$this->contextual_line_count;
		$this->log('unchanged '.$line);
	}

	private function parse_added($line)
	{
		++$this->added_line_count;
		$this->log('added '.$line);
	}

	private function parse_removed($line)
	{
		++$this->removed_line_count;
		$this->log('removed '.$line);
	}

	private function parse_comment($line)
	{
		$this->log('comment '.$line);
	}

	public function get_extracted_data()
	{
		return $this->extracted_data;
	}

	private function startsWith($text, $search)
	{
		return strpos($text, $search) === 0;
	}

	private function log($text, $eol = true) {
		if ($this->log) {
			echo $text.($eol ? PHP_EOL : '');
		}
	}
}