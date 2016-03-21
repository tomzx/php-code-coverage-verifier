<?php

use PHPCodeCoverageVerifier\CodeCoverageVerifier;

class CodeCoverageVerifierTest extends PHPUnit_Framework_TestCase
{
	private function fixture($path)
	{
		return __DIR__.'/fixtures/'.$path;
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 */
	public function testExecuteNullCloverXmlWithEmptyDiffString()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute(null, '');

		$this->assertEquals($codeCoverageVerifier->get_default_coverage_result(), $coverage);
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 */
	public function testExecuteNullCloverXmlWithDiffString()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute(null, file_get_contents($this->fixture('diff.diff')));

		$this->assertEquals($codeCoverageVerifier->get_default_coverage_result(), $coverage);
	}

	public function testExecuteEmptyCloverXmlFileWithEmptyDiffFile()
	{
		$this->setExpectedException('Exception', 'Failed loading XML: Start tag expected, '<' not found');
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('empty_clover_xml.xml'), $this->fixture('empty_diff.diff'));

		$this->assertEquals($codeCoverageVerifier->get_default_coverage_result(), $coverage);
	}

	public function testExecuteEmptyCloverXmlFileWithDiffFile()
	{
		$this->setExpectedException('Exception', 'Failed loading XML: Start tag expected, '<' not found');
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('empty_clover_xml.xml'), $this->fixture('diff.diff'));

		$this->assertEquals($codeCoverageVerifier->get_default_coverage_result(), $coverage);
	}


	public function testExecuteCloverXmlFileWithEmptyDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('empty_diff.diff'));

		$this->assertEquals($codeCoverageVerifier->get_default_coverage_result(), $coverage);
	}

	public function testExecuteCloverXmlFileWithUnrelatedDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('unrelated_diff.diff'));

		$expected = $codeCoverageVerifier->get_default_coverage_result();
		$expected['ignored'][] = 'application/classes/controller/unrelated.php';
		$this->assertEquals($expected, $coverage);
	}

	public function testExecuteCloverXmlFileWithDiffFile()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('diff.diff'));

		$expected = $codeCoverageVerifier->get_default_coverage_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);
	}

	public function testExecuteCloverXmlFileWithDiffFileWithOptionDisplayNotCoveredRange()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier(array('display_not_covered_range' => true));
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('clover_xml.xml'), $this->fixture('diff.diff'));

		$expected = $codeCoverageVerifier->get_default_coverage_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65 (58, 59)';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);
	}
	
	public function testExecuteCloverXmlWithNamespace()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('namespaced_clover_xml.xml'), $this->fixture('diff.diff'));

		$expected = $codeCoverageVerifier->get_default_coverage_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);
	}
	
	public function testExecuteCloverXmlWithGitDiff()
	{
		$codeCoverageVerifier = new CodeCoverageVerifier();
		$coverage = $codeCoverageVerifier->execute_file($this->fixture('namespaced_clover_xml.xml'), $this->fixture('git.diff'));
		
		$expected = $codeCoverageVerifier->get_default_coverage_result();
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 78 - 84';
		$expected['covered'][] = 'application/classes/controller/a_nice_file.php line 114 - 120';
		$expected['not-covered'][] = 'application/classes/controller/a_nice_file.php line 58 - 65';
		$expected['details']['covered'] = 6;
		$expected['details']['not-covered'] = 2;
		$this->assertEquals($expected, $coverage);

	}
}