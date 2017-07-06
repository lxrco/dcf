<?php

namespace Lxr\Dcf;

class SymbolExtractor
{
    /**
     * @param $directory
     * @return iterable|array[]
     */
    public function getFileSymbols($directory): iterable
    {
        $iterator = $this->getIterator($directory);
        foreach ($iterator as $file) {
            yield $file->getRealPath() => $this->getSymbols($file);
        }
    }

    protected function getSymbols(\SplFileInfo $file)
    {
        $code = file_get_contents($file);
        $tokens = @token_get_all($code);
        $namespace = $class = $classLevel = $level = null;
        $symbols = [];
        while (list(, $token) = each($tokens)) {
            switch (is_array($token) ? $token[0] : $token) {
                case T_NAMESPACE:
                    $namespace = ltrim($this->fetch($tokens, [T_STRING, T_NS_SEPARATOR]) . '\\', '\\');
                    break;
                case T_CLASS:
                case T_INTERFACE:
                    if ($name = $this->fetch($tokens, T_STRING)) {
                        $class = $name;
                    }
                    break;
                case T_FUNCTION:
                    if ($name = $this->fetch($tokens, T_STRING)) {
                        $symbol = '';
                        if ($namespace) {
                            $symbol .= $namespace;
                        }
                        if ($class) {
                            $symbol .= $class . '::';
                        }
                        $symbol .= $name;
                        $symbols[] = $symbol;
                    }
                    break;
            }
        }

        return $symbols;
    }

    private function fetch(&$tokens, $take)
    {
        $res = null;
        while ($token = current($tokens)) {
            list($token, $s) = is_array($token) ? $token : [$token, $token];
            if (in_array($token, (array)$take, true)) {
                $res .= $s;
            } else if (!in_array($token, [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT], true)) {
                break;
            }
            next($tokens);
        }

        return $res;
    }


    /**
     * @param $path
     * @return \Iterator|\SplFileInfo[]
     */
    protected function getIterator($path)
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $filter = new \CallbackFilterIterator($iterator, function (\SplFileInfo $current, $key, $iterator) {
            // Skip hidden files and directories.
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            if ($current->isDir()) {
                return false;
            }

            return $current->getExtension() === 'php';
        });

        return $filter;
    }
}
