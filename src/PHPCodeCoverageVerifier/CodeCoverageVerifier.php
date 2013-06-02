<?php

namespace PHPCodeCoverageVerifier;

class CodeCoverageVerifier
{
	private $display_not_covered_range = false;

	public function __construct($options)
	{
		$this->display_not_covered_range = $options['display_not_covered_range'];
	}

	private function find_file_in_xml($xml, $filename)
	{
		// TODO: use ends-with (xpath 2.0) instead of contains
		$file_node = $xml->xpath('/coverage/project/file[contains(@name, "'.$filename.'")]');
		if (count($file_node) === 1) {
			return $file_node[0];
		} else {
			return null;
		}
	}

	private function get_line_node_from_file_node($file_node, $line_no)
	{
		$line_node = $file_node->xpath('line[@num="'.$line_no.'" and @type="stmt"]');

		if (count($line_node) === 1) {
			return $line_node[0];
		} else {
			return null;
		}
	}

	/*private function is_covered($xml, $filename, $line_start, $line_count)
	{
		// Find file in clover xml
		$file = $this->find_file_in_xml($xml, $filename);

		if (count($file) !== 1) {
			//echo 'No coverage file found for file '.$filename.'.'.PHP_EOL;
			return false;
			// throw new \Exception('Could not find file info in coverage. File '.$filename);
		}

		$file = $file[0];

		// Iterate over $line_start -> $line_start + $line_count
		$end = $line_start + $line_count;
		for ($i = $line_start; $i < $end; ++$i) {

			$line_info = $file->xpath('line[@num="'.$i.'"]');

			if (count($line_info) !== 1) {
				// Most likely a comment
				continue;
				//throw new \Exception('Could not find file info in coverage. File '.$filename.' line '.$i);
			}
			$line_info = $line_info[0];

			// If line is not covered, return false
			if ((int)$line_info['count'] === 0) {
				return false;
			}
		}

		return true;
	}*/

	private function evaluate_coverage($file_node, $line_start, $line_count)
	{
		$coverage = array('covered' => array(), 'not-covered' => array());

		// Iterate over $line_start -> $line_start + $line_count
		$end = $line_start + $line_count;
		for ($i = $line_start; $i <= $end; ++$i) {
			$line_node = $this->get_line_node_from_file_node($file_node, $i);

			if ((int)$line_node['count'] > 0) {
				$coverage['covered'][] = $i;
			} else if ($line_node['count'] === null) {
				// Do nothing
			} else {
				$coverage['not-covered'][] = $i;
			}
		}

		return $coverage;
	}

	public function execute($clover_xml, $diff_file)
	{
		$xml = simplexml_load_file($clover_xml);
		$unified_diff_content = file_get_contents($diff_file);

		$unified_diff_parser = new UnifiedDiffParser();
		$unified_diff_parser->parse($unified_diff_content);

		$extracted_data = $unified_diff_parser->get_extracted_data();

		$coverage = array(	'covered' => array(),
							'not-covered' => array(),
							'ignored' => array(),
							'details' => array(
											'covered' => 0,
											'not-covered' => 0,
										),
					);

		foreach ($extracted_data as $filename => $line) {
			foreach ($line['line'] as $id => $range) {
				foreach ($range['range'] as $target => $line_info) {
					if ($target === 'destination') {
						$file_node = $this->find_file_in_xml($xml, $filename);
						// Put file with no coverage in ignored
						if ($file_node === null) {
							$coverage['ignored'][] = $filename;
							break;
						}

						$line_start = $line_info['line_start'];
						$line_end = $line_info['line_start'] + $line_info['line_count'];
						$message = $filename.' line '.$line_start.' - '.$line_end;

						$file_coverage = $this->evaluate_coverage($file_node, $line_info['line_start'], $line_info['line_count']);
						$coverage['details']['covered'] += count($file_coverage['covered']);
						$coverage['details']['not-covered'] += count($file_coverage['not-covered']);
						if (count($file_coverage['not-covered']) === 0) {
							$coverage['covered'][] = $message;
						} else {
							if ($this->display_not_covered_range) {
								$message = $message.' ('.implode(', ', $file_coverage['not-covered']).')';
							}
							$coverage['not-covered'][] = $message;
						}
					}
				}
			}
		}

		return $coverage;
	}
}