<?php
class Mocker_Builder
{
    protected $_mock_class_name = 'Mocker_Mock_';

    private $_class;

    public function __construct()
    {
        $this->_mock_class_name = $this->_generateMockClassName();
    }

    public function build($class)
    {
        $code = $this->_buildMockCode($class);
        eval($code);
        $class = $this->_mock_class_name;
        return new $class();
    }

    private function _buildMockCode($class)
    {
        $lines = file(__dir__ . DIRECTORY_SEPARATOR . 'Mock.php');
        $this->_class = $class;
        $code = $this->_buildClassDefinition($lines);
        $code = $this->_buildMethods($code);
        return $code;
    }

    private function _buildClassDefinition($lines)
    {
        $reflection = new ReflectionClass('Mocker_Mock');
        $lines = array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - 1
        );

        $lines[0] = str_replace(
            'class Mocker_Mock',
            "class {$this->_mock_class_name} extends $this->_class",
            $lines[0]
        );

        return implode("\n", $lines);
    }

    private function _buildMethods($code)
    {
        $methods = array();
        $mocked = new ReflectionClass($this->_class);
        foreach ($mocked->getMethods() as $method) {
            $tpl = '    public function ' . $method->getName() . '() {}';
            $methods[] = $tpl;
        }

        $code = str_replace(
            '// METHODS',
            implode("\n", $methods),
            $code
        );

        return $code;
    }

    private function _generateMockClassName()
    {
        return 'Mocker_Mock_' . rand(1000, 9999);
    }
}
